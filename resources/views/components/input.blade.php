@props(['name', 'label' => null, 'type' => 'text'])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block font-semibold">{{ $label }}</label>
    @endif
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name) }}"
        {{ $attributes->class([
            'min-h-13 w-full rounded-xl border-2 bg-white px-4 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20',
            'border-red-400' => $errors->has($name),
            'border-border' => ! $errors->has($name),
        ]) }}>
    @error($name) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>