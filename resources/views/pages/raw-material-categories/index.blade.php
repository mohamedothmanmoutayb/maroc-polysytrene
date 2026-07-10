@extends('layouts.app')

@section('title', 'Gestion des Catégories')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Catégories de Matières Premières</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Catégories
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
                            <i class="fas fa-tags me-2"></i>Liste des Catégories
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" id="createCategoryBtn">
                                <i class="fas fa-plus me-1"></i> Nouvelle Catégorie
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categories-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nom</th>
                                        <th>Description</th>
                                        <th>Catégorie Parent</th>
                                        <th>Sous-catégories</th>
                                        <th>Matières</th>
                                        <th>Type</th>
                                        <th width="5%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nouvelle Catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="categoryForm">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="category_id" name="category_id">

                        <div class="form-group mb-3">
                            <label class="form-label">Nom de la Catégorie *</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required
                                maxlength="100">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" maxlength="255"></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Catégorie Parent</label>
                            <select class="form-control select2" id="parent_category_id" name="parent_category_id">
                                <option value="">Sélectionner une catégorie parent</option>
                                @foreach ($parentCategories as $parent)
                                    <option value="{{ $parent->category_id }}">{{ $parent->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la Catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la catégorie : <strong id="deleteCategoryName"></strong> ?</p>
                    <p class="text-danger">Cette action est irréversible !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
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

            // Initialize DataTable
            var table = $('#categories-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: "{{ route('raw-material-categories.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'parent_category',
                        name: 'parent.category_name'
                    },
                    {
                        data: 'subcategories_count',
                        name: 'subcategories_count',
                        className: 'text-center'
                    },
                    {
                        data: 'materials_count',
                        name: 'materials_count',
                        className: 'text-center'
                    },
                    {
                        data: 'hierarchy',
                        name: 'hierarchy',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [1, 'asc']
                ],
                responsive: true,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-print me-1"></i> Imprimer'
                    }
                ]
            });

            // Create Category Button
            $('#createCategoryBtn').click(function() {
                $('#modalTitle').text('Nouvelle Catégorie');
                $('#modalSubmitBtn').text('Enregistrer');
                $('#categoryForm')[0].reset();
                $('#category_id').val('');
                $('#parent_category_id').val('').trigger('change');
                $('#categoryModal').modal('show');
            });

            // Handle dropdown menu clicks
            $(document).on('click', '.dropdown-item.view', function() {
                var categoryId = $(this).data('id');

                $.ajax({
                    url: "{{ url('raw-material-categories') }}/" + categoryId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var category = response.data;
                            var html = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Nom:</th>
                                                <td><strong>${category.category_name}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Description:</th>
                                                <td>${category.description || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <th>Catégorie Parent:</th>
                                                <td>${category.parent ? category.parent.category_name : 'Catégorie Principale'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Sous-catégories:</th>
                                                <td><span class="badge badge-info">${category.subcategories_count || 0}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Matières:</th>
                                                <td><span class="badge badge-primary">${category.materials_count || 0}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Type:</th>
                                                <td>${category.parent_category_id ? 'Sous-catégorie' : 'Catégorie Principale'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            `;

                            $('#viewModalBody').html(html);
                            $('#viewModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                });
            });

            $(document).on('click', '.dropdown-item.edit', function() {
                var categoryId = $(this).data('id');

                $.ajax({
                    url: "{{ url('raw-material-categories') }}/" + categoryId + "/edit",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#modalTitle').text('Modifier Catégorie');
                            $('#modalSubmitBtn').text('Mettre à jour');
                            $('#category_id').val(response.data.category_id);
                            $('#category_name').val(response.data.category_name);
                            $('#description').val(response.data.description);
                            $('#parent_category_id').val(response.data.parent_category_id)
                                .trigger('change');
                            $('#categoryModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                });
            });

            $(document).on('click', '.dropdown-item.delete', function() {
                var categoryId = $(this).data('id');
                var categoryName = $(this).data('name');

                $('#deleteCategoryName').text(categoryName);
                $('#deleteForm').attr('action', "{{ url('raw-material-categories') }}/" + categoryId);
                $('#deleteModal').modal('show');
            });

            // Category Form Submit
            $('#categoryForm').submit(function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var categoryId = $('#category_id').val();
                var url = categoryId ? "{{ url('raw-material-categories') }}/" + categoryId :
                    "{{ route('raw-material-categories.store') }}";
                var method = categoryId ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#categoryModal').modal('hide');
                            table.draw();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';

                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                        }

                        showToast('error', errorMessage);
                    }
                });
            });

            // Delete Form Submit
            $('#deleteForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteModal').modal('hide');
                            table.draw();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de la suppression');
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
                }, 3000);
            }
        });
    </script>
@endpush
