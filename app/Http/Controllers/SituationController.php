<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SituationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_client_situation');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SalesOrder::with(['client'])
                ->select('sales_orders.*');

            // Apply filters
            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('order_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('order_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('client_name', function($row) {
                    return $row->client->display_name;
                })
                ->addColumn('client_balance', function($row) {
                    $balance = $row->client->balance;
                    if ($balance > 0) {
                        return '<span class="text-success">+' . number_format($balance, 2, ',', '.') . ' DH</span>';
                    } elseif ($balance < 0) {
                        return '<span class="text-danger">' . number_format($balance, 2, ',', '.') . ' DH</span>';
                    }
                    return '<span class="text-secondary">0,00 DH</span>';
                })
                ->addColumn('client_credit_usage', function($row) {
                    $usage = $row->client->credit_usage;
                    $limit = $row->client->credit_limit;
                    if ($limit > 0) {
                        $percentage = ($usage / $limit) * 100;
                        $class = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                        return '<span class="badge bg-' . $class . '">' . number_format($usage, 2, ',', '.') . ' / ' . number_format($limit, 2, ',', '.') . ' DH</span>';
                    }
                    return '<span class="text-muted">Non défini</span>';
                })
                ->addColumn('order_date_formatted', function($row) {
                    return $row->order_date->format('d/m/Y');
                })
                ->addColumn('payment_status_badge', function($row) {
                    $badges = [
                        'pending' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                    ];
                    $color = $badges[$row->payment_status] ?? 'secondary';
                    $labels = [
                        'pending' => 'Non Payé',
                        'partial' => 'Avance',
                        'paid' => 'Payé',
                    ];
                    $label = $labels[$row->payment_status] ?? $row->payment_status;
                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('rest_amount', function($row) {
                    $rest = $row->final_amount - $row->paid_amount;
                    $class = $rest > 0 ? 'text-danger' : 'text-success';
                    return '<span class="' . $class . '">' . number_format($rest, 2, ',', '.') . ' DH</span>';
                })
                ->editColumn('final_amount', function($row) {
                    return number_format($row->final_amount, 2, ',', '.') . ' DH';
                })
                ->editColumn('paid_amount', function($row) {
                    return number_format($row->paid_amount, 2, ',', '.') . ' DH';
                })
                ->addColumn('actions', function($row) {
                    return $btn = '<a href="' . route('sales.orders.show', $row->order_id) . '"
                            class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>';
                })
                ->rawColumns(['client_balance', 'client_credit_usage', 'payment_status_badge', 'rest_amount', 'actions'])
                ->make(true);
        }

        $clients = Client::where('is_active', true)->orderBy('name')->get();

        // Get summary statistics with credit info
        $summary = [
            'total_orders' => SalesOrder::count(),
            'total_amount' => SalesOrder::sum('final_amount'),
            'total_paid' => SalesOrder::sum('paid_amount'),
            'total_unpaid' => SalesOrder::sum(DB::raw('final_amount - paid_amount')),
            'pending_orders' => SalesOrder::where('payment_status', 'pending')->count(),
            'partial_orders' => SalesOrder::where('payment_status', 'partial')->count(),
            'paid_orders' => SalesOrder::where('payment_status', 'paid')->count(),
            'total_credit_used' => Client::sum('credit_usage'),
            'total_credit_limit' => Client::sum('credit_limit'),
        ];

        return view('pages.sales.situation.index', compact('clients', 'summary'));
    }
    public function clientSituation(Request $request, $clientId)
    {
        $client = Client::with(['balanceHistory' => function ($query) {
            $query->with('creator')->limit(50);
        }])->findOrFail($clientId);

        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $badges = ['pending' => 'warning', 'partial' => 'info', 'paid' => 'success'];
        $labels = ['pending' => 'Non Payé', 'partial' => 'Avance', 'paid' => 'Payé'];

        $ordersQuery = $client->salesOrders()
            ->with(['invoiceItems.invoice'])
            ->orderBy('order_date', 'desc');

        if ($dateFrom) {
            $ordersQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $ordersQuery->whereDate('order_date', '<=', $dateTo);
        }

        $invoicesQuery = $client->invoices()->orderBy('invoice_date', 'desc');

        if ($dateFrom) {
            $invoicesQuery->whereDate('invoice_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $invoicesQuery->whereDate('invoice_date', '<=', $dateTo);
        }

        $totalsRow = $client->salesOrders()
            ->when($dateFrom, fn($q) => $q->whereDate('order_date', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('order_date', '<=', $dateTo))
            ->selectRaw('SUM(final_amount) as total_montant, SUM(paid_amount) as total_paye, SUM(final_amount - paid_amount) as total_reste')
            ->first();

        $standaloneQuery = SalesOrderPayment::where('client_id', $clientId)
            ->whereNull('order_id')
            ->orderBy('payment_date', 'desc')
            ->orderBy('payment_id', 'desc');

        if ($dateFrom) {
            $standaloneQuery->whereDate('payment_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $standaloneQuery->whereDate('payment_date', '<=', $dateTo);
        }

        $totalStandalone = (clone $standaloneQuery)->sum('amount');

        // "Vente & Facture" mixed timeline: both sales orders and invoices,
        // merged and sorted by date, so a facture created manually (with no
        // linked vente) is visible here too.
        $mixedItems = (clone $ordersQuery)->get()
            ->map(fn($order) => $this->mixedTimelineRow('vente', $order, $badges, $labels))
            ->concat(
                (clone $invoicesQuery)->get()
                    ->map(fn($invoice) => $this->mixedTimelineRow('facture', $invoice, $badges, $labels))
            )
            ->sortByDesc('date_raw')
            ->values()
            ->map(fn($item) => collect($item)->except('date_raw')->all());

        if ($request->ajax()) {
            $methodLabels = [
                'cash'     => 'Espèces',
                'check'    => 'Chèque',
                'transfer' => 'Virement',
                'traite'   => 'Traite',
                'advance'  => 'Avance',
                'avoir'    => 'Avoir',
            ];

            $orders = (clone $ordersQuery)->get()->map(function ($order) use ($badges, $labels) {
                $reste = $order->final_amount - $order->paid_amount;
                return [
                    'date'            => $order->order_date->format('d/m/Y'),
                    'order_number'    => $order->order_number,
                    'final_amount'    => number_format($order->final_amount, 2, ',', '.') . ' DH',
                    'paid_amount'     => number_format($order->paid_amount,  2, ',', '.') . ' DH',
                    'reste'           => number_format($reste,               2, ',', '.') . ' DH',
                    'reste_class'     => $reste > 0 ? 'text-danger' : 'text-success',
                    'status_badge'    => $badges[$order->payment_status] ?? 'secondary',
                    'status_label'    => $labels[$order->payment_status]  ?? $order->payment_status,
                    'show_url'        => route('sales.orders.show', $order->order_id),
                    'invoices'        => $order->related_invoices->map(fn($invoice) => [
                        'invoice_number' => $invoice->invoice_number,
                        'show_url'       => route('sales.invoices.show', $invoice->invoice_id),
                    ])->values(),
                ];
            });

            $payments = (clone $standaloneQuery)->get()->map(function ($payment) use ($methodLabels) {
                return [
                    'date'          => $payment->payment_date->format('d/m/Y'),
                    'ref'           => 'REG-' . str_pad($payment->payment_id, 4, '0', STR_PAD_LEFT),
                    'method'        => $methodLabels[$payment->payment_method] ?? ucfirst($payment->payment_method),
                    'amount'        => number_format($payment->amount, 2, ',', '.') . ' DH',
                    'notes'         => $payment->notes ?? '',
                ];
            });

            $totalReste = $totalsRow->total_reste ?? 0;

            return response()->json([
                'orders' => $orders,
                'totals' => [
                    'montant'      => number_format($totalsRow->total_montant ?? 0, 2, ',', '.') . ' DH',
                    'paye'         => number_format($totalsRow->total_paye    ?? 0, 2, ',', '.') . ' DH',
                    'reste'        => number_format($totalReste,               2, ',', '.') . ' DH',
                    'reste_class'  => $totalReste > 0 ? 'text-danger' : 'text-success',
                ],
                'payments'        => $payments,
                'total_standalone' => number_format($totalStandalone, 2, ',', '.') . ' DH',
                'mixed'           => $mixedItems,
            ]);
        }

        $totalsQuery       = $totalsRow;
        $orders            = $ordersQuery->paginate(20);
        $standalonePayments = (clone $standaloneQuery)->paginate(20, ['*'], 'pay_page');

        $mixedPage   = LengthAwarePaginator::resolveCurrentPage('mixed_page');
        $mixedPerPage = 20;
        $mixedPaginated = new LengthAwarePaginator(
            $mixedItems->forPage($mixedPage, $mixedPerPage)->values(),
            $mixedItems->count(),
            $mixedPerPage,
            $mixedPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'mixed_page']
        );

        return view('pages.sales.situation.client', compact(
            'client', 'orders', 'totalsQuery', 'dateFrom', 'dateTo',
            'standalonePayments', 'totalStandalone', 'mixedPaginated'
        ));
    }

    /**
     * Normalize a SalesOrder or Invoice into a common row shape for the
     * "Vente & Facture" mixed timeline.
     */
    private function mixedTimelineRow(string $type, $model, array $badges, array $labels): array
    {
        if ($type === 'vente') {
            $amount = (float) $model->final_amount;
            $paid   = (float) $model->paid_amount;
            $status = $model->payment_status;
            $reste  = $amount - $paid;

            return [
                'type'         => $type,
                'type_label'   => 'Vente',
                'type_badge'   => 'secondary',
                'date_raw'     => $model->order_date,
                'date'         => $model->order_date->format('d/m/Y'),
                'number'       => $model->order_number,
                'amount'       => number_format($amount, 2, ',', '.') . ' DH',
                'paid'         => number_format($paid, 2, ',', '.') . ' DH',
                'reste'        => number_format($reste, 2, ',', '.') . ' DH',
                'reste_class'  => $reste > 0 ? 'text-danger' : 'text-success',
                'status_badge' => $badges[$status] ?? 'secondary',
                'status_label' => $labels[$status] ?? $status,
                'show_url'     => route('sales.orders.show', $model->order_id),
            ];
        }

        // Factures aren't paid individually — payment is tracked against the
        // vente they're billing for, so Payé/Reste/Statut don't apply here.
        return [
            'type'         => $type,
            'type_label'   => 'Facture',
            'type_badge'   => 'primary',
            'date_raw'     => $model->invoice_date,
            'date'         => $model->invoice_date->format('d/m/Y'),
            'number'       => $model->invoice_number,
            'amount'       => number_format((float) $model->final_amount, 2, ',', '.') . ' DH',
            'paid'         => '-',
            'reste'        => '-',
            'reste_class'  => '',
            'status_badge' => 'secondary',
            'status_label' => '-',
            'show_url'     => route('sales.invoices.show', $model->invoice_id),
        ];
    }

/**
 * Print client situation as PDF (similar to delivery note)
 */
public function printClientSituation(Request $request, $clientId)
{
    try {
        $client = Client::findOrFail($clientId);

        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        // ── 1. Fetch orders (with payments) in date range ──────────────────
        $ordersQuery = $client->salesOrders()
            ->with(['payments' => function ($q) {
                $q->orderBy('payment_date')->orderBy('payment_id');
            }])
            ->orderBy('order_date')
            ->orderBy('order_id');

        if ($dateFrom) {
            $ordersQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $ordersQuery->whereDate('order_date', '<=', $dateTo);
        }

        $orders = $ordersQuery->get();

        // ── 2. Fetch standalone payments (no linked order) ─────────────────
        $standaloneQuery = \App\Models\SalesOrderPayment::where('client_id', $clientId)
            ->whereNull('order_id')
            ->orderBy('payment_date')
            ->orderBy('payment_id');

        if ($dateFrom) {
            $standaloneQuery->whereDate('payment_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $standaloneQuery->whereDate('payment_date', '<=', $dateTo);
        }

        $standalonePayments = $standaloneQuery->get();

        // ── 3. Build chronological ledger entries ──────────────────────────
        $modeMap = [
            'cash'     => 'ESPECE',
            'check'    => 'CHEQUE',
            'transfer' => 'VIREMENT',
            'traite'   => 'TRAITE',
            'advance'  => 'AVANCE',
            'avoir'    => 'AVOIR',
        ];

        $statusMap = [
            'pending' => 'Non Payé',
            'partial' => 'Avance',
            'paid'    => 'Payé',
        ];

        $entries = [];

        foreach ($orders as $order) {
            // Order → Debit row
            $entries[] = [
                'date'            => $order->order_date,
                'designation'     => 'Bon Vente : ' . $order->order_number,
                'debit'           => (float) $order->final_amount,
                'credit'          => 0.0,
                'montant_rejete'  => 0.0,
                'etat'            => $statusMap[$order->payment_status] ?? '',
                'mode'            => '',
                'sort_key'        => $order->order_date->format('Y-m-d') . '_0_' . str_pad($order->order_id, 10, '0', STR_PAD_LEFT),
            ];

            // Each payment on this order → Credit row
            foreach ($order->payments as $payment) {
                $ref = 'REG : ' . str_pad($payment->payment_id, 4, '0', STR_PAD_LEFT);
                $entries[] = [
                    'date'           => $payment->payment_date,
                    'designation'    => $ref,
                    'debit'          => 0.0,
                    'credit'         => (float) $payment->amount,
                    'montant_rejete' => 0.0,
                    'etat'           => '',
                    'mode'           => $modeMap[$payment->payment_method] ?? strtoupper($payment->payment_method),
                    'sort_key'       => $payment->payment_date->format('Y-m-d') . '_1_' . str_pad($payment->payment_id, 10, '0', STR_PAD_LEFT),
                ];
            }
        }

        // Standalone payments (excess / direct balance credits)
        foreach ($standalonePayments as $payment) {
            $ref = 'REG : ' . str_pad($payment->payment_id, 4, '0', STR_PAD_LEFT);
            $entries[] = [
                'date'           => $payment->payment_date,
                'designation'    => $ref,
                'debit'          => 0.0,
                'credit'         => (float) $payment->amount,
                'montant_rejete' => 0.0,
                'etat'           => '',
                'mode'           => $modeMap[$payment->payment_method] ?? strtoupper($payment->payment_method),
                'sort_key'       => $payment->payment_date->format('Y-m-d') . '_1_' . str_pad($payment->payment_id, 10, '0', STR_PAD_LEFT),
            ];
        }

        // Append standalone payments at the end (sorted by date from the query)
        // Do NOT re-sort the full list — entries are already grouped: each order
        // is immediately followed by its own payments, then standalone payments.

        // ── 4. Running balance (debit ↑ = client owes us, credit ↑ = advance) ──
        $runningBalance = 0.0;
        foreach ($entries as &$entry) {
            $runningBalance += $entry['debit'] - $entry['credit'];
            $entry['solde'] = $runningBalance;
        }
        unset($entry);

        $totalDebit  = array_sum(array_column($entries, 'debit'));
        $totalCredit = array_sum(array_column($entries, 'credit'));
        $finalSolde  = $totalDebit - $totalCredit;

        $printDate = now()->format('d/m/Y');

        // Get parameters for display options
        $showLogo = $request->query('show_logo', 1);

        // Prepare data for PDF view
        $data = [
            'client' => $client,
            'entries' => $entries,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'finalSolde' => $finalSolde,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'printDate' => $printDate,
            'showLogo' => (bool) $showLogo,
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.client-situation', $data);
        $pdf->setPaper('A5', 'portrait');

        // Stream the PDF inline (same as delivery note)
        return $pdf->stream('situation-client-' . $client->client_id . '-' . date('Y-m-d') . '.pdf');

    } catch (\Exception $e) {
        \Log::error('Client situation PDF generation error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()
        ], 500);
    }
}

    public function export(Request $request)
    {
        $query = SalesOrder::with(['client']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $orders = $query->orderBy('order_date', 'desc')->get();

        $filename = 'situation_client_' . date('Y-m-d_His') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Date', 'N° Commande', 'Client', 'Montant Total', 'Payé', 'Reste', 'Statut'];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $row = [
                    $order->order_date->format('d/m/Y'),
                    $order->order_number,
                    $order->client->display_name,
                    number_format($order->final_amount, 2, ',', '.') . ' DH',
                    number_format($order->paid_amount, 2, ',', '.') . ' DH',
                    number_format($order->final_amount - $order->paid_amount, 2, ',', '.') . ' DH',
                    $order->payment_status == 'pending' ? 'Non Payé' :
                        ($order->payment_status == 'partial' ? 'Avance' : 'Payé'),
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
