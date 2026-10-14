export default function () {
    return window.FilamentRichEditor.tiptap.core.Node.create({
        name: 'breadImageCaption', inline: true, content: 'inline*', defining: true,
        parseHTML() { return [{ tag: 'figcaption' }]; },
        renderHTML() { return ['figcaption', {}, 0]; },
    });
}
