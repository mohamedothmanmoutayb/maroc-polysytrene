<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';
    protected $primaryKey = 'expense_id';
    public $timestamps = true;

    protected $fillable = [
        'expense_number',
        'expense_date',
        'category_id',
        'amount',
        'payment_method',
        'paid_to',
        'description',
        'receipt_number',
        'approved_by',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function generateNextExpenseNumber(): string
    {
        $lastNumber = self::query()
            ->pluck('expense_number')
            ->map(function ($expenseNumber) {
                preg_match('/-(\d+)$/', $expenseNumber, $matches);

                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->max() ?? 0;

        return 'DEP-' . now()->format('Ymd') . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
