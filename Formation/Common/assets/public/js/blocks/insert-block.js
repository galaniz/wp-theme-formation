!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=8)}({8:function(e,t){document.addEventListener("DOMContentLoaded",(function(){window.addEventListener("load",(function(){var e=blockUtils,t=e.getNamespace,n=e.getNamespaceObj;if(t(!0)){var r=n(t());if(r){var o=!1,a=!1;if(r.hasOwnProperty("insert_block")&&(o=r.insert_block),r.hasOwnProperty("insert_blocks")&&(a=r.insert_blocks),(o||a)&&(o&&(a=[{name:o,defaults:r.insert_block_defaults}]),a.length)){var c=wp.data.select("core/block-editor").getBlocks();a.forEach((function(e){var t=e.name,n=!1;if(c.forEach((function(e){e.name!=t||(n=!0)})),!n){var r=wp.blocks.createBlock(t,e.defaults);wp.data.dispatch("core/block-editor").insertBlock(r,0)}}))}}}}))}))}});