import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../public/themes/default/assets/js/panel/login_register.js', import.meta.url), 'utf8');

function page(basePath) {
    const elements = new Map();
    const location = { href: '' };
    let ajax;
    const $ = selector => ({
        val: () => ['#email', '#password', '#password_reset_email'].includes(selector) ? 'test-value' : '',
        is: () => false,
    });
    $.ajax = options => { ajax = options; };
    $.each = (object, callback) => Object.entries(object).forEach(([key, value]) => callback(key, value));
    const context = vm.createContext({
        window: { magicaiBasePath: basePath, location }, location, $, FormData,
        document: { getElementById: id => {
            if (!elements.has(id)) elements.set(id, { value: '', disabled: false });
            return elements.get(id);
        } },
        Alpine: { store: () => ({ show() {}, hide() {} }) },
        toastr: { success() {}, error() {} },
        magicai_localize: {}, setTimeout: fn => fn(),
    });
    vm.runInContext(source, context);
    return { context, elements, location, request: () => ajax };
}

for (const base of ['', '/magicai']) {
    test(`auth requests, redirects and non-JSON errors at ${base || '/'}`, () => {
        const p = page(base);
        p.context.LoginForm();
        assert.equal(p.request().url, `${base}/login`);
        p.request().success({ link: '/dashboard/admin' });
        assert.equal(p.location.href, `${base}/dashboard/admin`);
        p.request().error({ status: 502 });
        assert.equal(p.elements.get('LoginFormButton').disabled, false);
        p.context.RegisterForm();
        assert.equal(p.request().url, `${base}/register`);
        p.request().success({});
        assert.equal(p.location.href, `${base}/dashboard`);
        p.request().error({ status: 502 });
        assert.equal(p.elements.get('RegisterFormButton').disabled, false);
        p.context.PasswordResetMailForm();
        assert.equal(p.request().url, `${base}/forgot-password`);
        p.context.PasswordReset('test-code');
        assert.equal(p.request().url, `${base}/forgot-password/save`);
        p.request().error({ status: 500 });
        assert.equal(p.elements.get('PasswordResetFormButton').disabled, false);
        assert.equal(p.context.magicaiAuthUrl('/dashboard/admin'), `${base}/dashboard/admin`);
        assert.equal(p.context.magicaiAuthUrl(`${base}/dashboard?plan=1`), `${base}/dashboard?plan=1`);
        assert.equal(p.context.magicaiAuthUrl('https://external.example.com'), 'https://external.example.com');
    });
}
