(function () {
    window.adminPaymentRequestCopy = function () {
        return {
            copied: false,
            manual: false,
            copying: false,
            async copy(url) {
                if (this.copying) return;
                this.copying = true;
                this.copied = false;
                this.manual = false;
                const previous = document.activeElement;
                try {
                    if (navigator.clipboard?.writeText) {
                        try {
                            await navigator.clipboard.writeText(url);
                            this.copied = true;
                            return;
                        } catch (_) {
                            // Use the same selection based fallback as Voyager.
                        }
                    }
                    const input = document.createElement('textarea');
                    input.value = url;
                    input.readOnly = true;
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    try {
                        input.select();
                        this.copied = Boolean(document.execCommand('copy'));
                    } catch (_) {
                        this.copied = false;
                    } finally {
                        input.remove();
                        previous?.focus?.({ preventScroll: true });
                    }
                    this.manual = !this.copied;
                } finally {
                    this.copying = false;
                }
            },
        };
    };
})();
