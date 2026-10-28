(()=>{
'use strict';
const cfg=window.quoteSetup;if(!cfg)return;
const $=id=>document.getElementById(id);const money=new Intl.NumberFormat('es-PE',{style:'currency',currency:'PEN'});
const items=Array.isArray(cfg.items)?cfg.items.map(x=>({producto_id:Number(x.producto_id),codigo:String(x.codigo),descripcion:String(x.descripcion),precio:Number(x.precio),cantidad:Number(x.cantidad),stock:(x.stock===null||x.stock===undefined||x.stock==='')?null:Number(x.stock)})):[];
let dirty=false,enviando=false;
const body=$('itemsBody'),hidden=$('itemsJson'),msg=$('scanMsg');let searchTimer=null;let stream=null;let cameraRunning=false;
function info(text,error=false){msg.textContent=text;msg.style.color=error?'#ce405c':'#1588ac';}
function safeMoney(n){return money.format(n)}
function render(){
 body.replaceChildren();let unidades=0,total=0;const excedidos=[];
 if(!items.length){const tr=document.createElement('tr');const td=document.createElement('td');td.colSpan=4;td.className='empty-cell';td.textContent='Aún no se agregaron productos.';tr.append(td);body.append(tr)}
 for(const x of items){unidades+=x.cantidad;total+=x.cantidad*x.precio;
 const tr=document.createElement('tr');const td=document.createElement('td');td.className='line-name';const strong=document.createElement('strong');strong.textContent=x.descripcion;const small=document.createElement('small');small.textContent=x.codigo+' · '+safeMoney(x.precio)+' c/u';td.append(strong,small);
 if(x.stock!==null&&x.cantidad>x.stock){excedidos.push(x.descripcion);const w=document.createElement('small');w.className='line-warning';w.textContent='⚠ Supera el stock referencial ('+x.stock+')';td.append(w)}
 tr.append(td);
 const q=document.createElement('td');const inp=document.createElement('input');inp.type='number';inp.className='qty-input';inp.min='1';inp.max='100000';inp.step='1';inp.value=String(x.cantidad);inp.setAttribute('aria-label','Cantidad de '+x.descripcion);inp.addEventListener('change',()=>{const n=Number(inp.value);if(!Number.isInteger(n)||n<1||n>100000){inp.value=x.cantidad;info('La cantidad debe ser un entero de 1 a 100000.',true);return}x.cantidad=n;dirty=true;render()});q.append(inp);tr.append(q);
 const sub=document.createElement('td');sub.className='price';sub.textContent=safeMoney(x.cantidad*x.precio);tr.append(sub);
 const end=document.createElement('td');const remove=document.createElement('button');remove.type='button';remove.className='remove-btn';remove.textContent='×';remove.setAttribute('aria-label','Eliminar '+x.descripcion);remove.addEventListener('click',()=>{items.splice(items.indexOf(x),1);dirty=true;render()});end.append(remove);tr.append(end);body.append(tr);
 }
 const aviso=$('stockWarning');if(aviso){aviso.hidden=!excedidos.length;aviso.textContent=excedidos.length?'Hay '+excedidos.length+' artículo(s) con cantidad mayor al stock referencial. Confirma la disponibilidad con almacén antes de ofrecerlo.':''}
 $('itemCount').textContent=String(items.length);$('unitCount').textContent=String(unidades);$('quoteTotal').textContent=safeMoney(total);
 hidden.value=JSON.stringify(items.map(x=>({producto_id:x.producto_id,cantidad:x.cantidad})));
}
function add(p){const id=Number(p.id??p.producto_id);if(!id)return;
 const other=items.find(x=>x.producto_id===id);
 const stock=(p.es_servicio||p.stock===null||p.stock===undefined||p.stock==='')?null:Number(p.stock);
 if(other){if(other.cantidad>=100000){info('Cantidad máxima alcanzada.',true);return;}other.cantidad+=1;other.stock=stock}else items.push({producto_id:id,codigo:String(p.codigo),descripcion:String(p.descripcion),precio:Number(p.precio),cantidad:1,stock});
 dirty=true;info('Producto agregado: '+p.descripcion);render();$('scanCode').value='';$('productSearch').value='';$('searchResults').replaceChildren();filterCatalog();$('scanCode').focus();
}
async function scan(){const code=$('scanCode').value.trim();if(!code){info('Ingresa o escanea un código.',true);return;}
 try{const r=await fetch(cfg.urlCode+'?codigo='+encodeURIComponent(code),{headers:{'Accept':'application/json'}});const p=await r.json();if(!r.ok)throw new Error(p.message||'Código no encontrado');add(p)}catch(e){info(e.message||'Error al buscar el producto.',true)}
}
// Catálogo rápido: tarjetas completamente cargadas desde MySQL.
const productCards=Array.from(document.querySelectorAll('.pos-product-card'));
let activeCategory='';
function filterCatalog(){
 const term=($('productSearch').value||'').trim().toLocaleLowerCase('es');let visible=0;
 productCards.forEach(card=>{
  const hitCategory=!activeCategory||card.dataset.category===activeCategory;
  const hitSearch=!term||card.dataset.search.includes(term);
  card.hidden=!(hitCategory&&hitSearch);if(!card.hidden)visible++;
 });
 if($('posEmpty'))$('posEmpty').hidden=visible>0;
}
productCards.forEach(card=>card.addEventListener('click',()=>add({id:card.dataset.id,codigo:card.dataset.code,descripcion:card.dataset.description,precio:card.dataset.price,stock:card.dataset.stock})));
document.querySelectorAll('.pos-category').forEach(button=>button.addEventListener('click',()=>{
 activeCategory=button.dataset.category||'';
 document.querySelectorAll('.pos-category').forEach(x=>x.classList.toggle('active',x===button));filterCatalog();
}));
$('productSearch').addEventListener('input',filterCatalog);
$('scanButton').addEventListener('click',scan);$('scanCode').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();scan()}});
$('productSearch').addEventListener('input',()=>{clearTimeout(searchTimer);const term=$('productSearch').value.trim();if(!term){$('searchResults').replaceChildren();return;}searchTimer=setTimeout(()=>searchProducts(term),260)});
async function searchProducts(term){const area=$('searchResults');try{const r=await fetch(cfg.urlSearch+'?term='+encodeURIComponent(term),{headers:{Accept:'application/json'}});if(!r.ok)throw new Error();const rows=await r.json();if($('productSearch').value.trim()!==term)return;area.replaceChildren();if(!rows.length){const small=document.createElement('small');small.textContent='No se encontraron productos activos.';area.append(small)}
 rows.forEach(p=>{const btn=document.createElement('button');btn.type='button';btn.className='suggestion';const t=document.createElement('div');const strong=document.createElement('strong');strong.textContent=p.descripcion;const sub=document.createElement('span');sub.textContent='Código: '+p.codigo;t.append(strong,sub);const cost=document.createElement('strong');cost.textContent=safeMoney(Number(p.precio));btn.append(t,cost);btn.addEventListener('click',()=>add(p));area.append(btn)})
 }catch{area.textContent='No se pudo consultar el catálogo.'}}
$('presupuestoForm').addEventListener('submit',e=>{render();if(enviando){e.preventDefault();return}if(!items.length){e.preventDefault();info('Debes agregar al menos un producto.',true);$('scanCode').focus();return}if(!$('clienteId').value){e.preventDefault();info('Debes seleccionar un cliente.',true);$('clienteId').focus();return}enviando=true;dirty=false;const b=$('saveQuote');if(b){b.disabled=true;b.textContent='Guardando…'}});
window.addEventListener('pageshow',()=>{enviando=false;const b=$('saveQuote');if(b){b.disabled=false;b.textContent='Guardar presupuesto →'}});
window.addEventListener('beforeunload',e=>{if(dirty&&!enviando){e.preventDefault();e.returnValue=''}});
$('clearCart')?.addEventListener('click',()=>{if(!items.length)return;if(!confirm('¿Vaciar todo el detalle del presupuesto?'))return;items.length=0;dirty=true;render();info('Detalle vaciado.');$('scanCode').focus()});
// Atajos: F2 enfoca el campo del lector; Esc limpia la búsqueda y vuelve al campo de código.
document.addEventListener('keydown',e=>{if(e.key==='F2'){e.preventDefault();$('scanCode').focus();$('scanCode').select()}else if(e.key==='Escape'&&['scanCode','productSearch'].includes(document.activeElement?.id)){$('scanCode').value='';$('productSearch').value='';$('searchResults').replaceChildren();filterCatalog();$('scanCode').focus()}});
$('newClientButton').addEventListener('click',()=>$('quickClientDialog').showModal());$('quickClientForm').addEventListener('submit',async e=>{e.preventDefault();$('quickClientMsg').textContent='';const fd=new FormData(e.currentTarget);try{const r=await fetch(e.currentTarget.action,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':cfg.token},body:fd});const data=await r.json();if(!r.ok){const x=data.errors?Object.values(data.errors).flat().join(' '):data.message;throw new Error(x||'Datos inválidos')}const o=new Option(data.nombre+(data.documento?' · '+data.documento:''),String(data.id),true,true);$('clienteId').add(o);$('clienteId').value=String(data.id);$('quickClientDialog').close();e.currentTarget.reset();info('Cliente registrado y seleccionado.')}catch(ex){$('quickClientMsg').textContent=ex.message||'No fue posible guardar al cliente.'}});
// Cámara web: lector ZXing como alternativa a la API experimental BarcodeDetector.
// Requiere HTTPS o localhost y permiso de cámara. La lectura se ejecuta en el navegador.
const cam=$('cameraPanel'),video=$('cameraVideo');
let cameraControls=null, cameraLoadPromise=null, scanBusy=false;
function stopCamera(){
 cameraRunning=false;
 if(cameraControls){try{cameraControls.stop()}catch{}cameraControls=null}
 if(stream){stream.getTracks().forEach(t=>t.stop());stream=null}
 if(video.srcObject){try{video.srcObject.getTracks().forEach(t=>t.stop())}catch{}}
 video.pause();video.srcObject=null;cam.hidden=true;
 $('cameraButton').textContent='▣ Escanear';
}
function acceptCameraCode(value){
 const code=String(value||'').trim();
 if(!cameraRunning||scanBusy||!code)return;
 scanBusy=true;$('scanCode').value=code;
 stopCamera();
 scan().finally(()=>{scanBusy=false});
}
function loadZXing(){
 if(window.ZXingBrowser?.BrowserMultiFormatReader)return Promise.resolve(window.ZXingBrowser);
 if(cameraLoadPromise)return cameraLoadPromise;
 cameraLoadPromise=new Promise((resolve,reject)=>{
  const script=document.createElement('script');
  script.src='https://unpkg.com/@zxing/browser@0.2.1/umd/zxing-browser.min.js';
  script.async=true;
  script.onload=()=>window.ZXingBrowser?.BrowserMultiFormatReader?resolve(window.ZXingBrowser):reject(new Error('La biblioteca ZXing no se cargó correctamente.'));
  script.onerror=()=>reject(new Error('No fue posible cargar ZXing. Comprueba la conexión a internet.'));
  document.head.append(script);
 }).catch(error=>{cameraLoadPromise=null;throw error});
 return cameraLoadPromise;
}
async function startZXing(){
 const ZX=await loadZXing();
 const reader=new ZX.BrowserMultiFormatReader();
 cameraRunning=true;cam.hidden=false;
 $('cameraButton').textContent='■ Detener cámara';
 info('Solicitando acceso a la cámara…');
 try{
  const controls=await reader.decodeFromVideoDevice(undefined,video,(result,error,controls)=>{
   if(!cameraRunning||!result)return;
   const value=typeof result.getText==='function'?result.getText():result.text;
   if(value){try{controls?.stop()}catch{}acceptCameraCode(value)}
  });
  if(!cameraRunning){controls.stop();return}
  cameraControls=controls;
  info('Cámara activa. Acerca un código EAN, Code 128 o QR.');
 }catch(error){
  stopCamera();
  if(error?.name==='NotAllowedError'||error?.name==='PermissionDeniedError')info('Permiso de cámara denegado: permite la cámara en Chrome y vuelve a intentar.',true);
  else if(error?.name==='NotFoundError'||error?.name==='DevicesNotFoundError')info('No se encontró ninguna cámara conectada.',true);
  else if(error?.name==='NotReadableError'||error?.name==='TrackStartError')info('La cámara está ocupada por otra aplicación o Windows no permite abrirla.',true);
  else info('No se pudo activar el escáner: '+(error?.message||'revisa los permisos de Chrome.'),true);
 }
}
$('stopCamera').addEventListener('click',stopCamera);
$('cameraButton').addEventListener('click',async()=>{
 if(cameraRunning){stopCamera();return}
 if(!window.isSecureContext||!navigator.mediaDevices?.getUserMedia){
  info('Abre el sistema en http://127.0.0.1:8000 o mediante HTTPS para habilitar la cámara.',true);return;
 }
 try{await startZXing()}
 catch(error){stopCamera();info(error?.message||'No se pudo cargar el escáner. Comprueba tu conexión a internet.',true)}
});
window.addEventListener('beforeunload',stopCamera);render();
})();
