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
console.log('PASS: 15 assertions; mobile default, drag/reload, independent positions, resize/clamp, click and sound controls');
