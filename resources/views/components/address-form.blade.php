@php
    $base = url(config('creasi.nusa.routes_prefix', 'nusa'));
@endphp

<div {{ $attributes->merge(['id' => $id, 'class' => 'nusa-address space-y-4']) }} x-data="nusaAddress(@js($base))">
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
            x-on:input.debounce.400ms="resolvePostal($event)"
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
            x-on:change="onParentChange($event)"
        />

        <x-nusa::select
            level="regency"
            :id="$id.'-regency'"
            :name="$fields['regency_code']"
            :value="$codes['regency']"
            :parent="$codes['province']"
            x-on:change="onParentChange($event)"
        />

        <x-nusa::select
            level="district"
            :id="$id.'-district'"
            :name="$fields['district_code']"
            :value="$codes['district']"
            :parent="$codes['regency']"
            x-on:change="onParentChange($event)"
        />

        <x-nusa::select
            level="village"
            :id="$id.'-village'"
            :name="$fields['village_code']"
            :value="$codes['village']"
            :parent="$codes['district']"
            x-on:change="onParentChange($event)"
        />
    </fieldset>
</div>

@verbatim
    <script>
        document.addEventListener('alpine:init', () => {
            if (!window.Alpine || window.nusaAddressRegistered) {
                return
            }

            window.nusaAddressRegistered = true

            const ORDER = ['province', 'regency', 'district', 'village']

            const CHILD = {
                province: 'regency',
                regency: 'district',
                district: 'village',
            }

            const PATH = {
                province: (code) => `provinces/${code}/regencies`,
                regency: (code) => `regencies/${code}/districts`,
                district: (code) => `districts/${code}/villages`,
            }

            const unique = (rows, key) => [...new Set(rows.map((row) => row[key]))]

            window.Alpine.data('nusaAddress', (base) => ({
                base,
                error: '',
                pending: false,

                select(level) {
                    return this.$root.querySelector(`select[data-level="${level}"]`)
                },

                postal() {
                    return this.$root.querySelector('[data-nusa-postal]')
                },

                hint(level) {
                    const el = this.select(level)

                    return el ? document.getElementById(`${el.id}-hint`) : null
                },

                setOptions(level, rows, value) {
                    const el = this.select(level)

                    if (!el) {
                        return
                    }

                    const placeholder = el.dataset.placeholder ?? ''

                    el.innerHTML = `<option value="">${placeholder}</option>` + rows
                        .map((row) => `<option value="${row.code}"${row.postal_code ? ` data-postal="${row.postal_code}"` : ''}>${row.name}</option>`)
                        .join('')

                    el.value = value ?? ''
                    el.disabled = rows.length === 0

                    if (el.disabled) {
                        el.setAttribute('aria-describedby', `${el.id}-hint`)
                    } else {
                        el.removeAttribute('aria-describedby')
                    }

                    const hint = this.hint(level)

                    if (hint) {
                        hint.hidden = !el.disabled
                    }
                },

                clearChildren(level) {
                    ORDER.slice(ORDER.indexOf(level) + 1).forEach((child) => {
                        this.setOptions(child, [], null)
                    })
                },

                resetSelection() {
                    const province = this.select('province')

                    if (province) {
                        province.value = ''
                    }

                    this.clearChildren('province')
                },

                async fetchRows(path) {
                    const response = await window.fetch(`${this.base}/${path}${path.includes('?') ? '&' : '?'}per_page=150`, {
                        headers: { Accept: 'application/json' },
                    })

                    if (!response.ok) {
                        return []
                    }

                    return (await response.json()).data ?? []
                },

                async fillLevel(level, path, value) {
                    const el = this.select(level)

                    if (el) {
                        el.setAttribute('aria-busy', 'true')
                    }

                    try {
                        const rows = await this.fetchRows(path)

                        this.setOptions(level, rows, rows.some((row) => row.code === value) ? value : null)
                    } finally {
                        if (el) {
                            el.removeAttribute('aria-busy')
                        }
                    }
                },

                onParentChange(event) {
                    const level = event.target.dataset.level
                    const code = event.target.value

                    this.error = ''
                    this.postal().value = ''
                    this.clearChildren(level)

                    if (!code) {
                        return
                    }

                    if (level === 'village') {
                        const option = event.target.selectedOptions[0]
                        this.postal().value = option?.dataset.postal ?? ''

                        return
                    }

                    this.fillLevel(CHILD[level], PATH[level](code), null)
                },

                async resolvePostal(event) {
                    const input = event.target
                    const value = input.value.trim()

                    this.error = ''
                    input.removeAttribute('aria-invalid')

                    // Only react to a complete code; anything shorter is still being typed.
                    if (!/^\d{5}$/.test(value)) {
                        return
                    }

                    const rows = await this.fetchRows(`villages?postal_code=${value}`)

                    if (rows.length === 0) {
                        input.setAttribute('aria-invalid', 'true')
                        this.error = input.dataset.error ?? ''
                        this.resetSelection()

                        return
                    }

                    await this.applyPostal(rows)
                },

                /**
                 * Fill down to the deepest level the postal code determines uniquely.
                 */
                async applyPostal(rows) {
                    const [province] = unique(rows, 'province_code')

                    if (unique(rows, 'province_code').length !== 1) {
                        this.resetSelection()

                        return
                    }

                    this.setOptions('province', this.provinceOptions(), province)

                    const regencies = unique(rows, 'regency_code')
                    await this.fillLevel('regency', PATH.province(province), regencies.length === 1 ? regencies[0] : null)

                    if (regencies.length !== 1) {
                        return
                    }

                    const districts = unique(rows, 'district_code')
                    await this.fillLevel('district', PATH.regency(regencies[0]), districts.length === 1 ? districts[0] : null)

                    if (districts.length !== 1) {
                        return
                    }

                    const villages = unique(rows, 'code')
                    await this.fillLevel('village', PATH.district(districts[0]), villages.length === 1 ? villages[0] : null)

                    if (villages.length === 1) {
                        const option = this.select('village')?.selectedOptions[0]
                        this.postal().value = option?.dataset.postal ?? ''
                    }
                },

                provinceOptions() {
                    const el = this.select('province')

                    return el
                        ? [...el.options].filter((option) => option.value !== '').map((option) => ({
                            code: option.value,
                            name: option.textContent.trim(),
                        }))
                        : []
                },
            }))
        })
    </script>
@endverbatim
