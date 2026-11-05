{{-- Plan de découpe 3D en temps réel pour le formulaire de création. --}}
<div class="row mb-4 d-none" id="cuttingLiveCard">
    <div class="col-12">
        <div class="card info-card">
            <div class="card-header text-white" style="background:linear-gradient(90deg,#1e3a8a,#2563eb)">
                <h6 class="card-title mb-0 text-white">
                    <i class="fas fa-cube me-2"></i>Plan de découpe 3D
                </h6>
            </div>
            <div class="card-body">
                <div id="cutting-live-root"></div>
            </div>
        </div>
    </div>
</div>

@php
    $cuttingDims = [];
    foreach ([$productionProducts, $decoupageProducts, $salesProducts] as $list) {
        foreach ($list as $p) {
            $cuttingDims[$p->product_id] = [
                'name' => $p->product_name,
                'code' => $p->product_code,
                'w' => round((float) $p->width_m * 100, 2),
                'h' => round((float) $p->height_m * 100, 2),
                'd' => round((float) $p->depth_m * 100, 2),
            ];
        }
    }
@endphp

@push('scripts')
    <script src="{{ asset('assets/libs/three/three.min.js') }}"></script>
    <script src="{{ asset('assets/libs/three/OrbitControls.js') }}"></script>
    <script src="{{ asset('assets/js/cutting-optimizer.js') }}?v={{ filemtime(public_path('assets/js/cutting-optimizer.js')) }}"></script>
    <script>
        (function() {
            // Dimensions (cm) de tous les produits connus du formulaire.
            var DIMS = @json($cuttingDims);

            var root, planner, timer;

            function item(id, qty) {
                var p = DIMS[id];
                if (!id || !p) return null;
                return { id: id, name: p.name, code: p.code, w: p.w, h: p.h, d: p.d, qty: Math.max(1, Math.ceil(qty || 0)) };
            }

            function collect(type) {
                var sources = [], pieces = [];
                function rows(rowSel, selSel, qtySel, into) {
                    $(rowSel).each(function() {
                        var it = item($(this).find(selSel).val(), parseFloat($(this).find(qtySel).val()) || 0);
                        if (it && parseFloat($(this).find(qtySel).val()) > 0) into.push(it);
                    });
                }
                if (type === 'type2') {
                    var src = item($('#type2_source_product_id').val(), parseFloat($('#type2_total_blocks').val()) || 1);
                    if (src) sources.push(src);
                    rows('.type2-product-row', '.type2-product-select', '.type2-quantity', pieces);
                } else if (type === 'type3') {
                    rows('.type3-sous-bloc-row', '.type3-sous-bloc-select', '.type3-sous-bloc-quantity', sources);
                    rows('.type3-product-row', '.type3-product-select', '.type3-quantity', pieces);
                } else if (type === 'type4') {
                    rows('.type4-source-row', '.type4-source-select', '.type4-source-quantity', sources);
                    rows('.type4-product-row', '.type4-product-select', '.type4-quantity', pieces);
                }
                // Un même produit ajouté deux fois = une seule ligne (quantités additionnées).
                var merge = function (list) {
                    var byId = {}, out = [];
                    list.forEach(function(it) {
                        if (byId[it.id]) { byId[it.id].qty += it.qty; } else { byId[it.id] = it; out.push(it); }
                    });
                    return out;
                };
                return { sources: merge(sources), pieces: merge(pieces), live: true, mode: type };
            }

            function refresh() {
                var type = $('input[name="production_type"]:checked').val();
                var card = $('#cuttingLiveCard');
                if (['type2', 'type3', 'type4'].indexOf(type) === -1) {
                    card.addClass('d-none');
                    return;
                }
                card.removeClass('d-none');
                if (!planner) {
                    root = document.getElementById('cutting-live-root');
                    planner = CuttingOptimizer.mount(root, { sources: [], pieces: [], live: true });
                }
                planner.update(collect(type));
            }

            function schedule() {
                clearTimeout(timer);
                timer = setTimeout(refresh, 250);
            }

            $(document).on('change input',
                '#type2_source_product_id, #type2_total_blocks, .type2-product-select, .type2-quantity, ' +
                '.type3-sous-bloc-select, .type3-sous-bloc-quantity, .type3-product-select, .type3-quantity, ' +
                '.type4-source-select, .type4-source-quantity, .type4-product-select, .type4-quantity',
                schedule);
            $(document).on('click', '.remove-type2-product, .remove-type3-product, .remove-type4-product, .remove-type3-sous-bloc, .remove-type4-source', function() {
                setTimeout(schedule, 50);
            });
            $(document).on('change', 'input[name="production_type"]', schedule);
            $(refresh);
        })();
    </script>
@endpush
