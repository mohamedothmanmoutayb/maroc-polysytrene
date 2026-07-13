<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Rapport Employé - {{ $employee->full_name }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #000;
            background: #fff;
            padding: 10px 28px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #000;
        }

        .page-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .page-subtitle {
            font-size: 10px;
            margin-top: 2px;
        }

        .print-meta {
            font-size: 8px;
            text-align: right;
        }

        .print-meta div {
            margin-bottom: 2px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding-bottom: 2px;
            margin: 14px 0 6px 0;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.info-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
            text-align: center;
        }

        table.info-table td.label {
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            width: 25%;
            padding: 4px 6px;
        }

        table.info-table td.value {
            font-size: 11px;
            font-weight: bold;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        table.data-table th {
            border: 1px solid #000;
            padding: 5px 4px;
            font-weight: bold;
            text-align: center;
            font-size: 7px;
            text-transform: uppercase;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            text-align: center;
            vertical-align: middle;
        }

        table.data-table td.left {
            text-align: left;
        }

        table.data-table td.right {
            text-align: right;
        }

        .progress-bar-bg {
            width: 50px;
            height: 7px;
            border: 1px solid #000;
            display: inline-block;
            vertical-align: middle;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: #000;
        }

        .text-muted {
            color: #555;
        }

        .footer {
            text-align: center;
            font-size: 7px;
            color: #000;
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #000;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-title">Rapport de Production Employé</div>
            <div class="page-subtitle">
                <strong>{{ $employee->full_name }}</strong>
                @if($employee->phone)
                    &nbsp;|&nbsp; {{ $employee->phone }}
                @endif
                @if($employee->email)
                    &nbsp;|&nbsp; {{ $employee->email }}
                @endif
            </div>
            <div class="page-subtitle" style="font-size:9px;">Période : {{ $dateRange }}</div>
        </div>
        <div class="print-meta">
            <div>Imprimé le : {{ $date }} à {{ $time }}</div>
            <div>Par : {{ $username }}</div>
        </div>
    </div>

    <!-- Statistics Cards - 4 per row -->
    <div class="section-title">Statistiques</div>
    <table class="info-table">
        <tr>
            <td class="label">Total Ordres</td>
            <td class="value">{{ $totalOrders }}</td>
            <td class="label">Terminés</td>
            <td class="value">{{ $completedOrders }}</td>
            <td class="label">En Cours</td>
            <td class="value">{{ $inProgressOrders }}</td>
            <td class="label">En Attente</td>
            <td class="value">{{ $pendingOrders }}</td>
        </tr>
        <tr>
            <td class="label">Annulés</td>
            <td class="value">{{ $cancelledOrders }}</td>
            <td class="label">Qté à produire</td>
            <td class="value">{{ number_format($totalQuantityToProduce, 0, ',', ' ') }}</td>
            <td class="label">Qté Produite</td>
            <td class="value">{{ number_format($totalProduced, 0, ',', ' ') }}</td>
            <td class="label">Qté Défect.</td>
            <td class="value">{{ number_format($totalDefective, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td class="label">Volume Produit</td>
            <td class="value">{{ number_format($totalVolume, 4) }} m³</td>
            <td class="label">Chutes Recycl.</td>
            <td class="value">{{ number_format($totalRecyclableVolume, 4) }} m³</td>
            <td class="label">Chutes Perdues</td>
            <td class="value">{{ number_format($totalWasteVolume, 4) }} m³</td>
            <td class="label">Taux Défaut</td>
            <td class="value">
                @if($totalProduced > 0)
                    {{ number_format(($totalDefective / max($totalProduced + $totalDefective, 1)) * 100, 1) }} %
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <!-- Production Orders Table -->
    <div class="section-title">Détail des Ordres de Production</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>N° Ordre</th>
                <th>Produit</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Qté Demandée</th>
                <th>Qté Produite</th>
                <th>Qté Défaut</th>
                <th>Progression</th>
                <th>Volume (m³)</th>
                <th>Chutes (m³)</th>
                <th>Date Début</th>
                <th>Fin Prévue</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                @php
                    $statusLabels = [
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ];
                    $typeLabels = [
                        'type1' => 'Production',
                        'type2' => 'Découpage',
                        'type3' => 'Conversion',
                        'type4' => 'Transformation',
                        'type5' => 'Chutes PF',
                        'decoupage' => 'Découpage',
                    ];
                    $progress = round($order->progress ?? 0, 1);
                @endphp
                <tr>
                    <td class="left"><strong>{{ $order->order_number }}</strong></td>
                    <td class="left">{{ $order->product->product_name ?? $order->product_name ?? 'N/A' }}</td>
                    <td>{{ $typeLabels[$order->production_type] ?? $order->production_type }}</td>
                    <td>{{ $statusLabels[$order->status] ?? $order->status }}</td>
                    <td class="right">{{ number_format($order->quantity_to_produce, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($order->produced_qty, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($order->defective_qty, 0, ',', ' ') }}</td>
                    <td>
                        {{ number_format($progress, 0) }}%
                    </td>
                    <td class="right">{{ $order->output_volume > 0 ? number_format($order->output_volume, 4) : '-' }}</td>
                    <td class="right">{{ $order->waste_total > 0 ? number_format($order->waste_total, 4) : '-' }}</td>
                    <td>{{ $order->start_date ? $order->start_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $order->expected_completion_date ? $order->expected_completion_date->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center; padding:20px;">Aucun ordre de production trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Rapport généré le {{ $date }} à {{ $time }} par {{ $username }} &nbsp;|&nbsp; {{ $totalOrders }} ordre(s) de production
    </div>
</body>

</html>
