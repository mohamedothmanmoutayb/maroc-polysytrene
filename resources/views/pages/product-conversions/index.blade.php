@extends('layouts.app')

@section('title', 'Gestion des Conversions Produits')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Conversions Produits</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('products.index') }}">
                                        Produits
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Conversions
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total Conversions</span>
                                <h3 class="mb-0" id="totalConversions">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exchange-alt fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Conversions Actives</span>
                                <h3 class="mb-0" id="activeConversions">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Produits avec Conversion</span>
                                <h3 class="mb-0" id="productionsWithConversion">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-box fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Déchet Moyen</span>
                                <h3 class="mb-0" id="averageWaste">0%</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-trash fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-exchange-alt me-2"></i>Liste des Conversions Produits
                        </h5>
                        <div>
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addConversionModal">
                                <i class="fas fa-plus me-1"></i> Nouvelle Conversion
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="product-conversions-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Produit Production</th>
                                        <th>Produit Vente</th>
                                        <th>Taux de Conversion</th>
                                        <th>Formule</th>
                                        <th>Taux Effectif</th>
                                        <th>% Déchet</th>
                                        <th>Statut</th>
                                        <th width="10%">Actions</th>
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

    <!-- Add Conversion Modal -->
    <div class="modal fade" id="addConversionModal" tabindex="-1" role="dialog" aria-labelledby="addConversionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addConversionModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Nouvelle Conversion Produit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addConversionForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="parent_product_id" class="form-label">Produit Production *</label>
                                    <select class="form-control select2" id="parent_product_id" name="parent_product_id"
                                        required>
                                        <option value="">Sélectionner un produit production</option>
                                        @foreach ($productionProducts as $product)
                                            <option value="{{ $product->product_id }}">
                                                {{ $product->product_name }} ({{ $product->unit_of_measure }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Produit fabriqué en usine (ex: matelas
                                        200m)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="child_product_id" class="form-label">Produit Vente *</label>
                                    <select class="form-control select2" id="child_product_id" name="child_product_id"
                                        required>
                                        <option value="">Sélectionner un produit vente</option>
                                        @foreach ($salesProducts as $product)
                                            <option value="{{ $product->product_id }}">
                                                {{ $product->product_name }} ({{ $product->unit_of_measure }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Produit vendu au client (ex: matelas final)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="conversion_rate" class="form-label">Taux de Conversion *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">1 →</span>
                                        <input type="number" class="form-control" id="conversion_rate"
                                            name="conversion_rate" required min="0.0001" step="0.0001"
                                            placeholder="Ex: 10">
                                        <span class="input-group-text">unités</span>
                                    </div>
                                    <small class="form-text text-muted">1 produit production = X produits vente</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="waste_percentage" class="form-label">Pourcentage de Déchet</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="waste_percentage"
                                            name="waste_percentage" min="0" max="100" step="0.01"
                                            value="0" placeholder="Ex: 5">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="form-text text-muted">Pourcentage perdu pendant la conversion</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Notes sur la conversion..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Exemple:</strong> Si 1 matelas de 200m produit 10 matelas finaux avec 5% de déchet:<br>
                            • Taux de conversion: 10 (1 → 10)<br>
                            • Déchet: 5%<br>
                            • Taux effectif: 9.5 matelas finaux par matelas de 200m
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Conversion Modal -->
    <div class="modal fade" id="editConversionModal" tabindex="-1" role="dialog"
        aria-labelledby="editConversionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="editConversionModalLabel">
                        <i class="fas fa-edit me-2"></i>Modifier Conversion Produit
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editConversionForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_conversion_id" name="conversion_id">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Produit Production</label>
                                    <input type="text" class="form-control" id="edit_parent_product" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Produit Vente</label>
                                    <input type="text" class="form-control" id="edit_child_product" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="edit_conversion_rate" class="form-label">Taux de Conversion *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">1 →</span>
                                        <input type="number" class="form-control" id="edit_conversion_rate"
                                            name="conversion_rate" required min="0.0001" step="0.0001">
                                        <span class="input-group-text">unités</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="edit_waste_percentage" class="form-label">Pourcentage de Déchet</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="edit_waste_percentage"
                                            name="waste_percentage" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="edit_is_active" class="form-label">Statut</label>
                                    <select class="form-control" id="edit_is_active" name="is_active">
                                        <option value="1">Actif</option>
                                        <option value="0">Inactif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteConversionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette conversion produit ?</p>
                    <p class="text-danger"><strong id="deleteConversionInfo"></strong></p>
                    <p class="text-danger">Cette action affectera les sorties de production futures!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteConversionForm" method="POST">
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
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            // $('.select2').select2({
            //     language: "fr",
            //     placeholder: "Sélectionner...",
            //     allowClear: true
            // });

            // Initialize DataTable
            var table = $('#product-conversions-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: "{{ route('product-conversions.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'parent_product_name',
                        name: 'parentProduct.product_name',
                        render: function(data, type, row) {
                            return data + ' <small class="text-muted">(' + row.parent_product_unit +
                                ')</small>';
                        }
                    },
                    {
                        data: 'child_product_name',
                        name: 'childProduct.product_name',
                        render: function(data, type, row) {
                            return data + ' <small class="text-muted">(' + row.child_product_unit +
                                ')</small>';
                        }
                    },
                    {
                        data: 'conversion_rate',
                        name: 'conversion_rate',
                        className: 'text-center'
                    },
                    {
                        data: 'conversion_formula',
                        name: 'conversion_formula',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'effective_rate',
                        name: 'effective_rate',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'waste_info',
                        name: 'waste_percentage',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
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
                ],
                createdRow: function(row, data, dataIndex) {
                    // Add highlighting for inactive conversions
                    if (!data.is_active) {
                        $(row).addClass('table-secondary');
                    }
                }
            });

            // Load statistics
            loadStatistics();

            // Function to load statistics
            function loadStatistics() {
                $.ajax({
                    url: "{{ route('product-conversions.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalConversions').text(response.data.total);
                            $('#activeConversions').text(response.data.active);
                            $('#productionsWithConversion').text(response.data
                                .productions_with_conversion);
                            $('#averageWaste').text(response.data.average_waste + '%');
                        }
                    },
                    error: function() {
                        // Fallback if statistics endpoint fails
                        $('#totalConversions').text('0');
                        $('#activeConversions').text('0');
                        $('#productionsWithConversion').text('0');
                        $('#averageWaste').text('0%');
                    }
                });
            }

            // Add Conversion Form Submit
            $('#addConversionForm').submit(function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('product-conversions.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#addConversionModal').modal('hide');
                            $('#addConversionForm')[0].reset();
                            $('.select2').val(null).trigger('change');
                            table.draw();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
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

            // Handle edit button click
            var editConversionId;
            $(document).on('click', '.edit-conversion', function() {
                editConversionId = $(this).data('id');

                $.ajax({
                    url: "{{ url('product-conversions') }}/" + editConversionId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var conversion = response.conversion;
                            $('#edit_conversion_id').val(conversion.conversion_id);
                            $('#edit_parent_product').val(conversion.parent_product
                                .product_name + ' (' + conversion.parent_product
                                .unit_of_measure + ')');
                            $('#edit_child_product').val(conversion.child_product.product_name +
                                ' (' + conversion.child_product.unit_of_measure + ')');
                            $('#edit_conversion_rate').val(conversion.conversion_rate);
                            $('#edit_waste_percentage').val(conversion.waste_percentage);
                            $('#edit_is_active').val(conversion.is_active ? '1' : '0');
                            $('#edit_notes').val(conversion.notes || '');

                            $('#editConversionForm').attr('action',
                                "{{ url('product-conversions') }}/" + editConversionId);
                            $('#editConversionModal').modal('show');
                        }
                    }
                });
            });

            // Edit Conversion Form Submit
            $('#editConversionForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');
                var formData = form.serialize();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#editConversionModal').modal('hide');
                            table.draw();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
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

            // Handle delete button click
            var deleteConversionId;
            $(document).on('click', '.delete-conversion', function() {
                deleteConversionId = $(this).data('id');
                var parentId = $(this).data('parent');
                var childId = $(this).data('child');

                // Get product names for confirmation message
                $.ajax({
                    url: "{{ url('product-conversions') }}/" + deleteConversionId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var conversion = response.conversion;
                            var info = conversion.parent_product.product_name + ' → ' +
                                conversion.child_product.product_name;
                            $('#deleteConversionInfo').text(info);
                            $('#deleteConversionForm').attr('action',
                                "{{ url('product-conversions') }}/" + deleteConversionId);
                            $('#deleteConversionModal').modal('show');
                        }
                    }
                });
            });

            // Delete Conversion Form Submit
            $('#deleteConversionForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteConversionModal').modal('hide');
                            table.draw();
                            loadStatistics();
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

            // Auto-refresh statistics every 30 seconds
            setInterval(loadStatistics, 30000);

            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')) +
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

            // Add CSS for inactive rows
            $('head').append(
                '<style>.table-secondary { background-color: rgba(108, 117, 125, 0.1) !important; }</style>'
            );
        });
    </script>
@endpush
