<!DOCTYPE html>
<html lang="fr" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <!-- Balises méta requises -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />

    <title>Maroc Polystyrene - Connexion</title>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <img src="{{ asset('assets/images/logos/favicon.png') }}" alt="loader" class="lds-ripple img-fluid" />
    </div>

    <div id="main-wrapper">
        <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100">
            <div class="position-relative z-index-5">
                <div class="row gx-0">
                    <!-- Left Column - Login Form -->
                    <div class="col-lg-6 col-xl-5 col-xxl-4">
                        <div class="min-vh-100 bg-body row justify-content-center align-items-center p-5">
                            <div class="col-12 auth-card">
                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}<br>
                                        @endforeach
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                @if (session('status'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('status') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <h2 class="mb-2 mt-4 fs-7 fw-bolder">Connexion</h2>
                                <p class="mb-9">Votre tableau de bord administrateur</p>

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Adresse mail</label>
                                        <input type="text" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email') }}" required
                                            autofocus />
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">Mot de passe</label>
                                        <input type="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            name="password" required />
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">
                                        Se connecter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Welcome Section -->
                    <div
                        class="col-lg-6 col-xl-7 col-xxl-8 position-relative overflow-hidden bg-dark d-none d-lg-block">
                        <div class="circle-top"></div>
                        <div class="d-lg-flex align-items-center z-index-5 position-relative h-n80">
                            <div style="align-items: center;" class="row justify-content-center w-100">
                                <img src="{{ asset('/assets/images/logos/logo-auth.png') }}"
                                    style="width: 241px;" alt="MP-Logo" />
                                <div class="col-lg-6">
                                    <h2 class="text-white fs-10 mb-3 lh-sm">
                                        Bienvenue sur
                                        <br />
                                        Maroc Polystyrène
                                    </h2>
                                    {{-- <span class="opacity-75 fs-4 text-white d-block mb-3">
                                        Maroc Polystyrène aide les développeurs à créer des tableaux de bord organisés
                                        <br />
                                        et bien codés, remplis de modules beaux et riches.
                                    </span> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dark-transparent sidebartoggler"></div>

    <!-- Import JavaScript Files -->
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme/app.init.js') }}"></script>
    <script src="{{ asset('assets/js/theme/theme.js') }}"></script>
    <script src="{{ asset('assets/js/theme/app.min.js') }}"></script>

    <!-- Solar Icons -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>
