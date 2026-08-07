@extends('layouts.app')
@section('header', __('Broker List'))

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">Broker List (IDX)</h3>
            <span class="badge bg-purple-subtle text-purple border rounded-pill ms-2" style="font-size:10px;">
                {{ $brokers->count() }} brokers
            </span>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-2">
                <div class="col-auto">
                    <span class="badge bg-danger">Asing</span>
                    <span class="badge bg-success">BUMN</span>
                    <span class="badge bg-purple">Swasta Lokal</span>
                </div>
            </div>
            <div style="max-height:70vh;overflow:auto;">
                <table class="table table-sm table-hover table-striped mb-0 align-middle font-monospace" style="font-size:12px;">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Code</th>
                            <th>Sekuritas</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($brokers as $b)
                            <tr>
                                <td><x-broker-badge :code="$b->code" /></td>
                                <td>{{ $b->name }}</td>
                                <td>
                                    <span class="badge {{ $b->category === 'asing' ? 'bg-danger' : ($b->category === 'bumn' ? 'bg-success' : 'bg-purple') }}">
                                        {{ match ($b->category) { 'asing' => 'Asing', 'bumn' => 'BUMN', default => 'Swasta Lokal' } }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
