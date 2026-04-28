@props(['value' => null])

<label {{ $attributes->merge(['class' => 'label-dark']) }}>
    {{ $value ?? $slot }}
</label>
