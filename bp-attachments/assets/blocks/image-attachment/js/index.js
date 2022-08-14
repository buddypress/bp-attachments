!function(){function e(e){return e&&e.__esModule?e.default:e}var t;t=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"bp/image-attachment","title":"Community Image","category":"media","icon":"image","description":"Insert an image from your personal media library.","keywords":["BuddyPress","community","image","img","picture","photo","media"],"textdomain":"bp-attachments","attributes":{"link":{"type":"string"}},"supports":{"align":false,"alignWide":false,"anchor":false,"className":false,"html":false},"editorScript":"file:./bp-attachments/js/blocks/image-attachment/index.js","style":"file:./bp-attachments/assets/blocks/image-attachment/index.css"}');const{components:{DropZone:a,FormFileUpload:n,Placeholder:i},data:{useDispatch:s,useSelect:r},element:{createElement:o},i18n:{__:l}}=wp;var m=e=>{let{type:t,icon:s,label:m}=e;const{bpAttachments:{allowedExtByMediaList:c}}=r((e=>e("core/editor").getEditorSettings()),[]),p=c[t]?c[t]:c.album,d=e=>{let t;t=e.currentTarget&&e.currentTarget.files?[...e.currentTarget.files]:e;t[0]};return o(i,{icon:s||"admin-media",label:m||l("Insert a media","bp-attachments")},o(a,{onFilesDrop:e=>d(e),className:"uploader-inline"}),o(n,{onChange:e=>d(e),multiple:!1,accept:p,className:"block-editor-media-placeholder__button block-editor-media-placeholder__upload-button"},l("Select a file","bp-attachments")))};const{blocks:{registerBlockType:c},blockEditor:{useBlockProps:p},element:{createElement:d},i18n:{__:u}}=wp;c(e(t),{title:u("Community Image","bp-attachments"),description:u("Insert an image from your personal media library.","bp-attachments"),icon:{background:"#fff",foreground:"#d84800",src:"format-image"},category:"media",keywords:["BuddyPress",u("community","bp-attachments"),u("image","bp-attachments"),u("img","bp-attachments"),u("picture","bp-attachments"),u("photo","bp-attachments"),u("media","bp-attachments")],attributes:{link:{type:"string"}},supports:{align:!1,alignWide:!1,anchor:!1,className:!1,html:!1},edit:function(e){let{attributes:t,setAttributes:a}=e;p();return d(m,{type:"image",icon:"format-image",label:u("Insert an image","bp-attachments")})},save:function(e){let{attributes:t}=e;p.save();return null}})}();
//# sourceMappingURL=index.js.map
