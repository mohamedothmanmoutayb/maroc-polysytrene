@php
    $totalStock = $famille->stocks->sum('current_quantity');
    $hasOutputs = $famille->outputs->count() > 0;
    $canDelete = $totalStock == 0 && !$hasOutputs;
@endphp

<div class="dropdown dropstart">
    <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical fs-6"></i>
    </a>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <!-- View Details -->
        <li>
            <a class="dropdown-item d-flex align-items-center gap-3"
                href="{{ route('familles.show', $famille->famille_id) }}">
                <i class="fs-4 ti ti-eye"></i>Voir Détails
            </a>
        </li>

        <!-- Edit -->
        @can('edit_families')
        <li>
            <a class="dropdown-item d-flex align-items-center gap-3"
                href="{{ route('familles.edit', $famille->famille_id) }}">
                <i class="fs-4 ti ti-edit"></i>Modifier
            </a>
        </li>

        <!-- Manage Products -->
        <li>
            <a class="dropdown-item d-flex align-items-center gap-3 btn-manage-products" href="javascript:void(0)"
                data-famille-id="{{ $famille->famille_id }}" data-famille-name="{{ $famille->famille_name }}"
                data-famille-code="{{ $famille->famille_code }}">
                <i class="fs-4 ti ti-packages text-primary"></i>
                <span class="text-primary">Gérer les Produits</span>
            </a>
        </li>

        <!-- Stock Adjustment -->
        <li>
            <a class="dropdown-item d-flex align-items-center gap-3 btn-adjust-stock" href="javascript:void(0)"
                data-famille-id="{{ $famille->famille_id }}" data-famille-name="{{ $famille->famille_name }}"
                data-famille-code="{{ $famille->famille_code }}">
                <i class="fs-4 ti ti-adjustments text-warning"></i>
                <span class="text-warning">Ajuster le Stock</span>
            </a>
        </li>
        @endcan

        <!-- View Production Outputs -->
        @if ($hasOutputs)
            <li>
                <a class="dropdown-item d-flex align-items-center gap-3"
                    href="{{ route('production-output.index', ['famille_id' => $famille->famille_id]) }}">
                    <i class="fs-4 ti ti-checkup-list"></i>Sorties de Production
                </a>
            </li>
        @endif

        <li>
            <hr class="dropdown-divider">
        </li>

        <!-- Activate/Deactivate -->
        @can('edit_families')
        @if ($famille->is_active)
            <li>
                <a class="dropdown-item d-flex align-items-center gap-3 btn-deactivate" href="javascript:void(0)"
                    data-famille-id="{{ $famille->famille_id }}" data-famille-name="{{ $famille->famille_name }}">
                    <i class="fs-4 ti ti-ban text-warning"></i>
                    <span class="text-warning">Désactiver</span>
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item d-flex align-items-center gap-3 btn-activate" href="javascript:void(0)"
                    data-famille-id="{{ $famille->famille_id }}" data-famille-name="{{ $famille->famille_name }}">
                    <i class="fs-4 ti ti-check text-success"></i>
                    <span class="text-success">Activer</span>
                </a>
            </li>
        @endif
        @endcan

        <!-- Delete (Only if no stock and no outputs) -->
        @can('delete_families')
        @if ($canDelete)
            <li>
                <a class="dropdown-item d-flex align-items-center gap-3 btn-delete" href="javascript:void(0)"
                    data-famille-id="{{ $famille->famille_id }}" data-famille-name="{{ $famille->famille_name }}">
                    <i class="fs-4 ti ti-trash text-danger"></i>
                    <span class="text-danger">Supprimer</span>
                </a>
            </li>
        @endif
        @endcan
    </ul>
</div>
