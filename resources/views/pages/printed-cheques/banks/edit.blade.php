@extends('layouts.app')

@section('title', 'Calibrer '.$bank->label)

@push('stylesheets')
<style>
    /* overflow-x:hidden sur les conteneurs de la page empêche position:sticky ; clip ne crée pas de zone de défilement */
    .page-wrapper, .body-wrapper, .container-fluid { overflow-x: clip !important; overflow-y: visible !important; }
    .calibration-workspace { position: sticky; top: 90px; z-index: 5; max-height: calc(100vh - 110px); min-height: 430px; overflow: auto; border: 1px solid #dee2e6; border-radius: .375rem; padding: 1rem; background: #e9edf2; }
    .calibration-cheque { position: relative; flex: 0 0 auto; overflow: hidden; background-color: #fff;
        background-image: linear-gradient(rgba(100,116,139,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(100,116,139,.18) 1px, transparent 1px);
        box-shadow: 0 8px 24px rgba(15,23,42,.2); user-select: none; touch-action: none; }
    .calibration-cheque.no-grid { background-image: none; }
    .calibration-background { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: fill; pointer-events: none; }
    .calibration-field { position: absolute; z-index: 1; display: flex; align-items: flex-end; overflow: hidden; border: 1px dashed #ef4444;
        background: rgba(254,226,226,.62); color: #111; white-space: nowrap; cursor: move; }
    .calibration-field.multiline { display: block; white-space: normal; overflow-wrap: anywhere; }
    .calibration-field.disabled-field { opacity: .45; }
    .calibration-field.selected { z-index: 2; border: 2px solid #2563eb; background: rgba(219,234,254,.72); }
    .calibration-field-label { position: absolute; top: 1px; left: 2px; color: #dc2626; font: 600 9px/1 Arial, sans-serif; text-indent: 0; letter-spacing: 0; pointer-events: none; }
    .calibration-field.selected .calibration-field-label { color: #1d4ed8; }
    .field-picker.active { color: #fff; background: #5d87ff; border-color: #5d87ff; }
</style>
@endpush

@section('content')
<div class="container-fluid" style="max-width:1531px !important">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h3 class="mb-1">Calibrage — {{ $bank->label }}</h3>
            <p class="text-muted mb-0">Déplacez chaque zone jusqu'à sa ligne sur le chèque, puis enregistrez le réglage.</p>
        </div>
        <a href="{{ route('cheque-banks.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour aux banques</a>
    </div>

    @include('pages.printed-cheques._flash')

    {{-- Exemple de dimensions --}}
    <div class="alert alert-info">
        <strong>Exemple de dimensions — chèque marocain standard : 175 × 80 mm (17,5 × 8 cm).</strong>
        <div class="small mt-1">
            Mesures depuis le coin supérieur gauche du <u>chèque</u> (pas de la feuille) :
            montant en chiffres ≈ x 126 / y 5 mm, boîte 43 × 5 mm (ligne à 1 cm du haut) ·
            montant en lettres : 1<sup>re</sup> ligne à y 12 mm, retrait 35 mm (saute « PAYEZ CONTRE CE CHEQUE »), 2<sup>e</sup> ligne 6 mm plus bas ·
            bénéficiaire ≈ x 20 / y 25 mm · « Fait à » ≈ x 91 / y 35 mm · date ≈ x 135 / y 35 mm.
            Le texte est calé en <strong>bas</strong> de sa boîte : <code>y + hauteur</code> = ligne pré-imprimée.
            Chaque carnet/imprimante décale de 1 à 3 mm → utilisez le <em>Décalage imprimante</em> après un tirage d'essai.
        </div>
    </div>

    <form id="reset-calibration" method="POST" action="{{ route('cheque-banks.reset', $bank) }}">@csrf @method('DELETE')</form>

    <form id="calibration-form" method="POST" action="{{ route('cheque-banks.update', $bank) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap align-items-end gap-3">
                <div style="min-width:210px">
                    <label for="calibration-zoom" class="form-label">Zoom <span id="zoom-value">6×</span></label>
                    <input id="calibration-zoom" type="range" class="form-range" min="2" max="7" step="0.5" value="6">
                </div>
                <div class="form-check mb-2">
                    <input id="show-grid" class="form-check-input" type="checkbox" checked>
                    <label class="form-check-label" for="show-grid">Grille de 5 mm</label>
                </div>
                <label class="btn btn-outline-secondary mb-0">
                    <i class="fas fa-image me-1"></i> Charger un scan (aperçu)
                    <input id="scan-input" type="file" accept="image/*" hidden>
                </label>
                <div class="d-flex flex-wrap gap-2 ms-lg-auto">
                    <a href="{{ route('cheque-banks.test', $bank) }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="fas fa-print me-1"></i> Tirage d'essai</a>
                    <button type="submit" form="reset-calibration" class="btn btn-outline-danger" onclick="return confirm('Revenir aux positions d\'origine ?')"><i class="fas fa-undo me-1"></i> Réinitialiser</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-9">
                <div class="calibration-workspace">
                    <div id="calibration-cheque" class="calibration-cheque" data-width="{{ $bank->width_mm }}" data-height="{{ $bank->height_mm }}">
                        <img id="calibration-background" class="calibration-background {{ $bank->sampleUrl() ? '' : 'd-none' }}" alt="Spécimen du chèque" src="{{ $bank->sampleUrl() }}">
                        @foreach ($fields as $key => $field)
                            <div class="calibration-field {{ $field['lines'] > 1 ? 'multiline' : '' }}" data-field="{{ $key }}" tabindex="0" dir="{{ $field['rtl'] ? 'rtl' : 'ltr' }}">
                                <span class="calibration-field-label">{{ $field['label'] }}</span>
                                <span>{{ $sample[$key] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Glissez une zone pour la déplacer. Les flèches déplacent de <strong>0,1 mm</strong> ; avec <kbd>Maj</kbd>, de <strong>1 mm</strong>.
                        Le bord inférieur de la zone doit tomber sur la ligne pré-imprimée.
                    </p>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Banque &amp; dimensions du chèque</h5></div>
                    <div class="card-body">
                        <div class="mb-2"><label class="form-label">Libellé</label><input name="label" class="form-control" value="{{ old('label', $bank->label) }}" required></div>
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label" for="width-mm">Largeur (mm)</label>
                                <input id="width-mm" name="width_mm" type="number" step="0.1" min="50" max="400" class="form-control" value="{{ old('width_mm', $bank->width_mm) }}" required></div>
                            <div class="col-6"><label class="form-label" for="height-mm">Hauteur (mm)</label>
                                <input id="height-mm" name="height_mm" type="number" step="0.1" min="30" max="300" class="form-control" value="{{ old('height_mm', $bank->height_mm) }}" required></div>
                        </div>
                        <div class="form-text">Standard : 175 × 80 mm.</div>
                        <div class="mt-2"><label class="form-label">Spécimen enregistré (affiché à l'écran, jamais imprimé)</label>
                            <input type="file" name="sample" accept="image/*" class="form-control form-control-sm"></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Décalage imprimante</h5></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6"><label for="offset-x" class="form-label">X (mm)</label>
                                <input id="offset-x" name="offset_x" type="number" class="form-control" min="-30" max="30" step="0.1" value="{{ old('offset_x', $offset['x']) }}" required></div>
                            <div class="col-6"><label for="offset-y" class="form-label">Y (mm)</label>
                                <input id="offset-y" name="offset_y" type="number" class="form-control" min="-30" max="30" step="0.1" value="{{ old('offset_y', $offset['y']) }}" required></div>
                        </div>
                        <div class="form-text">Déplace toutes les zones ensemble pour corriger l'entraînement papier (ex. texte 2 mm trop haut → Y = 2).</div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Zones à positionner</h5></div>
                    <div class="card-body d-grid gap-2">
                        @foreach ($fields as $key => $field)
                            <button type="button" class="btn btn-outline-secondary text-start field-picker" data-select-field="{{ $key }}">{{ $field['label'] }}</button>
                        @endforeach
                    </div>
                </div>

                @foreach ($fields as $key => $field)
                    @php($raw = ['x' => $field['x'] - $offset['x'], 'y' => $field['y'] - $offset['y'], 'w' => $field['w'], 'h' => $field['h'], 'size' => $field['size']])
                    <div class="card field-editor d-none" data-editor="{{ $key }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ $field['label'] }}</h5>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="fields[{{ $key }}][enabled]" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" name="fields[{{ $key }}][enabled]" value="1" data-enabled-for="{{ $key }}" @checked($field['enabled'])>
                                <label class="form-check-label small">Imprimé</label>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach (['x' => 'X', 'y' => 'Y', 'w' => 'Largeur', 'h' => 'Hauteur', 'size' => 'Police'] as $prop => $label)
                                    <div class="{{ $prop === 'size' ? 'col-12' : 'col-6' }}">
                                        <label class="form-label">{{ $label }} ({{ $prop === 'size' ? 'pt' : 'mm' }})</label>
                                        <input name="fields[{{ $key }}][{{ $prop }}]" data-setting="{{ $prop }}" data-for-field="{{ $key }}"
                                            type="number" class="form-control" step="0.1" value="{{ round($raw[$prop], 2) }}" required>
                                    </div>
                                @endforeach
                            </div>
                            <details class="mt-3">
                                <summary class="small text-muted">Options avancées</summary>
                                <div class="row g-2 mt-1">
                                    <div class="col-6"><label class="form-label small">Alignement</label>
                                        <select name="fields[{{ $key }}][align]" data-setting="align" data-for-field="{{ $key }}" class="form-select form-select-sm">
                                            @foreach (['left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite'] as $a => $l)<option value="{{ $a }}" @selected($field['align'] === $a)>{{ $l }}</option>@endforeach
                                        </select></div>
                                    <div class="col-6"><label class="form-label small">Graisse</label>
                                        <input type="number" step="100" min="100" max="900" name="fields[{{ $key }}][weight]" data-setting="weight" data-for-field="{{ $key }}" value="{{ $field['weight'] }}" class="form-control form-control-sm"></div>
                                    <div class="col-6"><label class="form-label small">Interlettrage (mm)</label>
                                        <input type="number" step="0.1" name="fields[{{ $key }}][spacing]" data-setting="spacing" data-for-field="{{ $key }}" value="{{ $field['spacing'] }}" class="form-control form-control-sm"></div>
                                    <div class="col-6"><label class="form-label small">Retrait 1<sup>re</sup> ligne (mm)</label>
                                        <input type="number" step="0.1" name="fields[{{ $key }}][indent]" data-setting="indent" data-for-field="{{ $key }}" value="{{ $field['indent'] }}" class="form-control form-control-sm"></div>
                                    <div class="col-6"><label class="form-label small">Lignes</label>
                                        <input type="number" min="1" max="4" name="fields[{{ $key }}][lines]" data-setting="lines" data-for-field="{{ $key }}" value="{{ $field['lines'] }}" class="form-control form-control-sm"></div>
                                    <div class="col-6"><label class="form-label small">Pas entre lignes (mm)</label>
                                        <input type="number" step="0.1" name="fields[{{ $key }}][leading]" data-setting="leading" data-for-field="{{ $key }}" value="{{ $field['leading'] }}" class="form-control form-control-sm"></div>
                                </div>
                            </details>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var cheque = document.getElementById('calibration-cheque');
    var zoom = document.getElementById('calibration-zoom');
    var offsetX = document.getElementById('offset-x');
    var offsetY = document.getElementById('offset-y');
    var widthMm = document.getElementById('width-mm');
    var heightMm = document.getElementById('height-mm');
    var selected = null, scale = Number(zoom.value), drag = null;

    function inputFor(key, prop) { return document.querySelector('[data-for-field="' + key + '"][data-setting="' + prop + '"]'); }
    function value(key, prop) { return Number(inputFor(key, prop).value) || 0; }
    function round(n) { return Math.round(n * 10) / 10; }

    function applyField(key) {
        var el = document.querySelector('[data-field="' + key + '"]');
        el.style.left = ((value(key, 'x') + (Number(offsetX.value) || 0)) * scale) + 'px';
        el.style.top = ((value(key, 'y') + (Number(offsetY.value) || 0)) * scale) + 'px';
        el.style.width = (value(key, 'w') * scale) + 'px';
        el.style.height = (value(key, 'h') * scale) + 'px';
        el.style.fontSize = (value(key, 'size') * 0.352778 * scale) + 'px';
        var align = inputFor(key, 'align').value;
        el.style.fontWeight = inputFor(key, 'weight').value;
        el.style.textAlign = align;
        el.style.justifyContent = align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start');
        el.style.letterSpacing = (value(key, 'spacing') * scale) + 'px';
        var lines = Math.max(1, value(key, 'lines'));
        el.classList.toggle('multiline', lines > 1);
        if (lines > 1) {
            var leading = value(key, 'leading') || (value(key, 'h') / lines);
            el.style.lineHeight = (leading * scale) + 'px';
            el.style.textIndent = (value(key, 'indent') * scale) + 'px';
        } else { el.style.lineHeight = ''; el.style.textIndent = ''; }
        var on = document.querySelector('[data-enabled-for="' + key + '"]');
        el.classList.toggle('disabled-field', on && !on.checked);
    }

    function applyAll() {
        cheque.style.width = ((Number(widthMm.value) || 175) * scale) + 'px';
        cheque.style.height = ((Number(heightMm.value) || 80) * scale) + 'px';
        cheque.style.backgroundSize = (5 * scale) + 'px ' + (5 * scale) + 'px';
        document.querySelectorAll('[data-field]').forEach(function (f) { applyField(f.dataset.field); });
    }

    function selectField(key) {
        selected = key;
        document.querySelectorAll('[data-field]').forEach(function (f) { f.classList.toggle('selected', f.dataset.field === key); });
        document.querySelectorAll('[data-editor]').forEach(function (e) { e.classList.toggle('d-none', e.dataset.editor !== key); });
        document.querySelectorAll('[data-select-field]').forEach(function (b) { b.classList.toggle('active', b.dataset.selectField === key); });
    }

    document.querySelectorAll('[data-field]').forEach(function (f) {
        f.addEventListener('pointerdown', function (e) {
            e.preventDefault();
            selectField(f.dataset.field);
            drag = { key: f.dataset.field, px: e.clientX, py: e.clientY, x: value(f.dataset.field, 'x'), y: value(f.dataset.field, 'y') };
        });
    });
    window.addEventListener('pointermove', function (e) {
        if (!drag) return;
        inputFor(drag.key, 'x').value = round(drag.x + (e.clientX - drag.px) / scale);
        inputFor(drag.key, 'y').value = round(drag.y + (e.clientY - drag.py) / scale);
        applyField(drag.key);
    });
    window.addEventListener('pointerup', function () { drag = null; });
    window.addEventListener('pointercancel', function () { drag = null; });

    window.addEventListener('keydown', function (e) {
        if (!selected || ['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
        var moves = { ArrowLeft: ['x', -1], ArrowRight: ['x', 1], ArrowUp: ['y', -1], ArrowDown: ['y', 1] };
        var m = moves[e.key]; if (!m) return;
        e.preventDefault();
        var input = inputFor(selected, m[0]);
        input.value = round((Number(input.value) || 0) + m[1] * (e.shiftKey ? 1 : 0.1));
        applyField(selected);
    });

    document.querySelectorAll('[data-select-field]').forEach(function (b) { b.addEventListener('click', function () { selectField(b.dataset.selectField); }); });
    document.querySelectorAll('[data-setting]').forEach(function (i) { i.addEventListener('input', function () { applyField(i.dataset.forField); }); });
    document.querySelectorAll('[data-enabled-for]').forEach(function (c) { c.addEventListener('change', function () { applyField(c.dataset.enabledFor); }); });
    [offsetX, offsetY, widthMm, heightMm].forEach(function (i) { i.addEventListener('input', applyAll); });
    zoom.addEventListener('input', function () { scale = Number(zoom.value); document.getElementById('zoom-value').textContent = scale + '×'; applyAll(); });
    document.getElementById('show-grid').addEventListener('change', function () { cheque.classList.toggle('no-grid', !this.checked); });
    document.getElementById('scan-input').addEventListener('change', function () {
        var img = document.getElementById('calibration-background'), file = this.files && this.files[0];
        if (!file) return;
        img.src = URL.createObjectURL(file); img.classList.remove('d-none');
    });

    applyAll();
    selectField(@json(array_key_first($fields)));
});
</script>
@endpush
