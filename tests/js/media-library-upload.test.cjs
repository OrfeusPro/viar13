const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function fixture(saveResult = true) {
    const context = { window: {} };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/filament/media-library-upload.js'), 'utf8'), context);
    let callbacks;
    const saved = [];
    const wire = {
        folder: 'photos',
        uploadMultiple(...args) { callbacks = args; },
        async uploadFiles(folder) { saved.push(folder); return saveResult; },
        cancelUpload(name) { assert.equal(name, 'uploads'); callbacks[5](); },
    };
    return { state: context.window.mediaLibraryUpload(wire), wire, saved, callbacks: () => callbacks };
}

test('drop captures destination, reports progress and saves only after transfer finishes', async () => {
    const f = fixture();
    f.state.drop({ dataTransfer: { files: [{ name: 'a.png', size: 100 }] } });
    const args = f.callbacks();
    assert.equal(args[0], 'uploads');
    assert.equal(args[6], false); // replace the previous temporary batch on retry
    assert.equal(f.state.busy, true);
    assert.deepEqual(f.saved, []);
    args[4]({ detail: { progress: 42 } });
    assert.equal(f.state.progress, 42);
    f.wire.folder = 'other';
    await args[2]();
    assert.deepEqual(f.saved, ['photos']);
    assert.equal(f.state.progress, 100);
    assert.equal(f.state.busy, false);
    assert.equal(f.state.status, 'Загрузка завершена.');
});

test('transfer failure and cancel never save the batch', () => {
    const f = fixture();
    f.state.receive([{ name: 'a.txt', size: 10 }]);
    f.callbacks()[3]();
    assert.equal(f.state.busy, false);
    assert.ok(f.state.error);
    f.state.receive([{ name: 'retry.txt', size: 10 }]);
    f.state.cancel();
    assert.equal(f.state.status, 'Передача отменена.');
    assert.deepEqual(f.saved, []);
});

test('size/count checks and a server validation failure do not report success', async () => {
    const f = fixture(null);
    f.state.receive([{ name: 'large.png', size: 10 * 1024 * 1024 + 1 }]);
    assert.equal(f.callbacks(), undefined);
    f.state.receive(Array.from({ length: 21 }, () => ({ name: 'a', size: 1 })));
    assert.equal(f.callbacks(), undefined);
    f.state.receive([{ name: 'invalid.txt', size: 10 }]);
    await f.callbacks()[2]();
    assert.equal(f.state.status, '');
    assert.ok(f.state.error);
    assert.equal(f.state.busy, false);
});
