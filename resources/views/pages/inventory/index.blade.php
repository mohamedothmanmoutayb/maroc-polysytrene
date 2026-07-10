@extends('layouts.app')

@section('title', 'Gestion des Stocks')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Stocks</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Inventaire
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
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-primary bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ $totalProducts }}</h4>
                                <p class="fs-3 mb-0">Total Produits</p>
                            </div>
                            <div class="text-primary">
                                <iconify-icon icon="solar:box-outline" class="fas"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-success bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ number_format($totalProductStock, 2, ',', '.') }}</h4>
                                <p class="fs-3 mb-0">Stock Produits (U)</p>
                            </div>
                            <div class="text-success">
                                <iconify-icon icon="solar:box-minimalistic-outline" class="fas"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-warning bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ $totalRawMaterials }}</h4>
                                <p class="fs-3 mb-0">Matières Premières</p>
                            </div>
                            <div class="text-warning">
                                <iconify-icon icon="arcticons:arcticons-material-you" class="fas"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                <div class="card bg-danger bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold mb-2">{{ $pendingAdjustments }}</h4>
                                <p class="fs-3 mb-0">Ajustements en attente</p>
                            </div>
                            <div class="text-danger">
                                <iconify-icon icon="solar:clock-circle-outline" class="fas"></iconify-icon>
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
                            <i class="fas fa-boxes me-2"></i>Gestion d'Inventaire
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Tabs navigation -->
                        <ul class="nav nav-tabs mb-4" id="inventoryTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="products-tab" data-bs-toggle="tab"
                                    data-bs-target="#products" type="button" role="tab" aria-controls="products"
                                    aria-selected="true">
                                    <i class="fas fa-box me-2"></i>Produits Finis
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="raw-materials-tab" data-bs-toggle="tab"
                                    data-bs-target="#raw-materials" type="button" role="tab"
                                    aria-controls="raw-materials" aria-selected="false">
                                    <i class="fas fa-cubes me-2"></i>Matières Premières
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="adjustments-tab" data-bs-toggle="tab"
                                    data-bs-target="#adjustments" type="button" role="tab" aria-controls="adjustments"
                                    aria-selected="false">
                                    <i class="fas fa-clipboard-list me-2 text-warning"></i>Ajustements en attente
                                    @if ($pendingAdjustments > 0)
                                        <span class="badge bg-warning ms-2">{{ $pendingAdjustments }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content" id="inventoryTabsContent">
                            <!-- Products Tab -->
                            <div class="tab-pane fade show active" id="products" role="tabpanel"
                                aria-labelledby="products-tab">
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded">
                                    <button id="validate-all-btn" class="btn btn-primary" disabled>
                                        <i class="fas fa-paper-plane me-1"></i>Envoyer pour approbation
                                    </button>
                                    <button id="reset-all-btn" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Effacer
                                    </button>
                                    <small class="text-muted ms-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Saisissez les nouvelles quantités puis envoyez pour approbation.
                                    </small>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="products-table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Produit</th>
                                                <th>Type</th>
                                                <th>Stock par Famille</th>
                                                <th>Stock Total</th>
                                                <th>Stock Min</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Raw Materials Tab -->
                            <div class="tab-pane fade" id="raw-materials" role="tabpanel"
                                aria-labelledby="raw-materials-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="raw-materials-table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Matière Première</th>
                                                <th>Catégorie</th>
                                                <th>Unité</th>
                                                <th>Stock Actuel</th>
                                                <th>Stock Min</th>
                                                <th>Stock Max</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Adjustments Tab -->
                            <div class="tab-pane fade" id="adjustments" role="tabpanel"
                                aria-labelledby="adjustments-tab">
                                <div class="d-flex justify-content-end mb-3">
                                    <button id="approve-all-btn" class="btn btn-success">
                                        <i class="fas fa-check-double me-1"></i>Approuver tout
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="adjustments-table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Référence</th>
                                                <th>Ancien Stock</th>
                                                <th>Nouveau Stock</th>
                                                <th>Variation</th>
                                                <th>Raison</th>
                                                <th>Demandé par</th>
                                                <th>Date demande</th>
                                                <th>Actions</th>
                                            </tr>
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
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-labelledby="stockAdjustmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stockAdjustmentModalLabel">
                        <i class="fas fa-edit me-2"></i>Ajuster le Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="stockAdjustmentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Produit / Matière</label>
                            <p class="form-control-plaintext" id="adjustmentItemName"></p>
                        </div>

                        <div class="mb-3" id="familySelectContainer" style="display:none;">
                            <label class="form-label fw-bold">Famille <span class="text-danger">*</span></label>
                            <select class="form-select" id="familySelect" name="famille_id">
                                <option value="">Sélectionner une famille</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Stock Actuel</label>
                            <p class="form-control-plaintext" id="currentStockDisplay"></p>
                            <input type="hidden" id="currentStockValue" name="old_quantity">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nouveau Stock <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" class="form-control" id="newStockValue"
                                name="new_quantity" required>
                            <small class="text-muted">Entrez la nouvelle quantité en stock</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Raison de l'ajustement</label>
                            <textarea class="form-control" id="adjustmentReason" name="reason" rows="3"
                                placeholder="Ex: Inventaire physique, Correction d'erreur, Retour client, etc."></textarea>
                        </div>

                        <input type="hidden" id="adjustmentType" name="adjustment_type">
                        <input type="hidden" id="referenceId" name="reference_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Soumettre la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Adjustment Modal -->
    <div class="modal fade" id="rejectAdjustmentModal" tabindex="-1" aria-labelledby="rejectAdjustmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectAdjustmentModalLabel">
                        <i class="fas fa-times-circle me-2 text-danger"></i>Rejeter l'ajustement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectAdjustmentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Motif du rejet <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectReason" name="admin_notes" rows="4" required
                                placeholder="Expliquez pourquoi cet ajustement est rejeté..."></textarea>
                        </div>
                        <input type="hidden" id="rejectAdjustmentId" name="adjustment_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i>Confirmer le rejet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Raw Material Direct Adjustment Modal -->
    <div class="modal fade" id="rawMaterialAdjustModal" tabindex="-1" aria-labelledby="rawMaterialAdjustModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rawMaterialAdjustModalLabel">
                        <i class="fas fa-cubes me-2"></i>Ajuster le stock — <span id="rmModalName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rawMaterialAdjustForm">
                    @csrf
                    <div class="modal-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold">Stock actuel :</span>
                            <span class="fs-5 fw-semibold text-primary" id="rmCurrentStock">—</span>
                        </div>

                        {{-- Existing FIFO lots --}}
                        <div id="rmLotsSection">
                            <h6 class="fw-bold mb-2"><i class="fas fa-layer-group me-1 text-secondary"></i>Lots en stock
                                (FIFO)</h6>
                            <div id="rmLotsEmpty" class="text-muted small fst-italic mb-2" style="display:none;">Aucun
                                lot existant pour cette matière.</div>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered mb-0" id="rmLotsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date entrée</th>
                                            <th class="text-end">Qté entrée</th>
                                            <th class="text-end">Qté restante</th>
                                            <th class="text-end">Prix unitaire</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="rmLotsTbody"></tbody>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <h6 class="fw-bold mb-3"><i class="fas fa-edit me-1 text-warning"></i>Définir le nouveau stock</h6>
                        <div class="alert alert-warning py-2 small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            La valeur saisie <strong>remplacera</strong> le stock actuel (pas d'ajout).
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nouveau stock <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.0001" min="0" class="form-control"
                                        id="rmQtyToAdd" name="quantity_to_add" required placeholder="0.00">
                                    <span class="input-group-text" id="rmUnitLabel">U</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Prix unitaire (DH) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        id="rmUnitPrice" name="unit_price" required placeholder="0.00">
                                    <span class="input-group-text">DH</span>
                                </div>
                                <small class="text-muted">Cliquez sur un lot existant pour utiliser son prix.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Raison <span
                                        class="text-muted fw-normal">(optionnel)</span></label>
                                <input type="text" class="form-control" id="rmReason" name="reason"
                                    placeholder="Ex: Achat fournisseur, Inventaire physique...">
                            </div>
                        </div>

                        <input type="hidden" id="rmMaterialId" name="material_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Soumettre la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>

    <!-- Floating validate button -->
    <div id="floating-validate-btn" class="floating-validate-btn">
        <button id="floating-validate-trigger" class="btn btn-primary shadow-lg" disabled>
            <i class="fas fa-paper-plane me-2"></i>
            <span id="floating-btn-label">Envoyer pour approbation</span>
        </button>
    </div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .nav-tabs .nav-link {
            font-weight: 500;
            color: #495057;
        }

        .nav-tabs .nav-link.active {
            background-color: #1268c5;
            color: #fff;
            border-color: #1268c5;
        }

        .nav-tabs .nav-link i {
            font-size: 1.1rem;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .table-responsive .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 11px;
            padding: 5px 10px;
        }

        .inline-stock-input {
            transition: border-color 0.2s, background-color 0.2s;
        }

        .inline-stock-input::-webkit-outer-spin-button,
        .inline-stock-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .inline-stock-input[type=number] {
            -moz-appearance: textfield;
        }

        .inline-stock-input.is-modified {
            border-color: #fd7e14 !important;
            background-color: #fff8f0;
        }

        .floating-validate-btn {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            z-index: 1050;
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
            pointer-events: none;
        }

        .floating-validate-btn.visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .floating-validate-btn .btn {
            padding: 10px 28px;
            font-size: 0.95rem;
            border-radius: 50px;
            white-space: nowrap;
        }

        .floating-validate-btn .btn:not(:disabled) {
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 4px 20px rgba(18, 104, 197, 0.4);
            }

            50% {
                box-shadow: 0 4px 32px rgba(18, 104, 197, 0.75);
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Common DataTable configuration
            var commonConfig = {
                processing: true,
                serverSide: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                responsive: true
            };

            // Track pending inline stock changes: key = "productId_familleId"
            var pendingChanges = {};

            function updateValidateBtn() {
                var count = Object.keys(pendingChanges).length;
                var $btn = $('#validate-all-btn');
                var $floatBtn = $('#floating-validate-trigger');
                if (count > 0) {
                    $btn.prop('disabled', false)
                        .html('<i class="fas fa-paper-plane me-1"></i>Envoyer pour approbation (' + count + ')');
                    $floatBtn.prop('disabled', false);
                    $('#floating-btn-label').text('Envoyer pour approbation (' + count + ')');
                } else {
                    $btn.prop('disabled', true)
                        .html('<i class="fas fa-paper-plane me-1"></i>Envoyer pour approbation');
                    $floatBtn.prop('disabled', true);
                    $('#floating-btn-label').text('Envoyer pour approbation');
                }
            }

            // Floating button scroll visibility
            $(window).on('scroll.floatingBtn', function() {
                var originalBottom = $('#validate-all-btn')[0].getBoundingClientRect().bottom;
                var $floatingWrapper = $('#floating-validate-btn');
                if (originalBottom < 0) {
                    $floatingWrapper.addClass('visible');
                } else {
                    $floatingWrapper.removeClass('visible');
                }
            });

            // Floating button click delegates to the original button
            $('#floating-validate-trigger').on('click', function() {
                $('#validate-all-btn').trigger('click');
            });

            function reapplyPendingVisuals() {
                // Re-fill inputs that are still pending (e.g. after DataTable redraw)
                Object.values(pendingChanges).forEach(function(c) {
                    var $input = $('.inline-stock-input[data-product-id="' + c.product_id +
                        '"][data-famille-id="' + c.famille_id + '"]');
                    if ($input.length) {
                        $input.val(c.new_quantity).addClass('is-modified');
                    }
                });
                updateValidateBtn();
            }

            // Products Table
            var productsTable = $('#products-table').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('inventory.products.data') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'product_name_with_code',
                        name: 'product_name'
                    },
                    {
                        data: 'product_type',
                        name: 'product_type'
                    },
                    {
                        data: 'families_stock',
                        name: 'families_stock',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total_stock',
                        name: 'total_stock',
                        className: 'text-end'
                    },
                    {
                        data: 'min_stock',
                        name: 'min_stock_level',
                        className: 'text-end'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function() {
                    reapplyPendingVisuals();
                }
            });

            // Raw Materials Table
            var rawMaterialsTable = $('#raw-materials-table').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('inventory.raw-materials.data') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'material_name_with_code',
                        name: 'material_name'
                    },
                    {
                        data: 'category_name',
                        name: 'category_id'
                    },
                    {
                        data: 'unit_of_measure',
                        name: 'unit_of_measure'
                    },
                    {
                        data: 'current_stock_display',
                        name: 'current_stock',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'min_stock',
                        name: 'min_stock_level',
                        className: 'text-end'
                    },
                    {
                        data: 'max_stock',
                        name: 'max_stock_level',
                        className: 'text-end'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Adjustments Table
            var adjustmentsTable = $('#adjustments-table').DataTable({ paging: false, lengthChange: false, 
                ...commonConfig,
                ajax: {
                    url: "{{ route('inventory.pending-adjustments.data') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'reference_info',
                        name: 'reference_info',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'old_quantity_formatted',
                        name: 'old_quantity',
                        className: 'text-end'
                    },
                    {
                        data: 'new_quantity_formatted',
                        name: 'new_quantity',
                        className: 'text-end'
                    },
                    {
                        data: 'adjusted_quantity_formatted',
                        name: 'adjusted_quantity',
                        className: 'text-end'
                    },
                    {
                        data: 'reason',
                        name: 'reason'
                    },
                    {
                        data: 'requested_by_name',
                        name: 'requested_by'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        width: '15%'
                    }
                ]
            });

            // Handle adjust family stock button click
            $(document).on('click', '.adjust-famille-stock', function() {
                var productId = $(this).data('product-id');
                var productName = $(this).data('product-name');
                var familleId = $(this).data('famille-id');
                var familleName = $(this).data('famille-name');
                var currentStock = $(this).data('current-stock');

                $('#adjustmentItemName').text(productName + ' - ' + familleName);
                $('#currentStockDisplay').text(parseFloat(currentStock).toLocaleString('de-DE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' U');
                $('#currentStockValue').val(currentStock);
                $('#adjustmentType').val('product_famille');
                $('#referenceId').val(productId);

                // Set famille select
                $('#familySelectContainer').show();
                $('#familySelect').html('<option value="' + familleId + '" selected>' + familleName +
                    '</option>');

                $('#stockAdjustmentModal').modal('show');
            });

            // Handle adjust product stock button click (products without families)
            $(document).on('click', '.adjust-product-stock', function() {
                var productId = $(this).data('product-id');
                var productName = $(this).data('product-name');
                var currentStock = $(this).data('current-stock');

                $('#adjustmentItemName').text(productName);
                $('#currentStockDisplay').text(parseFloat(currentStock).toLocaleString('de-DE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' U');
                $('#currentStockValue').val(currentStock);
                $('#adjustmentType').val('product_famille');
                $('#referenceId').val(productId);

                // Load families for this product
                $.ajax({
                    url: "{{ url('inventory/product') }}/" + productId + "/families",
                    type: "GET",
                    success: function(response) {
                        if (response.success && response.families.length > 0) {
                            var options = '<option value="">Sélectionner une famille</option>';
                            $.each(response.families, function(index, famille) {
                                options += '<option value="' + famille.id +
                                    '" data-current="' + famille.current_stock + '">' +
                                    famille.name + ' (Stock: ' + famille.current_stock +
                                    ')</option>';
                            });
                            $('#familySelect').html(options);
                            $('#familySelectContainer').show();
                        } else {
                            $('#familySelectContainer').hide();
                        }
                    },
                    error: function() {
                        $('#familySelectContainer').hide();
                    }
                });

                $('#stockAdjustmentModal').modal('show');
            });

            // Handle adjust raw material button click — opens dedicated modal with FIFO lots
            $(document).on('click', '.adjust-raw-material', function() {
                var materialId = $(this).data('material-id');
                var materialName = $(this).data('material-name');
                var unit = $(this).data('unit');

                $('#rmModalName').text(materialName);
                $('#rmMaterialId').val(materialId);
                $('#rmUnitLabel').text(unit || 'U');
                $('#rmCurrentStock').text('Chargement…');
                $('#rmLotsTbody').empty();
                $('#rmQtyToAdd').val('');
                $('#rmUnitPrice').val('');
                $('#rmReason').val('');

                // Load current stock + existing FIFO lots
                $.get("{{ url('inventory/raw-material') }}/" + materialId + "/details", function(res) {
                    if (!res.success) return;
                    var fmt = function(n) {
                        return parseFloat(n).toLocaleString('de-DE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    };
                    $('#rmCurrentStock').text(fmt(res.current_stock) + ' ' + (res.unit || 'U'));

                    var tbody = $('#rmLotsTbody');
                    tbody.empty();
                    if (res.details.length === 0) {
                        $('#rmLotsTable').hide();
                        $('#rmLotsEmpty').show();
                    } else {
                        $('#rmLotsTable').show();
                        $('#rmLotsEmpty').hide();
                        $.each(res.details, function(i, d) {
                            tbody.append(
                                '<tr>' +
                                '<td>' + d.date + '</td>' +
                                '<td class="text-end">' + fmt(d.quantity) + '</td>' +
                                '<td class="text-end fw-bold">' + fmt(d
                                    .remaining_quantity) + '</td>' +
                                '<td class="text-end">' + fmt(d.unit_price) +
                                ' DH</td>' +
                                '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 use-lot-price" data-price="' +
                                d.unit_price +
                                '"><i class="fas fa-tag me-1"></i>Utiliser</button></td>' +
                                '</tr>'
                            );
                        });
                    }
                });

                $('#rawMaterialAdjustModal').modal('show');
            });

            // Pre-fill unit price from existing lot
            $(document).on('click', '.use-lot-price', function() {
                $('#rmUnitPrice').val($(this).data('price'));
                $('#rmUnitPrice').focus();
            });

            // Handle family selection change
            $('#familySelect').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var currentStock = selectedOption.data('current');
                if (currentStock !== undefined) {
                    $('#currentStockDisplay').text(parseFloat(currentStock).toLocaleString('de-DE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' U');
                    $('#currentStockValue').val(currentStock);
                }
            });

            // Submit stock adjustment form
            $('#stockAdjustmentForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                Swal.fire({
                    title: 'Confirmation',
                    text: "Vous allez soumettre une demande d'ajustement de stock. Un administrateur doit approuver cette modification.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, soumettre',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('inventory.request-adjustment') }}",
                            type: "POST",
                            data: formData,
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    $('#stockAdjustmentModal').modal('hide');
                                    $('#stockAdjustmentForm')[0].reset();

                                    // Refresh tables
                                    productsTable.ajax.reload();
                                    rawMaterialsTable.ajax.reload();
                                    adjustmentsTable.ajax.reload();
                                } else {
                                    showToast('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                var response = xhr.responseJSON;
                                showToast('error', response?.message ||
                                    'Une erreur est survenue');
                            }
                        });
                    }
                });
            });

            // Approve adjustment
            $(document).on('click', '.approve-adjustment', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Approuver l\'ajustement',
                    text: "Voulez-vous approuver cette demande d'ajustement de stock ?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, approuver',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('inventory/approve-adjustment') }}/" + id,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    productsTable.ajax.reload();
                                    rawMaterialsTable.ajax.reload();
                                    adjustmentsTable.ajax.reload();
                                } else {
                                    showToast('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                showToast('error', 'Une erreur est survenue');
                            }
                        });
                    }
                });
            });

            // Show reject modal
            $(document).on('click', '.reject-adjustment', function() {
                var id = $(this).data('id');
                $('#rejectAdjustmentId').val(id);
                $('#rejectAdjustmentModal').modal('show');
            });

            // Submit rejection
            $('#rejectAdjustmentForm').on('submit', function(e) {
                e.preventDefault();

                var id = $('#rejectAdjustmentId').val();
                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ url('inventory/reject-adjustment') }}/" + id,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#rejectAdjustmentModal').modal('hide');
                            $('#rejectAdjustmentForm')[0].reset();
                            adjustmentsTable.ajax.reload();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Une erreur est survenue');
                    }
                });
            });

            // Submit raw-material direct adjustment
            $('#rawMaterialAdjustForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                Swal.fire({
                    title: 'Soumettre la demande',
                    text: 'La demande sera envoyée pour approbation avant d\'affecter le stock.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, soumettre',
                    cancelButtonText: 'Annuler'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('inventory.raw-material-adjust-direct') }}",
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                $('#rawMaterialAdjustModal').modal('hide');
                                adjustmentsTable.ajax.reload();
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message ||
                                'Une erreur est survenue');
                        }
                    });
                });
            });

            // ── Inline stock entry (non-admin submits for approval) ───────
            $(document).on('input change', '.inline-stock-input', function() {
                var $input = $(this);
                var productId = $input.data('product-id');
                var familleId = $input.data('famille-id');
                var val = $input.val().trim();
                var newVal = parseFloat(val);
                var key = productId + '_' + familleId;

                if (val !== '' && !isNaN(newVal) && newVal >= 0) {
                    $input.addClass('is-modified');
                    pendingChanges[key] = {
                        product_id: productId,
                        famille_id: familleId,
                        new_quantity: newVal,
                        label: $input.data('label') || ''
                    };
                } else {
                    $input.removeClass('is-modified');
                    delete pendingChanges[key];
                }
                updateValidateBtn();
            });

            // Submit all filled inputs as pending adjustment requests
            $('#validate-all-btn').on('click', function() {
                var items = Object.values(pendingChanges);
                if (items.length === 0) return;

                Swal.fire({
                    title: 'Envoyer ' + items.length + ' demande(s) ?',
                    text: 'Les demandes seront soumises pour approbation par un administrateur.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Envoyer',
                    cancelButtonText: 'Annuler'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $('#validate-all-btn').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin me-1"></i>Envoi...');

                    $.ajax({
                        url: "{{ route('inventory.bulk-request-adjustments') }}",
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            items: items,
                            _token: "{{ csrf_token() }}"
                        }),
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                pendingChanges = {};
                                $('.inline-stock-input').val('').removeClass(
                                    'is-modified');
                                adjustmentsTable.ajax.reload();
                            } else {
                                showToast('error', response.message);
                            }
                            updateValidateBtn();
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message ||
                                'Erreur lors de l\'envoi');
                            updateValidateBtn();
                        }
                    });
                });
            });

            // Clear all inputs
            $('#reset-all-btn').on('click', function() {
                $('.inline-stock-input').val('').removeClass('is-modified');
                pendingChanges = {};
                updateValidateBtn();
            });

            // ── Approve all adjustments ───────────────────────────────────
            $('#approve-all-btn').on('click', function() {
                Swal.fire({
                    title: 'Approuver tous les ajustements ?',
                    text: 'Tous les ajustements en attente seront approuvés et appliqués immédiatement.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Tout approuver',
                    cancelButtonText: 'Annuler'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $('#approve-all-btn').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin me-1"></i>Approbation...');

                    $.ajax({
                        url: "{{ route('inventory.approve-all-adjustments') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                adjustmentsTable.ajax.reload();
                                productsTable.ajax.reload();
                                rawMaterialsTable.ajax.reload();
                            } else {
                                showToast('error', response.message);
                            }
                            $('#approve-all-btn').prop('disabled', false).html(
                                '<i class="fas fa-check-double me-1"></i>Approuver tout'
                            );
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message ||
                                'Erreur lors de l\'approbation');
                            $('#approve-all-btn').prop('disabled', false).html(
                                '<i class="fas fa-check-double me-1"></i>Approuver tout'
                            );
                        }
                    });
                });
            });

            // Toast notification function
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
