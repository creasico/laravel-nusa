@php
    $base = url(config('creasi.nusa.routes_prefix', 'nusa'));
@endphp

<div {{ $attributes->merge(['id' => $id, 'class' => 'nusa-address space-y-4']) }} x-data="nusaPostal(@js($base))" x-on:nusa-changed="onChanged($event.detail)">
    <div>
        <label for="{{ $id }}-line" class="block text-sm font-medium text-gray-700">
            {{ __('creasico::nusa.address_line.label') }}
        </label>

        <input
            type="text"
            id="{{ $id }}-line"
            name="{{ $fields['line'] }}"
            value="{{ $line }}"
            autocomplete="address-line1"
            placeholder="{{ __('creasico::nusa.address_line.placeholder') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
    </div>

    <div>
        <label for="{{ $id }}-postal" class="block text-sm font-medium text-gray-700">
            {{ __('creasico::nusa.postal_code.label') }}
        </label>

        <input
            type="text"
            id="{{ $id }}-postal"
            name="{{ $fields['postal_code'] }}"
            value="{{ $postal }}"
            inputmode="numeric"
            maxlength="5"
            autocomplete="postal-code"
            placeholder="{{ __('creasico::nusa.postal_code.placeholder') }}"
            data-nusa-postal
            data-error="{{ __('creasico::nusa.postal_code.invalid') }}"
            x-on:input.debounce.400ms="resolve($event)"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >

        <p role="status" x-text="error" x-show="error" class="mt-1 text-sm text-red-600"></p>
    </div>

    <fieldset class="space-y-4">
        <legend class="block text-sm font-medium text-gray-700">
            {{ __('creasico::nusa.address_line.label') }}
        </legend>

        <x-nusa::select
            level="province"
            :id="$id.'-province'"
            :name="$fields['province_code']"
            :value="$codes['province']"
            :base="$base"
        />

        <x-nusa::select
            level="regency"
            :id="$id.'-regency'"
            :name="$fields['regency_code']"
            :value="$codes['regency']"
            :parent="$codes['province']"
            :parent-id="$id.'-province'"
            :base="$base"
        />

        <x-nusa::select
            level="district"
            :id="$id.'-district'"
            :name="$fields['district_code']"
            :value="$codes['district']"
            :parent="$codes['regency']"
            :parent-id="$id.'-regency'"
            :base="$base"
        />

        <x-nusa::select
            level="village"
            :id="$id.'-village'"
            :name="$fields['village_code']"
            :value="$codes['village']"
            :parent="$codes['district']"
            :parent-id="$id.'-district'"
            :base="$base"
        />
    </fieldset>
</div>
