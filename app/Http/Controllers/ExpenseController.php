<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_expenses')->only(['index', 'show', 'getStatistics']);
        $this->middleware('can:create_expenses')->only(['create', 'store']);
        $this->middleware('can:edit_expenses')->only(['edit', 'update']);
        $this->middleware('can:delete_expenses')->only(['destroy']);
        $this->middleware('can:approve_expenses')->only(['approve']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = Expense::with(['category', 'recorder', 'approver'])
                ->select('expenses.*')
                ->when($request->filled('category_id'), function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                })
                ->when($request->filled('payment_method'), function ($query) use ($request) {
                    return $query->where('payment_method', $request->payment_method);
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    return $request->status === 'pending'
                        ? $query->whereNull('approved_by')
                        : $query->whereNotNull('approved_by');
                })
                ->when($request->filled('date_range'), function ($query) use ($request) {
                    $dates = array_map('trim', explode(' - ', $request->date_range));

                    if (count($dates) == 2) {
                        $start = Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay();
                        $end = Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay();

                        return $query->whereBetween('expense_date', [$start, $end]);
                    }

                    return $query->whereDate('expense_date', Carbon::createFromFormat('d/m/Y', $dates[0]));
                })
                ->orderByDesc('expense_date')
                ->orderByDesc('expense_id');

            return DataTables::of($expenses)
                ->addIndexColumn()
                ->addColumn('expense_number', function($row) {
                    return '<strong>' . $row->expense_number . '</strong>';
                })
                ->addColumn('expense_date', function($row) {
                    return $row->expense_date->format('d/m/Y');
                })
                ->addColumn('category', function($row) {
                    return $row->category ? $row->category->category_name : '-';
                })
                ->addColumn('amount', function($row) {
                    return '<span class="text-danger fw-bold">' . number_format($row->amount, 2, ',', '.') . ' DH</span>';
                })
                ->addColumn('payment_method', function($row) {
                    $labels = [
                        'cash' => 'Espèces',
                        'check' => 'Chèque',
                        'transfer' => 'Virement',
                        'credit_card' => 'Carte',
                    ];
                    return $labels[$row->payment_method] ?? $row->payment_method;
                })
                ->addColumn('paid_to', function($row) {
                    return $row->paid_to ?? '-';
                })
                ->addColumn('status', function($row) {
                    if ($row->approved_by) {
                        return '<span class="badge bg-success">Approuvé</span>';
                    }
                    return '<span class="badge bg-warning">En attente</span>';
                })
                ->addColumn('recorded_by', function($row) {
                    return $row->recorder ? $row->recorder->username : '-';
                })
                ->addColumn('approved_by', function($row) {
                    return $row->approver ? $row->approver->username : '<span class="badge bg-warning">En attente</span>';
                })
                ->addColumn('action', function($row) {
                    $user = auth()->user();
                    $btn = '<div class="dropdown dropstart">';
                    $btn .= ' <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical fs-6"></i>
                        </a>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end">';
                    $btn .= '<li><a class="dropdown-item" href="'.route('expenses.show', $row->expense_id).'">
                                <i class="fas fa-eye me-2"></i>Voir</a></li>';
                    if ($user->can('edit_expenses')) {
                        $btn .= '<li><a class="dropdown-item" href="'.route('expenses.edit', $row->expense_id).'">
                                    <i class="fas fa-edit me-2"></i>Modifier</a></li>';
                    }
                    if ($user->can('approve_expenses') && !$row->approved_by) {
                        $btn .= '<li><a class="dropdown-item approve" href="#" data-id="'.$row->expense_id.'" data-number="'.$row->expense_number.'">
                                    <i class="fas fa-check-circle text-success me-2"></i>Approuver</a></li>';
                    }
                    if ($user->can('delete_expenses')) {
                        $btn .= '<li><hr class="dropdown-divider"></li>';
                        $btn .= '<li><a class="dropdown-item delete" href="#" data-id="'.$row->expense_id.'" data-number="'.$row->expense_number.'">
                                    <i class="fas fa-trash text-danger me-2"></i>Supprimer</a></li>';
                    }
                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['expense_number', 'amount', 'status', 'approved_by', 'action'])
                ->make(true);
        }

        $totalExpenses = Expense::sum('amount');
        $pendingExpenses = Expense::whereNull('approved_by')->count();
        $pendingAmount = Expense::whereNull('approved_by')->sum('amount');
        $approvedExpenses = Expense::whereNotNull('approved_by')->count();
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('pages.expenses.index', compact('totalExpenses', 'pendingExpenses', 'pendingAmount', 'approvedExpenses', 'categories'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        $nextExpenseNumber = 'DEP-' . date('Ymd') . '-' . str_pad(Expense::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('pages.expenses.create', compact('categories', 'nextExpenseNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_number' => 'required|unique:expenses|max:50',
            'expense_date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,category_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,check,transfer,credit_card',
            'paid_to' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expense = Expense::create([
                'expense_number' => $request->expense_number,
                'expense_date' => $request->expense_date,
                'category_id' => $request->category_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'paid_to' => $request->paid_to,
                'description' => $request->description,
                'receipt_number' => $request->receipt_number,
                'notes' => $request->notes,
                'recorded_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dépense enregistrée avec succès!',
                'expense_id' => $expense->expense_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $expense = Expense::with(['category', 'recorder', 'approver'])->findOrFail($id);
        return view('pages.expenses.show', compact('expense'));
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('pages.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        // Don't allow editing if already approved
        if ($expense->approved_by) {
            return response()->json([
                'success' => false,
                'message' => 'Cette dépense a déjà été approuvée et ne peut pas être modifiée.'
            ], 400);
        }

        $request->validate([
            'expense_number' => 'required|unique:expenses,expense_number,'.$id.',expense_id|max:50',
            'expense_date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,category_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,check,transfer,credit_card',
            'paid_to' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expense->update([
                'expense_number' => $request->expense_number,
                'expense_date' => $request->expense_date,
                'category_id' => $request->category_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'paid_to' => $request->paid_to,
                'description' => $request->description,
                'receipt_number' => $request->receipt_number,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dépense mise à jour avec succès!'
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
            $expense = Expense::findOrFail($id);

            // Don't allow deletion if already approved
            if ($expense->approved_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette dépense a déjà été approuvée et ne peut pas être supprimée.'
                ], 400);
            }

            $expense->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dépense supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $expense = Expense::findOrFail($id);

            if ($expense->approved_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette dépense a déjà été approuvée.'
                ], 400);
            }

            $expense->update([
                'approved_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dépense approuvée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics()
    {
        $totalExpenses = Expense::sum('amount');
        $pendingCount = Expense::whereNull('approved_by')->count();
        $pendingAmount = Expense::whereNull('approved_by')->sum('amount');
        $approvedCount = Expense::whereNotNull('approved_by')->count();
        $approvedAmount = Expense::whereNotNull('approved_by')->sum('amount');

        // Expenses by category
        $byCategory = Expense::with('category')
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->category ? $item->category->category_name : 'Sans catégorie',
                    'total' => $item->total,
                    'count' => $item->count,
                ];
            });

        // Monthly expenses
        $monthly = Expense::select(
                DB::raw('YEAR(expense_date) as year'),
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalExpenses,
                'pending_count' => $pendingCount,
                'pending_amount' => $pendingAmount,
                'approved_count' => $approvedCount,
                'approved_amount' => $approvedAmount,
                'by_category' => $byCategory,
                'monthly' => $monthly,
            ]
        ]);
    }
}
