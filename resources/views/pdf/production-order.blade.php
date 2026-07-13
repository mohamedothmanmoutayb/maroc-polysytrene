<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordre de Production N° {{ $order->order_number }}</title>
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
            border-bottom: 2px solid #000;
        }

        .logo {
            width: 110px;
        }

        .logo img {
            max-width: 100px;
            height: auto;
        }

        .title-section {
            text-align: center;
        }

        .page-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .page-subtitle {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }

        .print-meta {
            font-size: 8px;
            text-align: right;
            color: #000;
        }

        .print-meta div {
            margin-bottom: 2px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin: 14px 0 6px 0;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        table.info-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        table.info-table td.label {
            width: 32%;
            font-weight: bold;
            background: #fff;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        table.data-table th {
            border: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            text-align: left;
            font-size: 9px;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 4px 5px;
        }

        table.data-table tfoot td {
            font-weight: bold;
            border-top: 2px solid #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .two-col {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .two-col > tbody > tr > td {
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .two-col .pad-left {
            padding-left: 10px;
        }

        .pill-box {
            border: 1.5px solid #000;
            padding: 6px 10px;
            display: inline-block;
            font-weight: bold;
        }

        .muted {
            color: #333;
            font-style: italic;
            font-size: 9px;
        }

        .no-data {
            border: 1px solid #000;
            padding: 8px;
            font-style: italic;
            font-size: 9px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .signature-table td {
            border: 1px solid #000;
            width: 33.33%;
            height: 60px;
            vertical-align: top;
            padding: 5px;
            font-size: 9px;
        }

        .signature-table .sig-label {
            font-weight: bold;
            text-transform: uppercase;
        }

        .page-footer {
            margin-top: 18px;
            font-size: 8px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        @page {
            size: A4;
            margin: 12mm 10mm;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="page-header">
        <div class="logo">
            <img src="{{ public_path('assets/images/logos/logo.png') }}" alt="Logo">
        </div>
        <div class="title-section">
            <div class="page-title">Ordre de Production</div>
            <div class="page-subtitle">N° {{ $order->order_number }}</div>
        </div>
        <div class="print-meta">
            <div>Imprimé le {{ $date }} à {{ $time }}</div>
            <div>Par {{ strtoupper($username) }}</div>
        </div>
    </div>

    {{-- General info + planning --}}
    <table class="two-col">
        <tr>
            <td width="50%">
                <table class="info-table">
                    <tr>
                        <td class="label">Numéro</td>
                        <td><strong>{{ $order->order_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Type de production</td>
                        <td>
                            @switch($order->production_type)
                                @case('type1')
                                    Production Directe
                                    @break
                                @case('type2')
                                    Découpage
                                    @break
                                @case('type3')
                                    Conversion
                                    @break
                                @case('type4')
                                    Transformation
                                    @break
                                @case('type5')
                                    Chutes → Produits Finis
                                    @break
                                @default
                                    {{ $order->production_type }}
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Quantité à produire</td>
                        <td>{{ number_format($order->quantity_to_produce, 2, ',', ' ') }} unités</td>
                    </tr>
                    <tr>
                        <td class="label">Priorité</td>
                        <td>
                            @switch($order->priority)
                                @case('low')
                                    Basse
                                    @break
                                @case('medium')
                                    Moyenne
                                    @break
                                @case('high')
                                    Haute
                                    @break
                                @case('urgent')
                                    Urgente
                                    @break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Responsable</td>
                        <td>{{ $order->responsibleEmployee->full_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Créé par / le</td>
                        <td>{{ $order->creator->username ?? ($order->created_by ? 'Utilisateur #' . $order->created_by : 'N/A') }}
                            — {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </td>
            <td width="50%" class="pad-left">
                <table class="info-table">
                    <tr>
                        <td class="label">Statut</td>
                        <td>
                            @switch($order->status)
                                @case('pending')
                                    En attente
                                    @break
                                @case('approved')
                                    Approuvé
                                    @break
                                @case('in_progress')
                                    En cours
                                    @break
                                @case('completed')
                                    Terminé
                                    @break
                                @case('cancelled')
                                    Annulé
                                    @break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Date début</td>
                        <td>{{ $order->start_date ? $order->start_date->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date fin prévue</td>
                        <td>{{ $order->expected_completion_date ? $order->expected_completion_date->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date fin réelle</td>
                        <td>{{ $order->actual_completion_date ? $order->actual_completion_date->format('d/m/Y') : 'Pas encore terminée' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Progression</td>
                        <td>{{ number_format(min($progress, 100), 1) }}% ({{ number_format($totalProduced, 2, ',', ' ') }} / {{ number_format($order->quantity_to_produce, 2, ',', ' ') }} unités)</td>
                    </tr>
                    @if ($order->cancelled_at)
                        <tr>
                            <td class="label">Annulé le</td>
                            <td>{{ \Carbon\Carbon::parse($order->cancelled_at)->format('d/m/Y H:i') }}
                                @if ($order->cancellation_reason)
                                    — {{ $order->cancellation_reason }}
                                @endif
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Source products / Final products (type2, type3, type5) --}}
    @if (in_array($order->production_type, ['type2', 'type3', 'type5']))
        <div class="section-title">{{ $order->production_type === 'type5' ? 'Chutes Utilisées & Produits à Produire' : 'Produits Sources & Produits à Produire' }}</div>
        <table class="two-col">
            <tr>
                <td width="48%">
                    <div style="font-weight:bold; font-size:9px; margin-bottom:3px;">
                        {{ $order->production_type === 'type5' ? 'Chutes Utilisées' : 'Produits Sources' }}
                    </div>
                    @if ($order->production_type === 'type5')
                        <table class="data-table">
                            <tr>
                                <th>Volume de chutes alloué</th>
                                <td class="text-right">{{ number_format($order->chutes_volume ?? 0, 4) }} m³</td>
                            </tr>
                            <tr>
                                <th>Volume produits prévu</th>
                                <td class="text-right">{{ number_format(($order->chutes_volume ?? 0) - ($order->waste_volume ?? 0), 4) }} m³</td>
                            </tr>
                            <tr>
                                <th>Chute résiduelle estimée</th>
                                <td class="text-right">{{ number_format($order->waste_volume ?? 0, 4) }} m³</td>
                            </tr>
                        </table>
                    @elseif (!empty($sourceProducts))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Produit</th>
                                    <th class="text-right">Quantité</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sourceProducts as $sp)
                                    <tr>
                                        <td>{{ $sp['product_code'] ?: '—' }}</td>
                                        <td>{{ $sp['product_name'] }}</td>
                                        <td class="text-right">{{ number_format($sp['quantity'], 0, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-data">Aucun produit source enregistré.</div>
                    @endif
                </td>
                <td width="4%"></td>
                <td width="48%">
                    <div style="font-weight:bold; font-size:9px; margin-bottom:3px;">Produits à Produire</div>
                    @if ($subProducts->count() > 0)
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Produit</th>
                                    <th class="text-right">Planifié</th>
                                    <th class="text-right">Produit</th>
                                    <th class="text-right">Restant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subProducts as $sp)
                                    @php
                                        $planned = $sp['planned_quantity'] ?? $sp->quantity_to_produce ?? 0;
                                        $produced = $sp['produced_quantity'] ?? 0;
                                        $remaining = $sp['remaining_quantity'] ?? max(0, $planned - $produced);
                                    @endphp
                                    <tr>
                                        <td>{{ $sp['product_code'] ?? $sp->product_code ?? '—' }}</td>
                                        <td>{{ $sp['product_name'] ?? $sp->product_name ?? '—' }}</td>
                                        <td class="text-right">{{ number_format($planned) }}</td>
                                        <td class="text-right">{{ number_format($produced) }}</td>
                                        <td class="text-right">{{ number_format($remaining) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-data">Aucun produit final enregistré.</div>
                    @endif
                </td>
            </tr>
        </table>
    @else
        <div class="section-title">Produit</div>
        <table class="info-table">
            <tr>
                <td class="label">Produit</td>
                <td>{{ $order->product->product_name ?? 'N/A' }} ({{ $order->product->product_code ?? '' }})</td>
            </tr>
            @if ($order->sourceProduct)
                <tr>
                    <td class="label">Produit source</td>
                    <td>{{ $order->sourceProduct->product_name }} ({{ $order->sourceProduct->product_code ?? '' }})</td>
                </tr>
            @endif
            @if ($order->famille)
                <tr>
                    <td class="label">Famille destination</td>
                    <td>{{ $order->famille->famille_name }}</td>
                </tr>
            @endif
        </table>
    @endif

    {{-- Quality summary (Type 1 only) --}}
    @if ($order->production_type === 'type1' && isset($qualityMetrics))
        <div class="section-title">Contrôle Qualité</div>
        <table class="info-table">
            <tr>
                <td class="label">Statut qualité</td>
                <td>{{ ucfirst($qualityMetrics['quality']['quality_status'] ?? 'pending') }}</td>
                <td class="label">Score qualité</td>
                <td>{{ $qualityMetrics['quality']['quality_score'] !== null ? number_format($qualityMetrics['quality']['quality_score'], 1) . '%' : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Taux de défaut</td>
                <td>{{ number_format($qualityMetrics['quality']['defect_rate_percent'] ?? 0, 2, ',', ' ') }}%</td>
                <td class="label">Rendement</td>
                <td>{{ $qualityMetrics['quality']['efficiency_percent'] !== null ? number_format($qualityMetrics['quality']['efficiency_percent'], 1) . '%' : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Quantité bonne / défectueuse</td>
                <td colspan="3">
                    {{ number_format($qualityMetrics['quality']['total_good_quantity'] ?? 0) }} bonnes /
                    {{ number_format($qualityMetrics['quality']['total_defective_quantity'] ?? 0) }} défectueuses
                </td>
            </tr>
            @if ($qualityMetrics['quality']['quality_notes'])
                <tr>
                    <td class="label">Notes qualité</td>
                    <td colspan="3">{{ $qualityMetrics['quality']['quality_notes'] }}</td>
                </tr>
            @endif
            @if ($qualityMetrics['quality']['quality_override'])
                <tr>
                    <td class="label">Override qualité</td>
                    <td colspan="3">
                        {{ $qualityMetrics['quality']['quality_override_reason'] }}
                        — approuvé par {{ $qualityMetrics['quality']['quality_override_by'] ?? 'N/A' }}
                    </td>
                </tr>
            @endif
        </table>

        {{-- Consumption detail (weight-based, from quality metrics) --}}
        <div class="section-title">Détail des Consommations</div>
        @if (!empty($qualityMetrics['consumptions']))
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Code</th>
                        <th class="text-right">Planifié</th>
                        <th class="text-right">Réel</th>
                        <th class="text-right">Déchet</th>
                        <th class="text-right">Écart %</th>
                        <th class="text-right">Poids (kg)</th>
                        <th class="text-right">Coût (DH)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($qualityMetrics['consumptions'] as $consumption)
                        <tr>
                            <td>{{ $consumption['material_name'] }}</td>
                            <td>{{ $consumption['material_code'] }}</td>
                            <td class="text-right">{{ number_format($consumption['planned_quantity'], 2, ',', ' ') }} {{ $consumption['unit_of_measure'] }}</td>
                            <td class="text-right">{{ number_format($consumption['actual_quantity_used'], 2, ',', ' ') }} {{ $consumption['unit_of_measure'] }}</td>
                            <td class="text-right">{{ number_format($consumption['waste_quantity'], 2, ',', ' ') }} {{ $consumption['unit_of_measure'] }}</td>
                            <td class="text-right">{{ number_format($consumption['difference_percent'], 2, ',', ' ') }}%</td>
                            <td class="text-right">{{ number_format($consumption['weight_kg'], 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($consumption['total_cost'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">Aucune donnée de consommation.</div>
        @endif
    @endif

    {{-- Production outputs --}}
    <div class="section-title">Sorties de Production</div>
    @if ($order->outputs->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produit</th>
                    <th>Famille</th>
                    <th>Date</th>
                    <th class="text-right">Produite</th>
                    <th class="text-right">Défectueuse</th>
                    <th class="text-right">Bonne</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->outputs as $output)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $output->product->product_name ?? 'N/A' }}</td>
                        <td>{{ $output->famille->famille_name ?? 'Non spécifiée' }}</td>
                        <td>{{ \Carbon\Carbon::parse($output->production_date)->format('d/m/Y') }}</td>
                        <td class="text-right">{{ number_format($output->quantity_produced, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($output->quantity_defective, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($output->quantity_produced - $output->quantity_defective, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Total</td>
                    <td class="text-right">{{ number_format($order->outputs->sum('quantity_produced'), 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($order->outputs->sum('quantity_defective'), 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($order->outputs->sum('quantity_produced') - $order->outputs->sum('quantity_defective'), 2, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">Aucune sortie de production enregistrée pour cet ordre.</div>
    @endif

    {{-- Raw material consumption (Type 1) --}}
    @if ($order->production_type === 'type1')
        <div class="section-title">Consommation Matières Premières</div>
        @if ($order->consumptions->count() > 0)
            @php
                $totalPlanned = $order->consumptions->sum('planned_quantity');
                $totalActual = $order->consumptions->sum('actual_quantity_used');
                $totalWaste = $order->consumptions->sum('waste_quantity');
                $totalCost = $order->consumptions->sum('total_cost');
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Matière première</th>
                        <th class="text-right">Planifié</th>
                        <th class="text-right">Réel</th>
                        <th class="text-right">Déchet</th>
                        <th class="text-right">Coût total (DH)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->consumptions as $consumption)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $consumption->rawMaterial->material_name ?? 'N/A' }} ({{ $consumption->rawMaterial->material_code ?? '' }})</td>
                            <td class="text-right">{{ number_format($consumption->planned_quantity, 2, ',', ' ') }} {{ $consumption->rawMaterial->unit_of_measure ?? '' }}</td>
                            <td class="text-right">{{ number_format($consumption->actual_quantity_used, 2, ',', ' ') }} {{ $consumption->rawMaterial->unit_of_measure ?? '' }}</td>
                            <td class="text-right">{{ number_format($consumption->waste_quantity, 2, ',', ' ') }} {{ $consumption->rawMaterial->unit_of_measure ?? '' }}</td>
                            <td class="text-right">{{ number_format($consumption->total_cost, 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">Total</td>
                        <td class="text-right">{{ number_format($totalPlanned, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($totalActual, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($totalWaste, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($totalCost, 2, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <div class="no-data">Aucune consommation enregistrée pour cet ordre.</div>
        @endif
    @endif

    {{-- Waste / chutes --}}
    @if ($order->wastes->count() > 0)
        @php
            $totalRecyclableVolume = $order->wastes->where('waste_type', 'recyclable')->sum('volume_m3');
            $totalWasteVolume = $order->wastes->where('waste_type', 'waste')->sum('volume_m3');
            $totalWastesVolume = $totalRecyclableVolume + $totalWasteVolume;
        @endphp
        <div class="section-title">Chutes et Déchets</div>
        <table class="info-table">
            <tr>
                <td class="label">Volume recyclable</td>
                <td>{{ number_format($totalRecyclableVolume, 4) }} m³</td>
                <td class="label">Volume déchet</td>
                <td>{{ number_format($totalWasteVolume, 4) }} m³</td>
            </tr>
            <tr>
                <td class="label">Total chutes</td>
                <td colspan="3">{{ number_format($totalWastesVolume, 4) }} m³ ({{ $order->wastes->count() }} chute(s))</td>
            </tr>
        </table>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Dimensions</th>
                    <th class="text-right">Volume</th>
                    <th>Catégorie</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->wastes as $waste)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if ($waste->waste_type === 'recyclable')
                                Recyclable
                            @elseif ($waste->waste_type === 'auto_defective')
                                Auto-défaut
                            @else
                                Déchet
                            @endif
                        </td>
                        <td>{{ $waste->waste_source ?? '-' }}</td>
                        <td>
                            @if ($waste->height && $waste->width && $waste->depth)
                                {{ $waste->height }}m × {{ $waste->width }}m × {{ $waste->depth }}m
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($waste->volume_m3 ?? 0, 4) }} m³</td>
                        <td>{{ $waste->waste_category ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Notes --}}
    @if ($order->notes)
        <div class="section-title">Notes</div>
        <div class="no-data" style="font-style: normal;">{{ $order->notes }}</div>
    @endif

    {{-- Signatures --}}
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-label">Responsable Production</div>
            </td>
            <td>
                <div class="sig-label">Contrôle Qualité</div>
            </td>
            <td>
                <div class="sig-label">Direction</div>
            </td>
        </tr>
    </table>

    <div class="page-footer">
        Document généré automatiquement — Ordre de Production N° {{ $order->order_number }}
    </div>
</body>

</html>
