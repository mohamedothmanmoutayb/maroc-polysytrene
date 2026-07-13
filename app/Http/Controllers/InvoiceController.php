<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_sales_invoices')->only(['index', 'show', 'getStatistics', 'generatePdf', 'getProductsByType', 'getRawMaterialsList', 'getProductDetails', 'getRawMaterialDetails', 'generateNextInvoiceNumber', 'getSalesOrderDetails', 'getClientSalesOrders', 'getMultipleSalesOrders', 'getSalesByIds']);
        $this->middleware('can:create_sales_invoices')->only(['create', 'store', 'duplicate']);
        $this->middleware('can:edit_sales_invoices')->only(['edit', 'update']);
        $this->middleware('can:delete_sales_invoices')->only(['destroy']);
    }

    public function index(Request $request)
    {

    
        if ($request->ajax()) {
            $query = Invoice::with(['client'])->select('invoices.*');

            if ($request->filled('date_from')) {
                $query->whereDate('invoice_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('invoice_date', '<=', $request->date_to);
            }

            $totalAmount = (clone $query)->sum('final_amount');

            $invoices = $query;

            return DataTables::of($invoices)
                ->with(['total_amount' => $totalAmount])
                ->addIndexColumn()
                ->addColumn('action', function ($invoice) {
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= ' <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="' . route('sales.invoices.show', $invoice->invoice_id) . '">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    $btn .= '<li><a class="dropdown-item" href="' . route('sales.invoices.edit', $invoice->invoice_id) . '">
                                <i class="fas fa-edit me-2"></i>Modifier</a></li>';

                    // Generate PDF action
                    $btn .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openPdfOptions(' . $invoice->invoice_id . ')">
                                <i class="fas fa-file-pdf me-2"></i>Générer PDF
                            </a></li>';

                    $btn .= '<li><hr class="dropdown-divider"></li>';
                    $btn .= '<li><a class="dropdown-item delete" href="#" data-id="' . $invoice->invoice_id . '" data-number="' . $invoice->invoice_number . '">
                                <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->addColumn('client_name', function ($row) {
                    return $row->client->display_name;
                })
                ->editColumn('final_amount', function ($row) {
                    return number_format($row->final_amount, 2, ',', '.') . ' DH';
                })
                ->editColumn('invoice_date', function ($row) {
                    return $row->invoice_date->format('d/m/Y');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.sales.invoices.index');
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with(['familles'])->get();
        $rawMaterials = RawMaterial::where('is_active', true)->get();
        $salesOrders = SalesOrder::with(['client', 'items'])
            ->orderBy('order_date', 'desc')
            ->get();

        $nextInvoiceNumber = Invoice::generateInvoiceNumber();

        return view('pages.sales.invoices.create', compact(
            'clients',
            'products',
            'rawMaterials',
            'nextInvoiceNumber',
            'salesOrders'
        ));
    }

    /**
     * Get sales orders for a specific client (for multiple selection)
     */
    public function getClientSalesOrders(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,client_id'
        ]);

        try {
            $salesOrders = SalesOrder::with(['items', 'invoiceItems'])
                ->where('client_id', $request->client_id)
                ->orderBy('order_date', 'desc')
                ->get()
                ->filter(function ($order) {
                    // Only show ventes for which no invoice has been created yet
                    return $order->invoiceItems->isEmpty();
                })
                ->map(function ($order) {
                    return [
                        'order_id' => $order->order_id,
                        'order_number' => $order->order_number,
                        'order_date' => $order->order_date,
                        'order_date_formatted' => $order->order_date->format('d/m/Y'),
                        'total_amount' => $order->total_amount,
                        'final_amount' => $order->final_amount,
                        'items_count' => $order->items->count(),
                        'remaining_items_count' => $order->items->count(),
                        'remaining_quantity' => $order->items->sum('quantity'),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $salesOrders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des ventes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get multiple sales orders details to load into invoice
     */
    public function getMultipleSalesOrders(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:sales_orders,order_id'
        ]);

        try {
            $orders = SalesOrder::with(['client', 'items'])
                ->whereIn('order_id', $request->order_ids)
                ->get();

            $result = [];
            foreach ($orders as $order) {
                $items = [];
                foreach ($order->items as $item) {
                    $items[] = [
                        'type' => $item->item_type,
                        'item_id' => $item->item_id,
                        'name' => $item->item_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'family_id' => $item->family_id,
                        'family_name' => $item->family_name,
                    ];
                }

                $result[] = [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'order_date' => $order->order_date,
                    'order_date_formatted' => $order->order_date->format('d/m/Y'),
                    'client_id' => $order->client_id,
                    'client_name' => $order->client->display_name,
                    'items' => $items,
                    'total_amount' => $order->total_amount,
                    'final_amount' => $order->final_amount,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des ventes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales orders by IDs
     */
    public function getSalesByIds(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:sales_orders,order_id'
        ]);

        try {
            $orders = SalesOrder::whereIn('order_id', $request->order_ids)
                ->get()
                ->map(function ($order) {
                    return [
                        'order_id' => $order->order_id,
                        'order_number' => $order->order_number,
                        'order_date_formatted' => $order->order_date->format('d/m/Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales order details to load into invoice
     */
    public function getSalesOrderDetails($orderId)
    {
        try {
            $order = SalesOrder::with(['client', 'items'])->findOrFail($orderId);

            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    'type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'family_id' => $item->family_id,
                    'family_name' => $item->family_name,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'client_id' => $order->client_id,
                    'client_name' => $order->client->display_name,
                    'items' => $items,
                    'total_amount' => $order->total_amount,
                    'final_amount' => $order->final_amount,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next invoice number via AJAX
     */
    public function generateNextInvoiceNumber()
    {
        try {
            $nextNumber = Invoice::generateInvoiceNumber();

            return response()->json([
                'success' => true,
                'invoice_number' => $nextNumber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du numéro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|unique:invoices|max:50',
            'client_id' => 'required|exists:clients,client_id',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:raw_material,production,decoupage,finale',
            'items.*.item_id' => 'required',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'items.*.source_sales' => 'nullable|array',
            'items.*.source_sales.*' => 'numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $index => $itemData) {
                $quantity = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $sourceSales = $this->validSourceSales($itemData['source_sales'] ?? []);

                $itemsData[] = [
                    'data' => [
                        'item_type' => $itemData['type'],
                        'item_id' => $itemData['item_id'],
                        'item_name' => $itemData['name'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $itemTotal,
                        'family_id' => $itemData['family_id'] ?? null,
                        'family_name' => $itemData['family_name'] ?? null,
                        'source_sale_id' => $sourceSales ? array_key_first($sourceSales) : null,
                    ],
                    'source_sales' => $sourceSales,
                ];
            }

            $discount = (float) ($request->discount ?? 0);
            $finalAmount = $totalAmount - $discount;

            $invoice = Invoice::create([
                'invoice_number' => $request->invoice_number,
                'client_id' => $request->client_id,
                'invoice_date' => $request->invoice_date,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'amount_paid' => 0,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $itemData) {
                $item = $invoice->items()->create($itemData['data']);
                $this->attachItemSourceSales($item, $itemData['source_sales']);
            }

            DB::commit();

            \Log::info('Invoice created successfully', [
                'invoice_id' => $invoice->invoice_id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'items_count' => count($itemsData),
                'sale_items_count' => collect($itemsData)->where(fn ($i) => !empty($i['source_sales']))->count(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Facture créée avec succès!',
                'invoice_id' => $invoice->invoice_id,
                'invoice_number' => $invoice->invoice_number,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Invoice creation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with(['client', 'creator', 'items'])->findOrFail($id);

        return view('pages.sales.invoices.show', [
            'invoice' => $invoice,
            'numberToFrench' => function ($number) {
                return $this->numberToFrench($number);
            }
        ]);
    }

    public function edit($id)
    {
        $invoice = Invoice::with(['items' => function ($query) {
            $query->with('sourceSales');
        }])->findOrFail($id);

        $clients = Client::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with(['familles'])->get();
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        return view('pages.sales.invoices.edit', compact('invoice', 'clients', 'products', 'rawMaterials'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'invoice_number' => 'required|max:50|unique:invoices,invoice_number,' . $id . ',invoice_id',
            'client_id' => 'required|exists:clients,client_id',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:raw_material,production,decoupage,finale',
            'items.*.item_id' => 'required',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'items.*.source_sales' => 'nullable|array',
            'items.*.source_sales.*' => 'numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($id);

            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $index => $itemData) {
                $quantity = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $sourceSales = $this->validSourceSales($itemData['source_sales'] ?? []);

                $itemsData[] = [
                    'data' => [
                        'item_type' => $itemData['type'],
                        'item_id' => $itemData['item_id'],
                        'item_name' => $itemData['name'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $itemTotal,
                        'family_id' => $itemData['family_id'] ?? null,
                        'family_name' => $itemData['family_name'] ?? null,
                        'source_sale_id' => $sourceSales ? array_key_first($sourceSales) : null,
                    ],
                    'source_sales' => $sourceSales,
                ];
            }

            $discount = (float) ($request->discount ?? 0);
            $finalAmount = $totalAmount - $discount;

            // Update invoice
            $invoice->update([
                'invoice_number' => $request->invoice_number,
                'client_id' => $request->client_id,
                'invoice_date' => $request->invoice_date,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();
            foreach ($itemsData as $itemData) {
                $item = $invoice->items()->create($itemData['data']);
                $this->attachItemSourceSales($item, $itemData['source_sales']);
            }

            DB::commit();

            \Log::info('Invoice updated successfully', [
                'invoice_id' => $invoice->invoice_id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'items_count' => count($itemsData),
                'sale_items_count' => collect($itemsData)->where(fn ($i) => !empty($i['source_sales']))->count(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Facture mise à jour avec succès!',
                'invoice_id' => $invoice->invoice_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Invoice update error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la facture: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Keep only entries whose key is a real sales_orders id, keyed by int order_id.
     */
    private function validSourceSales(array $sourceSales)
    {
        if (empty($sourceSales)) {
            return [];
        }

        $validIds = SalesOrder::whereIn('order_id', array_keys($sourceSales))
            ->pluck('order_id')
            ->all();

        $result = [];
        foreach ($sourceSales as $orderId => $quantity) {
            if (in_array((int) $orderId, $validIds)) {
                $result[(int) $orderId] = (float) $quantity;
            }
        }

        return $result;
    }

    /**
     * Record which vente(s) contributed to an invoice line, and how much of
     * its quantity came from each (a line can have more than one source when
     * identical products loaded from different ventes get merged into one row).
     */
    private function attachItemSourceSales(InvoiceItem $item, array $sourceSales)
    {
        if (empty($sourceSales)) {
            return;
        }

        $pivotData = [];
        foreach ($sourceSales as $orderId => $quantity) {
            $pivotData[$orderId] = ['quantity' => $quantity];
        }

        $item->sourceSales()->attach($pivotData);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($id);

            // Delete related records
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Facture supprimée avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicate($id)
    {
        $originalInvoice = Invoice::with('items')->findOrFail($id);

        // Create new invoice based on the original
        $newInvoice = $originalInvoice->replicate();
        $newInvoice->invoice_number = Invoice::generateInvoiceNumber();
        $newInvoice->amount_paid = 0;
        $newInvoice->created_by = Auth::id();
        $newInvoice->save();

        // Replicate items
        foreach ($originalInvoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $newInvoice->invoice_id;
            $newItem->save();

            foreach ($item->sourceSales as $sourceSale) {
                $newItem->sourceSales()->attach($sourceSale->order_id, ['quantity' => $sourceSale->pivot->quantity]);
            }
        }

        return redirect()->route('sales.invoices.edit', $newInvoice->invoice_id)
            ->with('success', 'Facture dupliquée avec succès!');
    }

    public function getStatistics()
    {
        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::whereColumn('amount_paid', '>=', 'final_amount')->count();
        $unpaidInvoices = Invoice::whereColumn('amount_paid', '<', 'final_amount')->count();
        $totalPaidAmount = Invoice::sum('amount_paid');
        $pendingAmount = Invoice::sum(DB::raw('final_amount - amount_paid'));

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalInvoices,
                'paid' => $paidInvoices,
                'unpaid' => $unpaidInvoices,
                'amount_paid' => $totalPaidAmount,
                'pending_amount' => $pendingAmount,
            ]
        ]);
    }

    public function getProductDetails($id)
    {
        $product = Product::with(['familles'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'name' => $product->product_name,
            'code' => $product->product_code,
            'price' => $product->price_client,
            'type' => $product->product_type,
            'has_familles' => $product->familles->count() > 0,
            'familles' => $product->familles->map(function ($famille) {
                return [
                    'id' => $famille->famille_id,
                    'name' => $famille->famille_name,
                    'code' => $famille->famille_code,
                    'prix_client' => $famille->pivot->prix_client ?? null,
                ];
            })
        ]);
    }

    public function getRawMaterialDetails($id)
    {
        $material = RawMaterial::findOrFail($id);

        return response()->json([
            'success' => true,
            'name' => $material->material_name,
            'code' => $material->material_code,
            'price' => 0,
            'unit' => $material->unit_of_measure,
        ]);
    }

    public function getProductsByType($type)
    {
        try {
            $products = Product::where('is_active', true)
                ->where('product_type', $type)
                ->with(['familles'])
                ->get()
                ->map(function ($product) {
                    $data = [
                        'id' => $product->product_id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'price' => $product->price_client,
                        'price_revendeur' => $product->price_revendeur,
                        'price_commercial' => $product->price_commercial,
                        'price_special' => $product->price_special,
                        'has_families' => $product->familles->count() > 0,
                    ];

                    if ($product->familles->count() > 0) {
                        $data['families'] = $product->familles->map(function ($famille) use ($product) {
                            return [
                                'id' => $famille->famille_id,
                                'name' => $famille->famille_name,
                                'code' => $famille->famille_code,
                                'prix_client' => $famille->pivot->prix_client ?? $product->price_client,
                                'prix_grossiste' => $famille->pivot->prix_grossiste ?? $product->price_revendeur ?? $product->price_client,
                                'prix_commercial' => $famille->pivot->prix_commercial ?? $product->price_commercial ?? $product->price_client,
                                'prix_special' => $famille->pivot->prix_special ?? $product->price_special ?? $product->price_client,
                                'quantity_per_unit' => $famille->pivot->quantity_per_unit ?? 1,
                            ];
                        });
                    }

                    return $data;
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRawMaterialsList()
    {
        try {
            $materials = RawMaterial::where('is_active', true)
                ->get()
                ->map(function ($material) {
                    return [
                        'id' => $material->material_id,
                        'name' => $material->material_name,
                        'code' => $material->material_code,
                        'price' => 0,
                        'unit' => $material->unit_of_measure,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generatePdf($id, Request $request)
    {
        try {
            $invoice = Invoice::with(['client', 'items'])->findOrFail($id);
            $showPrices = $request->query('show_prices', 1);
            $showLogo = $request->query('show_logo', 1);
            $showCacher = $request->query('show_cacher', 1);
            $displayType = $request->query('display_type', 'unite');

            // Check if it's a print request (direct print without download)
            $isPrint = $request->query('print', 0);

            $totalVolume = 0;
            if ($displayType === 'volume') {
                foreach ($invoice->items as $item) {
                    if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                        $product = \App\Models\Product::find($item->item_id);
                        if ($product && $product->volume) {
                            $totalVolume += $item->quantity * $product->volume;
                        }
                    }
                }
            }

            $enteteBase64 = '';
            $entetePath = public_path('assets/images/logos/entete.svg');

            if (file_exists($entetePath)) {
                $enteteContent = file_get_contents($entetePath);
                $enteteBase64 = 'data:image/jpeg;base64,' . base64_encode($enteteContent);
            }

            $cacherBase64 = '';
            $cacherPath = public_path('assets/images/logos/cacher.png');

            if (file_exists($cacherPath)) {
                $cacherContent = file_get_contents($cacherPath);
                $cacherBase64 = 'data:image/png;base64,' . base64_encode($cacherContent);
            }

            $data = [
                'invoice' => $invoice,
                'client' => $invoice->client,
                'items' => $invoice->items,
                'showPrices' => (bool) $showPrices,
                'showLogo' => (bool) $showLogo,
                'showCacher' => (bool) $showCacher,
                'displayType' => $displayType,
                'totalVolume' => $totalVolume,
                'date' => now()->format('d/m/Y'),
                'time' => now()->format('H:i'),
                'invoice_number_formatted' => $invoice->invoice_number,
                'username' => auth()->user()->name ?? auth()->user()->username,
                'enteteBase64' => $enteteBase64,
                'cacherBase64' => $cacherBase64,
                'numberToFrench' => function ($number) {
                    return $this->numberToFrench($number);
                }
            ];

            $pdf = Pdf::loadView('pdf.invoice', $data);
            $pdf->setPaper('A4', 'portrait');

            $pdf->setOptions([
                'defaultFont' => 'dejavu sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 96,
                'enable_php' => true,
                'enable_javascript' => true,
            ]);


            $filename = 'facture-' . str_replace('/', '-', $invoice->invoice_number) . '.pdf';

            if ($isPrint) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert number to French words
     */
    private function numberToFrench($number)
    {
        $number = (float) $number;

        $negative = $number < 0;
        $number = abs($number);

        $integer = floor($number);
        $decimal = round(($number - $integer) * 100);

        $units = ['', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF', 'DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE', 'DIX-SEPT', 'DIX-HUIT', 'DIX-NEUF'];
        $tens = ['', '', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE', 'SOIXANTE', 'SOIXANTE-DIX', 'QUATRE-VINGTS', 'QUATRE-VINGT-DIX'];

        $convert = function ($num) use (&$convert, $units, $tens) {
            if ($num < 20) return $units[$num];

            if ($num < 100) {
                $ten = floor($num / 10);
                $unit = $num % 10;

                if ($ten == 7) {
                    return 'SOIXANTE' . ($unit == 0 ? '' : '-' . $units[10 + $unit]);
                }
                if ($ten == 9) {
                    return 'QUATRE-VINGT' . ($unit == 0 ? '' : '-' . $units[10 + $unit]);
                }

                if ($unit == 0) {
                    return $tens[$ten];
                } elseif ($unit == 1) {
                    return $tens[$ten] . ' ET UN';
                } else {
                    return $tens[$ten] . '-' . $units[$unit];
                }
            }

            if ($num < 1000) {
                $hundred = floor($num / 100);
                $remainder = $num % 100;

                $hundredText = ($hundred == 1 ? 'CENT' : $units[$hundred] . ' CENT');
                if ($hundred > 1 && $remainder == 0) $hundredText .= 'S';

                return $hundredText . ($remainder > 0 ? ' ' . $convert($remainder) : '');
            }

            $divisors = [
                1000000000 => 'MILLIARD',
                1000000 => 'MILLION',
                1000 => 'MILLE'
            ];

            foreach ($divisors as $divisor => $word) {
                if ($num >= $divisor) {
                    $quotient = floor($num / $divisor);
                    $remainder = $num % $divisor;

                    $quotientText = $quotient == 1 ? 'UN' : $convert($quotient);

                    if ($word == 'MILLE') {
                        $word = 'MILLE';
                    } else {
                        if ($quotient > 1) {
                            $word .= 'S';
                        }
                    }

                    $result = $quotientText . ' ' . $word;

                    if ($remainder > 0) {
                        $result .= ' ' . $convert($remainder);
                    }

                    return $result;
                }
            }

            return '';
        };

        $result = $convert($integer);

        if ($negative) {
            $result = 'MOINS ' . $result;
        }

        if ($decimal > 0) {
            $result .= ' ET ' . $convert($decimal) . ' CENTIME' . ($decimal > 1 ? 'S' : '');
        }

        return trim($result);
    }
}
