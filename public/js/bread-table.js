export default function () {
    const { Extension } = window.FilamentRichEditor.tiptap.core;
    const { Plugin } = window.FilamentRichEditor.tiptap.pmState;
    return Extension.create({
        name: 'breadTableCompatibility',
        addProseMirrorPlugins() {
            return [new Plugin({ props: { transformPastedHTML: html => {
                if (!html.includes('data-colwidth')) return html;
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                wrapper.querySelectorAll('td[data-colwidth], th[data-colwidth]').forEach(cell => {
                    if (!cell.hasAttribute('colwidth')) cell.setAttribute('colwidth', cell.getAttribute('data-colwidth'));
                    cell.removeAttribute('data-colwidth');
                });
                return wrapper.innerHTML;
            } } })];
        },
    });
}
