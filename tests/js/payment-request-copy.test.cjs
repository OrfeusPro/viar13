const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/admin-payment-request-copy.js', 'utf8');

function setup(clipboard, legacy) {
    const state = { appended: [], removed: 0, focused: 0, selected: 0, calls: 0 };
    const context = {
        window: {}, navigator: { clipboard },
        document: {
            activeElement: { focus() { state.focused++; } },
            body: { appendChild(input) { state.appended.push(input); } },
            createElement() { return { style: {}, select() { state.selected++; }, remove() { state.removed++; } }; },
            execCommand(command) { assert.equal(command, 'copy'); state.calls++; return legacy(); },
        },
    };
    vm.runInNewContext(source, context);
    return { component: context.window.adminPaymentRequestCopy(), state };
}

test('modern clipboard success copies exact URL without temporary elements', async () => {
    let copied;
    const { component, state } = setup({ async writeText(url) { copied = url; } }, () => assert.fail('No fallback expected'));
    await component.copy('https://example.test/lv/payment-request/token');
    assert.equal(copied, 'https://example.test/lv/payment-request/token');
    assert.equal(component.copied, true);
    assert.equal(component.manual, false);
    assert.equal(component.copying, false);
    assert.equal(state.appended.length, 0);
});

for (const clipboard of [undefined, { async writeText() { throw new Error('Denied'); } }]) {
    test(`legacy fallback works after ${clipboard ? 'permission rejection' : 'missing clipboard API'}`, async () => {
        const { component, state } = setup(clipboard, () => true);
        await component.copy('https://example.test/link');
        assert.equal(state.appended[0].value, 'https://example.test/link');
        assert.equal(state.selected, 1);
        assert.equal(state.removed, 1);
        assert.equal(state.focused, 1);
        assert.equal(component.copied, true);
        assert.equal(component.manual, false);
    });
}

for (const legacy of [() => false, () => { throw new Error('Denied'); }]) {
    test('total clipboard failure offers manual copy and removes temporary input', async () => {
        const { component, state } = setup(undefined, legacy);
        await component.copy('https://example.test/link');
        assert.equal(component.copied, false);
        assert.equal(component.manual, true);
        assert.equal(component.copying, false);
        assert.equal(state.removed, 1);
        assert.equal(state.focused, 1);
    });
}
