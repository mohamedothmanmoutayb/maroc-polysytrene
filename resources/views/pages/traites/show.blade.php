@extends('layouts.app')

@section('title', 'Détails Traite')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Détails de la Traite</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('traites.index') }}">
                                        Traites
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-file-invoice me-2"></i>Traite: {{ $traite->traite_number }}
                        </h5>
                        <div>
                            @if ($traite->status == 'pending')
                                <button class="btn btn-success btn-sm mark-paid-btn" data-id="{{ $traite->traite_id }}"
                                    data-number="{{ $traite->traite_number }}">
                                    <i class="fas fa-check-circle me-1"></i> Marquer Payé
                                </button>
                                @if ($traite->is_overdue)
                                    <button class="btn btn-warning btn-sm mark-overdue-btn"
                                        data-id="{{ $traite->traite_id }}" data-number="{{ $traite->traite_number }}">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Marquer En Retard
                                    </button>
                                @endif
                                <button class="btn btn-danger btn-sm mark-bounced-btn" data-id="{{ $traite->traite_id }}"
                                    data-number="{{ $traite->traite_number }}">
                                    <i class="fas fa-times-circle me-1"></i> Marquer Rebondi
                                </button>
                            @endif
                            <a href="{{ route('traites.edit', $traite->traite_id) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            <a href="{{ route('traites.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Traite Information -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Informations de la Traite</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Numéro Traite</th>
                                                <td><strong>{{ $traite->traite_number }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Montant</th>
                                                <td class="text-success">
                                                    <strong>{{ number_format($traite->amount, 2, ',', '.') }} DH</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Client</th>
                                                <td>
                                                    @if ($traite->client)
                                                        <a href="{{ route('clients.show', $traite->client_id) }}"
                                                            class="text-decoration-none">
                                                            <strong>{{ $traite->client->display_name }}</strong>
                                                        </a>
                                                        <br>
                                                        <small class="text-muted">Code:
                                                            {{ $traite->client->code ?? 'N/A' }}</small>
                                                        @if ($traite->client->balance != 0)
                                                            <br>
                                                            <small
                                                                class="{{ $traite->client->balance > 0 ? 'text-success' : 'text-danger' }}">
                                                                <i
                                                                    class="fas {{ $traite->client->balance > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1"></i>
                                                                Solde:
                                                                {{ number_format(abs($traite->client->balance), 2, ',', '.') }}
                                                                DH
                                                                ({{ $traite->client->balance > 0 ? 'Crédit' : 'Dette' }})
                                                            </small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Vente Associée</th>
                                                <td>
                                                    @if ($traite->order)
                                                        <a href="{{ route('sales.orders.show', $traite->order_id) }}"
                                                            class="text-decoration-none">
                                                            <strong>{{ $traite->order->order_number }}</strong>
                                                        </a>
                                                        <br>
                                                        <small class="text-muted">
                                                            Total:
                                                            {{ number_format($traite->order->final_amount, 2, ',', '.') }}
                                                            DH
                                                            @if ($traite->order->remaining_amount > 0)
                                                                - Restant: <span
                                                                    class="text-warning">{{ number_format($traite->order->remaining_amount, 2, ',', '.') }}
                                                                    DH</span>
                                                            @else
                                                                - <span class="text-success">Soldé</span>
                                                            @endif
                                                        </small>
                                                    @else
                                                        <span class="text-muted">Aucune vente associée</span>
                                                        <br>
                                                        <small class="text-success">Le montant sera crédité au
                                                            client</small>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($traite->payment_id)
                                                <tr>
                                                    <th>Paiement Associé</th>
                                                    <td>
                                                        <a href="{{ route('sales-order-payments.show', $traite->payment_id) }}"
                                                            class="text-decoration-none">
                                                            <i class="fas fa-receipt me-1"></i>
                                                            Voir le paiement #{{ $traite->payment_id }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th>Date d'Émission</th>
                                                <td>{{ $traite->issue_date ? $traite->issue_date->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Date d'Échéance</th>
                                                <td>
                                                    {{ $traite->due_date ? $traite->due_date->format('d/m/Y') : '-' }}
                                                    @if ($traite->status == 'pending' && $traite->due_date && $traite->due_date->isPast())
                                                        <span class="badge bg-danger ms-2">En retard</span>
                                                    @elseif($traite->due_date && $traite->due_date->diffInDays(now()) <= 7 && $traite->due_date->isFuture())
                                                        <span class="badge bg-warning ms-2">Proche échéance</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($traite->payment_date)
                                                <tr>
                                                    <th>Date de Paiement</th>
                                                    <td>{{ $traite->payment_date->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th>Banque</th>
                                                <td>{{ $traite->bank_name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tiré</th>
                                                <td>{{ $traite->drawee ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Adresse du Tiré</th>
                                                <td>{{ $traite->drawee_address ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Statut</th>
                                                <td>{!! $traite->status_badge !!}</td>
                                            </tr>
                                            <tr>
                                                <th>Enregistré par</th>
                                                <td>{{ $traite->creator->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date Enregistrement</th>
                                                <td>{{ $traite->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dernière Mise à Jour</th>
                                                <td>{{ $traite->updated_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Notes Card -->
                                @if ($traite->notes)
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Notes</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-0">{{ $traite->notes }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Right Column - Document and Balance Impact -->
                            <div class="col-md-6">
                                <!-- Document Card -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Document</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        @if ($traite->document_path)
                                            @php
                                                $extension = pathinfo($traite->document_path, PATHINFO_EXTENSION);
                                                $isImage = in_array(strtolower($extension), [
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'gif',
                                                    'webp',
                                                ]);
                                            @endphp

                                            @if ($isImage)
                                                <img src="{{ asset('storage/' . $traite->document_path) }}"
                                                    alt="Document de la traite {{ $traite->traite_number }}"
                                                    class="img-fluid img-thumbnail"
                                                    style="max-height: 300px; cursor: pointer;" data-bs-toggle="modal"
                                                    data-bs-target="#documentModal">
                                                <div class="mt-3">
                                                    <a href="{{ asset('storage/' . $traite->document_path) }}"
                                                        download="{{ $traite->original_filename ?? 'document' }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-download me-1"></i> Télécharger
                                                    </a>
                                                </div>
                                            @else
                                                <div class="py-4">
                                                    <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                                                    <p><strong>{{ $traite->original_filename ?? 'Document PDF' }}</strong>
                                                    </p>
                                                    <div class="btn-group">
                                                        <a href="{{ asset('storage/' . $traite->document_path) }}"
                                                            target="_blank" class="btn btn-primary">
                                                            <i class="fas fa-eye me-1"></i> Voir le document
                                                        </a>
                                                        <a href="{{ asset('storage/' . $traite->document_path) }}"
                                                            download="{{ $traite->original_filename ?? 'document' }}"
                                                            class="btn btn-secondary">
                                                            <i class="fas fa-download me-1"></i> Télécharger
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="py-5">
                                                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                                <p class="text-muted">Aucun document associé à cette traite</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Payment Impact Card -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Impact sur le Paiement et le Solde</h6>
                                    </div>
                                    <div class="card-body">
                                        @if ($traite->status == 'paid')
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <strong>Cette traite a été payée</strong>
                                                @if ($traite->payment_id)
                                                    <br><small>Un paiement a été créé automatiquement.</small>
                                                @endif
                                            </div>

                                            @if ($traite->order_id)
                                                <div class="alert alert-info">
                                                    <i class="fas fa-shopping-cart me-2"></i>
                                                    <strong>Impact sur la vente:</strong>
                                                    <ul class="mb-0 mt-2">
                                                        <li>Montant de la traite:
                                                            {{ number_format($traite->amount, 2, ',', '.') }}
                                                            DH</li>
                                                        <li>Déduit de la vente #{{ $traite->order->order_number }}</li>
                                                        @if ($traite->amount > $traite->order->remaining_amount + $traite->amount)
                                                            <li class="text-warning">Excédent:
                                                                {{ number_format($traite->amount - $traite->order->remaining_amount, 2, ',', '.') }}
                                                                DH ajouté au crédit client</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @else
                                                <div class="alert alert-success">
                                                    <i class="fas fa-coins me-2"></i>
                                                    <strong>Impact sur le client:</strong>
                                                    <ul class="mb-0 mt-2">
                                                        <li>Montant total:
                                                            {{ number_format($traite->amount, 2, ',', '.') }} DH</li>
                                                        <li>Ajouté au crédit client (solde client augmenté)</li>
                                                    </ul>
                                                </div>
                                            @endif
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-clock me-2"></i>
                                                <strong>Traite en attente de paiement</strong>
                                                <br><small>Lorsque cette traite sera marquée comme payée:</small>
                                                <ul class="mb-0 mt-2">
                                                    @if ($traite->order_id)
                                                        <li>Le montant sera déduit du reste à payer de la vente</li>
                                                        <li>Si excédent, il sera ajouté au crédit client</li>
                                                    @else
                                                        <li>Le montant total sera ajouté au crédit client</li>
                                                    @endif
                                                    <li>Un paiement sera automatiquement créé</li>
                                                    <li>L'historique du solde client sera mis à jour</li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Modal for Images -->
    @if ($traite->document_path)
        @php
            $extension = pathinfo($traite->document_path, PATHINFO_EXTENSION);
            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        @endphp
        @if ($isImage)
            <div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Document - {{ $traite->traite_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ asset('storage/' . $traite->document_path) }}" alt="Document"
                                class="img-fluid">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <a href="{{ asset('storage/' . $traite->document_path) }}"
                                download="{{ $traite->original_filename ?? 'document' }}" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Télécharger
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Mark as Paid
            $('.mark-paid-btn').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le paiement',
                    html: `Voulez-vous marquer la traite <strong>${number}</strong> comme payée?<br><br>
                           <small class="text-muted">Un paiement sera automatiquement créé et le solde client sera mis à jour.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer payée!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Traitement en cours...',
                            text: 'Création du paiement et mise à jour du solde...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('traites') }}/" + id + "/mark-paid",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Succès!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erreur',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: xhr.responseJSON?.message ||
                                        'Une erreur est survenue'
                                });
                            }
                        });
                    }
                });
            });

            // Mark as Overdue
            $('.mark-overdue-btn').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le retard',
                    text: "Voulez-vous marquer la traite " + number + " comme en retard?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer en retard!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('traites') }}/" + id + "/mark-overdue",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Succès!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erreur',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: xhr.responseJSON?.message ||
                                        'Une erreur est survenue'
                                });
                            }
                        });
                    }
                });
            });

            // Mark as Bounced
            $('.mark-bounced-btn').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var number = $(this).data('number');

                Swal.fire({
                    title: 'Confirmer le rejet',
                    text: "Voulez-vous marquer la traite " + number + " comme rejetée?",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, marquer rejetée!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('traites') }}/" + id + "/mark-bounced",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Succès!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erreur',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: xhr.responseJSON?.message ||
                                        'Une erreur est survenue'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .img-thumbnail:hover {
            opacity: 0.8;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .table-bordered th {
            background-color: #f8f9fa;
        }
    </style>
@endpush
