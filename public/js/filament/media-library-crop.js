/* Cropper.js 1.5.11 matches the original Voyager media manager. */
window.mediaLibraryCrop = function (wire) {
    // Keep the library instance outside Alpine's reactive proxy.
    let editor = null;
    let timer = null;
    let stopped = false;
    return {
        crop: wire.entangle('libraryCrop'),
        init() { this.scheduleEditor(); },
        scheduleEditor() {
            clearTimeout(timer);
            let attempts = 0;
            const mount = () => {
                if (stopped || editor) return;
                const image = this.$refs.image;
                if (!image || image.getBoundingClientRect().width === 0) {
                    if (++attempts < 30) timer = setTimeout(mount, 50);
                    return;
                }
                const component = this;
                editor = new window.Cropper(image, {
                    ready() { component.setRegion(); },
                    crop(event) {
                        component.crop = Object.fromEntries(
                            ['x', 'y', 'width', 'height'].map(key => [key, Math.round(event.detail[key])])
                        );
                    },
                });
            };
            this.$nextTick(mount);
        },
        setRegion() {
            if (!editor) return;
            const region = Object.fromEntries(
                ['x', 'y', 'width', 'height'].map(key => [key, Number(this.crop[key])])
            );
            if (Object.values(region).every(Number.isFinite)) editor.setData(region);
        },
        destroy() {
            stopped = true;
            clearTimeout(timer);
            if (editor) editor.destroy();
            editor = null;
        },
    };
};
