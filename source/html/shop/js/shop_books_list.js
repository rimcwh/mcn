
function clicked_display_book_detail()
{
}

function clicked_book_detail_number_minus()
{
  let num ;
  let detail ;
  let sum ;
  num = global_space.get_book_detail_number() ;
  if (num > 0)
  {
    num -- ;
  }
  global_space.set_book_detail_number(num) ;
  document.getElementById("shop-page-book-detail-number").innerHTML = num ;
  detail = global_space.get_book_detail() ;
  sum = detail.price * num ;
  document.getElementById("div-book-detail-sum").innerHTML = "小計：" + sum + " 元" ;
}

function clicked_book_detail_number_plus()
{
  let num ;
  let detail ;
  let sum ;
  num = global_space.get_book_detail_number() ;
  if (num < 9)
  {
    num ++ ;
  }
  global_space.set_book_detail_number(num) ;
  document.getElementById("shop-page-book-detail-number").innerHTML = num ;
  detail = global_space.get_book_detail() ;
  sum = detail.price * num ;
  document.getElementById("div-book-detail-sum").innerHTML = "小計：" + sum + " 元" ;
}

async function clicked_book_detail_putting_in_shopping_cart()
{
  let num ;
  let sum ;
  let detail ;
  let book_sn ;
  num = document.getElementById("shop-page-book-detail-number").innerHTML ;
  detail = global_space.get_book_detail() ;
  sum = detail.price * num ;
  book_sn = detail.book_serial_number ;
  
  await post_shopping_cart_api() ;
}

async function post_shopping_cart_api()
{
  let url = "/api/v1/shop/shopping-cart" ;
  let vbody ;
  let detail ;
  detail = global_space.get_book_detail() ;
  vbody = {
    "book_sn": detail.book_serial_number,
    "number": parseInt(document.getElementById("shop-page-book-detail-number").innerHTML)
  }
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      body: JSON.stringify(vbody)
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    if (retrieve_body.status === "success")
    {
      global_space.set_book_detail_number(0) ;
      document.getElementById("shop-page-book-detail-number").innerHTML = 0 ;
      document.getElementById("div-book-detail-sum").innerHTML = "小計：" + "0" + " 元" ;
      alert("成功加入購物車！") ;
      return ;
    }
    alert("失敗\n" + retrieve_body.message) ;
    return ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    alert (retrieve_body_string) ;
    return ;
    //let retrieve_body_string = JSON.stringify(retrieve_body) ;
    global_space.set_book_detail(retrieve_body) ;
    //alert (retrieve_body_string) ;
    return retrieve_body ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function clicked_book_detail_checkout()
{
  // 待開發功能
  let detail ;
  detail = global_space.get_book_detail() ;
  alert(detail.title) ;
}

async function item_detail(sn)
{
  // 參數 sn 應該要改成 book_id
  let output ;
  let detail ;
  
  detail = await get_book_detail_api(sn) ;
  output = "" ;
  if (! (detail.status === "success"))
  {
    document.getElementById("div-book-detail-overlay").style.display = "block" ;
    alert("失敗\n" + detail ["message"]) ;
    return ;
  }
  
  output = "/api/v1/shop/img/cover/" + sn ;
  document.getElementById("div-book-detail-cover-image").src = output ;
  document.getElementById("div-book-detail-overlay").style.display = "block" ;
  
  output = detail.title ;
  document.getElementById("div-book-detail-title").innerHTML = output ;
  
  output = "作者：" + detail.author ;
  document.getElementById("div-book-detail-author").innerHTML = output ;
  //output += "<div id = \"div-book-detail-author\">作者：" + detail.author + "</div>" ;
  //output += "<br />" ;
  output = "出版日期：" + detail.publication_date ;
  document.getElementById("div-book-detail-publication-date").innerHTML = output ;
  
  output = "ISBN：" + detail.isbn ;
  document.getElementById("div-book-detail-isbn").innerHTML = output ;
  
  output = detail.price ;
  document.getElementsByClassName("book-detail-price-number-part")[0].innerHTML = output ;
  
  global_space.set_book_detail_number(0) ;
  document.getElementById("shop-page-book-detail-number").innerHTML = 0 ;
  
  document.getElementById("div-book-detail-sum").innerHTML = "小計：0 元" ;
  
  //output += "<div id = \"div-book-detail-price\">價錢：<span class = \"book-detail-price-number\">" + detail.price + "</span>" + " 元</div>" ;
  //output += "<br />" ;
  //output += "</div>" ; // close for id div-book-detail-wrap-A
  //output += "</div>" ; // close for id div-book-detail-info
  
  //output += "數量：" ;
  //output += "<button id = \"shop-page-button-book-detail-number-minus\" onclick = \"clicked_book_detail_number_minus()\">-</button>" ;
  //output += "<span id = \"shop-page-book-detail-number\">0</span>" ;
  //output += "<button id = \"shop-page-button-book-detail-number-plus\" onclick = \"clicked_book_detail_number_plus()\">+</button>" ;
  //output += "<span id = \"div-book-detail-sum\">小計：0 元</span>" ;
  //output += "<button id = \"shop-page-button-book-detail-putting-in-shopping-cart\" onclick = \"clicked_book_detail_putting_in_shopping_cart()\">放入購物車</button>" ;
  //output += "<button id = \"shop-page-button-book-detail-checkout\" onclick = \"clicked_book_detail_checkout()\">直接結帳</button>" ;
  //output += "<br />" ;
  //output += "<br />" ;
  output = "提供人：" + detail.provider_name ;
  document.getElementById("div-book-detail-provider-name").innerHTML = output ;
  
  output = detail.introduction ;
  document.getElementById("div-book-detail-introduction").innerHTML = output ;
}

function clicked_book_detail_close()
{
  document.getElementById("div-book-detail-overlay").style.display = "none" ;
  global_space.set_book_detail_number(0) ;
}

async function clicked_books_list()
{
  let clicked ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_selected_tab("books-list") ;
  document.getElementById("shop-page-button-books-list").className = "button-selected" ;
  document.getElementById("div-books-list").style.display = "block" ;
  
  await get_books_list_api() ;
}

async function get_books_list_api()
{
  let url = "/api/v1/shop/books" ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      }
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    let output ;
    let rn ;
    let i ;
    
    if (! (retrieve_body.status == "success"))
    {
      document.getElementById("div-books-list").innerHTML = "Failed!!<br />" + retrieve_body.message ;
      return ;
    }
    
    global_space.set_books_list (retrieve_body) ;
    
    rn = retrieve_body.record_number ;
    if (rn <= 0)
    {
      return ;
    }
    
    output = "" ;
    for (i = 0 ; i < rn ; i ++)
    {
      output += "<div class = \"single-item-wrap\" onclick = \"item_detail(" + retrieve_body ["b_sn" + i] + ") ;\"><img src = \"" + "/api/v1/shop/img/thumbnail/" + retrieve_body ["b_sn" + i] + "\" class = \"item-image\" /><div class = \"item-title\">" + retrieve_body ["b_ti" + i] + "</div></div>" ;
    }
    document.getElementById("div-books-list").innerHTML = output ;
    
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function get_book_detail_api(book_id)
{
  let url = "/api/v1/shop/books/" ;
  url += book_id ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      }
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    //let retrieve_body_string = JSON.stringify(retrieve_body) ;
    global_space.set_book_detail(retrieve_body) ;
    //alert (retrieve_body_string) ;
    return retrieve_body ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
