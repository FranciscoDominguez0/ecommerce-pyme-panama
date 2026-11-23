<!-- Polyfill prioritario anti-deprecaciones (mitiga advertencias de StorageType y FencedFrame emitidas por scripts de Cloudflare o extensiones) -->
<script data-navigate-once>
(function() {
    'use strict';
    try {
        var fakeQuota = {
            queryUsageAndQuota: function(cb) { if (typeof cb === 'function') cb(0, 0); },
            requestQuota: function(b, cb) { if (typeof cb === 'function') cb(b); }
        };
        var fakeStorageInfo = {
            TEMPORARY: 0,
            PERSISTENT: 1,
            queryUsageAndQuota: function(t, cb) { if (typeof cb === 'function') cb(0, 0); },
            requestQuota: function(t, b, cb) { if (typeof cb === 'function') cb(b); }
        };
        var fakeStorageType = { temporary: 0, persistent: 1 };

        // 1. Sobrescribir en Navigator.prototype
        if (typeof Navigator !== 'undefined' && Navigator.prototype) {
            try {
                Object.defineProperty(Navigator.prototype, 'webkitPersistentStorage', {
                    get: function() { return fakeQuota; },
                    set: function() {},
                    configurable: true,
                    enumerable: true
                });
            } catch (_) {}
            try {
                Object.defineProperty(Navigator.prototype, 'webkitTemporaryStorage', {
                    get: function() { return fakeQuota; },
                    set: function() {},
                    configurable: true,
                    enumerable: true
                });
            } catch (_) {}
        }

        // 2. Sobrescribir en Window.prototype
        if (typeof Window !== 'undefined' && Window.prototype) {
            try {
                Object.defineProperty(Window.prototype, 'webkitStorageInfo', {
                    get: function() { return fakeStorageInfo; },
                    set: function() {},
                    configurable: true,
                    enumerable: true
                });
            } catch (_) {}
            try {
                Object.defineProperty(Window.prototype, 'StorageType', {
                    get: function() { return fakeStorageType; },
                    set: function() {},
                    configurable: true,
                    enumerable: true
                });
            } catch (_) {}
            try {
                if ('fence' in Window.prototype) {
                    Object.defineProperty(Window.prototype, 'fence', {
                        get: function() { return null; },
                        set: function() {},
                        configurable: true,
                        enumerable: true
                    });
                }
            } catch (_) {}
        }

        // 3. Sobrescribir directamente en la instancia de window y navigator
        try {
            Object.defineProperty(window, 'StorageType', {
                value: fakeStorageType,
                writable: true,
                configurable: true
            });
        } catch (_) {}
        try {
            Object.defineProperty(window, 'webkitStorageInfo', {
                value: fakeStorageInfo,
                writable: true,
                configurable: true
            });
        } catch (_) {}
        if (window.navigator) {
            try {
                Object.defineProperty(window.navigator, 'webkitPersistentStorage', {
                    value: fakeQuota,
                    writable: true,
                    configurable: true
                });
            } catch (_) {}
            try {
                Object.defineProperty(window.navigator, 'webkitTemporaryStorage', {
                    value: fakeQuota,
                    writable: true,
                    configurable: true
                });
            } catch (_) {}
        }

        // 4. Interceptar iframes que scripts de fingerprinting como Cloudflare JSD puedan inyectar dinámicamente
        function patchWin(w) {
            if (!w) return;
            try {
                Object.defineProperty(w, 'StorageType', { value: fakeStorageType, configurable: true, writable: true });
                Object.defineProperty(w, 'webkitStorageInfo', { value: fakeStorageInfo, configurable: true, writable: true });
                if (w.navigator) {
                    Object.defineProperty(w.navigator, 'webkitPersistentStorage', { value: fakeQuota, configurable: true, writable: true });
                    Object.defineProperty(w.navigator, 'webkitTemporaryStorage', { value: fakeQuota, configurable: true, writable: true });
                }
            } catch (_) {}
        }

        if (typeof Node !== 'undefined' && Node.prototype) {
            var origAppendChild = Node.prototype.appendChild;
            Node.prototype.appendChild = function(child) {
                var res = origAppendChild.apply(this, arguments);
                if (child && child.tagName === 'IFRAME') {
                    try { patchWin(child.contentWindow); } catch (_) {}
                }
                return res;
            };
            var origInsertBefore = Node.prototype.insertBefore;
            Node.prototype.insertBefore = function(newNode, refNode) {
                var res = origInsertBefore.apply(this, arguments);
                if (newNode && newNode.tagName === 'IFRAME') {
                    try { patchWin(newNode.contentWindow); } catch (_) {}
                }
                return res;
            };
        }
    } catch (_) {}
})();
</script>
