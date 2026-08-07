{{-- Shared broker badge: ownership color resolved from the brokers table (Broker::categoryClass). --}}
{{-- Props: code (string, required), class (string, extra classes). --}}
@props(['code', 'class' => ''])
@php
    // ponytail: ownership color comes from brokers table (Broker::categoryClass).
    // Unknown code -> swasta/purple fallback; no hardcoded lists.
    $cls = \App\Models\Broker::categoryClass($code);
@endphp
<span class="badge {{ $cls }} {{ $class }}">{{ $code }}</span>
