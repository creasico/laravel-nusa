<div class="nusa-field" x-data="nusaSelect(@js($base), @js($disabled), @js($hint ?? ''))">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>

    <select
        x-ref="control"
        id="{{ $id }}"
        name="{{ $name }}"
        autocomplete="{{ $autocomplete }}"
        data-level="{{ $level }}"
        data-placeholder="{{ $placeholder }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm disabled:bg-gray-100"
        @if ($parentId) data-parent-id="{{ $parentId }}" @endif
        x-on:change="onChange()"
        x-bind:disabled="disabled"
        @disabled($disabled)
        @if ($hint)
            @if ($disabled) aria-describedby="{{ $id }}-message" @endif
            x-bind:aria-describedby="message ? '{{ $id }}-message' : null"
        @endif
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
        <p
            id="{{ $id }}-message"
            role="status"
            class="mt-1 text-sm"
            x-bind:class="error ? 'text-red-600' : 'text-gray-500'"
            x-bind:hidden="! message"
            x-text="message"
            @if (! $disabled) hidden @endif
        >{{ $disabled ? $hint : '' }}</p>
    @endif
</div>
