<div class="nusa-field">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        autocomplete="{{ $autocomplete }}"
        data-level="{{ $level }}"
        data-placeholder="{{ $placeholder }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm disabled:bg-gray-100"
        @if ($parentId) data-parent-id="{{ $parentId }}" @endif
        x-data="nusaSelect(@js($base))"
        x-on:change="onChange()"
        @disabled($disabled)
        @if ($hint) aria-describedby="{{ $id }}-hint" @endif
        {{ $attributes }}
    >
        <option value="">{{ $placeholder }}</option>

        @foreach ($options as $option)
            <option value="{{ $option['code'] }}" @selected($value === $option['code']) @isset($option['postal']) data-postal="{{ $option['postal'] }}" @endisset>
                {{ $option['name'] }}
            </option>
        @endforeach
    </select>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1 text-sm text-gray-500" @if (! $disabled) hidden @endif>{{ $hint }}</p>
    @endif
</div>
