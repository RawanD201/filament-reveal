document.addEventListener('alpine:init', () => {
    Alpine.data('$revealColumn', (columnId, requiresAuth, apiEndpoint, token, translations) => ({
        state: {
            revealed: false,
            authenticated: false,
            loading: false,
            data: null,
            error: null
        },

        config: { columnId, requiresAuth, apiEndpoint, token },
        t: translations || {},

        get revealed() { return this.state.revealed; },
        get authenticated() { return this.state.authenticated; },
        get loading() { return this.state.loading; },
        get data() { return this.state.data; },

        set authenticated(value) {
            this.state.authenticated = value;
            if (value && !this.state.revealed) this.reveal();
        },

        async toggle() {
            if (this.config.requiresAuth && !this.state.authenticated && !this.state.revealed) {
                this.requestAuthentication();
            } else if (!this.state.revealed) {
                await this.reveal();
            } else {
                this.hide();
            }
        },

        requestAuthentication() {
            window.Livewire.dispatch('openAuthModal', {
                columnId: this.config.columnId,
                token: this.config.token
            });
        },

        async reveal() {
            if (this.state.loading) return;
            this.state.loading = true;
            this.state.error = null;
            try {
                const response = await fetch(this.config.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ token: this.config.token }),
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                if (data.success) {
                    this.state.data = data.value;
                    this.state.revealed = true;
                } else {
                    this.state.error = data.message;
                    this.state.data = 'Error: ' + data.message;
                    this.state.revealed = true;
                }
            } catch (error) {
                console.error('Reveal error:', error);
                this.state.error = 'Failed to load';
                this.state.data = 'Error loading data';
                this.state.revealed = true;
            } finally {
                this.state.loading = false;
            }
        },

        hide() { this.state.revealed = false; },

        async copy() {
            if (!this.state.data || this.state.error) return;
            try {
                await navigator.clipboard.writeText(this.state.data);
            } catch (error) {
                console.error('Copy failed:', error);
            }
        },

        getTitle() {
            if (this.config.requiresAuth && !this.state.authenticated && !this.state.revealed) {
                return this.t.authenticate_to_reveal || 'Authenticate to reveal';
            }
            return this.state.loading
                ? (this.t.loading || 'Loading...')
                : (this.t.toggle_visibility || 'Toggle visibility');
        }
    }));
});
