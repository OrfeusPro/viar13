export default function () {
    return window.FilamentRichEditor.tiptap.core.Node.create({
        name: 'breadImageFigure', group: 'block', content: 'image breadImageCaption', defining: true,
        parseHTML() { return [{ tag: 'figure.image' }]; },
        renderHTML() { return ['figure', { class: 'image' }, 0]; },
    });
}
