// modules are defined as an array
// [ module function, map of requires ]
//
// map of requires is short require name -> numeric require
//
// anything defined in a previous bundle is accessed via the
// orig method which is the require for previous bundles

(function (modules, entry, mainEntry, parcelRequireName, globalName) {
  /* eslint-disable no-undef */
  var globalObject =
    typeof globalThis !== 'undefined'
      ? globalThis
      : typeof self !== 'undefined'
      ? self
      : typeof window !== 'undefined'
      ? window
      : typeof global !== 'undefined'
      ? global
      : {};
  /* eslint-enable no-undef */

  // Save the require from previous bundle to this closure if any
  var previousRequire =
    typeof globalObject[parcelRequireName] === 'function' &&
    globalObject[parcelRequireName];

  var cache = previousRequire.cache || {};
  // Do not use `require` to prevent Webpack from trying to bundle this call
  var nodeRequire =
    typeof module !== 'undefined' &&
    typeof module.require === 'function' &&
    module.require.bind(module);

  function newRequire(name, jumped) {
    if (!cache[name]) {
      if (!modules[name]) {
        // if we cannot find the module within our internal map or
        // cache jump to the current global require ie. the last bundle
        // that was added to the page.
        var currentRequire =
          typeof globalObject[parcelRequireName] === 'function' &&
          globalObject[parcelRequireName];
        if (!jumped && currentRequire) {
          return currentRequire(name, true);
        }

        // If there are other bundles on this page the require from the
        // previous one is saved to 'previousRequire'. Repeat this as
        // many times as there are bundles until the module is found or
        // we exhaust the require chain.
        if (previousRequire) {
          return previousRequire(name, true);
        }

        // Try the node require function if it exists.
        if (nodeRequire && typeof name === 'string') {
          return nodeRequire(name);
        }

        var err = new Error("Cannot find module '" + name + "'");
        err.code = 'MODULE_NOT_FOUND';
        throw err;
      }

      localRequire.resolve = resolve;
      localRequire.cache = {};

      var module = (cache[name] = new newRequire.Module(name));

      modules[name][0].call(
        module.exports,
        localRequire,
        module,
        module.exports,
        this
      );
    }

    return cache[name].exports;

    function localRequire(x) {
      var res = localRequire.resolve(x);
      return res === false ? {} : newRequire(res);
    }

    function resolve(x) {
      var id = modules[name][1][x];
      return id != null ? id : x;
    }
  }

  function Module(moduleName) {
    this.id = moduleName;
    this.bundle = newRequire;
    this.exports = {};
  }

  newRequire.isParcelRequire = true;
  newRequire.Module = Module;
  newRequire.modules = modules;
  newRequire.cache = cache;
  newRequire.parent = previousRequire;
  newRequire.register = function (id, exports) {
    modules[id] = [
      function (require, module) {
        module.exports = exports;
      },
      {},
    ];
  };

  Object.defineProperty(newRequire, 'root', {
    get: function () {
      return globalObject[parcelRequireName];
    },
  });

  globalObject[parcelRequireName] = newRequire;

  for (var i = 0; i < entry.length; i++) {
    newRequire(entry[i]);
  }

  if (mainEntry) {
    // Expose entry point to Node, AMD or browser globals
    // Based on https://github.com/ForbesLindesay/umd/blob/master/template.js
    var mainExports = newRequire(mainEntry);

    // CommonJS
    if (typeof exports === 'object' && typeof module !== 'undefined') {
      module.exports = mainExports;

      // RequireJS
    } else if (typeof define === 'function' && define.amd) {
      define(function () {
        return mainExports;
      });

      // <script>
    } else if (globalName) {
      this[globalName] = mainExports;
    }
  }
})({"fwg5B":[function(require,module,exports) {
"use strict";
var global = arguments[3];
var HMR_HOST = null;
var HMR_PORT = null;
var HMR_SECURE = false;
var HMR_ENV_HASH = "d6ea1d42532a7575";
module.bundle.HMR_BUNDLE_ID = "e39680195b9dba80";
/* global HMR_HOST, HMR_PORT, HMR_ENV_HASH, HMR_SECURE, chrome, browser, globalThis, __parcel__import__, __parcel__importScripts__, ServiceWorkerGlobalScope */ /*::
import type {
  HMRAsset,
  HMRMessage,
} from '@parcel/reporter-dev-server/src/HMRServer.js';
interface ParcelRequire {
  (string): mixed;
  cache: {|[string]: ParcelModule|};
  hotData: mixed;
  Module: any;
  parent: ?ParcelRequire;
  isParcelRequire: true;
  modules: {|[string]: [Function, {|[string]: string|}]|};
  HMR_BUNDLE_ID: string;
  root: ParcelRequire;
}
interface ParcelModule {
  hot: {|
    data: mixed,
    accept(cb: (Function) => void): void,
    dispose(cb: (mixed) => void): void,
    // accept(deps: Array<string> | string, cb: (Function) => void): void,
    // decline(): void,
    _acceptCallbacks: Array<(Function) => void>,
    _disposeCallbacks: Array<(mixed) => void>,
  |};
}
interface ExtensionContext {
  runtime: {|
    reload(): void,
    getURL(url: string): string;
    getManifest(): {manifest_version: number, ...};
  |};
}
declare var module: {bundle: ParcelRequire, ...};
declare var HMR_HOST: string;
declare var HMR_PORT: string;
declare var HMR_ENV_HASH: string;
declare var HMR_SECURE: boolean;
declare var chrome: ExtensionContext;
declare var browser: ExtensionContext;
declare var __parcel__import__: (string) => Promise<void>;
declare var __parcel__importScripts__: (string) => Promise<void>;
declare var globalThis: typeof self;
declare var ServiceWorkerGlobalScope: Object;
*/ var OVERLAY_ID = "__parcel__error__overlay__";
var OldModule = module.bundle.Module;
function Module(moduleName) {
    OldModule.call(this, moduleName);
    this.hot = {
        data: module.bundle.hotData,
        _acceptCallbacks: [],
        _disposeCallbacks: [],
        accept: function(fn) {
            this._acceptCallbacks.push(fn || function() {});
        },
        dispose: function(fn) {
            this._disposeCallbacks.push(fn);
        }
    };
    module.bundle.hotData = undefined;
}
module.bundle.Module = Module;
var checkedAssets, acceptedAssets, assetsToAccept /*: Array<[ParcelRequire, string]> */ ;
function getHostname() {
    return HMR_HOST || (location.protocol.indexOf("http") === 0 ? location.hostname : "localhost");
}
function getPort() {
    return HMR_PORT || location.port;
} // eslint-disable-next-line no-redeclare
var parent = module.bundle.parent;
if ((!parent || !parent.isParcelRequire) && typeof WebSocket !== "undefined") {
    var hostname = getHostname();
    var port = getPort();
    var protocol = HMR_SECURE || location.protocol == "https:" && !/localhost|127.0.0.1|0.0.0.0/.test(hostname) ? "wss" : "ws";
    var ws = new WebSocket(protocol + "://" + hostname + (port ? ":" + port : "") + "/"); // Web extension context
    var extCtx = typeof chrome === "undefined" ? typeof browser === "undefined" ? null : browser : chrome; // Safari doesn't support sourceURL in error stacks.
    // eval may also be disabled via CSP, so do a quick check.
    var supportsSourceURL = false;
    try {
        (0, eval)('throw new Error("test"); //# sourceURL=test.js');
    } catch (err) {
        supportsSourceURL = err.stack.includes("test.js");
    } // $FlowFixMe
    ws.onmessage = async function(event) {
        checkedAssets = {} /*: {|[string]: boolean|} */ ;
        acceptedAssets = {} /*: {|[string]: boolean|} */ ;
        assetsToAccept = [];
        var data = JSON.parse(event.data);
        if (data.type === "update") {
            // Remove error overlay if there is one
            if (typeof document !== "undefined") removeErrorOverlay();
            let assets = data.assets.filter((asset)=>asset.envHash === HMR_ENV_HASH); // Handle HMR Update
            let handled = assets.every((asset)=>{
                return asset.type === "css" || asset.type === "js" && hmrAcceptCheck(module.bundle.root, asset.id, asset.depsByBundle);
            });
            if (handled) {
                console.clear(); // Dispatch custom event so other runtimes (e.g React Refresh) are aware.
                if (typeof window !== "undefined" && typeof CustomEvent !== "undefined") window.dispatchEvent(new CustomEvent("parcelhmraccept"));
                await hmrApplyUpdates(assets);
                for(var i = 0; i < assetsToAccept.length; i++){
                    var id = assetsToAccept[i][1];
                    if (!acceptedAssets[id]) hmrAcceptRun(assetsToAccept[i][0], id);
                }
            } else fullReload();
        }
        if (data.type === "error") {
            // Log parcel errors to console
            for (let ansiDiagnostic of data.diagnostics.ansi){
                let stack = ansiDiagnostic.codeframe ? ansiDiagnostic.codeframe : ansiDiagnostic.stack;
                console.error("\uD83D\uDEA8 [parcel]: " + ansiDiagnostic.message + "\n" + stack + "\n\n" + ansiDiagnostic.hints.join("\n"));
            }
            if (typeof document !== "undefined") {
                // Render the fancy html overlay
                removeErrorOverlay();
                var overlay = createErrorOverlay(data.diagnostics.html); // $FlowFixMe
                document.body.appendChild(overlay);
            }
        }
    };
    ws.onerror = function(e) {
        console.error(e.message);
    };
    ws.onclose = function() {
        console.warn("[parcel] \uD83D\uDEA8 Connection to the HMR server was lost");
    };
}
function removeErrorOverlay() {
    var overlay = document.getElementById(OVERLAY_ID);
    if (overlay) {
        overlay.remove();
        console.log("[parcel] \u2728 Error resolved");
    }
}
function createErrorOverlay(diagnostics) {
    var overlay = document.createElement("div");
    overlay.id = OVERLAY_ID;
    let errorHTML = '<div style="background: black; opacity: 0.85; font-size: 16px; color: white; position: fixed; height: 100%; width: 100%; top: 0px; left: 0px; padding: 30px; font-family: Menlo, Consolas, monospace; z-index: 9999;">';
    for (let diagnostic of diagnostics){
        let stack = diagnostic.frames.length ? diagnostic.frames.reduce((p, frame)=>{
            return `${p}
<a href="/__parcel_launch_editor?file=${encodeURIComponent(frame.location)}" style="text-decoration: underline; color: #888" onclick="fetch(this.href); return false">${frame.location}</a>
${frame.code}`;
        }, "") : diagnostic.stack;
        errorHTML += `
      <div>
        <div style="font-size: 18px; font-weight: bold; margin-top: 20px;">
          🚨 ${diagnostic.message}
        </div>
        <pre>${stack}</pre>
        <div>
          ${diagnostic.hints.map((hint)=>"<div>\uD83D\uDCA1 " + hint + "</div>").join("")}
        </div>
        ${diagnostic.documentation ? `<div>📝 <a style="color: violet" href="${diagnostic.documentation}" target="_blank">Learn more</a></div>` : ""}
      </div>
    `;
    }
    errorHTML += "</div>";
    overlay.innerHTML = errorHTML;
    return overlay;
}
function fullReload() {
    if ("reload" in location) location.reload();
    else if (extCtx && extCtx.runtime && extCtx.runtime.reload) extCtx.runtime.reload();
}
function getParents(bundle, id) /*: Array<[ParcelRequire, string]> */ {
    var modules = bundle.modules;
    if (!modules) return [];
    var parents = [];
    var k, d, dep;
    for(k in modules)for(d in modules[k][1]){
        dep = modules[k][1][d];
        if (dep === id || Array.isArray(dep) && dep[dep.length - 1] === id) parents.push([
            bundle,
            k
        ]);
    }
    if (bundle.parent) parents = parents.concat(getParents(bundle.parent, id));
    return parents;
}
function updateLink(link) {
    var newLink = link.cloneNode();
    newLink.onload = function() {
        if (link.parentNode !== null) // $FlowFixMe
        link.parentNode.removeChild(link);
    };
    newLink.setAttribute("href", link.getAttribute("href").split("?")[0] + "?" + Date.now()); // $FlowFixMe
    link.parentNode.insertBefore(newLink, link.nextSibling);
}
var cssTimeout = null;
function reloadCSS() {
    if (cssTimeout) return;
    cssTimeout = setTimeout(function() {
        var links = document.querySelectorAll('link[rel="stylesheet"]');
        for(var i = 0; i < links.length; i++){
            // $FlowFixMe[incompatible-type]
            var href = links[i].getAttribute("href");
            var hostname = getHostname();
            var servedFromHMRServer = hostname === "localhost" ? new RegExp("^(https?:\\/\\/(0.0.0.0|127.0.0.1)|localhost):" + getPort()).test(href) : href.indexOf(hostname + ":" + getPort());
            var absolute = /^https?:\/\//i.test(href) && href.indexOf(location.origin) !== 0 && !servedFromHMRServer;
            if (!absolute) updateLink(links[i]);
        }
        cssTimeout = null;
    }, 50);
}
function hmrDownload(asset) {
    if (asset.type === "js") {
        if (typeof document !== "undefined") {
            let script = document.createElement("script");
            script.src = asset.url + "?t=" + Date.now();
            if (asset.outputFormat === "esmodule") script.type = "module";
            return new Promise((resolve, reject)=>{
                var _document$head;
                script.onload = ()=>resolve(script);
                script.onerror = reject;
                (_document$head = document.head) === null || _document$head === void 0 || _document$head.appendChild(script);
            });
        } else if (typeof importScripts === "function") {
            // Worker scripts
            if (asset.outputFormat === "esmodule") return import(asset.url + "?t=" + Date.now());
            else return new Promise((resolve, reject)=>{
                try {
                    importScripts(asset.url + "?t=" + Date.now());
                    resolve();
                } catch (err) {
                    reject(err);
                }
            });
        }
    }
}
async function hmrApplyUpdates(assets) {
    global.parcelHotUpdate = Object.create(null);
    let scriptsToRemove;
    try {
        // If sourceURL comments aren't supported in eval, we need to load
        // the update from the dev server over HTTP so that stack traces
        // are correct in errors/logs. This is much slower than eval, so
        // we only do it if needed (currently just Safari).
        // https://bugs.webkit.org/show_bug.cgi?id=137297
        // This path is also taken if a CSP disallows eval.
        if (!supportsSourceURL) {
            let promises = assets.map((asset)=>{
                var _hmrDownload;
                return (_hmrDownload = hmrDownload(asset)) === null || _hmrDownload === void 0 ? void 0 : _hmrDownload.catch((err)=>{
                    // Web extension bugfix for Chromium
                    // https://bugs.chromium.org/p/chromium/issues/detail?id=1255412#c12
                    if (extCtx && extCtx.runtime && extCtx.runtime.getManifest().manifest_version == 3) {
                        if (typeof ServiceWorkerGlobalScope != "undefined" && global instanceof ServiceWorkerGlobalScope) {
                            extCtx.runtime.reload();
                            return;
                        }
                        asset.url = extCtx.runtime.getURL("/__parcel_hmr_proxy__?url=" + encodeURIComponent(asset.url + "?t=" + Date.now()));
                        return hmrDownload(asset);
                    }
                    throw err;
                });
            });
            scriptsToRemove = await Promise.all(promises);
        }
        assets.forEach(function(asset) {
            hmrApply(module.bundle.root, asset);
        });
    } finally{
        delete global.parcelHotUpdate;
        if (scriptsToRemove) scriptsToRemove.forEach((script)=>{
            if (script) {
                var _document$head2;
                (_document$head2 = document.head) === null || _document$head2 === void 0 || _document$head2.removeChild(script);
            }
        });
    }
}
function hmrApply(bundle, asset) {
    var modules = bundle.modules;
    if (!modules) return;
    if (asset.type === "css") reloadCSS();
    else if (asset.type === "js") {
        let deps = asset.depsByBundle[bundle.HMR_BUNDLE_ID];
        if (deps) {
            if (modules[asset.id]) {
                // Remove dependencies that are removed and will become orphaned.
                // This is necessary so that if the asset is added back again, the cache is gone, and we prevent a full page reload.
                let oldDeps = modules[asset.id][1];
                for(let dep in oldDeps)if (!deps[dep] || deps[dep] !== oldDeps[dep]) {
                    let id = oldDeps[dep];
                    let parents = getParents(module.bundle.root, id);
                    if (parents.length === 1) hmrDelete(module.bundle.root, id);
                }
            }
            if (supportsSourceURL) // Global eval. We would use `new Function` here but browser
            // support for source maps is better with eval.
            (0, eval)(asset.output);
             // $FlowFixMe
            let fn = global.parcelHotUpdate[asset.id];
            modules[asset.id] = [
                fn,
                deps
            ];
        } else if (bundle.parent) hmrApply(bundle.parent, asset);
    }
}
function hmrDelete(bundle, id1) {
    let modules = bundle.modules;
    if (!modules) return;
    if (modules[id1]) {
        // Collect dependencies that will become orphaned when this module is deleted.
        let deps = modules[id1][1];
        let orphans = [];
        for(let dep in deps){
            let parents = getParents(module.bundle.root, deps[dep]);
            if (parents.length === 1) orphans.push(deps[dep]);
        } // Delete the module. This must be done before deleting dependencies in case of circular dependencies.
        delete modules[id1];
        delete bundle.cache[id1]; // Now delete the orphans.
        orphans.forEach((id)=>{
            hmrDelete(module.bundle.root, id);
        });
    } else if (bundle.parent) hmrDelete(bundle.parent, id1);
}
function hmrAcceptCheck(bundle, id, depsByBundle) {
    if (hmrAcceptCheckOne(bundle, id, depsByBundle)) return true;
     // Traverse parents breadth first. All possible ancestries must accept the HMR update, or we'll reload.
    let parents = getParents(module.bundle.root, id);
    let accepted = false;
    while(parents.length > 0){
        let v = parents.shift();
        let a = hmrAcceptCheckOne(v[0], v[1], null);
        if (a) // If this parent accepts, stop traversing upward, but still consider siblings.
        accepted = true;
        else {
            // Otherwise, queue the parents in the next level upward.
            let p = getParents(module.bundle.root, v[1]);
            if (p.length === 0) {
                // If there are no parents, then we've reached an entry without accepting. Reload.
                accepted = false;
                break;
            }
            parents.push(...p);
        }
    }
    return accepted;
}
function hmrAcceptCheckOne(bundle, id, depsByBundle) {
    var modules = bundle.modules;
    if (!modules) return;
    if (depsByBundle && !depsByBundle[bundle.HMR_BUNDLE_ID]) {
        // If we reached the root bundle without finding where the asset should go,
        // there's nothing to do. Mark as "accepted" so we don't reload the page.
        if (!bundle.parent) return true;
        return hmrAcceptCheck(bundle.parent, id, depsByBundle);
    }
    if (checkedAssets[id]) return true;
    checkedAssets[id] = true;
    var cached = bundle.cache[id];
    assetsToAccept.push([
        bundle,
        id
    ]);
    if (!cached || cached.hot && cached.hot._acceptCallbacks.length) return true;
}
function hmrAcceptRun(bundle, id) {
    var cached = bundle.cache[id];
    bundle.hotData = {};
    if (cached && cached.hot) cached.hot.data = bundle.hotData;
    if (cached && cached.hot && cached.hot._disposeCallbacks.length) cached.hot._disposeCallbacks.forEach(function(cb) {
        cb(bundle.hotData);
    });
    delete bundle.cache[id];
    bundle(id);
    cached = bundle.cache[id];
    if (cached && cached.hot && cached.hot._acceptCallbacks.length) cached.hot._acceptCallbacks.forEach(function(cb) {
        var assetsToAlsoAccept = cb(function() {
            return getParents(module.bundle.root, id);
        });
        if (assetsToAlsoAccept && assetsToAccept.length) // $FlowFixMe[method-unbinding]
        assetsToAccept.push.apply(assetsToAccept, assetsToAlsoAccept);
    });
    acceptedAssets[id] = true;
}

},{}],"f36YN":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
/**
 * Internal dependencies.
 */ var _store = require("./store");
var _header = require("./elements/header");
var _headerDefault = parcelHelpers.interopDefault(_header);
var _uploader = require("./elements/uploader");
var _uploaderDefault = parcelHelpers.interopDefault(_uploader);
var _directoryCreator = require("./elements/directory-creator");
var _directoryCreatorDefault = parcelHelpers.interopDefault(_directoryCreator);
var _toolbar = require("./elements/toolbar");
var _toolbarDefault = parcelHelpers.interopDefault(_toolbar);
var _main = require("./elements/main");
var _mainDefault = parcelHelpers.interopDefault(_main);
/**
 * WordPress dependencies
 */ const { domReady , element: { createElement , render , Fragment  } , i18n: { __  } , data: { useSelect , useDispatch  }  } = wp;
const MediaLibrary = (_ref)=>{
    let { settings  } = _ref;
    const { isGrid , globalSettings , tree  } = useSelect((select)=>{
        const store = select((0, _store.BP_ATTACHMENTS_STORE_KEY));
        return {
            isGrid: store.isGridDisplayMode(),
            globalSettings: store.getSettings(),
            tree: store.getTree()
        };
    }, []);
    if (!Object.keys(globalSettings).length) {
        const { setSettings  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
        setSettings(settings);
    }
    return createElement(Fragment, null, createElement((0, _headerDefault.default), {
        settings: globalSettings
    }), createElement((0, _uploaderDefault.default), {
        settings: globalSettings
    }), createElement((0, _directoryCreatorDefault.default), null), createElement((0, _toolbarDefault.default), {
        gridDisplay: isGrid,
        tree: tree
    }), createElement((0, _mainDefault.default), {
        gridDisplay: isGrid,
        tree: tree
    }));
};
domReady(function() {
    const settings = window.bpAttachmentsMediaLibrarySettings || {};
    render(createElement(MediaLibrary, {
        settings: settings
    }), document.querySelector("#bp-media-library"));
});

},{"./store":"aEF6v","./elements/header":"i7QTP","./elements/uploader":"5AiyA","./elements/directory-creator":"jy9eT","./elements/toolbar":"54hUI","./elements/main":"gTXrr","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"aEF6v":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
parcelHelpers.export(exports, "BP_ATTACHMENTS_STORE_KEY", ()=>BP_ATTACHMENTS_STORE_KEY);
/**
 * Internal dependencies.
 */ var _constants = require("./constants");
var _selectors = require("./selectors");
var _actions = require("./actions");
var _resolvers = require("./resolvers");
var _reducers = require("./reducers");
var _reducersDefault = parcelHelpers.interopDefault(_reducers);
var _controls = require("./controls");
/**
 * WordPress dependencies.
 */ const { data: { registerStore  }  } = wp;
registerStore((0, _constants.STORE_KEY), {
    reducer: (0, _reducersDefault.default),
    actions: _actions,
    selectors: _selectors,
    controls: (0, _controls.controls),
    resolvers: _resolvers
});
const BP_ATTACHMENTS_STORE_KEY = (0, _constants.STORE_KEY);

},{"./constants":"acqZq","./selectors":"g8zBP","./actions":"l5k3n","./resolvers":"fWbBK","./reducers":"9PJxI","./controls":"3wk6S","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"acqZq":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
parcelHelpers.export(exports, "STORE_KEY", ()=>STORE_KEY);
const STORE_KEY = "bp/attachments";

},{"@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"gkKU3":[function(require,module,exports) {
exports.interopDefault = function(a) {
    return a && a.__esModule ? a : {
        default: a
    };
};
exports.defineInteropFlag = function(a) {
    Object.defineProperty(a, "__esModule", {
        value: true
    });
};
exports.exportAll = function(source, dest) {
    Object.keys(source).forEach(function(key) {
        if (key === "default" || key === "__esModule" || dest.hasOwnProperty(key)) return;
        Object.defineProperty(dest, key, {
            enumerable: true,
            get: function() {
                return source[key];
            }
        });
    });
    return dest;
};
exports.export = function(dest, destName, get) {
    Object.defineProperty(dest, destName, {
        enumerable: true,
        get: get
    });
};

},{}],"g8zBP":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
parcelHelpers.export(exports, "getSettings", ()=>getSettings);
parcelHelpers.export(exports, "getRequestsContext", ()=>getRequestsContext);
parcelHelpers.export(exports, "isGridDisplayMode", ()=>isGridDisplayMode);
parcelHelpers.export(exports, "getLoggedInUser", ()=>getLoggedInUser);
parcelHelpers.export(exports, "getFormState", ()=>getFormState);
parcelHelpers.export(exports, "isUploading", ()=>isUploading);
parcelHelpers.export(exports, "uploadEnded", ()=>uploadEnded);
parcelHelpers.export(exports, "getUploads", ()=>getUploads);
parcelHelpers.export(exports, "getErrors", ()=>getErrors);
parcelHelpers.export(exports, "getMedia", ()=>getMedia);
parcelHelpers.export(exports, "getCurrentDirectory", ()=>getCurrentDirectory);
parcelHelpers.export(exports, "getCurrentDirectoryObject", ()=>getCurrentDirectoryObject);
parcelHelpers.export(exports, "getTree", ()=>getTree);
parcelHelpers.export(exports, "getFlatTree", ()=>getFlatTree);
parcelHelpers.export(exports, "isSelectable", ()=>isSelectable);
parcelHelpers.export(exports, "selectedMedia", ()=>selectedMedia);
parcelHelpers.export(exports, "getRelativePath", ()=>getRelativePath);
parcelHelpers.export(exports, "getDestinationData", ()=>getDestinationData);
/**
 * Internal dependencies.
 */ var _functions = require("../utils/functions");
/**
 * External dependencies.
 */ const { trim , groupBy , filter , indexOf , find , defaultTo  } = lodash;
const getSettings = (state)=>{
    const { settings  } = state;
    return settings;
};
const getRequestsContext = (state)=>{
    const { settings: { isAdminScreen  }  } = state;
    return true === isAdminScreen ? "edit" : "view";
};
const isGridDisplayMode = (state)=>{
    const { isGrid  } = state;
    return isGrid;
};
const getLoggedInUser = (state)=>{
    const { user  } = state;
    return user;
};
const getFormState = (state)=>{
    const { formState  } = state;
    return formState;
};
const isUploading = (state)=>{
    const { uploading  } = state;
    return uploading;
};
const uploadEnded = (state)=>{
    const { ended  } = state;
    return ended;
};
const getUploads = (state)=>{
    const { uploads  } = state;
    return uploads;
};
const getErrors = (state)=>{
    const { errors  } = state;
    return errors;
};
const getMedia = (state)=>{
    const { files  } = state;
    return files;
};
const getCurrentDirectory = (state)=>{
    const { currentDirectory  } = state;
    return currentDirectory || "";
};
const getCurrentDirectoryObject = (state)=>{
    const { currentDirectory , tree  } = state;
    const defaultValue = {
        readonly: true
    };
    if ("" !== currentDirectory) return defaultTo(find(tree, {
        id: currentDirectory
    }), defaultValue);
    return defaultValue;
};
const getTree = (state)=>{
    const { tree , currentDirectory  } = state;
    const groupedTree = groupBy(tree, "parent");
    const currentChildrenIds = filter(tree, {
        "parent": currentDirectory || 0
    }).map((child)=>child.id); // Makes sure to only list current directory children.
    if (currentChildrenIds && currentChildrenIds.length) currentChildrenIds.forEach((childId)=>{
        if (groupedTree[childId]) delete groupedTree[childId];
    });
     // Makes sure to avoid listing children of directories that are not an ancestor of the currentDirectory one.
    if (currentDirectory) {
        const currentAncestors = (0, _functions.getDirectoryAncestors)(tree, currentDirectory).map((ancestor)=>ancestor.id);
        Object.keys(groupedTree).forEach((treeIndex)=>{
            if (0 !== parseInt(treeIndex, 10) && -1 === indexOf(currentAncestors, treeIndex)) delete groupedTree[treeIndex];
        });
    }
    const fillWithChildren = (items)=>{
        return items.map((item)=>{
            const children = groupedTree[item.id];
            return {
                ...item,
                children: children && children.length ? fillWithChildren(children) : []
            };
        });
    };
    return fillWithChildren(groupedTree[0] || []);
};
const getFlatTree = (state)=>{
    const { tree  } = state;
    return tree || [];
};
const isSelectable = (state)=>{
    const { isSelectable: isSelectable1  } = state;
    return isSelectable1;
};
const selectedMedia = (state)=>{
    const { files  } = state;
    return filter(files, [
        "selected",
        true
    ]);
};
const getRelativePath = (state)=>{
    const { relativePath  } = state;
    return relativePath;
};
const getDestinationData = (state)=>{
    const { relativePath  } = state;
    if (!relativePath) return {
        object: "members"
    };
    const destinationData = trim(relativePath, "/").split("/");
    return {
        visibility: destinationData[0] ? destinationData[0] : "public",
        object: destinationData[1] ? destinationData[1] : "members",
        item: destinationData[2] ? destinationData[2] : ""
    };
};

},{"../utils/functions":"gJTJw","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"gJTJw":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
parcelHelpers.export(exports, "getDirectoryAncestors", ()=>getDirectoryAncestors);
parcelHelpers.export(exports, "bytesToSize", ()=>bytesToSize);
/**
 * WordPress dependencies
 */ const { i18n: { __  }  } = wp;
/**
 * External dependencies
 */ const { filter  } = lodash;
const getDirectoryAncestors = (tree, parentId)=>{
    let parents = filter(tree, {
        id: parentId
    });
    parents.forEach((parent)=>{
        const grandParents = getDirectoryAncestors(tree, parent.parent);
        parents = [
            ...parents,
            ...grandParents
        ];
    });
    return parents;
};
const bytesToSize = (bytes)=>{
    const sizes = [
        __("Bytes", "bp-attachments"),
        __("KB", "bp-attachments"),
        __("MB", "bp-attachments"),
        __("GB", "bp-attachments"),
        __("TB", "bp-attachments")
    ];
    if (bytes === 0) return "0 " + sizes[0];
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);
    if (i === 0) return `${bytes} ${sizes[i]}`;
    return `${(bytes / 1024 ** i).toFixed(1)} ${sizes[i]}`;
};

},{"@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"l5k3n":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Returns an action to set the BP attachments Media Library settings.
 *
 * @param {Object} settings The settings to use.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "setSettings", ()=>setSettings);
/**
 * Returns an action object used to fetch media from the API.
 *
 * @param {string} path Endpoint path.
 * @param {boolean} parse Should we parse the request.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "fetchFromAPI", ()=>fetchFromAPI);
/**
 * Returns an action object used to create media via the API.
 *
 * @param {string} path Endpoint path.
 * @param {Object} data The data to be created.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "createFromAPI", ()=>createFromAPI);
/**
 * Returns an action object used to update media via the API.
 *
 * @param {string} path Endpoint path.
 * @param {Object} data The data to be updated.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "updateFromAPI", ()=>updateFromAPI);
/**
 * Returns an action object used to delete a media via the API.
 *
 * @param {string} path Endpoint path.
 * @param {Object} data The data to be created.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "deleteFromAPI", ()=>deleteFromAPI);
/**
 * Returns an action object used to switch between Grid & List mode.
 *
 * @param {Boolean} isGrid
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "switchDisplayMode", ()=>switchDisplayMode);
/**
 * Returns an action object used to get the logged in user.
 *
 * @param {Object} user Logged In User object.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "getLoggedInUser", ()=>getLoggedInUser);
/**
 * Returns an action object used to get media.
 *
 * @param {Array} files The list of files.
 * @param {String} relativePath The relative path.
 * @param {Object} currentDirectory The current directory.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "getMedia", ()=>getMedia);
/**
 * Returns an action object used to update the Upload/Directory Form state.
 *
 * @param {Object} params
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "updateFormState", ()=>updateFormState);
/**
 * Init the directories Tree.
 *
 * @param {array} items The list of media.
 */ parcelHelpers.export(exports, "initTree", ()=>initTree);
/**
 * Returns an action object used to add a directory item to the Items tree.
 *
 * @param {Object} item A media item.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "addItemTree", ()=>addItemTree);
/**
 * Returns an action object used to remove a directory item from the Items tree.
 *
 * @param {string} itemId A media item ID.
 * @return {Object} Object for action.
 */ parcelHelpers.export(exports, "removeItemTree", ()=>removeItemTree);
/**
 * Returns an action object used to switch between Selectable & Regular mode.
 *
 * @param {boolean} isSelectable True to switch to Selectable mode. False otherwise.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "toggleSelectable", ()=>toggleSelectable);
/**
 * Returns an action object used to switch between Selectable & Regular mode.
 *
 * @param {array} ids The list of media ID.
 * @param {boolean} isSelected True if the media is selected. False otherwise.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "toggleMediaSelection", ()=>toggleMediaSelection);
/**
 * Returns an action object used to add a new file to the Media list.
 *
 * @param {object} file The uploaded medium.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "addMedium", ()=>addMedium);
/**
 * Returns an action object used to add a new error.
 *
 * @param {object} error The uploaded file which errored.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "addMediumError", ()=>addMediumError);
/**
 * Creates a Medium uploading a file.
 *
 * @param {Object} file The file object to upload.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "createMedium", ()=>createMedium);
/**
 * Creates a new directory, photo album, audio or video playluist.
 *
 * @todo to avoid too much code duplication, createDirectory & createMedium should probably be
 *       gathered into one function.
 *
 * @param {Object} directory The data to use create the directory
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "createDirectory", ()=>createDirectory);
/**
 * Updates a Medium.
 *
 * @param {Object} medium The medium object to update.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "updateMedium", ()=>updateMedium);
parcelHelpers.export(exports, "parseResponseMedia", ()=>parseResponseMedia);
/**
 * Requests media according to specific arguments.
 *
 * @param {Object} args The Media request arguments.
 * @returns {void}
 */ parcelHelpers.export(exports, "requestMedia", ()=>requestMedia);
/**
 * Returns an action object used to remove a medium from the state.
 *
 * @param {integer} id The medium ID.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "removeMedium", ()=>removeMedium);
/**
 * Deletes a Medium removing the file from the server's filesystem.
 *
 * @param {Object} file The file object to upload.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "deleteMedium", ()=>deleteMedium);
/**
 * Returns an action object used to remove an error.
 *
 * @param {integer} errorID The error ID.
 * @returns {Object} Object for action.
 */ parcelHelpers.export(exports, "removeMediumError", ()=>removeMediumError);
/**
 * Internal dependencies.
 */ var _actionTypes = require("./action-types");
var _constants = require("./constants");
/**
 * External dependencies.
 */ const { uniqueId , hasIn , trim , trimEnd , filter  } = lodash;
/**
 * WordPress dependencies.
 */ const { data: { dispatch , select  } , url: { addQueryArgs  }  } = wp;
function setSettings(settings) {
    return {
        type: (0, _actionTypes.TYPES).SET_SETTINGS,
        settings
    };
}
function fetchFromAPI(path, parse) {
    return {
        type: (0, _actionTypes.TYPES).FETCH_FROM_API,
        path,
        parse
    };
}
function createFromAPI(path, data) {
    return {
        type: (0, _actionTypes.TYPES).CREATE_FROM_API,
        path,
        data
    };
}
function updateFromAPI(path, data) {
    return {
        type: (0, _actionTypes.TYPES).UPDATE_FROM_API,
        path,
        data
    };
}
function deleteFromAPI(path, relativePath) {
    return {
        type: (0, _actionTypes.TYPES).DELETE_FROM_API,
        path,
        relativePath
    };
}
function switchDisplayMode(isGrid) {
    return {
        type: (0, _actionTypes.TYPES).SWITCH_DISPLAY_MODE,
        isGrid
    };
}
function getLoggedInUser(user) {
    return {
        type: (0, _actionTypes.TYPES).GET_LOGGED_IN_USER,
        user
    };
}
function getMedia(files, relativePath, currentDirectory) {
    return {
        type: (0, _actionTypes.TYPES).GET_MEDIA,
        files,
        relativePath,
        currentDirectory
    };
}
function updateFormState(params) {
    return {
        type: (0, _actionTypes.TYPES).UPDATE_FORM_STATE,
        params
    };
}
/**
 * Prepare a directory to be added to the Tree.
 *
 * @param {Object} directory The medium object.
 * @param {string} parent The parent ID.
 * @returns {Object} The item Tree.
 */ const setItemTree = (directory, parent)=>{
    const itemTree = {
        id: directory.id,
        slug: directory.name,
        name: directory.title,
        parent: parent,
        object: directory.object ? directory.object : "members",
        readonly: directory.readonly ? directory.readonly : false,
        visibility: directory.visibility ? directory.visibility : "public",
        type: directory.media_type ? directory.media_type : "folder"
    };
    return itemTree;
};
function initTree(items) {
    const tree = select((0, _constants.STORE_KEY)).getTree();
    const directories = filter(items, {
        "mime_type": "inode/directory"
    });
    if (!tree.length) directories.forEach((item)=>{
        const itemTree = setItemTree(item, 0);
        dispatch((0, _constants.STORE_KEY)).addItemTree(itemTree);
    });
}
function addItemTree(item) {
    return {
        type: (0, _actionTypes.TYPES).FILL_TREE,
        item
    };
}
function removeItemTree(itemId) {
    return {
        type: (0, _actionTypes.TYPES).PURGE_TREE,
        itemId
    };
}
function toggleSelectable(isSelectable) {
    return {
        type: (0, _actionTypes.TYPES).TOGGLE_SELECTABLE,
        isSelectable
    };
}
function toggleMediaSelection(ids, isSelected) {
    return {
        type: (0, _actionTypes.TYPES).TOGGLE_MEDIA_SELECTION,
        ids,
        isSelected
    };
}
function addMedium(file) {
    return {
        type: (0, _actionTypes.TYPES).ADD_MEDIUM,
        file
    };
}
function addMediumError(error) {
    return {
        type: (0, _actionTypes.TYPES).ADD_ERROR,
        error
    };
}
function* createMedium(file) {
    let uploading = true, upload;
    const store = select((0, _constants.STORE_KEY));
    const { object , item , visibility  } = store.getDestinationData();
    const relativePath = store.getRelativePath();
    yield {
        type: "UPLOAD_START",
        uploading,
        file
    };
    const formData = new FormData();
    formData.append("file", file);
    formData.append("action", "bp_attachments_media_upload");
    formData.append("object", object);
    formData.append("object_item", item);
    if (visibility) formData.append("visibility", visibility);
    if (trim(relativePath, "/") !== visibility + "/" + object + "/" + item) {
        let uploadRelativePath = relativePath; // Private uploads are stored out of the site's uploads dir.
        if ("private" === visibility) uploadRelativePath = relativePath.replace("/private", "");
        formData.append("parent_dir", uploadRelativePath);
    }
    uploading = false;
    try {
        upload = yield createFromAPI("/buddypress/v1/attachments", formData);
        yield {
            type: "UPLOAD_END",
            uploading,
            file
        };
        return addMedium(upload);
    } catch (error) {
        upload = {
            id: uniqueId(),
            name: file.name,
            error: error.message,
            uploaded: false
        };
        yield {
            type: "UPLOAD_END",
            uploading,
            file
        };
        return addMediumError(upload);
    }
}
function* createDirectory(directory) {
    let uploading = true, upload; // A directory is handled as a file having the inode/directory mime type.
    let file = {
        name: directory.directoryName,
        type: directory.directoryType
    };
    const store = select((0, _constants.STORE_KEY));
    const { object , item , visibility  } = store.getDestinationData();
    const relativePath = store.getRelativePath();
    yield {
        type: "UPLOAD_START",
        uploading,
        file
    };
    const formData = new FormData();
    formData.append("directory_name", file.name);
    formData.append("directory_type", file.type);
    formData.append("action", "bp_attachments_make_directory");
    formData.append("object", object);
    formData.append("object_item", item);
    if (visibility) formData.append("visibility", visibility);
    if (trim(relativePath, "/") !== visibility + "/" + object + "/" + item) {
        let createDirRelativePath = relativePath; // Private uploads are stored out of the site's uploads dir.
        if ("private" === visibility) createDirRelativePath = relativePath.replace("/private", "");
        formData.append("parent_dir", createDirRelativePath);
    }
    uploading = false;
    try {
        upload = yield createFromAPI("/buddypress/v1/attachments", formData);
        yield {
            type: "UPLOAD_END",
            uploading,
            file
        };
        upload.uploaded = true;
        const currentDir = store.getCurrentDirectoryObject();
        const itemTree = setItemTree(upload, currentDir.id); // Add the directory to the tree.
        yield addItemTree(itemTree);
        return addMedium(upload);
    } catch (error) {
        upload = {
            id: uniqueId(),
            name: file.name,
            error: error.message,
            uploaded: false
        };
        yield {
            type: "UPLOAD_END",
            uploading,
            file
        };
        return addMediumError(upload);
    }
}
function* updateMedium(medium) {
    let update;
    const store = select((0, _constants.STORE_KEY));
    const relativePath = store.getRelativePath();
    try {
        update = yield updateFromAPI("/buddypress/v1/attachments/" + medium.id + "/", {
            "relative_path": relativePath,
            title: medium.title,
            description: medium.description
        });
        return addMedium(update);
    } catch (error) {
        update = {
            id: uniqueId(),
            name: medium.name,
            error: error.message,
            updated: false
        };
        return addMediumError(update);
    }
}
const parseResponseMedia = async function(response, relativePath) {
    let parent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : "";
    const items = await response.json().then((data)=>{
        data.forEach((item)=>{
            item.parent = parent;
            if ("inode/directory" === item.mime_type) {
                const itemTree = setItemTree(item, parent);
                dispatch((0, _constants.STORE_KEY)).addItemTree(itemTree);
            }
        });
        return data;
    }); // Init the Tree when needed.
    if (!relativePath && !parent) initTree(items);
    dispatch((0, _constants.STORE_KEY)).getMedia(items, relativePath, parent);
};
function* requestMedia() {
    let args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    const path = "/buddypress/v1/attachments";
    let relativePathHeader = "";
    let parent = "";
    if (!args.context) args.context = select((0, _constants.STORE_KEY)).getRequestsContext();
    if (args.directory && args.path) args.directory = trimEnd(args.path, "/") + "/" + args.directory;
    if (args.parent) {
        parent = args.parent;
        delete args.parent;
    }
    delete args.path;
    const response = yield fetchFromAPI(addQueryArgs(path, args), false);
    if (hasIn(response, [
        "headers",
        "get"
    ])) // If the request is fetched using the fetch api, the header can be
    // retrieved using the 'get' method.
    relativePathHeader = response.headers.get("X-BP-Attachments-Relative-Path");
    else // If the request was preloaded server-side and is returned by the
    // preloading middleware, the header will be a simple property.
    relativePathHeader = get(response, [
        "headers",
        "X-BP-Attachments-Relative-Path"
    ], "");
    return parseResponseMedia(response, relativePathHeader, parent);
}
function removeMedium(id) {
    return {
        type: "REMOVE_MEDIUM",
        id
    };
}
function* deleteMedium(file) {
    const store = select((0, _constants.STORE_KEY));
    const relativePath = store.getRelativePath();
    let deleted;
    try {
        deleted = yield deleteFromAPI("/buddypress/v1/attachments/" + file.id + "/", relativePath);
        if ("inode/directory" === deleted.previous.mime_type) yield removeItemTree(deleted.previous.id);
        return removeMedium(deleted.previous.id);
    } catch (error) {
        file.error = error.message;
        return addMediumError(file);
    }
}
function removeMediumError(errorID) {
    return {
        type: (0, _actionTypes.TYPES).REMOVE_ERROR,
        errorID
    };
}

},{"./action-types":"gprKR","./constants":"acqZq","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"gprKR":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
parcelHelpers.export(exports, "TYPES", ()=>TYPES);
const TYPES = {
    SET_SETTINGS: "SET_SETTINGS",
    GET_LOGGED_IN_USER: "GET_LOGGED_IN_USER",
    GET_MEDIA: "GET_MEDIA",
    ADD_MEDIUM: "ADD_MEDIUM",
    FILL_TREE: "FILL_TREE",
    PURGE_TREE: "PURGE_TREE",
    REMOVE_MEDIUM: "REMOVE_MEDIUM",
    FETCH_FROM_API: "FETCH_FROM_API",
    CREATE_FROM_API: "CREATE_FROM_API",
    UPDATE_FROM_API: "UPDATE_FROM_API",
    DELETE_FROM_API: "DELETE_FROM_API",
    UPLOAD_START: "UPLOAD_START",
    UPLOAD_END: "UPLOAD_END",
    RESET_UPLOADS: "RESET_UPLOADS",
    ADD_ERROR: "ADD_ERROR",
    REMOVE_ERROR: "REMOVE_ERROR",
    TOGGLE_SELECTABLE: "TOGGLE_SELECTABLE",
    TOGGLE_MEDIA_SELECTION: "TOGGLE_MEDIA_SELECTION",
    SWITCH_DISPLAY_MODE: "SWITCH_DISPLAY_MODE",
    UPDATE_FORM_STATE: "UPDATE_FORM_STATE"
};

},{"@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"fWbBK":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Resolver for retrieving current user.
 */ parcelHelpers.export(exports, "getLoggedInUser", ()=>getLoggedInUser);
/**
 * Resolver for retrieving the media root directories.
 */ parcelHelpers.export(exports, "getMedia", ()=>getMedia);
/**
 * Internal dependencies.
 */ var _actions = require("./actions");
/**
 * External dependencies.
 */ const { filter  } = lodash;
/**
 * Returns the requests context.
 *
 * @access private
 * @returns {string} The requests context (view or edit).
 */ const _requestContext = ()=>{
    const { isAdminScreen  } = window.bpAttachmentsMediaLibrarySettings || {};
    return isAdminScreen && true === isAdminScreen ? "edit" : "view";
};
function* getLoggedInUser() {
    const path = "/buddypress/v1/members/me?context=edit";
    const user = yield (0, _actions.fetchFromAPI)(path, true);
    yield (0, _actions.getLoggedInUser)(user);
}
function* getMedia() {
    const path = "/buddypress/v1/attachments?context=" + _requestContext();
    const files = yield (0, _actions.fetchFromAPI)(path, true); // Init the Directories tree.
    (0, _actions.initTree)(files);
    yield (0, _actions.getMedia)(files, "");
}

},{"./actions":"l5k3n","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"9PJxI":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies
 */ var _actionTypes = require("./action-types");
/**
 * External dependencies.
 */ const { reject  } = lodash;
/**
 * Default state.
 */ const DEFAULT_STATE = {
    user: {},
    tree: [],
    currentDirectory: "",
    files: [],
    relativePath: "",
    uploads: [],
    errors: [],
    uploading: false,
    ended: false,
    isSelectable: false,
    isGrid: true,
    settings: {},
    formState: {}
};
/**
 * Reducer for the BP Attachments Library.
 *
 * @param   {Object}  state   The current state in the store.
 * @param   {Object}  action  Action object.
 *
 * @return  {Object}          New or existing state.
 */ const reducer = function() {
    let state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : DEFAULT_STATE;
    let action = arguments.length > 1 ? arguments[1] : undefined;
    switch(action.type){
        case (0, _actionTypes.TYPES).SET_SETTINGS:
            return {
                ...state,
                settings: action.settings
            };
        case (0, _actionTypes.TYPES).GET_LOGGED_IN_USER:
            return {
                ...state,
                user: action.user
            };
        case (0, _actionTypes.TYPES).GET_MEDIA:
            return {
                ...state,
                files: action.files,
                relativePath: action.relativePath,
                currentDirectory: action.currentDirectory
            };
        case (0, _actionTypes.TYPES).FILL_TREE:
            return {
                ...state,
                tree: [
                    ...reject(state.tree, [
                        "id",
                        action.item.id
                    ]),
                    action.item
                ]
            };
        case (0, _actionTypes.TYPES).PURGE_TREE:
            return {
                ...state,
                tree: reject(state.tree, [
                    "id",
                    action.itemId
                ])
            };
        case (0, _actionTypes.TYPES).UPDATE_FORM_STATE:
            return {
                ...state,
                formState: action.params
            };
        case (0, _actionTypes.TYPES).ADD_MEDIUM:
            return {
                ...state,
                files: [
                    ...reject(state.files, [
                        "id",
                        action.file.id
                    ]),
                    action.file
                ]
            };
        case (0, _actionTypes.TYPES).UPLOAD_START:
            return {
                ...state,
                uploading: action.uploading,
                uploads: [
                    ...state.uploads,
                    action.file
                ]
            };
        case (0, _actionTypes.TYPES).ADD_ERROR:
            return {
                ...state,
                errors: [
                    ...state.errors,
                    action.error
                ]
            };
        case (0, _actionTypes.TYPES).REMOVE_ERROR:
            return {
                ...state,
                errors: reject(state.errors, [
                    "id",
                    action.errorID
                ])
            };
        case (0, _actionTypes.TYPES).UPLOAD_END:
            return {
                ...state,
                uploading: action.uploading,
                uploads: reject(state.uploads, (u)=>{
                    return u.name === action.file.name;
                }),
                ended: true
            };
        case (0, _actionTypes.TYPES).RESET_UPLOADS:
            return {
                ...state,
                uploading: false,
                uploads: [],
                errors: [],
                ended: false
            };
        case (0, _actionTypes.TYPES).TOGGLE_SELECTABLE:
            return {
                ...state,
                isSelectable: action.isSelectable
            };
        case (0, _actionTypes.TYPES).TOGGLE_MEDIA_SELECTION:
            return {
                ...state,
                files: state.files.map((file)=>{
                    if ("all" === action.ids[0] && !action.isSelected || -1 !== action.ids.indexOf(file.id)) file.selected = action.isSelected;
                    return file;
                })
            };
        case (0, _actionTypes.TYPES).SWITCH_DISPLAY_MODE:
            return {
                ...state,
                isGrid: action.isGrid
            };
        case (0, _actionTypes.TYPES).REMOVE_MEDIUM:
            return {
                ...state,
                files: [
                    ...reject(state.files, [
                        "id",
                        action.id
                    ])
                ]
            };
    }
    return state;
};
exports.default = reducer;

},{"./action-types":"gprKR","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"3wk6S":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
parcelHelpers.export(exports, "controls", ()=>controls);
/**
 * WordPress dependencies.
 */ const { apiFetch  } = wp;
const controls = {
    FETCH_FROM_API (_ref) {
        let { path , parse  } = _ref;
        return apiFetch({
            path,
            parse
        });
    },
    CREATE_FROM_API (_ref2) {
        let { path , data  } = _ref2;
        return apiFetch({
            path: path,
            method: "POST",
            body: data
        });
    },
    UPDATE_FROM_API (_ref3) {
        let { path , data  } = _ref3;
        return apiFetch({
            path: path,
            method: "PUT",
            data: data
        });
    },
    DELETE_FROM_API (_ref4) {
        let { path , relativePath  } = _ref4;
        return apiFetch({
            path: path,
            method: "DELETE",
            data: {
                relative_path: relativePath
            }
        });
    }
};

},{"@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"i7QTP":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
/**
 * WordPress dependencies
 */ const { components: { Popover  } , data: { useDispatch , useSelect  } , element: { createElement , Fragment , useState  } , i18n: { __  }  } = wp;
/**
 * Header element.
 */ const MediaLibraryHeader = (_ref)=>{
    let { settings  } = _ref;
    const { updateFormState  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
    const currentDirectoryObject = useSelect((select)=>{
        return select((0, _store.BP_ATTACHMENTS_STORE_KEY)).getCurrentDirectoryObject();
    }, []);
    const [isOpen, setOpen] = useState(false);
    const toggleClass = true === isOpen ? "split-button is-open" : "split-button";
    const dashiconClass = true === isOpen ? "dashicons dashicons-arrow-up-alt2" : "dashicons dashicons-arrow-down-alt2";
    const canUpload = true !== currentDirectoryObject.readonly;
    const { allowedExtByMediaList  } = settings;
    const showUploadForm = (e)=>{
        e.preventDefault();
        return updateFormState({
            parentDirectory: currentDirectoryObject.id,
            action: "upload"
        });
    };
    const showCreateDirForm = (e, type)=>{
        e.preventDefault();
        return updateFormState({
            parentDirectory: currentDirectoryObject.id,
            action: "createDirectory",
            directoryType: type
        });
    };
    let dirOptions = [];
    const directoryTypes = [
        "album",
        "audio_playlist",
        "video_playlist"
    ];
    if (!currentDirectoryObject.type || -1 === directoryTypes.indexOf(currentDirectoryObject.type)) {
        dirOptions = [
            {
                id: "folder",
                text: __("Add new directory", "bp-attachments")
            }
        ];
        if (allowedExtByMediaList && "private" !== currentDirectoryObject.visibility) Object.keys(allowedExtByMediaList).forEach((directoryType)=>{
            if ("album" === directoryType) dirOptions.push({
                id: "album",
                text: __("Add new photo album", "bp-attachments")
            });
            else if ("audio_playlist" === directoryType) dirOptions.push({
                id: "audio_playlist",
                text: __("Add new audio playlist", "bp-attachments")
            });
            else if ("video_playlist" === directoryType) dirOptions.push({
                id: "video_playlist",
                text: __("Add new video playlist", "bp-attachments")
            });
        });
    }
    const dirList = dirOptions.map((dirOption)=>{
        return createElement("li", {
            key: "type-" + dirOption.id
        }, createElement("a", {
            href: "#new-bp-media-directory",
            className: "button-link directory-button split-button-option",
            onClick: (e)=>showCreateDirForm(e, dirOption.id)
        }, dirOption.text));
    });
    return createElement(Fragment, null, createElement("h1", {
        className: "wp-heading-inline"
    }, __("Community Media Library", "bp-attachments")), !!canUpload && createElement("div", {
        className: toggleClass
    }, createElement("div", {
        className: "split-button-head"
    }, createElement("a", {
        href: "#new-bp-media-upload",
        className: "button split-button-primary",
        "aria-live": "polite",
        onClick: (e)=>showUploadForm(e)
    }, __("Add new", "bp-attachments")), createElement("button", {
        type: "button",
        className: "split-button-toggle",
        "aria-haspopup": "true",
        "aria-expanded": isOpen,
        onClick: ()=>setOpen(!isOpen)
    }, createElement("i", {
        className: dashiconClass
    }), createElement("span", {
        className: "screen-reader-text"
    }, __("More actions", "bp-attachments")), isOpen && createElement(Popover, {
        noArrow: false,
        onFocusOutside: ()=>setOpen(!isOpen)
    }, createElement("ul", {
        className: "split-button-body"
    }, createElement("li", null, createElement("a", {
        href: "#new-bp-media-upload",
        className: "button-link media-button split-button-option",
        onClick: (e)=>showUploadForm(e)
    }, __("Upload media", "bp-attachments"))), dirList))))), createElement("hr", {
        className: "wp-header-end"
    }));
};
exports.default = MediaLibraryHeader;

},{"../store":"aEF6v","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"5AiyA":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
var _functions = require("../utils/functions");
/**
 * WordPress dependencies
 */ const { components: { DropZone , FormFileUpload  } , data: { useDispatch , useSelect  } , element: { createElement  } , i18n: { __ , sprintf  }  } = wp;
/**
 * File Uploader element.
 */ const MediaLibraryUploader = (_ref)=>{
    let { settings  } = _ref;
    const { maxUploadFileSize , allowedExtTypes , allowedExtByMediaList  } = settings;
    const { updateFormState , createMedium  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
    const { formState , currentDirectoryObject  } = useSelect((select)=>{
        const store = select((0, _store.BP_ATTACHMENTS_STORE_KEY));
        return {
            formState: store.getFormState(),
            currentDirectoryObject: store.getCurrentDirectoryObject()
        };
    }, []);
    const resetFormState = ()=>{
        formState.action = "";
        return updateFormState(formState);
    };
    const closeForm = (e)=>{
        e.preventDefault();
        resetFormState();
    };
    const uploadMedia = (files)=>{
        let media;
        if (files.currentTarget && files.currentTarget.files) media = [
            ...files.currentTarget.files
        ];
        else media = files;
        media.forEach((medium)=>{
            createMedium(medium);
        });
        resetFormState();
    };
    if (!formState.action || "upload" !== formState.action) return null;
    let allowedExts = allowedExtTypes;
    const directoryTypes = [
        "album",
        "audio_playlist",
        "video_playlist"
    ];
    if (currentDirectoryObject.type && -1 !== directoryTypes.indexOf(currentDirectoryObject.type)) allowedExts = allowedExtByMediaList[currentDirectoryObject.type];
    return createElement("div", {
        className: "uploader-container enabled"
    }, createElement(DropZone, {
        label: __("Drop your files here.", "bp-attachments"),
        onFilesDrop: (files)=>uploadMedia(files),
        className: "uploader-inline"
    }), createElement("button", {
        className: "close dashicons dashicons-no",
        onClick: (e)=>closeForm(e)
    }, createElement("span", {
        className: "screen-reader-text"
    }, __("Close the upload panel", "bp-attachments"))), createElement("div", {
        className: "dropzone-label"
    }, createElement("h2", {
        className: "upload-instructions drop-instructions"
    }, __("Drop files to upload", "bp-attachments")), createElement("p", {
        className: "upload-instructions drop-instructions"
    }, __("or", "bp-attachments")), createElement(FormFileUpload, {
        onChange: (files)=>uploadMedia(files),
        multiple: true,
        accept: allowedExts,
        className: "browser button button-hero"
    }, __("Select Files", "bp-attachments"))), createElement("div", {
        className: "upload-restrictions"
    }, createElement("p", null, sprintf(__("Maximum upload file size: %s.", "bp-attachments"), (0, _functions.bytesToSize)(maxUploadFileSize)))));
};
exports.default = MediaLibraryUploader;

},{"../store":"aEF6v","../utils/functions":"gJTJw","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"jy9eT":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
/**
 * WordPress dependencies
 */ const { components: { Button , TextControl  } , data: { useDispatch , useSelect  } , element: { createElement , useState  } , i18n: { __  }  } = wp;
/**
 * Directory Creator element.
 */ const MediaLibraryDirectoryCreator = ()=>{
    const [directoryName1, setDirectoryName] = useState("");
    const { updateFormState , createDirectory  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
    const formState = useSelect((select)=>{
        return select((0, _store.BP_ATTACHMENTS_STORE_KEY)).getFormState();
    }, []);
    const resetFormState = ()=>{
        formState.action = "";
        formState.directoryType = "";
        return updateFormState(formState);
    };
    const closeForm = (e)=>{
        e.preventDefault();
        resetFormState();
    };
    const makeDirectory = (e)=>{
        e.preventDefault();
        const directory = {
            directoryName: directoryName1,
            directoryType: formState.directoryType
        };
        createDirectory(directory);
        setDirectoryName("");
        resetFormState();
    };
    if (!formState.action || "createDirectory" !== formState.action) return null;
    let title = __("Create a new directory", "bp-attachments");
    let nameLabel = __("Type a name for your directory", "bp-attachments");
    let buttonLabel = __("Save directory", "bp-attachments");
    if ("album" === formState.directoryType) {
        title = __("Create a new photo album", "bp-attachments");
        nameLabel = __("Type a name for your photo album", "bp-attachments");
        buttonLabel = __("Save photo album", "bp-attachments");
    } else if ("audio_playlist" === formState.directoryType) {
        title = __("Create a new audio playlist", "bp-attachments");
        nameLabel = __("Type a name for your audio playlist", "bp-attachments");
        buttonLabel = __("Save audio playlist", "bp-attachments");
    } else if ("video_playlist" === formState.directoryType) {
        title = __("Create a new video playlist", "bp-attachments");
        nameLabel = __("Type a name for your video playlist", "bp-attachments");
        buttonLabel = __("Save video playlist", "bp-attachments");
    }
    return createElement("form", {
        id: "bp-media-directory-form",
        className: "directory-creator-container enabled"
    }, createElement("button", {
        className: "close dashicons dashicons-no",
        onClick: (e)=>closeForm(e)
    }, createElement("span", {
        className: "screen-reader-text"
    }, __("Close the Create directory form", "bp-attachments"))), createElement("h2", null, title), createElement(TextControl, {
        label: nameLabel,
        value: directoryName1,
        onChange: (directoryName)=>setDirectoryName(directoryName)
    }), createElement(Button, {
        variant: "primary",
        onClick: (e)=>makeDirectory(e)
    }, buttonLabel));
};
exports.default = MediaLibraryDirectoryCreator;

},{"../store":"aEF6v","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"54hUI":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
var _functions = require("../utils/functions");
/**
 * External dependencies.
 */ const { find , reverse  } = lodash;
/**
 * WordPress dependencies
 */ const { components: { Button , TreeSelect  } , element: { createElement , useState  } , data: { useDispatch , useSelect  } , hooks: { applyFilters  } , i18n: { __  }  } = wp;
/**
 * Toolbar element.
 */ const MediaLibraryToolbar = (_ref)=>{
    let { gridDisplay , tree  } = _ref;
    const { switchDisplayMode , requestMedia , toggleSelectable , toggleMediaSelection , deleteMedium  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
    const { user , currentDirectory , currentDirectoryObject , flatTree , isSelectable , selectedMedia  } = useSelect((select)=>{
        const store = select((0, _store.BP_ATTACHMENTS_STORE_KEY));
        return {
            user: store.getLoggedInUser(),
            currentDirectory: store.getCurrentDirectory(),
            currentDirectoryObject: store.getCurrentDirectoryObject(),
            flatTree: store.getFlatTree(),
            isSelectable: store.isSelectable(),
            selectedMedia: store.selectedMedia()
        };
    }, []);
    const [page, setPage] = useState(currentDirectory);
    const canSelect = true !== currentDirectoryObject.readonly;
    const hasSelectedMedia = isSelectable && selectedMedia.length !== 0;
    if (currentDirectory !== page) setPage(currentDirectory);
    const switchMode = (e, isGrid)=>{
        e.preventDefault();
        switchDisplayMode(isGrid);
    };
    const changeDirectory = (directory)=>{
        setPage(directory);
        const directoryItem = find(flatTree, {
            id: directory
        });
        let args = {};
        if (directoryItem) {
            args.directory = directoryItem.slug;
            args.parent = directoryItem.id;
            if (directoryItem.parent && directoryItem.object) {
                let chunks = reverse((0, _functions.getDirectoryAncestors)(flatTree, directoryItem.parent).map((parent)=>parent.slug));
                if ("members" === directoryItem.object) {
                    /**
           * In a future release, when Groups will be supported. The root directories will be:
           * - My Groups Media,
           * - My Media.
           *
           * The "My Media" ID is 'member'. We need to remove this from chunks as files are stored in
           * `/uploads/buddypress/public/members/{userID}` or `../buddypress-private/members/{userID}`.
           */ const memberIndex = chunks.indexOf("member");
                    if (-1 !== memberIndex) chunks.splice(memberIndex, 1);
                    if (chunks.length) chunks.splice(1, 0, directoryItem.object, user.id);
                } else // Use this filter to customize the pathArray for other components (eg: groups).
                chunks = applyFilters("buddypress.Attachments.toolbarTreeSelect.pathArray", chunks, directoryItem, user.id);
                args.path = "/" + chunks.join("/");
            }
            if (directoryItem.object) args.object = directoryItem.object;
        }
        return requestMedia(args);
    };
    const onToggleSectable = (event)=>{
        event.preventDefault();
        const toggle = !isSelectable;
        if (!toggle) toggleMediaSelection([
            "all"
        ], toggle);
        return toggleSelectable(toggle);
    };
    const onDeleteSelected = (event)=>{
        event.preventDefault();
        selectedMedia.forEach((medium)=>{
            deleteMedium(medium);
        });
        return toggleSelectable(false);
    };
    return createElement("div", {
        className: "media-toolbar wp-filter"
    }, createElement("div", {
        className: "media-toolbar-secondary"
    }, !isSelectable && createElement("div", {
        className: "view-switch media-grid-view-switch"
    }, createElement("a", {
        href: "#view-list",
        onClick: (e)=>switchMode(e, false),
        className: gridDisplay ? "view-list" : "view-list current"
    }, createElement("span", {
        className: "screen-reader-text"
    }, __("Display list", "bp-attachments"))), createElement("a", {
        href: "#view-grid",
        onClick: (e)=>switchMode(e, true),
        className: gridDisplay ? "view-grid current" : "view-grid"
    }, createElement("span", {
        className: "screen-reader-text"
    }, __("Display grid", "bp-attachments")))), canSelect && createElement(Button, {
        variant: "secondary",
        className: "media-button select-mode-toggle-button",
        onClick: (e)=>onToggleSectable(e)
    }, !isSelectable ? __("Select", "bp-attachments") : __("Cancel Selection", "bp-attachments")), canSelect && hasSelectedMedia && createElement(Button, {
        variant: "primary",
        className: "media-button delete-selected-button",
        onClick: (e)=>onDeleteSelected(e)
    }, __("Delete selection", "bp-attachments"))), !!tree.length && createElement("div", {
        className: "media-toolbar-primary"
    }, createElement(TreeSelect, {
        noOptionLabel: __("Home", "bp-attachments"),
        onChange: (directory)=>changeDirectory(directory),
        selectedId: page,
        tree: tree
    })));
};
exports.default = MediaLibraryToolbar;

},{"../store":"aEF6v","../utils/functions":"gJTJw","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"gTXrr":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
var _item = require("./item");
var _itemDefault = parcelHelpers.interopDefault(_item);
var _notices = require("./notices");
var _noticesDefault = parcelHelpers.interopDefault(_notices);
/**
 * WordPress dependencies
 */ const { element: { createElement , Fragment  } , i18n: { __  } , data: { useSelect , useDispatch  }  } = wp;
/**
 * Main element.
 */ const MediaLibraryMain = (_ref)=>{
    let { gridDisplay , tree  } = _ref;
    const { items , isSelectable  } = useSelect((select)=>{
        const store = select((0, _store.BP_ATTACHMENTS_STORE_KEY));
        return {
            items: store.getMedia(),
            isSelectable: store.isSelectable()
        };
    }, []);
    let mediaItems = null;
    if (items.length) mediaItems = items.map((item)=>{
        return createElement((0, _itemDefault.default), {
            key: "media-item-" + item.id,
            id: item.id,
            name: item.name,
            title: item.title,
            mediaType: item.media_type,
            mimeType: item.mime_type,
            icon: item.icon,
            vignette: item.vignette,
            orientation: item.orientation,
            isSelected: item.selected || false,
            object: item.object || "members",
            isSelectable: isSelectable,
            tree: tree,
            medium: item
        });
    });
    return createElement("main", {
        className: "bp-user-media"
    }, createElement((0, _noticesDefault.default), null), createElement("div", {
        className: isSelectable ? "media-items mode-select" : "media-items"
    }, mediaItems, !items.length && createElement("p", {
        className: "no-media"
    }, __("No community media items found.", "bp-attachments"))));
};
exports.default = MediaLibraryMain;

},{"../store":"aEF6v","./item":"3IHsH","./notices":"eVyat","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"3IHsH":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
var _setTemplate = require("../utils/set-template");
var _setTemplateDefault = parcelHelpers.interopDefault(_setTemplate);
var _editItem = require("./edit-item");
var _editItemDefault = parcelHelpers.interopDefault(_editItem);
/**
 * WordPress dependencies
 */ const { element: { createElement , Fragment , useState  } , components: { Modal  } , i18n: { __  } , data: { useSelect , useDispatch  }  } = wp;
const MediaItem = (props)=>{
    const Template = (0, _setTemplateDefault.default)("bp-attachments-media-item");
    const { medium , selected  } = props;
    const { toggleMediaSelection , requestMedia  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
    const [isOpen, toggleModal] = useState(false);
    const [isSelected, selectMedia] = useState(selected);
    const { getRelativePath , isSelectable: isSelectable1  } = useSelect((select)=>{
        const store = select((0, _store.BP_ATTACHMENTS_STORE_KEY));
        return {
            getRelativePath: store.getRelativePath(),
            isSelectable: store.isSelectable()
        };
    }, []);
    if (!isSelectable1 && !selected && isSelected) selectMedia(false);
    const classes = isSelected ? "media-item selected" : "media-item";
    const onMediaClick = ()=>{
        const { mimeType , name , isSelectable , id , object  } = props;
        if (isSelectable) {
            selectMedia(!isSelected);
            return toggleMediaSelection([
                id
            ], !isSelected);
        }
        if ("inode/directory" === mimeType) return requestMedia({
            directory: name,
            path: getRelativePath,
            object: object,
            parent: id
        });
        toggleModal(true);
    };
    return createElement(Fragment, null, createElement("div", {
        className: classes,
        dangerouslySetInnerHTML: {
            __html: Template(props)
        },
        role: "checkbox",
        onClick: ()=>onMediaClick()
    }), isOpen && createElement(Modal, {
        title: __("Media details", "bp-attachments"),
        onRequestClose: ()=>toggleModal(false)
    }, createElement((0, _editItemDefault.default), {
        medium: medium,
        errorCallback: toggleModal
    })));
};
exports.default = MediaItem;

},{"../store":"aEF6v","../utils/set-template":"9UrM4","./edit-item":"8lWv7","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"9UrM4":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * External dependencies
 */ const { template  } = lodash;
function setTemplate(tmpl) {
    const options = {
        evaluate: /<#([\s\S]+?)#>/g,
        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
        escape: /\{\{([^\}]+?)\}\}(?!\})/g,
        variable: "data"
    };
    return template(document.querySelector("#tmpl-" + tmpl).innerHTML, options);
}
exports.default = setTemplate;

},{"@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"8lWv7":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
/**
 * WordPress dependencies
 */ const { components: { Button , ExternalLink , TextareaControl , TextControl  } , data: { useDispatch  } , element: { createElement , useState  } , i18n: { __ , sprintf  }  } = wp;
const EditMediaItem = (_ref)=>{
    let { medium , errorCallback  } = _ref;
    const { id , name , title , description , vignette , icon , links: { view , download  }  } = medium;
    const [editedMedium, editMedium] = useState({
        title: title,
        description: description
    });
    const { updateMedium  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
    const isDisabled = title === editedMedium.title && description === editedMedium.description;
    const saveMediumProps = (event)=>{
        event.preventDefault();
        updateMedium({
            id: id,
            name: name,
            title: editedMedium.title,
            description: editedMedium.description
        }).then((response)=>{
            if (response.error) errorCallback(false);
            else if (response.file) editMedium({
                ...editedMedium,
                title: response.file.title,
                description: response.file.description
            });
        });
    };
    const resetMediumProps = (event)=>{
        event.preventDefault();
        editMedium({
            ...editedMedium,
            title: title,
            description: description
        });
    };
    return createElement("div", {
        className: "bp-attachment-edit-item"
    }, createElement("div", {
        className: "bp-attachment-edit-item__preview"
    }, createElement("h3", {
        className: "bp-attachment-edit-item__preview_title"
    }, editedMedium.title), createElement("div", {
        className: "bp-attachment-edit-item__preview_vignette"
    }, createElement("ul", {
        className: "bp-attachment-edit-item__preview_links"
    }, createElement("li", null, createElement(ExternalLink, {
        href: view
    }, __("Open media page", "bp-attachments"))), createElement("li", null, createElement("a", {
        href: download
    }, __("Download media", "bp-attachments")))), createElement("p", null, editedMedium.description), vignette && createElement("img", {
        src: vignette,
        className: "bp-attachment-medium-vignette"
    }), !vignette && createElement("img", {
        src: icon,
        className: "bp-attachment-medium-icon"
    }))), createElement("div", {
        className: "bp-attachment-edit-item__form"
    }, createElement("h3", null, sprintf(__("Edit %s", "bp-attachments"), name)), createElement("p", {
        className: "description"
    }, __("Use the below fields to edit media properties.", "bp-attachments")), createElement(TextControl, {
        label: __("Title", "bp-attachments"),
        value: editedMedium.title,
        onChange: (value)=>editMedium({
                ...editedMedium,
                title: value
            }),
        help: __("Change the title of your medium to something more descriptive then its file name.", "bp-attachments")
    }), createElement(TextareaControl, {
        label: __("Description", "bp-attachments"),
        value: editedMedium.description,
        onChange: (text)=>editMedium({
                ...editedMedium,
                description: text
            }),
        help: __("Add or edit the description of your medium to tell your story about it.", "bp-attachments")
    }), createElement("div", {
        className: "bp-attachment-edit-item__form-actions"
    }, createElement(Button, {
        variant: "primary",
        disabled: isDisabled,
        onClick: (e)=>saveMediumProps(e)
    }, __("Save your edits", "bp-attachment")), createElement(Button, {
        variant: "tertiary",
        disabled: isDisabled,
        onClick: (e)=>resetMediumProps(e)
    }, __("Cancel", "bp-attachment")))));
};
exports.default = EditMediaItem;

},{"../store":"aEF6v","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}],"eVyat":[function(require,module,exports) {
var parcelHelpers = require("@parcel/transformer-js/src/esmodule-helpers.js");
parcelHelpers.defineInteropFlag(exports);
/**
 * Internal dependencies.
 */ var _store = require("../store");
/**
 * WordPress dependencies
 */ const { components: { Animate , Dashicon , Notice  } , element: { createElement , Fragment  } , i18n: { __ , sprintf  } , data: { useSelect , useDispatch  }  } = wp;
/**
 * Notices element.
 */ const MediaLibraryNotices = ()=>{
    const { uploads , errors  } = useSelect((select)=>{
        const store = select((0, _store.BP_ATTACHMENTS_STORE_KEY));
        return {
            uploads: store.getUploads(),
            errors: store.getErrors()
        };
    }, []);
    const { removeMediumError  } = useDispatch((0, _store.BP_ATTACHMENTS_STORE_KEY));
    const onRemoveError = (errorID)=>{
        return removeMediumError(errorID);
    };
    let errorNotices = [];
    if (errors && errors.length) errorNotices = errors.map((error)=>{
        return createElement(Notice, {
            key: "error-" + error.id,
            status: "error",
            onRemove: ()=>onRemoveError(error.id),
            isDismissible: true
        }, createElement("p", null, createElement(Dashicon, {
            icon: "warning"
        }), sprintf(/* translators: 1: file name. 2: error message. */ __("\xab %1$s \xbb wasn\u2018t added to the media library. %2$s", "bp-attachments"), error.name, error.error)));
    });
    let loadingNotice = null;
    const numberUploads = uploads && uploads.length ? uploads.length : 0;
    if (!!numberUploads) {
        // Looks like WP CLI can't find _n() usage.
        let uploadingMedia = __("Uploading the media, please wait.", "bp-attachments");
        if (numberUploads > 1) /* translators: %d: number of media being uploaded. */ uploadingMedia = sprintf(__("Uploading %d media, please wait.", "bp-attachments"), numberUploads);
        loadingNotice = createElement("div", {
            className: "chargement-de-documents"
        }, createElement(Animate, {
            type: "loading"
        }, (_ref)=>{
            let { className  } = _ref;
            return createElement(Notice, {
                status: "warning",
                isDismissible: false
            }, createElement("p", {
                className: className
            }, createElement(Dashicon, {
                icon: "update"
            }), uploadingMedia));
        }));
    }
    return createElement("div", {
        className: "bp-attachments-notices"
    }, loadingNotice, errorNotices);
};
exports.default = MediaLibraryNotices;

},{"../store":"aEF6v","@parcel/transformer-js/src/esmodule-helpers.js":"gkKU3"}]},["fwg5B","f36YN"], "f36YN", "parcelRequire2ce3")

//# sourceMappingURL=index.js.map
