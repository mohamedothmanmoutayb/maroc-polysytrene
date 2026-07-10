@extends('layouts.app')

@section('title', 'Mouvements de Stock')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Mouvements de Stock</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Mouvements de Stock
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
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-history me-2"></i>Historique des Mouvements
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse"
                            data-bs-target="#filterSection">
                            <i class="fas fa-filter me-1"></i> Filtres
                        </button>
                    </div>

                    <!-- Filter Section -->
                    <div class="collapse show" id="filterSection">
                        <div class="card-body border-bottom">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <select class="form-select form-select-sm" id="filterFamille">
                                        <option value="">Toutes les familles</option>
                                        @foreach ($familles as $famille)
                                            <option value="{{ $famille->famille_id }}"
                                                {{ request('famille_id') == $famille->famille_id ? 'selected' : '' }}>
                                                {{ $famille->famille_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select form-select-sm" id="filterProduct">
                                        <option value="">Tous les produits</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->product_id }}"
                                                {{ request('product_id') == $product->product_id ? 'selected' : '' }}>
                                                {{ $product->product_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select form-select-sm" id="filterMovementType">
                                        <option value="">Tous les types</option>
                                        @foreach ($movementTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="date" class="form-control form-control-sm" id="filterDateFrom"
                                        placeholder="Date début">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="date" class="form-control form-control-sm" id="filterDateTo"
                                        placeholder="Date fin">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button class="btn btn-primary btn-sm w-100" id="resetFilters">
                                        <i class="fas fa-undo-alt me-1"></i>Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="movements-table" class="table table-hover w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Famille</th>
                                        <th>Produit</th>
                                        <th>Code Produit</th>
                                        <th>Quantité</th>
                                        <th>Ancien Stock</th>
                                        <th>Nouveau Stock</th>
                                        <th>Effectué par</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                }
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }
    </style>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#movements-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('product-stock-movements.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.famille_id = $('#filterFamille').val();
                        d.product_id = $('#filterProduct').val();
                        d.movement_type = $('#filterMovementType').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'movement_date',
                        name: 'movement_date'
                    },
                    {
                        data: 'movement_type_badge',
                        name: 'movement_type',
                        searchable: false
                    },
                    {
                        data: 'famille_name',
                        name: 'famille_name'
                    },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'product_code',
                        name: 'product_code'
                    },
                    {
                        data: 'quantity_formatted',
                        name: 'quantity',
                        searchable: false
                    },
                    {
                        data: 'previous_stock',
                        name: 'previous_stock'
                    },
                    {
                        data: 'new_stock',
                        name: 'new_stock'
                    },
                    {
                        data: 'performed_by',
                        name: 'performed_by'
                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
                responsive: true,
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimer',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                        }
                    }
                ]
            });

            // Apply filters
            $('#filterFamille, #filterProduct, #filterMovementType, #filterDateFrom, #filterDateTo').on('change',
                function() {
                    table.draw();
                });

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#filterFamille').val('');
                $('#filterProduct').val('');
                $('#filterMovementType').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.draw();
            });
        });
    </script>
@endpush
