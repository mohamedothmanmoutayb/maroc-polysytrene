@extends('layouts.app')

@section('title', 'Nouvelle Machine')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Machine</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('machines.index') }}">
                                        Machines
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Nouvelle
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-custom">
                <h5 class="card-title mb-0" style="color:white">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter une Machine
                </h5>
            </div>
            <div class="card-body">
                <form id="machineForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Nom *</label>
                                <input type="text" class="form-control" name="name"
                                    placeholder="Ex: Pelle mécanique CAT 320" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">N° Série *</label>
                                <input type="text" class="form-control" name="serial_number"
                                    placeholder="Ex: CAT0320H75L" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Modèle</label>
                                <input type="text" class="form-control" name="model" placeholder="Ex: 320D">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Fabricant</label>
                                <input type="text" class="form-control" name="manufacturer"
                                    placeholder="Ex: Caterpillar">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Date d'achat</label>
                                <input type="date" class="form-control" name="purchase_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Heures de fonctionnement</label>
                                <input type="number" class="form-control" name="operating_hours" value="0"
                                    min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Statut *</label>
                                <select class="form-control" name="status" required>
                                    <option value="active">Actif</option>
                                    <option value="maintenance">En maintenance</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Documents</h6>
                        </div>
                        <div class="card-body">
                            <div id="documentsContainer">
                                @foreach ($documentTypes as $type)
                                    <div class="document-row mb-4 p-3 border rounded">
                                        <h6 class="mb-3">{{ $type->type_name }}</h6>
                                        <input type="hidden"
                                            name="documents[{{ $type->document_type_id }}][document_type_id]"
                                            value="{{ $type->document_type_id }}">

                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">N° Document</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][document_number]">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">Date de début</label>
                                                <input type="date" class="form-control start-date"
                                                    name="documents[{{ $type->document_type_id }}][start_date]">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small">Date de fin *</label>
                                                <input type="date" class="form-control end-date"
                                                    name="documents[{{ $type->document_type_id }}][end_date]">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Autorité émettrice</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][issuing_authority]">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Notes</label>
                                                <input type="text" class="form-control"
                                                    name="documents[{{ $type->document_type_id }}][notes]">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-group">
                            <label class="form-label">Notes générales</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        <a href="{{ route('machines.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Set default end dates
            @foreach ($documentTypes as $type)
                var defaultDate = new Date();
                @if (str_contains(strtolower($type->type_code), 'maintenance'))
                    defaultDate.setMonth(defaultDate.getMonth() + 6);
                @else
                    defaultDate.setFullYear(defaultDate.getFullYear() + 1);
                @endif
                $('input[name="documents[{{ $type->document_type_id }}][end_date]"]').val(defaultDate.toISOString()
                    .split('T')[0]);
            @endforeach

            $('#machineForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: "{{ route('machines.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('machines.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de l\'enregistrement';

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
        });

        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : 'danger') +
                ' border-0" role="alert">' +
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
    </script>
@endpush
