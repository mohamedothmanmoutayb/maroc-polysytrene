<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Export Produits - Stock par Famille</title>
    <style>
        /* Optimized CSS for better performance */
        body {
            font-family: sans-serif;
            font-size: 8px;
            margin: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #667EEA;
            font-size: 16px;
            margin: 0;
        }

        .filters-info {
            background: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th {
            background: #667EEA;
            color: white;
            font-weight: bold;
            padding: 5px;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        td {
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .product-info {
            font-weight: bold;
        }

        .family-name {
            font-weight: bold;
            color: #667EEA;
        }

        .stock-total {
            font-weight: bold;
            color: #28a745;
        }

        .volume-total {
            font-weight: bold;
            color: #9b59b6;
        }

        .footer {
            text-align: right;
            font-size: 8px;
            color: #666;
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
        }

        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }

        .badge-production {
            background: #007bff;
            color: white;
        }

        .badge-decoupage {
            background: #ffc107;
            color: black;
        }

        .badge-finale {
            background: #28a745;
            color: white;
        }

        .badge-active {
            background: #28a745;
            color: white;
        }

        .badge-inactive {
            background: #dc3545;
            color: white;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RAPPORT DES PRODUITS - STOCK PAR FAMILLE</h1>
        <p>Généré le {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filters-info">
        <strong>Filtres:</strong> Type: {{ request('product_type') ?: 'Tous' }} |
        Statut: {{ request('status') === '1' ? 'Actif' : (request('status') === '0' ? 'Inactif' : 'Tous') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Famille</th>
                <th>Stock Total</th>
                <th>Volume Total (m³)</th>
                <th>Emplacement</th>
                <th>Unité</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                @php
                    $families = $product->familles;
                    $rowCount = max($families->count(), 1);
                    $firstRow = true;
                    $productVolume = $product->volume_m3 ?? 0;
                @endphp

                @if ($families->count() > 0)
                    @foreach ($families as $family)
                        @php
                            $familleStock = $product->familleStocks->where('famille_id', $family->famille_id)->first();
                            $currentQuantity = $familleStock ? $familleStock->current_quantity : 0;
                            $totalVolume = $currentQuantity * $productVolume;
                        @endphp
                        <tr>
                            @if ($firstRow)
                                <td rowspan="{{ $rowCount }}" class="product-info">
                                    {{ $product->product_code }}<br>{{ $product->product_name }}
                                </td>
                                @php $firstRow = false; @endphp
                            @endif

                            <td class="family-name">{{ Str::limit($family->famille_name, 20) }}</td>
                            <td class="stock-total text-center">{{ number_format($currentQuantity, 2) }}</td>
                            <td class="volume-total text-center">
                                {{ $totalVolume > 0 ? number_format($totalVolume, 3) : '-' }}</td>
                            <td>{{ $familleStock ? ($familleStock->location ? Str::limit($familleStock->location, 10) : 'Principal') : '-' }}
                            </td>
                            <td>{{ $product->unit_label }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="product-info">
                            {{ $product->product_code }}<br>{{ Str::limit($product->product_name, 20) }}
                        </td>
                        <td class="text-center" style="color: #999;">-</td>
                        <td class="text-center">0.00</td>
                        <td class="text-center">-</td>
                        <td>-</td>
                        <td>{{ $product->unit_label }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center">Aucun produit trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Produits: {{ $products->count() }} | Familles: {{ $products->sum(fn($p) => $p->familles->count()) }}</p>
    </div>
</body>

</html>
