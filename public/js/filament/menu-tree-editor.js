window.menuTreeEditor = function (wire, menu, editable) {
    let instances = [];
    let stopped = false;
    return {
        saving: false, error: '',
        init() {
            if (!editable) return;
            this.$nextTick(() => this.mount());
        },
        mount() {
                if (stopped || !editable) return;
                instances.forEach(instance => instance.destroy());
                instances = [];
                this.$root.querySelectorAll('[data-menu-list]').forEach(list => {
                    instances.push(new window.Sortable(list, {
                        group: 'menu-' + menu,
                        handle: '.menu-drag-handle',
                        draggable: '[data-menu-id]',
                        animation: 150,
                        fallbackOnBody: true,
                        swapThreshold: 0.65,
                        onMove: event => {
                            if (this.saving || event.dragged.contains(event.to)) return false;
                            let depth = 1;
                            let parent = event.to.closest('[data-menu-id]');
                            while (parent) {
                                depth++;
                                parent = parent.parentElement.closest('[data-menu-id]');
                            }
                            const height = node => 1 + Math.max(0, ...Array.from(node.querySelector(':scope > [data-menu-list]').children).map(height));
                            return depth + height(event.dragged) - 1 <= 5;
                        },
                        onEnd: () => this.save(),
                    }));
                });
        },
        serialize(list) {
            return Array.from(list.children).filter(node => node.hasAttribute('data-menu-id')).map(node => ({
                id: Number(node.getAttribute('data-menu-id')),
                children: this.serialize(node.querySelector(':scope > [data-menu-list]')),
            }));
        },
        async save() {
            if (this.saving) return;
            this.saving = true;
            this.error = '';
            instances.forEach(instance => instance.option('disabled', true));
            try {
                const saved = await wire.reorderItems(menu, this.serialize(this.$root.querySelector('[data-menu-list]')));
                if (saved !== true) throw new Error('Order rejected');
            } catch (error) {
                this.error = 'Порядок не сохранён. Дерево обновляется с сервера.';
                await wire.$refresh();
                this.mount();
            } finally {
                this.saving = false;
                instances.forEach(instance => instance.option('disabled', false));
            }
        },
        destroy() { stopped = true; instances.forEach(instance => instance.destroy()); instances = []; },
    };
};
