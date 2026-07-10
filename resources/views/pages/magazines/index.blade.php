@extends('layouts.app')

@section('title', 'Gestion des Magasins')

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .card-header-custom {
            background: linear-gradient(45deg, #2c3e50, #4a6491);
            color: white;
            border-bottom: none;
        }

        .material-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .modal-lg-custom {
            max-width: 900px;
        }

        .magazine-info-card {
            border-left: 4px solid #007bff;
        }

        .materials-card {
            border-left: 4px solid #28a745;
        }

        .stats-card {
            border-left: 4px solid #ffc107;
        }

        .badge-sm {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Magasins</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Magasins
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
                            <i class="fas fa-warehouse me-2"></i>Liste des Magasins
                        </h5>
                        <div>
                            <button type="button" class="btn btn-light btn-sm" id="createMagazineBtn">
                                <i class="fas fa-plus me-1"></i> Nouveau Magasin
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="magazines-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Code</th>
                                        <th>Nom du Magasin</th>
                                        <th>Emplacement</th>
                                        {{-- <th>Matières</th> --}}
                                        <th>Description</th>
                                        <th>Statut</th>
                                        <th width="8%">Actions</th>
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

    <!-- Create Magazine Modal -->
    <div class="modal fade" id="createMagazineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Nouveau Magasin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createMagazineForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="magazine_code" class="form-label">Code Magasin *</label>
                                <input type="text" class="form-control" id="magazine_code" name="magazine_code" required
                                    maxlength="20" placeholder="Ex: MAG-01">
                                <small class="form-text text-muted">Code unique d'identification</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="magazine_name" class="form-label">Nom du Magasin *</label>
                                <input type="text" class="form-control" id="magazine_name" name="magazine_name" required
                                    maxlength="100" placeholder="Ex: Magasin Principal">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Emplacement</label>
                            <input type="text" class="form-control" id="location" name="location" maxlength="100"
                                placeholder="Ex: Zone A, Bâtiment 3">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                placeholder="Description du magasin..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Statut</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" selected>Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Magazine Modal -->
    <div class="modal fade" id="viewMagazineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg-custom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Détails du Magasin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card magazine-info-card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 id="viewMagazineName" class="text-primary mb-3"></h5>
                                            <p><strong>Code:</strong> <span id="viewMagazineCode"
                                                    class="badge bg-primary"></span></p>
                                            <p><strong>Emplacement:</strong> <span id="viewMagazineLocation"></span></p>
                                            <p><strong>Statut:</strong> <span id="viewMagazineStatus"
                                                    class="badge"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Matières stockées:</strong> <span id="viewMaterialsCount"
                                                    class="badge bg-info"></span></p>
                                            <p><strong>Valeur totale:</strong> <span id="viewTotalValue"
                                                    class="badge bg-success"></span></p>
                                            <p><strong>Créé le:</strong> <span id="viewCreatedAt"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Statistiques</h6>
                                    <div class="text-center">
                                        <h2 id="viewMatsCount" class="mb-1"></h2>
                                        <p class="text-muted mb-0">Matières</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials List -->
                    <div class="card materials-card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-boxes me-2"></i>Matières Premières dans ce Magasin
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="noMaterialsAlert" class="alert alert-info d-none">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucune matière première n'est assignée à ce magasin.
                            </div>
                            <div class="table-responsive d-none" id="materialsTableContainer">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Nom</th>
                                            <th>Catégorie</th>
                                            <th>Stock</th>
                                            <th>Coût Moyen</th>
                                            <th>Fournisseur</th>
                                        </tr>
                                    </thead>
                                    <tbody id="materialsTableBody">
                                        <!-- Materials will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-align-left me-2"></i>Description
                            </h6>
                        </div>
                        <div class="card-body">
                            <p id="viewDescription" class="mb-0"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Magazine Modal -->
    <div class="modal fade" id="editMagazineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Modifier le Magasin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMagazineForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_magazine_id" name="magazine_id">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_magazine_code" class="form-label">Code Magasin *</label>
                                <input type="text" class="form-control" id="edit_magazine_code" name="magazine_code"
                                    required maxlength="20">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="edit_magazine_name" class="form-label">Nom du Magasin *</label>
                                <input type="text" class="form-control" id="edit_magazine_name" name="magazine_name"
                                    required maxlength="100">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Emplacement</label>
                            <input type="text" class="form-control" id="edit_location" name="location"
                                maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_is_active" class="form-label">Statut</label>
                            <select class="form-control" id="edit_is_active" name="is_active">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
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

    <!-- Delete Magazine Modal -->
    <div class="modal fade" id="deleteMagazineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le magasin : <strong id="deleteMagazineName"></strong> ?</p>
                    <p class="text-danger">Cette action est irréversible !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteMagazineForm" method="POST">
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

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#magazines-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: "{{ route('magazines.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'magazine_code',
                        name: 'magazine_code'
                    },
                    {
                        data: 'magazine_name',
                        name: 'magazine_name'
                    },
                    {
                        data: 'location',
                        name: 'location',
                        render: function(data) {
                            return data || 'N/A';
                        }
                    },
                    // {
                    //     data: 'materials_count',
                    //     name: 'materials_count',
                    //     orderable: false,
                    //     searchable: false,
                    //     className: 'text-center'
                    // },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data) {
                            if (data && data.length > 50) {
                                return data.substring(0, 50) + '...';
                            }
                            return data || 'N/A';
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
                ],
                createdRow: function(row, data, dataIndex) {
                    // Add highlighting for inactive magazines
                    if (!data.is_active) {
                        $(row).addClass('table-secondary');
                    }
                }
            });

            // Create Magazine Button
            $('#createMagazineBtn').click(function() {
                $('#createMagazineForm')[0].reset();
                $('#createMagazineModal').modal('show');
            });

            // Create Magazine Form Submit
            $('#createMagazineForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('magazines.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#createMagazineModal').modal('hide');
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

            // View Magazine
            $(document).on('click', '.view-magazine', function() {
                var magazineId = $(this).data('id');

                $.ajax({
                    url: "{{ url('magazines') }}/" + magazineId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            var magazine = data.magazine;
                            var materials = data.materials;

                            // Fill magazine info
                            $('#viewMagazineName').text(magazine.magazine_name);
                            $('#viewMagazineCode').text(magazine.magazine_code);
                            $('#viewMagazineLocation').text(magazine.location || 'N/A');
                            $('#viewMagazineStatus').html(magazine.is_active ?
                                '<span class="badge bg-success">Actif</span>' :
                                '<span class="badge bg-danger">Inactif</span>');
                            $('#viewMaterialsCount').text(data.materials_count + ' matières');
                            $('#viewTotalValue').text(data.total_stock_value.toFixed(2) +
                                ' DH');
                            $('#viewCreatedAt').text(new Date(magazine.created_at)
                                .toLocaleDateString('fr-FR'));
                            $('#viewDescription').text(magazine.description ||
                                'Aucune description');
                            $('#viewMatsCount').text(data.materials_count);

                            // Fill materials table
                            $('#materialsTableBody').empty();
                            if (materials.length > 0) {
                                materials.forEach(function(material) {
                                    var row = '<tr class="material-row">' +
                                        '<td><span class="badge bg-primary badge-sm">' +
                                        material.material_code + '</span></td>' +
                                        '<td>' + material.material_name + '</td>' +
                                        '<td>' + (material.category ? material.category
                                            .category_name : 'N/A') + '</td>' +
                                        '<td class="text-center">' + parseFloat(material
                                            .current_stock).toFixed(2) + ' ' + material
                                        .unit_of_measure + '</td>' +
                                        '<td class="text-end">' + parseFloat(material
                                            .average_unit_cost).toFixed(2) +
                                        ' DH</td>' +
                                        '<td>' + (material.supplier ? material.supplier
                                            .company_name : 'N/A') + '</td>' +
                                        '</tr>';
                                    $('#materialsTableBody').append(row);
                                });

                                $('#noMaterialsAlert').addClass('d-none');
                                $('#materialsTableContainer').removeClass('d-none');
                            } else {
                                $('#noMaterialsAlert').removeClass('d-none');
                                $('#materialsTableContainer').addClass('d-none');
                            }

                            $('#viewMagazineModal').modal('show');
                        }
                    },
                    error: function() {
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                });
            });

            // Edit Magazine
            $(document).on('click', '.edit-magazine', function() {
                var magazineId = $(this).data('id');

                $.ajax({
                    url: "{{ url('magazines') }}/" + magazineId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var magazine = response.data.magazine;

                            $('#edit_magazine_id').val(magazine.magazine_id);
                            $('#edit_magazine_code').val(magazine.magazine_code);
                            $('#edit_magazine_name').val(magazine.magazine_name);
                            $('#edit_location').val(magazine.location || '');
                            $('#edit_description').val(magazine.description || '');
                            $('#edit_is_active').val(magazine.is_active ? '1' : '0');

                            $('#editMagazineModal').modal('show');
                        }
                    },
                    error: function() {
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                });
            });

            // Edit Magazine Form Submit
            $('#editMagazineForm').submit(function(e) {
                e.preventDefault();

                var magazineId = $('#edit_magazine_id').val();

                $.ajax({
                    url: "{{ url('magazines') }}/" + magazineId,
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#editMagazineModal').modal('hide');
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

            // Delete Magazine
            $(document).on('click', '.delete-magazine', function() {
                var magazineId = $(this).data('id');
                var magazineName = $(this).data('name');

                $('#deleteMagazineName').text(magazineName);
                $('#deleteMagazineForm').attr('action', "{{ url('magazines') }}/" + magazineId);
                $('#deleteMagazineModal').modal('show');
            });

            // Delete Form Submit
            $('#deleteMagazineForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteMagazineModal').modal('hide');
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

            // Toast function
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
