const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public/js/bread-image-row.js', 'utf8').replace('export default function', 'function createExtension');
const alerts = [];
const context = vm.createContext({
    window: { alert: message => alerts.push(message), FilamentRichEditor: { tiptap: {
        core: { Node: { create: config => config } }, pmModel: { DOMSerializer: {} }, pmState: {},
    } } },
});
vm.runInContext(source, context);
const extension = vm.runInContext('createExtension()', context);
const merge = extension.addCommands().mergeBreadImageRow();
assert.equal(merge({ state: { selection: { empty: true } }, dispatch: true }), false);
assert.equal(alerts.pop(), 'Сначала выделите блок(и) / картинки!');
const insideRow = {
    depth: 1, before: () => 10,
    node: () => ({ type: { name: 'breadImageRow' }, nodeSize: 4 }),
};
assert.equal(merge({ state: { selection: { empty: false, from: 11, to: 12, $from: insideRow, $to: insideRow } }, dispatch: true }), false);
assert.equal(alerts.pop(), 'Выделен только фрагмент <p class="image-row">. Операция отменена.');
assert.equal(merge({ state: { selection: { empty: true } } }), false);
assert.equal(alerts.length, 0, 'availability checks must not show alerts');
console.log('PASS: actual image-row handler distinguishes empty selection from a partial row; no mutation or alerts during availability checks');
const properties = extension.addCommands().applyBreadImageProperties;
let result;
const commands = {
    updateAttributes: (name, attrs) => { result = { name, attrs }; return true; },
    insertContent: content => { result = content; return true; },
    insertContentAt: (range, content) => { result = { range, content }; return true; },
};
const selected = { node: { type: { name: 'image' }, attrs: { src: '/one.jpg', id: 'kept', width: '200', height: '100' } }, $from: { depth: 0, parent: { type: { name: 'paragraph' } } }, from: 1, to: 2 };
properties({ src: '/one.jpg', width: '180', height: '100' }, false, '', true)({ state: { selection: selected }, commands });
assert.equal(result.attrs.height, '90'); assert.equal(result.attrs.id, 'kept');
properties({ src: '/two.jpg', width: '200', height: '150' }, false, '', true)({ state: { selection: selected }, commands });
assert.equal(result.attrs.width, '300'); assert.equal(result.attrs.id, null);
properties({ src: '/one.jpg', width: '180', height: '100' }, false, '', false)({ state: { selection: selected }, commands });
assert.equal(result.attrs.height, '100');
const withoutDimensions = { ...selected, node: { type: {name: 'image'}, attrs: { src: '/one.jpg' } } };
properties({ src: '/one.jpg', width: '300', height: null }, false, '', true)({ state: {selection: withoutDimensions}, commands, view: { nodeDOM: () => ({ tagName: 'IMG', naturalWidth: 600, naturalHeight: 400 }) } });
assert.equal(result.attrs.height, '200');
properties({ src: '/one.jpg' }, true, 'Caption', false)({ state: { selection: selected }, commands });
assert.equal(result.content.type, 'breadImageFigure'); assert.equal(result.content.content[1].content[0].text, 'Caption');
const figureSelection = { ...selected, $from: { depth: 1, before: () => 0, node: () => ({ type: { name: 'breadImageFigure' }, nodeSize: 12, lastChild: { textContent: 'Caption', toJSON: () => ({content: []}) } }) } };
properties({ src: '/one.jpg' }, false, '', false)({ state: { selection: figureSelection }, commands });
assert.equal(result.content.type, 'paragraph'); assert.equal(result.content.content[0].type, 'image');
assert.equal(result.range.to, 12);
const richCaption = { type: 'breadImageCaption', content: [{ type: 'text', text: 'Bold caption', marks: [{ type: 'bold' }] }] };
const richFigure = { type: { name: 'breadImageFigure' }, nodeSize: 18, firstChild: selected.node, lastChild: { textContent: 'Bold caption', toJSON: () => richCaption } };
const captionSelection = { $from: { depth: 1, before: () => 0, node: () => richFigure } };
properties({ src: '/one.jpg' }, true, 'Bold caption', true)({ state: { selection: captionSelection }, commands });
assert.equal(result.content.content[1].content[0].marks[0].type, 'bold');
properties({ src: '/new.jpg' }, true, 'New', false)({ state: { selection: { $from: { depth: 0 } } }, commands });
assert.equal(result.type, 'breadImageFigure');
console.log('PASS: proportions, unlock, URL attachment ID, caption wrap/unwrap and insertion commands');
const linkedImage = { ...selected.node, toJSON: () => ({marks: [{type: 'link', attrs: {href: '/keep-link'}}]}) };
properties({src: '/one.jpg'}, true, 'Caption', false)({state: {selection: {...selected, node: linkedImage}}, commands});
assert.equal(result.content.content[0].marks[0].attrs.href, '/keep-link');
const linkedFigure = {...richFigure, firstChild: linkedImage};
properties({src: '/one.jpg'}, false, '', false)({state: {selection: {$from: {depth: 1, before: () => 0, node: () => linkedFigure}}}, commands});
assert.equal(result.content.content[0].marks[0].attrs.href, '/keep-link');
console.log('PASS: caption wrap/unwrap preserves the image link');
