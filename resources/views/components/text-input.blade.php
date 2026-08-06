@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['type' => 'text', 'class' => 'form-control']) }}>
