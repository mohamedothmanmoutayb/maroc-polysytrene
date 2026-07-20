<?php

namespace App\Http\Controllers;

use App\Models\Check;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use App\Models\Traite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_clients')->only(['index', 'show', 'getStatistics', 'getClientsSelect2', 'list', 'checkCredit', 'getClientBalance', 'getCreditStatus', 'documents']);
        $this->middleware('can:create_clients')->only(['create', 'store', 'uploadDocument', 'deleteDocument']);
        $this->middleware('can:edit_clients')->only(['edit', 'update', 'addBalance', 'distributePayment']);
        $this->middleware('can:delete_clients')->only(['destroy']);
        $this->middleware('can:view_client_situation')->only(['clientSituation']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $clients = Client::query();

                if ($request->filled('client_type')) {
                    $clients->where('client_type', $request->client_type);
                }

                if ($request->filled('person_type')) {
                    $clients->where('person_type', $request->person_type);
                }

                if ($request->filled('is_active')) {
                    $clients->where('is_active', $request->is_active);
                }

                if ($request->filled('credit_status')) {
                    $creditStatus = $request->credit_status;

                    $clients->where(function($query) use ($creditStatus) {
                        $creditUsedSubquery = DB::table('sales_orders')
                            ->select(DB::raw('COALESCE(SUM(final_amount - paid_amount), 0)'))
                            ->whereColumn('client_id', 'clients.client_id')
                            ->whereIn('payment_status', ['pending', 'partial']);

                        if ($creditStatus === 'over') {
                            $query->whereRaw('(' . $creditUsedSubquery->toSql() . ') > credit_limit')
                                ->mergeBindings($creditUsedSubquery);
                        } elseif ($creditStatus === 'warning') {
                            $query->whereRaw('(' . $creditUsedSubquery->toSql() . ') > credit_limit * 0.7')
                                ->mergeBindings($creditUsedSubquery)
                                ->whereRaw('(' . $creditUsedSubquery->toSql() . ') <= credit_limit')
                                ->mergeBindings($creditUsedSubquery);
                        } elseif ($creditStatus === 'good') {
                            $query->whereRaw('(' . $creditUsedSubquery->toSql() . ') < credit_limit * 0.7')
                                ->mergeBindings($creditUsedSubquery);
                        }
                    });
                }

                if ($request->filled('search_name')) {
                    $searchTerm = $request->search_name;

                    $clients->where(function($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('entreprise_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('cin', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('ice', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                    });
                }

                return DataTables::of($clients)
                    ->addIndexColumn()
                    ->addColumn('action', function($row) {
                        $user = auth()->user();
                        $btn = '<div class="dropdown dropstart">';
                        $btn .= ' <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical fs-6"></i>
                                </a>';
                        $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                        $btn .= '<li><a class="dropdown-item" href="'.route('clients.show', $row->client_id).'">
                                    <i class="fas fa-eye me-2"></i>Voir</a></li>';
                        if ($user->can('edit_clients')) {
                            $btn .= '<li><a class="dropdown-item" href="'.route('clients.edit', $row->client_id).'">
                                        <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                        }
                        // if ($user->can('print_client_situation')) {
                            $btn .= '<li><a class="dropdown-item" href="'.route('sales.situation.client', $row->client_id).'" target="_blank">
                                        <i class="fas fa-chart-line me-1"></i>Situation</a></li>';
                            $btn .= '<li><a class="dropdown-item" href="'.route('sales.situation.client.print', $row->client_id).'" target="_blank">
                                        <i class="fas fa-print me-1"></i>Imprimer Situation</a></li>';
                        // }
                        if ($user->can('create_sales_orders')) {
                            $btn .= '<li><hr class="dropdown-divider"></li>';
                            $btn .= '<li>
                                        <button class="dropdown-item add-payment-btn" data-id="'.$row->client_id.'" data-name="'.$row->display_name.'">
                                            <i class="fas fa-money-bill-wave text-success me-2"></i>Ajouter Paiement
                                        </button>
                                    </li>';
                            $btn .= '<li>
                                        <button class="dropdown-item add-balance-btn" data-id="'.$row->client_id.'" data-name="'.$row->display_name.'">
                                            <i class="fas fa-wallet text-primary me-2"></i>Ajouter Solde
                                        </button>
                                    </li>';
                        }
                        if ($user->can('delete_clients')) {
                            $btn .= '<li><hr class="dropdown-divider"></li>';
                            $btn .= '<li><a class="dropdown-item delete-client" href="#" data-id="'.$row->client_id.'" data-name="'.$row->display_name.'">
                                        <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                        }
                        $btn .= '</ul></div>';

                        return $btn;
                    })
                    ->addColumn('display_name', function($row) {
                        return $row->display_name ?? ($row->name ?? $row->entreprise_name ?? 'N/A');
                    })
                    ->addColumn('client_type_badge', function($row) {
                        $colors = ['client' => 'primary', 'commerciale' => 'success', 'grossiste' => 'warning'];
                        $color = $colors[$row->client_type] ?? 'secondary';
                        return '<span class="badge bg-'.$color.'">'.ucfirst($row->client_type).'</span>';
                    })
                    ->addColumn('person_type_badge', function($row) {
                        $colors = ['physique' => 'info', 'morale' => 'success', 'special' => 'warning'];
                        $color = $colors[$row->person_type] ?? 'secondary';
                        return '<span class="badge bg-'.$color.'">'.ucfirst($row->person_type).'</span>';
                    })
                    ->addColumn('phone', function($row) {
                        return $row->phone ?? '-';
                    })
                    ->addColumn('identification', function($row) {
                        if ($row->person_type === 'morale') {
                            return $row->ice ?? $row->rc ?? '-';
                        }
                        return $row->cin ?? '-';
                    })
                    ->addColumn('address', function($row) {
                        return $row->address ? (strlen($row->address) > 30 ? substr($row->address, 0, 30).'...' : $row->address) : '-';
                    })
                    ->addColumn('balance', function($row) {
                        $balance = $row->balance;

                        if ($balance > 0) {
                            return '<span class="badge bg-success">' . number_format($balance, 2, ',', '.') . ' DH (Avance)</span>';
                        } elseif ($balance < 0) {
                            return '<span class="badge bg-danger">' . number_format(abs($balance), 2, ',', '.') . ' DH (Impayé)</span>';
                        } else {
                            return '<span class="badge bg-secondary">0,00 DH</span>';
                        }
                    })
                    ->addColumn('credit_info', function($row) {
                        $creditUsed = $this->calculateCreditUsed($row->client_id);
                        $creditLimit = $row->credit_limit ?: 0;

                        if ($creditLimit <= 0) {
                            return '<span class="badge bg-secondary">Illimité</span>';
                        }

                        $percentage = $creditLimit > 0 ? min(($creditUsed / $creditLimit) * 100, 100) : 0;
                        $color = $percentage >= 100 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');

                        return '<div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-'.$color.'"
                                role="progressbar"
                                style="width: '.$percentage.'%;"
                                aria-valuenow="'.$percentage.'"
                                aria-valuemin="0"
                                aria-valuemax="100">
                                '.number_format($creditUsed, 0).'/'.number_format($creditLimit, 0).'
                            </div>
                        </div>';
                    })
                    ->addColumn('status_badge', function($row) {
                        return $row->is_active ?
                            '<span class="badge bg-success">Actif</span>' :
                            '<span class="badge bg-danger">Inactif</span>';
                    })
                    ->addColumn('purchase_summary', function($row) {
                        $stats = $this->getPurchaseStats($row->client_id);
                        return '<small>Total: '.number_format($stats['total'], 0).' DH<br>Payé: '.number_format($stats['paid'], 0).' DH</small>';
                    })
                    ->rawColumns(['action', 'client_type_badge', 'person_type_badge', 'credit_info', 'status_badge', 'purchase_summary', 'balance'])
                    ->make(true);

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('pages.clients.index');
    }

    // Helper methods
    private function calculateCreditUsed($clientId)
    {
        return DB::table('sales_orders')
            ->where('client_id', $clientId)
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum(DB::raw('final_amount - paid_amount')) ?: 0;
    }

    /**
     * French validation messages for the client create/update forms.
     */
    private function validationMessages()
    {
        return [
            'client_type.required' => 'Le type de client est requis.',
            'client_type.in' => 'Le type de client sélectionné est invalide.',
            'person_type.required' => 'Le type de personne est requis.',
            'person_type.in' => 'Le type de personne sélectionné est invalide.',
            'name.required_if' => 'Le nom complet est requis pour les clients physiques.',
            'name.string' => 'Le nom complet doit être une chaîne de caractères.',
            'name.max' => 'Le nom complet ne doit pas dépasser 100 caractères.',
            'entreprise_name.required_if' => 'Le nom de l\'entreprise est requis pour les clients moraux.',
            'entreprise_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'entreprise_name.max' => 'Le nom de l\'entreprise ne doit pas dépasser 100 caractères.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé par un autre client.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre client.',
            'address.string' => 'L\'adresse doit être une chaîne de caractères.',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'cin.string' => 'Le CIN doit être une chaîne de caractères.',
            'cin.max' => 'Le CIN ne doit pas dépasser 20 caractères.',
            'ice.string' => 'L\'ICE doit être une chaîne de caractères.',
            'ice.max' => 'L\'ICE ne doit pas dépasser 20 caractères.',
            'rc.string' => 'Le registre de commerce doit être une chaîne de caractères.',
            'rc.max' => 'Le registre de commerce ne doit pas dépasser 20 caractères.',
            'patente.string' => 'La patente doit être une chaîne de caractères.',
            'patente.max' => 'La patente ne doit pas dépasser 20 caractères.',
            'credit_limit.required' => 'La limite de crédit est requise.',
            'credit_limit.numeric' => 'La limite de crédit doit être un nombre.',
            'credit_limit.min' => 'La limite de crédit ne peut pas être négative.',
            'notes.string' => 'Les notes doivent être une chaîne de caractères.',
            'is_active.boolean' => 'Le statut actif est invalide.',
        ];
    }

    private function getPurchaseStats($clientId)
    {
        $total = DB::table('sales_orders')
            ->where('client_id', $clientId)
            ->sum('final_amount') ?: 0;

        $paid = DB::table('sales_orders')
            ->where('client_id', $clientId)
            ->sum('paid_amount') ?: 0;

        return [
            'total' => $total,
            'paid' => $paid,
            'unpaid' => $total - $paid
        ];
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        return view('pages.clients.create');
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_type' => 'required|in:client,commerciale,grossiste,special',
            'person_type' => 'required|in:physique,morale,special',
            'name' => 'required_if:person_type,physique|nullable|string|max:100',
            'entreprise_name' => 'required_if:person_type,morale|nullable|string|max:100',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('clients', 'phone')->whereNull('deleted_at')],
            'email' => ['nullable', 'email', Rule::unique('clients', 'email')->whereNull('deleted_at')],
            'address' => 'nullable|string|max:255',
            'cin' => 'nullable|string|max:20',
            'ice' => 'nullable|string|max:20',
            'rc' => 'nullable|string|max:20',
            'patente' => 'nullable|string|max:20',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ], $this->validationMessages());

        try {
            Client::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Client créé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified client with all details.
     */

    public function show($id)
    {
        $client = Client::with(['salesOrders', 'documents'])->findOrFail($id);

        $paymentMethods = \DB::table('sales_order_payments')
            ->join('sales_orders', 'sales_order_payments.order_id', '=', 'sales_orders.order_id')
            ->where('sales_orders.client_id', $id)
            ->select(
                'sales_order_payments.payment_method',
                \DB::raw('SUM(sales_order_payments.amount) as total'),
                \DB::raw('COUNT(*) as count')
            )
            ->groupBy('sales_order_payments.payment_method')
            ->get();

        return view('pages.clients.show', compact('client', 'paymentMethods'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('pages.clients.edit', compact('client'));
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $request->validate([
            'client_type' => 'required|in:client,commerciale,grossiste,special',
            'person_type' => 'required|in:physique,morale,special',
            'name' => 'required_if:person_type,physique|nullable|string|max:100',
            'entreprise_name' => 'required_if:person_type,morale|nullable|string|max:100',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('clients', 'phone')->ignore($id, 'client_id')->whereNull('deleted_at')],
            'email' => ['nullable', 'email', Rule::unique('clients', 'email')->ignore($id, 'client_id')->whereNull('deleted_at')],
            'address' => 'nullable|string|max:255',
            'cin' => 'nullable|string|max:20',
            'ice' => 'nullable|string|max:20',
            'rc' => 'nullable|string|max:20',
            'patente' => 'nullable|string|max:20',
            'credit_limit' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ], $this->validationMessages());

        try {
            $client->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Client mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display documents page for client.
     */
    public function documents($id)
    {
        $client = Client::with('documents')->findOrFail($id);
        return view('pages.clients.documents', compact('client'));
    }

    /**
     * Upload document for client.
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'document_type' => 'required|string|max:50',
            'document_name' => 'nullable|string|max:100',
            'document' => 'required|file|max:5120',
            'notes' => 'nullable|string',
        ]);

        try {
            $client = Client::findOrFail($id);

            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('client_documents/' . $client->client_id, $fileName, 'public');

            ClientDocument::create([
                'client_id' => $client->client_id,
                'document_type' => $request->document_type,
                'document_name' => $request->document_name ?: $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'notes' => $request->notes,
                'uploaded_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document téléchargé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete document from client.
     */
    public function deleteDocument($id, $documentId)
    {
        try {
            $document = ClientDocument::where('client_id', $id)
                ->where('document_id', $documentId)
                ->firstOrFail();

            Storage::disk('public')->delete($document->file_path);
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified client.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);

            // Check if client has sales orders
            $hasSalesOrders = $client->salesOrders()->exists();

            if ($hasSalesOrders) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce client ne peut pas être supprimé car il a des ventes.'
                ], 400);
            }

            // Delete documents
            foreach ($client->documents as $document) {
                Storage::disk('public')->delete($document->file_path);
            }

            $client->documents()->delete();
            $client->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Client supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get global statistics for dashboard.
     */
    public function getStatistics()
    {
        $totalClients = Client::count();
        $activeClients = Client::where('is_active', true)->count();
        $physiqueClients = Client::where('person_type', 'physique')->count();
        $moraleClients = Client::where('person_type', 'morale')->count();

        // Calculate receivables from unpaid orders
        $totalReceivables = DB::table('sales_orders')
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum(DB::raw('final_amount - paid_amount'));

        $totalSales = DB::table('sales_orders')
            ->sum('final_amount');

        $totalPaid = DB::table('sales_orders')
            ->sum('paid_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalClients,
                'active' => $activeClients,
                'physique' => $physiqueClients,
                'morale' => $moraleClients,
                'total_receivables' => number_format($totalReceivables, 2, ',', '.'),
                'total_sales' => number_format($totalSales, 2, ',', '.'),
                'total_paid' => number_format($totalPaid, 2, ',', '.'),
            ]
        ]);
    }

    /**
     * Distribute payment across unpaid orders
     */
    public function distributePayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,transfer,traite',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($request->payment_method === 'check') {
            $request->validate([
                'check_number' => 'required|string|max:100',
                'check_amount' => 'required|numeric|min:0.01',
                'bank_name' => 'required|string|max:255',
                'account_holder' => 'required|string|max:255',
                'issue_date' => 'required|date',
            ]);

            if ($request->check_amount != $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant du chèque doit être égal au montant du paiement'
                ], 422);
            }
        }

        if ($request->payment_method === 'traite') {
            $request->validate([
                'traite_number' => 'required|string|max:100',
                'traite_amount' => 'required|numeric|min:0.01',
                'traite_bank_name' => 'required|string|max:255',
                'drawee' => 'required|string|max:255',
                'traite_issue_date' => 'required|date',
                'due_date' => 'required|date',
            ]);

            if ($request->traite_amount != $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant de la traite doit être égal au montant du paiement'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);
            $amount = (float) $request->amount;

            $checkOrTraiteId = null;

            if ($request->payment_method === 'check') {
                $check = Check::create([
                    'check_number' => $request->check_number,
                    'check_type' => 'client',
                    'client_id' => $client->client_id,
                    'amount' => $request->check_amount,
                    'remaining_amount' => $request->check_amount,
                    'bank_name' => $request->bank_name,
                    'account_holder' => $request->account_holder,
                    'issue_date' => $request->issue_date,
                    'deposit_date' => $request->deposit_date,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);

                if ($request->hasFile('check_images')) {
                    foreach ($request->file('check_images') as $image) {
                        $path = $image->store('checks/' . $check->check_id, 'public');
                    }
                }

                $checkOrTraiteId = $check->check_id;
            } elseif ($request->payment_method === 'traite') {
                $traite = Traite::create([
                    'traite_number' => $request->traite_number,
                    'amount' => $request->traite_amount,
                    'client_id' => $client->client_id,
                    'issue_date' => $request->traite_issue_date,
                    'due_date' => $request->due_date,
                    'bank_name' => $request->traite_bank_name,
                    'drawee' => $request->drawee,
                    'drawee_address' => $request->drawee_address,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'created_by' => auth()->id(),
                ]);

                if ($request->hasFile('traite_document')) {
                    $document = $request->file('traite_document');
                    $path = $document->store('traites/' . $traite->traite_id, 'public');
                    $traite->update([
                        'document_path' => $path,
                        'original_filename' => $document->getClientOriginalName(),
                    ]);
                }

                $checkOrTraiteId = $traite->traite_id;
            }

            $unpaidOrders = SalesOrder::where('client_id', $client->client_id)
                ->whereIn('payment_status', ['pending', 'partial'])
                ->whereRaw('final_amount > paid_amount')
                ->orderBy('order_date', 'asc')
                ->orderBy('order_id', 'asc')
                ->get();

            if ($unpaidOrders->isEmpty()) {
                $paymentData = [
                    'client_id' => $client->client_id,
                    'payment_method' => $request->payment_method,
                    'amount' => $amount,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes ?: ($request->reference ? 'Réf: ' . $request->reference : 'Paiement direct sans commande'),
                ];

                $payment = SalesOrderPayment::create($paymentData);

                if ($request->payment_method === 'check') {
                    $check->update(['payment_id' => $payment->payment_id]);
                } elseif ($request->payment_method === 'traite') {
                    $traite->update(['payment_id' => $payment->payment_id]);
                }

                $previousBalance = $client->balance;
                $newBalance = $previousBalance + $amount;

                $client->balance = $newBalance;
                $client->save();

                $client->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance' => $newBalance,
                    'amount' => $amount,
                    'type' => 'payment_excess',
                    'reference_type' => 'payment',
                    'reference_id' => $payment->payment_id,
                    'description' => "Paiement direct de " . number_format($amount, 2, ',', '.') . " DH ajouté au solde client (aucune commande impayée)" .
                        ($request->payment_method === 'check' ? " - Chèque N°: " . $request->check_number :
                        ($request->payment_method === 'traite' ? " - Traite N°: " . $request->traite_number : "")),
                    'created_by' => auth()->id(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement de ' . number_format($amount, 2, ',', '.') . ' DH ajouté directement au solde client (aucune commande impayée).',
                    'direct_balance_add' => $amount,
                    'payment_id' => $payment->payment_id,
                    'client_balance' => $client->balance,
                    'client_balance_formatted' => $client->balance_formatted
                ]);
            }

            $totalUnpaid = $unpaidOrders->sum(function($order) {
                return $order->final_amount - $order->paid_amount;
            });

            $remaining = $amount;
            $paymentsCreated = [];
            $excessAmount = 0;

            if ($amount > $totalUnpaid) {
                $excessAmount = $amount - $totalUnpaid;
                $remaining = $totalUnpaid;
            }

            foreach ($unpaidOrders as $order) {
                if ($remaining <= 0) break;

                $unpaidAmount = $order->final_amount - $order->paid_amount;
                $paymentAmount = min($remaining, $unpaidAmount);

                if ($paymentAmount <= 0) continue;

                $paymentData = [
                    'client_id' => $client->client_id,
                    'payment_method' => $request->payment_method,
                    'amount' => $paymentAmount,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes ?: ($request->reference ? 'Réf: ' . $request->reference : 'Paiement distribué depuis gestion clients'),
                ];

                $payment = $order->payments()->create($paymentData);

                if ($request->payment_method === 'check') {
                    $check->update(['payment_id' => $payment->payment_id, 'order_id' => $order->order_id]);
                } elseif ($request->payment_method === 'traite') {
                    $traite->update(['payment_id' => $payment->payment_id, 'order_id' => $order->order_id]);
                }

                $oldPaidForOrder = $order->paid_amount;
                $order->paid_amount += $paymentAmount;

                if ($order->paid_amount >= $order->final_amount - 0.01) {
                    $order->payment_status = 'paid';
                } elseif ($order->paid_amount > 0) {
                    $order->payment_status = 'partial';
                }

                $order->save();

                // Update client balance: payment reduces debt
                $client->updateBalanceFromOrder($order, 'payment_added', $oldPaidForOrder);

                $unpaidAfter = $order->final_amount - $order->paid_amount;
                $creditReleased = $unpaidAmount - $unpaidAfter;
                if ($creditReleased > 0) {
                    $client->releaseCredit($creditReleased, $order, 'Paiement reçu sur commande');
                }

                $paymentsCreated[] = [
                    'payment_id' => $payment->payment_id,
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'amount' => $paymentAmount
                ];

                $remaining -= $paymentAmount;
            }

            if ($excessAmount > 0) {
                $excessPaymentData = [
                    'client_id' => $client->client_id,
                    'payment_method' => $request->payment_method,
                    'amount' => $excessAmount,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes ?: ($request->reference ? 'Réf: ' . $request->reference : 'Excédent de paiement') . ' - Excédent de ' . number_format($excessAmount, 2, ',', '.') . ' DH',
                ];

                $excessPayment = SalesOrderPayment::create($excessPaymentData);

                // Excess isn't tied to a single order, so link straight to the client (no order_id)
                // so a later bounce falls back to a direct balance debit for the full amount.
                if ($request->payment_method === 'check') {
                    $check->update(['payment_id' => $excessPayment->payment_id, 'order_id' => null]);
                } elseif ($request->payment_method === 'traite') {
                    $traite->update(['payment_id' => $excessPayment->payment_id, 'order_id' => null]);
                }

                $previousBalance = $client->balance;
                $newBalance = $previousBalance + $excessAmount;

                $client->balance = $newBalance;
                $client->save();

                $client->balanceHistory()->create([
                    'previous_balance' => $previousBalance,
                    'new_balance' => $newBalance,
                    'amount' => $excessAmount,
                    'type' => 'payment_excess',
                    'reference_type' => $request->payment_method,
                    'reference_id' => $excessPayment->payment_id,
                    'description' => "Excédent de paiement de " . number_format($excessAmount, 2, ',', '.') . " DH ajouté au solde client",
                    'created_by' => auth()->id(),
                ]);

                $paymentsCreated[] = [
                    'payment_id' => $excessPayment->payment_id,
                    'order_id' => null,
                    'order_number' => 'Sans commande (Solde)',
                    'amount' => $excessAmount,
                    'is_excess' => true
                ];
            }

            DB::commit();

            $message = 'Paiement de ' . number_format($amount, 2, ',', '.') . ' DH traité. ';
            $message .= count(array_filter($paymentsCreated, function($payment) {
                return !isset($payment['is_excess']);
            })) . ' commande(s) payée(s).';

            if ($excessAmount > 0) {
                $message .= ' Excédent de ' . number_format($excessAmount, 2, ',', '.') . ' DH ajouté au solde client.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'payments' => $paymentsCreated,
                'excess_added' => $excessAmount,
                'client_balance' => $client->balance,
                'client_balance_formatted' => $client->balance_formatted
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check client credit before order creation.
     */
    public function checkCredit(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        // Calculate unpaid amount from previous orders
        $creditUsed = DB::table('sales_orders')
            ->where('client_id', $id)
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum(DB::raw('final_amount - paid_amount'));

        $orderTotal = $request->get('order_total', 0);
        $creditAvailable = $client->credit_limit - $creditUsed;
        $exceedsBy = max(0, $orderTotal - $creditAvailable);

        return response()->json([
            'success' => true,
            'can_proceed' => $orderTotal <= $creditAvailable,
            'client_name' => $client->display_name,
            'credit_limit' => $client->credit_limit,
            'credit_used' => $creditUsed,
            'credit_available' => $creditAvailable,
            'order_total' => $orderTotal,
            'exceeds_by' => $exceedsBy
        ]);
    }

    /**
     * Get client balance for advance payment
     */
    public function getClientBalance($id)
    {
        try {
            $client = Client::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $client->balance,
                    'available_advance' => $client->available_advance,
                    'balance_formatted' => $client->advance_formatted,
                    'has_advance' => $client->has_advance,
                    'currency' => 'DH'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client statistics for a specific client.
     */
    public function getClientStatistics($id)
    {
        $client = Client::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $client->purchase_summary
        ]);
    }

    public function getCreditStatus($id)
    {
        try {
            $client = Client::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    // Credit info
                    'has_credit' => $client->has_credit,
                    'credit_limit' => $client->credit_limit,
                    'credit_usage' => $client->credit_usage,
                    'available_credit' => $client->credit_available,
                    'credit_formatted' => $client->credit_formatted,
                    'credit_percentage' => $client->credit_percentage,

                    // Balance info
                    'balance' => $client->balance,
                    'balance_formatted' => $client->balance_formatted,
                    'balance_status' => $client->balance_status,

                    // Advance info (positive balance)
                    'has_advance' => $client->has_advance,
                    'available_advance' => $client->available_advance,
                    'advance_formatted' => $client->advance_formatted,

                    // Debt info (negative balance)
                    'has_debt' => $client->has_debt,
                    'total_debt' => $client->total_debt,
                    'debt_formatted' => $client->debt_formatted,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clients for Select2 dropdown.
     */
    public function getClientsSelect2(Request $request)
    {
        $search = $request->get('search');

        $clients = Client::where('is_active', true)
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('entreprise_name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('cin', 'like', "%{$search}%")
                          ->orWhere('ice', 'like', "%{$search}%");
                }
            })
            ->select(
                'client_id as id',
                DB::raw("CASE
                    WHEN person_type = 'morale' THEN entreprise_name
                    ELSE name
                END as text"),
                'person_type',
                'cin',
                'ice',
                'phone'
            )
            ->limit(10)
            ->get()
            ->map(function($client) {
                $client->text = $client->text . ' (' .
                    ($client->person_type == 'morale' ? 'ICE: ' . ($client->ice ?? 'N/A') : 'CIN: ' . ($client->cin ?? 'N/A')) .
                    ') - Tél: ' . $client->phone;
                return $client;
            });

        return response()->json([
            'results' => $clients
        ]);
    }

    public function list()
    {
        $clients = Client::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'data' => $clients->map(function($client) {
                return [
                    'client_id' => $client->client_id,
                    'display_name' => $client->display_name,
                    'phone' => $client->phone,
                    'client_type' => $client->client_type,
                    'client_type_label' => $client->client_type_label,
                ];
            })
        ]);
    }

    /**
     * Export clients to Excel/CSV.
     */
    public function export(Request $request)
    {
        $clients = Client::with(['salesOrders'])->get();

        // You can use Maatwebsite\Excel package here

        return response()->json([
            'success' => true,
            'message' => 'Export en cours de développement'
        ]);
    }

    /**
     * Get client balance history.
     */
    public function getBalanceHistory($id)
    {
        $client = Client::findOrFail($id);
        $history = $client->balanceHistory()->with('creator')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    public function addBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);
            $amount = $request->amount;
            $previousBalance = $client->balance;
            $newBalance = $previousBalance + $amount;

            // Update client balance
            $client->balance = $newBalance;
            $client->save();

            // Create balance history record
            $client->balanceHistory()->create([
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'amount' => $amount,
                'type' => 'manual_adjustment',
                'reference_type' => 'manual',
                'reference_id' => null,
                'description' => $request->reason ?: 'Ajout manuel de solde',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solde ajouté avec succès! Nouveau solde: ' . number_format($newBalance, 2, ',', '.') . ' DH'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
