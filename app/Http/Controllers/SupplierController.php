<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_suppliers')->only(['index', 'show', 'getStatistics']);
        $this->middleware('can:create_suppliers')->only(['create', 'store']);
        $this->middleware('can:edit_suppliers')->only(['edit', 'update']);
        $this->middleware('can:delete_suppliers')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $suppliers = Supplier::query()
                ->select('suppliers.*')
                ->selectSub(function ($q) {
                    $q->selectRaw('COALESCE(SUM(p2.final_amount - p2.paid_amount), 0)')
                        ->from('raw_material_purchases as p2')
                        ->whereColumn('p2.supplier_id', 'suppliers.supplier_id')
                        ->where('p2.payment_status', '!=', 'paid');
                }, 'actual_unpaid_rest');

            return DataTables::of($suppliers)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $user = auth()->user();
                    $actualUnpaid = (float) ($row->actual_unpaid_rest ?? 0);
                    $dropdown = '<div class="dropdown dropstart">
                        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="' . route('suppliers.show', $row->supplier_id) . '">
                                    <i class="fs-4 ti ti-eye"></i>Voir Détails
                                </a>
                            </li>';
                    $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3 pay-supplier-all-btn" href="javascript:void(0)"
                                   data-id="' . $row->supplier_id . '"
                                   data-name="' . e($row->display_name) . '"
                                   data-rest="' . $actualUnpaid . '"
                                   data-balance="' . (float)$row->balance . '">
                                    <i class="fs-4 ti ti-cash text-success"></i>Ajouter Paiement
                                </a>
                            </li>';
                    $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3 add-balance-btn" href="javascript:void(0)"
                                   data-id="' . $row->supplier_id . '"
                                   data-name="' . e($row->display_name) . '"
                                   data-balance="' . (float)$row->balance . '">
                                    <i class="fs-4 ti ti-wallet text-info"></i>Ajouter Solde
                                </a>
                            </li>';
                    if ($user->can('edit_suppliers')) {
                        $dropdown .= '<li>
                                <a class="dropdown-item d-flex align-items-center gap-3" href="' . route('suppliers.edit', $row->supplier_id) . '">
                                    <i class="fs-4 ti ti-edit"></i>Modifier
                                </a>
                            </li>';
                    }
                    if ($user->can('delete_suppliers')) {
                        $dropdown .= '<li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-3 delete" href="javascript:void(0)"
                                   data-id="' . $row->supplier_id . '"
                                   data-name="' . $row->display_name . '">
                                    <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                                </a>
                            </li>';
                    }
                    $dropdown .= '</ul></div>';
                    return $dropdown;
                })
                ->addColumn('balance_display', function ($row) {
                    $balance = (float) $row->balance;
                    $fmt = number_format(abs($balance), 2, ',', '.') . ' DH';
                    if ($balance > 0) {
                        return '<span class="badge bg-danger">' . $fmt . '</span>';
                    } elseif ($balance < 0) {
                        return '<span class="badge bg-success">' . $fmt . '</span>';
                    } else {
                        return '<span class="badge bg-warning text-dark">0,00 DH</span>';
                    }
                })
                ->addColumn('supplier_type_badge', function ($row) {
                    if ($row->supplier_type == 'morale') {
                        return '<span class="badge badge-primary">Morale (Entreprise)</span>';
                    } else {
                        return '<span class="badge badge-info">Physique (Individuel)</span>';
                    }
                })
                ->addColumn('display_name', function ($row) {
                    if ($row->supplier_type == 'morale') {
                        return $row->company_name ?: '-';
                    }
                    return $row->full_name ?: '-';
                })
                ->addColumn('contact_person', function ($row) {
                    if ($row->supplier_type == 'morale') {
                        return $row->representative_name ?: '-';
                    }
                    return $row->full_name ?: '-';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success">Actif</span>'
                        : '<span class="badge badge-danger">Inactif</span>';
                })
                ->addColumn('company_info', function ($row) {
                    if ($row->supplier_type == 'morale') {
                        $info = [];
                        if ($row->ice) $info[] = 'ICE: ' . $row->ice;
                        if ($row->rc) $info[] = 'RC: ' . $row->rc;
                        if ($row->patente) $info[] = 'Patente: ' . $row->patente;

                        if (count($info) > 0) {
                            $tooltip = implode('<br>', $info);
                            return '<span class="badge badge-primary" data-bs-toggle="tooltip" data-bs-html="true" title="' . $tooltip . '">
                                <i class="fas fa-building"></i> ' . count($info) . ' info
                            </span>';
                        }
                    }
                    return '-';
                })
                ->rawColumns(['action', 'supplier_type_badge', 'status_badge', 'company_info', 'balance_display'])
                ->make(true);
        }

        return view('pages.suppliers.index');
    }

    public function create()
    {
        return view('pages.suppliers.create');
    }

    /**
     * French validation messages for the supplier create/update forms.
     */
    private function validationMessages()
    {
        return [
            'supplier_type.required' => 'Le type de fournisseur est requis.',
            'supplier_type.in' => 'Le type de fournisseur sélectionné est invalide.',
            'full_name.string' => 'Le nom complet doit être une chaîne de caractères.',
            'full_name.max' => 'Le nom complet ne doit pas dépasser 100 caractères.',
            'representative_name.string' => 'Le nom du représentant doit être une chaîne de caractères.',
            'representative_name.max' => 'Le nom du représentant ne doit pas dépasser 100 caractères.',
            'company_name.required_if' => 'Le nom de l\'entreprise est requis pour les fournisseurs moraux.',
            'company_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'company_name.max' => 'Le nom de l\'entreprise ne doit pas dépasser 100 caractères.',
            'ice.string' => 'L\'ICE doit être une chaîne de caractères.',
            'ice.max' => 'L\'ICE ne doit pas dépasser 30 caractères.',
            'rc.string' => 'Le registre de commerce doit être une chaîne de caractères.',
            'rc.max' => 'Le registre de commerce ne doit pas dépasser 30 caractères.',
            'patente.string' => 'La patente doit être une chaîne de caractères.',
            'patente.max' => 'La patente ne doit pas dépasser 30 caractères.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé par un autre fournisseur.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre fournisseur.',
            'address.string' => 'L\'adresse doit être une chaîne de caractères.',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'is_active.boolean' => 'Le statut actif est invalide.',
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_type' => 'required|in:physique,morale',
            'full_name' => 'nullable|string|max:100',
            'representative_name' => 'nullable|string|max:100',
            'company_name' => 'required_if:supplier_type,morale|nullable|string|max:100',
            'ice' => 'nullable|string|max:30',
            'rc' => 'nullable|string|max:30',
            'patente' => 'nullable|string|max:30',
            'phone' => ['required', 'string', 'max:20', Rule::unique('suppliers', 'phone')->whereNull('deleted_at')],
            'email' => ['nullable', 'email', Rule::unique('suppliers', 'email')->whereNull('deleted_at')],
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ], $this->validationMessages());

        try {
            Supplier::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Fournisseur créé avec succès!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $supplier = Supplier::with(['rawMaterialPurchases'])->findOrFail($id);
        return view('pages.suppliers.show', compact('supplier'));
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('pages.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'supplier_type' => 'required|in:physique,morale',
            'full_name' => 'nullable|string|max:100',
            'representative_name' => 'nullable|string|max:100',
            'company_name' => 'required_if:supplier_type,morale|nullable|string|max:100',
            'ice' => 'nullable|string|max:30',
            'rc' => 'nullable|string|max:30',
            'patente' => 'nullable|string|max:30',
            'phone' => ['required', 'string', 'max:20', Rule::unique('suppliers', 'phone')->ignore($id, 'supplier_id')->whereNull('deleted_at')],
            'email' => ['nullable', 'email', Rule::unique('suppliers', 'email')->ignore($id, 'supplier_id')->whereNull('deleted_at')],
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ], $this->validationMessages());

        try {
            $supplier->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Fournisseur mis à jour avec succès!'
            ]);
        } catch (\Exception $e) {
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
            $supplier = Supplier::findOrFail($id);

            // Check if supplier has associated purchases
            $hasPurchases = $supplier->rawMaterialPurchases()->exists();

            if ($hasPurchases) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce fournisseur ne peut pas être supprimé car il est utilisé dans le système.'
                ], 400);
            }

            $supplier->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fournisseur supprimé avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics()
    {
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();
        $physiqueSuppliers = Supplier::where('supplier_type', 'physique')->count();
        $moraleSuppliers = Supplier::where('supplier_type', 'morale')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalSuppliers,
                'active' => $activeSuppliers,
                'physique' => $physiqueSuppliers,
                'morale' => $moraleSuppliers
            ]
        ]);
    }

    public function getSuppliersSelect2(Request $request)
    {
        $search = $request->get('search');

        $suppliers = Supplier::where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('company_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('representative_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->select('supplier_id as id', DB::raw("CONCAT(
                CASE
                    WHEN supplier_type = 'morale' THEN COALESCE(company_name, full_name)
                    ELSE COALESCE(full_name, company_name)
                END,
                ' - ',
                phone
            ) as text"))
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $suppliers
        ]);
    }
}
