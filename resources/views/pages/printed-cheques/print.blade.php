@php
    /**
     * Vue d'impression d'un chèque.
     *
     * Elle n'imprime QUE les valeurs saisies : le fond, les libellés et le
     * cadre appartiennent au chèque physique chargé dans l'imprimante.
     *
     * @var array  $layout      ['template' => …, 'paper' => …, 'a4_origin' => …, 'font' => …, 'guards' => …]
     * @var array  $values      texte à déposer, indexé par clé de champ
     * @var array  $nudge       correction ponctuelle en mm ['x' => float, 'y' => float]
     * @var bool   $autoprint   ouvrir la boîte de dialogue d'impression au chargement
     * @var bool   $guides      afficher les repères (jamais en production)
     * @var bool   $sample      afficher le spécimen en fond (écran uniquement)
     * @var ?string $confirmUrl endpoint à notifier après impression
     */
    $template = $layout['template'];
    $fields   = $template['fields'];
    $paper    = $layout['paper'];
    // Chèque imprimé à la VERTICALE (tourné de 90°, alimenté par son petit côté) :
    // son empreinte sur la page est (hauteur × largeur) = 80 × 175 mm. En A4 on le
    // centre en haut de la feuille, sauf si CHEQUE_A4_X / CHEQUE_A4_Y sont définis.
    $pageW    = $paper === 'a4' ? 210.0 : (float) $template['height_mm'];
    $pageH    = $paper === 'a4' ? 297.0 : (float) $template['width_mm'];
    $origin   = $paper === 'a4'
        ? ['x' => $layout['a4_origin']['x'] ?? (210.0 - (float) $template['height_mm']) / 2, 'y' => $layout['a4_origin']['y'] ?? 0.0]
        : ['x' => 0.0, 'y' => 0.0];
    $guards   = $layout['guards'] ?? [];

    // Scan du carnet, posé derrière les valeurs pour vérifier le calage à
    // l'écran. Il ne part JAMAIS à l'imprimante : le fond appartient au
    // chèque physique chargé dans le bac.
    $sampleUrl = $sample ? ($template['sample_url'] ?? null) : null;

    $mm = static fn (float $value): string => number_format($value, 3, '.', '').'mm';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        @page {
            /* En mode « exact », la page EST le chèque : aucune marge, sinon le
               pilote recentre le contenu et tout le calage est perdu. */
            size: {{ $paper === 'a4' ? 'A4' : $mm($pageW).' '.$mm($pageH) }};
            margin: 0;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: #eef1f5;
            font-family: {!! $layout['font']['family'] !!};
            color: {{ $layout['font']['color'] ?? '#111' }};
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sheet {
            position: relative;
            width: {{ $mm($pageW) }};
            height: {{ $mm($pageH) }};
            margin: 24px auto;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
            overflow: hidden;
        }

        .cheque {
            position: absolute;
            left: {{ $mm($origin['x'] + $nudge['x']) }};
            top: {{ $mm($origin['y'] + $nudge['y']) }};
            width: {{ $mm($template['width_mm']) }};
            height: {{ $mm($template['height_mm']) }};
            /* Rotation à 90° : le chèque occupe hauteur × largeur sur la page. */
            transform: rotate(90deg) translateY(-100%);
            transform-origin: top left;
        }

        .cheque__sample {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            /* Le spécimen est calé sur les dimensions du gabarit, pas sur son
               propre ratio : c'est le chèque qui fait référence. */
            object-fit: fill;
            pointer-events: none;
        }

        .field {
            position: absolute;
            /* Au-dessus du spécimen. */
            z-index: 1;
            overflow: hidden;
            /* Le texte est calé en bas de la boîte : son bord inférieur tombe
               sur la ligne pré-imprimée du chèque. */
            display: flex;
            align-items: flex-end;
            white-space: nowrap;
        }

        .field--multiline {
            display: block;
            white-space: normal;
            word-break: break-word;
        }

        .field__fill {
            /* Barre de fin de ligne, complétée en JS pour occuper exactement
               l'espace restant (protection contre l'ajout de mots). */
            opacity: .75;
            letter-spacing: -0.1mm;
        }

        /* ---- Repères d'alignement : écran + tirage d'essai uniquement ---- */
        .guides .cheque {
            outline: 0.2mm dashed #94a3b8;
        }

        .guides .cheque::after {
            /* Grille au millimètre. Sur un calque à part : posée en fond, elle
               passerait sous le spécimen. */
            content: '';
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background-image:
                repeating-linear-gradient(to right, rgba(148,163,184,.35) 0 0.1mm, transparent 0.1mm 5mm),
                repeating-linear-gradient(to bottom, rgba(148,163,184,.35) 0 0.1mm, transparent 0.1mm 5mm);
        }

        .guides .field {
            outline: 0.2mm dashed #f43f5e;
            background: rgba(244, 63, 94, .05);
        }

        .guides .field::before {
            /* Ancré à l'intérieur de la boîte : celle-ci est en overflow:hidden,
               une étiquette posée au-dessus serait rognée. */
            content: attr(data-key);
            position: absolute;
            top: 0;
            left: 0;
            line-height: 1;
            font-size: 5pt;
            letter-spacing: 0;
            text-indent: 0;
            color: #f43f5e;
            font-weight: 400;
        }

        /* ---- Barre d'outils : écran uniquement ---- */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            padding: 10px 16px;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 13px;
        }

        .toolbar strong { font-weight: 600; color: #fff; }
        .toolbar .spacer { flex: 1; }

        .toolbar a, .toolbar button {
            font: inherit;
            color: #e2e8f0;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 6px;
            padding: 5px 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar button.primary {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
            font-weight: 600;
        }

        .hint {
            max-width: {{ $mm($pageW) }};
            margin: 0 auto 32px;
            font-size: 12px;
            line-height: 1.5;
            color: #475569;
        }

        @media print {
            html, body { background: #fff; }
            .toolbar, .hint { display: none !important; }
            .sheet { margin: 0; box-shadow: none; }
            /* Le scan de référence ne doit jamais atteindre le papier. */
            .screen-only { display: none !important; }
        }
    </style>
</head>
<body class="{{ $guides ? 'guides' : '' }}">

<div class="toolbar">
    <strong>{{ $title }}</strong>
    <span>{{ $template['label'] ?? $template['key'] }} · {{ $template['width_mm'] }} × {{ $template['height_mm'] }} mm · orientation verticale ·
        {{ $paper === 'a4' ? 'feuille A4' : 'format chèque' }}</span>
    <span class="spacer"></span>
    <a href="{{ request()->fullUrlWithQuery(['guides' => $guides ? 0 : 1]) }}">
        {{ $guides ? 'Masquer' : 'Afficher' }} les repères
    </a>
    @if ($template['sample_url'] ?? null)
        <a href="{{ request()->fullUrlWithQuery(['sample' => $sample ? 0 : 1]) }}">
            {{ $sample ? 'Masquer' : 'Afficher' }} le spécimen
        </a>
    @endif
    <button type="button" class="primary" onclick="window.print()">Imprimer</button>
</div>

<div class="sheet">
    <div class="cheque">
        @if ($sampleUrl)
            <img class="cheque__sample screen-only" src="{{ $sampleUrl }}" alt="" aria-hidden="true">
        @endif

        @foreach ($fields as $key => $field)
            @continue(! $field['enabled'] || blank($values[$key] ?? null))

            @php
                $multiline = $field['lines'] > 1;
                $fillable  = $key === 'amount_words' && ($guards['fill_words_line'] ?? false);

                $style = [
                    'left'           => $mm($field['x']),
                    'top'            => $mm($field['y']),
                    'width'          => $mm($field['w']),
                    'height'         => $mm($field['h']),
                    'font-size'      => $field['size'].'pt',
                    'font-weight'    => $field['weight'],
                    'text-align'     => $field['align'],
                    'justify-content' => match ($field['align']) {
                        'center' => 'center',
                        'right'  => 'flex-end',
                        default  => 'flex-start',
                    },
                ];

                if ($field['spacing'] != 0.0) {
                    $style['letter-spacing'] = $mm($field['spacing']);
                }

                if ($field['upper']) {
                    $style['text-transform'] = 'uppercase';
                }

                if ($field['rtl']) {
                    $style['direction']   = 'rtl';
                    $style['font-family'] = $layout['font']['family_ar'];
                }

                if ($multiline) {
                    $style['line-height']  = $mm($field['leading']);
                    $style['text-indent']  = $mm($field['indent']);
                }
            @endphp

            <div class="field {{ $multiline ? 'field--multiline' : '' }}"
                 data-key="{{ $key }}"
                 @if ($field['shrink']) data-shrink="{{ $field['size'] }}" @endif
                 @if ($fillable) data-fill="{{ $guards['fill_char'] ?? '-' }}" @endif
                 style="@foreach ($style as $property => $value){{ $property }}:{{ $value }};@endforeach">
                <span class="field__text">{{ $values[$key] }}</span>@if ($fillable)<span class="field__fill"></span>@endif
            </div>
        @endforeach
    </div>
</div>

<p class="hint">
    Chargez le chèque vierge en <strong>orientation verticale</strong> (petit côté en premier) dans le bac manuel, puis lancez l'impression.
    Vérifiez que l'échelle du navigateur est à <strong>100 %</strong> et que « Ajuster à la page » est
    <strong>désactivé</strong> : c'est la cause la plus fréquente d'un décalage régulier sur tout le chèque.
    Un décalage ponctuel se corrige sans rien enregistrer en ajoutant <code>?dx=-1.5&amp;dy=2</code> à l'adresse
    (millimètres, valeurs négatives autorisées).
</p>

<script>
    (function () {
        /**
         * Réduit la police d'un champ jusqu'à ce que son contenu tienne dans la
         * boîte. Un bénéficiaire à rallonge doit rester dans sa zone plutôt que
         * de déborder sur la ligne voisine du chèque.
         */
        function fit(field) {
            var start = parseFloat(field.dataset.shrink);
            if (!start) return;

            var size = start;
            var min = 5;

            field.style.fontSize = size + 'pt';

            while (size > min && (field.scrollWidth > field.clientWidth + 1 || field.scrollHeight > field.clientHeight + 1)) {
                size -= 0.25;
                field.style.fontSize = size + 'pt';
            }
        }

        /**
         * Complète la fin de la ligne du montant en lettres. On ajoute les
         * caractères un par un et on s'arrête au premier retour à la ligne :
         * seul l'espace restant sur la dernière ligne écrite est barré.
         */
        function fill(field) {
            var filler = field.querySelector('.field__fill');
            if (!filler) return;

            var char = field.dataset.fill || '-';
            filler.textContent = '';

            var baseline = field.scrollHeight;
            var run = ' ';

            for (var i = 0; i < 400; i++) {
                run += char;
                filler.textContent = run;

                if (field.scrollHeight > baseline + 0.5 || field.scrollWidth > field.clientWidth + 1) {
                    filler.textContent = run.slice(0, -1);
                    return;
                }
            }
        }

        function layout() {
            document.querySelectorAll('.field').forEach(function (field) {
                fit(field);
                fill(field);
            });
        }

        // Les polices web décalent les mesures : on recalcule une fois chargées.
        layout();
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(layout);
        }

        @if ($confirmUrl)
        var confirmed = false;

        window.addEventListener('afterprint', function () {
            if (confirmed) return;
            confirmed = true;

            // Le navigateur ne dit pas si l'utilisateur a imprimé ou annulé :
            // on incrémente un compteur d'impressions, on ne verrouille rien.
            fetch(@json($confirmUrl), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                keepalive: true
            }).catch(function () {});
        });
        @endif

        @if ($autoprint)
        window.addEventListener('load', function () {
            window.setTimeout(function () { window.print(); }, 250);
        });
        @endif
    })();
</script>

</body>
</html>
