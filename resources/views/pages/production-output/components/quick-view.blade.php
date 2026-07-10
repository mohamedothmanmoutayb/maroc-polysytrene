<div class="row">
    <div class="col-md-6">
        <h6>Informations Générales</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td width="40%">Ordre:</td>
                <td><strong>{{ $output->productionOrder->order_number }}</strong></td>
            </tr>
            <tr>
                <td>Produit:</td>
                <td>{{ $output->product->product_name }}</td>
            </tr>
            <tr>
                <td>Type:</td>
                <td>
                    @if ($output->product->product_type === 'production')
                        <span class="badge bg-primary">Production</span>
                    @elseif($output->product->product_type === 'sales')
                        <span class="badge bg-success">Vente</span>
                    @else
                        <span class="badge bg-info">Mixte</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Date:</td>
                <td>{{ $output->production_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Approuvé par:</td>
                <td>{{ $output->approver->name ?? 'Non spécifié' }}</td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6>Quantités</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td width="40%">Produite:</td>
                <td class="text-end">{{ $output->quantity_produced }} unités</td>
            </tr>
            <tr>
                <td>Défectueuse:</td>
                <td class="text-end">{{ $output->quantity_defective }} unités</td>
            </tr>
            <tr class="table-success">
                <td><strong>Bonne:</strong></td>
                <td class="text-end"><strong>{{ $goodQuantity }} unités</strong></td>
            </tr>
            <tr>
                <td>Taux Défaut:</td>
                <td class="text-end">{{ number_format($defectRate, 1) }}%</td>
            </tr>
            <tr>
                <td>Qualité:</td>
                <td class="text-end">
                    @if ($output->quality_grade === 'excellent')
                        <span class="badge bg-success">Excellent</span>
                    @elseif($output->quality_grade === 'good')
                        <span class="badge bg-info">Bon</span>
                    @elseif($output->quality_grade === 'average')
                        <span class="badge bg-warning">Moyen</span>
                    @else
                        <span class="badge bg-danger">Mauvais</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

@if ($output->notes)
    <div class="row mt-3">
        <div class="col-12">
            <h6>Notes</h6>
            <div class="alert alert-light">
                {{ $output->notes }}
            </div>
        </div>
    </div>
@endif

@if ($output->conversion_data)
    <div class="row mt-3">
        <div class="col-12">
            <h6>Conversion</h6>
            <div class="alert alert-info">
                <i class="fas fa-exchange-alt me-2"></i>
                Ce produit a été converti en produits de vente.
            </div>
        </div>
    </div>
@endif

<div class="row mt-3">
    <div class="col-12">
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>
            Créé le {{ $output->created_at->format('d/m/Y H:i') }}
        </small>
    </div>
</div>
