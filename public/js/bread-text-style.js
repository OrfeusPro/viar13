// Uses Filament's bundled TipTap instance; no separate ProseMirror runtime.
export default function () {
    const { Mark } = window.FilamentRichEditor.tiptap.core;
    const sizes = ['8pt', '10pt', '12pt', '14pt', '18pt', '24pt', '36pt'];
    const color = value => {
        if (typeof value !== 'string') return null;
        value = value.trim().toLowerCase();
        if (/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/.test(value)) return value;
        const rgb = value.match(/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/);
        return rgb && rgb.slice(1).every(x => Number(x) <= 255)
            ? '#' + rgb.slice(1).map(x => Number(x).toString(16).padStart(2, '0')).join('') : null;
    };
    return Mark.create({
        name: 'breadTextStyle',
        addAttributes() {
            return {
                fontSize: { default: null, parseHTML: el => sizes.includes(el.style.fontSize) ? el.style.fontSize : null, rendered: false },
                backgroundColor: { default: null, parseHTML: el => color(el.style.backgroundColor), rendered: false },
            };
        },
        parseHTML() {
            return [{ tag: 'span', getAttrs: el => sizes.includes(el.style.fontSize) || color(el.style.backgroundColor) ? null : false }];
        },
        renderHTML({ mark }) {
            const style = [];
            if (sizes.includes(mark.attrs.fontSize)) style.push('font-size: ' + mark.attrs.fontSize);
            if (color(mark.attrs.backgroundColor)) style.push('background-color: ' + color(mark.attrs.backgroundColor));
            return ['span', style.length ? { style: style.join('; ') } : {}, 0];
        },
        addCommands() {
            const set = (key, value) => ({ commands }) => commands.setMark(this.name, { [key]: value });
            return {
                setBreadFontSize: value => set('fontSize', sizes.includes(value) ? value : null),
                setBreadBackgroundColor: value => set('backgroundColor', color(value)),
            };
        },
    });
}
