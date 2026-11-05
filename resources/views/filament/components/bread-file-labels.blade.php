<div data-bread-file-labels="{{ json_encode($labels, JSON_UNESCAPED_UNICODE) }}"
    x-data="{
        observer: null,
        decodedLabels: new WeakMap(),
        refreshLabels() {
            const labels = JSON.parse(this.$el.dataset.breadFileLabels);
            this.$el.querySelectorAll('.filepond--file-info-main, .filepond--file-wrapper legend').forEach(node => {
                if (this.decodedLabels.get(node) === node.textContent) return;
                const label = labels[node.textContent];
                if (label &amp;&amp; label !== node.textContent) {
                    this.decodedLabels.set(node, label);
                    node.textContent = label;
                    if (node.hasAttribute('title')) node.setAttribute('title', label);
                }
            });
        },
        init() {
            this.observer = new MutationObserver(() => this.refreshLabels());
            this.observer.observe(this.$el, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: ['data-bread-file-labels'] });
            this.refreshLabels();
        },
        destroy() { this.observer?.disconnect(); }
    }">
    {!! $content !!}
</div>
