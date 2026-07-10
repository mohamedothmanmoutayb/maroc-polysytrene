@extends('layouts.app')

@section('title', 'Détails Chauffeur - ' . $driver->full_name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails Chauffeur</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('drivers.index') }}">
                                        Chauffeurs
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        {{ $driver->full_name }}
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Informations Personnelles</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if ($driver->photo)
                                    <img src="{{ asset('storage/' . $driver->photo) }}" class="rounded-circle"
                                        width="150" height="150"
                                        style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 150px; height: 150px;">
                                        <i class="fas fa-user fa-4x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <h5 class="mt-3 mb-1">{{ $driver->full_name }}</h5>
                            <span
                                class="badge {{ $driver->status == 'active' ? 'bg-success' : ($driver->status == 'suspended' ? 'bg-warning' : 'bg-secondary') }} px-3 py-2">
                                {{ $driver->status_label }}
                            </span>
                        </div>

                        <table class="table table-sm">
                            <tr>
                                <th>CIN:</th>
                                <td><strong>{{ $driver->cin }}</strong></td>
                            </tr>
                            <tr>
                                <th>Téléphone:</th>
                                <td>{{ $driver->phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $driver->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Date d'embauche:</th>
                                <td>{{ $driver->hire_date ? $driver->hire_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @if ($driver->address)
                                <tr>
                                    <th>Adresse:</th>
                                    <td>{{ $driver->address }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Véhicule Assigné</h6>
                    </div>
                    <div class="card-body">
                        @if ($driver->currentVehicle && $driver->currentVehicle->vehicle)
                            <div class="text-center">
                                <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                                <h6>{{ $driver->currentVehicle->vehicle->registration_number }}</h6>
                                <p class="mb-1">
                                    <span class="badge bg-info">{{ $driver->currentVehicle->vehicle->type_label }}</span>
                                </p>
                                <p class="small text-muted mb-0">
                                    Assigné depuis le {{ $driver->currentVehicle->start_date->format('d/m/Y') }}
                                </p>
                                <a href="{{ route('vehicles.show', $driver->currentVehicle->vehicle->vehicle_id) }}"
                                    class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-eye me-1"></i> Voir véhicule
                                </a>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-truck fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Aucun véhicule assigné</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Informations du Permis de Conduire</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th>N° Permis:</th>
                                        <td><strong>{{ $driver->license_number }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Catégorie:</th>
                                        <td>{{ $driver->license_category ?? 'B' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Date d'expiration:</th>
                                        <td>
                                            {{ $driver->license_expiry_date ? $driver->license_expiry_date->format('d/m/Y') : '-' }}
                                            @if ($driver->license_expiring_soon)
                                                <span class="badge bg-warning text-dark ms-2">Expire bientôt</span>
                                            @elseif($driver->license_expiry_date && $driver->license_expiry_date < now())
                                                <span class="badge bg-danger ms-2">Expiré</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($driver->license_expiry_date)
                                        <tr>
                                            <th>Jours restants:</th>
                                            <td>
                                                @php
                                                    $daysLeft = \Carbon\Carbon::now()->diffInDays(
                                                        $driver->license_expiry_date,
                                                        false,
                                                    );
                                                @endphp
                                                @if ($daysLeft > 0)
                                                    <span
                                                        class="fw-bold {{ $daysLeft <= 10 ? 'text-danger' : 'text-success' }}">
                                                        {{ $daysLeft }} jours
                                                    </span>
                                                @elseif($daysLeft == 0)
                                                    <span class="text-danger fw-bold">Expire aujourd'hui</span>
                                                @else
                                                    <span class="text-danger fw-bold">Expiré depuis {{ abs($daysLeft) }}
                                                        jours</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Visites Médicales</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-check text-primary fs-2 mb-2"></i>
                                        <h6>Dernière visite</h6>
                                        <p class="mb-0">
                                            {{ $driver->medical_visit_date ? $driver->medical_visit_date->format('d/m/Y') : 'Non renseignée' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-alt text-warning fs-2 mb-2"></i>
                                        <h6>Prochaine visite</h6>
                                        <p class="mb-0">
                                            @if ($driver->next_medical_visit_date)
                                                {{ $driver->next_medical_visit_date->format('d/m/Y') }}
                                                @if ($driver->medical_visit_due_soon)
                                                    <span class="badge bg-warning text-dark d-block mt-1">Prévue
                                                        bientôt</span>
                                                @elseif($driver->next_medical_visit_date < now())
                                                    <span class="badge bg-danger d-block mt-1">En retard</span>
                                                @endif
                                            @else
                                                Non planifiée
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($driver->notes)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Notes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $driver->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            @can('edit_drivers')
            <a href="{{ route('drivers.edit', $driver->driver_id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Modifier
            </a>
            @endcan
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>
@endsection
