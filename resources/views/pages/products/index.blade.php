@extends('layouts.app')

@section('title', 'Gestion des Articles')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Articles</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Articles
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="statistics row mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total Articles</span>
                                <h3 class="mb-0" id="totalProducts">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fs-1 text-primary"></i>
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
                                <span class="text-muted">Articles Actifs</span>
                                <h3 class="mb-0" id="activeProducts">0</h3>
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
                                <span class="text-muted">Production</span>
                                <h3 class="mb-0" id="productionProducts">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-industry fs-1 text-primary"></i>
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
                                <span class="text-muted">Stock Bas</span>
                                <h3 class="mb-0" id="lowStockProducts">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fs-1 text-warning"></i>
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
                            <i class="fas fa-boxes me-2"></i>Liste des Articles
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" id="filterBtn">
                                <i class="fas fa-filter me-1"></i> Filtres
                            </button>
                            @can('create_products')
                                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Nouveau Article
                                </a>
                            @endcan
                            @can('export_products')
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-light btn-sm dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-download me-1"></i> Exporter
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" id="exportExcelBtn">
                                                <i class="fas fa-file-excel text-success me-2"></i>Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" id="exportPdfBtn">
                                                <i class="fas fa-file-pdf text-danger me-2"></i>PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <div class="row mb-3" id="filtersSection" style="display: none;">
                            <div class="col-md-3">
                                <label for="filterType" class="form-label">Type</label>
                                <select class="form-control" id="filterType" name="product_type">
                                    <option value="">Tous les types</option>
                                    <option value="production">Production</option>
                                    <option value="decoupage">Découpage</option>
                                    <option value="finale">Finale</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterStatus" class="form-label">Statut</label>
                                <select class="form-control" id="filterStatus" name="is_active">
                                    <option value="">Tous</option>
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary me-2" id="applyFilters">
                                    <i class="fas fa-check me-1"></i> Appliquer
                                </button>
                                <button class="btn btn-secondary" id="resetFilters">
                                    <i class="fas fa-undo me-1"></i> Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Products Table -->
                        <div class="table-responsive">
                            <table id="products-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <th width="5%">#</th>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Prix</th>
                                    <th>Stock Total</th>
                                    <th>Statut Stock</th>
                                    <th>Volume</th>
                                    <th width="4%">Actions</th>
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

    <!-- Add Stock Modal -->
    <div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Ajouter du Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addStockForm">
                    @csrf
                    <input type="hidden" name="product_id" id="addStockProductId">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 id="addStockProductName" class="fw-bold"></h6>
                                <div class="small text-muted">
                                    Code: <span id="addStockProductCode" class="fw-bold"></span>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="alert alert-info mb-0 py-2">
                                    <i class="fas fa-boxes me-1"></i>
                                    Stock Actuel: <strong id="addStockCurrentStock">0</strong>
                                    <span id="addStockUnit"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Famille Selection (if product has familles) -->
                        <div id="familleSelectionSection" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Famille *</label>
                                <select class="form-control" id="famille_id" name="famille_id">
                                    <option value="">Sélectionner une famille...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Stock Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Quantité *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="quantity" step="0.001"
                                        min="0.001" required>
                                    <span class="input-group-text" id="quantityUnit"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date d'Ajustement</label>
                                <input type="date" class="form-control" name="movement_date"
                                    value="{{ date('Y-m-d') }}">
                                <small class="text-muted">Par défaut: aujourd'hui</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Type d'Ajustement *</label>
                            <select class="form-control" name="adjustment_type" required>
                                <option value="add">Ajouter au stock (+)</option>
                                <option value="remove">Retirer du stock (-)</option>
                                <option value="set">Définir le stock (=)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes *</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Raison de l'ajustement..." required></textarea>
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

    <!-- Update Family Prices Modal -->
    <div class="modal fade" id="updateFamilyPricesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Modifier les Prix par Famille
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateFamilyPricesForm">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="product_id" id="updatePricesProductId">
                    <div class="modal-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Article:</strong> <span id="updatePricesProductName"></span>
                            (<span id="updatePricesProductCode"></span>)
                            <br>
                            <small>Modifiez directement les prix pour chaque famille associée à ce article.</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="familyPricesTable">
                                <thead class="table-light">
                                    <th width="20%">Famille</th>
                                    <th width="15%">Code</th>
                                    <th width="16.25%">Prix Client (DH)</th>
                                    <th width="16.25%">Prix Grossiste (DH)</th>
                                    <th width="16.25%">Prix Commercial (DH)</th>
                                    <th width="16.25%">Prix Spécial (DH)</th>
                                    </tr>
                                </thead>
                                <tbody id="familyPricesTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Chargement...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention:</strong> Ces modifications s'appliqueront uniquement à ce article pour les
                            familles sélectionnées.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Enregistrer les Modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Famille Stock Details Modal -->
    <div class="modal fade" id="familleStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-boxes me-2"></i>Détail du Stock par Famille
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 id="stockModalProductName" class="fw-bold"></h5>
                            <div class="text-muted">
                                Code: <span id="stockModalProductCode" class="fw-bold"></span>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="alert alert-info mb-0 d-inline-block">
                                <i class="fas fa-cubes me-1"></i>
                                Stock Total: <strong id="stockModalTotalStock">0</strong>
                                <span id="stockModalUnit"></span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Famille</th>
                                    <th>Code</th>
                                    <th class="text-center">Stock Total</th>
                                    <th class="text-center">Stock Disponible</th>
                                    <th class="text-center">Stock Réservé</th>
                                    <th class="text-center">Emplacement</th>
                                    <th class="text-center">Dernier Restock</th>
                                </tr>
                            </thead>
                            <tbody id="familleStockTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Chargement...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Famille Prices Modal (View Only) -->
    <div class="modal fade" id="famillePricesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-tags me-2"></i>Détail des Prix par Famille
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 id="pricesModalProductName" class="fw-bold"></h5>
                            <div class="text-muted">
                                Code: <span id="pricesModalProductCode" class="fw-bold"></span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Famille</th>
                                    <th>Code</th>
                                    <th class="text-center bg-primary text-white">Prix Client</th>
                                    <th class="text-center bg-info text-white">Prix Grossiste</th>
                                    <th class="text-center bg-success text-white">Prix Commercial</th>
                                    <th class="text-center bg-warning text-white">Prix Spécial</th>
                                </tr>
                            </thead>
                            <tbody id="famillePricesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Chargement...</td>
                                </tr>
                            </tbody>
                            <tfoot id="famillePricesTotal" class="table-light">
                                <!-- Will be populated with totals -->
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-trash-alt me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le article :</p>
                    <p class="fw-bold text-center" id="deleteProductName"></p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette action est irréversible !
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i>Supprimer
                        </button>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }

        .select2-filter {
            width: 100% !important;
        }

        .btn-view-famille-stock {
            padding: 0.15rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-view-prices-quick {
            padding: 0.15rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-edit-prices {
            padding: 0.15rem 0.5rem;
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .modal-header {
            padding: 1rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .toast {
            min-width: 300px;
        }

        .bg-primary.text-white,
        .bg-info.text-white,
        .bg-success.text-white,
        .bg-warning.text-white {
            font-weight: 600;
        }

        .price-input {
            font-weight: bold;
        }

        .price-input:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }

        /* Add to your styles section */
        #updateFamilyPricesModal .modal-xl {
            max-width: 1400px;
        }

        #updateFamilyPricesModal .price-input {
            font-weight: bold;
            text-align: right;
            padding-right: 10px;
        }

        #updateFamilyPricesModal .price-input:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
            background-color: #fff;
        }

        #updateFamilyPricesModal .input-group-text {
            background-color: #f8f9fa;
            font-weight: 500;
        }

        #updateFamilyPricesModal table th {
            text-align: center;
            vertical-align: middle;
        }

        #updateFamilyPricesModal table td {
            vertical-align: middle;
            padding: 12px;
        }

        #updateFamilyPricesModal .table-responsive {
            max-height: 60vh;
            overflow-y: auto;
        }

        #updateFamilyPricesModal .badge {
            font-size: 0.9rem;
            padding: 6px 12px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for filters
            $('.select2-filter').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            // Toggle filters
            $('#filterBtn').click(function() {
                $('#filtersSection').slideToggle();
            });

            // Initialize DataTable
            var table = $('#products-table').DataTable({
                paging: true,
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Tout']
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('products.index') }}",
                    data: function(d) {
                        d.product_type = $('#filterType').val();
                        d.is_active = $('#filterStatus').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'product_code',
                        name: 'product_code',
                        className: 'font-weight-bold'
                    },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'famille_prices',
                        name: 'famille_prices',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'current_stock',
                        name: 'total_stock',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'stock_status',
                        name: 'stock_status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'volume',
                        name: 'volume_m3',
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
                pageLength: 25
            });

            // Apply filters
            $('#applyFilters').click(function() {
                table.ajax.reload();
            });

            // Reset filters
            $('#resetFilters').click(function() {
                $('#filterType').val('');
                $('#filterStatus').val('');
                table.ajax.reload();
            });

            // Load statistics
            loadStatistics();

            function loadStatistics() {
                $.ajax({
                    url: "{{ route('products.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalProducts').text(response.data.total);
                            $('#activeProducts').text(response.data.active);
                            $('#productionProducts').text(response.data.production);
                            $('#lowStockProducts').text(response.data.low_stock);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading statistics:', xhr);
                    }
                });
            }

            // Handle add stock button click
            $(document).on('click', '.btn-add-stock', function() {
                var productId = $(this).data('product-id');
                var productName = $(this).data('product-name');
                var productCode = $(this).data('product-code');
                var hasFamilles = $(this).data('has-familles') == 1;
                var currentStock = $(this).data('current-stock');
                var unit = $(this).data('unit');

                // Reset form
                $('#addStockForm')[0].reset();
                $('#addStockProductId').val(productId);
                $('#addStockProductName').text(productName);
                $('#addStockProductCode').text(productCode);
                $('#addStockCurrentStock').text(currentStock);
                $('#addStockUnit').text(unit);
                $('#quantityUnit').text(unit);

                // Show/hide famille selection
                if (hasFamilles) {
                    $('#familleSelectionSection').show();
                    $('#famille_id').prop('required', true);

                    // Load familles for this product
                    $.ajax({
                        url: "{{ url('products') }}/" + productId + "/familles",
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                var select = $('#famille_id');
                                select.empty().append(
                                    '<option value="">Sélectionner une famille...</option>');
                                if (response.familles && response.familles.length > 0) {
                                    $.each(response.familles, function(index, famille) {
                                        select.append('<option value="' + famille
                                            .famille_id + '">' +
                                            famille.famille_name + ' (' + famille
                                            .famille_code + ')' +
                                            '</option>');
                                    });
                                } else {
                                    select.append(
                                        '<option value="" disabled>Aucune famille disponible</option>'
                                    );
                                }
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading familles:', xhr);
                            showToast('error', 'Erreur lors du chargement des familles');
                        }
                    });
                } else {
                    $('#familleSelectionSection').hide();
                    $('#famille_id').prop('required', false);
                }

                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('addStockModal'));
                modal.show();
            });

            // Handle edit family prices button click
            $(document).on('click', '.btn-edit-prices', function() {
                var productId = $(this).data('product-id');
                var productName = $(this).data('product-name');
                var productCode = $(this).data('product-code');

                // Set modal info
                $('#updatePricesProductId').val(productId);
                $('#updatePricesProductName').text(productName);
                $('#updatePricesProductCode').text(productCode);
                $('#familyPricesTableBody').html(
                    '<tr><td colspan="6" class="text-center text-muted">Chargement...</td></tr>');

                // Load famille prices for this product
                $.ajax({
                    url: "{{ url('products') }}/" + productId + "/familles",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var tableBody = $('#familyPricesTableBody');
                            tableBody.empty();

                            if (response.familles && response.familles.length > 0) {
                                $.each(response.familles, function(index, famille) {
                                    var row = $('<tr>');

                                    // Famille Name
                                    row.append('<td class="fw-bold">' + escapeHtml(
                                            famille.famille_name || 'N/A') +
                                        '</td>');

                                    // Famille Code
                                    row.append('<td><span class="badge bg-secondary">' +
                                        escapeHtml(famille.famille_code || 'N/A') +
                                        '</span></td>');

                                    // Prix Client
                                    row.append('\
                                                                                    <td>\
                                                                                        <div class="input-group">\
                                                                                            <input type="number" \
                                                                                                   class="form-control price-input text-end fw-bold" \
                                                                                                   name="prices[' + famille
                                        .famille_id + '][prix_client]" \
                                                                                                   value="' + parseFloat(
                                            famille
                                            .prix_client ||
                                            0)
                                        .toFixed(
                                            2) + '" \
                                                                                                   step="0.01" \
                                                                                                   min="0" \
                                                                                                   style="font-size: 1rem; background-color: #fff3e0;">\
                                                                                            <span class="input-group-text">DH</span>\
                                                                                        </div>\
                                                                                    </td>\
                                                                                ');

                                    // Prix Grossiste
                                    row.append('\
                                                                                    <td>\
                                                                                        <div class="input-group">\
                                                                                            <input type="number" \
                                                                                                   class="form-control price-input text-end fw-bold" \
                                                                                                   name="prices[' + famille
                                        .famille_id + '][prix_grossiste]" \
                                                                                                   value="' + parseFloat(
                                            famille
                                            .prix_grossiste ||
                                            0)
                                        .toFixed(
                                            2) + '" \
                                                                                                   step="0.01" \
                                                                                                   min="0" \
                                                                                                   style="font-size: 1rem; background-color: #e8f5e9;">\
                                                                                            <span class="input-group-text">DH</span>\
                                                                                        </div>\
                                                                                    </td>\
                                                                                ');

                                    // Prix Commercial
                                    row.append('\
                                                                                    <td>\
                                                                                        <div class="input-group">\
                                                                                            <input type="number" \
                                                                                                   class="form-control price-input text-end fw-bold" \
                                                                                                   name="prices[' + famille
                                        .famille_id + '][prix_commercial]" \
                                                                                                   value="' + parseFloat(
                                            famille
                                            .prix_commercial ||
                                            0)
                                        .toFixed(
                                            2) + '" \
                                                                                                   step="0.01" \
                                                                                                   min="0" \
                                                                                                   style="font-size: 1rem; background-color: #e3f2fd;">\
                                                                                            <span class="input-group-text">DH</span>\
                                                                                        </div>\
                                                                                    </td>\
                                                                                ');

                                    // Prix Spécial
                                    row.append('\
                                                                                    <td>\
                                                                                        <div class="input-group">\
                                                                                            <input type="number" \
                                                                                                   class="form-control price-input text-end fw-bold" \
                                                                                                   name="prices[' + famille
                                        .famille_id + '][prix_special]" \
                                                                                                   value="' + parseFloat(
                                            famille
                                            .prix_special || 0)
                                        .toFixed(
                                            2) + '" \
                                                                                                   step="0.01" \
                                                                                                   min="0" \
                                                                                                   style="font-size: 1rem; background-color: #fff3e0;">\
                                                                                            <span class="input-group-text">DH</span>\
                                                                                        </div>\
                                                                                    </td>\
                                                                                ');

                                    tableBody.append(row);
                                });
                            } else {
                                tableBody.html(
                                    '<tr><td colspan="6" class="text-center text-muted">Aucune famille associée à ce article</td></tr>'
                                );
                            }
                        } else {
                            $('#familyPricesTableBody').html(
                                '<tr><td colspan="6" class="text-center text-danger">' +
                                response.message + '</td></tr>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading famille prices:', xhr);
                        $('#familyPricesTableBody').html(
                            '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des prix</td></tr>'
                        );
                        showToast('error', 'Erreur lors du chargement des prix par famille');
                    }
                });

                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('updateFamilyPricesModal'));
                modal.show();
            });

            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Handle update family prices form submission
            $('#updateFamilyPricesForm').submit(function(e) {
                e.preventDefault();

                var productId = $('#updatePricesProductId').val();
                var url = "{{ url('products') }}/" + productId + "/update-family-prices";

                // Disable submit button
                var submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#updateFamilyPricesModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de la mise à jour des prix');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Enregistrer les Modifications'
                        );
                    }
                });
            });

            // Export functions
            $('#exportExcelBtn').click(function(e) {
                e.preventDefault();

                var filters = {
                    product_type: $('#filterType').val(),
                    status: $('#filterStatus').val()
                };

                var queryString = $.param(filters);
                window.location.href = "{{ route('products.export.excel') }}?" + queryString;
            });

            $('#exportPdfBtn').click(function(e) {
                e.preventDefault();

                var filters = {
                    product_type: $('#filterType').val(),
                    status: $('#filterStatus').val()
                };

                var queryString = $.param(filters);
                window.location.href = "{{ route('products.export.pdf') }}?" + queryString;
            });

            // Handle add stock form submission
            $('#addStockForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var productId = $('#addStockProductId').val();
                var url = "{{ url('products/add-stock') }}/" + productId;

                // Disable submit button
                var submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#addStockModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de l\'ajout du stock');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html('Enregistrer');
                    }
                });
            });

            // Handle famille stock view button click
            $(document).on('click', '.btn-view-famille-stock', function() {
                var productId = $(this).data('product-id');
                var productName = $(this).data('product-name');
                var productCode = $(this).data('product-code');
                var unit = $(this).data('unit');

                // Set modal info
                $('#stockModalProductName').text(productName);
                $('#stockModalProductCode').text(productCode);
                $('#stockModalUnit').text(unit);
                $('#familleStockTableBody').html(
                    '<tr><td colspan="7" class="text-center text-muted">Chargement...</td></tr>'
                );

                // Load famille stock details
                $.ajax({
                    url: "{{ route('products.famille-stock', ':id') }}".replace(':id', productId),
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#stockModalTotalStock').text(response.product.total_stock
                                .toFixed(2));

                            var tableBody = $('#familleStockTableBody');
                            tableBody.empty();

                            if (response.famille_stocks && response.famille_stocks.length > 0) {
                                $.each(response.famille_stocks, function(index, famille) {
                                    var row = $('<tr>');
                                    row.append('<td class="fw-bold">' + (famille
                                        .famille_name || 'N/A') + '</td>');
                                    row.append('<td>' + (famille.famille_code ||
                                        'N/A') + '</td>');
                                    row.append('<td class="text-center fw-bold">' +
                                        parseFloat(famille.current_quantity || 0)
                                        .toFixed(2) + '</td>');
                                    row.append('<td class="text-center text-success">' +
                                        parseFloat(famille.available_quantity || 0)
                                        .toFixed(2) + '</td>');
                                    row.append('<td class="text-center text-warning">' +
                                        parseFloat(famille.reserved_quantity || 0)
                                        .toFixed(2) + '</td>');
                                    row.append('<td class="text-center">' + (famille
                                            .location || 'Entrepôt Principal') +
                                        '</td>');
                                    row.append('<td class="text-center">' + (famille
                                        .last_restocked || 'Jamais') + '</td>');
                                    tableBody.append(row);
                                });
                            } else {
                                tableBody.html(
                                    '<tr><td colspan="7" class="text-center text-muted">Aucun stock par famille</td></tr>'
                                );
                            }
                        } else {
                            $('#familleStockTableBody').html(
                                '<tr><td colspan="7" class="text-center text-danger">' +
                                response.message + '</td></tr>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading famille stock:', xhr);
                        $('#familleStockTableBody').html(
                            '<tr><td colspan="7" class="text-center text-danger">Erreur lors du chargement des données</td></tr>'
                        );
                        showToast('error', 'Erreur lors du chargement du stock par famille');
                    }
                });

                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('familleStockModal'));
                modal.show();
            });

            // Handle view prices button click (from dropdown)
            $(document).on('click', '.btn-view-prices', function() {
                loadFamillePrices($(this));
            });

            // Handle view prices button click (from quick view in table)
            $(document).on('click', '.btn-view-prices-quick', function() {
                loadFamillePrices($(this));
            });

            function loadFamillePrices(btn) {
                var productId = btn.data('product-id');
                var productName = btn.data('product-name');
                var productCode = btn.data('product-code');

                // Set modal info
                $('#pricesModalProductName').text(productName);
                $('#pricesModalProductCode').text(productCode);
                $('#famillePricesTableBody').html(
                    '<tr><td colspan="7" class="text-center text-muted">Chargement...</td></tr>'
                );
                $('#famillePricesTotal').empty();

                // Load famille prices
                $.ajax({
                    url: "{{ url('products') }}/" + productId + "/familles",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            var tableBody = $('#famillePricesTableBody');
                            tableBody.empty();

                            if (response.familles && response.familles.length > 0) {
                                var totalClient = 0,
                                    totalGrossiste = 0,
                                    totalCommercial = 0,
                                    totalSpecial = 0;

                                $.each(response.familles, function(index, famille) {
                                    var prixClient = parseFloat(famille.prix_client || 0);
                                    var prixGrossiste = parseFloat(famille.prix_grossiste || 0);
                                    var prixCommercial = parseFloat(famille.prix_commercial ||
                                        0);
                                    var prixSpecial = parseFloat(famille.prix_special || 0);

                                    totalClient += prixClient;
                                    totalGrossiste += prixGrossiste;
                                    totalCommercial += prixCommercial;
                                    totalSpecial += prixSpecial;

                                    var row = $('<tr>');
                                    row.append('<td class="fw-bold">' + (famille.famille_name ||
                                        'N/A') + '</td>');
                                    row.append('<td>' + (famille.famille_code || 'N/A') +
                                        '</td>');
                                    row.append('<td class="text-center text-primary fw-bold">' +
                                        prixClient.toFixed(2) + ' DH</td>');
                                    row.append('<td class="text-center text-info fw-bold">' +
                                        prixGrossiste.toFixed(2) + ' DH</td>');
                                    row.append('<td class="text-center text-success fw-bold">' +
                                        prixCommercial.toFixed(2) + ' DH</td>');
                                    row.append('<td class="text-center text-warning fw-bold">' +
                                        prixSpecial.toFixed(2) + ' DH</td>');
                                    tableBody.append(row);
                                });

                                // Add totals row
                                // var totalRow = $('<tr class="table-light">');
                                // totalRow.append('<td colspan="2" class="fw-bold text-end">TOTAL:</td>');
                                // totalRow.append(
                                //     '<td class="text-center fw-bold bg-primary text-white">' +
                                //     totalClient.toFixed(2) + ' DH</td>');
                                // totalRow.append('<td class="text-center fw-bold bg-info text-white">' +
                                //     totalGrossiste.toFixed(2) + ' DH</td>');
                                // totalRow.append(
                                //     '<td class="text-center fw-bold bg-success text-white">' +
                                //     totalCommercial.toFixed(2) + ' DH</td>');
                                // totalRow.append(
                                //     '<td class="text-center fw-bold bg-warning text-white">' +
                                //     totalSpecial.toFixed(2) + ' DH</td>');
                                // $('#famillePricesTotal').html(totalRow);

                            } else {
                                tableBody.html(
                                    '<tr><td colspan="7" class="text-center text-muted">Aucune famille associée à ce article</td></tr>'
                                );
                            }
                        } else {
                            $('#famillePricesTableBody').html(
                                '<tr><td colspan="7" class="text-center text-danger">' + response
                                .message + '</td></tr>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading famille prices:', xhr);
                        $('#famillePricesTableBody').html(
                            '<tr><td colspan="7" class="text-center text-danger">Erreur lors du chargement des prix</td></tr>'
                        );
                        showToast('error', 'Erreur lors du chargement des prix par famille');
                    }
                });

                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('famillePricesModal'));
                modal.show();
            }

            // Handle delete button click
            $(document).on('click', '.delete', function() {
                var productId = $(this).data('id');
                var productName = $(this).data('name');

                $('#deleteProductName').text(productName);
                $('#deleteForm').attr('action', "{{ url('products') }}/" + productId);

                var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });

            // Delete Form Submit
            $('#deleteForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');

                // Disable submit button
                var submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Suppression...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteModal').modal('hide');
                            table.ajax.reload();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-trash-alt me-1"></i>Supprimer');
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de la suppression');
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-trash-alt me-1"></i>Supprimer');
                    }
                });
            });

            // Auto-refresh statistics every 30 seconds
            setInterval(loadStatistics, 30000);

            // Toast notification function
            function showToast(type, message) {
                var toastId = 'toast-' + Date.now();
                var bgColor = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning' : 'bg-danger');

                var toast = $('<div id="' + toastId + '" class="toast align-items-center text-white ' + bgColor +
                    ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                    '</div>' +
                    '</div>');

                $('#toast-container').append(toast);

                var bsToast = new bootstrap.Toast(toast[0], {
                    autohide: true,
                    delay: 5000
                });

                bsToast.show();

                // Remove toast after it's hidden
                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }
        });
    </script>
@endpush
