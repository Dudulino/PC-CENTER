document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('dialog.modal').forEach(d=>d.addEventListener('click',event=>{if(event.target===d)d.close()}));
 document.querySelectorAll('input[type="number"]').forEach(e=>e.addEventListener('wheel',()=>e.blur()));
});
