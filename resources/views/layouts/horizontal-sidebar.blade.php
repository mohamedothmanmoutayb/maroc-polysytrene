<aside class="left-sidebar with-vertical">
    <div>
        <div class="brand-logo d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
                <img src="{{ asset('assets/images/logos/logo.png') }}" alt="maroc-polystyrène" style="height: 90px;" />
            </a>
        </div>

        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <ul class="sidebar-menu" id="sidebarnav">
                <!-- Dashboard -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
                    <span class="hide-menu">Dashboards</span>
                </li>

                @can('view_dashboard')
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('dashboard') }}" aria-expanded="false">
                            <iconify-icon icon="solar:widget-add-line-duotone"></iconify-icon>
                            <span class="hide-menu">Dashboard</span>
                        </a>
                    </li>
                @endcan

                <!-- Matières Premières -->
                @canany(['view_raw_materials', 'view_raw_material_purchases'])
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                            <iconify-icon icon="solar:bomb-broken"></iconify-icon>
                            <span class="hide-menu">Matières Premières</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            @can('view_raw_materials')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('raw-materials.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Matières Premières</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_raw_materials')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('raw-material-categories.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Catégories</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_raw_material_purchases')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('raw-material-purchases.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Achats</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_raw_materials')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('magazines.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Magasins</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <!-- Produits -->
                @canany(['view_products', 'view_families'])
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                            <iconify-icon icon="solar:box-minimalistic-broken"></iconify-icon>
                            <span class="hide-menu">Articles</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            @can('view_products')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('products.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Articles</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_families')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('familles.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Familles</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <!-- Production -->
                @canany(['view_production_orders', 'view_production_output', 'view_production_consumption'])
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                            <iconify-icon icon="healthicons:factory-worker-outline"></iconify-icon>
                            <span class="hide-menu">Production</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            @can('view_production_orders')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('production-orders.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Liste des Ordres</span>
                                    </a>
                                </li>
                            @endcan
                            @can('create_production_orders')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('production-orders.create') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Nouvel Ordre</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_production_output')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('production-output.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Sorties Production</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_production_consumption')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('production-consumption.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Consommation Matières</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <!-- Présences -->
                @canany(['view_attendance', 'manage_attendance'])
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                            <iconify-icon icon="solar:check-square-broken"></iconify-icon>
                            <span class="hide-menu">Présences</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            @can('view_attendance')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('attendance.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Présences du jour</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_attendance_reports')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('attendance.report') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Rapport mensuel</span>
                                    </a>
                                </li>
                            @endcan
                            @can('manage_attendance')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('attendance.settings') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Paramètres</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <li><span class="sidebar-divider lg"></span></li>

                <!-- Parc Auto -->
                @canany(['view_vehicles', 'view_machines', 'view_drivers'])
                    <li class="nav-small-cap">
                        <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
                        <span class="hide-menu">Parc Auto</span>
                    </li>

                    @can('view_vehicles')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="fa7-solid:car"></iconify-icon>
                                <span class="hide-menu">Véhicules</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('vehicles.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Liste des Véhicules</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('vehicle-document-types.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Types de Documents</span>
                                    </a>
                                </li>
                                @can('create_vehicles')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('vehicles.create') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Nouveau Véhicule</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_machines')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="fa7-solid:gears"></iconify-icon>
                                <span class="hide-menu">Machines</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('machines.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Liste des Machines</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('machine-document-types.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Types de Documents</span>
                                    </a>
                                </li>
                                @can('create_machines')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('machines.create') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Nouvelle Machine</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_drivers')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="uis:user-md"></iconify-icon>
                                <span class="hide-menu">Chauffeurs</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('drivers.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Liste des Chauffeurs</span>
                                    </a>
                                </li>
                                @can('create_drivers')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('drivers.create') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Nouveau Chauffeur</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endcanany

                <li><span class="sidebar-divider lg"></span></li>

                <!-- Gestion -->
                @canany(['view_clients', 'view_suppliers', 'view_recharge_parts', 'view_inventory', 'view_purchases'])
                    <li class="nav-small-cap">
                        <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
                        <span class="hide-menu">Gestion</span>
                    </li>

                    @can('view_clients')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="iconoir:user-plus"></iconify-icon>
                                <span class="hide-menu">Clients</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('clients.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Gestion Clients</span>
                                    </a>
                                </li>
                                @can('create_clients')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('clients.create') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Nouveau Client</span>
                                        </a>
                                    </li>
                                @endcan
                                {{-- @can('view_client_situation')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('sales.situation.index') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Situation Clients</span>
                                        </a>
                                    </li>
                                @endcan --}}
                            </ul>
                        </li>
                    @endcan

                    @can('view_suppliers')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="iconoir:user-cart"></iconify-icon>
                                <span class="hide-menu">Fournisseurs</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('suppliers.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Gestion Fournisseurs</span>
                                    </a>
                                </li>
                                @can('create_suppliers')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('suppliers.create') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Nouveau Fournisseur</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_supplier_situation')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('suppliers.situation.index') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Situation Fournisseur</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_recharge_parts')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="vaadin:stock"></iconify-icon>
                                <span class="hide-menu">Pièces de Rechange</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('recharge-parts.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Gestion des Pièces</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @can('view_inventory')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="ic:outline-inventory"></iconify-icon>
                                <span class="hide-menu">Inventaire</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('inventory.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Gestion Inventaire</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @can('view_purchases')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="streamline-ultimate:cash-payment-bills-bold"></iconify-icon>
                                <span class="hide-menu">Règlements</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('purchases.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Liste des Règlements</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                @endcanany

                <li><span class="sidebar-divider lg"></span></li>

                <!-- Ventes -->
                @canany(['view_sales_orders', 'view_sales_quotations', 'view_sales_invoices', 'view_credit_notes',
                    'view_checks', 'view_traites', 'view_expenses'])
                    <li class="nav-small-cap">
                        <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
                        <span class="hide-menu">Ventes</span>
                    </li>

                    @can('view_sales_quotations')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="icon-park-solid:newspaper-folding"></iconify-icon>
                                <span class="hide-menu">Devis</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('sales.quotations.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Liste des Devis</span>
                                    </a>
                                </li>
                                @can('create_sales_quotations')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('sales.quotations.create') }}">
                                            <iconify-icon icon="solar:plus-circle-line-duotone" class="icon-small"></iconify-icon>
                                            <span class="hide-menu">Nouveau Devis</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_sales_orders')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:cart-plus-broken"></iconify-icon>
                                <span class="hide-menu">Ventes</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('sales.orders.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Liste des Ventes</span>
                                    </a>
                                </li>
                                @can('create_sales_orders')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('sales.orders.create') }}">
                                            <iconify-icon icon="solar:plus-circle-line-duotone" class="icon-small"></iconify-icon>
                                            <span class="hide-menu">Nouvelle Vente</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_credit_notes')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:refresh-square-broken"></iconify-icon>
                                <span class="hide-menu">Avoirs</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('credit-notes.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Liste des Avoirs</span>
                                    </a>
                                </li>
                                @can('create_credit_notes')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('credit-notes.create') }}">
                                            <iconify-icon icon="solar:plus-circle-line-duotone" class="icon-small"></iconify-icon>
                                            <span class="hide-menu">Nouvel Avoir</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_sales_invoices')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:dollar-minimalistic-linear"></iconify-icon>
                                <span class="hide-menu">Factures</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('sales.invoices.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Liste des Factures</span>
                                    </a>
                                </li>
                                @can('create_sales_invoices')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('sales.invoices.create') }}">
                                            <iconify-icon icon="solar:plus-circle-line-duotone" class="icon-small"></iconify-icon>
                                            <span class="hide-menu">Nouvelle Facture</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_checks')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:letter-opened-bold-duotone"></iconify-icon>
                                <span class="hide-menu">Chèques</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('checks.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Gestion Chèques</span>
                                    </a>
                                </li>
                                @can('create_checks')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('checks.create') }}">
                                            <iconify-icon icon="solar:plus-circle-line-duotone" class="icon-small"></iconify-icon>
                                            <span class="hide-menu">Nouveau Chèque</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_traites')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="iconamoon:cheque-light"></iconify-icon>
                                <span class="hide-menu">Traites</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('traites.index') }}">
                                        <iconify-icon icon="solar:list-line-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Gestion Traites</span>
                                    </a>
                                </li>
                                @can('create_traites')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('traites.create') }}">
                                            <iconify-icon icon="solar:plus-circle-line-duotone" class="icon-small"></iconify-icon>
                                            <span class="hide-menu">Nouveau Traites</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    @can('view_expenses')
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('expenses.index') }}" aria-expanded="false">
                                <iconify-icon icon="solar:download-square-line-duotone"></iconify-icon>
                                <span class="hide-menu">
                                    Dépenses
                                    @php
                                        $pendingExpenses = \App\Models\Expense::whereNull('approved_by')->count();
                                    @endphp
                                    @if ($pendingExpenses > 0)
                                        <span style="width: fit-content"
                                            class="badge bg-danger-subtle text-danger rounded ms-2">{{ $pendingExpenses }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endcan
                @endcanany

                <li><span class="sidebar-divider lg"></span></li>

                <!-- Administration (Roles & Permissions) -->
                @can('manage_roles')
                    <li class="nav-small-cap">
                        <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
                        <span class="hide-menu">Administration</span>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                            <iconify-icon icon="solar:shield-keyhole-bold-duotone"></iconify-icon>
                            <span class="hide-menu">Sécurité</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            @can('manage_roles')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.roles.index') }}">
                                        <iconify-icon icon="solar:users-group-rounded-bold-duotone"
                                            class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Rôles</span>
                                    </a>
                                </li>
                            @endcan
                            {{-- @can('manage_permissions')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.permissions.index') }}">
                                        <iconify-icon icon="solar:key-bold-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Permissions</span>
                                    </a>
                                </li>
                            @endcan --}}
                            @can('manage_users')
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('admin.users.roles') }}">
                                        <iconify-icon icon="solar:user-check-bold-duotone" class="icon-small"></iconify-icon>
                                        <span class="hide-menu">Attribution des Rôles</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                <li><span class="sidebar-divider lg"></span></li>

                <!-- Ressources Humaines -->
                @canany(['view_employees', 'view_attendance'])
                    <li class="nav-small-cap">
                        <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
                        <span class="hide-menu">Ressources</span>
                    </li>

                    @can('view_employees')
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <iconify-icon icon="solar:user-hands-linear"></iconify-icon>
                                <span class="hide-menu">Employés</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('employees.index') }}">
                                        <span class="icon-small"></span>
                                        <span class="hide-menu">Liste des Employés</span>
                                    </a>
                                </li>
                                @can('create_employees')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('employees.create') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Nouvel Employé</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_attendance')
                                    <li class="sidebar-item">
                                        <a class="sidebar-link" href="{{ route('attendance.index') }}">
                                            <span class="icon-small"></span>
                                            <span class="hide-menu">Présences</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endcanany

            </ul>
        </nav>
    </div>
</aside>
