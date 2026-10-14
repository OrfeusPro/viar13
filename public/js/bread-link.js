export default function () {
    const { Extension } = window.FilamentRichEditor.tiptap.core;
    return Extension.create({
        name: 'breadLink',
        addStorage() {
            return { getArguments: editor => {
                const selection = editor.state.selection;
                let from = selection.from, to = selection.to;
                if (selection.empty && editor.isActive('link')) {
                    const range = window.FilamentRichEditor.tiptap.core.getMarkRange(selection.$from, editor.schema.marks.link);
                    if (range) { from = range.from; to = range.to; }
                }
                let canEditText = selection.empty || selection.$from.parent === selection.$to.parent;
                editor.state.doc.nodesBetween(from, to, node => {
                    if (node.isLeaf && !node.isText) canEditText = false;
                    if (!selection.empty && node.isText && node.marks.some(mark => mark.type.name !== 'link')) canEditText = false;
                });
                return { ...editor.getAttributes('link'), text: editor.state.doc.textBetween(from, to, ' '), canEditText };
            } };
        },
        addGlobalAttributes() {
            return [{ types: ['link'], attributes: { title: {
                default: null, parseHTML: element => element.getAttribute('title'),
                renderHTML: attributes => attributes.title ? { title: attributes.title } : {},
            } } }];
        },
        addCommands() {
            return { applyBreadLink: (attrs, text, originalText, canEditText = true) => ({ commands, state }) => {
                if (state.selection.empty) commands.extendMarkRange('link');
                if (!attrs.href) return commands.unsetLink();
                // Enforce this from the actual selection as well as the dialog:
                // an image/hard break must never be replaced by display text.
                state.doc?.nodesBetween(state.selection.from, state.selection.to, node => {
                    if (node.isLeaf && !node.isText) canEditText = false;
                    if (!state.selection.empty && node.isText && node.marks.some(mark => mark.type.name !== 'link')) canEditText = false;
                });
                if (!canEditText) return commands.setLink(attrs);
                if (text !== originalText || !originalText) {
                    return commands.insertContent({ type: 'text', text: text || attrs.href, marks: [{ type: 'link', attrs }] });
                }
                return commands.setLink(attrs);
            } };
        },
    });
}
