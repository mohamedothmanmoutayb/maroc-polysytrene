<header class="topbar">
    <div class="with-vertical">
        <!-- Start Vertical Layout Header -->
        <nav class="navbar navbar-expand-lg p-0">
            <ul class="navbar-nav">
                <li class="nav-item nav-icon-hover-bg rounded-circle d-flex">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-6"></iconify-icon>
                    </a>
                </li>

                <!-- NEW ACTION BUTTONS - Vertical Header (Desktop) -->
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('sales.orders.index') }}"
                        class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:cart-bold-duotone" class="fs-6"></iconify-icon>
                        <span>Liste de Vente</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('production-orders.create') }}"
                        class="btn btn-info btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="material-symbols-light:factory-outline" class="fs-6"></iconify-icon>
                        <span>Ordre de Production</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('expenses.index') }}"
                        class="btn btn-warning btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:download-square-line-duotone" class="fs-6"></iconify-icon>
                        <span>Dépense</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('sales.orders.create') }}"
                        class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:cart-check-bold-duotone" class="fs-6"></iconify-icon>
                        <span>Nouvelle Vente</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('attendance.index') }}"
                        class="btn btn-success btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:check-read-outline" class="fs-6"></iconify-icon>
                        <span>Présence du Jour</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('raw-material-purchases.index') }}"
                        class="btn btn-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:bag-bold-duotone" class="fs-6"></iconify-icon>
                        <span>Les Achats</span>
                    </a>
                </li>

                <li class="nav-item d-none d-lg-flex dropdown nav-icon-hover-bg rounded-circle">
                    <!-- Quick Actions Dropdown -->
                    <div class="hover-dd">
                        <a class="nav-link" id="drop2" href="javascript:void(0)" aria-haspopup="true"
                            aria-expanded="false">
                            <iconify-icon icon="solar:widget-3-line-duotone" class="fs-6"></iconify-icon>
                        </a>
                        <div class="dropdown-menu dropdown-menu-nav dropdown-menu-animate-up py-0 overflow-hidden"
                            aria-labelledby="drop2">
                            <div class="position-relative">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="p-4 pb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="position-relative">
                                                        <a href="{{ route('sales.orders.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-primary-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:cart-plus-bold-duotone"
                                                                    class="fs-7 text-primary"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouvelle Vente</h6>
                                                                <span class="fs-11 d-block text-body-color">Créer une
                                                                    vente client</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('checks.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-warning-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:wallet-money-broken"
                                                                    class="fs-7 text-warning"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouveau Chèque</h6>
                                                                <span class="fs-11 d-block text-body-color">Enregistrer
                                                                    un chèque</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('attendance.index') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-success-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:check-read-outline"
                                                                    class="fs-7 text-success"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Présences</h6>
                                                                <span class="fs-11 d-block text-body-color">Gérer les
                                                                    présences</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="position-relative">
                                                        <a href="{{ route('production-orders.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-info-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon
                                                                    icon="material-symbols-light:factory-outline"
                                                                    class="fs-7 text-info"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouvel Ordre Production</h6>
                                                                <span class="fs-11 d-block text-body-color">Lancer une
                                                                    production</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('production-output.batch-create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-danger-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:box-bold-duotone"
                                                                    class="fs-7 text-danger"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Sortie Production</h6>
                                                                <span class="fs-11 d-block text-body-color">Enregistrer
                                                                    production</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('clients.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-secondary-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:user-plus-bold-duotone"
                                                                    class="fs-7 text-secondary"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouveau Client</h6>
                                                                <span class="fs-11 d-block text-body-color">Ajouter un
                                                                    client</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3 pt-2 border-top">
                                                <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                                    <i class="fas fa-rocket me-1"></i> Accès rapide aux actions
                                                    principales
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            <div class="d-block d-lg-none py-9 py-xl-0">
                <img src="{{ asset('assets/images/logos/logo-poly.png') }}" alt="maroc-polystyrène"
                    style="height: 35px;" />
            </div>

            <a class="navbar-toggler p-0 border-0 nav-icon-hover-bg rounded-circle" href="javascript:void(0)"
                data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
                <iconify-icon icon="solar:menu-dots-bold-duotone" class="fs-6"></iconify-icon>
            </a>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex align-items-center justify-content-between">
                    <ul class="navbar-nav flex-row mx-auto ms-lg-auto align-items-center justify-content-center">
                        <li class="nav-item">
                            <a class="nav-link moon dark-layout nav-icon-hover-bg rounded-circle"
                                href="javascript:void(0)">
                                <iconify-icon icon="solar:moon-line-duotone" class="moon fs-6"></iconify-icon>
                            </a>
                            <a class="nav-link sun light-layout nav-icon-hover-bg rounded-circle"
                                href="javascript:void(0)" style="display: none">
                                <iconify-icon icon="solar:sun-2-line-duotone" class="sun fs-6"></iconify-icon>
                            </a>
                        </li>
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link nav-icon-hover-bg rounded-circle" href="javascript:void(0)"
                                data-bs-toggle="modal" data-bs-target="#exampleModal">
                                <iconify-icon icon="solar:magnifer-line-duotone" class="fs-6"></iconify-icon>
                            </a>
                        </li>

                        <!-- Notifications Dropdown (admin only) -->
                        @if (Auth::check() && Auth::user()->isAdmin())
                            <li class="nav-item dropdown nav-icon-hover-bg rounded-circle">
                                <a class="nav-link position-relative" href="javascript:void(0)"
                                    id="notificationsDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <iconify-icon icon="solar:bell-bing-line-duotone" class="fs-6"></iconify-icon>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        id="notification-badge" style="font-size: 10px; display:none;">0</span>
                                </a>
                                <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up"
                                    aria-labelledby="notificationsDrop" style="width: 400px;">
                                    <div class="d-flex align-items-center justify-content-between py-3 px-7">
                                        <h5 class="mb-0 fs-5 fw-semibold">Notifications</h5>
                                        <span class="badge text-bg-primary rounded-4 px-3 py-1 lh-sm"
                                            id="notification-count">0</span>
                                    </div>
                                    <div class="message-body" data-simplebar
                                        style="max-height: 420px; overflow-y: auto;" id="notifications-list">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Chargement...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-6 px-7 mb-1">
                                        <button class="btn btn-primary w-100" id="mark-all-read-btn"
                                            onclick="markAllAsRead()">Marquer tout comme lu</button>
                                    </div>
                                </div>
                            </li>
                        @endif

                        <!-- Profile Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="javascript:void(0)" id="profileDrop" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <div class="d-flex align-items-center gap-2 lh-base">
                                    <img src="{{ Auth::user()->employee && Auth::user()->employee->photo ? asset('storage/' . Auth::user()->employee->photo) : asset('assets/images/profile/user-1.jpg') }}"
                                        class="rounded-circle" width="35" height="35"
                                        alt="{{ Auth::user()->username }}" />
                                    <iconify-icon icon="solar:alt-arrow-down-bold" class="fs-2"></iconify-icon>
                                </div>
                            </a>
                            <div class="dropdown-menu profile-dropdown dropdown-menu-end dropdown-menu-animate-up"
                                aria-labelledby="profileDrop">
                                <div class="position-relative px-4 pt-3 pb-2">
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom gap-6">
                                        <img src="{{ Auth::user()->employee && Auth::user()->employee->photo ? asset('storage/' . Auth::user()->employee->photo) : asset('assets/images/profile/user-1.jpg') }}"
                                            class="rounded-circle" width="56" height="56"
                                            alt="{{ Auth::user()->username }}" />
                                        <div>
                                            <h5 class="mb-0 fs-12">
                                                {{ Auth::user()->employee->full_name ?? Auth::user()->username }}
                                                <span
                                                    class="badge bg-{{ Auth::user()->role == 'admin' ? 'danger' : (Auth::user()->role == 'manager' ? 'warning' : 'info') }} fs-11">
                                                    {{ ucfirst(Auth::user()->role) }}
                                                </span>
                                            </h5>
                                            <p class="mb-0 text-dark">{{ Auth::user()->email }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-circle me-1"
                                                    style="color: {{ Auth::user()->is_active ? '#28a745' : '#dc3545' }}; font-size: 8px;"></i>
                                                {{ Auth::user()->is_active ? 'Actif' : 'Inactif' }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="message-body">
                                        @if (Auth::user()->isAdmin())
                                            <a href="{{ route('profile.edit') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center">
                                                <i class="fas fa-user-circle me-2 fs-5"></i> Mon Profil
                                            </a>
                                            <a href="{{ route('profile.password') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center">
                                                <i class="fas fa-lock me-2 fs-5"></i> Mot de passe
                                            </a>
                                            <div class="dropdown-divider"></div>
                                        @endif

                                        @if (Auth::user()->isAdmin())
                                            <a href="{{ route('expenses.index') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center justify-content-between">
                                                <span><i class="fas fa-coins me-2 fs-5"></i> Dépenses</span>
                                                @php
                                                    $pendingExpenses = \App\Models\Expense::whereNull(
                                                        'approved_by',
                                                    )->count();
                                                @endphp
                                                @if ($pendingExpenses > 0)
                                                    <span
                                                        class="badge bg-danger-subtle text-danger rounded">{{ $pendingExpenses }}</span>
                                                @endif
                                            </a>
                                            <a href="{{ route('employees.index') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center">
                                                <i class="fas fa-users me-2 fs-5"></i> Ressources
                                            </a>
                                            <div class="dropdown-divider"></div>
                                        @endif

                                        <form action="{{ route('logout') }}" method="POST"
                                            class="dropdown-item p-0">
                                            @csrf
                                            <button type="submit"
                                                class="p-2 h6 rounded-1 w-100 text-start border-0 bg-transparent d-flex align-items-center">
                                                <i class="fas fa-sign-out-alt me-2 fs-5"></i> Déconnexion
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Mobile Navigation Content -->
        <div class="offcanvas offcanvas-start pt-0" data-bs-scroll="true" tabindex="-1" id="mobilenavbar"
            aria-labelledby="offcanvasWithBothOptionsLabel">
            <nav class="sidebar-nav scroll-sidebar">
                <div class="offcanvas-header justify-content-between">
                    <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
                        <img src="{{ asset('assets/images/logos/logo-poly.png') }}" alt="Logo"
                            style="height: 35px;" />
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body pt-0" data-simplebar style="height: calc(100vh - 80px)">
                    <!-- Mobile Quick Action Buttons -->
                    <div class="mb-3">
                        <div class="d-flex flex-wrap gap-2 justify-content-between">
                            <a href="{{ route('sales.orders.index') }}"
                                class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                                <iconify-icon icon="solar:cart-bold-duotone" class="fs-5"></iconify-icon>
                                <span>Liste Vente</span>
                            </a>
                            <a href="{{ route('production-orders.create') }}"
                                class="btn btn-info d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                                <iconify-icon icon="solar:factory-bold-duotone" class="fs-5"></iconify-icon>
                                <span>Ordre Prod.</span>
                            </a>
                            <a href="{{ route('expenses.index') }}"
                                class="btn btn-warning d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                                <iconify-icon icon="solar:download-square-line-duotone" class="fs-5"></iconify-icon>
                                <span>Dépense</span>
                            </a>
                            <a href="{{ route('sales.orders.create') }}"
                                class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                                <iconify-icon icon="solar:cart-check-bold-duotone" class="fs-5"></iconify-icon>
                                <span>Nv. Vente</span>
                            </a>
                            <a href="{{ route('attendance.index') }}"
                                class="btn btn-success d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                                <iconify-icon icon="solar:check-read-outline" class="fs-5"></iconify-icon>
                                <span>Présence</span>
                            </a>
                            <a href="{{ route('raw-material-purchases.index') }}"
                                class="btn btn-secondary d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                                <iconify-icon icon="solar:bag-bold-duotone" class="fs-5"></iconify-icon>
                                <span>Achats</span>
                            </a>
                        </div>
                    </div>
                    <ul id="sidebarnav">
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow ms-0" href="javascript:void(0)" aria-expanded="false">
                                <span>
                                    <iconify-icon icon="solar:slider-vertical-line-duotone"
                                        class="fs-7"></iconify-icon>
                                </span>
                                <span class="hide-menu">Actions rapides</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level my-3 ps-3">
                                <li class="sidebar-item py-2">
                                    <a href="{{ route('sales.orders.create') }}" class="d-flex align-items-center">
                                        <div
                                            class="bg-primary-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                            <iconify-icon icon="solar:cart-plus-bold-duotone"
                                                class="fs-7 text-primary"></iconify-icon>
                                        </div>
                                        <div class="d-inline-block">
                                            <h6 class="mb-0 bg-hover-primary">Nouvelle Commande</h6>
                                            <span class="fs-11 d-block text-body-color">Créer une commande
                                                client</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="sidebar-item py-2">
                                    <a href="{{ route('checks.create') }}" class="d-flex align-items-center">
                                        <div
                                            class="bg-warning-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                            <iconify-icon icon="solar:money-check-bold-duotone"
                                                class="fs-7 text-warning"></iconify-icon>
                                        </div>
                                        <div class="d-inline-block">
                                            <h6 class="mb-0 bg-hover-primary">Nouveau Chèque</h6>
                                            <span class="fs-11 d-block text-body-color">Enregistrer un chèque</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="sidebar-item py-2">
                                    <a href="{{ route('production-orders.create') }}"
                                        class="d-flex align-items-center">
                                        <div
                                            class="bg-info-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                            <iconify-icon icon="solar:factory-bold-duotone"
                                                class="fs-7 text-info"></iconify-icon>
                                        </div>
                                        <div class="d-inline-block">
                                            <h6 class="mb-0 bg-hover-primary">Nouvel Ordre Production</h6>
                                            <span class="fs-11 d-block text-body-color">Lancer une production</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="sidebar-item py-2">
                                    <a href="{{ route('clients.create') }}" class="d-flex align-items-center">
                                        <div
                                            class="bg-secondary-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                            <iconify-icon icon="solar:user-plus-bold-duotone"
                                                class="fs-7 text-secondary"></iconify-icon>
                                        </div>
                                        <div class="d-inline-block">
                                            <h6 class="mb-0 bg-hover-primary">Nouveau Client</h6>
                                            <span class="fs-11 d-block text-body-color">Ajouter un client</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="sidebar-item py-2">
                                    <a href="{{ route('attendance.index') }}" class="d-flex align-items-center">
                                        <div
                                            class="bg-success-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                            <iconify-icon icon="solar:calendar-check-bold-duotone"
                                                class="fs-7 text-success"></iconify-icon>
                                        </div>
                                        <div class="d-inline-block">
                                            <h6 class="mb-0 bg-hover-primary">Présences</h6>
                                            <span class="fs-11 d-block text-body-color">Gérer les présences</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <!-- Horizontal Header (Desktop) -->
    <div class="app-header with-horizontal">
        <nav class="navbar navbar-expand-xl container-fluid p-0">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item d-flex d-xl-none">
                    <a class="nav-link sidebartoggler nav-icon-hover-bg rounded-circle" id="sidebarCollapse"
                        href="javascript:void(0)">
                        <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-7"></iconify-icon>
                    </a>
                </li>
                <li class="nav-item d-none d-xl-flex align-items-center">
                    <a href="{{ route('dashboard') }}" class="text-nowrap nav-link">
                        <img style="width:112px" src="{{ asset('assets/images/logos/logo-poly.png') }}"
                            alt="maroc-polystyrène" />
                    </a>
                </li>

                <li class="nav-item d-none d-xl-flex align-items-center nav-icon-hover-bg rounded-circle">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <iconify-icon icon="solar:magnifer-linear" class="fs-6"></iconify-icon>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center dropdown nav-icon-hover-bg rounded-circle">
                    <div class="hover-dd">
                        <a class="nav-link" id="drop2" href="javascript:void(0)" aria-haspopup="true"
                            aria-expanded="false">
                            <iconify-icon icon="solar:widget-3-line-duotone" class="fs-6"></iconify-icon>
                        </a>
                        <div class="dropdown-menu dropdown-menu-nav dropdown-menu-animate-up py-0 overflow-hidden"
                            aria-labelledby="drop2">
                            <div class="position-relative">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="p-4 pb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="position-relative">
                                                        <a href="{{ route('sales.orders.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-primary-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:cart-plus-bold-duotone"
                                                                    class="fs-7 text-primary"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouvelle Commande</h6>
                                                                <span class="fs-11 d-block text-body-color">Créer une
                                                                    commande client</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('checks.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-warning-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:money-check-bold-duotone"
                                                                    class="fs-7 text-warning"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouveau Chèque</h6>
                                                                <span class="fs-11 d-block text-body-color">Enregistrer
                                                                    un chèque</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('attendance.index') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-success-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:calendar-check-bold-duotone"
                                                                    class="fs-7 text-success"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Présences</h6>
                                                                <span class="fs-11 d-block text-body-color">Gérer les
                                                                    présences</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="position-relative">
                                                        <a href="{{ route('production-orders.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-info-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:factory-bold-duotone"
                                                                    class="fs-7 text-info"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouvel Ordre Production</h6>
                                                                <span class="fs-11 d-block text-body-color">Lancer une
                                                                    production</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('production-output.batch-create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-danger-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:box-bold-duotone"
                                                                    class="fs-7 text-danger"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Sortie Production</h6>
                                                                <span class="fs-11 d-block text-body-color">Enregistrer
                                                                    production</span>
                                                            </div>
                                                        </a>
                                                        <a href="{{ route('clients.create') }}"
                                                            class="d-flex align-items-center pb-9 position-relative">
                                                            <div
                                                                class="bg-secondary-subtle rounded round-48 me-3 d-flex align-items-center justify-content-center">
                                                                <iconify-icon icon="solar:user-plus-bold-duotone"
                                                                    class="fs-7 text-secondary"></iconify-icon>
                                                            </div>
                                                            <div class="d-inline-block">
                                                                <h6 class="mb-0">Nouveau Client</h6>
                                                                <span class="fs-11 d-block text-body-color">Ajouter un
                                                                    client</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <img src="{{ asset('assets/images/backgrounds/mega-dd-bg.jpg') }}"
                                            alt="mega-dd" class="img-fluid mega-dd-bg" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Horizontal Header -->
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('sales.orders.index') }}"
                        class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:cart-bold-duotone" class="fs-6"></iconify-icon>
                        <span>Liste de Vente</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('production-orders.create') }}"
                        class="btn btn-info btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="material-symbols-light:factory-outline" class="fs-6"></iconify-icon>
                        <span>Ordre de Production</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('expenses.index') }}"
                        class="btn btn-warning btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:download-square-line-duotone" class="fs-6"></iconify-icon>
                        <span>Dépense</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('sales.orders.create') }}"
                        class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:cart-check-bold-duotone" class="fs-6"></iconify-icon>
                        <span>Nouvelle Vente</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('attendance.index') }}"
                        class="btn btn-success btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:check-read-outline" class="fs-6"></iconify-icon>
                        <span>Présence du Jour</span>
                    </a>
                </li>
                <li class="nav-item d-none d-lg-flex align-items-center mx-1">
                    <a href="{{ route('raw-material-purchases.index') }}"
                        class="btn btn-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                        style="height: 38px;">
                        <iconify-icon icon="solar:bag-bold-duotone" class="fs-6"></iconify-icon>
                        <span>Les Achats</span>
                    </a>
                </li>
            </ul>
            <div class="d-block d-xl-none">
                <a href="{{ route('dashboard') }}" class="text-nowrap nav-link">
                    <img src="{{ asset('assets/images/logos/logo-poly.png') }}" alt="maroc-polystyrène"
                        style="height: 35px;" />
                </a>
            </div>
            <a class="navbar-toggler nav-icon-hover p-0 border-0 nav-icon-hover-bg rounded-circle"
                href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="p-2">
                    <i class="ti ti-dots fs-7"></i>
                </span>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex align-items-center justify-content-between px-0 px-xl-8">
                    <ul class="navbar-nav flex-row mx-auto ms-lg-auto align-items-center justify-content-center">
                        <li class="nav-item dropdown">
                            <a href="javascript:void(0)"
                                class="nav-link nav-icon-hover-bg rounded-circle d-flex d-lg-none align-items-center justify-content-center"
                                type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar"
                                aria-controls="offcanvasWithBothOptions">
                                <iconify-icon icon="solar:sort-line-duotone" class="fs-6"></iconify-icon>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-icon-hover-bg rounded-circle moon dark-layout"
                                href="javascript:void(0)">
                                <iconify-icon icon="solar:moon-line-duotone" class="moon fs-6"></iconify-icon>
                            </a>
                            <a class="nav-link nav-icon-hover-bg rounded-circle sun light-layout"
                                href="javascript:void(0)" style="display: none">
                                <iconify-icon icon="solar:sun-2-line-duotone" class="sun fs-6"></iconify-icon>
                            </a>
                        </li>
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link nav-icon-hover-bg rounded-circle" href="javascript:void(0)"
                                data-bs-toggle="modal" data-bs-target="#exampleModal">
                                <iconify-icon icon="solar:magnifer-line-duotone" class="fs-6"></iconify-icon>
                            </a>
                        </li>

                        <!-- Notifications Dropdown Horizontal (admin only) -->
                        @if (Auth::check() && Auth::user()->isAdmin())
                            <li class="nav-item dropdown nav-icon-hover-bg rounded-circle">
                                <a class="nav-link position-relative" href="javascript:void(0)"
                                    id="notificationsDropHorizontal" data-bs-toggle="dropdown" aria-expanded="false">
                                    <iconify-icon icon="solar:bell-bing-line-duotone" class="fs-6"></iconify-icon>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        id="notification-badge-horizontal"
                                        style="font-size: 10px; display:none;">0</span>
                                </a>
                                <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up"
                                    aria-labelledby="notificationsDropHorizontal" style="width: 400px;">
                                    <div class="d-flex align-items-center justify-content-between py-3 px-7">
                                        <h5 class="mb-0 fs-5 fw-semibold">Notifications</h5>
                                        <span class="badge text-bg-primary rounded-4 px-3 py-1 lh-sm"
                                            id="notification-count-horizontal">0</span>
                                    </div>
                                    <div class="message-body" data-simplebar
                                        style="max-height: 420px; overflow-y: auto;"
                                        id="notifications-list-horizontal">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Chargement...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-6 px-7 mb-1">
                                        <button class="btn btn-primary w-100 notif-mark-all-btn"
                                            onclick="markAllAsRead()">Marquer tout comme lu</button>
                                    </div>
                                </div>
                            </li>
                        @endif

                        <!-- Profile Dropdown (Horizontal) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="javascript:void(0)" id="drop1" aria-expanded="false">
                                <div class="d-flex align-items-center gap-2 lh-base">
                                    <img src="{{ Auth::user()->employee && Auth::user()->employee->photo ? asset('storage/' . Auth::user()->employee->photo) : asset('assets/images/profile/user-1.jpg') }}"
                                        class="rounded-circle" width="35" height="35"
                                        alt="{{ Auth::user()->username }}" />
                                    <iconify-icon icon="solar:alt-arrow-down-bold" class="fs-2"></iconify-icon>
                                </div>
                            </a>
                            <div class="dropdown-menu profile-dropdown dropdown-menu-end dropdown-menu-animate-up"
                                aria-labelledby="drop1">
                                <div class="position-relative px-4 pt-3 pb-2">
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom gap-6">
                                        <img src="{{ Auth::user()->employee && Auth::user()->employee->photo ? asset('storage/' . Auth::user()->employee->photo) : asset('assets/images/profile/user-1.jpg') }}"
                                            class="rounded-circle" width="56" height="56"
                                            alt="{{ Auth::user()->username }}" />
                                        <div>
                                            <h5 class="mb-0 fs-12">
                                                {{ Auth::user()->employee->full_name ?? Auth::user()->username }}
                                                <span
                                                    class="badge bg-{{ Auth::user()->role == 'admin' ? 'danger' : (Auth::user()->role == 'manager' ? 'warning' : 'info') }} fs-11">
                                                    {{ ucfirst(Auth::user()->role) }}
                                                </span>
                                            </h5>
                                            <p class="mb-0 text-dark">{{ Auth::user()->email }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-circle me-1"
                                                    style="color: {{ Auth::user()->is_active ? '#28a745' : '#dc3545' }}; font-size: 8px;"></i>
                                                {{ Auth::user()->is_active ? 'Actif' : 'Inactif' }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="message-body">
                                        @if (Auth::user()->isAdmin())
                                            <a href="{{ route('profile.edit') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center">
                                                <i class="fas fa-user-circle me-2 fs-5"></i> Mon Profil
                                            </a>
                                            <a href="{{ route('profile.password') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center">
                                                <i class="fas fa-lock me-2 fs-5"></i> Mot de passe
                                            </a>
                                            <div class="dropdown-divider"></div>
                                        @endif

                                        @if (Auth::user()->isAdmin())
                                            <a href="{{ route('expenses.index') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center">
                                                <i class="fas fa-coins me-2 fs-5"></i> Dépenses
                                                @php
                                                    $pendingExpenses = \App\Models\Expense::whereNull(
                                                        'approved_by',
                                                    )->count();
                                                @endphp
                                                @if ($pendingExpenses > 0)
                                                    <span
                                                        class="badge bg-danger-subtle text-danger rounded ms-auto">{{ $pendingExpenses }}</span>
                                                @endif
                                            </a>
                                            <a href="{{ route('employees.index') }}"
                                                class="p-2 dropdown-item h6 rounded-1 d-flex align-items-center">
                                                <i class="fas fa-users me-2 fs-5"></i> Ressources
                                            </a>
                                            <div class="dropdown-divider"></div>
                                        @endif

                                        <form action="{{ route('logout') }}" method="POST"
                                            class="dropdown-item p-0">
                                            @csrf
                                            <button type="submit"
                                                class="p-2 h6 rounded-1 w-100 text-start border-0 bg-transparent d-flex align-items-center">
                                                <i class="fas fa-sign-out-alt me-2 fs-5"></i> Déconnexion
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>

<!-- Add this CSS to ensure buttons are properly styled -->
<style>
    .btn-sm.rounded-pill {
        border-radius: 50px !important;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-sm.rounded-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .btn-sm.rounded-pill iconify-icon {
        font-size: 1.1rem;
    }

    @media (max-width: 991.98px) {
        .with-vertical .navbar-nav .nav-item.d-none.d-lg-flex {
            display: none !important;
        }
    }
</style>
