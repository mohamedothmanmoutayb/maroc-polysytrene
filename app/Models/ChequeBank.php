<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Gabarit d'impression d'une banque : dimensions du chèque et position de chaque champ (mm). */
class ChequeBank extends Model
{
    protected $table = 'cheque_banks';

    protected $fillable = ['key', 'label', 'width_mm', 'height_mm', 'offset_x', 'offset_y', 'fields', 'sample_path'];

    protected $casts = [
        'width_mm' => 'float',
        'height_mm' => 'float',
        'offset_x' => 'float',
        'offset_y' => 'float',
        'fields' => 'array',
    ];

    private const FIELD_DEFAULTS = [
        'label' => null, 'enabled' => true, 'x' => 0.0, 'y' => 0.0, 'w' => 40.0, 'h' => 5.0,
        'align' => 'left', 'size' => 10.0, 'weight' => 400, 'spacing' => 0.0, 'upper' => false,
        'rtl' => false, 'indent' => 0.0, 'lines' => 1, 'leading' => 0.0, 'shrink' => true,
    ];

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function sampleUrl(): ?string
    {
        return $this->sample_path ? asset('storage/'.$this->sample_path) : null;
    }

    /** Champs prêts à rendre : valeurs par défaut + décalage global déjà appliqué. */
    public function resolvedFields(): array
    {
        $fields = [];
        foreach (($this->fields ?: config('cheque_print.default_fields')) as $key => $field) {
            $field = array_merge(self::FIELD_DEFAULTS, ['label' => $key], $field);
            $field['x'] = (float) $field['x'] + $this->offset_x;
            $field['y'] = (float) $field['y'] + $this->offset_y;
            foreach (['w', 'h', 'size', 'spacing', 'indent', 'leading'] as $n) {
                $field[$n] = (float) $field[$n];
            }
            $field['lines'] = max(1, (int) $field['lines']);
            $field['enabled'] = (bool) $field['enabled'];
            if ($field['lines'] > 1 && $field['leading'] <= 0.0) {
                $field['leading'] = $field['h'] / $field['lines'];
            }
            $fields[$key] = $field;
        }

        return $fields;
    }

    /** Données de rendu pour la vue d'impression. */
    public function layout(): array
    {
        return [
            'template' => [
                'key' => $this->key,
                'label' => $this->label,
                'width_mm' => $this->width_mm,
                'height_mm' => $this->height_mm,
                'sample_url' => $this->sampleUrl(),
                'fields' => $this->resolvedFields(),
            ],
            'paper' => config('cheque_print.paper', 'exact'),
            'a4_origin' => config('cheque_print.a4_origin'),
            'font' => config('cheque_print.font'),
            'guards' => config('cheque_print.guards'),
        ];
    }
}
