export default function () {
    const { Extension } = window.FilamentRichEditor.tiptap.core;
    const indent = value => /^\d{1,6}px$/.test(value ?? '') && parseInt(value, 10) % 40 === 0 ? parseInt(value, 10) : 0;
    return Extension.create({
        name: 'breadBlockStyle',
        addGlobalAttributes() {
            return [{ types: ['paragraph', 'heading', 'codeBlock'], attributes: {
                breadIndent: { default: 0, parseHTML: el => indent(el.style.paddingLeft),
                    renderHTML: attrs => attrs.breadIndent > 0 && indent(attrs.breadIndent + 'px') === attrs.breadIndent
                        ? { style: 'padding-left: ' + attrs.breadIndent + 'px' } : {} },
            } }];
        },
        addCommands() {
            const change = delta => ({ editor, state, tr, dispatch, commands }) => {
                if (editor.isActive('listItem')) return delta > 0 ? commands.sinkListItem('listItem') : commands.liftListItem('listItem');
                let changed = false;
                state.doc.nodesBetween(state.selection.from, state.selection.to, (node, position) => {
                    if (!['paragraph', 'heading', 'codeBlock'].includes(node.type.name)) return;
                    const before = Number(node.attrs.breadIndent) || 0;
                    const after = Math.max(0, before + delta);
                    if (after > 999960 || before === after) return;
                    if (dispatch) tr.setNodeMarkup(position, undefined, { ...node.attrs, breadIndent: after });
                    changed = true;
                });
                return changed;
            };
            return {
                increaseBreadIndent: () => change(40),
                decreaseBreadIndent: () => change(-40),
                setBreadFormat: value => ({ commands }) => {
                    if (/^h[1-6]$/.test(value)) return commands.setHeading({ level: Number(value[1]) });
                    if (value === 'pre') return commands.setNode('codeBlock');
                    return value === 'p' && commands.setParagraph();
                },
            };
        },
    });
}
