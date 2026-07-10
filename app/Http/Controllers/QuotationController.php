<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Client;
use App\Models\Product;
use App\Models\RawMaterial;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_sales_quotations')->only(['index', 'show', 'getStatistics', 'getProductsByType', 'getRawMaterialsList', 'getProductDetails', 'getRawMaterialDetails', 'generatePdf']);
        $this->middleware('can:create_sales_quotations')->only(['create', 'store', 'duplicate']);
        $this->middleware('can:edit_sales_quotations')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('can:delete_sales_quotations')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Quotation::with(['client'])->select('quotations.*');

            if ($request->filled('date_from')) {
                $query->whereDate('quote_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('quote_date', '<=', $request->date_to);
            }

            $totalAmount = (clone $query)->sum('final_amount');

            $quotations = $query;

            return DataTables::of($quotations)
                ->with(['total_amount' => $totalAmount])
                ->addIndexColumn()
                ->addColumn('action', function($quote) {
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= ' <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="'.route('sales.quotations.show', $quote->quote_id).'">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';

                    // Print action
                    $btn .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openPdfOptions('.$quote->quote_id.')">
                                <i class="fas fa-print me-2"></i>Imprimer
                            </a></li>';

                    // Duplicate quote
                    $btn .= '<li><a class="dropdown-item" href="'.route('sales.quotations.duplicate', $quote->quote_id).'">
                                <i class="fas fa-copy me-2"></i>Modifier</a></li>';

                    $btn .= '<li><hr class="dropdown-divider"></li>';
                    $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$quote->quote_id.'" data-number="'.$quote->quote_number.'">
                                <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->addColumn('client_name', function($row){
                    return $row->client->display_name;
                })
                ->addColumn('status_badge', function($row){
                    $badges = [
                        'draft' => 'secondary',
                        'sent' => 'primary',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'warning',
                    ];
                    $color = $badges[$row->status] ?? 'secondary';
                    $labels = [
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyé',
                        'accepted' => 'Accepté',
                        'rejected' => 'Refusé',
                        'expired' => 'Expiré',
                    ];
                    $label = $labels[$row->status] ?? $row->status;
                    return '<span class="badge bg-'.$color.'">'.$label.'</span>';
                })
                ->editColumn('final_amount', function($row){
                    return number_format($row->final_amount, 2, ',', '.') . ' DH';
                })
                ->editColumn('quote_date', function($row){
                    return $row->quote_date->format('d/m/Y');
                })
                ->editColumn('valid_until', function($row){
                    return $row->valid_until ? $row->valid_until->format('d/m/Y') : '-';
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('pages.sales.quotations.index');
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with(['familles'])->get();
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        // Generate quote number with format YYYY-001
        $nextQuoteNumber = Quotation::generateQuoteNumber();

        return view('pages.sales.quotations.create', compact('clients', 'products', 'rawMaterials', 'nextQuoteNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quote_number' => 'required|unique:quotations|max:50',
            'client_id' => 'required|exists:clients,client_id',
            'quote_date' => 'required|date',
            'valid_until' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:raw_material,production,decoupage,finale',
            'items.*.item_id' => 'required',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
            'observation' => 'nullable|string',
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

                $itemsData[] = [
                    'item_type' => $itemData['type'],
                    'item_id' => $itemData['item_id'],
                    'item_name' => $itemData['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'family_id' => $itemData['family_id'] ?? null,
                    'family_name' => $itemData['family_name'] ?? null,
                ];
            }

            $discount = (float) ($request->discount ?? 0);
            $finalAmount = $totalAmount - $discount;

            $quotation = Quotation::create([
                'quote_number' => $request->quote_number,
                'client_id' => $request->client_id,
                'quote_date' => $request->quote_date,
                'valid_until' => $request->valid_until,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'status' => $request->status,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'observation' => $request->observation,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $itemData) {
                $quotation->items()->create($itemData);
            }

            DB::commit();

            \Log::info('Quotation created successfully', [
                'quote_id' => $quotation->quote_id,
                'quote_number' => $quotation->quote_number,
                'client_id' => $quotation->client_id,
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Devis créé avec succès!',
                'quote_id' => $quotation->quote_id,
                'quote_number' => $quotation->quote_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Quotation creation error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du devis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);
        $clients = Client::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with(['familles'])->get();
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        return view('pages.sales.quotations.edit', compact('quotation', 'clients', 'products', 'rawMaterials'));
    }

    public function show($id)
    {
        $quotation = Quotation::with(['client', 'creator', 'items'])->findOrFail($id);

        return view('pages.sales.quotations.show', [
            'quotation' => $quotation,
            'numberToFrench' => function($number) {
                return $this->numberToFrench($number);
            }
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,client_id',
            'quote_date' => 'required|date',
            'valid_until' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:raw_material,production,decoupage,finale',
            'items.*.item_id' => 'required',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.family_id' => 'nullable',
            'items.*.family_name' => 'nullable',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
            'observation' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $quotation = Quotation::findOrFail($id);

            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $index => $itemData) {
                $quantity = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $itemTotal = $quantity * $unitPrice;
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'item_type' => $itemData['type'],
                    'item_id' => $itemData['item_id'],
                    'item_name' => $itemData['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'family_id' => $itemData['family_id'] ?? null,
                    'family_name' => $itemData['family_name'] ?? null,
                ];
            }

            $discount = (float) ($request->discount ?? 0);
            $finalAmount = $totalAmount - $discount;

            // Update quotation
            $quotation->update([
                'client_id' => $request->client_id,
                'quote_date' => $request->quote_date,
                'valid_until' => $request->valid_until,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'status' => $request->status,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'observation' => $request->observation,
            ]);

            // Delete old items and create new ones
            $quotation->items()->delete();
            foreach ($itemsData as $itemData) {
                $quotation->items()->create($itemData);
            }

            DB::commit();

            \Log::info('Quotation updated successfully', [
                'quote_id' => $quotation->quote_id,
                'quote_number' => $quotation->quote_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Devis mis à jour avec succès!',
                'quote_id' => $quotation->quote_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Quotation update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du devis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
        ]);

        try {
            $quotation = Quotation::findOrFail($id);
            $quotation->status = $request->status;
            $quotation->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut du devis mis à jour avec succès!',
                'status' => $quotation->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicate($id)
    {
        $originalQuote = Quotation::with('items')->findOrFail($id);

        // Create new quotation based on the original
        $newQuote = $originalQuote->replicate();
        $newQuote->quote_number = Quotation::generateQuoteNumber();
        $newQuote->status = 'draft';
        $newQuote->created_by = Auth::id();
        $newQuote->save();

        // Replicate items
        foreach ($originalQuote->items as $item) {
            $newItem = $item->replicate();
            $newItem->quote_id = $newQuote->quote_id;
            $newItem->save();
        }

        return redirect()->route('sales.quotations.edit', $newQuote->quote_id)
            ->with('success', 'Devis dupliqué avec succès!');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $quotation = Quotation::findOrFail($id);

            // Delete related records
            $quotation->items()->delete();
            $quotation->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Devis supprimé avec succès!'
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
        $totalQuotes = Quotation::count();
        $draftQuotes = Quotation::where('status', 'draft')->count();
        $sentQuotes = Quotation::where('status', 'sent')->count();
        $acceptedQuotes = Quotation::where('status', 'accepted')->sum('final_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalQuotes,
                'draft' => $draftQuotes,
                'sent' => $sentQuotes,
                'accepted_amount' => $acceptedQuotes
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
            'familles' => $product->familles->map(function($famille) {
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
                ->map(function($product) {
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
                        $data['families'] = $product->familles->map(function($famille) use ($product) {
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
                ->map(function($material) {
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
            $quotation = Quotation::with(['client', 'items'])->findOrFail($id);
            $showPrices = $request->query('show_prices', 1);
            $showLogo = $request->query('show_logo', 1);
            $displayType = $request->query('display_type', 'unite');

            $totalQuantity = $quotation->items->sum('quantity');

            $totalVolume = 0;
            if ($displayType === 'volume') {
                foreach ($quotation->items as $item) {
                    if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                        $product = \App\Models\Product::find($item->item_id);
                        if ($product && $product->volume) {
                            $totalVolume += $item->quantity * $product->volume;
                        }
                    }
                }
            }

            $enteteBase64 = '';
            // $entetePath = public_path('assets/images/logos/entete.svg');

            // if (file_exists($entetePath)) {
            //     $enteteContent = file_get_contents($entetePath);
            //     $enteteBase64 = 'data:image/svg+xml;base64,' . base64_encode($enteteContent);
            // } else {
                $jpgPath = public_path('assets/images/logos/entete.svg');
                $pngPath = public_path('assets/images/logos/entete.png');

                if (file_exists($jpgPath)) {
                    $enteteContent = file_get_contents($jpgPath);
                    $enteteBase64 = 'data:image/jpeg;base64,' . base64_encode($enteteContent);
                }
            // }

            $data = [
                'quotation' => $quotation,
                'client' => $quotation->client,
                'items' => $quotation->items,
                'showPrices' => (bool) $showPrices,
                'showLogo' => (bool) $showLogo,
                'displayType' => $displayType,
                'totalVolume' => $totalVolume,
                'date' => now()->format('d/m/Y'),
                'time' => now()->format('H:i'),
                'quote_number_formatted' => $quotation->quote_number,
                'username' => auth()->user()->name ?? auth()->user()->username,
                'totalQuantity' => $totalQuantity,
                'enteteBase64' => $enteteBase64,
                'numberToFrench' => function($number) {
                        return $this->numberToFrench($number);
                    }
            ];

            $pdf = Pdf::loadView('pdf.quotation', $data);
            $pdf->setPaper('A4', 'portrait');

            // Stream the PDF inline instead of downloading, so it can be printed directly
            return $pdf->stream('devis-' . str_replace('/', '-', $quotation->quote_number) . '.pdf');

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

        $convert = function($num) use (&$convert, $units, $tens) {
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
