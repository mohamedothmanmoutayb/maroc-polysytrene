@php
    // Check if we have $output (from first output) or need to get it from $order
    $output = $output ?? ($order->outputs->first() ?? null);

    if ($output) {
        $status = $output->productionOrder->status ?? 'unknown';
        $outputId = $output->output_id;
        $orderId = $output->production_order_id;
        $productId = $output->product_id;
        $productionDate = $output->production_date;
        $canEdit = in_array($status, ['in_progress', 'pending', 'approved']);
    } else {
        $status = $order->status ?? 'unknown';
        $orderId = $order->order_id;
        $productId = $order->product_id;
        $canEdit = false;
    }
@endphp

@if ($output)
    <div class="dropdown dropstart">
        <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti ti-dots-vertical fs-6"></i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <!-- View Order Details -->
            <li>
                <a class="dropdown-item d-flex align-items-center gap-3"
                    href="{{ route('production-orders.show', $orderId) }}">
                    <i class="fs-4 ti ti-eye"></i>Voir Commande
                </a>
            </li>

            @if ($outputId)
                <!-- View Output Details -->
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3"
                        href="{{ route('production-output.show', $outputId) }}">
                        <i class="fs-4 ti ti-clipboard-list"></i>Voir Sortie
                    </a>
                </li>

                @if ($canEdit && $productionDate->diffInDays(now()) <= 7)
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-3"
                            href="{{ route('production-output.edit', $outputId) }}">
                            <i class="fs-4 ti ti-edit"></i>Modifier Sortie
                        </a>
                    </li>
                @endif
            @endif

            <!-- View Product -->
            <li>
                <a class="dropdown-item d-flex align-items-center gap-3"
                    href="{{ route('products.show', $productId) }}">
                    <i class="fs-4 ti ti-box"></i>Voir Produit
                </a>
            </li>

            <!-- Record Another Output (Only for in-progress orders) -->
            @if ($status === 'in_progress')
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3"
                        href="{{ route('production-output.create', ['order_id' => $orderId]) }}">
                        <i class="fs-4 ti ti-plus text-success"></i><span class="text-success">Nouvelle Sortie</span>
                    </a>
                </li>
            @endif

            <li>
                <hr class="dropdown-divider">
            </li>

            <!-- Delete (Only for outputs of pending or in-progress orders) -->
            @if ($canEdit && $outputId)
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 delete-output-btn" href="javascript:void(0)"
                        data-id="{{ $outputId }}">
                        <i class="fs-4 ti ti-trash text-danger"></i><span class="text-danger">Supprimer Sortie</span>
                    </a>
                </li>
            @else
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 disabled" href="javascript:void(0)">
                        <i class="fs-4 ti ti-lock text-muted"></i><span class="text-muted">Verrouillé</span>
                    </a>
                    <small class="dropdown-item-text text-muted ps-4">
                        Seules les sorties récentes d'ordres actifs peuvent être modifiées
                    </small>
                </li>
            @endif
        </ul>
    </div>
@else
    <span class="text-muted">N/A</span>
@endif
