<?php

/*
| Impression de chèques émis (coordonnées en MILLIMÈTRES depuis le coin
| supérieur gauche du chèque). Le texte est calé en BAS de chaque boîte : le
| bord inférieur (y + h) doit tomber sur la ligne pré-imprimée.
|
| ⚠ Les gabarits sont un point de départ (chèque marocain standard 175 x 80 mm).
|   Chaque carnet/agence/imprimante décale de 1 à 3 mm : calibrez depuis
|   « Chèques à imprimer > Banques » avec une impression d'essai sur un chèque annulé.
*/

return [

    'default_bank' => env('CHEQUE_DEFAULT_BANK', 'attijariwafa'),

    // Le chèque est imprimé à la VERTICALE (tourné de 90°, alimenté par son petit côté).
    // 'a4'    : chèque centré en haut d'une feuille A4 (80 x 175 mm d'empreinte).
    // 'exact' : la page fait exactement 80 x 175 mm.
    // a4_origin : null = centré horizontalement ; sinon position en mm du coin haut-gauche.
    'paper' => env('CHEQUE_PAPER', 'a4'),
    'a4_origin' => [
        'x' => env('CHEQUE_A4_X') !== null ? (float) env('CHEQUE_A4_X') : null,
        'y' => (float) env('CHEQUE_A4_Y', 0.0),
    ],

    'date_format' => env('CHEQUE_DATE_FORMAT', 'd/m/Y'),
    'words_locale' => env('CHEQUE_WORDS_LOCALE', 'fr'),

    'currency' => [
        'code' => 'MAD',
        'fr' => ['major' => 'dirham', 'major_plural' => 'dirhams', 'minor' => 'centime', 'minor_plural' => 'centimes'],
        // L'arabe accorde le spécificatif (تمييز) selon le nombre : les quatre
        // formes sont nécessaires.
        'ar' => [
            'major' => ['singular' => 'درهم', 'dual' => 'درهمان', 'plural' => 'دراهم', 'accusative' => 'درهماً'],
            'minor' => ['singular' => 'سنتيم', 'dual' => 'سنتيمان', 'plural' => 'سنتيمات', 'accusative' => 'سنتيماً'],
        ],
    ],

    'guards' => [
        'figures_prefix' => '',
        'figures_suffix' => '',
        'fill_words_line' => false,
        'fill_char' => '-',
    ],

    'font' => [
        'family' => "'DejaVu Sans', Arial, Helvetica, sans-serif",
        'family_ar' => "'Noto Naskh Arabic', 'Amiri', 'DejaVu Sans', sans-serif",
        'color' => '#111111',
    ],

    // Banque créées à l'installation (clé => libellé). Chacune part de 'default_fields'.
    'seed_banks' => [
        'attijariwafa' => 'Attijariwafa Bank',
    ],

    // Champs et dimensions de départ d'une banque.
    'default_size' => ['width_mm' => 175.0, 'height_mm' => 80.0],

    'default_fields' => [
        'amount_figures' => ['label' => 'Montant en chiffres', 'x' => 126.0, 'y' => 5.0, 'w' => 43.0, 'h' => 5.0, 'align' => 'center', 'size' => 11.0, 'weight' => 700, 'spacing' => 0.2],
        'amount_words' => ['label' => 'Montant en lettres', 'x' => 5.0, 'y' => 12.0, 'w' => 130.0, 'h' => 12.0, 'indent' => 35.0, 'lines' => 2, 'leading' => 6.0, 'size' => 9.5],
        'beneficiary' => ['label' => 'Bénéficiaire', 'x' => 20.0, 'y' => 25.0, 'w' => 136.0, 'h' => 5.0, 'size' => 10.0, 'weight' => 600],
        'place' => ['label' => 'Fait à', 'x' => 91.0, 'y' => 35.0, 'w' => 39.0, 'h' => 5.0, 'size' => 9.0],
        'date' => ['label' => 'Date', 'x' => 135.0, 'y' => 35.0, 'w' => 30.0, 'h' => 5.0, 'size' => 9.5, 'spacing' => 0.4],
        'payable_at' => ['label' => 'Payable à', 'x' => 3.0, 'y' => 45.0, 'w' => 53.0, 'h' => 6.0, 'size' => 8.5, 'enabled' => false],
        'account_number' => ['label' => 'N° de compte', 'x' => 91.0, 'y' => 35.0, 'w' => 39.0, 'h' => 5.0, 'size' => 8.5, 'enabled' => false],
    ],
];
