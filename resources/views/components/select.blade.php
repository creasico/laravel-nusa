<div class="nusa-field" x-data="nusaSelect(@js($base), @js($disabled))">
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
            x-bind:aria-describedby="disabled ? '{{ $id }}-message' : null"
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
        <p id="{{ $id }}-message" role="status" class="mt-1 text-sm text-gray-500" x-bind:hidden="! disabled" @if (! $disabled) hidden @endif>
            {{ $hint }}
        </p>
    @endif
</div>
