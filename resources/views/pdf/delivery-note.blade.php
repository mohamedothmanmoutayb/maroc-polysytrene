<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de livraison N° {{ $delivery_note_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: black;
            margin: 0;
            padding: 0px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 8px;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        .header-right {
            text-align: right;
            font-size: 9px;
            margin-top: 20px;
        }

        .header-right div {
            margin-bottom: 2px;
        }

        .header-right .name {
            font-weight: bold;
            color: #000;
            font-size: 10px;
            text-transform: uppercase;
        }

        /* Main boxes table */
        .boxes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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

        /* Date with slashes and bold */
        .date-bold {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* Main table with fixed height */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }

        th {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            background-color: #f0f0f0;
            font-size: 9px;
        }

        td {
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

        /* Style for empty cells in tfoot */
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

        .footer-section {
            margin-top: 20px;
            width: 100%;
        }

        .footer-left {
            width: 100%;
            font-size: 10px;
        }

        .amount-in-words {
            margin: 5px 0 0 0;
            font-weight: bold;
            font-size: 10px;
        }

        .client-balance {
            margin-top: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #dee2e6;
            font-size: 10px;
        }

        .balance-positive {
            color: #dc3545;
            font-weight: bold;
        }

        .balance-negative {
            color: #28a745;
            font-weight: bold;
        }

        .balance-zero {
            color: #6c757d;
            font-weight: bold;
        }

        .price-hidden {
            color: #999;
            font-style: italic;
            font-size: 9px;
        }

        .item-code {
            font-size: 9px;
        }

        .item-name-small {
            font-size: 8px;
        }

        .unit-uppercase {
            text-transform: uppercase;
        }

        /* Ensure tbody maintains structure */
        tbody {
            vertical-align: top;
        }

        .famille-cell {
            vertical-align: middle;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            background-color: #efefef;
            border: 1px solid #000;
            padding: 4px 3px;
        }
    </style>
</head>

<body>
    <!-- Watermark Background Logo -->
    @if ($showLogo)
        <img src="/public/assets/images/logos/logo.png" alt=""
            style="position: fixed; top: 72mm; left: 30mm; width: 60mm; height: auto; opacity: 0.08; z-index: -1;">
    @endif

    <!-- Header with Logo and Date/Time/Name -->
    <div class="header">
        @if ($showLogo)
            <img src="/public/assets/images/logos/logo.png" alt="Logo" class="logo">
        @else
            <div class="logo" style="width: 60px;">&nbsp;</div>
            <div class="header-right">
                <div class="date-bold">{{ $date }} - {{ $time }} - <span
                        class="name">{{ strtoupper($username) }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Info Boxes -->
    <table class="boxes-table" style="width:100%; border-collapse:collapse;">
        <tr>
            <!-- LEFT SIDE - Two stacked boxes -->
            <td width="40%" style="border:none; padding:0; padding-right:10px;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="border:1px solid #000; padding:5px;">
                            <strong>Bon de livraison N° {{ $delivery_note_number }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none; height:10px;"></td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #000; padding:5px;">
                            DATE: <span
                                class="date-bold">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</span>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- RIGHT SIDE - Single box -->
            <td width="60%" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:5px;">
                <div class="client-name"><strong>{{ $client->display_name }}</strong></div>
            </td>
        </tr>
    </table>

    <!-- Items Table with Fixed Height -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="{{ $showPrices ? '12%' : '15%' }}" class="text-center">QUANTITE</th>
                <th
                    width="{{ $displayType === 'volume' ? ($showPrices ? '26%' : '50%') : ($showPrices ? '31%' : '60%') }}">
                    DESIGNATION</th>
                <th width="{{ $showPrices ? '12%' : '15%' }}" class="text-center">FAMILLE</th>
                @if ($displayType === 'volume')
                    <th width="{{ $showPrices ? '14%' : '20%' }}" class="text-center">VOLUME (m³)</th>
                @else
                    <th width="{{ $showPrices ? '9%' : '10%' }}" class="text-center">U</th>
                @endif
                @if ($showPrices)
                    @if ($priceType == 'ttc')
                        <th style="text-align: center" width="18%">PRIX</th>
                        <th style="text-align: center" width="18%">TOTAL</th>
                    @elseif ($priceType == 'ht')
                        <th style="text-align: center" width="18%">PRIX (HT)</th>
                        <th style="text-align: center" width="18%">TOTAL (HT)</th>
                    @elseif ($priceType == 'both')
                        <th style="text-align: center" width="18%" colspan="2">PRIX</th>
                        <th style="text-align: center" width="13%" colspan="2">TOTAL</th>
                    @endif
                @endif
            </tr>
            @if ($showPrices && $priceType == 'both')
                <tr>
                    <th colspan="4"></th>
                    <th style="text-align: center"></th>
                    <th style="text-align: center">HT</th>
                    <th style="text-align: center"></th>
                    <th style="text-align: center">HT</th>
                </tr>
            @endif
        </thead>

        <!-- Items grouped by famille with rowspan -->
        <tbody>
            @php
                // Group items by famille, preserving insertion order
                $groupedItems = collect($itemsData)->groupBy(function ($item) {
                    return !empty($item['familleName']) ? $item['familleName'] : '-';
                });

                $itemCount = count($itemsData);
                $desiredRows = 6;
                $emptyRowsNeeded = max(0, $desiredRows - $itemCount);
            @endphp

            @foreach ($groupedItems as $familleName => $groupItems)
                @foreach ($groupItems as $itemData)
                    <tr>
                        <td style="border-bottom: none; border-top: none;" class="text-center">
                            {{ number_format($itemData['item']->quantity, 2, '.', '') }}
                        </td>
                        <td style="border-bottom: none; border-top: none;">
                            @if ($itemData['item']->item_type == 'raw_material')
                                <!-- For raw materials: show only the name -->
                                <span class="item-name-small">{{ $itemData['displayName'] }}</span>
                            @else
                                <!-- For products: show code -->
                                <span
                                    class="item-code">{{ $itemData['productCode'] ?: $itemData['item']->item_code ?? '' }}</span>
                                {{-- <span class="item-name-small">{{ $itemData['displayName'] }}</span> --}}
                            @endif
                        </td>

                        {{-- Famille cell spans all rows of this groupe --}}
                        @if ($loop->first)
                            <td class="famille-cell" rowspan="{{ $groupItems->count() }}">
                                {{ $familleName }}
                            </td>
                        @endif

                        @if ($displayType === 'volume')
                            <td style="border-bottom: none; border-top: none;" class="text-center">
                                <strong>{{ $itemData['totalVolume'] > 0 ? number_format($itemData['totalVolume'], 4) : '-' }}</strong>
                            </td>
                        @else
                            <td style="border-bottom: none; border-top: none;" class="text-center unit-uppercase">
                                {{ $itemData['productUnit'] ? strtoupper($itemData['productUnit']) : '-' }}
                            </td>
                        @endif

                        @if ($showPrices)
                            @if ($priceType == 'ttc')
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['unitPriceTTC'], 2) }} DH
                                </td>
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['roundedTotalPriceTTC'], 0) }} DH
                                </td>
                            @elseif ($priceType == 'ht')
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['unitPriceHT'], 2) }} DH
                                </td>
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['roundedTotalPriceHT'], 0) }} DH
                                </td>
                            @elseif ($priceType == 'both')
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['unitPriceTTC'], 2) }} DH
                                </td>
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['unitPriceHT'], 2) }} DH
                                </td>
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['roundedTotalPriceTTC'], 0) }} DH
                                </td>
                                <td style="border-bottom: none; border-top: none;" class="text-right">
                                    {{ number_format($itemData['roundedTotalPriceHT'], 0) }} DH
                                </td>
                            @endif
                        @endif
                    </tr>
                @endforeach
            @endforeach

            <!-- Empty rows to fill minimum height -->
            @for ($i = 0; $i < $emptyRowsNeeded; $i++)
                <tr class="empty-row">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    @if ($showPrices)
                        @if ($priceType == 'ttc' || $priceType == 'ht')
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        @elseif ($priceType == 'both')
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        @endif
                    @endif
                </tr>
            @endfor
        </tbody>

        <tfoot>
            <!-- First footer row: Total Quantity and Total Volume/Unit -->
            <tr class="total-row">
                <td class="text-center"><strong>{{ $totalQuantity }}</strong></td>
                <td style="border-top: none;"></td>
                <td style="border-top: none;"></td>{{-- FAMILLE empty --}}
                @if ($displayType === 'volume')
                    <td class="text-center">
                        <strong>{{ $totalVolume > 0 ? number_format($totalVolume, 4) : '-' }}</strong>
                    </td>
                @else
                    <td style="border-top: none;"></td>
                @endif
                @if ($showPrices)
                    @if ($priceType == 'ttc' || $priceType == 'ht')
                        <td style="border-top: none;"></td>
                        <td style="border-top: none;"></td>
                    @elseif ($priceType == 'both')
                        <td style="border-top: none;"></td>
                        <td style="border-top: none;"></td>
                        <td style="border-top: none;"></td>
                        <td style="border-top: none;"></td>
                    @endif
                @endif
            </tr>

            <!-- Second footer row: Total Amounts -->
            @if ($showPrices)
                @if ($priceType == 'ttc')
                    <tr class="total-row">
                        <td style="border:none" colspan="3"></td>
                        <td colspan="2" class="text-center"><strong>TOTAL</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalAmountTTC, 0) }} DH</strong></td>
                    </tr>
                @elseif ($priceType == 'ht')
                    <tr class="total-row">
                        <td style="border:none" colspan="3"></td>
                        <td colspan="2" class="text-center"><strong>TOTAL (HT)</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalAmountHT, 0) }} DH</strong></td>
                    </tr>
                @elseif ($priceType == 'both')
                    <tr class="total-row">
                        <td style="border:none" colspan="3"></td>
                        <td colspan="2" class="text-center"><strong>TOTAL</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalAmountTTC, 0) }} DH</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalAmountHT, 0) }} DH</strong></td>
                    </tr>
                    <tr class="total-row">
                        <td style="border:none" colspan="7"></td>
                        <td class="text-right">
                            <small>(TVA {{ $tvaRate }}%)</small>
                        </td>
                    </tr>
                @endif
            @endif
        </tfoot>

    </table>

    @if ($showPrices)
        <div class="footer-section">
            <div class="footer-left">
                @if ($priceType == 'ttc')
                    <p style="margin: 0;">Arrêtée le présent bon de livraison à la somme de :</p>
                    <div class="amount-in-words">
                        {{ ucfirst($numberToFrench($totalAmountTTC)) }} DIRHAMS
                    </div>
                @elseif ($priceType == 'ht')
                    <p style="margin: 0;">Arrêtée le présent bon de livraison à la somme de :</p>
                    <div class="amount-in-words">
                        {{ ucfirst($numberToFrench($totalAmountHT)) }} DIRHAMS (HT)
                    </div>
                @elseif ($priceType == 'both')
                    <p style="margin: 0;">Arrêtée le présent bon de livraison :</p>
                    <div class="amount-in-words">
                        HT: {{ ucfirst($numberToFrench($totalAmountHT)) }} DIRHAMS
                    </div>
                    <div class="amount-in-words" style="margin-top: 5px;">
                        TTC: {{ ucfirst($numberToFrench($totalAmountTTC)) }} DIRHAMS
                    </div>
                @endif

                <!-- Client Balance Section -->
                <div class="client-balance">
                    <strong>Solde du client :</strong>
                    <span class="{{ $balance_class }}">
                        {{ $balance_formatted }}
                    </span>
                </div>
            </div>
        </div>
    @else
        <!-- Footer without prices -->
        <div class="footer-section">
            <div class="footer-left">
                <p style="margin: 0;"><strong>Arrêtée le présent bon de livraison</strong></p>

                <!-- Client Balance Section -->
                <div class="client-balance" style="margin-top: 10px;">
                    <strong>Solde du client :</strong>
                    <span class="{{ $balance_class }}">
                        {{ $balance_formatted }}
                    </span>
                    {{-- <span style="margin-left: 10px; font-style: italic;">
                        (Client {{ $balance_status }})
                    </span> --}}
                </div>
            </div>
        </div>
    @endif
</body>

</html>
