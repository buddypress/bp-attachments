!function(){function e(e,t,n,r){Object.defineProperty(e,t,{get:n,set:r,enumerable:!0,configurable:!0})}function t(e){return e&&e.__esModule?e.default:e}var n="undefined"!=typeof globalThis?globalThis:"undefined"!=typeof self?self:"undefined"!=typeof window?window:"undefined"!=typeof global?global:{},r={},o={},i=n.parcelRequire2ce3;null==i&&((i=function(e){if(e in r)return r[e].exports;if(e in o){var t=o[e];delete o[e];var n={id:e,exports:{}};return r[e]=n,t.call(n.exports,n,n.exports),n.exports}var i=new Error("Cannot find module '"+e+"'");throw i.code="MODULE_NOT_FOUND",i}).register=function(e,t){o[e]=t},n.parcelRequire2ce3=i),i.register("9UMFO",(function(t,n){var r,o,a,s,c,u,p,l,h,d,f,m,g,v,y,w,C,S,x,b,_,R,E,z,P,M,O,D;e(t.exports,"Fragment",(function(){return r}),(function(e){return r=e})),e(t.exports,"StrictMode",(function(){return o}),(function(e){return o=e})),e(t.exports,"Profiler",(function(){return a}),(function(e){return a=e})),e(t.exports,"Suspense",(function(){return s}),(function(e){return s=e})),e(t.exports,"Children",(function(){return c}),(function(e){return c=e})),e(t.exports,"Component",(function(){return u}),(function(e){return u=e})),e(t.exports,"PureComponent",(function(){return p}),(function(e){return p=e})),e(t.exports,"__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED",(function(){return l}),(function(e){return l=e})),e(t.exports,"cloneElement",(function(){return h}),(function(e){return h=e})),e(t.exports,"createContext",(function(){return d}),(function(e){return d=e})),e(t.exports,"createElement",(function(){return f}),(function(e){return f=e})),e(t.exports,"createFactory",(function(){return m}),(function(e){return m=e})),e(t.exports,"createRef",(function(){return g}),(function(e){return g=e})),e(t.exports,"forwardRef",(function(){return v}),(function(e){return v=e})),e(t.exports,"isValidElement",(function(){return y}),(function(e){return y=e})),e(t.exports,"lazy",(function(){return w}),(function(e){return w=e})),e(t.exports,"memo",(function(){return C}),(function(e){return C=e})),e(t.exports,"useCallback",(function(){return S}),(function(e){return S=e})),e(t.exports,"useContext",(function(){return x}),(function(e){return x=e})),e(t.exports,"useDebugValue",(function(){return b}),(function(e){return b=e})),e(t.exports,"useEffect",(function(){return _}),(function(e){return _=e})),e(t.exports,"useImperativeHandle",(function(){return R}),(function(e){return R=e})),e(t.exports,"useLayoutEffect",(function(){return E}),(function(e){return E=e})),e(t.exports,"useMemo",(function(){return z}),(function(e){return z=e})),e(t.exports,"useReducer",(function(){return P}),(function(e){return P=e})),e(t.exports,"useRef",(function(){return M}),(function(e){return M=e})),e(t.exports,"useState",(function(){return O}),(function(e){return O=e})),e(t.exports,"version",(function(){return D}),(function(e){return D=e}));var A=i("8yyWS"),T=60103,W=60106;r=60107,o=60108,a=60114;var j=60109,N=60110,F=60112;s=60113;var L=60115,I=60116;if("function"==typeof Symbol&&Symbol.for){var k=Symbol.for;T=k("react.element"),W=k("react.portal"),r=k("react.fragment"),o=k("react.strict_mode"),a=k("react.profiler"),j=k("react.provider"),N=k("react.context"),F=k("react.forward_ref"),s=k("react.suspense"),L=k("react.memo"),I=k("react.lazy")}var U="function"==typeof Symbol&&Symbol.iterator;function H(e){for(var t="https://reactjs.org/docs/error-decoder.html?invariant="+e,n=1;n<arguments.length;n++)t+="&args[]="+encodeURIComponent(arguments[n]);return"Minified React error #"+e+"; visit "+t+" for the full message or use the non-minified dev environment for full errors and additional helpful warnings."}var Z={isMounted:function(){return!1},enqueueForceUpdate:function(){},enqueueReplaceState:function(){},enqueueSetState:function(){}},$={};function q(e,t,n){this.props=e,this.context=t,this.refs=$,this.updater=n||Z}function V(){}function X(e,t,n){this.props=e,this.context=t,this.refs=$,this.updater=n||Z}q.prototype.isReactComponent={},q.prototype.setState=function(e,t){if("object"!=typeof e&&"function"!=typeof e&&null!=e)throw Error(H(85));this.updater.enqueueSetState(this,e,t,"setState")},q.prototype.forceUpdate=function(e){this.updater.enqueueForceUpdate(this,e,"forceUpdate")},V.prototype=q.prototype;var Y=X.prototype=new V;Y.constructor=X,A(Y,q.prototype),Y.isPureReactComponent=!0;var B={current:null},G=Object.prototype.hasOwnProperty,J={key:!0,ref:!0,__self:!0,__source:!0};function K(e,t,n){var r,o={},i=null,a=null;if(null!=t)for(r in void 0!==t.ref&&(a=t.ref),void 0!==t.key&&(i=""+t.key),t)G.call(t,r)&&!J.hasOwnProperty(r)&&(o[r]=t[r]);var s=arguments.length-2;if(1===s)o.children=n;else if(1<s){for(var c=Array(s),u=0;u<s;u++)c[u]=arguments[u+2];o.children=c}if(e&&e.defaultProps)for(r in s=e.defaultProps)void 0===o[r]&&(o[r]=s[r]);return{$$typeof:T,type:e,key:i,ref:a,props:o,_owner:B.current}}function Q(e){return"object"==typeof e&&null!==e&&e.$$typeof===T}var ee=/\/+/g;function te(e,t){return"object"==typeof e&&null!==e&&null!=e.key?function(e){var t={"=":"=0",":":"=2"};return"$"+e.replace(/[=:]/g,(function(e){return t[e]}))}(""+e.key):t.toString(36)}function ne(e,t,n,r,o){var i=typeof e;"undefined"!==i&&"boolean"!==i||(e=null);var a,s=!1;if(null===e)s=!0;else switch(i){case"string":case"number":s=!0;break;case"object":switch(e.$$typeof){case T:case W:s=!0}}if(s)return o=o(s=e),e=""===r?"."+te(s,0):r,Array.isArray(o)?(n="",null!=e&&(n=e.replace(ee,"$&/")+"/"),ne(o,t,n,"",(function(e){return e}))):null!=o&&(Q(o)&&(o=function(e,t){return{$$typeof:T,type:e.type,key:t,ref:e.ref,props:e.props,_owner:e._owner}}(o,n+(!o.key||s&&s.key===o.key?"":(""+o.key).replace(ee,"$&/")+"/")+e)),t.push(o)),1;if(s=0,r=""===r?".":r+":",Array.isArray(e))for(var c=0;c<e.length;c++){var u=r+te(i=e[c],c);s+=ne(i,t,n,u,o)}else if("function"==typeof(u=null===(a=e)||"object"!=typeof a?null:"function"==typeof(a=U&&a[U]||a["@@iterator"])?a:null))for(e=u.call(e),c=0;!(i=e.next()).done;)s+=ne(i=i.value,t,n,u=r+te(i,c++),o);else if("object"===i)throw t=""+e,Error(H(31,"[object Object]"===t?"object with keys {"+Object.keys(e).join(", ")+"}":t));return s}function re(e,t,n){if(null==e)return e;var r=[],o=0;return ne(e,r,"","",(function(e){return t.call(n,e,o++)})),r}function oe(e){if(-1===e._status){var t=e._result;t=t(),e._status=0,e._result=t,t.then((function(t){0===e._status&&(t=t.default,e._status=1,e._result=t)}),(function(t){0===e._status&&(e._status=2,e._result=t)}))}if(1===e._status)return e._result;throw e._result}var ie={current:null};function ae(){var e=ie.current;if(null===e)throw Error(H(321));return e}c={map:re,forEach:function(e,t,n){re(e,(function(){t.apply(this,arguments)}),n)},count:function(e){var t=0;return re(e,(function(){t++})),t},toArray:function(e){return re(e,(function(e){return e}))||[]},only:function(e){if(!Q(e))throw Error(H(143));return e}},u=q,p=X,l={ReactCurrentDispatcher:ie,ReactCurrentBatchConfig:{transition:0},ReactCurrentOwner:B,IsSomeRendererActing:{current:!1},assign:A},h=function(e,t,n){if(null==e)throw Error(H(267,e));var r=A({},e.props),o=e.key,i=e.ref,a=e._owner;if(null!=t){if(void 0!==t.ref&&(i=t.ref,a=B.current),void 0!==t.key&&(o=""+t.key),e.type&&e.type.defaultProps)var s=e.type.defaultProps;for(c in t)G.call(t,c)&&!J.hasOwnProperty(c)&&(r[c]=void 0===t[c]&&void 0!==s?s[c]:t[c])}var c=arguments.length-2;if(1===c)r.children=n;else if(1<c){s=Array(c);for(var u=0;u<c;u++)s[u]=arguments[u+2];r.children=s}return{$$typeof:T,type:e.type,key:o,ref:i,props:r,_owner:a}},d=function(e,t){return void 0===t&&(t=null),(e={$$typeof:N,_calculateChangedBits:t,_currentValue:e,_currentValue2:e,_threadCount:0,Provider:null,Consumer:null}).Provider={$$typeof:j,_context:e},e.Consumer=e},f=K,m=function(e){var t=K.bind(null,e);return t.type=e,t},g=function(){return{current:null}},v=function(e){return{$$typeof:F,render:e}},y=Q,w=function(e){return{$$typeof:I,_payload:{_status:-1,_result:e},_init:oe}},C=function(e,t){return{$$typeof:L,type:e,compare:void 0===t?null:t}},S=function(e,t){return ae().useCallback(e,t)},x=function(e,t){return ae().useContext(e,t)},b=function(){},_=function(e,t){return ae().useEffect(e,t)},R=function(e,t,n){return ae().useImperativeHandle(e,t,n)},E=function(e,t){return ae().useLayoutEffect(e,t)},z=function(e,t){return ae().useMemo(e,t)},P=function(e,t,n){return ae().useReducer(e,t,n)},M=function(e){return ae().useRef(e)},O=function(e){return ae().useState(e)},D="17.0.2"})),i.register("8yyWS",(function(e,t){
/*
object-assign
(c) Sindre Sorhus
@license MIT
*/
"use strict";var n=Object.getOwnPropertySymbols,r=Object.prototype.hasOwnProperty,o=Object.prototype.propertyIsEnumerable;function i(e){if(null==e)throw new TypeError("Object.assign cannot be called with null or undefined");return Object(e)}e.exports=function(){try{if(!Object.assign)return!1;var e=new String("abc");if(e[5]="de","5"===Object.getOwnPropertyNames(e)[0])return!1;for(var t={},n=0;n<10;n++)t["_"+String.fromCharCode(n)]=n;if("0123456789"!==Object.getOwnPropertyNames(t).map((function(e){return t[e]})).join(""))return!1;var r={};return"abcdefghijklmnopqrst".split("").forEach((function(e){r[e]=e})),"abcdefghijklmnopqrst"===Object.keys(Object.assign({},r)).join("")}catch(e){return!1}}()?Object.assign:function(e,t){for(var a,s,c=i(e),u=1;u<arguments.length;u++){for(var p in a=Object(arguments[u]))r.call(a,p)&&(c[p]=a[p]);if(n){s=n(a);for(var l=0;l<s.length;l++)o.call(a,s[l])&&(c[s[l]]=a[s[l]])}}return c}})),i.register("4I199",(function(e,t){"use strict";var n=i("4DVgJ"),r=i("16bnu");function o(e){var t=0,n=0,r=0,o=0;return"detail"in e&&(n=e.detail),"wheelDelta"in e&&(n=-e.wheelDelta/120),"wheelDeltaY"in e&&(n=-e.wheelDeltaY/120),"wheelDeltaX"in e&&(t=-e.wheelDeltaX/120),"axis"in e&&e.axis===e.HORIZONTAL_AXIS&&(t=n,n=0),r=10*t,o=10*n,"deltaY"in e&&(o=e.deltaY),"deltaX"in e&&(r=e.deltaX),(r||o)&&e.deltaMode&&(1==e.deltaMode?(r*=40,o*=40):(r*=800,o*=800)),r&&!t&&(t=r<1?-1:1),o&&!n&&(n=o<1?-1:1),{spinX:t,spinY:n,pixelX:r,pixelY:o}}o.getEventType=function(){return n.firefox()?"DOMMouseScroll":r("wheel")?"wheel":"mousewheel"},e.exports=o})),i.register("4DVgJ",(function(e,t){var n,r,o,i,a,s,c,u,p,l,h,d,f,m,g,v=!1;function y(){if(!v){v=!0;var e=navigator.userAgent,t=/(?:MSIE.(\d+\.\d+))|(?:(?:Firefox|GranParadiso|Iceweasel).(\d+\.\d+))|(?:Opera(?:.+Version.|.)(\d+\.\d+))|(?:AppleWebKit.(\d+(?:\.\d+)?))|(?:Trident\/\d+\.\d+.*rv:(\d+\.\d+))/.exec(e),y=/(Mac OS X)|(Windows)|(Linux)/.exec(e);if(d=/\b(iPhone|iP[ao]d)/.exec(e),f=/\b(iP[ao]d)/.exec(e),l=/Android/i.exec(e),m=/FBAN\/\w+;/i.exec(e),g=/Mobile/i.exec(e),h=!!/Win64/.exec(e),t){(n=t[1]?parseFloat(t[1]):t[5]?parseFloat(t[5]):NaN)&&document&&document.documentMode&&(n=document.documentMode);var w=/(?:Trident\/(\d+.\d+))/.exec(e);s=w?parseFloat(w[1])+4:n,r=t[2]?parseFloat(t[2]):NaN,o=t[3]?parseFloat(t[3]):NaN,(i=t[4]?parseFloat(t[4]):NaN)?(t=/(?:Chrome\/(\d+\.\d+))/.exec(e),a=t&&t[1]?parseFloat(t[1]):NaN):a=NaN}else n=r=o=a=i=NaN;if(y){if(y[1]){var C=/(?:Mac OS X (\d+(?:[._]\d+)?))/.exec(e);c=!C||parseFloat(C[1].replace("_","."))}else c=!1;u=!!y[2],p=!!y[3]}else c=u=p=!1}}var w={ie:function(){return y()||n},ieCompatibilityMode:function(){return y()||s>n},ie64:function(){return w.ie()&&h},firefox:function(){return y()||r},opera:function(){return y()||o},webkit:function(){return y()||i},safari:function(){return w.webkit()},chrome:function(){return y()||a},windows:function(){return y()||u},osx:function(){return y()||c},linux:function(){return y()||p},iphone:function(){return y()||d},mobile:function(){return y()||d||f||l||g},nativeApp:function(){return y()||m},android:function(){return y()||l},ipad:function(){return y()||f}};e.exports=w})),i.register("16bnu",(function(e,t){"use strict";var n,r=i("8WUuC");r.canUseDOM&&(n=document.implementation&&document.implementation.hasFeature&&!0!==document.implementation.hasFeature("",""))
/**
 * Checks if an event is supported in the current execution environment.
 *
 * NOTE: This will not work correctly for non-generic events such as `change`,
 * `reset`, `load`, `error`, and `select`.
 *
 * Borrows from Modernizr.
 *
 * @param {string} eventNameSuffix Event name, e.g. "click".
 * @param {?boolean} capture Check if the capture phase is supported.
 * @return {boolean} True if the event is supported.
 * @internal
 * @license Modernizr 3.0.0pre (Custom Build) | MIT
 */,e.exports=function(e,t){if(!r.canUseDOM||t&&!("addEventListener"in document))return!1;var o="on"+e,i=o in document;if(!i){var a=document.createElement("div");a.setAttribute(o,"return;"),i="function"==typeof a[o]}return!i&&n&&"wheel"===e&&(i=document.implementation.hasFeature("Events.wheel","3.0")),i}})),i.register("8WUuC",(function(e,t){"use strict";var n=!("undefined"==typeof window||!window.document||!window.document.createElement),r={canUseDOM:n,canUseWorkers:"undefined"!=typeof Worker,canUseEventListeners:n&&!(!window.addEventListener&&!window.attachEvent),canUseViewport:n&&!!window.screen,isInWorker:!n};e.exports=r}));const{components:{DropZone:a,FormFileUpload:s},element:{createElement:c},i18n:{__:u}}=wp;var p=e=>{let{settings:t,onSelectedImage:n}=e;const{allowedExtTypes:r}=t;return c("div",{className:"uploader-container enabled"},c(a,{label:u("Drop your image here.","bp-attachments"),onFilesDrop:e=>n(e),className:"uploader-inline"}),c("div",{className:"dropzone-label"},c("h2",{className:"upload-instructions drop-instructions"},u("Drop an image here","bp-attachments")),c("p",{className:"upload-instructions drop-instructions"},u("or","bp-attachments")),c(s,{onChange:e=>n(e),multiple:!1,accept:"."+r.join(", ."),className:"browser button button-hero"},u("Select an image","bp-attachments"))))},l=function(e,t){return(l=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(e,t){e.__proto__=t}||function(e,t){for(var n in t)Object.prototype.hasOwnProperty.call(t,n)&&(e[n]=t[n])})(e,t)};
/*! *****************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */function h(e,t){function n(){this.constructor=e}l(e,t),e.prototype=null===t?Object.create(t):(n.prototype=t.prototype,new n)}var d=function(){return d=Object.assign||function(e){for(var t,n=1,r=arguments.length;n<r;n++)for(var o in t=arguments[n])Object.prototype.hasOwnProperty.call(t,o)&&(e[o]=t[o]);return e},d.apply(this,arguments)};Object.create;Object.create;var f;f=i("9UMFO");var m;function g(e,t,n,r,o,i){void 0===i&&(i=0);var a=z(e,t,i),s=a.width,c=a.height,u=Math.min(s,n),p=Math.min(c,r);return u>p*o?{width:p*o,height:p}:{width:u,height:u/o}}function v(e,t,n,r,o){void 0===o&&(o=0);var i=z(t.width,t.height,o),a=i.width,s=i.height;return{x:y(e.x,a,n.width,r),y:y(e.y,s,n.height,r)}}function y(e,t,n,r){var o=t*r/2-n/2;return P(e,-o,o)}function w(e,t){return Math.sqrt(Math.pow(e.y-t.y,2)+Math.pow(e.x-t.x,2))}function C(e,t){return 180*Math.atan2(t.y-e.y,t.x-e.x)/Math.PI}function S(e,t,n,r,o,i,a){void 0===i&&(i=0),void 0===a&&(a=!0);var s=a?x:b,c=z(t.width,t.height,i),u=z(t.naturalWidth,t.naturalHeight,i),p={x:s(100,((c.width-n.width/o)/2-e.x/o)/c.width*100),y:s(100,((c.height-n.height/o)/2-e.y/o)/c.height*100),width:s(100,n.width/c.width*100/o),height:s(100,n.height/c.height*100/o)},l=Math.round(s(u.width,p.width*u.width/100)),h=Math.round(s(u.height,p.height*u.height/100)),f=u.width>=u.height*r?{width:Math.round(h*r),height:h}:{width:l,height:Math.round(l/r)};return{croppedAreaPercentages:p,croppedAreaPixels:d(d({},f),{x:Math.round(s(u.width-f.width,p.x*u.width/100)),y:Math.round(s(u.height-f.height,p.y*u.height/100))})}}function x(e,t){return Math.min(e,Math.max(0,t))}function b(e,t){return t}function _(e,t,n,r,o,i){var a=z(t.width,t.height,n),s=P(r.width/a.width*(100/e.width),o,i);return{crop:{x:s*a.width/2-r.width/2-a.width*s*(e.x/100),y:s*a.height/2-r.height/2-a.height*s*(e.y/100)},zoom:s}}function R(e,t,n,r,o,i){void 0===n&&(n=0);var a=z(t.naturalWidth,t.naturalHeight,n),s=P(function(e,t,n){var r=function(e){return e.width>e.height?e.width/e.naturalWidth:e.height/e.naturalHeight}(t);return n.height>n.width?n.height/(e.height*r):n.width/(e.width*r)}(e,t,r),o,i),c=r.height>r.width?r.height/e.height:r.width/e.width;return{crop:{x:((a.width-e.width)/2-e.x)*c,y:((a.height-e.height)/2-e.y)*c},zoom:s}}function E(e,t){return{x:(t.x+e.x)/2,y:(t.y+e.y)/2}}function z(e,t,n){var r=n*Math.PI/180;return{width:Math.abs(Math.cos(r)*e)+Math.abs(Math.sin(r)*t),height:Math.abs(Math.sin(r)*e)+Math.abs(Math.cos(r)*t)}}function P(e,t,n){return Math.min(Math.max(e,t),n)}function M(){for(var e=[],t=0;t<arguments.length;t++)e[t]=arguments[t];return e.filter((function(e){return"string"==typeof e&&e.length>0})).join(" ").trim()}m=i("4I199");var O=function(e){function n(){var r=null!==e&&e.apply(this,arguments)||this;return r.imageRef=t(f).createRef(),r.videoRef=t(f).createRef(),r.containerRef=null,r.styleRef=null,r.containerRect=null,r.mediaSize={width:0,height:0,naturalWidth:0,naturalHeight:0},r.dragStartPosition={x:0,y:0},r.dragStartCrop={x:0,y:0},r.lastPinchDistance=0,r.lastPinchRotation=0,r.rafDragTimeout=null,r.rafPinchTimeout=null,r.wheelTimer=null,r.state={cropSize:null,hasWheelJustStarted:!1},r.preventZoomSafari=function(e){return e.preventDefault()},r.cleanEvents=function(){document.removeEventListener("mousemove",r.onMouseMove),document.removeEventListener("mouseup",r.onDragStopped),document.removeEventListener("touchmove",r.onTouchMove),document.removeEventListener("touchend",r.onDragStopped)},r.clearScrollEvent=function(){r.containerRef&&r.containerRef.removeEventListener("wheel",r.onWheel),r.wheelTimer&&clearTimeout(r.wheelTimer)},r.onMediaLoad=function(){var e=r.computeSizes();e&&(r.emitCropData(),r.setInitialCrop(e)),r.props.onMediaLoaded&&r.props.onMediaLoaded(r.mediaSize)},r.setInitialCrop=function(e){if(r.props.initialCroppedAreaPercentages){var t=_(r.props.initialCroppedAreaPercentages,r.mediaSize,r.props.rotation,e,r.props.minZoom,r.props.maxZoom),n=t.crop,o=t.zoom;r.props.onCropChange(n),r.props.onZoomChange&&r.props.onZoomChange(o)}else if(r.props.initialCroppedAreaPixels){var i=R(r.props.initialCroppedAreaPixels,r.mediaSize,r.props.rotation,e,r.props.minZoom,r.props.maxZoom);n=i.crop,o=i.zoom;r.props.onCropChange(n),r.props.onZoomChange&&r.props.onZoomChange(o)}},r.computeSizes=function(){var e,t,n,o,i,a,s=r.imageRef.current||r.videoRef.current;if(s&&r.containerRef){r.containerRect=r.containerRef.getBoundingClientRect();var c=r.containerRect.width/r.containerRect.height,u=(null===(e=r.imageRef.current)||void 0===e?void 0:e.naturalWidth)||(null===(t=r.videoRef.current)||void 0===t?void 0:t.videoWidth)||0,p=(null===(n=r.imageRef.current)||void 0===n?void 0:n.naturalHeight)||(null===(o=r.videoRef.current)||void 0===o?void 0:o.videoHeight)||0,l=u/p,h=void 0;if(s.offsetWidth<u||s.offsetHeight<p)switch(r.props.objectFit){default:case"contain":h=c>l?{width:r.containerRect.height*l,height:r.containerRect.height}:{width:r.containerRect.width,height:r.containerRect.width/l};break;case"horizontal-cover":h={width:r.containerRect.width,height:r.containerRect.width/l};break;case"vertical-cover":h={width:r.containerRect.height*l,height:r.containerRect.height};break;case"auto-cover":h=u>p?{width:r.containerRect.width,height:r.containerRect.width/l}:{width:r.containerRect.height*l,height:r.containerRect.height}}else h={width:s.offsetWidth,height:s.offsetHeight};r.mediaSize=d(d({},h),{naturalWidth:u,naturalHeight:p});var f=r.props.cropSize?r.props.cropSize:g(r.mediaSize.width,r.mediaSize.height,r.containerRect.width,r.containerRect.height,r.props.aspect,r.props.rotation);return(null===(i=r.state.cropSize)||void 0===i?void 0:i.height)===f.height&&(null===(a=r.state.cropSize)||void 0===a?void 0:a.width)===f.width||r.props.onCropSizeChange&&r.props.onCropSizeChange(f),r.setState({cropSize:f},r.recomputeCropPosition),f}},r.onMouseDown=function(e){e.preventDefault(),document.addEventListener("mousemove",r.onMouseMove),document.addEventListener("mouseup",r.onDragStopped),r.onDragStart(n.getMousePoint(e))},r.onMouseMove=function(e){return r.onDrag(n.getMousePoint(e))},r.onTouchStart=function(e){r.props.onTouchRequest&&!r.props.onTouchRequest(e)||(document.addEventListener("touchmove",r.onTouchMove,{passive:!1}),document.addEventListener("touchend",r.onDragStopped),2===e.touches.length?r.onPinchStart(e):1===e.touches.length&&r.onDragStart(n.getTouchPoint(e.touches[0])))},r.onTouchMove=function(e){e.preventDefault(),2===e.touches.length?r.onPinchMove(e):1===e.touches.length&&r.onDrag(n.getTouchPoint(e.touches[0]))},r.onDragStart=function(e){var t,n,o=e.x,i=e.y;r.dragStartPosition={x:o,y:i},r.dragStartCrop=d({},r.props.crop),null===(n=(t=r.props).onInteractionStart)||void 0===n||n.call(t)},r.onDrag=function(e){var t=e.x,n=e.y;r.rafDragTimeout&&window.cancelAnimationFrame(r.rafDragTimeout),r.rafDragTimeout=window.requestAnimationFrame((function(){if(r.state.cropSize&&void 0!==t&&void 0!==n){var e=t-r.dragStartPosition.x,o=n-r.dragStartPosition.y,i={x:r.dragStartCrop.x+e,y:r.dragStartCrop.y+o},a=r.props.restrictPosition?v(i,r.mediaSize,r.state.cropSize,r.props.zoom,r.props.rotation):i;r.props.onCropChange(a)}}))},r.onDragStopped=function(){var e,t;r.cleanEvents(),r.emitCropData(),null===(t=(e=r.props).onInteractionEnd)||void 0===t||t.call(e)},r.onWheel=function(e){if(!r.props.onWheelRequest||r.props.onWheelRequest(e)){e.preventDefault();var o=n.getMousePoint(e),i=t(m)(e).pixelY,a=r.props.zoom-i*r.props.zoomSpeed/200;r.setNewZoom(a,o,{shouldUpdatePosition:!0}),r.state.hasWheelJustStarted||r.setState({hasWheelJustStarted:!0},(function(){var e,t;return null===(t=(e=r.props).onInteractionStart)||void 0===t?void 0:t.call(e)})),r.wheelTimer&&clearTimeout(r.wheelTimer),r.wheelTimer=window.setTimeout((function(){return r.setState({hasWheelJustStarted:!1},(function(){var e,t;return null===(t=(e=r.props).onInteractionEnd)||void 0===t?void 0:t.call(e)}))}),250)}},r.getPointOnContainer=function(e){var t=e.x,n=e.y;if(!r.containerRect)throw new Error("The Cropper is not mounted");return{x:r.containerRect.width/2-(t-r.containerRect.left),y:r.containerRect.height/2-(n-r.containerRect.top)}},r.getPointOnMedia=function(e){var t=e.x,n=e.y,o=r.props,i=o.crop,a=o.zoom;return{x:(t+i.x)/a,y:(n+i.y)/a}},r.setNewZoom=function(e,t,n){var o=(void 0===n?{}:n).shouldUpdatePosition,i=void 0===o||o;if(r.state.cropSize&&r.props.onZoomChange){var a=r.getPointOnContainer(t),s=r.getPointOnMedia(a),c=P(e,r.props.minZoom,r.props.maxZoom),u={x:s.x*c-a.x,y:s.y*c-a.y};if(i){var p=r.props.restrictPosition?v(u,r.mediaSize,r.state.cropSize,c,r.props.rotation):u;r.props.onCropChange(p)}r.props.onZoomChange(c)}},r.getCropData=function(){return r.state.cropSize?S(r.props.restrictPosition?v(r.props.crop,r.mediaSize,r.state.cropSize,r.props.zoom,r.props.rotation):r.props.crop,r.mediaSize,r.state.cropSize,r.getAspect(),r.props.zoom,r.props.rotation,r.props.restrictPosition):null},r.emitCropData=function(){var e=r.getCropData();if(e){var t=e.croppedAreaPercentages,n=e.croppedAreaPixels;r.props.onCropComplete&&r.props.onCropComplete(t,n),r.props.onCropAreaChange&&r.props.onCropAreaChange(t,n)}},r.emitCropAreaChange=function(){var e=r.getCropData();if(e){var t=e.croppedAreaPercentages,n=e.croppedAreaPixels;r.props.onCropAreaChange&&r.props.onCropAreaChange(t,n)}},r.recomputeCropPosition=function(){if(r.state.cropSize){var e=r.props.restrictPosition?v(r.props.crop,r.mediaSize,r.state.cropSize,r.props.zoom,r.props.rotation):r.props.crop;r.props.onCropChange(e),r.emitCropData()}},r}return h(n,e),n.prototype.componentDidMount=function(){window.addEventListener("resize",this.computeSizes),this.containerRef&&(this.props.zoomWithScroll&&this.containerRef.addEventListener("wheel",this.onWheel,{passive:!1}),this.containerRef.addEventListener("gesturestart",this.preventZoomSafari),this.containerRef.addEventListener("gesturechange",this.preventZoomSafari)),this.props.disableAutomaticStylesInjection||(this.styleRef=document.createElement("style"),this.styleRef.setAttribute("type","text/css"),this.props.nonce&&this.styleRef.setAttribute("nonce",this.props.nonce),this.styleRef.innerHTML=".reactEasyCrop_Container {\n  position: absolute;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  overflow: hidden;\n  user-select: none;\n  touch-action: none;\n  cursor: move;\n  display: flex;\n  justify-content: center;\n  align-items: center;\n}\n\n.reactEasyCrop_Image,\n.reactEasyCrop_Video {\n  will-change: transform; /* this improves performances and prevent painting issues on iOS Chrome */\n}\n\n.reactEasyCrop_Contain {\n  max-width: 100%;\n  max-height: 100%;\n  margin: auto;\n  position: absolute;\n  top: 0;\n  bottom: 0;\n  left: 0;\n  right: 0;\n}\n.reactEasyCrop_Cover_Horizontal {\n  width: 100%;\n  height: auto;\n}\n.reactEasyCrop_Cover_Vertical {\n  width: auto;\n  height: 100%;\n}\n\n.reactEasyCrop_CropArea {\n  position: absolute;\n  left: 50%;\n  top: 50%;\n  transform: translate(-50%, -50%);\n  border: 1px solid rgba(255, 255, 255, 0.5);\n  box-sizing: border-box;\n  box-shadow: 0 0 0 9999em;\n  color: rgba(0, 0, 0, 0.5);\n  overflow: hidden;\n}\n\n.reactEasyCrop_CropAreaRound {\n  border-radius: 50%;\n}\n\n.reactEasyCrop_CropAreaGrid::before {\n  content: ' ';\n  box-sizing: border-box;\n  position: absolute;\n  border: 1px solid rgba(255, 255, 255, 0.5);\n  top: 0;\n  bottom: 0;\n  left: 33.33%;\n  right: 33.33%;\n  border-top: 0;\n  border-bottom: 0;\n}\n\n.reactEasyCrop_CropAreaGrid::after {\n  content: ' ';\n  box-sizing: border-box;\n  position: absolute;\n  border: 1px solid rgba(255, 255, 255, 0.5);\n  top: 33.33%;\n  bottom: 33.33%;\n  left: 0;\n  right: 0;\n  border-left: 0;\n  border-right: 0;\n}\n",document.head.appendChild(this.styleRef)),this.imageRef.current&&this.imageRef.current.complete&&this.onMediaLoad(),this.props.setImageRef&&this.props.setImageRef(this.imageRef),this.props.setVideoRef&&this.props.setVideoRef(this.videoRef)},n.prototype.componentWillUnmount=function(){var e;window.removeEventListener("resize",this.computeSizes),this.containerRef&&(this.containerRef.removeEventListener("gesturestart",this.preventZoomSafari),this.containerRef.removeEventListener("gesturechange",this.preventZoomSafari)),this.styleRef&&(null===(e=this.styleRef.parentNode)||void 0===e||e.removeChild(this.styleRef)),this.cleanEvents(),this.props.zoomWithScroll&&this.clearScrollEvent()},n.prototype.componentDidUpdate=function(e){var t,n,r,o,i,a,s,c,u;e.rotation!==this.props.rotation?(this.computeSizes(),this.recomputeCropPosition()):e.aspect!==this.props.aspect?this.computeSizes():e.zoom!==this.props.zoom?this.recomputeCropPosition():(null===(t=e.cropSize)||void 0===t?void 0:t.height)!==(null===(n=this.props.cropSize)||void 0===n?void 0:n.height)||(null===(r=e.cropSize)||void 0===r?void 0:r.width)!==(null===(o=this.props.cropSize)||void 0===o?void 0:o.width)?this.computeSizes():(null===(i=e.crop)||void 0===i?void 0:i.x)===(null===(a=this.props.crop)||void 0===a?void 0:a.x)&&(null===(s=e.crop)||void 0===s?void 0:s.y)===(null===(c=this.props.crop)||void 0===c?void 0:c.y)||this.emitCropAreaChange(),e.zoomWithScroll!==this.props.zoomWithScroll&&this.containerRef&&(this.props.zoomWithScroll?this.containerRef.addEventListener("wheel",this.onWheel,{passive:!1}):this.clearScrollEvent()),e.video!==this.props.video&&(null===(u=this.videoRef.current)||void 0===u||u.load())},n.prototype.getAspect=function(){var e=this.props,t=e.cropSize,n=e.aspect;return t?t.width/t.height:n},n.prototype.onPinchStart=function(e){var t=n.getTouchPoint(e.touches[0]),r=n.getTouchPoint(e.touches[1]);this.lastPinchDistance=w(t,r),this.lastPinchRotation=C(t,r),this.onDragStart(E(t,r))},n.prototype.onPinchMove=function(e){var t=this,r=n.getTouchPoint(e.touches[0]),o=n.getTouchPoint(e.touches[1]),i=E(r,o);this.onDrag(i),this.rafPinchTimeout&&window.cancelAnimationFrame(this.rafPinchTimeout),this.rafPinchTimeout=window.requestAnimationFrame((function(){var e=w(r,o),n=t.props.zoom*(e/t.lastPinchDistance);t.setNewZoom(n,i,{shouldUpdatePosition:!1}),t.lastPinchDistance=e;var a=C(r,o),s=t.props.rotation+(a-t.lastPinchRotation);t.props.onRotationChange&&t.props.onRotationChange(s),t.lastPinchRotation=a}))},n.prototype.render=function(){var e=this,n=this.props,r=n.image,o=n.video,i=n.mediaProps,a=n.transform,s=n.crop,c=s.x,u=s.y,p=n.rotation,l=n.zoom,h=n.cropShape,m=n.showGrid,g=n.style,v=g.containerStyle,y=g.cropAreaStyle,w=g.mediaStyle,C=n.classes,S=C.containerClassName,x=C.cropAreaClassName,b=C.mediaClassName,_=n.objectFit;return t(f).createElement("div",{onMouseDown:this.onMouseDown,onTouchStart:this.onTouchStart,ref:function(t){return e.containerRef=t},"data-testid":"container",style:v,className:M("reactEasyCrop_Container",S)},r?t(f).createElement("img",d({alt:"",className:M("reactEasyCrop_Image","contain"===_&&"reactEasyCrop_Contain","horizontal-cover"===_&&"reactEasyCrop_Cover_Horizontal","vertical-cover"===_&&"reactEasyCrop_Cover_Vertical","auto-cover"===_&&(this.mediaSize.naturalWidth>this.mediaSize.naturalHeight?"reactEasyCrop_Cover_Horizontal":"reactEasyCrop_Cover_Vertical"),b)},i,{src:r,ref:this.imageRef,style:d(d({},w),{transform:a||"translate("+c+"px, "+u+"px) rotate("+p+"deg) scale("+l+")"}),onLoad:this.onMediaLoad})):o&&t(f).createElement("video",d({autoPlay:!0,loop:!0,muted:!0,className:M("reactEasyCrop_Video","contain"===_&&"reactEasyCrop_Contain","horizontal-cover"===_&&"reactEasyCrop_Cover_Horizontal","vertical-cover"===_&&"reactEasyCrop_Cover_Vertical","auto-cover"===_&&(this.mediaSize.naturalWidth>this.mediaSize.naturalHeight?"reactEasyCrop_Cover_Horizontal":"reactEasyCrop_Cover_Vertical"),b)},i,{ref:this.videoRef,onLoadedMetadata:this.onMediaLoad,style:d(d({},w),{transform:a||"translate("+c+"px, "+u+"px) rotate("+p+"deg) scale("+l+")"}),controls:!1}),(Array.isArray(o)?o:[{src:o}]).map((function(e){return t(f).createElement("source",d({key:e.src},e))}))),this.state.cropSize&&t(f).createElement("div",{style:d(d({},y),{width:this.state.cropSize.width,height:this.state.cropSize.height}),"data-testid":"cropper",className:M("reactEasyCrop_CropArea","round"===h&&"reactEasyCrop_CropAreaRound",m&&"reactEasyCrop_CropAreaGrid",x)}))},n.defaultProps={zoom:1,rotation:0,aspect:4/3,maxZoom:3,minZoom:1,cropShape:"rect",objectFit:"contain",showGrid:!0,style:{},classes:{},mediaProps:{},zoomSpeed:1,restrictPosition:!0,zoomWithScroll:!0},n.getMousePoint=function(e){return{x:Number(e.clientX),y:Number(e.clientY)}},n.getTouchPoint=function(e){return{x:Number(e.clientX),y:Number(e.clientY)}},n}(t(f).Component),D=O;const{element:{Component:A,createElement:T,createPortal:W}}=wp;var j=class extends A{render(){const{selector:e}=this.props;return W(this.props.children,document.querySelector("#"+e))}};const{components:{Button:N,RangeControl:F},element:{createElement:L,Fragment:I,useCallback:k,useState:U},i18n:{__:H}}=wp;var Z=e=>{let{image:t,originalSize:n,onCropEdit:r,onSaveEdits:o}=e;const[i,a]=U({x:0,y:0}),[s,c]=U(1),{naturalHeight:u,naturalWidth:p}=n,l=u&&p&&u>p?"horizontal-cover":"vertical-cover",h=k(((e,t)=>{r(e,t,null)}),[]),d=k((e=>{r(null,null,e),c(e)}),[]);return L(I,null,L(D,{image:t,objectFit:l,crop:i,zoom:s,aspect:1,onCropChange:a,onCropComplete:h,onZoomChange:d}),L(j,{selector:"bp-avatar-editor-controls"},L(F,{label:H("Zoom","bp-attachments"),value:s,onChange:d,min:1,max:10}),L(N,{variant:"primary",onClick:()=>o()},H("Save profile photo","bp-attachments"))))};const{element:{createElement:$,Fragment:q,useRef:V},i18n:{__:X}}=wp;var Y=e=>{let{settings:t,originalImageSrc:n,onOriginalImageLoaded:r}=e;const{avatarFullWidth:o,avatarFullHeight:i}=t,a=V(null),s=V(null);return $(j,{selector:"bp-attachments-avatar-editor"},$(q,null,$("canvas",{ref:a,width:o,height:i}),!!n&&$("img",{ref:s,className:"bp-hide",src:n,onLoad:e=>{var t;r({naturalHeight:(t=e).target.naturalHeight,naturalWidth:t.target.naturalWidth})}})))};const{apiFetch:B,blob:{createBlobURL:G},domReady:J,element:{createElement:K,Fragment:Q,render:ee,useState:te},i18n:{__:ne}}=wp,re=e=>{let{settings:t}=e;const[n,r]=te({file:null,src:null,x:0,y:0,width:0,height:0,area:{},originalSize:{},zoom:1,isUploading:!1});let o=null;if(n.isUploading){const{area:e,originalSize:r,zoom:i}=n,a=Math.round(10*parseFloat(e.x))/10,s=Math.round(10*parseFloat(e.y))/10,c=r.naturalHeight>r.naturalWidth,u=new FormData;u.append("file",n.file),u.append("action","bp_avatar_upload"),u.append("crop_x",n.x),u.append("crop_y",n.y),u.append("crop_w",n.width),u.append("crop_h",n.height),B({path:"buddypress/v1/members/"+t.displayedUserId+"/avatar",method:"POST",body:u}).catch((e=>{console.log(e)}));const p={height:c?"auto":"100%",width:c?"100%":"auto",transform:"translate(-"+parseInt(i)*a+"%,-"+parseInt(i)*s+"%) scale("+i+")",transformOrigin:"top left"};o=K("img",{src:n.src,style:p})}else n.src&&n.originalSize.naturalHeight?o=K(Z,{image:n.src,originalSize:n.originalSize,onCropEdit:(e,t,o)=>{const i=n;null!==e&&(i.area=e),null!==t&&(i.x=t.x,i.y=t.y,i.width=t.width,i.height=t.height),null!==o&&(i.zoom=o),r(i)},onSaveEdits:()=>{r({...n,isUploading:!0})}}):n.src||(o=K(p,{settings:t,onSelectedImage:e=>{let t;t=e.currentTarget&&e.currentTarget.files?[...e.currentTarget.files]:e,r({...n,file:t[0],src:G(t[0])})}}));return K(Q,null,o,K(Y,{settings:t,originalImageSrc:n.src,onOriginalImageLoaded:e=>{r({...n,originalSize:{naturalHeight:e.naturalHeight,naturalWidth:e.naturalWidth}})}}))};J((function(){const e=window.bpAttachmentsAvatarEditorSettings||{};ee(K(re,{settings:e}),document.querySelector("#bp-avatar-editor"))}))}();
//# sourceMappingURL=index.js.map
