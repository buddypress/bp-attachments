!function(){function t(t){return t&&t.__esModule?t.default:t}var e;e=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"bp/image-attachment","title":"Community Image","category":"media","icon":"image","description":"Insert an image from your personal media library.","keywords":["BuddyPress","community","image","img","picture","photo","media"],"textdomain":"bp-attachments","attributes":{"link":{"type":"string"}},"supports":{"align":false,"alignWide":false,"anchor":false,"className":false,"html":false},"editorScript":"file:./bp-attachments/js/blocks/image-attachment/index.js","style":"file:./bp-attachments/assets/blocks/image-attachment/index.css"}');const{blocks:{registerBlockType:a},blockEditor:{useBlockProps:s},components:{Placeholder:n},element:{createElement:i},i18n:{__:m}}=wp;a(t(e),{title:m("Community Image","bp-attachments"),description:m("Insert an image from your personal media library.","bp-attachments"),icon:{background:"#fff",foreground:"#d84800",src:"format-image"},category:"media",keywords:["BuddyPress",m("community","bp-attachments"),m("image","bp-attachments"),m("img","bp-attachments"),m("picture","bp-attachments"),m("photo","bp-attachments"),m("media","bp-attachments")],attributes:{link:{type:"string"}},supports:{align:!1,alignWide:!1,anchor:!1,className:!1,html:!1},edit:function(t){let{attributes:e,setAttributes:a}=t;s();return i(n,{icon:"format-image",label:m("Insert an image","bp-attachments")})},save:function(t){let{attributes:e}=t;s.save();return null}})}();
//# sourceMappingURL=index.js.map