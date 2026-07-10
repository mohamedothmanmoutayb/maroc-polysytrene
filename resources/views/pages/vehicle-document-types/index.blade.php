@extends('layouts.app')

@section('title', 'Gestion des Types de Documents')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Types de Documents</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Types de Documents
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
                            <i class="fas fa-file-alt me-2"></i>Types de Documents
                        </h5>
                        <div>
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createDocumentTypeModal">
                                <i class="fas fa-plus me-1"></i> Nouveau Type
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="document-types-table">
                                <thead class="thead-light">
                                    32
                                    <th width="5%">#</th>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th class="text-center">Durée par défaut</th>
                                    <th class="text-center">Rappel (jours)</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-center">Ordre</th>
                                    <th width="15%">Actions</th>
                                    32
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Document Type Modal -->
    <div class="modal fade" id="createDocumentTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Nouveau Type de Document
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="createDocumentTypeForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Code *</label>
                            <input type="text" class="form-control" name="type_code" required>
                            <small class="text-muted">Code unique (ex: insurance, registration, etc.)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="type_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Durée par défaut (jours)</label>
                                <input type="number" class="form-control" name="default_duration_days" value="365">
                                <small class="text-muted">Durée de validité par défaut</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jours de rappel</label>
                                <input type="number" class="form-control" name="reminder_days_before" value="30">
                                <small class="text-muted">Rappeler X jours avant expiration</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ordre d'affichage</label>
                            <input type="number" class="form-control" name="sort_order" value="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">Actif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Document Type Modal -->
    <div class="modal fade" id="editDocumentTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Modifier Type de Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editDocumentTypeForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="document_type_id" id="edit_document_type_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Code *</label>
                            <input type="text" class="form-control" name="type_code" id="edit_type_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="type_name" id="edit_type_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Durée par défaut (jours)</label>
                                <input type="number" class="form-control" name="default_duration_days"
                                    id="edit_default_duration_days">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jours de rappel</label>
                                <input type="number" class="form-control" name="reminder_days_before"
                                    id="edit_reminder_days_before">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ordre d'affichage</label>
                            <input type="number" class="form-control" name="sort_order" id="edit_sort_order">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="edit_is_active">
                                <label class="form-check-label">Actif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le type de document :</p>
                    <p class="fw-bold text-center" id="deleteTypeName"></p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette action supprimera également tous les documents associés à ce type !
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        let table;
        let deleteId = null;

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#document-types-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: "{{ route('vehicle-document-types.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'type_code',
                        name: 'type_code',
                        className: 'fw-bold'
                    },
                    {
                        data: 'type_name',
                        name: 'type_name'
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'default_duration_days',
                        name: 'default_duration_days',
                        className: 'text-center',
                        render: function(data) {
                            return data ? data + ' jours' : '-';
                        }
                    },
                    {
                        data: 'reminder_days_before',
                        name: 'reminder_days_before',
                        className: 'text-center',
                        render: function(data) {
                            return data ? data + ' jours' : '-';
                        }
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'sort_order',
                        name: 'sort_order',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [7, 'asc']
                ],
                pageLength: 25
            });

            // Create document type
            $('#createDocumentTypeForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Création...');

                $.ajax({
                    url: "{{ route('vehicle-document-types.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#createDocumentTypeModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                            $('#createDocumentTypeForm')[0].reset();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de la création';

                        if (errors) {
                            errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        }

                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Edit document type
            $(document).on('click', '.edit-type', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: "{{ url('vehicle-document-types') }}/" + id,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#edit_document_type_id').val(response.data.document_type_id);
                            $('#edit_type_code').val(response.data.type_code);
                            $('#edit_type_name').val(response.data.type_name);
                            $('#edit_description').val(response.data.description);
                            $('#edit_default_duration_days').val(response.data
                                .default_duration_days);
                            $('#edit_reminder_days_before').val(response.data
                                .reminder_days_before);
                            $('#edit_sort_order').val(response.data.sort_order);
                            $('#edit_is_active').prop('checked', response.data.is_active == 1);

                            $('#editDocumentTypeModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                });
            });

            // Update document type
            $('#editDocumentTypeForm').submit(function(e) {
                e.preventDefault();

                const id = $('#edit_document_type_id').val();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mise à jour...');

                $.ajax({
                    url: "{{ url('vehicle-document-types') }}/" + id,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#editDocumentTypeModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de la mise à jour';

                        if (errors) {
                            errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        }

                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Delete document type
            $(document).on('click', '.delete-type', function() {
                deleteId = $(this).data('id');
                const typeName = $(this).data('name');
                $('#deleteTypeName').text(typeName);
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').click(function() {
                if (!deleteId) return;

                $.ajax({
                    url: "{{ url('vehicle-document-types') }}/" + deleteId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#deleteModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors de la suppression');
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
