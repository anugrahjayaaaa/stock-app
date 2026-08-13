@extends('layouts.app')
@section('header', __('Broker Summary'))

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Broker Summary — Stockbit Market Detector</h3>
            </div>
            <div class="card-body">
                <x-broker-summary
                    :ticker="$symbol"
                    :from="$from" :to="$to"
                    :tx-type="$txType" :board="$board" :investor="$investor"
                    :stocks="$stocks"
                    :buyers="$buyers" :sellers="$sellers"
                    :bandar="$bandar" :total="$total"
                    :error="$error"
                    :ipott-route="route('stock.broker-summary.data', ['code' => 'PLACEHOLDER'])" />
            </div>
        </div>
    </div>
@endsection
