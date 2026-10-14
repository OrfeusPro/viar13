const { readFileSync } = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const template = readFileSync('resources/views/filament/components/sa-inbox-badge.blade.php', 'utf8');
const expression = template.match(/x-data="([\s\S]*?)"\s+x-bind:style/)[1].replace('@js($count)', '38');

function setup(width, height, stored = {}) {
    const values = new Map(Object.entries(stored));
    const window = { innerWidth: width, innerHeight: height };
    const localStorage = { getItem: key => values.get(key) ?? null, setItem: (key, value) => values.set(key, value) };
    const controller = vm.runInNewContext(`(${expression})`, { window, localStorage });
    controller.$el = { offsetHeight: 43 };
    controller.$nextTick = callback => callback();
    controller.init();
    return { controller, window, values };
}

const mobile = setup(390, 844, { saConversationsFloatingTop: '70' });
assert.equal(mobile.controller.top, 781, 'desktop position must not cover mobile breadcrumbs');
assert.equal(mobile.controller.top + 43, 824, 'default mobile bottom gap');
mobile.controller.begin({ button: 0, pointerId: 1, clientY: 800, target: { closest: () => null } });
let prevented = false;
mobile.controller.move({ pointerId: 1, clientY: 400, preventDefault: () => { prevented = true; } });
mobile.controller.end();
assert.equal(mobile.controller.top, 381);
assert.equal(prevented, true);
assert.equal(mobile.values.get('saConversationsFloatingTopMobile'), '381');
assert.equal(mobile.values.get('saConversationsFloatingTop'), '70');
assert.equal(setup(390, 844, Object.fromEntries(mobile.values)).controller.top, 381, 'mobile reload restores drag');

mobile.window.innerWidth = 1280;
mobile.controller.resize();
assert.equal(mobile.controller.top, 70, 'desktop position restored on breakpoint change');
mobile.window.innerWidth = 390;
mobile.controller.resize();
assert.equal(mobile.controller.top, 381, 'mobile position restored on breakpoint change');
mobile.window.innerHeight = 300;
mobile.controller.resize();
assert.equal(mobile.controller.top, 237, 'rotation/short viewport keeps badge on screen');
assert.equal(setup(390, 844, { saConversationsFloatingTopMobile: '-200' }).controller.top, 64);
assert.equal(setup(390, 844, { saConversationsFloatingTopMobile: 'not-a-number' }).controller.top, 781);

const click = setup(1280, 900);
click.controller.begin({ button: 0, pointerId: 2, clientY: 80, target: { closest: () => null } });
click.controller.move({ pointerId: 2, clientY: 83, preventDefault: () => assert.fail('ordinary click must remain clickable') });
click.controller.end();
assert.equal(click.controller.moved, false);
assert.equal(click.values.size, 0, 'ordinary click does not persist a position');
click.controller.begin({ button: 0, pointerId: 3, clientY: 80, target: { closest: () => ({}) } });
assert.equal(click.controller.pointer, null, 'sound button does not start drag');
// Exercise the actual Alpine handlers with a touch pointer; no browser or network side effects.
const touch = setup(390, 844);
touch.controller.begin({ button: 0, pointerType: 'touch', pointerId: 7, clientY: 800, target: { closest: () => null } });
touch.controller.move({ pointerId: 8, clientY: 200, preventDefault: () => assert.fail('another pointer must be ignored') });
assert.equal(touch.controller.top, 781);
touch.controller.move({ pointerId: 7, clientY: 200, preventDefault() {} });
touch.controller.end();
assert.equal(touch.controller.top, 181);
assert.equal(touch.values.get('saConversationsFloatingTopMobile'), '181');
assert.equal(touch.controller.pointer, null);
const clickExpression = template.match(/x-on:click.capture="([^"]*)"/)[1];
let suppressed = 0;
touch.controller.$event = { detail: 1, preventDefault: () => suppressed++, stopPropagation: () => suppressed++ };
vm.runInNewContext(`with (controller) { ${clickExpression} }`, { controller: touch.controller });
assert.equal(suppressed, 2, 'click after touch drag must not navigate');
assert.equal(touch.controller.moved, false);
vm.runInNewContext(`with (controller) { ${clickExpression} }`, { controller: touch.controller });
assert.equal(suppressed, 2, 'ordinary next click remains usable');
touch.controller.begin({ button: 0, pointerType: 'touch', pointerId: 9, clientY: 200, target: { closest: () => null } });
touch.controller.move({ pointerId: 9, clientY: -1000, preventDefault() {} });
assert.equal(touch.controller.top, 64, 'touch upper clamp');
touch.controller.move({ pointerId: 9, clientY: 3000, preventDefault() {} });
assert.equal(touch.controller.top, 781, 'touch lower clamp');
assert.match(template, /x-on:pointercancel.window="end\(\)"/);
touch.controller.end(); // Same handler used by pointercancel.
const cancelledPosition = touch.controller.top;
touch.controller.move({ pointerId: 9, clientY: 100, preventDefault: () => assert.fail('cancelled gesture moved') });
assert.equal(touch.controller.top, cancelledPosition);
const styles = readFileSync('public/css/filament-admin-workflows.css', 'utf8');
assert.match(styles, /\.viar-sa-global\s*\{[^}]*touch-action:\s*none/);
console.log('PASS: mobile default, mouse/touch handlers, drag/reload, independent positions, resize/clamp, cancel, click suppression and sound controls; physical touch not simulated');
