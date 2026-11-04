<?php

namespace App\Models;

use App\Services\AmountInWords;
use Illuminate\Database\Eloquent\Model;

/** Chèque émis, saisi pour être imprimé sur le chèque vierge d'une banque. */
class PrintedCheque extends Model
{
    public const STATUSES = [
        'draft' => ['Brouillon', 'secondary'],
        'printed' => ['Imprimé', 'primary'],
        'issued' => ['Remis', 'warning'],
        'cashed' => ['Encaissé', 'success'],
        'cancelled' => ['Annulé', 'danger'],
    ];

    protected $table = 'printed_cheques';

    protected $fillable = [
        'bank_key', 'cheque_number', 'account_number', 'beneficiary', 'amount', 'amount_words',
        'amount_words_locale', 'place', 'issue_date', 'payable_at', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
        'printed_at' => 'datetime',
        'print_count' => 'integer',
    ];

    public function bank()
    {
        return $this->belongsTo(ChequeBank::class, 'bank_key', 'key');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status][1] ?? 'secondary';
    }

    /** Texte à déposer dans chaque zone du chèque. */
    public function printableFields(): array
    {
        $guards = config('cheque_print.guards', []);
        $figures = ($guards['figures_prefix'] ?? '')
            .app(AmountInWords::class)->figures($this->amount)
            .($guards['figures_suffix'] ?? '');

        return array_filter([
            'amount_figures' => $figures,
            'amount_words' => (string) $this->amount_words,
            'beneficiary' => (string) $this->beneficiary,
            'place' => (string) $this->place,
            'date' => $this->issue_date?->format(config('cheque_print.date_format', 'd/m/Y')) ?? '',
            'payable_at' => (string) $this->payable_at,
            'account_number' => (string) $this->account_number,
        ], static fn (string $v) => $v !== '');
    }

    public function markPrinted(): void
    {
        $this->forceFill([
            'printed_at' => now(),
            'print_count' => $this->print_count + 1,
            'status' => $this->status === 'draft' ? 'printed' : $this->status,
        ])->save();
    }
}
