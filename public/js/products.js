(()=>{'use strict';const dialog=document.getElementById('productoDialog');const form=document.getElementById('productoForm');if(!dialog||!form)return;
const field=id=>document.getElementById(id);
function open(p){form.reset();form.action=window.location.origin+window.location.pathname.split('/productos')[0]+'/productos';field('productoMethod').value='POST';field('productoTitle').textContent='Agregar producto';
 if(p){form.action=form.action+'/'+encodeURIComponent(p.id);field('productoMethod').value='PUT';field('productoTitle').textContent='Editar producto';
 for(const [id,key] of [['pCodigo','codigo'],['pSku','sku'],['pDescripcion','descripcion'],['pCategoria','categoria'],['pIcono','icono'],['pCosto','costo'],['pPrecio','precio'],['pStock','stock'],['pMinimo','stock_minimo']])field(id).value=p[key]??'';
 field('pActivo').value=p.activo?'1':'0';field('pServicio').value=p.es_servicio?'1':'0';
 }else{field('pCategoria').value='Periféricos';field('pIcono').value='📦';field('pCosto').value='0';field('pStock').value='0';field('pMinimo').value='5'}syncServicio();dialog.showModal();}
// Un servicio no maneja existencias: se bloquea el campo de stock.
const syncServicio=()=>{const srv=field('pServicio').value==='1';field('pStock').disabled=srv;field('pStockHint').textContent=srv?'No aplica a servicios.':'Unidades disponibles de referencia.'};
field('pServicio').addEventListener('change',syncServicio);
field('nuevoProducto')?.addEventListener('click',()=>open());
document.querySelectorAll('.js-edit-product').forEach(b=>b.addEventListener('click',()=>{try{open(JSON.parse(b.dataset.product))}catch(e){console.error(e)}}));
field('cerrarProducto').addEventListener('click',()=>dialog.close());field('cancelarProducto').addEventListener('click',()=>dialog.close());
})();
