!function(){const{template:e}=lodash;const{domReady:t}=wp;window.bp=window.bp||{},window.bp.Attachments=window.bp.Attachments||{};const n=window.bpAttachmentsDirectoryItems||{};window.bp.Attachments.Directory=new class{renderItem(t){t._embedded&&t._embedded.owner&&(t.owner=t._embedded.owner.at(0));var n;return(n=this.template,e(document.querySelector("#tmpl-"+n).innerHTML,{evaluate:/<#([\s\S]+?)#>/g,interpolate:/\{\{\{([\s\S]+?)\}\}\}/g,escape:/\{\{([^\}]+?)\}\}(?!\})/g,variable:"data"}))(t)}renderItems(){this.items.forEach((e=>{this.container.innerHTML+=this.renderItem(e)})),document.querySelectorAll(".bp-media-item").forEach((e=>{e.innerHTML.trim()?e.style.height=e.clientWidth+"px":e.style.display="none"}))}start(){this.items&&this.items.length&&this.renderItems()}constructor(e){const{body:t}=e;this.items=t,this.template="bp-media-item",this.container=document.querySelector("#bp-media-directory")}}(n),t((()=>window.bp.Attachments.Directory.start()))}();
//# sourceMappingURL=directory.js.map
