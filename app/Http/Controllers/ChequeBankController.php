<?php

namespace App\Http\Controllers;

use App\Models\ChequeBank;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Banques et dimensions/positions de leurs chèques. */
class ChequeBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_checks');
        $this->middleware('can:create_checks')->except(['index']);
    }

    public function index()
    {
        return view('pages.printed-cheques.banks.index', ['banks' => ChequeBank::orderBy('label')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['label' => 'required|string|max:100']);
        $key = Str::slug($data['label']);
        abort_if(ChequeBank::where('key', $key)->exists(), 422, 'Cette banque existe déjà.');

        $bank = ChequeBank::create([
            'key' => $key,
            'label' => $data['label'],
            'width_mm' => config('cheque_print.default_size.width_mm'),
            'height_mm' => config('cheque_print.default_size.height_mm'),
            'fields' => config('cheque_print.default_fields'),
        ]);

        return redirect()->route('cheque-banks.edit', $bank)->with('success', 'Banque ajoutée : calibrez ses champs.');
    }

    public function edit(ChequeBank $bank)
    {
        return view('pages.printed-cheques.banks.edit', [
            'bank' => $bank,
            'fields' => $bank->resolvedFields(),
            'offset' => ['x' => $bank->offset_x, 'y' => $bank->offset_y],
            'sample' => [
                'amount_figures' => '1 234 567,89',
                'amount_words' => 'Un million deux cent trente-quatre mille cinq cent soixante-sept dirhams et quatre-vingt-neuf centimes',
                'beneficiary' => 'SOCIÉTÉ EXEMPLE SARL',
                'place' => 'Casablanca',
                'date' => now()->format(config('cheque_print.date_format', 'd/m/Y')),
                'payable_at' => 'Casablanca Centre',
                'account_number' => '007 780 0001234567890123 45',
            ],
        ]);
    }

    public function update(Request $request, ChequeBank $bank)
    {
        $data = $request->validate([
            'label' => 'required|string|max:100',
            'width_mm' => 'required|numeric|min:50|max:400',
            'height_mm' => 'required|numeric|min:30|max:300',
            'offset_x' => 'nullable|numeric|between:-30,30',
            'offset_y' => 'nullable|numeric|between:-30,30',
            'fields' => 'required|array',
            'fields.*.x' => 'required|numeric', 'fields.*.y' => 'required|numeric',
            'fields.*.w' => 'required|numeric|min:1', 'fields.*.h' => 'required|numeric|min:1',
            'fields.*.size' => 'required|numeric|min:4|max:30',
            'fields.*.align' => ['nullable', Rule::in(['left', 'center', 'right'])],
            'fields.*.weight' => 'nullable|integer|between:100,900',
            'fields.*.spacing' => 'nullable|numeric', 'fields.*.indent' => 'nullable|numeric',
            'fields.*.lines' => 'nullable|integer|between:1,4', 'fields.*.leading' => 'nullable|numeric',
            'fields.*.enabled' => 'nullable|boolean',
            'sample' => 'nullable|image|max:4096',
        ]);

        // On stocke les coordonnées brutes : le décalage global est ré-appliqué au rendu.
        $stored = $bank->fields ?: config('cheque_print.default_fields');
        foreach ($data['fields'] as $key => $f) {
            if (! isset($stored[$key])) {
                continue;
            }
            $stored[$key] = array_merge($stored[$key], [
                'x' => (float) $f['x'], 'y' => (float) $f['y'], 'w' => (float) $f['w'], 'h' => (float) $f['h'],
                'size' => (float) $f['size'], 'align' => $f['align'] ?? 'left', 'weight' => (int) ($f['weight'] ?? 400),
                'spacing' => (float) ($f['spacing'] ?? 0), 'indent' => (float) ($f['indent'] ?? 0),
                'lines' => (int) ($f['lines'] ?? 1), 'leading' => (float) ($f['leading'] ?? 0),
                'enabled' => (bool) ($f['enabled'] ?? false),
            ]);
        }

        $update = [
            'label' => $data['label'], 'width_mm' => $data['width_mm'], 'height_mm' => $data['height_mm'],
            'offset_x' => $data['offset_x'] ?? 0, 'offset_y' => $data['offset_y'] ?? 0, 'fields' => $stored,
        ];
        if ($request->hasFile('sample')) {
            $update['sample_path'] = $request->file('sample')->store('cheque-samples', 'public');
        }
        $bank->update($update);

        return redirect()->route('cheque-banks.edit', $bank)->with('success', 'Dimensions et positions enregistrées.');
    }

    public function reset(ChequeBank $bank)
    {
        $bank->update([
            'width_mm' => config('cheque_print.default_size.width_mm'),
            'height_mm' => config('cheque_print.default_size.height_mm'),
            'offset_x' => 0, 'offset_y' => 0,
            'fields' => config('cheque_print.default_fields'),
        ]);

        return back()->with('success', 'Gabarit réinitialisé.');
    }

    public function destroy(ChequeBank $bank)
    {
        if (\App\Models\PrintedCheque::where('bank_key', $bank->key)->exists()) {
            return back()->with('error', 'Cette banque a des chèques enregistrés : suppression impossible.');
        }
        $bank->delete();

        return redirect()->route('cheque-banks.index')->with('success', 'Banque supprimée.');
    }
}
