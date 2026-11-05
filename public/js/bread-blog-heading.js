// Filament's default headings are h2/h3. The original blog offers h1 through h6.
export default function () {
    const { Node, mergeAttributes } = window.FilamentRichEditor.tiptap.core;
    return Node.create({
        name: 'heading', content: 'inline*', group: 'block', defining: true,
        addAttributes() { return { level: { default: 1, rendered: false } }; },
        parseHTML() { return [1, 2, 3, 4, 5, 6].map(level => ({ tag: 'h' + level, attrs: { level } })); },
        renderHTML({ node, HTMLAttributes }) { return ['h' + ([1, 2, 3, 4, 5, 6].includes(node.attrs.level) ? node.attrs.level : 1), mergeAttributes(HTMLAttributes), 0]; },
        addCommands() {
            return {
                setHeading: attrs => ({ commands }) => [1, 2, 3, 4, 5, 6].includes(attrs.level) && commands.setNode('heading', attrs),
                toggleHeading: attrs => ({ commands }) => [1, 2, 3, 4, 5, 6].includes(attrs.level) && commands.toggleNode('heading', 'paragraph', attrs),
            };
        },
    });
}
