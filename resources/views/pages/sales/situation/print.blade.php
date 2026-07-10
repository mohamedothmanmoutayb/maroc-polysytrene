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
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            padding: 20px 100px;
        }

        /* ── Header ───────────────────────────────────────────────── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .page-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .print-date {
            font-size: 11px;
            text-align: right;
            padding-top: 3px;
        }

        /* ── Client name box ──────────────────────────────────────── */
        .client-box-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .client-box {
            border: 1.5px solid #000;
            padding: 8px 40px;
            min-width: 240px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ── Date range (optional) ────────────────────────────────── */
        .date-range {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-bottom: 6px;
        }

        /* ── Subtitle ─────────────────────────────────────────────── */
        .subtitle {
            font-size: 11px;
            margin-bottom: 12px;
        }

        /* ── Ledger table ─────────────────────────────────────────── */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .ledger-table th {
            border: 1px solid #000;
            padding: 5px 6px;
            background-color: #e8e8e8;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            white-space: nowrap;
        }

        .ledger-table td {
            border: 1px solid #000;
            padding: 4px 6px;
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
            padding: 5px 6px;
            font-weight: bold;
            background-color: #e8e8e8;
        }

        /* ── Opening balance row ──────────────────────────────────── */
        .opening-row td {
            background-color: #f0f0f0 !important;
            font-style: italic;
            color: #444;
        }

        /* ── Column alignments ────────────────────────────────────── */
        .col-date {
            text-align: center;
            white-space: nowrap;
            width: 80px;
        }

        .col-designation {
            text-align: left;
            white-space: nowrap;
            width: 90px !important;
        }

        .col-debit {
            text-align: right;
            white-space: nowrap;
            width: 90px;
        }

        .col-credit {
            text-align: right;
            white-space: nowrap;
            width: 90px;
        }

        .col-solde {
            text-align: right;
            white-space: nowrap;
            width: 90px;
            font-weight: 600;
        }

        .col-rejete {
            text-align: right;
            white-space: nowrap;
            width: 80px;
        }

        .col-etat {
            text-align: center;
            white-space: nowrap;
            width: 70px;
        }

        .col-mode {
            text-align: center;
            white-space: nowrap;
            width: 70px;
        }

        /* ── Solde colouring ──────────────────────────────────────── */
        .solde-debit {
            color: #c00;
        }

        .solde-credit {
            color: #080;
        }

        .solde-zero {
            color: #555;
        }

        /* ── Final balance box ────────────────────────────────────── */
        .balance-summary {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .balance-box {
            border: 1.5px solid #000;
            padding: 8px 16px;
            font-size: 11px;
            min-width: 260px;
        }

        .balance-box .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .balance-box .label {
            font-weight: bold;
        }

        .balance-box .value {
            font-weight: bold;
        }

        .balance-box .separator {
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        /* ── Footer ───────────────────────────────────────────────── */
        .page-footer {
            margin-top: 30px;
            font-size: 9px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 6px;
        }

        /* ── Print media ──────────────────────────────────────────── */
        @media print {
            body {
                padding: 10px 100px;
            }

            .col-designation {
                width: 90px !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>

<body>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-title">Situation de compte client</div>
        <div class="print-date">{{ $printDate }}</div>
    </div>

    {{-- ── Client name box ─────────────────────────────────────────────── --}}
    <div class="client-box-wrapper">
        <div class="client-box">{{ $client->display_name }}</div>
    </div>

    {{-- ── Optional period label ───────────────────────────────────────── --}}
    @if ($dateFrom || $dateTo)
        <div class="date-range">
            Période :
            @if ($dateFrom)
                du {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
            @endif
            @if ($dateTo)
                au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @endif
        </div>
    @endif

    {{-- ── Subtitle ────────────────────────────────────────────────────── --}}
    <div class="subtitle">Veuillez trouver ci après le relevé de votre compte</div>

    {{-- ── Ledger table ────────────────────────────────────────────────── --}}
    <table class="ledger-table">
        <thead>
            <tr>
                <th class="col-date">Date</th>
                <th class="col-designation">Désignation</th>
                <th class="col-debit">Débit</th>
                <th class="col-credit">Crédit</th>
                <th class="col-solde">Solde</th>
                <th class="col-rejete">Montant Rejeté</th>
                <th class="col-etat">Etat</th>
                <th class="col-mode">Mode</th>
            </tr>
        </thead>
        <tbody>
            {{-- Opening balance row --}}
            <tr class="opening-row">
                <td class="col-date">
                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '–' }}
                </td>
                <td class="col-designation">
                    Solde au :
                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Début' }}
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
                        @endif
                    </td>
                    <td class="col-credit">
                        @if ($entry['credit'] > 0)
                            {{ number_format($entry['credit'], 2, ',', ' ') }}
                        @endif
                    </td>
                    <td class="col-solde {{ $soldeClass }}">{{ $soldeStr }}</td>
                    <td class="col-rejete">
                        @if ($entry['montant_rejete'] > 0)
                            {{ number_format($entry['montant_rejete'], 2, ',', ' ') }}
                        @endif
                    </td>
                    <td class="col-etat">{{ $entry['etat'] }}</td>
                    <td class="col-mode">{{ $entry['mode'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:16px; color:#888;">
                        Aucun mouvement pour cette période.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="col-date"></td>
                <td class="col-designation" style="text-align:right; font-weight:bold;">Total</td>
                <td class="col-debit" style="text-align:right;">
                    {{ number_format($totalDebit, 2, ',', ' ') }}
                </td>
                <td class="col-credit" style="text-align:right;">
                    {{ number_format($totalCredit, 2, ',', ' ') }}
                </td>
                <td class="col-solde {{ $finalSolde > 0 ? 'solde-debit' : ($finalSolde < 0 ? 'solde-credit' : 'solde-zero') }}"
                    style="text-align:right;">
                    {{ number_format(abs($finalSolde), 2, ',', ' ') }}
                </td>
                <td class="col-rejete"></td>
                <td class="col-etat"></td>
                <td class="col-mode"></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Final balance summary ────────────────────────────────────────── --}}
    <div class="balance-summary">
        <div class="balance-box">
            <div class="row">
                <span class="label">Total Débit :</span>
                <span class="value">{{ number_format($totalDebit, 2, ',', ' ') }} DH</span>
            </div>
            <div class="row">
                <span class="label">Total Crédit :</span>
                <span class="value">{{ number_format($totalCredit, 2, ',', ' ') }} DH</span>
            </div>
            <div class="separator"></div>
            <div class="row">
                <span class="label">
                    @if ($finalSolde > 0)
                        Solde impayé (Client doit) :
                    @elseif ($finalSolde < 0)
                        Avance client (Nous devons) :
                    @else
                        Solde :
                    @endif
                </span>
                <span class="value {{ $finalSolde > 0 ? 'solde-debit' : ($finalSolde < 0 ? 'solde-credit' : '') }}">
                    {{ number_format(abs($finalSolde), 2, ',', ' ') }} DH
                </span>
            </div>
        </div>
    </div>

    <div class="page-footer">
        Imprimé le {{ $printDate }} — Situation de compte : {{ $client->display_name }}
    </div>

</body>

</html>
