<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductsFamilyStockExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $productType;
    protected $status;

    // Populated in array(), consumed in styles() — same instance, guaranteed order
    protected $familleGroups = [];

    public function __construct($productType = null, $status = null)
    {
        $this->productType = $productType;
        $this->status      = $status;
    }

    public function array(): array
    {
        $query = Product::with(['familles', 'familleStocks.famille']);

        if ($this->productType) {
            $query->where('product_type', $this->productType);
        }
        if ($this->status !== null) {
            $query->where('is_active', $this->status);
        }

        $products = $query->get();

        // Build famille → rows map, filter stock ≤ 0
        $famillesData = [];
        foreach ($products as $product) {
            foreach ($product->familles as $famille) {
                $familleStock = $product->familleStocks
                    ->where('famille_id', $famille->famille_id)
                    ->first();

                $qty = $familleStock ? (float) $familleStock->current_quantity : 0;
                if ($qty <= 0) {
                    continue;
                }

                $totalVolume = $qty * ($product->volume_m3 ?? 0);

                $famillesData[$famille->famille_id]['name']   = $famille->famille_name;
                $famillesData[$famille->famille_id]['rows'][] = [
                    $product->product_code,
                    $product->product_name,
                    number_format($qty, 2),
                    $totalVolume > 0 ? number_format($totalVolume, 3) : '-',
                    $familleStock->location ?? 'Entrepôt Principal',
                    $this->getUnitLabel($product->product_type),
                ];
            }
        }

        // Sort alphabetically by famille name
        uasort($famillesData, fn($a, $b) => strcmp($a['name'], $b['name']));

        $rows       = [];
        $currentRow = 2; // row 1 is the heading

        foreach ($famillesData as $familleData) {
            if (empty($familleData['rows'])) {
                continue;
            }

            $count = count($familleData['rows']);

            // Record this groupe for vertical merging in styles()
            $this->familleGroups[] = [
                'start' => $currentRow,
                'count' => $count,
                'name'  => $familleData['name'],
            ];

            $isFirst = true;
            foreach ($familleData['rows'] as $dataRow) {
                // Column A: famille name only on first row of the groupe (merged later)
                $rows[] = array_merge([$isFirst ? $familleData['name'] : ''], $dataRow);
                $currentRow++;
                $isFirst = false;
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Famille',
            'Code Produit',
            'Nom Produit',
            'Stock Total',
            'Volume Total (m³)',
            'Emplacement',
            'Unités',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // 1. Thin border on every cell
        $sheet->getStyle('A1:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // 2. Column-header row (row 1) — blue, bold, white text, centered
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667EEA']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Vertical famille column — merge A cells per groupe, rotate text 90°
        foreach ($this->familleGroups as $group) {
            $startCell = 'A' . $group['start'];
            $endCell   = 'A' . ($group['start'] + $group['count'] - 1);

            // Merge only when the groupe has more than one row
            if ($group['count'] > 1) {
                $sheet->mergeCells($startCell . ':' . $endCell);
            }

            // Style the (merged) famille cell
            $sheet->getStyle($startCell)->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '764BA2'],
                ],
                'alignment' => [
                    'horizontal'   => Alignment::HORIZONTAL_CENTER,
                    'vertical'     => Alignment::VERTICAL_CENTER,
                    'textRotation' => 90, // bottom → top vertical text
                    'wrapText'     => false,
                ],
            ]);
        }

        // 4. Data column alignment
        $sheet->getStyle('B:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    private function getUnitLabel($type)
    {
        switch ($type) {
            case 'production': return 'Bloc';
            case 'decoupage':  return 'Sous Bloc';
            case 'finale':     return 'Pièce';
            default:           return 'Unité';
        }
    }

    public function title(): string
    {
        return 'Produits - Stock par Famille';
    }
}
