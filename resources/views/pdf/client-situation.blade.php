<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Situation de compte client – {{ $client->display_name }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
            padding: 15px 70px;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        .logo {
            width: 100px;
            height: auto;
        }

        .logo img {
            max-width: 80px;
            height: auto;
        }

        .title-section {
            text-align: center;
        }

        .page-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            text-underline-offset: 3px;
            margin-bottom: 5px;
        }

        .print-date {
            font-size: 9px;
            text-align: right;
        }

        /* Client box */
        .client-box-wrapper {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }

        .client-box {
            border: 1.5px solid #000;
            padding: 8px 40px;
            min-width: 280px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background-color: #f8f9fa;
        }

        /* Date range */
        .date-range {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-bottom: 15px;
            padding: 5px;
            background-color: #f0f0f0;
        }

        /* Message before table */
        .intro-message {
            text-align: left;
            font-size: 10px;
            margin: 15px 0 10px 0;
            padding: 5px 0;
            font-style: italic;
        }

        /* Ledger table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 10px;
        }

        .ledger-table th {
            border: 1px solid #000;
            padding: 6px 5px;
            background-color: #e8e8e8;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }

        .ledger-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .ledger-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .ledger-table tbody tr.order-row td {
            background-color: #f5f9ff;
        }

        .ledger-table tfoot td {
            border: 1px solid #000;
            padding: 6px 5px;
            font-weight: bold;
            background-color: #e8e8e8;
        }

        /* Column alignments */
        .col-date {
            text-align: center;
            width: 70px;
        }

        .col-designation {
            text-align: left;
            width: auto;
        }

        .col-debit {
            text-align: right;
            width: 85px;
        }

        .col-credit {
            text-align: right;
            width: 85px;
        }

        .col-solde {
            text-align: right;
            width: 85px;
            font-weight: 600;
        }

        .col-rejete {
            text-align: right;
            width: 80px;
        }

        .col-etat {
            text-align: center;
            width: 70px;
        }

        .col-mode {
            text-align: center;
            width: 70px;
        }

        /* Solde coloring */
        .solde-debit {
            color: #dc3545;
            font-weight: bold;
        }

        .solde-credit {
            color: #28a745;
            font-weight: bold;
        }

        .solde-zero {
            color: #6c757d;
        }

        /* Balance summary */
        .balance-summary {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .balance-box {
            border: 1.5px solid #000;
            padding: 10px 18px;
            font-size: 10px;
            min-width: 280px;
            background-color: #f8f9fa;
        }

        .balance-box .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .balance-box .label {
            font-weight: bold;
        }

        .balance-box .value {
            font-weight: bold;
        }

        .balance-box .separator {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        /* Footer */
        .page-footer {
            margin-top: 25px;
            font-size: 8px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 6px;
        }

        /* Print optimization */
        @media print {
            body {
                padding: 10px;
            }

            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="page-header">
        @if ($showLogo)
            <div class="logo">
                <img src="{{ public_path('assets/images/logos/logo.png') }}" alt="Logo">
            </div>
        @else
            <div style="width: 100px;">&nbsp;</div>
        @endif

        <div class="title-section">
            <div class="page-title">SITUATION DE COMPTE CLIENT</div>
        </div>

        <div class="print-date">
            {{ $printDate }}
        </div>
    </div>

    {{-- Client box --}}
    <div class="client-box-wrapper">
        <div class="client-box">{{ $client->display_name }}</div>
    </div>

    {{-- Period --}}
    @if ($dateFrom || $dateTo)
        <div class="date-range">
            <strong>Période :</strong>
            @if ($dateFrom)
                du {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
            @endif
            @if ($dateTo)
                au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @endif
            @if (!$dateFrom && !$dateTo)
                Toutes les périodes
            @endif
        </div>
    @endif

    {{-- Introduction message --}}
    <div class="intro-message">
        <strong>Veuillez trouver ci-après le relevé de votre compte</strong>
    </div>

    {{-- Ledger table --}}
    <table class="ledger-table">
        <thead>
            <tr>
                <th class="col-date">DATE</th>
                <th class="col-designation">DÉSIGNATION</th>
                <th class="col-debit">DÉBIT (DH)</th>
                <th class="col-credit">CRÉDIT (DH)</th>
                <th class="col-solde">SOLDE (DH)</th>
                <th class="col-rejete">REJETÉ</th>
                <th class="col-etat">ÉTAT</th>
                <th class="col-mode">MODE</th>
            </tr>
        </thead>
        <tbody>
            {{-- Opening balance row (always 0) --}}
            <tr style="background-color:#f0f0f0; font-style:italic; color:#444;">
                <td class="col-date">
                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '–' }}
                </td>
                <td class="col-designation">
                    Solde au : {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Début' }}
                </td>
                <td class="col-debit"></td>
                <td class="col-credit"></td>
                <td class="col-solde solde-zero">0,00</td>
                <td class="col-rejete"></td>
                <td class="col-etat"></td>
                <td class="col-mode"></td>
            </tr>

            @forelse ($entries as $entry)
                @php
                    $isOrder = $entry['debit'] > 0;
                    $solde = $entry['solde'];
                    $soldeClass = $solde > 0 ? 'solde-debit' : ($solde < 0 ? 'solde-credit' : 'solde-zero');
                    $soldeStr = number_format(abs($solde), 2, ',', ' ');
                    if ($solde < 0) {
                        $soldeStr = '-' . $soldeStr;
                    }
                @endphp
                <tr class="{{ $isOrder ? 'order-row' : '' }}">
                    <td class="col-date">{{ $entry['date']->format('d/m/Y') }}</td>
                    <td class="col-designation">{{ $entry['designation'] }}</td>
                    <td class="col-debit">
                        @if ($entry['debit'] > 0)
                            {{ number_format($entry['debit'], 2, ',', ' ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-credit">
                        @if ($entry['credit'] > 0)
                            {{ number_format($entry['credit'], 2, ',', ' ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-solde {{ $soldeClass }}">{{ $soldeStr }}</td>
                    <td class="col-rejete">
                        @if ($entry['montant_rejete'] > 0)
                            {{ number_format($entry['montant_rejete'], 2, ',', ' ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-etat">{{ $entry['etat'] ?: '-' }}</td>
                    <td class="col-mode">{{ $entry['mode'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#888;">
                        Aucun mouvement pour cette période.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right; font-weight:bold;">TOTAUX</td>
                <td class="col-debit" style="text-align:right; font-weight:bold;">
                    {{ number_format($totalDebit, 2, ',', ' ') }}
                </td>
                <td class="col-credit" style="text-align:right; font-weight:bold;">
                    {{ number_format($totalCredit, 2, ',', ' ') }}
                </td>
                @php
                    $finalSoldeClass =
                        $finalSolde > 0 ? 'solde-debit' : ($finalSolde < 0 ? 'solde-credit' : 'solde-zero');
                    $finalSoldeStr = number_format(abs($finalSolde), 2, ',', ' ');
                    if ($finalSolde < 0) {
                        $finalSoldeStr = '-' . $finalSoldeStr;
                    }
                @endphp
                <td class="col-solde {{ $finalSoldeClass }}" style="text-align:right; font-weight:bold;">
                    {{ $finalSoldeStr }}
                </td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Balance summary --}}
    @php
        // $client->balance: positive = client has credit (overpaid), negative = client owes us
        $clientBalance = (float) $client->balance;
        $clientOwes    = $clientBalance < 0;
        $clientCredit  = $clientBalance > 0;
    @endphp
    <div class="balance-summary">
        <div class="balance-box">
            <div class="row">
                <span class="label">TOTAL DÉBIT :</span>
                <span class="value">{{ number_format($totalDebit, 2, ',', ' ') }} DH</span>
            </div>
            <div class="row">
                <span class="label">TOTAL CRÉDIT :</span>
                <span class="value">{{ number_format($totalCredit, 2, ',', ' ') }} DH</span>
            </div>
            <div class="separator"></div>
            <div class="row">
                <span class="label">
                    @if ($clientOwes)
                        SOLDE IMPAYÉ (Client doit) :
                    @elseif ($clientCredit)
                        AVANCE CLIENT (Nous devons) :
                    @else
                        SOLDE ACTUEL :
                    @endif
                </span>
                <span class="value {{ $clientOwes ? 'solde-debit' : ($clientCredit ? 'solde-credit' : '') }}">
                    {{ number_format(abs($clientBalance), 2, ',', ' ') }} DH
                </span>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="page-footer">
        Document généré automatiquement le {{ $printDate }} — Situation de compte: {{ $client->display_name }}
    </div>

</body>

</html>
