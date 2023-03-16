!function(){function e(e,t,a,r){Object.defineProperty(e,t,{get:a,set:r,enumerable:!0,configurable:!0})}const t="bp/attachments";var a={};e(a,"getSettings",(function(){return m})),e(a,"getRequestsContext",(function(){return y})),e(a,"isGridDisplayMode",(function(){return b})),e(a,"getLoggedInUser",(function(){return h})),e(a,"getDisplayedUserId",(function(){return g})),e(a,"getFormState",(function(){return _})),e(a,"isUploading",(function(){return E})),e(a,"isQuerying",(function(){return f})),e(a,"uploadEnded",(function(){return D})),e(a,"getUploads",(function(){return T})),e(a,"getErrors",(function(){return v})),e(a,"getMedia",(function(){return S})),e(a,"countMedia",(function(){return A})),e(a,"getCurrentDirectory",(function(){return M})),e(a,"getCurrentDirectoryObject",(function(){return I})),e(a,"getTree",(function(){return P})),e(a,"getFlatTree",(function(){return R})),e(a,"isSelectable",(function(){return O})),e(a,"selectedMedia",(function(){return L})),e(a,"getRelativePath",(function(){return N})),e(a,"getDestinationData",(function(){return U})),e(a,"getPagination",(function(){return w}));const{i18n:{__:r}}=wp,{filter:n}=lodash,i=(e,t)=>{let a=n(e,{id:t});return a.forEach((t=>{const r=i(e,t.parent);a=[...a,...r]})),a},s=e=>{const t=[r("Bytes","bp-attachments"),r("KB","bp-attachments"),r("MB","bp-attachments"),r("GB","bp-attachments"),r("TB","bp-attachments")];if(0===e)return"0 "+t[0];const a=parseInt(Math.floor(Math.log(e)/Math.log(1024)),10);return 0===a?`${e} ${t[a]}`:`${(e/1024**a).toFixed(1)} ${t[a]}`};const{trim:o,groupBy:c,filter:l,indexOf:d,find:p,defaultTo:u}=lodash,m=e=>{const{settings:t}=e;return t},y=e=>{const{settings:{isAdminScreen:t}}=e;return!0===t?"edit":"view"},b=e=>{const{isGrid:t}=e;return t},h=e=>{const{user:t}=e;return t},g=e=>{const{displayedUserId:t}=e;return t},_=e=>{const{formState:t}=e;return t||{}},E=e=>{const{uploading:t}=e;return t},f=e=>{const{querying:t}=e;return t},D=e=>{const{ended:t}=e;return t},T=e=>{const{uploads:t}=e;return t},v=e=>{const{errors:t}=e;return t},S=e=>{const{files:t}=e;return t},A=e=>{const{files:t}=e;return t.length},M=e=>{const{currentDirectory:t}=e;return t||""},I=e=>{const{currentDirectory:t,tree:a}=e,r={readonly:!0};return""!==t?u(p(a,{id:t}),r):r},P=e=>{const{tree:t,currentDirectory:a}=e,r=c(t,"parent"),n=l(t,{parent:a||0}).map((e=>e.id));if(n&&n.length&&n.forEach((e=>{r[e]&&delete r[e]})),a){const e=i(t,a).map((e=>e.id));Object.keys(r).forEach((t=>{0!==parseInt(t,10)&&-1===d(e,t)&&delete r[t]}))}const s=e=>e.map((e=>{const t=r[e.id];return{...e,children:t&&t.length?s(t):[]}}));return s(r[0]||[])},R=e=>{const{tree:t}=e;return t||[]},O=e=>{const{isSelectable:t}=e;return t},L=e=>{const{files:t}=e;return l(t,["selected",!0])},N=e=>{const{relativePath:t}=e;return t},U=e=>{const{relativePath:t}=e;if(!t)return{object:"members"};const a=o(t,"/").split("/");return{visibility:a[0]?a[0]:"public",object:a[1]?a[1]:"members",item:a[2]?a[2]:""}},w=e=>{const{pagination:t}=e;return t};var C={};e(C,"setSettings",(function(){return Q})),e(C,"fetchFromAPI",(function(){return X})),e(C,"getFromAPI",(function(){return z})),e(C,"createFromAPI",(function(){return V})),e(C,"updateFromAPI",(function(){return $})),e(C,"deleteFromAPI",(function(){return W})),e(C,"switchDisplayMode",(function(){return K})),e(C,"getLoggedInUser",(function(){return Z})),e(C,"setDisplayedUserId",(function(){return J})),e(C,"getMedia",(function(){return ee})),e(C,"updateFormState",(function(){return te})),e(C,"initTree",(function(){return re})),e(C,"addItemTree",(function(){return ne})),e(C,"removeItemTree",(function(){return ie})),e(C,"toggleSelectable",(function(){return se})),e(C,"toggleMediaSelection",(function(){return oe})),e(C,"addMedium",(function(){return ce})),e(C,"addMediumError",(function(){return le})),e(C,"createMedium",(function(){return de})),e(C,"createDirectory",(function(){return pe})),e(C,"updateMedium",(function(){return ue})),e(C,"parseResponseMedia",(function(){return me})),e(C,"requestMedia",(function(){return ye})),e(C,"removeMedium",(function(){return be})),e(C,"deleteMedium",(function(){return he})),e(C,"removeMediumError",(function(){return ge}));const F={SET_SETTINGS:"SET_SETTINGS",GET_LOGGED_IN_USER:"GET_LOGGED_IN_USER",SET_DISPLAYED_USER_ID:"SET_DISPLAYED_USER_ID",GET_MEDIA:"GET_MEDIA",ADD_MEDIUM:"ADD_MEDIUM",FILL_TREE:"FILL_TREE",PURGE_TREE:"PURGE_TREE",REMOVE_MEDIUM:"REMOVE_MEDIUM",FETCH_FROM_API:"FETCH_FROM_API",GET_FROM_API:"GET_FROM_API",CREATE_FROM_API:"CREATE_FROM_API",UPDATE_FROM_API:"UPDATE_FROM_API",DELETE_FROM_API:"DELETE_FROM_API",UPLOAD_START:"UPLOAD_START",UPLOAD_END:"UPLOAD_END",RESET_UPLOADS:"RESET_UPLOADS",ADD_ERROR:"ADD_ERROR",REMOVE_ERROR:"REMOVE_ERROR",TOGGLE_SELECTABLE:"TOGGLE_SELECTABLE",TOGGLE_MEDIA_SELECTION:"TOGGLE_MEDIA_SELECTION",SWITCH_DISPLAY_MODE:"SWITCH_DISPLAY_MODE",UPDATE_FORM_STATE:"UPDATE_FORM_STATE",SET_QUERY_STATUS:"SET_QUERY_STATUS"},{uniqueId:G,hasIn:j,trim:x,trimEnd:k,filter:B}=lodash,{data:{dispatch:q,select:Y},url:{addQueryArgs:H}}=wp;function Q(e){return{type:F.SET_SETTINGS,settings:e}}function X(e,t){return{type:F.FETCH_FROM_API,path:e,parse:t}}function z(e){return{type:F.GET_FROM_API,response:e}}function V(e,t){return{type:F.CREATE_FROM_API,path:e,data:t}}function $(e,t){return{type:F.UPDATE_FROM_API,path:e,data:t}}function W(e,t,a){return{type:F.DELETE_FROM_API,path:e,relativePath:t,totalBytes:a}}function K(e){return{type:F.SWITCH_DISPLAY_MODE,isGrid:e}}function Z(e){return{type:F.GET_LOGGED_IN_USER,user:e}}function J(e){return{type:F.SET_DISPLAYED_USER_ID,userId:e}}function ee(e,t,a,r){return{type:F.GET_MEDIA,files:e,relativePath:t,currentDirectory:a,pagination:r}}function te(e){return{type:F.UPDATE_FORM_STATE,params:e}}const ae=(e,t)=>({id:e.id,slug:e.name,name:e.title,parent:t,object:e.object?e.object:"members",readonly:!!e.readonly&&e.readonly,visibility:e.visibility?e.visibility:"public",type:e.media_type?e.media_type:"folder"});function re(e){const a=Y(t).getTree(),r=B(e,{mime_type:"inode/directory"});a.length||r.forEach((e=>{const a=ae(e,0);q(t).addItemTree(a)}))}function ne(e){return{type:F.FILL_TREE,item:e}}function ie(e){return{type:F.PURGE_TREE,itemId:e}}function se(e){return{type:F.TOGGLE_SELECTABLE,isSelectable:e}}function oe(e,t){return{type:F.TOGGLE_MEDIA_SELECTION,ids:e,isSelected:t}}function ce(e){return{type:F.ADD_MEDIUM,file:e}}function le(e){return{type:F.ADD_ERROR,error:e}}function*de(e,a){let r,n=!0;const i=Y(t),{object:s,item:o,visibility:c}=i.getDestinationData(),l=i.getRelativePath();yield{type:"UPLOAD_START",uploading:n,file:e};const d=new FormData;if(d.append("file",e),d.append("action","bp_attachments_media_upload"),d.append("object",s),d.append("object_item",o),c&&d.append("visibility",c),a&&d.append("total_bytes",a),x(l,"/")!==c+"/"+s+"/"+o){let e=l;"private"===c&&(e=l.replace("/private","")),d.append("parent_dir",e)}n=!1;try{return r=yield V("/buddypress/v1/attachments",d),yield{type:"UPLOAD_END",uploading:n,file:e},ce(r)}catch(t){return r={id:G(),name:e.name,error:t.message,uploaded:!1},yield{type:"UPLOAD_END",uploading:n,file:e},le(r)}}function*pe(e){let a,r=!0,n={name:e.directoryName,type:e.directoryType};const i=Y(t),{object:s,item:o,visibility:c}=i.getDestinationData(),l=i.getRelativePath();yield{type:"UPLOAD_START",uploading:r,file:n};const d=new FormData;if(d.append("directory_name",n.name),d.append("directory_type",n.type),d.append("action","bp_attachments_make_directory"),d.append("object",s),d.append("object_item",o),c&&d.append("visibility",c),x(l,"/")!==c+"/"+s+"/"+o){let e=l;"private"===c&&(e=l.replace("/private","")),d.append("parent_dir",e)}r=!1;try{a=yield V("/buddypress/v1/attachments",d),yield{type:"UPLOAD_END",uploading:r,file:n},a.uploaded=!0;const e=i.getCurrentDirectoryObject(),t=ae(a,e.id);return yield ne(t),ce(a)}catch(e){return a={id:G(),name:n.name,error:e.message,uploaded:!1},yield{type:"UPLOAD_END",uploading:r,file:n},le(a)}}function*ue(e){let a;const r=Y(t).getRelativePath();try{return a=yield $("/buddypress/v1/attachments/"+e.id+"/",{relative_path:r,title:e.title,description:e.description}),e.selected&&(a.selected=!0),ce(a)}catch(t){return a={id:G(),name:e.name,error:t.message,updated:!1},le(a)}}const me=async function(e,a){let r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",n=arguments.length>3?arguments[3]:void 0;const i=await e.json().then((e=>(e.forEach((e=>{if(e.parent=r,"inode/directory"===e.mime_type){const a=ae(e,0===e.id.indexOf("member-")?0:r);q(t).addItemTree(a)}})),e)));a||r||1!==parseInt(n.membersPage,10)||re(i),q(t).getMedia(i,a,r,n)};function*ye(){let e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};const a="/buddypress/v1/attachments",r=Y(t).getDisplayedUserId();let n=!0,i="",s="",o={};yield{type:"SET_QUERY_STATUS",querying:n},e.context||(e.context=Y(t).getRequestsContext()),e.directory&&e.path&&(e.directory=k(e.path,"/")+"/"+e.directory),e.parent&&(s=e.parent,delete e.parent),r&&(e.user_id=r),delete e.path;const c=yield X(H(a,e),!1);return n=!1,j(c,["headers","get"])?(i=c.headers.get("X-BP-Attachments-Relative-Path"),o={membersDisplayedAmount:c.headers.get("X-BP-Attachments-Media-Libraries-Total"),totalMembersPage:c.headers.get("X-BP-Attachments-Media-Libraries-TotalPages")}):(i=get(c,["headers","X-BP-Attachments-Relative-Path"],""),o={membersDisplayedAmount:get(c,["headers","X-BP-Attachments-Media-Libraries-Total"],0),totalMembersPage:get(c,["headers","X-BP-Attachments-Media-Libraries-TotalPages"],0)}),o.totalMembersPage&&(o.membersPage=e.page?parseInt(e.page,10):1),yield{type:"SET_QUERY_STATUS",querying:n},me(c,i,s,o)}function be(e){return{type:"REMOVE_MEDIUM",id:e}}function*he(e,a){const r=Y(t).getRelativePath();let n;try{return n=yield W("/buddypress/v1/attachments/"+e.id+"/",r,a),"inode/directory"===n.previous.mime_type&&(yield ie(n.previous.id)),be(n.previous.id)}catch(t){return e.error=t.message,le(e)}}function ge(e){return{type:F.REMOVE_ERROR,errorID:e}}var _e={};e(_e,"getLoggedInUser",(function(){return De})),e(_e,"getMedia",(function(){return Te}));const{get:Ee,hasIn:fe}=lodash;function*De(){const e=yield X("/buddypress/v1/members/me?context=edit",!0);yield Z(e)}function*Te(){const e="/buddypress/v1/attachments?context="+(()=>{const{isAdminScreen:e}=window.bpAttachmentsMediaLibrarySettings||{};return e&&!0===e?"edit":"view"})(),t=yield X(e,!1),a=yield z(t);let r={membersPage:1};fe(t,["headers","get"])?(r.membersDisplayedAmount=parseInt(t.headers.get("X-BP-Attachments-Media-Libraries-Total"),10),r.totalMembersPage=parseInt(t.headers.get("X-BP-Attachments-Media-Libraries-TotalPages"),10)):(r.membersDisplayedAmount=parseInt(Ee(t,["headers","X-BP-Attachments-Media-Libraries-Total"],0),10),r.totalMembersPage=parseInt(Ee(t,["headers","X-BP-Attachments-Media-Libraries-TotalPages"],0),10)),re(a),yield ee(a,"","",r)}const{reject:ve}=lodash,{data:{select:Se},preferences:{store:Ae}}=wp,Me={user:{},displayedUserId:0,tree:[],currentDirectory:"",files:[],relativePath:"",uploads:[],errors:[],uploading:!1,ended:!1,isSelectable:!1,isGrid:Se(Ae).get("bp/attachments","isGridLayout"),settings:{},formState:{},pagination:{membersPage:1,membersDisplayedAmount:0,totalMembersPage:0},querying:!1};var Ie=function(){let e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:Me,t=arguments.length>1?arguments[1]:void 0;switch(t.type){case F.SET_SETTINGS:return{...e,settings:t.settings};case F.GET_LOGGED_IN_USER:return{...e,user:t.user};case F.SET_DISPLAYED_USER_ID:return{...e,displayedUserId:parseInt(t.userId,10)};case F.GET_MEDIA:let a=[];return a=t.pagination.membersPage&&1<t.pagination.membersPage?[...e.files,...t.files]:t.files,{...e,files:a,relativePath:t.relativePath,currentDirectory:t.currentDirectory,pagination:{...t.pagination}};case F.FILL_TREE:return{...e,tree:[...ve(e.tree,["id",t.item.id]),t.item]};case F.PURGE_TREE:return{...e,tree:ve(e.tree,["id",t.itemId])};case F.UPDATE_FORM_STATE:return{...e,formState:{...t.params}};case F.ADD_MEDIUM:return{...e,files:[...ve(e.files,["id",t.file.id]),t.file]};case F.UPLOAD_START:return{...e,uploading:t.uploading,uploads:[...e.uploads,t.file]};case F.ADD_ERROR:return{...e,errors:[...e.errors,t.error]};case F.REMOVE_ERROR:return{...e,errors:ve(e.errors,["id",t.errorID])};case F.UPLOAD_END:return{...e,uploading:t.uploading,uploads:ve(e.uploads,(e=>e.name===t.file.name)),ended:!0};case F.RESET_UPLOADS:return{...e,uploading:!1,uploads:[],errors:[],ended:!1};case F.TOGGLE_SELECTABLE:return{...e,isSelectable:t.isSelectable};case F.TOGGLE_MEDIA_SELECTION:return{...e,files:e.files.map((e=>(("all"===t.ids[0]&&!t.isSelected||-1!==t.ids.indexOf(e.id))&&(e.selected=t.isSelected),e)))};case F.SWITCH_DISPLAY_MODE:return{...e,isGrid:t.isGrid};case F.REMOVE_MEDIUM:return{...e,files:[...ve(e.files,["id",t.id])]};case F.SET_QUERY_STATUS:return{...e,querying:t.querying}}return e};const{apiFetch:Pe}=wp,Re={FETCH_FROM_API(e){let{path:t,parse:a}=e;return Pe({path:t,parse:a})},GET_FROM_API(e){let{response:t}=e;return t.json()},CREATE_FROM_API(e){let{path:t,data:a}=e;return Pe({path:t,method:"POST",body:a})},UPDATE_FROM_API(e){let{path:t,data:a}=e;return Pe({path:t,method:"PUT",data:a})},DELETE_FROM_API(e){let{path:t,relativePath:a,totalBytes:r}=e;return Pe({path:t,method:"DELETE",data:{relative_path:a,total_bytes:r}})}},{data:{registerStore:Oe}}=wp;Oe(t,{reducer:Ie,actions:C,selectors:a,controls:Re,resolvers:_e});const Le=t,{components:{Popover:Ne},data:{useDispatch:Ue,useSelect:we},element:{createElement:Ce,Fragment:Fe,useState:Ge},i18n:{__:je}}=wp;var xe=e=>{let{settings:t}=e;const{updateFormState:a}=Ue(Le),{currentDirectoryObject:r,user:n,displayedUserId:i}=we((e=>{const t=e(Le);return{currentDirectoryObject:t.getCurrentDirectoryObject(),user:t.getLoggedInUser(),displayedUserId:t.getDisplayedUserId()}}),[]),[s,o]=Ge(!1),c=!0===s?"split-button is-open":"split-button",l=!0===s?"dashicons dashicons-arrow-up-alt2":"dashicons dashicons-arrow-down-alt2",d=!0!==r.readonly&&(!i||i===n.id),{allowedExtByMediaList:p,isAdminScreen:u}=t,m=u?"wp-header-end":"screen-header-end",y=n.capabilities&&-1!==n.capabilities.indexOf("bp_moderate")?je("Community Media Libraries","bp-attachments"):je("Community Media Library","bp-attachments"),b=e=>(e.preventDefault(),a({parentDirectory:r.id,action:"upload"}));let h=[];r.type&&-1!==["album","audio_playlist","video_playlist"].indexOf(r.type)||(h=[{id:"folder",text:je("Add new directory","bp-attachments")}],p&&"private"!==r.visibility&&Object.keys(p).forEach((e=>{"album"===e?h.push({id:"album",text:je("Add new photo album","bp-attachments")}):"audio_playlist"===e?h.push({id:"audio_playlist",text:je("Add new audio playlist","bp-attachments")}):"video_playlist"===e&&h.push({id:"video_playlist",text:je("Add new video playlist","bp-attachments")})})));const g=h.map((e=>Ce("li",{key:"type-"+e.id},Ce("a",{href:"#new-bp-media-directory",className:"button-link directory-button split-button-option",onClick:t=>((e,t)=>(e.preventDefault(),a({parentDirectory:r.id,action:"createDirectory",directoryType:t})))(t,e.id)},e.text))));return Ce(Fe,null,!!u&&Ce("h1",{className:"wp-heading-inline"},y),!u&&Ce("h3",{className:"screen-heading"},je("Library","bp-attachments")),!!d&&Ce("div",{className:c},Ce("div",{className:"split-button-head"},Ce("a",{href:"#new-bp-media-upload",className:"button split-button-primary","aria-live":"polite",onClick:e=>b(e)},je("Add new","bp-attachments")),Ce("button",{type:"button",className:"split-button-toggle","aria-haspopup":"true","aria-expanded":s,onClick:()=>o(!s)},Ce("i",{className:l}),Ce("span",{className:"screen-reader-text"},je("More actions","bp-attachments")),s&&Ce(Ne,{noArrow:!1,onFocusOutside:()=>o(!s)},Ce("ul",{className:"split-button-body"},Ce("li",null,Ce("a",{href:"#new-bp-media-upload",className:"button-link media-button split-button-option",onClick:e=>b(e)},je("Upload media","bp-attachments"))),g))))),Ce("hr",{className:m}))};const{components:{DropZone:ke,FormFileUpload:Be},data:{useDispatch:qe,useSelect:Ye},element:{createElement:He},i18n:{__:Qe,sprintf:Xe}}=wp;var ze=e=>{let{settings:t}=e;const{maxUploadFileSize:a,allowedExtTypes:r,allowedExtByMediaList:n}=t,{updateFormState:i,createMedium:o}=qe(Le),{formState:c,currentDirectoryObject:l}=Ye((e=>{const t=e(Le);return{formState:t.getFormState(),currentDirectoryObject:t.getCurrentDirectoryObject()}}),[]),d=()=>{c.action="",i(c)},p=e=>{let t,a=0;t=e.currentTarget&&e.currentTarget.files?[...e.currentTarget.files]:e;let r=t.length;t.forEach((e=>{a+=parseInt(e.size,10),r-=1;o(e,0===r?a:0)})),d()};if(!c.action||"upload"!==c.action)return null;let u=r;return l.type&&-1!==["album","audio_playlist","video_playlist"].indexOf(l.type)&&(u=n[l.type]),He("div",{className:"uploader-container enabled"},He(ke,{label:Qe("Drop your files here.","bp-attachments"),onFilesDrop:e=>p(e),className:"uploader-inline"}),He("button",{className:"close dashicons dashicons-no",onClick:e=>(e=>{e.preventDefault(),d()})(e)},He("span",{className:"screen-reader-text"},Qe("Close the upload panel","bp-attachments"))),He("div",{className:"dropzone-label"},He("h2",{className:"upload-instructions drop-instructions"},Qe("Drop files to upload","bp-attachments")),He("p",{className:"upload-instructions drop-instructions"},Qe("or","bp-attachments")),He(Be,{onChange:e=>p(e),multiple:!0,accept:u,className:"browser button button-hero"},Qe("Select Files","bp-attachments"))),He("div",{className:"upload-restrictions"},He("p",null,Xe(Qe("Maximum upload file size: %s.","bp-attachments"),s(a)))))};const{components:{Button:Ve,TextControl:$e},data:{useDispatch:We,useSelect:Ke},element:{createElement:Ze,useState:Je},i18n:{__:et}}=wp;var tt=()=>{const[e,t]=Je(""),{updateFormState:a,createDirectory:r}=We(Le),n=Ke((e=>e(Le).getFormState()),[]),i=()=>(n.action="",n.directoryType="",a(n));if(!n.action||"createDirectory"!==n.action)return null;let s=et("Create a new directory","bp-attachments"),o=et("Type a name for your directory","bp-attachments"),c=et("Save directory","bp-attachments");return"album"===n.directoryType?(s=et("Create a new photo album","bp-attachments"),o=et("Type a name for your photo album","bp-attachments"),c=et("Save photo album","bp-attachments")):"audio_playlist"===n.directoryType?(s=et("Create a new audio playlist","bp-attachments"),o=et("Type a name for your audio playlist","bp-attachments"),c=et("Save audio playlist","bp-attachments")):"video_playlist"===n.directoryType&&(s=et("Create a new video playlist","bp-attachments"),o=et("Type a name for your video playlist","bp-attachments"),c=et("Save video playlist","bp-attachments")),Ze("form",{id:"bp-media-directory-form",className:"directory-creator-container enabled"},Ze("button",{className:"close dashicons dashicons-no",onClick:e=>(e=>{e.preventDefault(),i()})(e)},Ze("span",{className:"screen-reader-text"},et("Close the Create directory form","bp-attachments"))),Ze("h2",null,s),Ze($e,{label:o,value:e,onChange:e=>t(e)}),Ze(Ve,{variant:"primary",onClick:a=>(a=>{a.preventDefault();const s={directoryName:e,directoryType:n.directoryType};r(s),t(""),i()})(a)},c))};const{components:{Button:at,ExternalLink:rt,TextareaControl:nt,TextControl:it},data:{useDispatch:st},element:{createElement:ot,useState:ct},i18n:{__:lt,sprintf:dt}}=wp;var pt=e=>{let{medium:t,errorCallback:a}=e;const{id:r,name:n,title:i,description:s,icon:o,media_type:c,mime_type:l,visibility:d,selected:p,links:{view:u,download:m,src:y}}=t,[b,h]=ct({title:i,description:s}),{updateMedium:g}=st(Le),_=i===b.title&&s===b.description,E=-1===["image","video","audio"].indexOf(c)||"private"===d,f="inode/directory"===l;let D=["bp-attachment-edit-item__preview_content"];E||D.push("has-rich-preview"),f&&D.push("is-directory");return ot("div",{className:"bp-attachment-edit-item"},ot("div",{className:"bp-attachment-edit-item__preview"},ot("h3",{className:"bp-attachment-edit-item__preview_title"},b.title),ot("ul",{className:"bp-attachment-edit-item__preview_links"},ot("li",null,ot(rt,{href:u},lt(f?"Open directory page":"Open media page","bp-attachments"))),!f&&ot("li",null,ot("a",{href:m},lt("Download media","bp-attachments")))),ot("div",{className:D.join(" ")},ot("div",{className:"bp-attachment-edit-item__preview_illustration"},E&&ot("img",{src:o,className:"bp-attachment-medium-icon"}),"image"===c&&y&&ot("img",{src:y,className:"bp-attachment-medium-vignette"}),"audio"===c&&y&&ot("audio",{controls:"controls",preload:"metadata",className:"bp-attachment-medium-player"},ot("source",{src:y})),"video"===c&&y&&ot("video",{controls:"controls",muted:!0,preload:"metadata",className:"bp-attachment-medium-player"},ot("source",{src:y}))),ot("div",{className:"bp-attachment-edit-item__preview_description"},ot("p",null,b.description)))),ot("div",{className:"bp-attachment-edit-item__form"},ot("h3",null,dt(lt("Edit %s","bp-attachments"),n)),ot("p",{className:"description"},lt("Use the below fields to edit media properties.","bp-attachments")),ot(it,{label:lt("Title","bp-attachments"),value:b.title,onChange:e=>h({...b,title:e}),help:lt("Change the title of your medium to something more descriptive then its file name.","bp-attachments")}),ot(nt,{label:lt("Description","bp-attachments"),value:b.description,onChange:e=>h({...b,description:e}),help:lt("Add or edit the description of your medium to tell your story about it.","bp-attachments")}),ot("div",{className:"bp-attachment-edit-item__form-actions"},ot(at,{variant:"primary",disabled:_,onClick:e=>(e.preventDefault(),void g({id:r,name:n,title:b.title,description:b.description,selected:p}).then((e=>{e.error?a(!1):e.file&&h({...b,title:e.file.title,description:e.file.description})})))},lt("Save your edits","bp-attachment")),ot(at,{variant:"tertiary",disabled:_,onClick:e=>(e.preventDefault(),void h({...b,title:i,description:s}))},lt("Cancel","bp-attachment")))))};const{find:ut,reverse:mt,filter:yt}=lodash,{components:{Button:bt,Modal:ht,TreeSelect:gt},element:{createElement:_t,useState:Et},data:{useDispatch:ft,useSelect:Dt},hooks:{applyFilters:Tt},i18n:{__:vt},preferences:{store:St}}=wp;var At=e=>{let{gridDisplay:t}=e;const{switchDisplayMode:a,requestMedia:r,toggleSelectable:n,toggleMediaSelection:s,deleteMedium:o,setDisplayedUserId:c}=ft(Le),{set:l}=ft(St),{user:d,displayedUserId:p,currentDirectory:u,currentDirectoryObject:m,flatTree:y,tree:b,isSelectable:h,selectedMedia:g,settings:_}=Dt((e=>{const t=e(Le);return{user:t.getLoggedInUser(),displayedUserId:t.getDisplayedUserId(),currentDirectory:t.getCurrentDirectory(),currentDirectoryObject:t.getCurrentDirectoryObject(),flatTree:t.getFlatTree(),tree:t.getTree(),isSelectable:t.isSelectable(),selectedMedia:t.selectedMedia(),settings:t.getSettings()}}),[]),[E,f]=Et(u),[D,T]=Et(!1),v=!0!==m.readonly,S=h&&0!==g.length,A=h&&1===g.length,M=!!_.isAdminScreen&&!!d.capabilities&&-1!==d.capabilities.indexOf("bp_moderate");u!==E&&f(u);const I=(e,t)=>{e.preventDefault(),l("bp/attachments","isGridLayout",t),a(t)};return _t("div",{className:"media-toolbar wp-filter"},_t("div",{className:"media-toolbar-secondary"},!h&&_t("div",{className:"view-switch media-grid-view-switch"},_t("a",{href:"#view-list",onClick:e=>I(e,!1),className:t?"view-list":"view-list current"},_t("span",{className:"screen-reader-text"},vt("Display list","bp-attachments"))),_t("a",{href:"#view-grid",onClick:e=>I(e,!0),className:t?"view-grid current":"view-grid"},_t("span",{className:"screen-reader-text"},vt("Display grid","bp-attachments")))),v&&_t(bt,{variant:"secondary",className:"media-button select-mode-toggle-button",onClick:e=>(e=>{e.preventDefault();const t=!h;return t||s(["all"],t),n(t)})(e)},vt(h?"Cancel Selection":"Select","bp-attachments")),v&&A&&_t(bt,{variant:"primary",className:"media-button edit-selected-button",onClick:e=>(e.preventDefault(),void T(!0))},vt("Edit","bp-attachments")),v&&S&&_t(bt,{variant:"tertiary",className:"media-button delete-selected-button",onClick:e=>(e=>{e.preventDefault();let t=0,a=g.length;return g.forEach((e=>{e.size&&(t+=parseInt(e.size,10)),a-=1,o(e,0===a?t:0)})),n(!1)})(e)},vt("Delete selection","bp-attachments")),D&&_t(ht,{title:vt("Media details","bp-attachments"),onRequestClose:()=>T(!1)},_t(pt,{medium:g[0],errorCallback:T}))),!!b.length&&_t("div",{className:"media-toolbar-primary"},_t(gt,{noOptionLabel:vt(M?"All members":"Home","bp-attachments"),onChange:e=>(e=>{f(e);const t=0===e.indexOf("member-")?parseInt(e.replace("member-",""),10):0;t&&c(t);const a=ut(y,{id:e});let n={};if(a){if(n.directory=a.slug,n.parent=a.id,a.parent&&a.object){let e=mt(i(y,a.parent).map((e=>e.slug)));if("members"===a.object){const t=e.indexOf("member");-1!==t&&e.splice(t,1),e.length&&e.splice(1,0,a.object,d.id)}else e=Tt("buddypress.Attachments.toolbarTreeSelect.pathArray",e,a,d.id);n.path="/"+e.join("/")}a.object&&(n.object=a.object),(t||p)&&(n.user_id=t!==p?t:p)}else p&&c(0);return r(n)})(e),selectedId:E,tree:M?yt(b,{id:"member-"+p}):b})))};const{template:Mt}=lodash;var It=function(e){return Mt(document.querySelector("#tmpl-"+e).innerHTML,{evaluate:/<#([\s\S]+?)#>/g,interpolate:/\{\{\{([\s\S]+?)\}\}\}/g,escape:/\{\{([^\}]+?)\}\}(?!\})/g,variable:"data"})};const{element:{createElement:Pt,Fragment:Rt,useState:Ot},components:{Modal:Lt},i18n:{__:Nt},data:{useSelect:Ut,useDispatch:wt}}=wp;var Ct=e=>{const{medium:t,selected:a,isGrid:r}=e,n=It(r?"bp-attachments-media-item":"bp-attachments-list-media-item"),{toggleMediaSelection:i,requestMedia:s,setDisplayedUserId:o}=wt(Le),[c,l]=Ot(!1),[d,p]=Ot(a),{getRelativePath:u,isSelectable:m}=Ut((e=>{const t=e(Le);return{getRelativePath:t.getRelativePath(),isSelectable:t.isSelectable()}}),[]);m||a||!d||p(!1);return Pt(Rt,null,Pt("div",{className:d?"media-item selected":"media-item",dangerouslySetInnerHTML:{__html:n(e)},role:"checkbox",onClick:()=>(()=>{const{mimeType:t,name:a,isSelectable:r,id:n,object:c}=e;if(r)return p(!d),i([n],!d);if("inode/directory"===t){const e=0===n.indexOf("member-")?parseInt(n.replace("member-",""),10):0;return e&&o(e),s({directory:a,path:u,object:c,parent:n})}l(!0)})()}),c&&Pt(Lt,{title:Nt("Media details","bp-attachments"),onRequestClose:()=>l(!1)},Pt(pt,{medium:t,errorCallback:l})))};const{components:{Animate:Ft,Dashicon:Gt,Notice:jt},element:{createElement:xt,Fragment:kt},i18n:{__:Bt,sprintf:qt},data:{useSelect:Yt,useDispatch:Ht}}=wp;var Qt=()=>{const{uploads:e,errors:t}=Yt((e=>{const t=e(Le);return{uploads:t.getUploads(),errors:t.getErrors()}}),[]),{removeMediumError:a}=Ht(Le);let r=[];t&&t.length&&(r=t.map((e=>xt(jt,{key:"error-"+e.id,status:"error",onRemove:()=>{return t=e.id,a(t);var t},isDismissible:!0},xt("p",null,xt(Gt,{icon:"warning"}),qt(Bt("« %1$s » wasn‘t added to the media library. %2$s","bp-attachments"),e.name,e.error))))));let n=null;const i=e&&e.length?e.length:0;if(i){let e=Bt("Uploading the media, please wait.","bp-attachments");i>1&&(e=qt(Bt("Uploading %d media, please wait.","bp-attachments"),i)),n=xt("div",{className:"chargement-de-documents"},xt(Ft,{type:"loading"},(t=>{let{className:a}=t;return xt(jt,{status:"warning",isDismissible:!1},xt("p",{className:a},xt(Gt,{icon:"update"}),e))})))}return xt("div",{className:"bp-attachments-notices"},n,r)};const{element:{createElement:Xt,Fragment:zt},i18n:{__:Vt},data:{useSelect:$t,useDispatch:Wt}}=wp;var Kt=e=>{let{gridDisplay:t}=e;const{items:a,isSelectable:r}=$t((e=>{const t=e(Le);return{items:t.getMedia(),isSelectable:t.isSelectable()}}),[]),n=!0===t?" grid":" list";let i=null;return a.length&&(i=a.map((e=>Xt(Ct,{key:"media-item-"+e.id,id:e.id,name:e.name,title:e.title,description:e.description,size:e.size?s(e.size):"",mediaType:e.media_type,mimeType:e.mime_type,icon:e.icon,vignette:e.vignette,orientation:e.orientation,isSelected:e.selected||!1,object:e.object||"members",isSelectable:r,medium:e,isGrid:t})))),Xt("main",{className:"bp-user-media"},Xt(Qt,null),Xt("div",{className:r?"media-items mode-select"+n:"media-items"+n},i,!a.length&&Xt("p",{className:"no-media"},Vt("No community media items found.","bp-attachments"))))};const{components:{Button:Zt,Spinner:Jt},data:{useDispatch:ea,useSelect:ta},element:{createElement:aa},i18n:{__:ra,sprintf:na}}=wp;var ia=e=>{let{settings:t}=e;const{requestMedia:a}=ea(Le),{user:{capabilities:r},pagination:n,mediaCount:i,isQuerying:s}=ta((e=>{const t=e(Le);return{user:t.getLoggedInUser(),pagination:t.getPagination(),mediaCount:t.countMedia(),isQuerying:t.isQuerying()}}),[]),o=!!t.isAdminScreen&&!!r&&-1!==r.indexOf("bp_moderate"),c=parseInt(n.membersDisplayedAmount,10);return o&&n.membersDisplayedAmount?aa("div",{className:"load-more-wrapper"},!0===s&&aa(Jt,null),aa("p",{className:"load-more-count"},1!==c?na(ra("Showing %1$s of %2$s media libraries","bp-attachments"),i,c):ra("Showing one media library","bp-attachments")),i!==c&&!s&&aa(Zt,{variant:"primary",className:"load-more",onClick:e=>(e=>{e.preventDefault();const{membersPage:t}=n;return a({page:t+1})})(e)},ra("Load more","bp-attachments"))):null};const{domReady:sa,element:{createElement:oa,render:ca,Fragment:la},i18n:{__:da},data:{useSelect:pa,useDispatch:ua,dispatch:ma},preferences:{store:ya}}=wp,ba=e=>{let{settings:t}=e;const{isGrid:a,globalSettings:r}=pa((e=>{const t=e(Le);return{isGrid:t.isGridDisplayMode(),globalSettings:t.getSettings()}}),[]);if(!Object.keys(r).length){const{setSettings:e}=ua(Le);e(t)}return oa(la,null,oa(xe,{settings:r}),oa(ze,{settings:r}),oa(tt,null),oa(At,{gridDisplay:a}),oa(Kt,{gridDisplay:a}),oa(ia,{settings:r}))};sa((function(){const e=window.bpAttachmentsMediaLibrarySettings||{};ma(ya).setDefaults("bp/attachments",{isGridLayout:!0}),ca(oa(ba,{settings:e}),document.querySelector("#bp-media-library"))}))}();
//# sourceMappingURL=index.js.map
