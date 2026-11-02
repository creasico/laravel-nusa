<div class="nusa-field">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        autocomplete="{{ $autocomplete }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm disabled:bg-gray-100"
        @disabled($disabled)
        @if ($hint) aria-describedby="{{ $id }}-hint" @endif
    >
        <option value="">{{ $placeholder }}</option>

        @foreach ($options as $option)
            <option value="{{ $option['code'] }}" @selected($value === $option['code'])>
                {{ $option['name'] }}
            </option>
        @endforeach
    </select>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
    @endif
</div>
