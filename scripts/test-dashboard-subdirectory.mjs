import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const read = path => fs.readFileSync(new URL('../' + path, import.meta.url), 'utf8');
const adapter = read('public/themes/default/assets/js/panel/subdirectory-requests.js');

function page(base) {
    const requests = [], filters = [], elements = new Map();
    const response = new Response('data: test\n\n', { headers: { 'Content-Type': 'text/event-stream' } });
    const window = { magicaiBasePath: base, fetch: (input, options) => {
        requests.push({ url: input, options });
        return Promise.resolve(response);
    } };
    const $ = selector => ({ val: () => selector === '#createChatUrl' ? '' : 'test-value', ready() {} });
    $.ajaxPrefilter = fn => filters.push(fn);
    $.ajax = options => { filters.forEach(fn => fn(options)); requests.push(options); };
    $.each = (obj, fn) => Object.entries(obj).forEach(([key, value]) => fn(key, value));
    const context = vm.createContext({ window, $, FormData, document: {
        getElementById(id) { if (!elements.has(id)) elements.set(id, {}); return elements.get(id); },
        querySelector() { return null; },
    }, collectCreditsToFormData(form) { form.append('entities[gemini][gemini-3-flash-preview][credit]', '5000'); },
    magicai_localize: {}, toastr: { error() {}, success() {} } });
    vm.runInContext(adapter, context);
    window.magicaiInstallAjaxPaths($);
    window.magicaiInstallAjaxPaths($);
    return { window, context, requests, response, filters };
}

for (const base of ['', '/magicai']) {
    test(`chat creation and settings requests at ${base || '/'}`, () => {
        const p = page(base);
        // Execute actual production handlers rather than just testing URL strings.
        const chat = read('public/themes/default/assets/js/panel/openai_chat.js');
        vm.runInContext(chat.slice(chat.indexOf('function startNewChat('), chat.indexOf('function deleteAllConv(')), p.context);
        p.context.startNewChat(1, 'en');
        assert.equal(p.requests.at(-1).url, `${base}/dashboard/user/openai/chat/start-new-chat`);
        assert.equal(p.requests.at(-1).data.get('category_id'), '1');
        vm.runInContext(read('public/themes/default/assets/js/panel/settings.js'), p.context);
        p.context.geminiSettingsSave();
        assert.equal(p.requests.at(-1).url, `${base}/dashboard/admin/settings/gemini`);
        const user = read('public/themes/default/assets/js/panel/user.js');
        vm.runInContext(user.slice(user.indexOf('function userSave('), user.indexOf('//User', user.indexOf('function userSave('))), p.context);
        p.context.userSave(1);
        assert.equal(p.requests.at(-1).url, `${base}/dashboard/admin/users/save`);
        assert.equal(p.filters.length, base ? 1 : 0);
    });

    test(`chat list and streaming preserve bodies, abort signals and streams at ${base || '/'}`, async () => {
        const p = page(base);
        const controller = new AbortController();
        const options = { method: 'POST', body: 'prompt=test', signal: controller.signal, credentials: 'same-origin' };
        const response = await p.window.fetch('/dashboard/user/generator/generate-stream', options);
        assert.equal(p.requests.at(-1).url, `${base}/dashboard/user/generator/generate-stream`);
        assert.equal(p.requests.at(-1).options, options);
        assert.equal(response, p.response);
        assert.equal(await response.text(), 'data: test\n\n');
        await p.window.fetch('/dashboard/user/openai/chat/get-chats?page=1');
        assert.equal(p.requests.at(-1).url, `${base}/dashboard/user/openai/chat/get-chats?page=1`);
        for (const url of ['https://generativelanguage.googleapis.com/test', '//cdn.example/test', '/callmine/api', '/magicai/dashboard/user', 'relative/path']) {
            await p.window.fetch(url);
            assert.equal(p.requests.at(-1).url, url);
        }
        const request = new Request('https://example.com/stream', options);
        await p.window.fetch(request);
        assert.equal(p.requests.at(-1).url, request);
    });
}

test('request adapter runs before dashboard modules and jQuery page handlers', () => {
    const head = read('resources/views/default/panel/layout/partials/head.blade.php');
    assert.ok(head.indexOf('subdirectory-requests.js') < head.indexOf("@vite(\\App\\Helpers\\Classes\\ThemeHelper::appJsPath())"));
    const scripts = read('resources/views/default/panel/layout/scripts.blade.php');
    assert.ok(scripts.indexOf('jquery.min.js') < scripts.indexOf('magicaiInstallAjaxPaths'));
});
