<?php

namespace App\Http\Controllers;

use App\Models\ChequeBank;
use App\Models\PrintedCheque;
use App\Services\AmountInWords;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PrintedChequeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_checks');
    }

    public function index(Request $request)
    {
        $cheques = PrintedCheque::with('bank')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w
                ->where('beneficiary', 'like', "%$t%")->orWhere('cheque_number', 'like', "%$t%")))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('issue_date')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('pages.printed-cheques.index', compact('cheques'));
    }

    public function create()
    {
        return view('pages.printed-cheques.form', [
            'cheque' => new PrintedCheque([
                'bank_key' => config('cheque_print.default_bank'),
                'issue_date' => now(),
                'amount_words_locale' => config('cheque_print.words_locale', 'fr'),
            ]),
            'banks' => ChequeBank::orderBy('label')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $cheque = PrintedCheque::create($this->validated($request) + ['created_by' => auth()->id()]);

        return redirect()->route('printed-cheques.print', $cheque)->with('success', 'Chèque enregistré.');
    }

    public function edit(PrintedCheque $printedCheque)
    {
        return view('pages.printed-cheques.form', [
            'cheque' => $printedCheque,
            'banks' => ChequeBank::orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, PrintedCheque $printedCheque)
    {
        $printedCheque->update($this->validated($request));

        return redirect()->route('printed-cheques.index')->with('success', 'Chèque mis à jour.');
    }

    public function destroy(PrintedCheque $printedCheque)
    {
        $printedCheque->delete();

        return redirect()->route('printed-cheques.index')->with('success', 'Chèque supprimé.');
    }

    public function status(Request $request, PrintedCheque $printedCheque)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(PrintedCheque::STATUSES))]]);
        $printedCheque->update($data);

        return back()->with('success', 'Statut mis à jour.');
    }

    /** Conversion montant → lettres, appelée pendant la saisie. */
    public function words(Request $request, AmountInWords $words)
    {
        $v = $request->validate(['amount' => 'required|numeric|min:0', 'locale' => 'nullable|in:fr,ar']);

        return response()->json([
            'words' => $words->convert($v['amount'], $v['locale'] ?? 'fr'),
            'figures' => $words->figures($v['amount']),
        ]);
    }

    public function print(Request $request, PrintedCheque $printedCheque)
    {
        return view('pages.printed-cheques.print', [
            'title' => 'Chèque '.($printedCheque->cheque_number ?: $printedCheque->id),
            'layout' => $printedCheque->bank->layout(),
            'values' => $printedCheque->printableFields(),
            'nudge' => $this->nudge($request),
            'autoprint' => $request->boolean('autoprint'),
            'guides' => $request->boolean('guides'),
            'sample' => ! $request->has('sample') || $request->boolean('sample'),
            'confirmUrl' => route('printed-cheques.print.confirm', $printedCheque),
        ]);
    }

    /** Impression d'essai : mêmes zones, contenu factice, repères visibles. */
    public function test(Request $request, ChequeBank $bank)
    {
        return view('pages.printed-cheques.print', [
            'title' => 'Test d\'alignement — '.$bank->label,
            'layout' => $bank->layout(),
            'values' => [
                'amount_figures' => '**1 234 567,89**',
                'amount_words' => 'Un million deux cent trente-quatre mille cinq cent soixante-sept dirhams et quatre-vingt-neuf centimes',
                'beneficiary' => 'ESSAI D\'ALIGNEMENT — BENEFICIAIRE',
                'place' => 'Casablanca',
                'date' => now()->format(config('cheque_print.date_format', 'd/m/Y')),
            ],
            'nudge' => $this->nudge($request),
            'autoprint' => $request->boolean('autoprint'),
            'guides' => ! $request->has('guides') || $request->boolean('guides'),
            'sample' => ! $request->has('sample') || $request->boolean('sample'),
            'confirmUrl' => null,
        ]);
    }

    public function confirmPrint(PrintedCheque $printedCheque): Response
    {
        $printedCheque->markPrinted();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'bank_key' => ['required', Rule::exists('cheque_banks', 'key')],
            'cheque_number' => 'nullable|string|max:30',
            'account_number' => 'nullable|string|max:40',
            'beneficiary' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:9999999999999',
            'amount_words' => 'required|string',
            'amount_words_locale' => 'required|in:fr,ar',
            'place' => 'required|string|max:80',
            'issue_date' => 'required|date',
            'payable_at' => 'nullable|string|max:80',
            'notes' => 'nullable|string',
        ]);
    }

    private function nudge(Request $request): array
    {
        return ['x' => (float) $request->query('dx', 0), 'y' => (float) $request->query('dy', 0)];
    }
}
