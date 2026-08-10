<?php

namespace App\Traits;

use App\Models\Check;
use App\Models\Client;
use App\Models\ClientBalanceHistory;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use App\Models\Traite;
use Illuminate\Support\Facades\Auth;

/**
 * A chèque or a traite received from a client is credited to that client the moment
 * it is recorded — before it has actually cleared. When it comes back unpaid the
 * credit has to be taken back: the vente it paid returns to unpaid and the client's
 * solde is debited again.
 *
 * The same money has to be undone wherever an instrument can be flagged impayé —
 * the chèques module, the traites module, and a client chèque endorsed to a
 * supplier — so the reversal lives here once.
 */
trait ReversesClientPayments
{
    /**
     * Undo what a chèque credited to its client. Returns the amount taken back.
     */
    protected function reverseClientCheckPayment(Check $check): float
    {
        $amount = $this->reverseClientInstrumentPayment([
            'payment_id'     => $check->payment_id,
            'order_id'       => $check->order_id,
            'client_id'      => $check->client_id,
            'amount'         => (float) $check->amount,
            'reference_type' => 'check',
            'reference_id'   => $check->check_id,
            'debit_type'     => 'check_debit',
            'credit_note'    => "Annulation du crédit suite au rejet du chèque #{$check->check_number}",
            'excess_note'    => "Annulation de l'excédent suite au rejet du chèque #{$check->check_number}",
        ]);

        $check->update(['payment_id' => null]);

        return $amount;
    }

    /**
     * Undo what a traite credited to its client. Returns the amount taken back.
     */
    protected function reverseClientTraitePayment(Traite $traite): float
    {
        $amount = $this->reverseClientInstrumentPayment([
            'payment_id'     => $traite->payment_id,
            'order_id'       => $traite->order_id,
            'client_id'      => $traite->client_id,
            'amount'         => (float) $traite->amount,
            'reference_type' => 'traite',
            'reference_id'   => $traite->traite_id,
            'debit_type'     => 'traite_debit',
            'credit_note'    => "Annulation du crédit suite à suppression/annulation de la traite #{$traite->traite_number}",
            'excess_note'    => "Annulation de l'excédent suite à suppression/annulation de la traite #{$traite->traite_number}",
        ]);

        $traite->update([
            'payment_id'   => null,
            'payment_date' => null,
        ]);

        return $amount;
    }

    /**
     * Take back everything a bounced chèque / traite had credited to its client,
     * whether it paid a vente or was booked straight onto the solde. Returns the
     * amount taken back — 0 when the instrument is tied to no client at all.
     *
     * Must run inside a transaction.
     */
    protected function reverseBouncedClientCredit($instrument): float
    {
        if ($instrument instanceof Check) {
            if ($instrument->payment_id) {
                return $this->reverseClientCheckPayment($instrument);
            }

            if ($instrument->client_id) {
                return $this->debitClientForInstrument(
                    $instrument->client_id,
                    (float) $instrument->amount,
                    'check',
                    $instrument->check_id,
                    'check_debit',
                    "Annulation du crédit suite au rejet du chèque #{$instrument->check_number}"
                );
            }

            return 0.0;
        }

        if ($instrument instanceof Traite) {
            if ($instrument->payment_id) {
                return $this->reverseClientTraitePayment($instrument);
            }

            if ($instrument->client_id) {
                return $this->debitClientForInstrument(
                    $instrument->client_id,
                    (float) $instrument->amount,
                    'traite',
                    $instrument->traite_id,
                    'traite_debit',
                    "Annulation du crédit suite au rejet de la traite #{$instrument->traite_number}"
                );
            }
        }

        return 0.0;
    }

    /**
     * The shared body of both reversals: drop the vente payment the instrument
     * created, put the order back to unpaid and debit the client for it.
     */
    private function reverseClientInstrumentPayment(array $meta): float
    {
        if (!$meta['payment_id']) {
            return 0.0;
        }

        $payment = SalesOrderPayment::find($meta['payment_id']);

        if (!$payment) {
            return 0.0;
        }

        $reversed = 0.0;
        $order    = $meta['order_id'] ? SalesOrder::find($meta['order_id']) : null;

        if ($order) {
            $order->paid_amount -= $payment->amount;
            $order->save();
            $order->updatePaymentStatus();

            $client = Client::find($meta['client_id']);
            if ($client) {
                // updateBalanceFromOrder expects the amount actually applied to THIS
                // order, not the full instrument amount — passing the full amount
                // undercounts the reversal whenever part of it went to this order and
                // the rest was credited as excess.
                $client->updateBalanceFromOrder($order, 'payment_deleted', $payment->amount);
                $reversed += (float) $payment->amount;

                // Anything beyond what was applied to the order was credited straight
                // to the solde — take that back too.
                $excess = round($meta['amount'] - (float) $payment->amount, 2);
                if ($excess > 0.005) {
                    $client->refresh();
                    $previousBalance = (float) $client->balance;
                    $newBalance      = $previousBalance - $excess;
                    $client->balance = $newBalance;
                    $client->save();

                    $client->balanceHistory()->create([
                        'previous_balance' => $previousBalance,
                        'new_balance'      => $newBalance,
                        'amount'           => -$excess,
                        'type'             => 'payment_deleted',
                        'reference_type'   => $meta['reference_type'],
                        'reference_id'     => $meta['reference_id'],
                        'description'      => $meta['excess_note'] . ': ' . number_format($excess, 2, ',', '.') . ' DH',
                        'created_by'       => Auth::id(),
                    ]);
                    $reversed += $excess;
                }
            }
        } else {
            $reversed += $this->debitClientForInstrument(
                $meta['client_id'],
                $meta['amount'],
                $meta['reference_type'],
                $meta['reference_id'],
                $meta['debit_type'],
                $meta['credit_note']
            );
        }

        $payment->delete();

        return $reversed;
    }

    /** Debit a client's solde and log why. Returns the amount debited. */
    private function debitClientForInstrument($clientId, float $amount, string $referenceType, $referenceId, string $historyType, string $description): float
    {
        $client = Client::find($clientId);

        if (!$client) {
            return 0.0;
        }

        $previousBalance = (float) $client->balance;
        $newBalance      = $previousBalance - $amount;

        $client->balance = $newBalance;
        $client->save();

        ClientBalanceHistory::create([
            'client_id'        => $clientId,
            'previous_balance' => $previousBalance,
            'new_balance'      => $newBalance,
            'amount'           => -$amount,
            'type'             => $historyType,
            'reference_type'   => $referenceType,
            'reference_id'     => $referenceId,
            'description'      => $description,
            'created_by'       => Auth::id(),
        ]);

        return $amount;
    }
}
