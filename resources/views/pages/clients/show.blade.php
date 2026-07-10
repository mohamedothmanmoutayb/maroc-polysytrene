@extends('layouts.app')

@section('title', 'Détails du Client - ' . $client->display_name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                @if ($client->person_type == 'morale')
                                    <i class="fas fa-building fs-2 text-primary"></i>
                                @else
                                    <i class="fas fa-user fs-2 text-info"></i>
                                @endif
                            </div>
                            <div>
                                <h4 class="mb-1 card-title">{{ $client->display_name }}</h4>
                                <div class="d-flex align-items-center">
                                    {!! $client->status_badge !!}
                                    <span class="mx-2">•</span>
                                    <span class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>Client depuis
                                        {{ $client->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('clients.index') }}">
                                        Clients
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Détails
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted me-2">Actions rapides:</span>
                                @can('edit_clients')
                                    <a href="{{ route('clients.edit', $client->client_id) }}"
                                        class="btn btn-sm btn-primary me-1">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </a>
                                @endcan
                                <a href="{{ route('clients.documents', $client->client_id) }}"
                                    class="btn btn-sm btn-info me-1">
                                    <i class="fas fa-folder me-1"></i>Documents
                                </a>
                                @can('create_sales_orders')
                                    <a href="{{ route('sales.orders.create', ['client_id' => $client->client_id]) }}"
                                        class="btn btn-sm btn-success me-1">
                                        <i class="fas fa-shopping-cart me-1"></i>Nouvelle vente
                                    </a>
                                @endcan
                            </div>
                            <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Left Column - Client Information -->
            <div class="col-lg-4">
                <!-- Client Info Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Informations Générales
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="text-muted">Type de personne:</td>
                                <td class="fw-bold">{!! $client->person_type_badge !!}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Type de client:</td>
                                <td class="fw-bold">{!! $client->client_type_badge !!}</td>
                            </tr>
                            @if ($client->person_type == 'morale')
                                <tr>
                                    <td class="text-muted">Entreprise:</td>
                                    <td class="fw-bold">{{ $client->entreprise_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ICE:</td>
                                    <td class="fw-bold">{{ $client->ice ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">RC / Patente:</td>
                                    <td>{{ $client->rc ?? '-' }} / {{ $client->patente ?? '-' }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-muted">Nom complet:</td>
                                    <td class="fw-bold">{{ $client->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">CIN:</td>
                                    <td class="fw-bold">{{ $client->cin ?? 'Non renseigné' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Téléphone:</td>
                                <td class="fw-bold">
                                    <a href="tel:{{ $client->phone }}" class="text-decoration-none">
                                        <i class="fas fa-phone-alt me-1 text-success"></i>{{ $client->phone }}
                                    </a>
                                </td>
                            </tr>
                            @if ($client->email)
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td>
                                        <a href="mailto:{{ $client->email }}" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1 text-info"></i>{{ $client->email }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Adresse:</td>
                                <td>
                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                    {{ $client->address ?? 'Non renseignée' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Balance Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i
                                class="fas fa-balance-scale me-2 text-{{ $client->balance > 0 ? 'success' : ($client->balance < 0 ? 'danger' : 'secondary') }}"></i>
                            Solde du Client
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @php
                                $balance = $client->balance;
                                $isPositive = $balance > 0;
                                $isNegative = $balance < 0;
                                $absoluteBalance = abs($balance);
                                $balanceClass = $isPositive
                                    ? 'text-success'
                                    : ($isNegative
                                        ? 'text-danger'
                                        : 'text-secondary');
                                $balanceIcon = $isPositive
                                    ? 'fa-arrow-up'
                                    : ($isNegative
                                        ? 'fa-arrow-down'
                                        : 'fa-circle');
                            @endphp
                            <h2 class="fw-bold {{ $balanceClass }} mb-0">
                                <i class="fas {{ $balanceIcon }} me-2"></i>
                                {{ number_format($absoluteBalance, 2, ',', '.') }} DH
                            </h2>
                            <span class="text-muted small">
                                @if ($isPositive)
                                    Avance / Trop-perçu (Nous devons au client)
                                @elseif($isNegative)
                                    Impayé (Le client nous doit)
                                @else
                                    Solde nul
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Credit Limit Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-credit-card me-2 text-warning"></i>Plafond de Crédit
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h3 class="fw-bold text-primary mb-0">{{ number_format($client->credit_limit, 2, ',', '.') }} DH</h3>
                            <span class="text-muted small">Plafond total</span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Solde actuel</span>
                                <span
                                    class="small fw-bold {{ $client->balance > 0 ? 'text-success' : ($client->balance < 0 ? 'text-danger' : 'text-secondary') }}">
                                    {{ number_format(abs($client->balance), 2, ',', '.') }} DH
                                    <span class="text-muted">
                                        ({{ $client->balance > 0 ? 'Avance' : ($client->balance < 0 ? 'Impayé' : 'Soldé') }})
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Rest of the credit limit card remains the same -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Utilisé (Crédit)</span>
                                <span class="small fw-bold text-danger">{{ number_format($client->credit_used, 2, ',', '.') }}
                                    DH</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small">Disponible</span>
                                <span class="small fw-bold text-success">{{ number_format($client->credit_available, 2, ',', '.') }}
                                    DH</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-{{ $client->credit_progress_class }}" role="progressbar"
                                    style="width: {{ $client->credit_percentage }}%"
                                    aria-valuenow="{{ $client->credit_percentage }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                            <div class="text-end mt-1">
                                <span class="small text-muted">{{ $client->credit_percentage }}% du crédit utilisé</span>
                            </div>
                        </div>

                        @if ($client->credit_percentage >= 90)
                            <div class="alert alert-danger alert-sm mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Attention: Crédit presque épuisé!
                            </div>
                        @elseif($client->credit_percentage >= 70)
                            <div class="alert alert-warning alert-sm mb-0">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Crédit à plus de 70% utilisé
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Documents Summary Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-folder me-2 text-primary"></i>Documents
                        </h6>
                        <span class="badge bg-primary">{{ $client->documents->count() }}</span>
                    </div>
                    <div class="card-body">
                        @if ($client->documents->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($client->documents->sortByDesc('created_at')->take(5) as $document)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file-alt me-2 text-muted"></i>
                                                <span class="small">{{ $document->document_name }}</span>
                                                <div class="small text-muted">
                                                    {{ $document->created_at->format('d/m/Y') }} -
                                                    {{ $document->file_size ? round($document->file_size / 1024, 0) . ' KB' : '' }}
                                                </div>
                                            </div>
                                            <a href="{{ asset('storage/' . $document->file_path) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($client->documents->count() > 5)
                                <div class="text-center mt-3">
                                    <a href="{{ route('clients.documents', $client->client_id) }}"
                                        class="btn btn-sm btn-link">
                                        Voir tous les documents ({{ $client->documents->count() }})
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-folder-open fs-1 text-muted mb-3"></i>
                                <p class="text-muted mb-3">Aucun document enregistré</p>
                                <a href="{{ route('clients.documents', $client->client_id) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-upload me-1"></i>Ajouter des documents
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes Card -->
                @if ($client->notes)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-sticky-note me-2 text-info"></i>Notes
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $client->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Purchase Summary & History -->
            <div class="col-lg-8">
                <!-- Résumé en Chiffre - Purchase Summary Card -->
                <div class="card mb-4">
                    <div class="card-header bg-gradient text-white"
                        style="background: linear-gradient(45deg, #3a3f51, #2c3e50);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-white">
                                <i class="fas fa-chart-pie me-2"></i>Résumé des Achats (En Chiffre)
                            </h6>
                            <span class="badge bg-light text-dark">
                                {{ $client->salesOrders->count() }} Ventes
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $summary = $client->purchase_summary;
                        @endphp

                        <!-- Key Figures -->
                        <div class="row g-4 mb-4">
                            <div class="col-sm-4">
                                <div class="bg-light rounded-3 p-3 text-center h-100">
                                    <div class="text-muted small mb-1">Total Achats</div>
                                    <div class="h5 mb-0 fw-bold text-primary">
                                        {{ number_format($summary['total'], 2, ',', '.') }} DH
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-light rounded-3 p-3 text-center h-100">
                                    <div class="text-muted small mb-1">Total Payé</div>
                                    <div class="h5 mb-0 fw-bold text-success">
                                        {{ number_format($summary['paid'], 2, ',', '.') }} DH
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-light rounded-3 p-3 text-center h-100">
                                    <div class="text-muted small mb-1">Non Payé</div>
                                    <div class="h5 mb-0 fw-bold text-{{ $summary['unpaid'] > 0 ? 'danger' : 'success' }}">
                                        {{ number_format($summary['unpaid'], 2, ',', '.') }} DH
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Progress -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Progression des paiements</span>
                                <span class="badge bg-{{ $summary['progress_class'] }}">
                                    {{ $summary['progress'] }}% payé
                                </span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-{{ $summary['progress_class'] }}" role="progressbar"
                                    style="width: {{ $summary['progress'] }}%"
                                    aria-valuenow="{{ $summary['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Statistics -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-info bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-shopping-cart text-info"></i>
                                        </div>
                                        <span class="text-muted">Nombre de Ventes</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $summary['orders_count'] }}</h4>
                                    <small class="text-muted">
                                        Dernière: {{ $client->last_purchase_date }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-calculator text-warning"></i>
                                        </div>
                                        <span class="text-muted">Valeur moyenne</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ number_format($client->average_purchase_value, 2, ',', '.') }} DH
                                    </h4>
                                    <small class="text-muted">Par commande</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Orders -->
                @if ($client->outstanding_invoices->count() > 0)
                    <div class="card mb-4 border-danger border">
                        <div class="card-header bg-danger bg-opacity-10 text-danger">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Ventes Impayées
                                </h6>
                                <span class="badge bg-danger">
                                    {{ number_format($summary['unpaid'], 2, ',', '.') }} DH
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>N° Commande</th>
                                            <th>Date</th>
                                            <th class="text-end">Montant</th>
                                            <th class="text-end">Payé</th>
                                            <th class="text-end">Solde</th>
                                            <th>Statut</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($client->outstanding_invoices as $order)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('sales.orders.show', $order->order_id) }}"
                                                        class="text-decoration-none">
                                                        {{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $order->order_date->format('d/m/Y') }}</td>
                                                <td class="text-end">{{ number_format($order->final_amount, 2, ',', '.') }} DH</td>
                                                <td class="text-end text-success">
                                                    {{ number_format($order->paid_amount, 2, ',', '.') }} DH
                                                </td>
                                                <td class="text-end fw-bold text-danger">
                                                    {{ number_format($order->final_amount - $order->paid_amount, 2, ',', '.') }} DH
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($order->payment_status) {
                                                            'pending' => 'danger',
                                                            'partial' => 'warning',
                                                            'paid' => 'success',
                                                            default => 'secondary',
                                                        };
                                                        $statusLabels = [
                                                            'pending' => 'Non Payé',
                                                            'partial' => 'Avance',
                                                            'paid' => 'Payé',
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $statusClass }}">
                                                        {{ $statusLabels[$order->payment_status] ?? $order->payment_status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('sales.orders.show', $order->order_id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Complete Purchase History -->
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-history me-2 text-primary"></i>Historique des Achats
                            </h6>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active" id="showAllOrders">
                                    Tous
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="showOpenOrders">
                                    En cours
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="showCompletedOrders">
                                    Payées
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if ($client->salesOrders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>N° Commande</th>
                                            <th class="text-end">Montant Total</th>
                                            <th class="text-end">Payé</th>
                                            <th class="text-end">Solde</th>
                                            <th>Statut Paiement</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($client->salesOrders->sortByDesc('order_date') as $order)
                                            <tr class="order-row" data-status="{{ $order->payment_status }}">
                                                <td>{{ $order->order_date->format('d/m/Y') }}</td>
                                                <td>
                                                    <a href="{{ route('sales.orders.show', $order->order_id) }}"
                                                        class="text-decoration-none fw-bold">
                                                        {{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td class="text-end fw-bold">{{ number_format($order->final_amount, 2, ',', '.') }}
                                                    DH</td>
                                                <td class="text-end text-success">
                                                    {{ number_format($order->paid_amount, 2, ',', '.') }} DH
                                                </td>
                                                <td
                                                    class="text-end {{ $order->final_amount - $order->paid_amount > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                                    {{ number_format($order->final_amount - $order->paid_amount, 2, ',', '.') }} DH
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($order->payment_status) {
                                                            'pending' => 'danger',
                                                            'partial' => 'warning',
                                                            'paid' => 'success',
                                                            default => 'secondary',
                                                        };
                                                        $statusLabels = [
                                                            'pending' => 'Non Payé',
                                                            'partial' => 'Avance',
                                                            'paid' => 'Payé',
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $statusClass }}">
                                                        {{ $statusLabels[$order->payment_status] ?? $order->payment_status }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('sales.orders.show', $order->order_id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="2" class="text-end fw-bold">Totaux:</td>
                                            <td class="text-end fw-bold text-primary">
                                                {{ number_format($summary['total'], 2, ',', '.') }} DH
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                {{ number_format($summary['paid'], 2, ',', '.') }} DH
                                            </td>
                                            <td class="text-end fw-bold text-danger">
                                                {{ number_format($summary['unpaid'], 2, ',', '.') }} DH
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Payment Methods Summary -->
                            @if ($paymentMethods && $paymentMethods->count() > 0)
                                <div class="card-footer bg-white">
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3">
                                                <i class="fas fa-credit-card me-2 text-primary"></i>
                                                Répartition des paiements
                                            </h6>
                                            <div class="chart-container"
                                                style="position: relative; height:200px; width:100%">
                                                <canvas id="paymentMethodsChart"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3">
                                                <i class="fas fa-chart-bar me-2 text-success"></i>
                                                Détail par méthode
                                            </h6>
                                            <div class="list-group">
                                                @foreach ($paymentMethods as $method)
                                                    <div
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            @switch($method->payment_method)
                                                                @case('cash')
                                                                    <i class="fas fa-money-bill-wave text-success me-2"></i>Espèces
                                                                @break

                                                                @case('check')
                                                                    <i class="fas fa-money-check-alt text-primary me-2"></i>Chèque
                                                                @break

                                                                @case('transfer')
                                                                    <i class="fas fa-university text-info me-2"></i>Virement
                                                                @break

                                                                @case('traite')
                                                                    <i class="fas fa-file-invoice text-warning me-2"></i>Traite
                                                                @break

                                                                @default
                                                                    <i
                                                                        class="fas fa-circle text-secondary me-2"></i>{{ ucfirst($method->payment_method) }}
                                                            @endswitch
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold">{{ number_format($method->total, 2, ',', '.') }}
                                                                DH</span>
                                                            <span class="text-muted ms-2">({{ $method->count }}
                                                                paiements)</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-shopping-cart fs-1 text-muted"></i>
                                </div>
                                <h6 class="text-muted mb-3">Aucun achat enregistré pour ce client</h6>
                                <p class="small text-muted mb-4">
                                    Commencez par créer une commande pour ce client
                                </p>
                                <a href="{{ route('sales.orders.create', ['client_id' => $client->client_id]) }}"
                                    class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Créer une commande
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-gradient {
            background: linear-gradient(45deg, #3a3f51, #2c3e50);
        }

        .progress {
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .bg-opacity-10 {
            opacity: 0.1;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .border-danger {
            border-width: 2px;
        }

        .table> :not(caption)>*>* {
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.75rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Filter orders by status
            $('#showAllOrders').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                $('.order-row').show();
            });

            $('#showOpenOrders').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                $('.order-row').hide();
                $('.order-row[data-status="pending"], .order-row[data-status="partial"]').show();
            });

            $('#showCompletedOrders').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                $('.order-row').hide();
                $('.order-row[data-status="paid"]').show();
            });

            // Initialize payment methods chart if data exists
            @if (isset($paymentMethods) && $paymentMethods->count() > 0)
                const ctx = document.getElementById('paymentMethodsChart').getContext('2d');
                const paymentMethods = @json($paymentMethods);

                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: paymentMethods.map(m => {
                            switch (m.payment_method) {
                                case 'cash':
                                    return 'Espèces';
                                case 'check':
                                    return 'Chèques';
                                case 'transfer':
                                    return 'Virements';
                                case 'traite':
                                    return 'Traites';
                                default:
                                    return m.payment_method;
                            }
                        }),
                        datasets: [{
                            data: paymentMethods.map(m => m.total),
                            backgroundColor: [
                                '#28a745', // cash - green
                                '#007bff', // check - blue
                                '#17a2b8', // transfer - teal
                                '#ffc107', // traite - yellow
                                '#6c757d' // others - gray
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value.toLocaleString('de-DE')} DH (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
@endpush
