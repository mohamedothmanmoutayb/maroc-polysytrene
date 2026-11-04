@extends('layouts.app')

@section('title', 'Banques & dimensions des chèques')

@section('content')
    <div class="container-fluid" style="max-width:1100px !important">
        <div class="card card-body py-3 mb-4 d-flex flex-row justify-content-between align-items-center">
            <h4 class="mb-0 card-title">Banques &amp; dimensions des chèques</h4>
            <a href="{{ route('printed-cheques.index') }}" class="btn btn-light btn-sm">Retour aux chèques</a>
        </div>
        @include('pages.printed-cheques._flash')

        <div class="card mb-4"><div class="card-body">
            <form method="POST" action="{{ route('cheque-banks.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-6"><label class="form-label">Ajouter une banque</label>
                    <input name="label" class="form-control" placeholder="Ex. Bank of Africa" required></div>
                <div class="col-md-3"><button class="btn btn-primary">Ajouter</button></div>
            </form>
        </div></div>

        <div class="card"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Banque</th><th>Dimensions</th><th>Spécimen</th><th></th></tr></thead>
                <tbody>
                @foreach ($banks as $b)
                    <tr>
                        <td class="fw-semibold">{{ $b->label }}</td>
                        <td>{{ rtrim(rtrim(number_format($b->width_mm, 2, '.', ''), '0'), '.') }} × {{ rtrim(rtrim(number_format($b->height_mm, 2, '.', ''), '0'), '.') }} mm</td>
                        <td>{!! $b->sample_path ? '<span class="badge bg-success">Oui</span>' : '<span class="text-muted">—</span>' !!}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('cheque-banks.edit', $b) }}" class="btn btn-sm btn-outline-primary">Calibrer</a>
                            <a href="{{ route('cheque-banks.test', $b) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Test d'impression</a>
                            <form method="POST" action="{{ route('cheque-banks.destroy', $b) }}" class="d-inline" onsubmit="return confirm('Supprimer cette banque ?')">
                                @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div></div>
    </div>
@endsection
