!function(e){var t={};function n(l){if(t[l])return t[l].exports;var r=t[l]={i:l,l:!1,exports:{}};return e[l].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,l){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:l})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var l=Object.create(null);if(n.r(l),Object.defineProperty(l,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(l,r,function(t){return e[t]}.bind(null,r));return l},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=9)}({9:function(e,t){function n(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var l=Object.getOwnPropertySymbols(e);t&&(l=l.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,l)}return n}function l(e){for(var t=1;t<arguments.length;t++){var l=null!=arguments[t]?arguments[t]:{};t%2?n(Object(l),!0).forEach((function(t){r(e,t,l[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(l)):n(Object(l)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(l,t))}))}return e}function r(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}var o=wp.components,a=(o.Panel,o.PanelBody),i=o.BaseControl,c=o.CheckboxControl,u=(o.TextControl,o.Button),m=wp.blockEditor,p=m.MediaUpload,d=m.MediaUploadCheck,s=m.InspectorControls,v=wp.element.Fragment,w=wp.blocks.registerBlockType,f=window.namespace,b=f+"/media",y=window[f].blocks[b].attr,E=window[f].blocks[b].default,g=window[f].blocks[b].parent,h={title:"Media",category:"theme-blocks",attributes:y,edit:function(e){var t=e.attributes,n=e.setAttributes,r=t.id,o=void 0===r?E.id:r,m=t.url,w=void 0===m?E.url:m,f=t.type,b=void 0===f?E.type:f,y=t.subtype,g=(void 0===y&&E.subtype,t.alt),h=void 0===g?E.alt:g,O=t.height,k=(void 0===O&&E.height,t.width),j=(void 0===k&&E.width,t.autoplay),C=void 0===j?E.autoplay:j,P=t.loop,N=void 0===P?E.loop:P,_=t.muted,S=void 0===_?E.muted:_,M=t.controls,D=void 0===M?E.controls:M,T=t.poster,x=void 0===T?E.poster:T,U=t.src,B=void 0===U?E.src:U,V=function(t){var r=t.mime.split("/")[0],o=t.mime.split("/")[1],a={id:t.id,type:r,subtype:o,width:t.width,height:t.height};if("video"==r){var i=l({},e.attributes.src);i[o]=t.url,a.src=i}"image"==r&&(a.alt=t.alt,a.url=t.url),n(a)},A=function(){n({id:"",type:"",url:"",src:E.src})},I="",L="",R="";for(var F in B)if(""!==B[F]){L=F,I=B[F];break}o&&I&&L&&(R='<video poster="'.concat(x||"",'">\n          <source src="').concat(I,'" type="video/').concat(L,'">\n        </video>'));var H=wp.element.createElement("div",null,"video"!=b?"":wp.element.createElement(v,null,wp.element.createElement(s,null,wp.element.createElement(a,{title:"Video Options"},wp.element.createElement(c,{label:"Autoplay",value:"1",checked:!!C,onChange:function(e){n({autoplay:e})}}),wp.element.createElement(c,{label:"Loop",value:"1",checked:!!N,onChange:function(e){n({loop:e})}}),wp.element.createElement(c,{label:"Muted",value:"1",checked:!!S,onChange:function(e){n({muted:e})}}),wp.element.createElement(c,{label:"Controls",value:"1",checked:!!D,onChange:function(e){n({controls:e})}}),wp.element.createElement("div",null,wp.element.createElement(d,null,["webm","mp4","ogv"].map((function(t,n){return wp.element.createElement(i,{label:t},B.hasOwnProperty(t)&&""!=B[t]?wp.element.createElement("div",null,wp.element.createElement(u,{isLink:!0,isDestructive:!0,onClick:function(){!function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",r=l({},e.attributes.src);r[t]=n,e.setAttributes({src:r})}(t,"")}},"Remove ",t)):wp.element.createElement(p,{onSelect:V,allowedTypes:["video/".concat(t)],render:function(e){var n=e.open;return wp.element.createElement(u,{isDefault:!0,onClick:n},"Upload ",t)}}))}))))))),R?wp.element.createElement("div",{className:"u-position-relative"},wp.element.createElement(u,{className:"o-remove-button",onClick:A},wp.element.createElement("div",null,wp.element.createElement("span",{className:"u-visually-hidden"},"Remove Video"),wp.element.createElement("div",{className:"o-remove-button__icon"},"×"))),wp.element.createElement("div",{className:"o-media-wrap",dangerouslySetInnerHTML:{__html:R}})):wp.element.createElement("div",{className:"o-button-wrap"},wp.element.createElement(d,null,wp.element.createElement(p,{onSelect:V,allowedTypes:["video/webm","video/mp4","video/ogv"],render:function(e){var t=e.open;return wp.element.createElement(u,{isDefault:!0,onClick:t},"Upload Video")}})))),q="";o&&w&&(q=wp.element.createElement("div",{className:"o-media-wrap"},wp.element.createElement("img",{src:w,alt:h})));var z=wp.element.createElement("div",{className:"u-position-relative"},q?wp.element.createElement("div",null,wp.element.createElement(u,{className:"o-remove-button",onClick:A},wp.element.createElement("div",null,wp.element.createElement("span",{className:"u-visually-hidden"},"Remove Video"),wp.element.createElement("div",{className:"o-remove-button__icon"},"×"))),q):wp.element.createElement("div",{className:"o-button-wrap"},wp.element.createElement(d,null,wp.element.createElement(p,{onSelect:V,allowedTypes:["image"],render:function(e){var t=e.open;return wp.element.createElement(u,{isDefault:!0,onClick:t},"Upload Image")}}))));return[H="image"!=b?H:"",z="video"!=b?z:""]},save:function(){return null}};g.length>0&&(h.parent=g),w(b,h)}});