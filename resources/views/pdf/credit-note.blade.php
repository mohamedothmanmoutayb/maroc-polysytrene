<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avoir N° {{ $credit_note_number_formatted }}</title>
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
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            margin-top: 60px;
        }

        .date-info {
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

        /* Title section without borders */
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .avoir-number {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .client-name-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 0;
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
            font-size: 9px;
        }

        .items-table th {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0;
            font-size: 10px;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
        }

        .col-designation {
            text-align: left;
        }

        /* Empty rows styling */
        tr.empty-row td {
            border-top: none !important;
            border-bottom: none !important;
            background-color: transparent;
            height: 15px;
        }

        tr.empty-row td {
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }

        /* Style for tfoot */
        tfoot td {
            border: 1px solid #000;
            padding: 6px;
        }

        .total-row td {
            font-size: 12px;
            font-weight: bold;
            padding: 8px;
            background-color: transparent;
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

        /* Footer with Amount in Words */
        .footer-section {
            margin-top: 10px;
            width: 100%;
            text-align: center;
            flex-shrink: 0;
        }

        .amount-in-words-title {
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-align: left
        }

        .amount-in-words {
            margin: 0 auto;
            font-weight: bold;
            font-size: 12px;
            text-decoration: underline;
            padding: 3px 10px;
            display: inline-block;
        }

        .item-code {
            font-weight: bold;
            font-size: 9px;
        }

        .unit-uppercase {
            text-transform: uppercase;
            text-align: center;
            font-size: 9px;
        }

        /* Ensure tbody maintains structure */
        tbody {
            vertical-align: top;
        }

        .reason-section {
            margin-top: 10px;
            font-size: 9px;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            text-align: left;
            width: 100%;
            flex-shrink: 0;
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
    @if ($enteteBase64)
        <div class="background-header">
            <img src="{{ $enteteBase64 }}" alt="Entête">
        </div>
    @endif

    <div class="container">
        <div class="header-section">
            <!-- Top Header with Date -->
            <div class="top-header">
                <div class="date-info">
                    <p class="underline-with-border">Agadir le : {{ $date }}</p>
                </div>
            </div>

            <!-- Title section without borders -->
            <div class="title-section">
                <div class="avoir-number">AVOIR N° {{ $credit_note_number_formatted }}</div>
                <div class="client-name-title">{{ $client->display_name }}</div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <!-- Items Table - Normal styling -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-quantity">QTÉ</th>
                        <th class="col-designation">DÉSIGNATION</th>
                        <th class="col-unit">PRIX UNIT.</th>
                        <th class="col-total">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $itemCount = count($items);
                        // Calculate rows needed to fill the page
                        $baseRows = 12;
                        $emptyRowsNeeded = max(0, $baseRows - $itemCount);
                        $roundedTotalAmount = 0;
                    @endphp

                    @foreach ($items as $item)
                        @php
                            // Get product details if available
                            $productCode = '';
                            $productUnit = '';

                            if ($item->item_type != 'raw_material' && class_exists('App\Models\Product')) {
                                $product = \App\Models\Product::find($item->item_id);
                                if ($product) {
                                    $productCode = $product->product_code;
                                    $productUnit = $product->unit_of_measure;
                                }
                            }

                            // Round up unit price to nearest integer
                            $roundedUnitPrice = ceil($item->unit_price);
                            // Calculate rounded total price
                            $roundedTotalPrice = $roundedUnitPrice * $item->quantity;
                            // Add to order total
                            $roundedTotalAmount += $roundedTotalPrice;
                        @endphp
                        <tr>
                            <td class="text-center">
                                {{ number_format($item->quantity, 2, '.', '') }}
                            </td>
                            <td class="text-left">
                                <span class="item-code">{{ $productCode ?: $item->item_name }}</span>
                                @if ($item->reason)
                                    <br><small class="text-muted" style="font-size: 7px;">Motif:
                                        {{ $item->reason }}</small>
                                @endif
                            </td>
                            <td class="text-right">
                                {{ number_format($roundedUnitPrice, 0) }} DH
                            </td>
                            <td class="text-right">
                                <strong>{{ number_format($roundedTotalPrice, 0) }} DH</strong>
                            </td>
                        </tr>
                    @endforeach

                    <!-- Empty rows -->
                    @for ($i = 0; $i < $emptyRowsNeeded; $i++)
                        <tr class="empty-row">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-center"><strong>TOTAL AVOIR</strong></td>
                        <td class="text-right"><strong>{{ number_format($creditNote->total_amount, 0) }} DH</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer with Amount in Words -->
        <div class="footer-section">
            <p class="amount-in-words-title"><strong>Arrêté le présent avoir à la somme de :</strong></p>
            <div class="amount-in-words">
                {{ ucfirst($numberToFrench($creditNote->total_amount)) }} DIRHAMS
            </div>
        </div>

        <!-- Reason Section -->
        @if ($creditNote->reason)
            <div class="reason-section">
                <strong>Raison :</strong> {{ $creditNote->reason }}
                @if ($creditNote->notes)
                    <br><strong>Notes :</strong> {{ $creditNote->notes }}
                @endif
                @if ($creditNote->salesOrder)
                    <br><strong>Commande associée :</strong> {{ $creditNote->salesOrder->order_number }}
                @endif
            </div>
        @endif
    </div>
</body>

</html>
