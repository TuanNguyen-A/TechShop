function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};

function pagination(num, page, total, id,search){
    n=Math.ceil(total/num);
    url='';
    if(id){
        url+='?id='+id+'&';
    }
    if(search){
        url+='?search='+search+'&';
    }
    if(page > 1){
        $('#pagination').append(`
        <li class="page-item ">
            <a class="page-link" href="${url}page=${Number(page)-1}">Previous</a>
        </li>
        `);
    }
    for(i=1;i<=n;i++){
        $('#pagination').append(`
        <li class="page-item page_num" ${page==i ? 'disabled' : ''}>
            <a class="page-link" href="${url}page=${i}">${i}</a>
        </li>
        `);
    }
    if(page < n){
        $('#pagination').append(`
        <li class="page-item">
            <a class="page-link" href="${url}page=${Number(page)+1}">Next</a>
        </li>
        `);
    }
}

function updateCart(){
    storage = localStorage.getItem('cart');
    if(storage){
      cart = JSON.parse(storage);
      count = 0;
      for(i = 0; i < cart.length; i++){
        item = cart[i];
        count += item.quantity;
      }
      $('.num').append(`
        ${count}
      `)
    }
  }