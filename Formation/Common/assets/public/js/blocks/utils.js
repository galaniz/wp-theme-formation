var blockUtils;!function(){var e={7:function(e){e.exports={getNamespace:function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0];return window.hasOwnProperty("namespace")?window.namespace+(e?"/":""):""},getNamespaceObj:function(e){return!!window.hasOwnProperty(e)&&window[e]},editInnerBlocks:function(e){return[wp.element.createElement("div",null,wp.element.createElement(InnerBlocks,{allowedBlocks:e}))]},saveInnerBlocks:function(){return wp.element.createElement(InnerBlocks.Content,null)},getColorSlug:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"";if(!e.length||!n)return"";var t="";return e.forEach((function(e){e.color!=n||(t=e.slug)})),t}}}},n={},t=function t(r){var o=n[r];if(void 0!==o)return o.exports;var l=n[r]={exports:{}};return e[r](l,l.exports,t),l.exports}(7);blockUtils=t}();