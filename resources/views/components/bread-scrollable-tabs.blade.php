<div style="min-width: 0; max-width: 100%;"
    x-data="{
        nav: null,
        pointer: null,
        startX: 0,
        startScroll: 0,
        dragged: false,
        begin(event) {
            this.end();
            if (event.pointerType !== 'mouse' || event.button !== 0) return;
            this.dragged = false;
            const nav = event.target.closest('.viar-bread-tabs > .fi-tabs');
            if (!nav || nav.scrollWidth <= nav.clientWidth) return;
            this.nav = nav;
            this.pointer = event.pointerId;
            this.startX = event.clientX;
            this.startScroll = nav.scrollLeft;
        },
        move(event) {
            if (!this.nav || event.pointerId !== this.pointer) return;
            if (!(event.buttons & 1)) { this.end(); return; }
            const distance = event.clientX - this.startX;
            if (!this.dragged && Math.abs(distance) < 6) return;
            if (!this.dragged) {
                this.dragged = true;
                this.nav.setPointerCapture(event.pointerId);
                this.nav.classList.add('viar-tabs-dragging');
            }
            event.preventDefault();
            this.nav.scrollLeft = this.startScroll - distance;
        },
        end() {
            if (this.nav) {
                this.nav.classList.remove('viar-tabs-dragging');
                if (this.nav.hasPointerCapture(this.pointer)) this.nav.releasePointerCapture(this.pointer);
            }
            this.nav = null;
            this.pointer = null;
        },
        clicked(event) {
            if (!this.dragged || event.detail === 0 || !event.target.closest('.viar-bread-tabs > .fi-tabs')) return;
            event.preventDefault();
            event.stopImmediatePropagation();
        },
        destroy() { this.end(); }
    }"
    x-on:pointerdown="begin($event)"
    x-on:pointermove="move($event)"
    x-on:pointerup="end()"
    x-on:pointercancel="end()"
    x-on:lostpointercapture="end()"
    x-on:click.capture="clicked($event)"
>
    {{ $slot }}
</div>
