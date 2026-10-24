// Scope dashboard requests to APP_URL without changing external/provider URLs.
(function (window) {
    'use strict';
    const base = (window.magicaiBasePath || '').replace(/\/+$/, '');
    const routes = ['/dashboard', '/livewire', '/files', '/tiptap-content-store'];

    window.magicaiRequestUrl = function (url) {
        if (!base || typeof url !== 'string' || !url.startsWith('/') || url.startsWith('//')) return url;
        if (url === base || url.startsWith(base + '/') || url.startsWith(base + '?')) return url;
        if (!routes.some(route => url === route || url.startsWith(route + '/') || url.startsWith(route + '?'))) return url;
        return base + url;
    };

    // Chat streaming and sidebar requests use fetch; settings and chat creation
    // use jQuery. Preserve their request options, bodies and response streams.
    if (base && window.fetch) {
        const originalFetch = window.fetch.bind(window);
        window.fetch = function (input, options) {
            return originalFetch(window.magicaiRequestUrl(input), options);
        };
    }

    window.magicaiInstallAjaxPaths = function ($) {
        if (!base || !$ || window.magicaiAjaxPathsInstalled) return;
        $.ajaxPrefilter(function (options) {
            options.url = window.magicaiRequestUrl(options.url);
        });
        window.magicaiAjaxPathsInstalled = true;
    };
})(window);
