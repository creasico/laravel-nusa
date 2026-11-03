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

    /**
     * The server's own explanation when it sends one: Laravel answers a 500 with
     * `{"message":"Server Error"}` and a throttled request with `{"message":"Too Many
     * Attempts."}`. Anything that is not JSON — a fatal, a proxy error — falls back.
     */
    const failureMessage = async (response, fallback) => {
        try {
            const payload = await response.json()

            if (payload !== null && typeof payload.message === 'string' && payload.message !== '') {
                return payload.message
            }
        } catch {
            // Not JSON, so there is nothing in the body worth showing.
        }

        return fallback
    }

    /**
     * `{ rows: [] }` means the request succeeded and matched nothing; `{ error }` means it
     * failed. A caller must not confuse the two.
     */
    const fetchRows = async (base, path, fallback = '') => {
        try {
            const response = await window.fetch(`${base}/${path}${path.includes('?') ? '&' : '?'}per_page=150`, {
                headers: { Accept: 'application/json' },
            })

            if (!response.ok) {
                return { error: await failureMessage(response, fallback) }
            }

            return { rows: (await response.json()).data ?? [] }
        } catch {
            return { error: fallback }
        }
    }

    const render = (rows) => rows
        .map((row) => `<option value="${row.code}"${row.postal_code ? ` data-postal="${row.postal_code}"` : ''}>${row.name}</option>`)
        .join('')

    window.Alpine.data('nusaSelect', (base, initialDisabled = false, hint = '') => ({
        base,
        disabled: initialDisabled,
        hint,
        error: '',
        // Bumped whenever this select's own options are replaced, so a response that
        // belongs to a previous parent can be recognised and dropped.
        sequence: 0,

        init() {
            this.hydrate()
        },

        /**
         * A standalone select has no `parent` prop, so the server cannot know its
         * options. When its parent already holds a value — a prefilled page — load
         * them here instead of leaving the field disabled.
         */
        async hydrate() {
            const parentId = this.el.dataset.parentId

            if (!parentId) {
                return
            }

            const parent = document.getElementById(parentId)

            if (parent === null) {
                console.error(`[laravel-nusa] select#${this.el.id} points at a missing parent-id "${parentId}".`)

                return
            }

            if (!this.disabled || !parent.value || !this.parentLevel) {
                return
            }

            const token = ++this.sequence
            const value = parent.value
            const result = await fetchRows(this.base, PATHS[this.parentLevel](value))

            if (this.sequence !== token || parent.value !== value) {
                return
            }

            if (result.error !== undefined) {
                this.error = result.error

                return
            }

            this.fill(result.rows, null)

            // A deeper level may have been waiting on this one.
            this.children().forEach((element) => this.data(element)?.hydrate())
        },

        get el() {
            return this.$refs.control
        },

        get level() {
            return this.el.dataset.level || ''
        },

        /**
         * What the field says about itself: the hint while it waits for a parent, or the
         * server's explanation when loading failed.
         */
        get message() {
            if (this.error !== '') {
                return this.error
            }

            return this.disabled ? this.hint : ''
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
            if (!this.el.id) {
                return []
            }

            return Array.from(document.querySelectorAll(`[data-parent-id="${CSS.escape(this.el.id)}"]`))
        },

        data(element) {
            return window.Alpine.$data(element)
        },

        fail(message) {
            this.error = message
        },

        /**
         * Drop whatever this select is loading: another control now owns its value.
         */
        invalidate() {
            this.sequence++
        },

        fill(rows, value) {
            const el = this.el
            const placeholder = el.dataset.placeholder || ''

            el.innerHTML = `<option value="">${placeholder}</option>` + render(rows)
            el.value = value || ''
            this.error = ''

            // Everything the field shows about itself — disabled, describedby, message —
            // follows from this one flag.
            this.disabled = rows.length === 0
        },

        reset() {
            this.sequence++
            this.fill([], null)
            this.error = ''
            this.children().forEach((element) => this.data(element)?.reset())
        },

        async onChange() {
            const code = this.el.value
            const children = this.children()

            children.forEach((element) => this.data(element)?.reset())

            this.$dispatch('nusa-changed', {
                level: this.level,
                code,
                postal: this.el.selectedOptions[0]?.dataset.postal ?? null,
            })

            if (!code || children.length === 0 || !this.childLevel) {
                return
            }

            const token = ++this.sequence
            const result = await fetchRows(this.base, PATHS[this.level](code))

            // A later change on this select owns the result; this one is history.
            if (this.sequence !== token) {
                return
            }

            if (result.error !== undefined) {
                children.forEach((element) => this.data(element)?.fail(result.error))

                return
            }

            // A parent may feed more than one child; `reset()` already treats them
            // as a set, so filling does the same.
            children.forEach((element) => this.data(element)?.fill(result.rows, null))
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

            // Any edit invalidates a lookup in flight, including one that just lost a digit.
            const token = ++this.sequence

            this.error = ''
            input.removeAttribute('aria-invalid')

            // Only react to a complete code; anything shorter is still being typed.
            if (!/^\d{5}$/.test(value)) {
                return
            }

            const result = await fetchRows(this.base, `villages?postal_code=${value}`, this.failed())

            if (this.sequence !== token) {
                return
            }

            // A failed request is not an unknown code: keep what the user had.
            if (result.error !== undefined) {
                this.error = result.error

                return
            }

            const rows = result.rows

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

        failed() {
            return this.postal()?.dataset.failed || ''
        },

        /**
         * Fetch one level, or explain why it could not be fetched.
         *
         * Returns `null` both when the request failed and when a newer lookup owns the
         * result, without touching the form in either case.
         */
        async rowsFor(path, token) {
            const result = await fetchRows(this.base, path, this.failed())

            if (this.sequence !== token) {
                return null
            }

            if (result.error !== undefined) {
                this.error = result.error

                return null
            }

            return result.rows
        },

        /**
         * Fill down to the deepest level the postal code determines uniquely.
         *
         * Every level is fetched before anything is written: a failure halfway through
         * must not leave the form with a new province and no regency.
         */
        async apply(rows, token) {
            const provinces = unique(rows, 'province_code')

            if (provinces.length !== 1) {
                this.clear()

                return
            }

            const regencies = unique(rows, 'regency_code')
            const regencyRows = await this.rowsFor(PATHS.province(provinces[0]), token)

            if (regencyRows === null) {
                return
            }

            const districts = regencies.length === 1 ? unique(rows, 'district_code') : []
            const districtRows = districts.length > 0
                ? await this.rowsFor(PATHS.regency(regencies[0]), token)
                : null

            if (districts.length > 0 && districtRows === null) {
                return
            }

            const villages = districts.length === 1 ? unique(rows, 'code') : []
            const villageRows = villages.length > 0 || districts.length === 1
                ? await this.rowsFor(PATHS.district(districts[0]), token)
                : null

            if (districts.length === 1 && villageRows === null) {
                return
            }

            const province = this.selectEl('province')

            if (!province) {
                return
            }

            // Anything the province was already loading belongs to the previous value.
            this.select('province')?.invalidate()

            province.value = provinces[0]
            this.select('regency')?.reset()
            this.select('regency')?.fill(regencyRows, regencies.length === 1 ? regencies[0] : null)

            if (regencies.length !== 1) {
                return
            }

            this.select('district')?.fill(districtRows, districts.length === 1 ? districts[0] : null)

            if (districts.length !== 1) {
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
