
async function clicked_shopping_cart_page()
{
  let clicked ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_selected_tab("shopping-cart") ;
  document.getElementById("shop-page-button-shopping-cart").className = "button-selected" ;
  document.getElementById("div-shopping-cart").style.display = "block" ;
  
  await get_shopping_cart_api() ;
}

async function get_shopping_cart_api()
{
  let url = "/api/v1/shop/shopping-cart/" ;
  url += localStorage.getItem("user_id") ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      }
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;

    if (! (retrieve_body.status == "success"))
    {
      alert(retrieve_body.message) ;
      return ;
    }
    
    let output ;
    let rn ;
    let i ;
    
    rn = retrieve_body.record_number ;
    if (rn <= 0)
    {
      document.getElementById("div-shopping-cart-total").innerHTML = "總金額 0 元" ;
      global_space.set_shopping_cart_total_price (0) ;
      document.getElementById("div-shopping-cart-item-list").innerHTML = "" ;

      return ;
    }
    
    let total_price ;
    let normal_or_last_item_selector ;
    output = "" ;
    total_price = 0 ;
    normal_or_last_item_selector = "normal" ;
    for (i = 0 ; i < rn ; i ++)
    {
      if (i == rn - 1)
      {
        normal_or_last_item_selector = "last" ;
      }

      output += "<li class = \"li-shopping-cart-single-item\">" ;
      
        output += "<table class = \"table-shopping-cart-single-item-" + normal_or_last_item_selector + "\"><tr>" ;
        
          output += "<td class = \"td-shopping-cart-single-item-book-info\">" ;
            output += "<img src = \"" + "/api/v1/shop/img/thumbnail/" + retrieve_body ["book_id" + i] + "\" class = \"shopping-cart-item-image\" />" ;
            output += "<div class = \"shopping-cart-item-title\">" + retrieve_body ["title" + i] + "</div>" ;
          output += "</td>" ;
          
          output += "<td class = \"td-shopping-cart-single-item-price\">" ;
            output += "<span class = \"book-detail-price-number-part\">" + retrieve_body ["price" + i] + "</span> 元" ;
          output += "</td>" ;
          
          output += "<td class = \"td-shopping-cart-single-item-number\">" ;
            output += "　" + retrieve_body ["number" + i] + "　" ;
          output += "</td>" ;
          
          output += "<td class = \"td-shopping-cart-single-item-sum\">" ;
            output += retrieve_body ["price" + i] * retrieve_body ["number" + i] + " 元" ;
          output += "</td>" ;
          
          output += "<td class = \"td-shopping-cart-single-item-change-detail\">" ;
            output += "<button id = \"button-shopping-cart-delete" + i + "\" class = \"shopping-cart-single-item-button-delete\" onclick = \"clicked_shopping_cart_delete_item(" + i + ", " + retrieve_body ["sc_id" + i]  + ")\">刪除</button>" ;
          output += "</td>" ;
        
        output += "</tr></table>" ;
      
      output += "</li>" ;
      
      total_price += (retrieve_body ["price" + i] * retrieve_body ["number" + i]) ;
    }
    //alert(output) ;
    document.getElementById("div-shopping-cart-item-list").innerHTML = output ;
    
    document.getElementById("div-shopping-cart-total").innerHTML = "總金額 " + total_price + " 元" ;
    
    global_space.set_shopping_cart_total_price (total_price) ;
    
    return ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_shopping_cart_checkout()
{
  let basic_info ;
  document.getElementById("div-shopping-cart-checkout-overlay").style.display = "block" ;

  await get_basic_info_api() ;
  basic_info = global_space.get_basic_info() ;
  document.getElementById("input-shopping-cart-checkout-name").value = basic_info.name ;
  document.getElementById("input-shopping-cart-checkout-tel").value = basic_info.tel ;
  document.getElementById("input-shopping-cart-checkout-address").value = basic_info.address ;

  document.getElementById("span-display-shopping-cart-checkout-total-price").innerHTML = global_space.get_shopping_cart_total_price() ;
}

async function clicked_shopping_cart_checkout_sending()
{
  let data ;
  let name, tel, address ;
  let result ;
  document.getElementById("shopping-cart-button-checkout-sending").disabled = true ;
  name = document.getElementById("input-shopping-cart-checkout-name").value ;
  tel = document.getElementById("input-shopping-cart-checkout-tel").value ;
  address = document.getElementById("input-shopping-cart-checkout-address").value ;
  data = {
    "name": name,
    "tel": tel,
    "address": address
  } ;
  data ["order_from"] = "shopping_cart" ;
  
  await post_checkout_order_api(data) ;
  result = global_space.get_shopping_cart_rr_post_checkout_order_api() ;
  
  document.getElementById("shopping-cart-button-checkout-sending").disabled = false ;
}

async function post_checkout_order_api(data)
{
  let url = "/api/v1/users/" ;
  url += localStorage.getItem("user_id") ;
  url += "/orders" ;

  request_body = JSON.stringify(data) ;

  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      body: request_body
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json() ;
    global_space.set_shopping_cart_rr_post_checkout_order_api(retrieve_body) ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    //alert(retrieve_body_string) ;
    if (! (retrieve_body.status == "success"))
    {
      alert("失敗！\n\n" + retrieve_body.message) ;
      return ;
    }
    document.getElementById("div-shopping-cart-item-list").innerHTML = "" ;
    document.getElementById("div-shopping-cart-total").innerHTML = "總金額 0 元" ;
    global_space.set_shopping_cart_total_price(0) ;
    document.getElementById("span-display-shopping-cart-checkout-total-price").innerHTML = global_space.get_shopping_cart_total_price() ;
    document.getElementById("div-shopping-cart-checkout-overlay").style.display = "none" ;
    alert("成功！") ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_shopping_cart_delete_item(buttonIndex, sc_id)
{
  var ret ;
  document.getElementById("button-shopping-cart-delete" + buttonIndex).disabled = true ;
  ret = await delete_shopping_cart_item(sc_id) ;
  document.getElementById("button-shopping-cart-delete" + buttonIndex).disabled = false ;
  if (ret == 0)
  {
    get_shopping_cart_api() ;
  }
}

async function delete_shopping_cart_item(sc_id)
{
  let url = "/api/v1/shop/shopping-cart/" ;
  url += localStorage.getItem("user_id") ;
  url += "/" + sc_id ;

  try
  {
    const response = await fetch (url, {
      method: "DELETE",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;

    if (! (retrieve_body.status == "success"))
    {
      alert("失敗！\n\n" + retrieve_body.message) ;
      return -1 ;
    }
    alert("成功！") ;
    return 0 ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function clicked_shopping_cart_checkout_detail_close()
{
  document.getElementById("div-shopping-cart-checkout-overlay").style.display = "none" ;
}
