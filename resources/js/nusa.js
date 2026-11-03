/*
 * Laravel Nusa — address cascade for Alpine.
 *
 * Loaded via @nusaAssets. Registers two Alpine components:
 *
 *   nusaSelect  one region level; cascades to any element whose `parent-id`
 *               points at it. No wiring means no cascade and no error.
 *   nusaPostal  the form: resolves a postal code and fills the levels below.
 */
document.addEventListener('alpine:init', () => {
    if (!window.Alpine || window.nusaRegistered) {
        return
    }

    window.nusaRegistered = true

    const CHILD = {
        province: 'regency',
        regency: 'district',
        district: 'village',
    }

    const PATHS = {
        province: (code) => `provinces/${code}/regencies`,
        regency: (code) => `regencies/${code}/districts`,
        district: (code) => `districts/${code}/villages`,
    }

    const unique = (rows, key) => [...new Set(rows.map((row) => row[key]))]

    const fetchRows = async (base, path) => {
        const response = await window.fetch(`${base}/${path}${path.includes('?') ? '&' : '?'}per_page=150`, {
            headers: { Accept: 'application/json' },
        })

        return response.ok ? ((await response.json()).data ?? []) : []
    }

    const render = (rows) => rows
        .map((row) => `<option value="${row.code}"${row.postal_code ? ` data-postal="${row.postal_code}"` : ''}>${row.name}</option>`)
        .join('')

    window.Alpine.data('nusaSelect', (base) => ({
        base,

        init() {
            this.hydrate()
        },

        /**
         * A standalone select has no `parent` prop, so the server cannot know its
         * options. When its parent already holds a value — a prefilled page — load
         * them here instead of leaving the field disabled.
         */
        async hydrate() {
            const parentId = this.$el.dataset.parentId

            if (!parentId) {
                return
            }

            const parent = document.getElementById(parentId)

            if (parent === null) {
                console.error(`[laravel-nusa] select#${this.$el.id} points at a missing parent-id "${parentId}".`)

                return
            }

            if (!this.$el.disabled || !parent.value || !this.parentLevel) {
                return
            }

            const rows = await fetchRows(this.base, PATHS[this.parentLevel](parent.value))

            this.fill(rows, null)

            // A deeper level may have been waiting on this one.
            this.children().forEach((element) => this.data(element)?.hydrate())
        },

        get level() {
            return this.$el.dataset.level || ''
        },

        get childLevel() {
            return CHILD[this.level] || null
        },

        get parentLevel() {
            return {
                regency: 'province',
                district: 'regency',
                village: 'district',
            }[this.level] || null
        },

        children() {
            if (!this.$el.id) {
                return []
            }

            return Array.from(document.querySelectorAll(`[data-parent-id="${CSS.escape(this.$el.id)}"]`))
        },

        data(element) {
            return window.Alpine.$data(element)
        },

        fill(rows, value) {
            const el = this.$el
            const placeholder = el.dataset.placeholder || ''

            el.innerHTML = `<option value="">${placeholder}</option>` + render(rows)
            el.value = value || ''
            el.disabled = rows.length === 0

            if (el.disabled) {
                el.setAttribute('aria-describedby', `${el.id}-message`)
            } else {
                el.removeAttribute('aria-describedby')
            }

            const message = document.getElementById(`${el.id}-message`)

            if (message) {
                message.hidden = !el.disabled
            }
        },

        reset() {
            this.fill([], null)
            this.children().forEach((element) => this.data(element)?.reset())
        },

        async onChange() {
            const code = this.$el.value
            const children = this.children()

            children.forEach((element) => this.data(element)?.reset())

            this.$dispatch('nusa-changed', {
                level: this.level,
                code,
                postal: this.$el.selectedOptions[0]?.dataset.postal ?? null,
            })

            if (!code || children.length === 0 || !this.childLevel) {
                return
            }

            const rows = await fetchRows(this.base, PATHS[this.level](code))

            this.data(children[0])?.fill(rows, null)
        },
    }))

    window.Alpine.data('nusaPostal', (base, targetId = null) => ({
        base,
        targetId,
        error: '',
        listener: null,
        // Monotonic: an object token would come back from Alpine's reactive proxy as a
        // different identity, so every comparison would look stale.
        sequence: 0,

        init() {
            const root = this.root()

            if (root) {
                this.listener = (event) => this.onChanged(event.detail)
                root.addEventListener('nusa-changed', this.listener)
            }
        },

        destroy() {
            this.root()?.removeEventListener('nusa-changed', this.listener)
        },

        /**
         * The container holding the region selects: `for` when given, otherwise the
         * nearest ancestor the form marks.
         */
        root() {
            if (this.targetId) {
                return document.getElementById(this.targetId)
            }

            return this.$el.closest('[data-nusa-address]')
        },

        selectEl(level) {
            return this.root()?.querySelector(`select[data-level="${level}"]`) ?? null
        },

        select(level) {
            const element = this.selectEl(level)

            return element ? window.Alpine.$data(element) : null
        },

        postal() {
            return this.$el.querySelector('[data-nusa-postal]')
        },

        onChanged(detail) {
            const input = this.postal()

            // A region change wins over a postal lookup still in flight.
            this.sequence++
            this.error = ''

            if (!input) {
                return
            }

            input.value = detail.level === 'village' ? (detail.postal || '') : ''
            input.removeAttribute('aria-invalid')
        },

        async resolve(event) {
            const input = event.target
            const value = input.value.trim()

            this.error = ''
            input.removeAttribute('aria-invalid')

            // Only react to a complete code; anything shorter is still being typed.
            if (!/^\d{5}$/.test(value)) {
                return
            }

            const token = ++this.sequence
            const rows = await fetchRows(this.base, `villages?postal_code=${value}`)

            if (this.sequence !== token) {
                return
            }

            if (rows.length === 0) {
                input.setAttribute('aria-invalid', 'true')
                this.error = input.dataset.error || ''
                this.clear()

                return
            }

            await this.apply(rows, token)
        },

        clear() {
            const province = this.selectEl('province')

            if (province) {
                province.value = ''
            }

            this.select('regency')?.reset()
        },

        /**
         * Fill down to the deepest level the postal code determines uniquely.
         */
        async apply(rows, token) {
            const provinces = unique(rows, 'province_code')

            if (provinces.length !== 1) {
                this.clear()

                return
            }

            const province = this.selectEl('province')

            if (!province) {
                return
            }

            province.value = provinces[0]
            this.select('regency')?.reset()

            const regencies = unique(rows, 'regency_code')
            const regencyRows = await fetchRows(this.base, PATHS.province(provinces[0]))

            if (this.sequence !== token) {
                return
            }

            this.select('regency')?.fill(regencyRows, regencies.length === 1 ? regencies[0] : null)

            if (regencies.length !== 1) {
                return
            }

            const districts = unique(rows, 'district_code')
            const districtRows = await fetchRows(this.base, PATHS.regency(regencies[0]))

            if (this.sequence !== token) {
                return
            }

            this.select('district')?.fill(districtRows, districts.length === 1 ? districts[0] : null)

            if (districts.length !== 1) {
                return
            }

            const villages = unique(rows, 'code')
            const villageRows = await fetchRows(this.base, PATHS.district(districts[0]))

            if (this.sequence !== token) {
                return
            }

            this.select('village')?.fill(villageRows, villages.length === 1 ? villages[0] : null)

            if (villages.length === 1) {
                const input = this.postal()
                const option = this.selectEl('village')?.selectedOptions[0]

                if (input) {
                    input.value = option?.dataset.postal || ''
                }
            }
        },
    }))
})
