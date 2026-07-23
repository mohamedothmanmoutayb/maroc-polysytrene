@extends('layouts.app')

@section('title', 'Gestion des Ventes')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Ventes</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Ventes
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards - MODIFIED: Replaced Chiffre d'Affaires Brut with Total m³ -->
        <div class="row mb-4 vente">
            <div class="col-xl-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total m³ vendus</span>
                                <h3 class="mb-0" id="totalVolumeSold">0 m³</h3>
                                <small class="text-muted">Volume total des produits</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cube fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">En Attente</span>
                                <h3 class="mb-0" id="pendingOrders">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Ventes Totales</span>
                                <h3 class="mb-0" id="totalOrders">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-shopping-cart fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card  -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Valeur Totale des Ventes</span>
                                        <h2 class="mb-0" id="totalOrdersValue">0 DH</h2>
                                        <small>
                                            <span class="text-success" id="paidOrdersValue">0 DH</span> payé /
                                            <span class="text-warning" id="unpaidOrdersValue">0 DH</span> impayé
                                        </small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-shopping-cart fs-1 text-info"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Dépenses & Paiements</span>
                                        <h2 class="mb-0 text-danger" id="totalOutgoing">0 DH</h2>
                                        <small>
                                            <span id="expensesAmount">0 DH</span> (Dépenses) +
                                            <span id="supplierPaymentsAmount">0 DH</span> (Fournisseurs)
                                        </small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-arrow-down fs-1 text-danger"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Solde caisse</span>
                                        <h2 class="mb-0 text-success" id="netRevenue">0 DH</h2>
                                        <small class="text-muted">Encaissements (hors virement) des ventes de la période - Dépenses - Fournisseurs</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-coins fs-1 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section - MODIFIED: Default to aujourd'hui -->
                        <div class="row mt-3 g-2">
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Filtre Rapide</label>
                                <select class="form-select form-select-sm" id="quickRevenueFilter">
                                    <option value="today" selected>Aujourd'hui</option>
                                    <option value="yesterday">Hier</option>
                                    <option value="this_week">Cette semaine</option>
                                    <option value="last_week">Semaine dernière</option>
                                    <option value="this_month">Ce mois</option>
                                    <option value="last_month">Mois dernier</option>
                                    <option value="this_year">Cette année</option>
                                    <option value="last_year">Année dernière</option>
                                    <option value="all">Toutes les ventes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Date de début</label>
                                <input type="date" class="form-control form-control-sm" id="revenueDateFrom">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Date de fin</label>
                                <input type="date" class="form-control form-control-sm" id="revenueDateTo">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-sm btn-primary" id="applyRevenueFilter">
                                        <i class="fas fa-filter me-1"></i> Appliquer
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" id="resetRevenueFilter">
                                        <i class="fas fa-undo me-1"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Details Card - MODIFIED: Changed Solde Net to Espèce -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Détail des Flux de Trésorerie
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- ENTRÉES --}}
                            <div class="col-md-4">
                                <div class="card bg-success bg-opacity-10 h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-success">
                                            <i class="fas fa-arrow-up me-1"></i>Entrées (Ventes)
                                        </h6>
                                        <div id="cashInDetails">
                                            <div class="d-flex justify-content-between">
                                                <span>Espèces:</span>
                                                <strong id="cashInCash">0 DH</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span id="cashInCheckLabel">Chèques:</span>
                                                <strong id="cashInCheck">0 DH</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span>Virements:</span>
                                                <strong id="cashInTransfer">0 DH</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span id="cashInTraiteLabel">Traites:</span>
                                                <strong id="cashInTraite">0 DH</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span>Avoir / Avance:</span>
                                                <strong id="cashInOther">0 DH</strong>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>Total Entrées:</strong>
                                                <strong class="text-success" id="totalCashIn">0 DH</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SORTIES --}}
                            <div class="col-md-4">
                                <div class="card bg-danger bg-opacity-10 h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-danger">
                                            <i class="fas fa-arrow-down me-1"></i>Sorties
                                        </h6>
                                        <div id="cashOutDetails">
                                            <small class="text-muted fw-semibold">Dépenses</small>
                                            <div id="expensesBreakdown">
                                                <div class="d-flex justify-content-between text-muted fst-italic">
                                                    <span>—</span><span>0 DH</span>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span class="fw-semibold">Sous-total:</span>
                                                <strong id="totalExpensesOut">0 DH</strong>
                                            </div>
                                            <hr class="my-2">
                                            <small class="text-muted fw-semibold">Fournisseurs</small>
                                            <div id="supplierBreakdown">
                                                <div class="d-flex justify-content-between text-muted fst-italic">
                                                    <span>—</span><span>0 DH</span>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span class="fw-semibold">Sous-total:</span>
                                                <strong id="totalSupplierPaymentsOut">0 DH</strong>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>Total Sorties:</strong>
                                                <strong class="text-danger" id="totalCashOut">0 DH</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CAISSE --}}
                            <div class="col-md-4">
                                <div class="card bg-info bg-opacity-10 h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">
                                            <i class="fas fa-cash-register me-1"></i>Caisse
                                        </h6>
                                        <div id="caisseDetails">
                                            <div class="d-flex justify-content-between">
                                                <span>Espèces entrées:</span>
                                                <strong class="text-success" id="caisseEspecesIn">0 DH</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span id="caisseClientCheckLabel">Chèques clients:</span>
                                                <strong class="text-success" id="caisseClientChecks">0 DH</strong>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Espèces sorties:</span>
                                                <strong class="text-danger" id="caisseEspecesOut">0 DH</strong>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>Solde caisse:</strong>
                                                <strong id="netCashFlow" class="text-success">0 DH</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                            <i class="fas fa-shopping-cart me-2"></i>Liste des Ventes
                        </h5>
                        <div>
                            @can('create_sales_orders')
                                <a href="{{ route('sales.orders.create') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i> Nouvelle Vente
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                           <table id="orders-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Numéro</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th class="text-center">Solde Client</th>
                                        <th>Volume / Qté</th>
                                        <th>Paiement</th>
                                        <th>Montant Final</th>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la vente : <strong id="deleteOrderNumber"></strong> ?</p>
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

    <!-- Delivery Note Options Modal -->
    <div class="modal fade" id="deliveryNoteModal" tabindex="-1" aria-labelledby="deliveryNoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliveryNoteModalLabel">
                        <i class="fas fa-truck me-2"></i>Options du Bon de Livraison
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="delivery_order_id" name="order_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Type d'affichage des prix</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="show_prices" id="showPricesYes"
                                        value="1" checked>
                                    <label class="form-check-label" for="showPricesYes">
                                        <i class="fas fa-eye text-success me-1"></i>Avec prix
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="show_prices" id="showPricesNo"
                                        value="0">
                                    <label class="form-check-label" for="showPricesNo">
                                        <i class="fas fa-eye-slash text-warning me-1"></i>Sans prix
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Type d'affichage</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="display_type"
                                        id="displayTypeUnite" value="unite" checked>
                                    <label class="form-check-label" for="displayTypeUnite">
                                        <i class="fas fa-weight-hanging text-primary me-1"></i>Avec unité (U)
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="display_type"
                                        id="displayTypeVolume" value="volume">
                                    <label class="form-check-label" for="displayTypeVolume">
                                        <i class="fas fa-cube text-success me-1"></i>Avec volume
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New: Price Type Options -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type de prix</label>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" id="priceTypeTTC"
                                        value="ttc" checked>
                                    <label class="form-check-label" for="priceTypeTTC">
                                        TTC
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" id="priceTypeHT"
                                        value="ht">
                                    <label class="form-check-label" for="priceTypeHT">
                                        HT
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" id="priceTypeBoth"
                                        value="both">
                                    <label class="form-check-label" for="priceTypeBoth">
                                        Les deux
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-info" id="printDeliveryNoteBtn">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                    <button type="submit" class="btn btn-primary" form="deliveryNoteForm">
                        <i class="fas fa-file-pdf me-1"></i>Générer le PDF
                    </button>
                </div>
            </div>
            <!-- Hidden form for download -->
            <form id="deliveryNoteForm" method="GET" target="_blank" style="display: none;">
                <input type="hidden" name="order_id" id="form_order_id">
                <input type="hidden" name="show_prices" id="form_show_prices">
                <input type="hidden" name="show_logo" id="form_show_logo">
                <input type="hidden" name="display_type" id="form_display_type">
                <input type="hidden" name="price_type" id="form_price_type">
            </form>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .vente .fas,
        .fa-money-bill-wave {
            font-size: 38px !important;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -6px;
            margin-left: 1px;
        }

        .dropdown-submenu:hover .dropdown-menu {
            display: block;
        }

        .dropdown-submenu .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid transparent;
            border-right: 0;
            border-bottom: 0.3em solid transparent;
            border-left: 0.3em solid;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .dropdown-menu .dropdown-item {
            cursor: pointer;
        }

        .dropdown-menu .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        #revenueFilterInfo {
            font-style: italic;
        }

        #quickRevenueFilter,
        #revenueDateFrom,
        #revenueDateTo {
            cursor: pointer;
        }

        #revenueDateFrom:focus,
        #revenueDateTo:focus,
        #quickRevenueFilter:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }
    </style>
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
    <script>
        let isLoadingStatistics = false;
        let statisticsTimeout = null;

        function openDeliveryNoteModal(orderId, orderNumber) {
            $('#delivery_order_id').val(orderId);
            $('#deliveryNoteModal').modal('show');
        }

        function formatCurrency(amount) {
            if (amount === undefined || amount === null) return '0,00 DH';
            const num = parseFloat(amount);
            if (isNaN(num)) return '0,00 DH';
            return new Intl.NumberFormat('fr-MA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num) + ' DH';
        }

        function formatVolume(volume) {
            if (volume === undefined || volume === null) return '0,0000 m³';
            return new Intl.NumberFormat('fr-MA', {
                minimumFractionDigits: 4,
                maximumFractionDigits: 4
            }).format(volume) + ' m³';
        }

        function getDateRangeFromQuickFilter(filterType) {
            const today = new Date();
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

            let dateFrom = null;
            let dateTo = null;

            switch (filterType) {
                case 'today':
                    dateFrom = today;
                    dateTo = today;
                    break;
                case 'yesterday':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 1);
                    dateTo = new Date(dateFrom);
                    break;
                case 'this_week':
                    const day = today.getDay();
                    const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                    dateFrom = new Date(today);
                    dateFrom.setDate(diff);
                    dateTo = new Date(today);
                    break;
                case 'last_week':
                    const lastWeek = new Date(today);
                    lastWeek.setDate(today.getDate() - 7);
                    const lastWeekMonday = new Date(lastWeek);
                    const lastWeekDay = lastWeek.getDay();
                    const lastWeekDiff = lastWeek.getDate() - lastWeekDay + (lastWeekDay === 0 ? -6 : 1);
                    dateFrom = new Date(lastWeek);
                    dateFrom.setDate(lastWeekDiff);
                    dateTo = new Date(today);
                    dateTo.setDate(today.getDate() - 1);
                    break;
                case 'this_month':
                    dateFrom = startOfMonth;
                    dateTo = new Date(today);
                    break;
                case 'last_month':
                    dateFrom = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    dateTo = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'this_year':
                    dateFrom = new Date(today.getFullYear(), 0, 1);
                    dateTo = new Date(today);
                    break;
                case 'last_year':
                    dateFrom = new Date(today.getFullYear() - 1, 0, 1);
                    dateTo = new Date(today.getFullYear() - 1, 11, 31);
                    break;
                case 'all':
                    dateFrom = null;
                    dateTo = null;
                    break;
                default:
                    dateFrom = startOfMonth;
                    dateTo = new Date(today);
            }

            return {
                date_from: dateFrom ? dateFrom.toISOString().split('T')[0] : null,
                date_to: dateTo ? dateTo.toISOString().split('T')[0] : null
            };
        }

        function loadVolumeStatistics() {
            let dateFrom = $('#revenueDateFrom').val();
            let dateTo = $('#revenueDateTo').val();

            let url = "{{ route('sales.orders.volume-statistics') }}";
            let params = [];

            if (dateFrom) params.push(`date_from=${dateFrom}`);
            if (dateTo) params.push(`date_to=${dateTo}`);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        $('#totalVolumeSold').text(formatVolume(response.data.total_volume));
                    } else {
                        $('#totalVolumeSold').text('0 m³');
                    }
                },
                error: function() {
                    $('#totalVolumeSold').text('0 m³');
                }
            });
        }

        // Helper: payment method label
        function methodLabel(method) {
            const labels = {
                cash: 'Espèces',
                check: 'Chèques',
                transfer: 'Virements',
                traite: 'Traites',
                advance: 'Avance',
                avoir: 'Avoir'
            };
            return labels[method] || method;
        }

        // Replace the buildBreakdown function with this updated version
        function buildBreakdown(map, showCount = false) {
            // Define all possible payment methods in order
            const allMethods = [{
                    key: 'cash',
                    label: 'Espèces'
                },
                {
                    key: 'check',
                    label: 'Chèques'
                },
                {
                    key: 'transfer',
                    label: 'Virements'
                },
                {
                    key: 'traite',
                    label: 'Traites'
                }
            ];

            let html = '';

            for (const method of allMethods) {
                const data = (map && map[method.key]) || {
                    total: 0,
                    count: 0
                };
                const count = data.count || 0;
                const total = data.total || 0;

                // Build label with count if showCount is true and method is check or traite
                let label = method.label;
                if (showCount && (method.key === 'check' || method.key === 'traite') && count > 0) {
                    label = `${method.label} (${count})`;
                }

                html += `<div class="d-flex justify-content-between mt-1">
                    <span>${label}:</span>
                    <strong>${formatCurrency(total)}</strong>
                 </div>`;
            }

            return html;
        }

        function loadCashFlowDetails(dateFrom, dateTo) {
            let url = "{{ route('sales.orders.cash-flow') }}";
            let params = [];

            if (dateFrom) params.push(`date_from=${dateFrom}`);
            if (dateTo) params.push(`date_to=${dateTo}`);
            if (params.length > 0) url += '?' + params.join('&');

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (!response.success) return;
                    const d = response.data;

                    // ── Entrées ──────────────────────────────────────────
                    const si = d.sales_income || {};
                    const cash = si.cash || {
                        total: 0,
                        count: 0
                    };
                    const check = si.check || {
                        total: 0,
                        count: 0
                    };
                    const xfer = si.transfer || {
                        total: 0,
                        count: 0
                    };
                    const traite = si.traite || {
                        total: 0,
                        count: 0
                    };

                    // Entrées display
                    $('#cashInCash').text(formatCurrency(cash.total));

                    const checkLabel = 'Chèques' + (check.count > 0 ? ` (${check.count})` : '');
                    $('#cashInCheckLabel').text(checkLabel + ':');
                    $('#cashInCheck').text(formatCurrency(check.total));

                    const traiteLabel = 'Traites' + (traite.count > 0 ? ` (${traite.count})` : '');
                    $('#cashInTraiteLabel').text(traiteLabel + ':');
                    $('#cashInTraite').text(formatCurrency(traite.total));

                    $('#cashInTransfer').text(formatCurrency(xfer.total));

                    const other = Object.entries(si)
                        .filter(([k]) => !['cash', 'check', 'transfer', 'traite'].includes(k))
                        .reduce((s, [, v]) => s + (v.total || 0), 0);
                    $('#cashInOther').text(formatCurrency(other));
                    $('#totalCashIn').text(formatCurrency(d.sales_income_total || 0));

                    // ── Sorties ──────────────────────────────────────────
                    $('#expensesBreakdown').html(buildBreakdown(d.expenses, true));
                    $('#totalExpensesOut').text(formatCurrency(d.expenses_total || 0));

                    $('#supplierBreakdown').html(buildBreakdown(d.supplier_payments, true));
                    $('#totalSupplierPaymentsOut').text(formatCurrency(d.supplier_payments_total || 0));

                    $('#totalCashOut').text(formatCurrency(d.total_out || 0));

                    // ── Caisse - Show balance for EACH payment method separately ─────────────────
                    const expensesByMethod = d.expenses || {};
                    const supplierByMethod = d.supplier_payments || {};

                    // Calculate incoming for each method (from sales)
                    const incoming = {
                        especes: cash.total,
                        cheques: check.total,
                        virements: xfer.total,
                        traites: traite.total
                    };

                    // Calculate outgoing for each method (expenses + supplier payments)
                    // Supplier payments by cheque are NOT subtracted from Solde Chèques
                    // because the caisse tracks cheque receivables on-hand. Issuing a
                    // cheque to a supplier does not reduce held receivables — it is a
                    // bank instrument that draws from the company's bank account.
                    const outgoing = {
                        especes: (expensesByMethod['cash']?.total || 0) + (supplierByMethod['cash']
                            ?.total || 0),
                        cheques: (expensesByMethod['check']?.total || 0),
                        virements: (expensesByMethod['transfer']?.total || 0) + (supplierByMethod[
                            'transfer']?.total || 0),
                        traites: (expensesByMethod['traite']?.total || 0) + (supplierByMethod['traite']
                            ?.total || 0)
                    };

                    // Calculate balance for each method
                    const balances = {
                        especes: incoming.especes - outgoing.especes,
                        cheques: incoming.cheques - outgoing.cheques,
                        virements: incoming.virements - outgoing.virements,
                        traites: incoming.traites - outgoing.traites
                    };

                    // Calculate global balance (virements excluded — not physical cash)
                    const globalBalance = balances.especes + balances.cheques + balances.traites;

                    // Build Caisse HTML with balance for each method
                    let caisseHtml = `
                        <div class="mb-3">
                            <div class="fw-bold mb-2">SOLDE PAR MÉTHODE</div>
                            <div class="d-flex justify-content-between">
                                <span>Solde Espèces:</span>
                                <strong class="${balances.especes > 0 ? 'text-success' : (balances.especes < 0 ? 'text-danger' : 'text-secondary')}">
                                    ${formatCurrency(balances.especes)}
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span>Solde Chèques:</span>
                                <strong class="${balances.cheques > 0 ? 'text-success' : (balances.cheques < 0 ? 'text-danger' : 'text-secondary')}">
                                    ${formatCurrency(balances.cheques)}
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span>Solde Traites:</span>
                                <strong class="${balances.traites > 0 ? 'text-success' : (balances.traites < 0 ? 'text-danger' : 'text-secondary')}">
                                    ${formatCurrency(balances.traites)}
                                </strong>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <strong>SOLDE CAISSE GLOBAL:</strong>
                            <strong class="${globalBalance > 0 ? 'text-success' : (globalBalance < 0 ? 'text-danger' : 'text-secondary')}">
                                ${formatCurrency(globalBalance)}
                            </strong>
                        </div>
                    `;

                    $('#caisseDetails').html(caisseHtml);
                },
                error: function(xhr) {
                    console.error('Error loading cash flow:', xhr);
                }
            });
        }

        function loadStatistics() {
            if (isLoadingStatistics) {
                return;
            }

            isLoadingStatistics = true;

            let dateFrom = $('#revenueDateFrom').val();
            let dateTo = $('#revenueDateTo').val();

            let url = "{{ route('sales.orders.revenue') }}";
            let params = [];

            if (dateFrom) params.push(`date_from=${dateFrom}`);
            if (dateTo) params.push(`date_to=${dateTo}`);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            $('#netRevenue').html('<div class="spinner-border spinner-border-sm text-success" role="status"></div>');

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        $('#expensesAmount').text(formatCurrency(response.data.expenses));
                        $('#supplierPaymentsAmount').text(formatCurrency(response.data.supplier_payments));
                        $('#totalOutgoing').text(formatCurrency(parseFloat(response.data.expenses || 0) + parseFloat(response.data.supplier_payments || 0)));
                        $('#netRevenue').text(formatCurrency(response.data.net_revenue));

                        loadCashFlowDetails(dateFrom, dateTo);
                    } else {
                        $('#netRevenue').text('0 DH');
                        showToast('error', response.message || 'Erreur lors du chargement');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading revenue:', xhr);
                    $('#netRevenue').text('0 DH');
                    showToast('error', 'Erreur lors du chargement du solde caisse');
                },
                complete: function() {
                    isLoadingStatistics = false;
                }
            });
        }

        function loadOrderStatistics() {
            let dateFrom = $('#revenueDateFrom').val();
            let dateTo = $('#revenueDateTo').val();

            let url = "{{ route('sales.orders.statistics') }}";
            let params = [];

            if (dateFrom) params.push(`date_from=${dateFrom}`);
            if (dateTo) params.push(`date_to=${dateTo}`);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        $('#totalOrders').text(response.data.total || 0);
                        $('#pendingOrders').text(response.data.pending || 0);
                        $('#todayOrders').text(response.data.today || 0);
                    }
                },
                error: function() {
                    $('#totalOrders').text('0');
                    $('#pendingOrders').text('0');
                    $('#todayOrders').text('0');
                }
            });
        }

        function loadTotalOrdersValue() {
            let dateFrom = $('#revenueDateFrom').val();
            let dateTo = $('#revenueDateTo').val();

            let url = "{{ route('sales.orders.total-value') }}";
            let params = [];

            if (dateFrom && dateFrom !== '') {
                params.push(`date_from=${dateFrom}`);
            }
            if (dateTo && dateTo !== '') {
                params.push(`date_to=${dateTo}`);
            }

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        $('#totalOrdersValue').text(formatCurrency(response.data.total_orders_value));
                        $('#paidOrdersValue').text(formatCurrency(response.data.paid_value));
                        $('#unpaidOrdersValue').text(formatCurrency(response.data.unpaid_value));
                    } else {
                        console.error('Error in response:', response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading total orders value:', xhr);
                    $('#totalOrdersValue').text('0 DH');
                    $('#paidOrdersValue').text('0 DH');
                    $('#unpaidOrdersValue').text('0 DH');
                }
            });
        }

        function updateRevenueStatistics() {
            let dateFrom = $('#revenueDateFrom').val();
            let dateTo = $('#revenueDateTo').val();

            const quickFilter = $('#quickRevenueFilter').val();
            if ((!dateFrom || !dateTo) && quickFilter !== 'all') {
                const range = getDateRangeFromQuickFilter(quickFilter);
                dateFrom = range.date_from;
                dateTo = range.date_to;

                if (dateFrom) $('#revenueDateFrom').val(dateFrom);
                if (dateTo) $('#revenueDateTo').val(dateTo);
            }

            if (statisticsTimeout) {
                clearTimeout(statisticsTimeout);
            }

            statisticsTimeout = setTimeout(function() {
                loadStatistics();
                loadTotalOrdersValue();
                loadOrderStatistics();
                loadVolumeStatistics();
            }, 100);
        }

        $(document).ready(function() {
            // Set default date range to today
            const _todayStr = new Date().toISOString().split('T')[0];
            $('#revenueDateFrom').val(_todayStr);
            $('#revenueDateTo').val(_todayStr);

            // Function to get current date filters
            function getDateFilters() {
                return {
                    date_from: $('#revenueDateFrom').val(),
                    date_to: $('#revenueDateTo').val()
                };
            }

            var table = $('#orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sales.orders.index') }}",
                    type: 'GET',
                    data: function(d) {
                        // Add date filters to the AJAX request
                        var filters = getDateFilters();
                        d.date_from = filters.date_from;
                        d.date_to = filters.date_to;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable error:', error);
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'order_number',
                        name: 'order_number'
                    },
                    {
                        data: 'order_date_formatted',
                        name: 'order_date',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'client_name',
                        name: 'client_name',
                        searchable: true
                    },
                    {
                        data: 'client_balance',
                        name: 'client_balance',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'total_volume',
                        name: 'total_volume',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'payment_status_badge',
                        name: 'payment_status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'final_amount',
                        name: 'final_amount',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[2, 'desc']],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json",
                    search: "Rechercher:",
                    searchPlaceholder: "Numéro, client, montant..."
                },
                responsive: true,
                paging: false,
                dom: '<"row"<"col-md-12"f>>rt',
                drawCallback: function() {}
            });

            // Load initial statistics
            loadStatistics();
            loadOrderStatistics();
            loadTotalOrdersValue();
            loadVolumeStatistics();

            // Handle delete button click
            $(document).on('click', '.dropdown-item.delete', function() {
                var orderId = $(this).data('id');
                var orderNumber = $(this).data('number');

                $('#deleteOrderNumber').text(orderNumber);
                $('#deleteForm').attr('action', "{{ url('sales/orders') }}/" + orderId);
                $('#deleteModal').modal('show');
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
                            table.ajax.reload(null, false);
                            loadStatistics();
                            loadOrderStatistics();
                            loadVolumeStatistics();
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

            // Handle Download button
            $('#deliveryNoteForm').submit(function(e) {
                e.preventDefault();

                var orderId = $('#delivery_order_id').val();
                var showPrices = $('input[name="show_prices"]:checked').val();
                var showLogo = $('input[name="show_logo"]:checked').val();
                var displayType = $('input[name="display_type"]:checked').val();
                var priceType = $('input[name="price_type"]:checked').val();

                var baseUrl = "{{ url('sales/orders/delivery-note') }}";
                var url = baseUrl + "/" + orderId +
                    "?show_prices=" + showPrices +
                    "&show_logo=" + showLogo +
                    "&display_type=" + displayType +
                    "&price_type=" + priceType;

                var printWindow = window.open(url, '_blank');

                if (printWindow) {
                    printWindow.focus();
                }

                $('#deliveryNoteModal').modal('hide');
            });

            // Handle Print button
            $(document).on('click', '#printDeliveryNoteBtn', function() {
                var orderId = $('#delivery_order_id').val();
                var showPrices = $('input[name="show_prices"]:checked').val();
                var showLogo = $('input[name="show_logo"]:checked').val();
                var displayType = $('input[name="display_type"]:checked').val();
                var priceType = $('input[name="price_type"]:checked').val();

                var baseUrl = "{{ route('sales.orders.delivery-note.view', ['id' => '__ID__']) }}";
                var url = baseUrl.replace('__ID__', orderId) +
                    "?show_prices=" + showPrices +
                    "&show_logo=" + showLogo +
                    "&display_type=" + displayType +
                    "&price_type=" + priceType;

                var printWindow = window.open(url, '_blank', 'width=800,height=600');

                if (printWindow) {
                    printWindow.focus();

                    printWindow.onload = function() {
                        setTimeout(function() {
                            printWindow.print();
                            printWindow.onafterprint = function() {
                                printWindow.close();
                            };
                        }, 1000);
                    };
                }

                $('#deliveryNoteModal').modal('hide');
            });

            $('input[name="display_type"]').change(function() {
                if ($(this).val() === 'unite') {
                    $('#displayTypeHelp').text(
                        'Unité: Affiche l\'unité de mesure standard (PIECE, KG, etc.)');
                } else {
                    $('#displayTypeHelp').text(
                        'Volume: Affiche le volume total (quantité × volume unitaire)');
                }
            });

            // Update both statistics AND DataTable when filters change
            $('#quickRevenueFilter').change(function() {
                const range = getDateRangeFromQuickFilter($(this).val());
                $('#revenueDateFrom').val(range.date_from || '');
                $('#revenueDateTo').val(range.date_to || '');
                updateRevenueStatistics();
                table.ajax.reload(); // Reload DataTable with new filters
            });

            $('#applyRevenueFilter').click(function() {
                updateRevenueStatistics();
                table.ajax.reload(); // Reload DataTable with new filters
            });

            $('#resetRevenueFilter').click(function() {
                $('#quickRevenueFilter').val('today');
                const _r = new Date().toISOString().split('T')[0];
                $('#revenueDateFrom').val(_r);
                $('#revenueDateTo').val(_r);
                updateRevenueStatistics();
                table.ajax.reload(); // Reload DataTable with new filters
            });

            let revenueTimeout;
            $('#revenueDateFrom, #revenueDateTo').on('input', function() {
                clearTimeout(revenueTimeout);
                revenueTimeout = setTimeout(() => {
                    if (!$('#revenueDateFrom').val() && !$('#revenueDateTo').val()) {
                        $('#quickRevenueFilter').val('today');
                        const _e = new Date().toISOString().split('T')[0];
                        $('#revenueDateFrom').val(_e);
                        $('#revenueDateTo').val(_e);
                    } else {
                        $('#quickRevenueFilter').val('custom');
                    }
                    updateRevenueStatistics();
                    table.ajax.reload(); // Reload DataTable with new filters
                }, 500);
            });

            // Auto-refresh statistics every 60 seconds
            setInterval(function() {
                if (!isLoadingStatistics) {
                    loadStatistics();
                    loadOrderStatistics();
                    loadVolumeStatistics();
                    table.ajax.reload(null, false);
                }
            }, 60000);

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
        });
    </script>
@endpush
