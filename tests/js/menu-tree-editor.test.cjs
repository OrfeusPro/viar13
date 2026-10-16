const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function fixture(result = true, editable = true) {
    const instances = [];
    const calls = [];
    const list = { children: [], closest() { return this.owner || null; } };
    function item(id, parentList) {
        const node = { id, parentElement: parentList, hasAttribute: () => true, getAttribute: () => String(id),
            closest() { return this; }, contains(target) { return target === this || target === this.list; },
            querySelector() { return this.list; } };
        node.list = { children: [], owner: node, closest() { return node; } };
        parentList.children.push(node);
        return node;
    }
    const first = item(1, list);
    item(2, first.list);
    item(3, list);
    function lists(root) { return [root, ...root.children.flatMap(node => lists(node.list))]; }
    const root = { querySelector: () => list, querySelectorAll: () => lists(list) };
    const context = { window: { Sortable: class {
        constructor(element, options) { this.options = options; this.element = element; this.destroyed = false; instances.push(this); }
        option(name, value) { this.options[name] = value; }
        destroy() { this.destroyed = true; }
    } } };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/filament/menu-tree-editor.js'), 'utf8'), context);
    const wire = { async reorderItems(...args) { calls.push(args); return result; }, async $refresh() { calls.push('refresh'); } };
    const state = context.window.menuTreeEditor(wire, 7, editable);
    state.$root = root;
    state.$nextTick = callback => callback();
    state.init();
    return { state, instances, calls, root, list, first, item };
}

test('nested order is serialized in DOM order and saved with its menu id', async () => {
    const f = fixture();
    assert.equal(f.instances.length, 4);
    assert.equal(f.instances[0].options.group, 'menu-7');
    assert.equal(f.instances[0].options.handle, '.menu-drag-handle');
    assert.deepEqual(f.calls, []);
    f.list.children.reverse();
    await f.state.save();
    assert.equal(f.calls[0][0], 7);
    assert.equal(JSON.stringify(f.calls[0][1]), JSON.stringify([{ id: 3, children: [] }, { id: 1, children: [{ id: 2, children: [] }] }]));
    assert.equal(f.state.saving, false);
    assert.equal(f.state.error, '');
});

test('a rejected order refreshes server DOM and recreates drag instances', async () => {
    const f = fixture(null);
    await f.state.save();
    assert.equal(f.calls[1], 'refresh');
    assert.ok(f.state.error);
    assert.equal(f.instances.length, 8);
    assert.ok(f.instances.slice(0, 4).every(instance => instance.destroyed));
    assert.ok(f.instances.slice(4).every(instance => !instance.destroyed));
    f.state.destroy();
    assert.ok(f.instances.every(instance => instance.destroyed));
});

test('read-only trees install no drag handlers; dragging into itself is rejected', () => {
    const readOnly = fixture(true, false);
    assert.equal(readOnly.instances.length, 0);
    const f = fixture();
    assert.equal(f.instances[0].options.onMove({ dragged: f.first, to: f.first.list }), false);
});

test('moving a leaf to level five is allowed and to level six is rejected', () => {
    const f = fixture();
    const third = f.item(4, f.first.list.children[0].list);
    const fourth = f.item(5, third.list);
    const fifth = f.item(6, fourth.list);
    const leaf = f.list.children[1];
    const move = f.instances[0].options.onMove;
    assert.equal(move({ dragged: leaf, to: fourth.list }), true);
    assert.equal(move({ dragged: leaf, to: fifth.list }), false);
});
