<div class="nusa-field" x-data="nusaPostal(@js($base), @js($for))">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">
        {{ __('creasico::nusa.postal_code.label') }}
    </label>

    <input
        type="text"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        inputmode="numeric"
        maxlength="5"
        autocomplete="postal-code"
        placeholder="{{ __('creasico::nusa.postal_code.placeholder') }}"
        data-nusa-postal
        data-error="{{ __('creasico::nusa.postal_code.invalid') }}"
        data-failed="{{ __('creasico::nusa.postal_code.failed') }}"
        x-on:input.debounce.400ms="resolve($event)"
        x-bind:aria-describedby="error ? '{{ $id }}-message' : null"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        {{ $attributes }}
    >

    <p id="{{ $id }}-message" role="status" x-text="error" x-bind:hidden="! error" class="mt-1 text-sm text-red-600"></p>
</div>
