<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MAROC POLYSTRENE')</title>
    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />

    <!-- Core Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/libs/prismjs/themes/prism-okaidia.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/dist/css/select2.min.css') }}">
    <!-- Icon Kits -->
    <script src="https://kit.fontawesome.com/f20b560d29.js" crossorigin="anonymous"></script>
    @stack('stylesheets')

    <style>
        * {
            font-weight: 700 !important;
        }

        .statistics .fas {
            font-size: 38px !important;
        }

        .select2-selection__arrow {
            display: none;
        }

        .select2-container.select2-container--default.select2-container--open {
            z-index: 9999;
        }

        .select2-selection__rendered {
            color: black !important;
        }

        .table-responsive .dataTables_wrapper .dataTables_paginate .paginate_button {
            cursor: pointer;
            padding: 0px;
            border: 0;
            border-radius: 20px !important;
            margin: 0 3px;
        }

        td {
            color: black !important;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .dataTables_wrapper .dataTables_length {
            float: left;
        }

        /* Black rows */
        #materials-table tbody tr {
            background-color: #000000;
            color: #ffffff;
        }

        #materials-table tbody tr:hover {
            background-color: #333333;
            color: #ffffff;
        }

        /* Table header */
        #materials-table thead th {
            background-color: #f8f9fa;
            color: #000000;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        /* Badges with better contrast */
        .badge {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 500;
        }

        .badge-success {
            background-color: rgba(40, 167, 69, 0.2) !important;
            color: #28a745 !important;
            border-color: rgba(40, 167, 69, 0.3);
        }

        .badge-danger {
            background-color: rgba(220, 53, 69, 0.2) !important;
            color: #dc3545 !important;
            border-color: rgba(220, 53, 69, 0.3);
        }

        .badge-warning {
            background-color: rgba(255, 193, 7, 0.2) !important;
            color: #ffc107 !important;
            border-color: rgba(255, 193, 7, 0.3);
        }

        .badge-primary {
            background-color: rgba(7, 73, 255, 0.2) !important;
            color: #4d4e8d !important;
            border-color: rgba(255, 193, 7, 0.3);
        }

        .badge-info {
            background-color: rgba(45, 128, 196, 0.2) !important;
            color: #3c7cc5 !important;
            border-color: rgba(255, 193, 7, 0.3);
        }

        /* Stock status highlighting */
        .stock-low {
            border-left: 4px solid #dc3545 !important;
        }

        .stock-high {
            border-left: 4px solid #ffc107 !important;
        }

        /* Dropdown styling */
        .dropdown-toggle-no-caret::after {
            display: none !important;
        }

        .dropdown-menu {
            border: 1px solid #9b9797;
        }

        /* Card header styling */
        .card-header-custom {
            background: linear-gradient(45deg, #2c3e50, #4a6491);
            color: white;
            border-bottom: none;
        }

        /* Custom button */
        .btn-custom {
            background-color: #000000;
            color: white;
            border: 1px solid #333333;
        }

        .btn-custom:hover {
            background-color: #333333;
            color: white;
        }

        /* Fix for mobile menu overlay - UPDATED */
        @media (max-width: 991.98px) {
            #mobileNavContent {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1040;
                background-color: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(2px);
                display: none;
            }

            #mobileNavContent.show {
                display: block !important;
            }

            #mobileNavContent .mobile-menu-container {
                background: white;
                max-height: calc(100vh - 70px);
                overflow-y: auto;
                border-radius: 0 0 12px 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            }

            /* Prevent body scroll when menu is open */
            body.mobile-menu-open {
                overflow: hidden;
            }

            /* Ensure the hamburger icon is clickable */
            #headerCollapse {
                position: relative;
                z-index: 1050;
            }
        }

        /* Fix for accordion */
        .accordion-button:not(.collapsed) {
            background-color: transparent !important;
            box-shadow: none !important;
        }

        .accordion-button:focus {
            box-shadow: none !important;
            border-color: transparent !important;
        }

        .accordion-button::after {
            background-size: 1rem;
            width: 1rem;
            height: 1rem;
        }

        /* Fix for Bootstrap collapse transition */
        .collapse:not(.show) {
            display: none;
        }

        .collapsing {
            transition: height 0.35s ease;
        }

        /* Add to your CSS file */
        .sidebar-item>ul {
            display: none;
            list-style: none;
            padding-left: 1.5rem;
        }

        .sidebar-item>ul.show {
            display: block;
        }

        .sidebar-link.has-arrow {
            cursor: pointer;
        }

        .sidebar-link.has-arrow[aria-expanded="true"]::after {
            transform: translateY(-50%) rotate(50deg);
        }
    </style>

</head>

<body class="link-sidebar">
    <!-- Toast -->
    {{-- <div class="toast toast-onload align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body hstack align-items-start gap-6">
        <i class="ti ti-alert-circle fs-6"></i>
        <div>
            <h5 class="text-white fs-3 mb-1">Welcome to MatDash</h5>
            <h6 class="text-white fs-2 mb-0">Easy to costomize the Template!!!</h6>
        </div>
        <button type="button" class="btn-close btn-close-white fs-2 m-0 ms-auto shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div> --}}
    <!-- Preloader -->
    <div class="preloader">
        <img src="{{ asset('assets/images/logos/favicon.png') }}" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper">
        @include('layouts.horizontal-sidebar')
        <div class="page-wrapper">
            <!--  Header Start -->
            @include('layouts.header')

            <!--  Header End -->
            <div class="body-wrapper" style="padding-top: 40px;">
                @yield('content')
            </div>
        </div>
    </div>
    <div class="dark-transparent sidebartoggler"></div>

    <!-- Import Js Files -->
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn-script.com/ajax/libs/jquery/3.7.1/jquery.js" type="text/javascript"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme/app.init.js') }}"></script>
    <script src="{{ asset('assets/js/theme/theme.js') }}"></script>
    <script src="{{ asset('assets/js/theme/app.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme/sidebarmenu-default.js') }}"></script>

    <!-- solar icons -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards/dashboard1.js') }}"></script>
    <script src="{{ asset('assets/libs/fullcalendar/index.global.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#headerCollapse').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var $menu = $('#mobileNavContent');

                if ($menu.hasClass('show')) {
                    $menu.removeClass('show').collapse('hide');
                    $('body').removeClass('mobile-menu-open');
                } else {
                    $menu.addClass('show').collapse('show');
                    $('body').addClass('mobile-menu-open');
                }
            });

            // Close mobile menu when clicking on a link
            $('#mobileNavContent a').on('click', function() {
                $('#mobileNavContent').removeClass('show').collapse('hide');
                $('body').removeClass('mobile-menu-open');
            });

            // Close mobile menu when clicking on the overlay background
            $('#mobileNavContent').on('click', function(e) {
                if (e.target === this) {
                    $(this).removeClass('show').collapse('hide');
                    $('body').removeClass('mobile-menu-open');
                }
            });

            // Handle escape key
            $(document).on('keyup', function(e) {
                if (e.key === 'Escape' && $('#mobileNavContent').hasClass('show')) {
                    $('#mobileNavContent').removeClass('show').collapse('hide');
                    $('body').removeClass('mobile-menu-open');
                }
            });

            // Make sure Bootstrap collapse events work properly
            $('#mobileNavContent').on('hidden.bs.collapse', function() {
                $(this).removeClass('show');
                $('body').removeClass('mobile-menu-open');
            });


            $('.sidebar-item .has-arrow').on('click', function(e) {
                e.preventDefault();

                // Get the parent sidebar-item
                var parentItem = $(this).closest('.sidebar-item');

                // Find the submenu (ul) within this sidebar-item
                var submenu = parentItem.find('> ul');

                // Toggle the submenu
                if (submenu.hasClass('show')) {
                    submenu.removeClass('show');
                    $(this).attr('aria-expanded', 'false');
                } else {
                    // Close other open submenus at the same level (optional)
                    // parentItem.siblings('.sidebar-item').find('> ul').removeClass('show');
                    // parentItem.siblings('.sidebar-item').find('.has-arrow').attr('aria-expanded', 'false');

                    submenu.addClass('show');
                    $(this).attr('aria-expanded', 'true');
                }
            });

            // Keep the current active menu open
            var currentUrl = window.location.href;
            $('.sidebar-link').each(function() {
                var linkUrl = $(this).attr('href');
                if (linkUrl && linkUrl !== 'javascript:void(0)' && currentUrl.indexOf(linkUrl) !== -1) {
                    $(this).addClass('active');
                    // Open parent menu if it's a sub-item
                    $(this).closest('.sidebar-item').find('> .has-arrow').attr('aria-expanded', 'true');
                    $(this).closest('ul').addClass('show');
                }
            });

            // Add this function to handle theme toggling with localStorage
            function handleThemeToggle() {
                // Get the current theme from localStorage or default to 'dark'
                const savedTheme = localStorage.getItem('bs-theme') || 'dark';

                // Apply the saved theme on page load
                applyTheme(savedTheme);

                // Add click event listeners to both moon and sun icons
                const moonIcons = document.querySelectorAll('.moon, .dark-layout');
                const sunIcons = document.querySelectorAll('.sun, .light-layout');

                // When clicking on moon icon (switch to dark mode)
                moonIcons.forEach(icon => {
                    icon.addEventListener('click', function(e) {
                        e.preventDefault();
                        applyTheme('dark');
                        localStorage.setItem('bs-theme', 'dark');
                    });
                });

                // When clicking on sun icon (switch to light mode)
                sunIcons.forEach(icon => {
                    icon.addEventListener('click', function(e) {
                        e.preventDefault();
                        applyTheme('light');
                        localStorage.setItem('bs-theme', 'light');
                    });
                });
            }

            // Function to apply theme and update UI elements
            function applyTheme(theme) {
                // Set the data-bs-theme attribute on html element
                document.documentElement.setAttribute('data-bs-theme', theme);

                // Update icon visibility
                const moonElements = document.querySelectorAll('.moon, .dark-layout');
                const sunElements = document.querySelectorAll('.sun, .light-layout');

                if (theme === 'dark') {
                    // Dark mode active - show moon icons, hide sun icons
                    moonElements.forEach(el => {
                        if (el.classList && el.classList.contains('moon')) {
                            el.style.display = 'flex';
                        } else if (el.style) {
                            el.style.display = 'flex';
                        }
                    });
                    sunElements.forEach(el => {
                        if (el.classList && el.classList.contains('sun')) {
                            el.style.display = 'none';
                        } else if (el.style) {
                            el.style.display = 'none';
                        }
                    });

                    // Update logo if needed
                    const lightLogos = document.querySelectorAll('.light-logo');
                    const darkLogos = document.querySelectorAll('.dark-logo');
                    lightLogos.forEach(el => el.style.display = 'none');
                    darkLogos.forEach(el => el.style.display = 'flex');

                } else {
                    // Light mode active - show sun icons, hide moon icons
                    moonElements.forEach(el => {
                        if (el.classList && el.classList.contains('moon')) {
                            el.style.display = 'none';
                        } else if (el.style) {
                            el.style.display = 'none';
                        }
                    });
                    sunElements.forEach(el => {
                        if (el.classList && el.classList.contains('sun')) {
                            el.style.display = 'flex';
                        } else if (el.style) {
                            el.style.display = 'flex';
                        }
                    });

                    // Update logo if needed
                    const lightLogos = document.querySelectorAll('.light-logo');
                    const darkLogos = document.querySelectorAll('.dark-logo');
                    lightLogos.forEach(el => el.style.display = 'flex');
                    darkLogos.forEach(el => el.style.display = 'none');
                }
            }

            // Initialize the theme toggle when DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                handleThemeToggle();

                if (typeof AdminSettings !== 'undefined') {
                    const originalManageDarkThemeLayout = AdminSettings.ManageDarkThemeLayout;
                    AdminSettings.ManageDarkThemeLayout = function() {
                        const savedTheme = localStorage.getItem('bs-theme');
                        if (savedTheme) {
                            settings.Theme = savedTheme;
                        }
                        originalManageDarkThemeLayout.call(this);
                    };

                    // Re-initialize with localStorage preference
                    AdminSettings.ManageDarkThemeLayout();
                }
            });
        });

        @if(Auth::check() && Auth::user()->isAdmin())
        // ── Notification polling (admin only, fetch + setTimeout) ──────────────
        const NOTIF_POLL_MS   = 60000;  // re-fetch every 60 s
        const NOTIF_URL       = '{{ route('notifications.get') }}';
        const NOTIF_MARK_URL  = '{{ route('notifications.mark-read', '') }}';
        const NOTIF_ALL_URL   = '{{ route('notifications.mark-all-read') }}';
        const NOTIF_CSRF      = '{{ csrf_token() }}';

        let _notifTimeout      = null;
        let _lastNotifCount    = 0;

        const CATEGORY_LABELS = {
            production : 'Production',
            vehicle    : 'Véhicule',
            machine    : 'Machine',
            driver     : 'Chauffeur',
            stock      : 'Stock',
        };

        function scheduleNotifLoad(delay) {
            clearTimeout(_notifTimeout);
            _notifTimeout = setTimeout(loadNotifications, delay);
        }

        function loadNotifications() {
            fetch(NOTIF_URL, {
                method  : 'GET',
                headers : { 'X-CSRF-TOKEN': NOTIF_CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(function (res) {
                if (res.success) {
                    const data  = res.data || [];
                    const total = data.length;
                    if (total > _lastNotifCount && _lastNotifCount !== 0) playNotifSound();
                    _lastNotifCount = total;
                    renderNotifications(data);
                    updateUnreadBadge(res.unread_count || 0);
                }
                scheduleNotifLoad(NOTIF_POLL_MS);
            })
            .catch(function () {
                showNotifError();
                scheduleNotifLoad(NOTIF_POLL_MS);
            });
        }

        function renderNotifications(list) {
            let html = '';

            if (!list || list.length === 0) {
                html = `
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-0">Aucune notification</p>
                    </div>`;
            } else {
                list.forEach(function (n) {
                    const catLabel = CATEGORY_LABELS[n.category] || n.category;
                    const bg       = n.is_read ? '' : 'background-color:rgba(13,110,253,.05);';
                    html += `
                        <a href="javascript:void(0)"
                           class="py-6 px-7 d-flex align-items-start dropdown-item gap-3 notif-item"
                           data-id="${n.id || ''}" data-link="${escHtml(n.link || '')}">
                            <span class="flex-shrink-0 bg-${n.color}-subtle text-${n.color} rounded-circle
                                         d-flex align-items-center justify-content-center"
                                  style="width:40px;height:40px;min-width:40px;${bg}">
                                <i class="${n.icon}"></i>
                            </span>
                            <div class="flex-grow-1 overflow-hidden" style="${bg}border-radius:6px;padding:4px 6px;">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                    <h6 class="mb-0 fw-semibold lh-sm" style="font-size:.8rem;">${escHtml(n.title)}</h6>
                                    <span class="text-muted text-nowrap" style="font-size:.7rem;">${fmtDate(n.date)}</span>
                                </div>
                                <p class="mb-1 text-muted" style="font-size:.75rem;line-height:1.3;">${escHtml(n.message)}</p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge bg-${n.color}-subtle text-${n.color}" style="font-size:.65rem;">${escHtml(catLabel)}</span>
                                    ${!n.is_read ? '<span class="badge bg-primary" style="font-size:.65rem;">Nouveau</span>' : ''}
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider my-0"></div>`;
                });
            }

            document.querySelectorAll('#notifications-list, #notifications-list-horizontal')
                .forEach(el => el.innerHTML = html);

            const total = list.length;
            document.querySelectorAll('#notification-count, #notification-count-horizontal')
                .forEach(el => el.textContent = total);

            // Click handlers
            document.querySelectorAll('.notif-item').forEach(function (el) {
                el.addEventListener('click', function () {
                    const id   = el.dataset.id;
                    const link = el.dataset.link;
                    if (id) {
                        doMarkRead(id, function () { if (link) window.location.href = link; });
                    } else if (link) {
                        window.location.href = link;
                    }
                });
            });
        }

        function showNotifError() {
            const errHtml = `
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fs-1 text-danger mb-3 d-block"></i>
                    <p class="text-muted mb-0">Erreur de chargement</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="loadNotifications()">Réessayer</button>
                </div>`;
            document.querySelectorAll('#notifications-list, #notifications-list-horizontal')
                .forEach(el => el.innerHTML = errHtml);
        }

        function doMarkRead(id, cb) {
            fetch(NOTIF_MARK_URL + '/' + id, {
                method  : 'POST',
                headers : { 'X-CSRF-TOKEN': NOTIF_CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(function (res) {
                if (res.success) updateUnreadBadge(res.unread_count || 0);
                if (cb) cb();
            })
            .catch(function () { if (cb) cb(); });
        }

        function markAllAsRead() {
            const btns = document.querySelectorAll('#mark-all-read-btn, .notif-mark-all-btn');
            btns.forEach(b => b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Chargement...');

            fetch(NOTIF_ALL_URL, {
                method  : 'POST',
                headers : { 'X-CSRF-TOKEN': NOTIF_CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(function (res) {
                if (res.success) {
                    updateUnreadBadge(0);
                    loadNotifications();
                    showToast('Toutes les notifications ont été marquées comme lues', 'success');
                }
            })
            .catch(function () { showToast('Erreur lors de la mise à jour', 'error'); })
            .finally(function () {
                btns.forEach(b => b.textContent = 'Marquer tout comme lu');
            });
        }

        function updateUnreadBadge(count) {
            document.querySelectorAll('#notification-badge, #notification-badge-horizontal').forEach(function (el) {
                el.textContent = count;
                el.style.display = count > 0 ? '' : 'none';
            });
        }

        function fmtDate(iso) {
            if (!iso) return '';
            const d    = new Date(iso);
            const diff = Math.floor((Date.now() - d) / 86400000);
            if (diff === 0) return "Aujourd'hui";
            if (diff === 1) return 'Hier';
            if (diff < 7)  return `Il y a ${diff} j`;
            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
        }

        function escHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function playNotifSound() {
            try {
                new Audio('{{ asset('sounds/notification.mp3') }}')
                    .play().catch(() => {});
            } catch (_) {}
        }

        function showToast(msg, type) {
            let box = document.querySelector('.toast-container');
            if (!box) {
                box = document.createElement('div');
                box.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                box.style.zIndex = '1100';
                document.body.appendChild(box);
            }
            const id  = 'toast-' + Date.now();
            const cls = type === 'error' ? 'bg-danger' : 'bg-success';
            const ico = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
            box.insertAdjacentHTML('beforeend', `
                <div id="${id}" class="toast align-items-center text-white ${cls} border-0"
                     role="alert" data-bs-autohide="true" data-bs-delay="3000">
                    <div class="d-flex">
                        <div class="toast-body"><i class="fas ${ico} me-2"></i>${escHtml(msg)}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast"></button>
                    </div>
                </div>`);
            const el = document.getElementById(id);
            new bootstrap.Toast(el).show();
            el.addEventListener('hidden.bs.toast', () => el.remove());
        }

        // Boot
        document.addEventListener('DOMContentLoaded', function () {
            loadNotifications();

            // Reload immediately when dropdown is opened
            document.querySelectorAll('#notificationsDrop, #notificationsDropHorizontal').forEach(function (trigger) {
                trigger.addEventListener('shown.bs.dropdown', loadNotifications);
            });
        });
        @endif
    </script>
    @stack('scripts')
</body>

</html>
