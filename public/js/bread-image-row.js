export default function () {
    const { Node } = window.FilamentRichEditor.tiptap.core;
    const { DOMSerializer, DOMParser: SchemaParser } = window.FilamentRichEditor.tiptap.pmModel;
    const { Plugin } = window.FilamentRichEditor.tiptap.pmState;
    return Node.create({
        name: 'breadImageRow', priority: 1200, group: 'block', content: 'image+', defining: true,
        parseHTML() { return [{ tag: 'p.image-row', priority: 1100 }]; },
        renderHTML() { return ['p', { class: 'image-row' }, 0]; },
        addProseMirrorPlugins() {
            return [new Plugin({ props: { handlePaste: (view, event) => {
                const html = event.clipboardData?.getData('text/html');
                if (!html || event.clipboardData?.files?.length) return false;
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                const images = [...wrapper.querySelectorAll('img')];
                if (!images.length || images.some(image => !/^(?:https?:\/\/|\/(?!\/))/i.test(image.getAttribute('src') ?? ''))) return false;
                let normalizedHtml = wrapper.innerHTML;
                view.someProp('transformPastedHTML', transform => { normalizedHtml = transform(normalizedHtml); });
                wrapper.innerHTML = normalizedHtml;
                // Filament's upload paste handler wraps every image in a paragraph,
                // which splits inline paragraphs and figure/image-row. Parse URL-only HTML directly.
                const slice = SchemaParser.fromSchema(view.state.schema).parseSlice(wrapper, { preserveWhitespace: true });
                event.preventDefault();
                view.dispatch(view.state.tr.replaceSelection(slice).scrollIntoView());
                return true;
            } } })];
        },
        addCommands() {
            return {
                applyBreadImageProperties: (attributes, caption, captionText, constrain) => ({ state, commands, view }) => {
                    const { selection } = state;
                    let image = selection.node?.type.name === 'image' ? selection.node : null;
                    let figure = null;
                    for (let depth = selection.$from.depth; depth > 0; depth--) {
                        if (selection.$from.node(depth).type.name === 'breadImageFigure') {
                            figure = { node: selection.$from.node(depth), from: selection.$from.before(depth) };
                            break;
                        }
                    }
                    if (!image && figure) image = figure.node.firstChild;
                    const previous = image?.attrs;
                    const attrs = { ...previous, ...attributes };
                    if (previous && previous.src !== attrs.src) attrs.id = null;
                    // Preserve the original ratio when only one numeric dimension changes.
                    if (constrain && previous) {
                        const numeric = value => /^\d+(?:\.\d+)?(?:px)?$/.test(String(value ?? '')) ? parseFloat(value) : null;
                        const dom = view?.nodeDOM(selection.from);
                        const domImage = dom?.tagName === 'IMG' ? dom : dom?.querySelector?.('img');
                        const w = numeric(previous.width) ?? domImage?.naturalWidth, h = numeric(previous.height) ?? domImage?.naturalHeight;
                        if (w > 0 && h > 0) {
                            if ((attrs.width ?? null) !== (previous.width ?? null) && (attrs.height ?? null) === (previous.height ?? null) && numeric(attrs.width) !== null) attrs.height = String(Math.round(numeric(attrs.width) * h / w));
                            else if ((attrs.height ?? null) !== (previous.height ?? null) && (attrs.width ?? null) === (previous.width ?? null) && numeric(attrs.height) !== null) attrs.width = String(Math.round(numeric(attrs.height) * w / h));
                        }
                    }
                    if (!image) return commands.insertContent(caption ? {
                        type: 'breadImageFigure', content: [{ type: 'image', attrs }, { type: 'breadImageCaption', content: captionText ? [{ type: 'text', text: captionText }] : [] }],
                    } : { type: 'image', attrs });
                    const captionContent = figure && figure.node.lastChild.textContent === captionText
                        ? figure.node.lastChild.toJSON().content ?? []
                        : captionText ? [{ type: 'text', text: captionText }] : [];
                    const replacement = caption ? {
                        type: 'breadImageFigure', content: [{ type: 'image', attrs, marks: image.toJSON?.().marks ?? [] }, { type: 'breadImageCaption', content: captionContent }],
                    } : { type: 'image', attrs, marks: image.toJSON?.().marks ?? [] };
                    if (figure) return commands.insertContentAt({ from: figure.from, to: figure.from + figure.node.nodeSize }, caption ? replacement : { type: 'paragraph', content: [replacement] });
                    if (!caption) return commands.updateAttributes('image', attrs);
                    return commands.insertContentAt({ from: selection.from, to: selection.to }, replacement);
                },
                mergeBreadImageRow: () => ({ state, commands, dispatch }) => {
                    const { selection } = state;
                    const reject = message => { if (dispatch) window.alert(message); return false; };
                    if (selection.empty) return reject('Сначала выделите блок(и) / картинки!');
                    const parentRow = position => {
                        for (let depth = position.depth; depth > 0; depth--) {
                            if (position.node(depth).type.name === this.name) {
                                return { from: position.before(depth) + 1, to: position.before(depth) + position.node(depth).nodeSize - 1 };
                            }
                        }
                        return null;
                    };
                    const start = parentRow(selection.$from), end = parentRow(selection.$to);
                    if (start && end && start.from === end.from && (selection.from > start.from || selection.to < start.to)) {
                        return reject('Выделен только фрагмент <p class="image-row">. Операция отменена.');
                    }
                    const wrapper = document.createElement('div');
                    wrapper.appendChild(DOMSerializer.fromSchema(state.schema).serializeFragment(selection.content().content));
                    const images = [];
                    // Match Voyager: images from existing rows precede standalone images.
                    wrapper.querySelectorAll('p.image-row').forEach(row => {
                        row.querySelectorAll('img').forEach(image => images.push(image.outerHTML));
                        row.remove();
                    });
                    wrapper.querySelectorAll('img').forEach(image => { images.push(image.outerHTML); image.remove(); });
                    if (!images.length) return reject('Не найдено изображений для объединения.');
                    // Keep caption text when its image moves into the row. An empty
                    // figure would otherwise make the schema create a blank image.
                    wrapper.querySelectorAll('figure.image').forEach(figure => {
                        const caption = figure.querySelector('figcaption');
                        if (!caption) { figure.remove(); return; }
                        const paragraph = document.createElement('p');
                        paragraph.innerHTML = caption.innerHTML;
                        figure.replaceWith(paragraph);
                    });
                    return commands.insertContentAt({ from: selection.from, to: selection.to },
                        wrapper.innerHTML + '<p class="image-row">' + images.join('') + '</p>');
                },
            };
        },
    });
}
