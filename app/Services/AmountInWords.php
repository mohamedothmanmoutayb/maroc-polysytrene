<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Conversion d'un montant en toutes lettres pour la ligne
 * « Somme en toutes lettres / المبلغ مبينا بالأحرف » d'un chèque marocain.
 *
 * Le résultat reste modifiable à la main dans le formulaire : ce service
 * pré-remplit, il ne décide pas à la place du signataire.
 */
class AmountInWords
{
    /** Plafond raisonnable : au-delà, on refuse plutôt que d'écrire n'importe quoi. */
    private const MAX = 999_999_999_999.99;

    private const FR_UNITS = [
        0 => 'zéro', 1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre',
        5 => 'cinq', 6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf',
        10 => 'dix', 11 => 'onze', 12 => 'douze', 13 => 'treize',
        14 => 'quatorze', 15 => 'quinze', 16 => 'seize', 17 => 'dix-sept',
        18 => 'dix-huit', 19 => 'dix-neuf',
    ];

    private const FR_TENS = [
        2 => 'vingt', 3 => 'trente', 4 => 'quarante',
        5 => 'cinquante', 6 => 'soixante',
    ];

    private const AR_UNITS = [
        0 => 'صفر', 1 => 'واحد', 2 => 'اثنان', 3 => 'ثلاثة', 4 => 'أربعة',
        5 => 'خمسة', 6 => 'ستة', 7 => 'سبعة', 8 => 'ثمانية', 9 => 'تسعة',
        10 => 'عشرة', 11 => 'أحد عشر', 12 => 'اثنا عشر', 13 => 'ثلاثة عشر',
        14 => 'أربعة عشر', 15 => 'خمسة عشر', 16 => 'ستة عشر',
        17 => 'سبعة عشر', 18 => 'ثمانية عشر', 19 => 'تسعة عشر',
    ];

    private const AR_TENS = [
        2 => 'عشرون', 3 => 'ثلاثون', 4 => 'أربعون', 5 => 'خمسون',
        6 => 'ستون', 7 => 'سبعون', 8 => 'ثمانون', 9 => 'تسعون',
    ];

    private const AR_HUNDREDS = [
        1 => 'مائة', 2 => 'مائتان', 3 => 'ثلاثمائة', 4 => 'أربعمائة',
        5 => 'خمسمائة', 6 => 'ستمائة', 7 => 'سبعمائة', 8 => 'ثمانمائة',
        9 => 'تسعمائة',
    ];

    /**
     * Montant → lettres, dirhams et centimes compris.
     *
     * @param  string  $locale  'fr' ou 'ar'
     */
    public function convert(float|int|string $amount, string $locale = 'fr'): string
    {
        $amount = round((float) $amount, 2);

        if ($amount < 0) {
            throw new InvalidArgumentException('Le montant d\'un chèque ne peut pas être négatif.');
        }

        if ($amount > self::MAX) {
            throw new InvalidArgumentException('Montant hors des limites gérées par le convertisseur.');
        }

        $major = (int) floor($amount);
        $minor = (int) round(($amount - $major) * 100);

        // round() sur les flottants peut ramener 100 centimes : on reporte.
        if ($minor === 100) {
            $major++;
            $minor = 0;
        }

        return $locale === 'ar'
            ? $this->arabic($major, $minor)
            : $this->french($major, $minor);
    }

    /**
     * Montant en chiffres au format marocain : 1 234 567,89
     * (espace insécable fine pour les milliers, virgule décimale).
     */
    public function figures(float|int|string $amount, bool $withDecimals = true): string
    {
        $amount = round((float) $amount, 2);

        return number_format($amount, $withDecimals ? 2 : 0, ',', ' ');
    }

    // -----------------------------------------------------------------
    // Français
    // -----------------------------------------------------------------

    private function french(int $major, int $minor): string
    {
        $currency = config('cheque_print.currency.fr', [
            'major' => 'dirham', 'major_plural' => 'dirhams',
            'minor' => 'centime', 'minor_plural' => 'centimes',
        ]);

        $words = $this->frenchNumber($major).' '
            .($major > 1 ? $currency['major_plural'] : $currency['major']);

        if ($minor > 0) {
            $words .= ' et '.$this->frenchNumber($minor).' '
                .($minor > 1 ? $currency['minor_plural'] : $currency['minor']);
        }

        return $this->ucfirst($words);
    }

    private function frenchNumber(int $n): string
    {
        if ($n === 0) {
            return self::FR_UNITS[0];
        }

        $parts = [];

        // « mille » est invariable et ne prend jamais « un » devant ; « million »
        // et « milliard » sont des noms : ils s'accordent et gardent leur « un ».
        foreach ([
            1_000_000_000 => ['milliard', 'milliards', true],
            1_000_000 => ['million', 'millions', true],
            1_000 => ['mille', 'mille', false],
        ] as $value => [$singular, $plural, $isNoun]) {
            $count = intdiv($n, $value);
            $n %= $value;

            if ($count === 0) {
                continue;
            }

            if (! $isNoun) {
                // 1 000 → « mille » ; 200 000 → « deux cent mille » (cent invariable
                // devant mille), d'où le drapeau $final à false.
                $parts[] = $count === 1
                    ? $singular
                    : $this->frenchBelowThousand($count, false).' '.$singular;

                continue;
            }

            $parts[] = ($count === 1 ? 'un' : $this->frenchBelowThousand($count, true))
                .' '.($count === 1 ? $singular : $plural);
        }

        if ($n > 0) {
            $parts[] = $this->frenchBelowThousand($n, true);
        }

        return implode(' ', $parts);
    }

    /**
     * @param  bool  $final  Le groupe termine-t-il le nombre ? Conditionne le
     *                       pluriel de « cent » et de « quatre-vingt ».
     */
    private function frenchBelowThousand(int $n, bool $final): string
    {
        $hundreds = intdiv($n, 100);
        $rest = $n % 100;

        if ($hundreds === 0) {
            return $this->frenchBelowHundred($rest, $final);
        }

        $words = $hundreds === 1 ? 'cent' : self::FR_UNITS[$hundreds].' cent';

        if ($rest > 0) {
            return $words.' '.$this->frenchBelowHundred($rest, $final);
        }

        // « deux cents » mais « deux cent trente » et « deux cent mille ».
        if ($hundreds > 1 && $final) {
            $words .= 's';
        }

        return $words;
    }

    private function frenchBelowHundred(int $n, bool $final): string
    {
        if ($n < 20) {
            return self::FR_UNITS[$n];
        }

        $tens = intdiv($n, 10);
        $units = $n % 10;

        // 70-79 et 90-99 se construisent sur soixante / quatre-vingt + 10..19.
        if ($tens === 7) {
            return $units === 1
                ? 'soixante-et-onze'
                : 'soixante-'.self::FR_UNITS[10 + $units];
        }

        if ($tens === 9) {
            return 'quatre-vingt-'.self::FR_UNITS[10 + $units];
        }

        if ($tens === 8) {
            // « quatre-vingts » seulement s'il termine le nombre.
            return $units === 0
                ? ($final ? 'quatre-vingts' : 'quatre-vingt')
                : 'quatre-vingt-'.self::FR_UNITS[$units];
        }

        $word = self::FR_TENS[$tens];

        if ($units === 0) {
            return $word;
        }

        // 21, 31, … 61 prennent « et un » ; 81 non (traité plus haut).
        return $units === 1 ? $word.'-et-un' : $word.'-'.self::FR_UNITS[$units];
    }

    // -----------------------------------------------------------------
    // Arabe
    // -----------------------------------------------------------------

    /** Formes du duel qui perdent leur « ن » devant le spécificatif (إضافة). */
    private const AR_CONSTRUCT = [
        'مائتان' => 'مائتا',
        'ألفان' => 'ألفا',
        'مليونان' => 'مليونا',
        'ملياران' => 'مليارا',
    ];

    private function arabic(int $major, int $minor): string
    {
        $currency = config('cheque_print.currency.ar', []);

        $words = $this->arabicWithUnit($major, $currency['major'] ?? [
            'singular' => 'درهم', 'dual' => 'درهمان', 'plural' => 'دراهم', 'accusative' => 'درهماً',
        ]);

        if ($minor > 0) {
            $words .= ' و'.$this->arabicWithUnit($minor, $currency['minor'] ?? [
                'singular' => 'سنتيم', 'dual' => 'سنتيمان', 'plural' => 'سنتيمات', 'accusative' => 'سنتيماً',
            ]);
        }

        return $words;
    }

    /**
     * Accord du spécificatif (تمييز) : 1 → singulier, 2 → duel, 3-10 →
     * pluriel, multiple rond de cent/mille/million → singulier en annexion,
     * tout le reste → accusatif singulier.
     *
     * @param  array{singular: string, dual: string, plural: string, accusative: string}  $forms
     */
    private function arabicWithUnit(int $n, array $forms): string
    {
        if ($n === 0) {
            return self::AR_UNITS[0].' '.$forms['singular'];
        }

        if ($n === 1) {
            return $forms['singular'].' واحد';
        }

        if ($n === 2) {
            return $forms['dual'];
        }

        $remainder = $n % 100;
        $words = $this->arabicNumber($n);

        // « مائة درهم », « ألف درهم » : le spécificatif reste au singulier.
        if ($remainder === 0) {
            return $this->arabicConstruct($words).' '.$forms['singular'];
        }

        return $words.' '.($remainder >= 3 && $remainder <= 10 ? $forms['plural'] : $forms['accusative']);
    }

    /** « ألفان » → « ألفا » lorsqu'un spécificatif suit. */
    private function arabicConstruct(string $words): string
    {
        foreach (self::AR_CONSTRUCT as $dual => $construct) {
            if (str_ends_with($words, $dual)) {
                return mb_substr($words, 0, mb_strlen($words) - mb_strlen($dual)).$construct;
            }
        }

        return $words;
    }

    private function arabicNumber(int $n): string
    {
        if ($n === 0) {
            return self::AR_UNITS[0];
        }

        $parts = [];

        foreach ([
            1_000_000_000 => ['مليار', 'ملياران', 'مليارات', 'ملياراً'],
            1_000_000 => ['مليون', 'مليونان', 'ملايين', 'مليوناً'],
            1_000 => ['ألف', 'ألفان', 'آلاف', 'ألفاً'],
        ] as $value => [$singular, $dual, $plural, $accusative]) {
            $count = intdiv($n, $value);
            $n %= $value;

            if ($count === 0) {
                continue;
            }

            $parts[] = match (true) {
                $count === 1 => $singular,
                $count === 2 => $dual,
                $count <= 10 => $this->arabicBelowThousand($count).' '.$plural,
                default => $this->arabicBelowThousand($count).' '.$accusative,
            };
        }

        if ($n > 0) {
            $parts[] = $this->arabicBelowThousand($n);
        }

        return implode(' و', $parts);
    }

    private function arabicBelowThousand(int $n): string
    {
        $hundreds = intdiv($n, 100);
        $rest = $n % 100;

        if ($hundreds === 0) {
            return $this->arabicBelowHundred($rest);
        }

        $words = self::AR_HUNDREDS[$hundreds];

        return $rest > 0 ? $words.' و'.$this->arabicBelowHundred($rest) : $words;
    }

    private function arabicBelowHundred(int $n): string
    {
        if ($n < 20) {
            return self::AR_UNITS[$n];
        }

        $tens = intdiv($n, 10);
        $units = $n % 10;

        // En arabe l'unité précède la dizaine : « ثلاثة وأربعون ».
        return $units === 0
            ? self::AR_TENS[$tens]
            : self::AR_UNITS[$units].' و'.self::AR_TENS[$tens];
    }

    /** mb_ucfirst : ucfirst() casserait les accents. */
    private function ucfirst(string $value): string
    {
        return mb_strtoupper(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }
}
