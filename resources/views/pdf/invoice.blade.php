<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture N° {{ $invoice_number_formatted }}</title>
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

        /* Background entête - fixed to A4 size (same as devis). Note: dompdf
           inserts a phantom blank leading page when a full-page element uses
           position:fixed, so this stays absolute (single page, background
           on that one page only, as intended). */
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

        .header-section {
            flex-shrink: 0;
            /* padding-top, not margin-top: dompdf's flexbox implementation
               miscalculates a top margin on a flex-shrink:0 child inside a
               flex-direction:column container, forcing a spurious second
               page. padding avoids that bug (same technique as devis). */
            padding-top: 190px;
            margin-bottom: 15px;
        }

        /* Info boxes table */
        .boxes-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .boxes-table td {
            vertical-align: top;
        }

        .client-name {
            font-size: 14px;
            font-weight: bold;
            word-wrap: break-word;
            width: 100%;
        }

        .client-ice {
            font-size: 10px;
            font-weight: normal;
        }

        .date-bold {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .table-container {
            flex: 1;
            overflow: hidden;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }

        .items-table th {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            background-color: #f0f0f0;
            font-size: 9px;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        /* Style for empty rows - invisible but maintain height */
        tr.empty-row td {
            border: none;
            padding: 5px;
            background-color: transparent;
            height: 20px;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
        }

        tfoot td {
            border: 1px solid #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
        }

        .item-code {
            font-size: 9px;
        }

        .unit-uppercase {
            text-transform: uppercase;
        }

        tbody {
            vertical-align: top;
        }

        .items-table tbody {
            border-bottom: 1px solid;
            vertical-align: top;
        }

        .footer-section {
            margin-top: 15px;
            width: 100%;
            font-size: 10px;
            flex-shrink: 0;
        }

        .amount-in-words {
            margin: 5px 0 0 0;
            font-weight: bold;
            font-size: 10px;
        }

        /* Signature, below the "Arrêtée la présente..." text */
        .signature-section {
            margin-top: 15px;
            text-align: center;
        }

        .terms-conditions {
            margin-top: 10px;
            font-size: 8px;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
        }

        .col-designation {
            width: 55%;
            text-align: left;
        }

        .col-unit {
            width: 10%;
        }

        .col-quantity {
            width: 9%;
        }

        .col-price {
            width: 10%;
        }

        .col-total {
            width: 10%;
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
            <!-- Info Boxes -->
            <table class="boxes-table">
                <tr>
                    <!-- LEFT SIDE - Two stacked boxes -->
                    <td width="40%" style="border:none; padding:0; padding-right:10px;">
                        <table style="width:100%; border-collapse:collapse;">
                            <tr>
                                <td style="border:1px solid #000; border-radius:4px; padding:5px;">
                                    <strong>Facture N° : {{ $invoice_number_formatted }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="border:none; height:10px;"></td>
                            </tr>
                            <tr>
                                <td class="date-bold" style="border:1px solid #000; border-radius:4px; padding:5px;">
                                    DATE : <span
                                        >{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- RIGHT SIDE - Single box -->
                    <td width="60%"
                        style="border:1px solid #000; border-radius:4px; text-align:center; vertical-align:middle; padding:5px;">
                        <div class="client-name"><strong>{{ $client->display_name }}</strong></div>
                        @if ($client->ice)
                            <div class="client-ice">ICE : {{ $client->ice }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-designation">DESIGNATION</th>
                        @if ($displayType === 'volume')
                            <th class="col-unit text-center">VOLUME (m³)</th>
                        @else
                            <th class="col-unit text-center">UNITE</th>
                        @endif
                        <th class="col-quantity text-center">QUANTITE</th>
                        <th class="col-price text-center">PRIX</th>
                        <th class="col-total text-center">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $itemCount = count($items);
                        $desiredRows = 4;
                        $emptyRowsNeeded = max(0, $desiredRows - $itemCount);
                    @endphp

                    @foreach ($items as $item)
                        @php
                            $productUnit = '';
                            $volumePerUnit = 0;
                            $totalVolume = 0;

                            if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                                $product = \App\Models\Product::find($item->item_id);
                                if ($product) {
                                    $productUnit = $product->unit_of_measure;
                                    $volumePerUnit = $product->volume_per_unit ?? ($product->total_volume ?? 0);
                                    $totalVolume = $item->quantity * $volumePerUnit;
                                }
                            }
                        @endphp
                        <tr>
                            <td style="border-bottom: none; border-top: none;">
                                <span class="item-code">{{ trim($item->item_name) }}</span>
                            </td>
                            @if ($displayType === 'volume')
                                <td style="border-bottom: none; border-top: none;" class="text-center">
                                    {{ $totalVolume > 0 ? number_format($totalVolume, 2, '.', '') : '-' }}
                                </td>
                            @else
                                <td style="border-bottom: none; border-top: none;" class="text-center unit-uppercase">
                                    {{ strtoupper($productUnit) }}
                                </td>
                            @endif
                            <td style="border-bottom: none; border-top: none;" class="text-center">
                                {{ number_format($item->quantity, 2, '.', '') }}
                            </td>
                            <td style="border-bottom: none; border-top: none;" class="text-right">
                                {{ number_format($item->unit_price, 2, '.', '') }} DH
                            </td>
                            <td style="border-bottom: none; border-top: none;" class="text-right">
                                {{ number_format($item->total_price, 2, '.', '') }} DH
                            </td>
                        </tr>
                    @endforeach

                    <!-- Empty rows to fill minimum height -->
                    @for ($i = 0; $i < $emptyRowsNeeded; $i++)
                        <tr class="empty-row">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    @php
                        $tvaRate = 0.2;
                        $finalTTC = (float) $invoice->final_amount;
                        $totalHT = $finalTTC / (1 + $tvaRate);
                        $totalTVA = $finalTTC - $totalHT;
                    @endphp

                    {{-- Totals only sit under Quantité/Prix (label) + Total (value);
                         Désignation and Unité stay blank instead of spanning the full table. --}}
                    @if ($invoice->discount > 0)
                        <tr class="total-row">
                            <td style="border:none"></td>
                            <td style="border:none"></td>
                            <td colspan="2" class="text-right">Remise</td>
                            <td class="text-right">- {{ number_format($invoice->discount, 2, '.', '') }} DH</td>
                        </tr>
                    @endif

                    <tr class="total-row">
                        <td style="border:none"></td>
                        <td style="border:none"></td>
                        <td colspan="2" class="text-center">TOTAL H.T</td>
                        <td class="text-right">{{ number_format($totalHT, 2, '.', '') }} DH</td>
                    </tr>
                    <tr class="total-row">
                        <td style="border:none"></td>
                        <td style="border:none"></td>
                        <td colspan="2" class="text-center">TVA 20%</td>
                        <td class="text-right">{{ number_format($totalTVA, 2, '.', '') }} DH</td>
                    </tr>
                    <tr class="total-row">
                        <td style="border:none"></td>
                        <td style="border:none"></td>
                        <td colspan="2" class="text-center">TOTAL TTC</td>
                        <td class="text-right">{{ number_format($finalTTC, 2, '.', '') }} DH</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer with Amount in Words -->
        <div class="footer-section">
            <p style="margin: 0;">Arrêtée la présente facture à la somme de :</p>
            <div class="amount-in-words">
                {{ ucfirst($numberToFrench($invoice->final_amount)) }} DIRHAMS TTC
            </div>
        </div>

        <!-- Signature, right below "Arrêtée la présente..." -->
        <div class="signature-section">
            @if ($cacherBase64)
                <img src="{{ $cacherBase64 }}" alt="Signature"
                    style="height: 200px; width: auto; max-width: 200px; object-fit: contain;"
                    onerror="this.style.display='none'">
            @endif
        </div>

        <!-- Terms and Conditions -->
        @if ($invoice->terms_conditions)
            <div class="terms-conditions">
                Conditions : {{ $invoice->terms_conditions }}
            </div>
        @endif
    </div>
</body>

</html>
