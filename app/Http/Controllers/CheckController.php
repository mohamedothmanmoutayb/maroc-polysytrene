<?php

namespace App\Http\Controllers;

use App\Models\Check;
use App\Models\CheckAllocation;
use App\Models\RawMaterialPurchase;
use App\Models\Client;
use App\Models\ClientBalanceHistory;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;    
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CheckController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_checks')->only(['index', 'show', 'getStatistics']);
        $this->middleware('can:create_checks')->only(['create', 'store']);
        $this->middleware('can:manage_checks')->only(['edit', 'update', 'destroy', 'clearCheck', 'markAsDeposited', 'markAsBounced']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $type = $request->get('type', 'all');

            $checks = Check::with(['creator', 'allocations'])->select('checks.*')->orderBy('created_at', 'desc');

            if ($type === 'entreprise') {
                $checks->where('check_type', 'entreprise');
            } elseif ($type === 'client') {
                $checks->where('check_type', 'client');
            }

            return DataTables::of($checks)
                ->addIndexColumn()
                ->addColumn('action', function($check) {
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= ' <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="'.route('checks.show', $check->check_id).'">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    $btn .= '<li><a class="dropdown-item" href="'.route('checks.edit', $check->check_id).'">
                                <i class="fas fa-edit me-2"></i>Éditer</a></li>';

                    if ($check->available_amount > 0 && $check->status !== 'cancelled') {
                        $btn .= '<li><a class="dropdown-item allocate-check" href="'.route('checks.allocate', $check->check_id).'">
                                    <i class="fas fa-hand-holding-usd me-2"></i>Allouer</a></li>';
                    }

                    if ($check->status === 'pending') {
                        $btn .= '<li><a class="dropdown-item mark-deposited" href="#" data-id="'.$check->check_id.'" data-number="'.$check->check_number.'">
                                    <i class="fas fa-inbox me-2 text-info"></i>Marquer comme Déposé</a></li>';
                    }

                    if (!in_array($check->status, ['cleared', 'bounced', 'cancelled'])) {
                        $btn .= '<li><a class="dropdown-item clear-check" href="#" data-id="'.$check->check_id.'" data-number="'.$check->check_number.'">
                                    <i class="fas fa-check-circle me-2 text-success"></i>Marquer comme Payé</a></li>';
                    }

                    if (!in_array($check->status, ['cleared', 'bounced', 'cancelled'])) {
                        $btn .= '<li><a class="dropdown-item mark-bounced" href="#" data-id="'.$check->check_id.'" data-number="'.$check->check_number.'">
                                    <i class="fas fa-times-circle me-2 text-danger"></i>Marquer Rebondi</a></li>';
                    }

                    $btn .= '<li><hr class="dropdown-divider"></li>';
                    $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$check->check_id.'" data-number="'.$check->check_number.'">
                                <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->addColumn('check_number_formatted', function($row) {
                    return '<strong>' . $row->check_number . '</strong>';
                })
                ->addColumn('account_holder', function($row) {
                    return $row->account_holder;
                })
                ->addColumn('bank_name', function($row) {
                    return $row->bank_name ?? '-';
                })
                ->addColumn('amount_formatted', function($row) {
                    return number_format($row->amount, 2, ',', '.') . ' DH';
                })
                ->addColumn('available_amount', function($row) {
                    $available = $row->available_amount;
                    $color = $available > 0 ? 'success' : 'warning';
                    $badge = $available > 0 ? 'bg-success' : 'bg-warning';
                    $text = $available > 0 ? 'Disponible' : 'Épuisé';

                    return '<span class="badge '.$badge.'">'.number_format($available, 2, ',', '.').' DH</span>
                            <small class="d-block text-muted">'.$text.'</small>';
                })
                ->addColumn('dates', function($row) {
                    $html = '<div class="small">';
                    $html .= '<div><strong>Émission:</strong> ' . $row->issue_date->format('d/m/Y') . '</div>';
                    if ($row->deposit_date) {
                        $html .= '<div><strong>Dépôt:</strong> ' . $row->deposit_date->format('d/m/Y') . '</div>';
                    }
                    if ($row->clearing_date) {
                        $html .= '<div><strong>Encaissement:</strong> ' . $row->clearing_date->format('d/m/Y') . '</div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('clearing_info', function($row) {
                    if ($row->status === 'cleared') {
                        return '<span class="badge bg-success">Encaissé</span>';
                    }

                    if (!$row->deposit_date) {
                        return '<span class="text-muted">Non déposé</span>';
                    }

                    $daysLeft = $row->days_to_clearing;

                    if ($daysLeft === 0) {
                        return '<span class="badge bg-warning">Encaissement aujourd\'hui</span>';
                    } elseif ($daysLeft > 0) {
                        return '<span class="badge bg-info">' . $daysLeft . ' jour(s) restant</span>';
                    } else {
                        return '<span class="badge bg-danger">En retard</span>';
                    }
                })
                ->addColumn('clearing_action', function($row) {
                    if ($row->can_clear) {
                        return '<button class="btn btn-sm btn-success clear-check-btn"
                                data-id="'.$row->check_id.'"
                                data-number="'.$row->check_number.'">
                                <i class="fas fa-check-circle me-1"></i>Encaisser
                            </button>';
                    }
                    return 'Aucun';
                })
                ->addColumn('check_image', function($row) {
                    if ($row->check_image) {
                        return '<img src="'.asset('storage/'.$row->check_image).'"
                                  alt="Chèque" style="max-height: 40px; max-width: 60px; cursor: pointer;"
                                  class="img-thumbnail" onclick="window.open(\''.asset('storage/'.$row->check_image).'\', \'_blank\')">';
                    }
                    return '-';
                })
                ->addColumn('status_badge', function($row) {
                    $badges = [
                        'pending' => 'warning',
                        'deposited' => 'info',
                        'cleared' => 'success',
                        'bounced' => 'danger',
                        'cancelled' => 'secondary'
                    ];
                    $color = $badges[$row->status] ?? 'secondary';
                    $labels = [
                        'pending' => 'En attente',
                        'deposited' => 'Déposé',
                        'cleared' => 'Encaissé',
                        'bounced' => 'Rebondi',
                        'cancelled' => 'Annulé'
                    ];
                    $label = $labels[$row->status] ?? $row->status;
                    return '<span class="badge badge-'.$color.'">'.$label.'</span>';
                })
                ->addColumn('check_type_badge', function($row) {
                    $badges = [
                        'entreprise' => 'primary',
                        'client' => 'success'
                    ];
                    $color = $badges[$row->check_type] ?? 'secondary';
                    $labels = [
                        'entreprise' => 'Entreprise',
                        'client' => 'Client'
                    ];
                    $label = $labels[$row->check_type] ?? $row->check_type;
                    return '<span class="badge badge-'.$color.'">'.$label.'</span>';
                })
                ->rawColumns(['action', 'check_number_formatted', 'available_amount', 'dates', 'clearing_info', 'clearing_action', 'check_image', 'status_badge', 'check_type_badge'])
                ->make(true);
        }

        $totalChecks = Check::count();
        $totalAmount = Check::sum('amount');
        $availableAmount = Check::sum('amount') - CheckAllocation::sum('allocated_amount');
        $depositedChecks = Check::where('status', 'deposited')->count();
        $enterpriseChecks = Check::enterprise()->count();
        $clientChecks = Check::where('check_type', 'client')->count();

        return view('pages.checks.index', compact(
            'totalChecks',
            'totalAmount',
            'availableAmount',
            'depositedChecks',
            'enterpriseChecks',
            'clientChecks'
        ));
    }

    public function create()
    {
        $nextCheckNumber = 'CHK-' . date('Ymd') . '-' . str_pad(Check::count() + 1, 4, '0', STR_PAD_LEFT);
        $clients = Client::where('is_active', true)->get();

        return view('pages.checks.create', compact('nextCheckNumber', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'check_number' => 'required|unique:checks|max:50',
            'check_type' => 'required|in:entreprise,client',
            'amount' => 'required|numeric|min:0.01',
            'bank_name' => 'required|string|max:100',
            'account_holder' => 'required|string|max:200',
            'issue_date' => 'required|date',
            'deposit_date' => 'required|date',
            'clearing_date' => 'nullable|date',
            'check_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:pending,deposited,cleared,bounced,cancelled',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $checkImagePath = null;
            if ($request->hasFile('check_image')) {
                $checkImage = $request->file('check_image');
                $filename = time() . '_' . $request->check_number . '.' . $checkImage->getClientOriginalExtension();
                $path = $checkImage->storeAs('checks', $filename, 'public');
                $checkImagePath = $path;
            }

            $check = Check::create([
                'check_number' => $request->check_number,
                'check_type' => $request->check_type,
                'amount' => $request->amount,
                'remaining_amount' => $request->amount,
                'bank_name' => $request->bank_name,
                'account_holder' => $request->account_holder,
                'issue_date' => $request->issue_date,
                'deposit_date' => $request->deposit_date,
                'clearing_date' => $request->clearing_date,
                'check_image' => $checkImagePath,
                'status' => $request->status,
                'notes' => $request->notes,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chèque créé avec succès!',
                'check_id' => $check->check_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $check = Check::with(['creator', 'allocations.purchase.supplier'])->findOrFail($id);
        return view('pages.checks.show', compact('check'));
    }

    public function edit($id)
    {
        $check = Check::findOrFail($id);
        $clients = Client::where('is_active', true)->get();

        return view('pages.checks.edit', compact('check', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $check = Check::findOrFail($id);

        $request->validate([
            'check_number' => 'required|unique:checks,check_number,'.$id.',check_id|max:50',
            'check_type' => 'required|in:entreprise,client',
            'amount' => 'required|numeric|min:0.01',
            'bank_name' => 'required|string|max:100',
            'account_holder' => 'required|string|max:200',
            'issue_date' => 'required|date',
            'deposit_date' => 'required|date|after_or_equal:issue_date',
            'clearing_date' => 'nullable|date|after_or_equal:deposit_date',
            'check_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:pending,deposited,cleared,bounced,cancelled',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $check->status;
            $checkImagePath = $check->check_image;

            if ($request->hasFile('check_image')) {
                if ($check->check_image && Storage::disk('public')->exists($check->check_image)) {
                    Storage::disk('public')->delete($check->check_image);
                }

                $checkImage = $request->file('check_image');
                $filename = time() . '_' . $request->check_number . '.' . $checkImage->getClientOriginalExtension();
                $path = $checkImage->storeAs('checks', $filename, 'public');
                $checkImagePath = $path;
            }

            $check->update([
                'check_number' => $request->check_number,
                'check_type' => $request->check_type,
                'amount' => $request->amount,
                'bank_name' => $request->bank_name,
                'account_holder' => $request->account_holder,
                'issue_date' => $request->issue_date,
                'deposit_date' => $request->deposit_date,
                'clearing_date' => $request->clearing_date,
                'check_image' => $checkImagePath,
                'status' => $request->status,
                'notes' => $request->notes,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // If this edit moved a client check into bounced/cancelled and it had
            // already been counted as a payment, reverse it so the solde stays correct.
            if (
                $check->check_type === 'client' &&
                in_array($request->status, ['bounced', 'cancelled']) &&
                !in_array($oldStatus, ['bounced', 'cancelled']) &&
                $check->payment_id
            ) {
                $this->reverseCheckPayment($check);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chèque mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $check = Check::findOrFail($id);

            if ($check->allocations()->exists()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Ce chèque ne peut pas être supprimé car il a des allocations associées.'
                ], 400);
            }

            // If this check already impacted the client's solde, reverse it first
            if ($check->check_type === 'client' && $check->payment_id) {
                $this->reverseCheckPayment($check);
            }

            if ($check->check_image && Storage::disk('public')->exists($check->check_image)) {
                Storage::disk('public')->delete($check->check_image);
            }

            $check->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chèque supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function allocate($id)
    {
        $check = Check::findOrFail($id);
        $purchases = RawMaterialPurchase::where('payment_status', '!=', 'paid')
            ->with('supplier')
            ->get();

        return view('pages.checks.allocate', compact('check', 'purchases'));
    }

    public function storeAllocation(Request $request, $id)
    {
        $check = Check::findOrFail($id);

        $request->validate([
            'purchase_id' => 'required|exists:raw_material_purchases,purchase_id',
            'allocated_amount' => 'required|numeric|min:0.01|max:' . $check->available_amount,
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $allocation = CheckAllocation::create([
                'check_id' => $id,
                'purchase_id' => $request->purchase_id,
                'allocated_amount' => $request->allocated_amount,
                'notes' => $request->notes,
            ]);

            $purchase = RawMaterialPurchase::find($request->purchase_id);
            $totalAllocated = CheckAllocation::where('purchase_id', $request->purchase_id)->sum('allocated_amount');

            if ($totalAllocated >= $purchase->final_amount) {
                $purchase->update(['payment_status' => 'paid']);
            } elseif ($totalAllocated > 0) {
                $purchase->update(['payment_status' => 'partial']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Allocation enregistrée avec succès!',
                'allocation_id' => $allocation->allocation_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'allocation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearCheck(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $check = Check::findOrFail($id);

            if (in_array($check->status, ['cleared', 'bounced', 'cancelled'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Ce chèque ne peut pas être marqué comme payé.'
                ], 400);
            }

            $check->update([
                'status' => 'cleared',
                'clearing_date' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chèque marqué comme payé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsDeposited($id)
    {
        try {
            $check = Check::findOrFail($id);

            if ($check->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seul un chèque en attente peut être marqué comme déposé.'
                ], 400);
            }

            $check->update([
                'status' => 'deposited',
                'deposit_date' => $check->deposit_date ?: now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chèque marqué comme déposé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'opération: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsBounced($id)
    {
        DB::beginTransaction();
        try {
            $check = Check::findOrFail($id);

            if (in_array($check->status, ['cleared', 'bounced', 'cancelled'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Ce chèque ne peut pas être marqué comme rebondi.'
                ], 400);
            }

            // If this check had already been counted as a client payment, reverse it
            // so the client's solde reflects the bounce.
            if ($check->check_type === 'client' && $check->payment_id) {
                $this->reverseCheckPayment($check);
            }

            $check->update(['status' => 'bounced']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chèque marqué comme rebondi! Le solde client a été mis à jour.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'opération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse the client payment/balance impact of a bounced client check.
     */
    private function reverseCheckPayment($check)
    {
        $payment = SalesOrderPayment::find($check->payment_id);

        if ($payment) {
            $order = $check->order_id ? SalesOrder::find($check->order_id) : null;

            if ($order) {
                $order->paid_amount -= $payment->amount;
                $order->save();
                $order->updatePaymentStatus();

                $client = Client::find($check->client_id);
                if ($client) {
                    $client->updateBalanceFromOrder($order, 'payment_deleted', $check->amount);
                }
            } else {
                $this->updateClientBalance(
                    $check->client_id,
                    $check->amount,
                    'debit',
                    $check,
                    "Annulation du crédit suite au rejet du chèque #{$check->check_number}"
                );
            }

            $payment->delete();
        }

        $check->update(['payment_id' => null]);
    }

    /**
     * Update client balance using the existing Client model methods.
     */
    private function updateClientBalance($clientId, $amount, $type, $check, $description = null)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return;
        }

        $previousBalance = $client->balance;
        $newBalance = $type === 'credit' ? $previousBalance + $amount : $previousBalance - $amount;

        $client->balance = $newBalance;
        $client->save();

        ClientBalanceHistory::create([
            'client_id' => $clientId,
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'amount' => $type === 'credit' ? $amount : -$amount,
            'type' => $type === 'credit' ? 'check_credit' : 'check_debit',
            'reference_type' => 'check',
            'reference_id' => $check->check_id,
            'description' => $description ?: ($type === 'credit'
                ? "Crédit via chèque #{$check->check_number}"
                : "Débit via annulation chèque #{$check->check_number}"),
            'created_by' => Auth::id(),
        ]);
    }

    public function getStatistics()
    {
        $totalChecks = Check::count();
        $depositedChecks = Check::where('status', 'deposited')->count();
        $clearedChecks = Check::where('status', 'cleared')->count();
        $bouncedChecks = Check::where('status', 'bounced')->count();
        $totalAmount = Check::sum('amount');
        $availableAmount = Check::sum('amount') - CheckAllocation::sum('allocated_amount');
        $enterpriseChecks = Check::enterprise()->count();
        $clientChecks = Check::where('check_type', 'client')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalChecks,
                'deposited' => $depositedChecks,
                'cleared' => $clearedChecks,
                'bounced' => $bouncedChecks,
                'total_amount' => $totalAmount,
                'available_amount' => $availableAmount,
                'enterprise' => $enterpriseChecks,
                'client' => $clientChecks
            ]
        ]);
    }
}
