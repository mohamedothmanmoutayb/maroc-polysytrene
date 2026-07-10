<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis N° {{ $quote_number_formatted }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: black;
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
            background-color: white;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Background entête - Fixed to A4 size */
        .background-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: -1;
            overflow: hidden;
        }

        .background-header img {
            width: 210mm;
            height: 297mm;
            object-fit: cover;
            display: block;
        }

        /* Main Container - Exactly A4 size */
        .container {
            width: 190mm;
            height: 277mm;
            margin: 10mm auto;
            position: relative;
            z-index: 1;
            padding: 0 10mm;
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header Section */
        .header-section {
            padding-top: 80px;
            flex-shrink: 0;
        }

        /* Top header with date */
        .top-header {
            position: relative;
            height: 60px;
            margin-bottom: 20px;
            margin-top: 60px;
        }

        .date-info {
            position: absolute;
            top: 0;
            right: 0;
            width: 80mm;
            text-align: right;
        }

        .date-info p {
            margin: 0;
            font-size: 14px;
            color: #000000;
        }

        .underline-with-border {
            border-bottom: 1px solid #000;
            display: inline-block;
            line-height: 1;
            padding-bottom: 2px;
        }

        /* Client info under the date, left aligned to match ICE */
        .client-info {
            text-align: left;
            margin-top: 6px;
        }

        .client-info p {
            margin: 0;
            font-size: 11px;
            font-weight: bold;
            color: #000000;
        }

        .client-info .client-ice {
            font-size: 10px;
            font-weight: normal;
        }

        /* Title section without borders */
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .devis-number {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .table-container {
            flex: 1;
            overflow: hidden;
            margin-bottom: 10px;
        }

        /* Normal table styling */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 12px;
        }

        .items-table th {
            border: none;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0;
            font-size: 13px;
        }

        .items-table td {
            border: none;
            border-bottom: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .item-code {
            font-weight: bold;
            font-size: 12px;
        }

        .unit-uppercase {
            text-transform: uppercase;
            text-align: center;
            font-size: 12px;
        }

        /* Ensure tbody maintains structure */
        tbody {
            vertical-align: top;
        }

        .terms-conditions {
            margin-top: 10px;
            font-size: 8px;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            text-align: left;
            width: 100%;
            flex-shrink: 0;
        }

        .price-column {
            text-align: right;
            font-weight: normal;
        }

        /* Column widths */
        .col-quantity {
            width: 15%;
        }

        .col-designation {
            width: 55%;
            text-align: left;
        }

        .col-unit {
            width: 15%;
        }

        .col-total {
            width: 15%;
        }

        /* Print adjustments */
        @media print {
            body {
                width: 210mm;
                height: 297mm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                width: 190mm;
                height: 277mm;
                margin: 10mm auto;
            }

            .background-header {
                width: 210mm;
                height: 297mm;
            }

            .background-header img {
                width: 210mm;
                height: 297mm;
            }
        }
    </style>
</head>

<body>
    <!-- Background entête - Fixed to A4 size -->
    <div class="background-header">
        <img src="{{ $enteteBase64 }}" alt="Entête">
    </div>

    <div class="container">
        <div class="header-section">
            <!-- Top Header with Date -->
            <div class="top-header">
                <div class="date-info">
                    <p class="underline-with-border">Agadir le : {{ $date }}</p>
                    <div class="client-info">
                        <p>{{ $client->display_name }}</p>
                        @if ($client->ice)
                            <p class="client-ice">ICE : {{ $client->ice }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Title section without borders -->
            <div class="title-section">
                <div class="devis-number">DEVIS N° {{ $quote_number_formatted }}</div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <!-- Items Table - Normal styling -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-quantity">QTE</th>
                        <th class="col-designation">DESIGNATION</th>
                        @if ($displayType === 'volume')
                            <th class="col-unit">VOLUME</th>
                        @else
                            <th class="col-unit">UNITE</th>
                        @endif
                        <th class="col-total">TOTALE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            // Get product details if available
                            $productCode = '';
                            $productUnit = '';
                            $volumePerUnit = 0;
                            $totalVolume = 0;

                            if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                                $product = \App\Models\Product::find($item->item_id);
                                if ($product) {
                                    $productCode = $product->product_code;
                                    $productUnit = $product->unit_of_measure;
                                    $volumePerUnit = $product->volume_per_unit ?? ($product->total_volume ?? 0);
                                    $totalVolume = $item->quantity * $volumePerUnit;
                                }
                            }

                            // Round up unit price to nearest integer
                            $roundedUnitPrice = ceil($item->unit_price);
                            // Calculate rounded total price
                            $roundedTotalPrice = $roundedUnitPrice * $item->quantity;
                        @endphp
                        <tr>
                            <td class="text-center">
                                {{ number_format($item->quantity, 0) }}
                            </td>
                            <td class="text-left">
                                <span class="item-code">{{ $productCode ?: $item->item_name }}</span>
                            </td>
                            @if ($displayType === 'volume')
                                <td class="text-center">
                                    {{ $totalVolume > 0 ? number_format($totalVolume, 2) : '-' }}
                                </td>
                            @else
                                <td class="text-center unit-uppercase">
                                    {{ strtoupper($productUnit) }}
                                </td>
                            @endif
                            <td class="text-right price-column">
                                <strong>{{ number_format($roundedTotalPrice, 0) }} DH</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Observation -->
        @if ($quotation->observation)
            <div class="terms-conditions">
                <strong>Observation :</strong> {{ $quotation->observation }}
            </div>
        @endif

        <!-- Terms and Conditions -->
        @if ($quotation->terms_conditions)
            <div class="terms-conditions">
                <strong>Conditions :</strong> {{ $quotation->terms_conditions }}
            </div>
        @endif
    </div>
</body>

</html>
