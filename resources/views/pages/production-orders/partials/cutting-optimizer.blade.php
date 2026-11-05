{{-- Plan de découpe 3D optimisé. Attend $cuttingConfig = ['sources' => [...], 'pieces' => [...]] (cm). --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card info-card">
            <div class="card-header text-white" style="background:linear-gradient(90deg,#1e3a8a,#2563eb)">
                <h6 class="card-title mb-0 text-white">
                    <i class="fas fa-cube me-2"></i>Plan de découpe 3D optimisé
                    <small class="ms-2 opacity-75">moins de chutes, moins de blocs</small>
                </h6>
            </div>
            <div class="card-body">
                <div id="cutting-optimizer-root"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/libs/three/three.min.js') }}"></script>
    <script src="{{ asset('assets/libs/three/OrbitControls.js') }}"></script>
    <script src="{{ asset('assets/js/cutting-optimizer.js') }}?v={{ filemtime(public_path('assets/js/cutting-optimizer.js')) }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            CuttingOptimizer.mount(document.getElementById('cutting-optimizer-root'), @json($cuttingConfig));
        });
    </script>
@endpush
