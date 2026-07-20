<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
            padding: 15px 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        .logo img {
            max-width: 80px;
            height: auto;
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

        .machine-block {
            margin-bottom: 18px;
        }

        .machine-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 5px 8px;
        }

        .machine-meta {
            font-size: 9px;
            color: #444;
            margin: 3px 0 6px 0;
        }

        table.schedules-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        table.schedules-table th {
            border: 1px solid #000;
            padding: 5px;
            background-color: #efefef;
            text-align: left;
        }

        table.schedules-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
            color: #fff;
        }

        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #e0a800; color: #000; }
        .badge-success { background-color: #28a745; }

        .empty-state {
            font-style: italic;
            color: #777;
            padding: 8px 0;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="logo">
            <img src="/public/assets/images/logos/logo.png" alt="Logo">
        </div>
        <div>
            <div class="page-title">{{ $title }}</div>
            <div class="print-date">Imprimé le {{ $date }}</div>
        </div>
    </div>

    @forelse ($machines as $machine)
        <div class="machine-block">
            <div class="machine-title">{{ $machine->name }}</div>
            <div class="machine-meta">
                N° Série: {{ $machine->serial_number ?? '-' }}
                @if ($machine->model) &nbsp;|&nbsp; Modèle: {{ $machine->model }} @endif
                @if ($machine->manufacturer) &nbsp;|&nbsp; Fabricant: {{ $machine->manufacturer }} @endif
            </div>

            @if ($machine->maintenanceSchedules->count() > 0)
                <table class="schedules-table">
                    <thead>
                        <tr>
                            <th>Programme</th>
                            <th class="text-center">Intervalle</th>
                            <th class="text-center">Dernière effectuée</th>
                            <th class="text-center">Prochaine échéance</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($machine->maintenanceSchedules as $schedule)
                            <tr>
                                <td>{{ $schedule->label }}</td>
                                <td class="text-center">Tous les {{ $schedule->interval_days }} jours</td>
                                <td class="text-center">{{ $schedule->last_completed_at ? $schedule->last_completed_at->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">{{ $schedule->next_due_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    @php $status = $schedule->status; @endphp
                                    @if ($status === 'overdue')
                                        <span class="badge badge-danger">En retard</span>
                                    @elseif ($status === 'due_soon')
                                        <span class="badge badge-warning">Bientôt</span>
                                    @else
                                        <span class="badge badge-success">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">Aucun programme de maintenance préventive actif.</div>
            @endif
        </div>
    @empty
        <div class="empty-state">Aucune machine avec un programme de maintenance préventive.</div>
    @endforelse
</body>

</html>
