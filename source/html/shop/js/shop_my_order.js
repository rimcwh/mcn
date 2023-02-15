
async function clicked_my_order()
{
  let clicked ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_selected_tab("my-order") ;
  document.getElementById("shop-page-button-my-order").className = "button-selected" ;
  document.getElementById("div-my-order").style.display = "block" ;

  await get_my_order_list_api() ;

  generate_html_code_for_my_orders_list() ;
}

function my_order_show_detail_toggle(index)
{
  let number ;
  let toggle ;
  let display_element ;
  let toggle_element, sign_element, text_element, detail_element ;
  number = global_space.get_my_order_detail_number() ;
  toggle = global_space.get_my_order_detail_toggle() ;

  toggle_element = document.getElementById("span-my-order-show-detail-toggle" + index) ;
  sign_element = document.getElementById("span-my-order-show-detail-toggle-sign" + index) ;
  text_element = document.getElementById("span-my-order-show-detail-toggle-text" + index) ;
  display_element = document.getElementById("div-my-order-display-detail" + index) ;

  if (toggle[index] == 0)
  {
    toggle_element.style.top = "13px" ;
    sign_element.innerHTML = "-" ;
    sign_element.style.left = "2px" ;
    sign_element.style.top = "-2px" ;
    text_element.style.display = "none" ;
    display_element.style.display = "inline-block" ;
    toggle[index] = 1 ;
  }
  else
  {
    toggle_element.style.top = "11px" ;
    sign_element.innerHTML = "+" ;
    sign_element.style.left = "0" ;
    sign_element.style.top = "0" ;
    text_element.style.display = "inline" ;
    display_element.style.display = "none" ;
    toggle[index] = 0 ;
  }
  global_space.set_my_order_detail_toggle(toggle) ;
}

async function get_my_order_list_api()
{
  let url = "/api/v1/shop/my-order/" ;
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
    global_space.set_my_order_list(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log("Request Failed", error) ;
  }
}

function count_my_order_number()
{
  var list ;
  var number = 0 ;
  var i ;
  var rn ;
  list = global_space.get_my_order_list() ;
  rn = list.record_number ;
  if (rn <= 0)
  {
    return 0 ;
  }
  number = 1 ;
  for (i = 1 ; i < rn ; i ++)
  {
    if (list ["order_serial_number" + i] != list ["order_serial_number" + (i - 1)])
    {
      number ++ ;
    }
  }
  return number ;
}

function init_my_order_detail_toggle(number)
{
  let detail_toggle = [] ;
  var i ;
  for (i = 0 ; i < number ; i ++)
  {
    detail_toggle [i] = 0 ;
  }
  global_space.set_my_order_detail_toggle(detail_toggle) ;
}

function generate_html_code_for_my_orders_list()
{
  var list ;
  var order_number ;
  var group_index, i ;
  var rn ;
  var output ;
  list = global_space.get_my_order_list() ;

  order_number = count_my_order_number() ;
  init_my_order_detail_toggle(order_number) ;

  group_index = 0 ;
  rn = list.record_number ;

  output = "" ;

  if (rn <= 0)
  {
    // only display header......
    document.getElementById("ul-my-order-item-list").innerHTML = "" ;
    document.getElementById("div-my-order-list-none-data").innerHTML = "您還沒有任何訂單。" ;
    document.getElementById("div-my-order-list-none-data").style.display = "block" ;
    return ;
  }
  document.getElementById("div-my-order-list-none-data").style.display = "none" ;
  output += generate_html_code_for_my_orders_single_contact_info_part_open(group_index, 0) ;
  output += generate_html_code_for_my_orders_single_detail(0) ;
  for (i = 1 ; i < rn ; i ++)
  {
    if (list ["order_serial_number" + i] != list ["order_serial_number" + (i - 1)])
    {
      output += generate_html_code_for_my_orders_single_contact_info_part_close() ;
      group_index ++ ;
      output += generate_html_code_for_my_orders_single_contact_info_part_open(group_index, i) ;
    }
    output += generate_html_code_for_my_orders_single_detail(i) ;
  }
  output += generate_html_code_for_my_orders_single_contact_info_part_close() ;
  document.getElementById("ul-my-order-item-list").innerHTML = output ;
}

function generate_html_code_for_my_orders_single_contact_info_part_open(group_index, index)
{
  var output ;
  var list ;
  list = global_space.get_my_order_list() ;
  output = "" ;

  output += "<li class = \"li-my-order-single-item\">" ;
    output += "<br />" ;
    if (group_index != 0)
    {
      output += "<div class = \"my-order-separator\"></div>"
    }
    output += "<div class = \"div-my-order-single-item-contact-info\">" ;
      output += "<div class = \"div-my-order-single-item-contact-info-serial-number\">訂單編號："
        + list ["order_serial_number" + index] + "</div>" ;
      output += "<div class = \"div-my-order-single-item-contact-info-date\">訂單日期："
        + list ["order_date" + index] + "</div>" ;
      output += "<div class = \"div-my-order-single-item-contact-info-name\">姓名："
        + list ["name" + index] + "</div>" ;
      output += "<div class = \"div-my-order-single-item-contact-info-tel\">電話："
        + list ["tel" + index] + "</div>" ;
      output += "<div class = \"div-my-order-single-item-contact-info-address\">地址："
        + list ["address" + index] + "</div>" ;
      output += "<div class = \"div-my-order-single-item-contact-info-total\">總價："
        + list ["total" + index] + " 元</div>" ;
    output += "</div>" ;

    output += "<div class = \"div-my-order-show-detail-toggle-wrap\">" ;
      output += "<span class = \"span-my-order-show-detail-lead-line\"></span>" ;
      output += "<div class = \"div-my-order-show-detail-toggle\">" ;
        output += "<span class = \"span-my-order-show-detail-toggle\" id = \"span-my-order-show-detail-toggle" + group_index + "\" onclick = \"my_order_show_detail_toggle(" + group_index + ")\">" ;
          output += "<span class = \"span-my-order-show-detail-toggle-sign\" id = \"span-my-order-show-detail-toggle-sign" + group_index + "\">+</span>" ;
        output += "</span>" ;
        output += "<span class = \"span-my-order-show-detail-toggle-text\" id = \"span-my-order-show-detail-toggle-text" + group_index + "\" onclick = \"my_order_show_detail_toggle(" + group_index + ")\">&nbsp;&nbsp;看明細</span>" ;
      output += "</div>" ;

      output += "<div class = \"div-my-order-display-detail\" id = \"div-my-order-display-detail" + group_index + "\">" ;
        output += "<table class = \"my-order-detail-header\"><tbody><tr class = \"tr-my-order-item-header\">" ;
          output += "<td class = \"td-my-order-item-header-book-info\">書籍明細</td>" ;
          output += "<td class = \"td-my-order-item-header-book-price\">單價</td>" ;
          output += "<td class = \"td-my-order-item-header-book-number\">數量</td>" ;
          output += "<td class = \"td-my-order-item-header-book-sum\">小計</td>" ;
          output += "<td class = \"td-my-order-item-header-changing\">變更</td>" ;
        output += "</tr></tbody></table>" ;

        output += "<table class = \"table-my-order-single-item-detail\">" ;
  return output ;
}

function generate_html_code_for_my_orders_single_contact_info_part_close()
{
  var output ;
  output = "" ;

        output += "</table>" ;
      output += "</div>" ;
    output += "</div>" ;
  output += "</li>" ;
  return output ;
}

function generate_html_code_for_my_orders_single_detail(index)
{
  var output ;
  var list ;
  var normal_last_detail_selector ;
  var rn ;
  list = global_space.get_my_order_list() ;
  output = "" ;

  rn = list.record_number ;

  normal_last_detail_selector = "normal" ;
  if (index + 1 === rn)
  {
    normal_last_detail_selector = "last" ;
  }
  else
  {
    if (list ["order_serial_number" + index] != list ["order_serial_number" + (index + 1)])
    {
      normal_last_detail_selector = "last" ;
    }
  }
  output += "<tr class = \"tr-my-order-item-detail\">" ;
    
    output += "<td class = \"td-my-order-item-book-info-" + normal_last_detail_selector + "\"><img src = \"" + "/api/v1/shop/img/thumbnail/" + list ["book_id" + index] + "\" class = \"my-order-item-image\" /><div class = \"my-order-item-title\">" + list ["title" + index] + "</div></td>" ;
    
    output += "<td class = \"td-my-order-item-book-price-" + normal_last_detail_selector + "\">" + list ["price" + index] + " 元</td>" ;
    
    output += "<td class = \"td-my-order-item-book-number-" + normal_last_detail_selector + "\"><span class = \"span-my-order-item-book-number\">" + list ["number" + index] + "</span></td>" ;
    
    output += "<td class = \"td-my-order-item-book-sum-" + normal_last_detail_selector + "\">" + (list ["price" + index] * list ["number" + index]) + " 元</td>" ;

    output += "<td class = \"td-my-order-item-changing-" + normal_last_detail_selector +  "\"><button class = \"button-my-order-item-deleting\" onclick=\"clicked_delete_my_order_item(" + list ["order_serial_number" + index] + ", " + list ["book_id" + index] + ") ;\">刪除</button></td>" ;
  output += "</tr>" ;
  return output ;
}

function clicked_delete_my_order_item(order_id, book_id)
{
  document.getElementById("button-my-order-confirm-deleting-yes").onclick = function() {
    clicked_button_my_order_status_info_confirm_deleting(order_id, book_id) ;
  } ;

  document.getElementById("button-my-order-confirm-deleting-close").style.display = "none" ;
  document.getElementById("button-my-order-confirm-deleting-yes").style.display = "inline-block" ;
  document.getElementById("button-my-order-confirm-deleting-no").style.display = "inline-block" ;
  document.getElementById("div-my-order-status-info-poparea-text").innerHTML = "確定要刪除嗎？" ;
  document.getElementById("div-my-order-status-info-overlay").style.display = "block" ;
}

function clicked_button_my_order_status_info_cancel()
{
  document.getElementById("div-my-order-status-info-overlay").style.display = "none" ;
}

function clicked_button_my_order_status_info_close()
{
  document.getElementById("div-my-order-status-info-overlay").style.display = "none" ;
}

async function clicked_button_my_order_status_info_confirm_deleting(order_id, book_id)
{
  let temp_result ;
  document.getElementById("button-my-order-confirm-deleting-yes").style.display = "none" ;
  document.getElementById("button-my-order-confirm-deleting-no").style.display = "none" ;
  
  document.getElementById("div-my-order-status-info-poparea-text").innerHTML = "確定刪除！" ;

  document.getElementById("button-my-order-confirm-deleting-close").style.display = "inline-block" ;
  await delete_my_order_item_api(order_id, book_id) ;
  temp_result = global_space.get_temp_result() ;

  if (temp_result["status"] === "success")
  {
    document.getElementById("div-my-order-status-info-poparea-text").innerHTML = "確定刪除！" ;
    document.getElementById("button-my-order-confirm-deleting-close").style.display = "inline-block" ;
    await get_my_order_list_api() ;
    generate_html_code_for_my_orders_list() ;
  }
  else
  {
    document.getElementById("div-my-order-status-info-poparea-text").innerHTML = "失敗<br />" + temp_result["message"] ;
    document.getElementById("button-my-order-confirm-deleting-close").style.display = "inline-block" ;
  }
}

async function delete_my_order_item_api(order_id, book_id)
{
  let url = "/api/v1/shop/my-order/" ;
  url += localStorage.getItem("user_id") + "/" + order_id + "/" + book_id ;
  try
  {
    const response = await fetch (url, {
      method: "DELETE",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      }
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    global_space.set_temp_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}
