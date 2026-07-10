@php
    // Calculate remaining quantity using the same logic as controller
    if ($order->status === 'completed') {
        $remainingQuantity = 0;
    } else {
        if ($order->production_type === 'decoupage') {
            $totalTargetProduced = $order->outputs->sum('quantity_produced');
            $remainingQuantity = max(0, $order->quantity_to_produce - $totalTargetProduced);
        } else {
            $totalTargetProduced = $order->outputs->sum('quantity_produced');
            $remainingQuantity = max(0, $order->quantity_to_produce - $totalTargetProduced);
        }
    }

    // Calculate total produced based on production type
    $totalProduced = 0;
    $totalVolume = 0;

    if ($order->production_type === 'type1') {
        $totalProduced = $order->outputs->where('famille_id', $order->famille_id)->sum('quantity_produced');
        $totalVolume = $order->outputs->where('famille_id', $order->famille_id)->sum('total_volume_m3');
    } elseif ($order->production_type === 'type2') {
        $totalProduced = $order->outputs->where('output_type', 'type2')->sum('quantity_produced');
        $totalConsumed = $order->outputs->where('output_type', 'type2')->sum('quantity_consumed');
        $totalVolume = $order->outputs->where('output_type', 'type2')->sum('total_volume_m3');
    } elseif ($order->production_type === 'type3') {
        $totalProduced = $order->outputs->where('output_type', 'type3')->sum('quantity_produced');
        $totalConsumed = $order->outputs->where('output_type', 'type3')->sum('quantity_consumed');
        $totalVolume = $order->outputs->where('output_type', 'type3')->sum('total_volume_m3');
    } elseif ($order->production_type === 'type4') {
        $totalProduced = $order->outputs->where('output_type', 'type4')->sum('quantity_produced');
        $totalConsumed = $order->outputs->where('output_type', 'type4')->sum('quantity_consumed');
        $totalVolume = $order->outputs->where('output_type', 'type4')->sum('total_volume_m3');
    } elseif ($order->production_type === 'type5') {
        $totalProduced = $order->outputs->where('output_type', 'type5')->sum('quantity_produced');
        $totalConsumed = $order->outputs->where('output_type', 'type5')->sum('quantity_consumed');
        $totalVolume = $order->outputs->where('output_type', 'type5')->sum('total_volume_m3');
    }

    // Calculate defect rate
    $totalDefective = $order->outputs->sum('quantity_defective');

    // Check if has waste declaration
    $hasWasteDeclaration = $order->wastes->count() > 0;
@endphp

<div class="dropdown dropstart">
    <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical fs-6"></i>
    </a>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <!-- View Details -->
        <li>
            <a class="dropdown-item d-flex align-items-center gap-3"
                href="{{ route('production-orders.show', $order->order_id) }}">
                <i class="fs-4 ti ti-eye"></i>Voir Détails
            </a>
        </li>

        <!-- Edit -->
        @can('edit_production_orders')
            @if ($order->status === 'pending' || $order->status === 'approved')
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3"
                        href="{{ route('production-orders.edit', $order->order_id) }}">
                        <i class="fs-4 ti ti-edit"></i>Modifier
                    </a>
                </li>
            @endif
        @endcan

        <!-- View Product -->
        <li>
            <a class="dropdown-item d-flex align-items-center gap-3"
                href="{{ route('products.show', $order->product_id) }}">
                <i class="fs-4 ti ti-box"></i>Voir Produit
            </a>
        </li>

        <li>
            <hr class="dropdown-divider">
        </li>

        <!-- Waste Declaration Actions -->
        @if ($remainingQuantity == 0)
            @if ($hasWasteDeclaration)
                <!-- View Waste Declaration - visible to all -->
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 btn-view-waste" href="javascript:void(0)"
                        data-order-id="{{ $order->order_id }}" data-order-number="{{ $order->order_number }}"
                        data-product-name="{{ $order->product->product_name ?? 'N/A' }}"
                        data-total-volume="{{ $totalVolume }}" data-status="{{ $order->status }}"
                        data-source-volume="{{ number_format($order->source_volume ?? 0, 4) }}"
                        data-final-volume="{{ number_format($order->final_volume ?? 0, 4) }}"
                        data-estimated-chute="{{ number_format(max(0, ($order->source_volume ?? 0) - ($order->final_volume ?? 0)), 4) }}">
                        <i class="fs-4 ti ti-eye text-info"></i><span class="text-info">Voir Chutes</span>
                    </a>
                </li>
                @can('declare_production_waste')
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-3 btn-declare-waste" href="javascript:void(0)"
                            data-order-id="{{ $order->order_id }}" data-order-number="{{ $order->order_number }}"
                            data-product-name="{{ $order->product->product_name ?? 'N/A' }}"
                            data-production-type="{{ $order->production_type }}"
                            data-target-quantity="{{ $order->production_type === 'type1' ? $order->quantity_to_produce : $order->required_quantity }}"
                            data-total-produced="{{ $totalProduced }}" data-total-volume="{{ $totalVolume }}"
                            data-defect-rate="{{ number_format($order->waste_percentage, 1) }}"
                            data-source-volume="{{ number_format($order->source_volume ?? 0, 4) }}"
                            data-final-volume="{{ number_format($order->final_volume ?? 0, 4) }}"
                            data-estimated-chute="{{ number_format(($order->waste_volume ?? 0), 4) }}">
                            <i class="fs-4 ti ti-edit text-warning"></i><span class="text-warning">Modifier Chutes</span>
                        </a>
                    </li>
                @endcan
            @else
                @can('declare_production_waste')
                    <!-- Declare Waste -->
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-3 btn-declare-waste" href="javascript:void(0)"
                            data-order-id="{{ $order->order_id }}" data-order-number="{{ $order->order_number }}"
                            data-product-name="{{ $order->product->product_name ?? 'N/A' }}"
                            data-production-type="{{ $order->production_type }}"
                            data-target-quantity="{{ $order->production_type === 'type1' ? $order->quantity_to_produce : $order->required_quantity }}"
                            data-total-produced="{{ $totalProduced }}" data-total-volume="{{ $totalVolume }}"
                            data-defect-rate="{{ number_format($order->waste_percentage, 1) }}"
                            data-source-volume="{{ number_format($order->source_volume ?? 0, 4) }}"
                            data-final-volume="{{ number_format($order->final_volume ?? 0, 4) }}"
                            data-estimated-chute="{{ number_format(($order->waste_volume ?? 0), 4) }}">
                            <i class="fs-4 ti ti-trash text-warning"></i><span class="text-warning">Déclarer Chutes</span>
                        </a>
                    </li>
                @endcan
            @endif
        @endif

        <!-- Record New Output -->
        @can('create_production_output')
            @if ($order->status === 'in_progress' && $remainingQuantity > 0)
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3"
                        href="{{ route('production-output.create', ['order_id' => $order->order_id]) }}">
                        <i class="fs-4 ti ti-plus text-success"></i><span class="text-success">Nouvelle Sortie</span>
                    </a>
                </li>
            @endif
        @endcan

        <li>
            <hr class="dropdown-divider">
        </li>

        <!-- Status-specific actions -->
        @can('approve_production_orders')
            @if ($order->status === 'pending')
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 btn-approve" href="javascript:void(0)"
                        data-id="{{ $order->order_id }}" data-order-number="{{ $order->order_number }}">
                        <i class="fs-4 ti ti-check text-info"></i><span class="text-info">Approuver</span>
                    </a>
                </li>
            @endif
        @endcan

        @can('start_production_orders')
            @if ($order->status === 'approved')
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 btn-start" href="javascript:void(0)"
                        data-id="{{ $order->order_id }}" data-order-number="{{ $order->order_number }}">
                        <i class="fs-4 ti ti-play text-primary"></i><span class="text-primary">Démarrer</span>
                    </a>
                </li>
            @endif
        @endcan

        @can('edit_production_orders')
            @if ($order->status !== 'cancelled')
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 btn-cancel-production" href="javascript:void(0)"
                        data-id="{{ $order->order_id }}" data-order-number="{{ $order->order_number }}"
                        data-status="{{ $order->status }}"
                        data-production-date="{{ $order->actual_completion_date ? $order->actual_completion_date->format('d/m/Y') : '' }}">
                        <i class="fs-4 ti ti-ban text-danger"></i><span class="text-danger">Annuler</span>
                    </a>
                </li>
            @endif
        @endcan

        @can('delete_production_orders')
            @if ($order->status === 'pending')
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 btn-delete" href="javascript:void(0)"
                        data-id="{{ $order->order_id }}" data-order-number="{{ $order->order_number }}">
                        <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer</span>
                    </a>
                </li>
            @endif
        @endcan
    </ul>
</div>
