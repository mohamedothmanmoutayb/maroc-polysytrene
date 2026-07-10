@extends('layouts.app')

@section('title', 'Modifier Dépense')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier Dépense</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('expenses.index') }}">
                                        Dépenses
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Modifier
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        @if ($expense->approved_by)
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Cette dépense a déjà été approuvée et ne peut pas être modifiée.
                <a href="{{ route('expenses.show', $expense->expense_id) }}" class="alert-link">Voir les détails</a>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h5 class="card-title mb-0" style="color:white">
                                <i class="fas fa-edit me-2"></i>Modifier: {{ $expense->expense_number }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="expenseForm">
                                @csrf
                                @method('PUT')

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Numéro Dépense *</label>
                                            <input type="text" class="form-control" name="expense_number"
                                                value="{{ $expense->expense_number }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Date Dépense *</label>
                                            <input type="date" class="form-control" name="expense_date"
                                                value="{{ $expense->expense_date->format('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Catégorie *</label>
                                            <select class="form-control select2" name="category_id" required>
                                                <option value="">Sélectionner une catégorie...</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->category_id }}"
                                                        {{ $expense->category_id == $category->category_id ? 'selected' : '' }}>
                                                        {{ $category->category_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Montant * (DH)</label>
                                            <input type="number" class="form-control" name="amount" required
                                                min="0.01" step="0.01" value="{{ $expense->amount }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Mode de Paiement *</label>
                                            <select class="form-control" name="payment_method" required>
                                                <option value="">Sélectionner...</option>
                                                <option value="cash"
                                                    {{ $expense->payment_method == 'cash' ? 'selected' : '' }}>Espèces
                                                </option>
                                                <option value="check"
                                                    {{ $expense->payment_method == 'check' ? 'selected' : '' }}>Chèque
                                                </option>
                                                <option value="traite"
                                                    {{ $expense->payment_method == 'traite' ? 'selected' : '' }}>Traite
                                                </option>
                                                <option value="transfer"
                                                    {{ $expense->payment_method == 'transfer' ? 'selected' : '' }}>
                                                    Virement</option>
                                                <option value="credit_card"
                                                    {{ $expense->payment_method == 'credit_card' ? 'selected' : '' }}>Carte
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Bénéficiaire</label>
                                            <input type="text" class="form-control" name="paid_to"
                                                value="{{ $expense->paid_to }}" placeholder="Nom du bénéficiaire">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">N° Reçu/Facture</label>
                                            <input type="text" class="form-control" name="receipt_number"
                                                value="{{ $expense->receipt_number }}" placeholder="Ex: FAC-2024-001">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="2" placeholder="Description de la dépense...">{{ $expense->description }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control" name="notes" rows="2" placeholder="Notes supplémentaires...">{{ $expense->notes }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Mettre à jour
                                    </button>
                                    <a href="{{ route('expenses.show', $expense->expense_id) }}" class="btn btn-info">
                                        <i class="fas fa-eye me-1"></i> Voir détails
                                    </a>
                                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Annuler
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            // Form submit
            $('#expenseForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');

                $.ajax({
                    url: "{{ route('expenses.update', $expense->expense_id) }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('expenses.show', $expense->expense_id) }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        var errorMessage = xhr.responseJSON?.message ||
                            'Une erreur est survenue';

                        if (errors) {
                            errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : 'danger') +
                    ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                    '</div>' +
                    '</div>');

                $('#toast-container').append(toast);
                var bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(function() {
                    toast.remove();
                }, 5000);
            }
        });
    </script>
@endpush
