const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { test } = require('node:test');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/filament/pages/sa-simulator.blade.php'), 'utf8');
const code = source.slice(source.indexOf('    var blogEdit ='), source.indexOf('    function loadPreset(key)'));

function editor() {
    const elements = Object.fromEntries(['endpoint', 'payload', 'blogRevisionNotice', 'blogPreserved', 'blogReload']
        .map(id => [id, { value: '', style: {}, addEventListener() {} }]));
    elements.endpoint.value = '/api/blog/posts/10';
    elements.payload.value = 'Initial input';
    const context = {
        currentPresetKey: 'blog_post_update',
        document: { getElementById: id => elements[id] },
        window: { confirm: () => true },
        $wire: { executeSimulator: async () => ({ http_code: 200, api_response: {
            data: { posts: [{ id: 10, revision: 'a'.repeat(64), status: 'draft', translations: {
                en: { title: 'Native', text: '<p>Current</p>', slug: 'current' },
                de: { title: null, text: null },
            } }] }, meta: { pagination: { last_page: 1 } },
        } }) },
    };
    vm.createContext(context);
    vm.runInContext(code, context);
    return { context, elements };
}

test('loads the revision and explicit translations through Livewire', async () => {
    const { context, elements } = editor();
    await context.loadBlogVersion();
    const payload = JSON.parse(elements.payload.value);
    assert.equal(payload.expected_revision, 'a'.repeat(64));
    assert.equal(payload.expected_status, 'draft');
    assert.equal(payload.translations.en.content, '<p>Current</p>');
    assert.equal(payload.translations.de, undefined);
    assert.equal(context.blogEdit.blocked, false);
});

test('failed explicit reload preserves edited text and blocks saving', async () => {
    const { context, elements } = editor();
    await context.loadBlogVersion();
    elements.payload.value = 'Unsaved editor input';
    context.$wire.executeSimulator = async () => { throw new Error('Network failure'); };
    await context.loadBlogVersion();
    assert.equal(elements.payload.value, 'Unsaved editor input');
    assert.equal(elements.blogPreserved.value, 'Unsaved editor input');
    assert.equal(context.blogEdit.blocked, true);
    assert.equal(context.blogEdit.loading, false);
});

test('a response for a previous endpoint cannot replace the current editor', async () => {
    const { context, elements } = editor();
    const original = context.$wire.executeSimulator;
    let release;
    context.$wire.executeSimulator = () => new Promise(resolve => { release = resolve; });
    const pending = context.loadBlogVersion();
    elements.endpoint.value = '/api/blog/posts/11';
    release(await original());
    await pending;
    assert.equal(elements.payload.value, 'Initial input');
    assert.equal(context.blogEdit.blocked, true);
});
