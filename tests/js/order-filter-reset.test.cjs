const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/vendor/filament-tables/components/filters.blade.php'), 'utf8');
const state = vm.runInNewContext(`(${blade.match(/x-data="([\s\S]*?)"/)[1]})`);

test('empty filter forms and whitespace have nothing to reset', () => {
    for (const value of [null, undefined, {}, { status: { value: null }, price: { value: '' } }, { customer: { value: '   ' } }]) {
        assert.equal(state.hasFilterValue(value), false);
    }
});

test('zero price and both boolean choices are real filter values', () => {
    for (const value of [0, '0', false, true]) {
        assert.equal(state.hasFilterValue({ filter: { value } }), true);
    }
});

test('draft and applied states independently keep reset available', () => {
    const visible = (applied, draft) => state.hasFilterValue(applied) || state.hasFilterValue(draft);
    assert.equal(visible({}, { created_at: { from: '2026-10-23', until: null } }), true);
    assert.equal(visible({ status: { value: 'watching' } }, { status: { value: '' } }), true);
    assert.equal(visible({ status: { value: null } }, { status: { value: null } }), false);
});
