
async function clicked_my_shop_page()
{
  let clicked ;
  let my_shop_clicked ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_selected_tab("my-shop") ;
  document.getElementById("shop-page-button-my-shop").className = "button-selected" ;
  document.getElementById("div-my-shop").style.display = "block" ;

  my_shop_clicked = global_space.get_my_shop_selected_tab() ;
  if (my_shop_clicked == "checking-orders")
  {
    await clicked_my_shop_checking_orders() ;
  }
  if (my_shop_clicked == "launched-books")
  {
    await clicked_my_shop_launched_books() ;
  }
}

async function clicked_my_shop_checking_orders()
{
  let clicked ;
  clicked = global_space.get_my_shop_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-my-shop-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_my_shop_selected_tab("checking-orders") ;
  document.getElementById("shop-page-button-checking-orders").className = "button-selected" ;
  document.getElementById("div-my-shop-checking-orders").style.display = "block" ;

  await get_my_shop_orders_list_api() ;

  generate_html_code_for_my_shop_orders_list() ;
}

function clicked_my_shop_new_book()
{
  let clicked ;
  clicked = global_space.get_my_shop_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-my-shop-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_my_shop_selected_tab("new-book") ;
  document.getElementById("shop-page-button-new-book").className = "button-selected" ;
  document.getElementById("div-my-shop-new-book").style.display = "block" ;
}

async function clicked_my_shop_launched_books()
{
  let clicked ;
  clicked = global_space.get_my_shop_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-my-shop-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_my_shop_selected_tab("launched-books") ;
  document.getElementById("shop-page-button-launched-books").className = "button-selected" ;
  document.getElementById("div-my-shop-launched-books").style.display = "block" ;

  await get_my_shop_launched_books_list_api() ;

  generate_html_code_for_my_shop_launched_books_list() ;
}

async function clicked_my_shop_new_book_submit()
{
  let input_file ;
  let file ;
  let form_data = new FormData() ;
  let url = "/api/v1/shop/books" ;
  let book_price ;
  let book_title ;
  let book_publication_date ;
  let book_author ;
  let book_isbn ;
  let book_intro ;
  document.getElementById("shop-page-button-new-book-submit").disabled = true ;
  book_price = Math.abs(Number(document.getElementById("input-new-book-price").value)) ;
  book_title = document.getElementById("input-new-book-title").value ;
  book_publication_date = document.getElementById("input-new-book-publication-date").value ;
  book_author = document.getElementById("input-new-book-author").value ;
  book_isbn = document.getElementById("input-new-book-isbn").value ;
  book_intro = document.getElementById("input-new-book-intro").value ;
  //alert(book_title + '\n' + book_publication_date + '\n' + book_author + '\n' + book_isbn + '\n' + book_intro) ;
  
  input_file = document.getElementById("file-uploader") ;
  file = input_file.files [0] ;
  //alert(file.name + '\n' + file.size + '\n' + file.type + '\n' + file.lastModifiedDate) ;
  form_data.append("book_price", book_price) ;
  form_data.append("book_title", book_title) ;
  form_data.append("book_author", book_author) ;
  form_data.append("book_publication_date", book_publication_date) ;
  form_data.append("book_isbn", book_isbn) ;
  form_data.append("book_intro", book_intro) ;
  form_data.append("the_file", file) ;
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        //"Content-type": "application/json; charset=UTF-8",
        "X-Csrf-Token": load_csrf_token()
      },
      //body: 'account_id=' + encodeURIComponent (filtered_account_id) + '&password=' + encodeURIComponent (filtered_password),
      body: form_data
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    //alert('yo2') ;
    const retrieve_body = await response.json() ;
    //document.getElementById("result").innerHTML = JSON.stringify(retrieve_body) ;
    if (! (retrieve_body.status == "success"))
    {
      alert(retrieve_body.message) ;
      document.getElementById("shop-page-button-new-book-submit").disabled = false ;
      return ;
    }
    alert("新增成功！") ;
    
    document.getElementById("input-new-book-price").value = "" ;
    document.getElementById("input-new-book-title").value = "" ;
    document.getElementById("input-new-book-publication-date").value = "" ;
    document.getElementById("input-new-book-author").value = "" ;
    document.getElementById("input-new-book-isbn").value = "" ;
    document.getElementById("input-new-book-intro").value = "" ;
    document.getElementById("file-uploader").value = "" ;
    
    document.getElementById("shop-page-button-new-book-submit").disabled = false ;

    return ;
    let output ;
    output = JSON.stringify(retrieve_body) ;
    alert(output) ;
    return ;
    //global_space.as_set (result) ;
    output = JSON.stringify (result) ;
    alert (output) ;
    return ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function get_my_shop_launched_books_list_api()
{
  let url = "/api/v1/shop/my-shop/launched-books/" ;
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
    global_space.set_my_shop_launched_books_list(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function generate_html_code_for_my_shop_orders_list()
{
  var list ;
  var code ;
  var i, rn ;
  code = "" ;
  list = global_space.get_my_shop_orders_list() ;
  if (list ["status"] !== "success")
  {
    alert("失敗\n" + list ["message"]) ;
    return ;
  }
  rn = list.record_number ;
  if (rn <= 0)
  {
    code = "沒有資料！" ;
    return ;
  }
  for (i = 0 ; i < rn ; i ++)
  {
    code += generate_html_code_for_my_shop_orders_list_single_item(rn, i, list) ;
  }
  document.getElementById("ul-my-shop-orders-list-item-list").innerHTML = code ;
}

function generate_html_code_for_my_shop_orders_list_single_item(rn, i, list)
{
  var code ;
  var normal_last_selector ;
  if (i + 1 == rn)
  {
    normal_last_selector = "last" ;
  }
  else
  {
    normal_last_selector = "normal" ;
  }

  code = "" ;
  code += "<li class = \"li-my-shop-orders-list-single-item\">" ;

  code += "<table class = \"table-my-shop-orders-list-single-item-" +
    normal_last_selector + "\">" ;

  code += "<tr>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-order-id\">" +
      list ["order_id" + i] ;
    code += "</td>" ;

    //code += "<td class = \"td-my-shop-orders-list-single-item-orderer-id\">" +
    //  list ["orderer_id" + i] + "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-order-date\">" +
      list ["order_date" + i] +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-name\">" +
      list ["name" + i] +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-tel\">" +
      list ["tel" + i] +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-address\">" +
      list ["address" + i] +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-book-id\">" +
      list ["book_id" + i] +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-book-isbn\">" +
      list ["isbn" + i] +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-book-title\">" +
      list ["title" + i] +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-price\">" +
      list ["price" + i] + " 元" +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-number\">" +
      "<span class=\"span-my-order-item-book-number\">" +
      list ["number" + i] +
      "</span>" +
      "</td>" ;

    code += "<td class = \"td-my-shop-orders-list-single-item-sum\">" +
      list ["price" + i] * list ["number" + i] + " 元" +
      "</td>" ;

  code += "</tr>" ;

  code += "</table>" ;

  code += "</li>" ;

  return code ;
}

function generate_html_code_for_my_shop_launched_books_list()
{
  var list ;
  var code ;
  var i, rn ;
  code = "" ;
  list = global_space.get_my_shop_launched_books_list() ;
  rn = list.record_number ;
  if (rn <= 0)
  {
    code = "沒有資料！" ;
    return ;
  }

  for (i = 0 ; i < rn ; i ++)
  {
    code += generate_html_code_for_my_shop_launched_books_single_item(rn, i, list) ;
  }

  document.getElementById("ul-my-shop-launched-books-item-list").innerHTML = code ;
}

function generate_html_code_for_my_shop_launched_books_single_item(rn, i, list)
{
  var code ;
  var normal_last_selector ;
  if (i + 1 == rn)
  {
    normal_last_selector = "last" ;
  }
  else
  {
    normal_last_selector = "normal" ;
  }

  code = "" ;
  code += "<li class = \"li-my-shop-launched-books-single-item\">" ;

  code += "<table class = \"table-my-shop-launched-books-single-item-" +
    normal_last_selector + "\">" ;

  code += "<tr>" ;

    code += "<td class = \"td-my-shop-launched-books-single-item-book-info\">" +
      "<img src = \"/api/v1/shop/img/thumbnail/" + list ["book_id" + i] + "\"" +
      " class = \"img-my-shop-launched-books-item-image\" />" +
      "<div class = \"div-my-shop-launched-books-item-title\">" + list ["title" + i] +
      "</div>" ;
    code += "</td>" ;

    code += "<td class = \"td-my-shop-launched-books-single-item-isbn\">" +
      list ["isbn" + i] + "</td>" ;

    code += "<td class = \"td-my-shop-launched-books-single-item-change-detail\">" +
      "<button class = \"button-my-shop-launched-books-single-item-edit\"" +
      " onclick = \"edit_my_shop_launched_books_detail(" + list ["book_id" + i] + ") ;\">" +
      " 編輯 </button>" +
      "</td>" ;

  code += "</tr>" ;

  code += "</table>" ;

  code += "</li>" ;

  return code ;
}

async function edit_my_shop_launched_books_detail(book_id)
{
  var detail ;
  await get_my_shop_launched_book_detail_api(book_id) ;
  detail = global_space.get_my_shop_launched_book_detail() ;
  //alert ("status: " + detail ["status"] + "\n\nmessage: " + detail ["message"]) ;
  //alert ("user_id: " + detail ["provider"] + "\n\nbook_id: " + detail ["book_id"]) ;
  //alert (JSON.stringify(detail)) ;
  if (detail ["status"] != "success")
  {
    document.getElementById("div-my-shop-launched-books-detail-status-info-poparea-text").innerHTML = detail ["message"] ;
    document.getElementById("div-my-shop-launched-books-detail-status-info-overlay").style.display = "block" ;
    return ;
  }
  document.getElementById("span-my-shop-launched-books-detail-book-id").innerHTML = book_id ;
  document.getElementById("input-my-shop-launched-books-detail-title").value = detail ["title"] ;
  document.getElementById("input-my-shop-launched-books-detail-author").value = detail ["author"] ;
  document.getElementById("input-my-shop-launched-books-detail-publication-date").value = detail ["publication_date"] ;
  document.getElementById("input-my-shop-launched-books-detail-isbn").value = detail ["isbn"] ;
  document.getElementById("input-my-shop-launched-books-detail-price").value = detail ["price"] ;
  document.getElementById("input-my-shop-launched-books-detail-intro").value = detail ["intro"] ;

  document.getElementById("img-my-shop-launched-books-detail-cover-image").src = "/api/v1/shop/img/cover/" + book_id ;
  
  if (detail ["book_status"] == 1)
  {
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").style.display = "none" ;
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").style.display = "inline-block" ;
  }
  else
  {
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").style.display = "none" ;
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").style.display = "inline-block" ;
  }

  document.getElementById("div-my-shop-launched-books-detail-overlay").style.display = "block" ;
}

function clicked_my_shop_launched_books_detail_edited_poparea_close()
{
  document.getElementById("div-my-shop-launched-books-detail-overlay").style.display = "none" ;
}

async function get_my_shop_launched_book_detail_api(book_id)
{
  let url = "/api/v1/shop/my-shop/launched-books/" ;
  url += localStorage.getItem("user_id") + "/" + book_id ;
  //url = url + localStorage.getItem("sn") + "/" + book_id + "000" ;
  //url = url + "1" + "/" + book_id ;
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
    global_space.set_my_shop_launched_book_detail(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function clicked_my_shop_launched_books_detail_status_info_poparea_close()
{
  document.getElementById("div-my-shop-launched-books-detail-status-info-overlay").style.display = "none" ;
}

async function clicked_my_shop_launched_books_detail_edited_poparea_updating_detail_send()
{
  var data ;
  var temp ;
  var temp_result ;
  data = {} ;
  temp = document.getElementById("input-my-shop-launched-books-detail-title").value ;
  data ["title"] = temp ;
  temp = document.getElementById("input-my-shop-launched-books-detail-author").value ;
  data ["author"] = temp ;
  temp = document.getElementById("input-my-shop-launched-books-detail-publication-date").value ;
  data ["publication_date"] = temp ;
  temp = document.getElementById("input-my-shop-launched-books-detail-isbn").value ;
  data ["isbn"] = temp ;
  temp = document.getElementById("input-my-shop-launched-books-detail-price").value ;
  data ["price"] = temp ;
  temp = document.getElementById("input-my-shop-launched-books-detail-intro").value ;
  data ["intro"] = temp ;
  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-updating-detail").disabled = true ;
  await patch_my_shop_launched_book_detail_api(data) ;
  temp_result = global_space.get_temp_result() ;
  set_my_shop_launched_books_updating_detail_status_info_poparea_attribute(temp_result) ;
  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").style.display = "block" ;
  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-updating-detail").disabled = false ;
}

async function patch_my_shop_launched_book_detail_api(data)
{
  let url = "/api/v1/shop/my-shop/launched-books/" ;
  let request_body ;
  var detail ;
  detail = global_space.get_my_shop_launched_book_detail() ;

  url += localStorage.getItem("user_id") + "/" + detail ["book_id"] ;
  request_body = JSON.stringify(data) ;
  try
  {
    const response = await fetch (url, {
      method: "PATCH",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      body: request_body
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    global_space.set_temp_result(retrieve_body) ;
    //alert (retrieve_body_string) ;
    return ;
    //document.getElementById("div-basic-info-result").innerHTML = retrieve_body_string ;
    if (retrieve_body.status == 'success')
    {
      alert ('更新成功！') ;
    }
    else
    {
      alert ('更新失敗！\n\n' + retrieve_body.message) ;
    }
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_my_shop_launched_books_detail_edited_poparea_discontinuing_book()
{
  var result ;
  var detail ;
  var book_id ;
  var temp_result ;
  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").disabled = true ;
  await patch_my_shop_launched_book_status_api(0) ;
  result = global_space.get_my_shop_launched_book_status_result() ;
  //alert(JSON.stringify(result)) ;
  temp_result = {} ;
  temp_result ["status"] = result ["status"] ;
  temp_result ["message"] = result ["message"] ;

  //temp_result = global_space.get_temp_result() ;
  set_my_shop_launched_books_updating_detail_status_info_poparea_attribute(temp_result) ;
  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").style.display = "block" ;

  detail = global_space.get_my_shop_launched_book_detail() ;
  book_id = detail ["book_id"] ;
  await get_my_shop_launched_book_detail_api(book_id) ;
  detail = global_space.get_my_shop_launched_book_detail() ;

  if (detail ["book_status"] == 1)
  {
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").style.display = "none" ;
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").style.display = "inline-block" ;
  }
  else
  {
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").style.display = "none" ;
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").style.display = "inline-block" ;
  }

  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").disabled = false ;
}

async function clicked_my_shop_launched_books_detail_edited_poparea_launching_book()
{
  var result ;
  var detail ;
  var book_id ;
  var temp_result ;
  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").disabled = true ;
  await patch_my_shop_launched_book_status_api(1) ;
  result = global_space.get_my_shop_launched_book_status_result() ;
  //alert(JSON.stringify(result)) ;
  temp_result = {} ;
  temp_result ["status"] = result ["status"] ;
  temp_result ["message"] = result ["message"] ;
  //temp_result = global_space.get_temp_result() ;
  set_my_shop_launched_books_updating_detail_status_info_poparea_attribute(temp_result) ;
  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").style.display = "block" ;

  detail = global_space.get_my_shop_launched_book_detail() ;
  book_id = detail ["book_id"] ;
  await get_my_shop_launched_book_detail_api(book_id) ;
  detail = global_space.get_my_shop_launched_book_detail() ;

  if (detail ["book_status"] == 1)
  {
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").style.display = "none" ;
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").style.display = "inline-block" ;
  }
  else
  {
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-discontinuing-book").style.display = "none" ;
    document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").style.display = "inline-block" ;
  }

  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-launching-book").disabled = false ;
}

async function patch_my_shop_launched_book_status_api(data)
{
  let url = "/api/v1/shop/my-shop/launched-books-status/" ;
  let request_body ;
  var detail ;
  var vbody ;
  detail = global_space.get_my_shop_launched_book_detail() ;

  url += localStorage.getItem("user_id") + "/" + detail ["book_id"] ;
  vbody = {
    "status": data
  } ;
  request_body = JSON.stringify(vbody) ;
  try
  {
    const response = await fetch (url, {
      method: "PATCH",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      body: request_body
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    global_space.set_my_shop_launched_book_status_result(retrieve_body) ;
    return ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    alert (retrieve_body_string) ;
    return ;
    //document.getElementById("div-basic-info-result").innerHTML = retrieve_body_string ;
    if (retrieve_body.status == "success")
    {
      alert ("更新成功！") ;
    }
    else
    {
      alert ("更新失敗！\n\n" + retrieve_body.message) ;
    }
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function set_my_shop_launched_books_updating_detail_status_info_poparea_attribute(result)
{
  var temp ;
  temp = result ["status"] ;
  if (temp == "success")
  {
    temp = "成功" ;
  }
  else
  {
    temp = "失敗" ;
  }
  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-poparea-title").innerHTML = temp ;
  temp = result ["message"] ;
  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-poparea-text").innerHTML = temp ;
}

function clicked_my_shop_launched_books_updating_detail_status_info_poparea_close()
{
  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").style.display = "none" ;
}

async function clicked_my_shop_launched_books_detail_edited_poparea_new_cover_image()
{
  var input_file ;
  var file ;
  var form_data = new FormData() ;
  var temp_result ;
  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-new-cover-image").disabled = true ;
  input_file = document.getElementById("updating-image-uploader") ;
  file = input_file.files [0] ;
  form_data.append("method_for", "PATCH") ;
  form_data.append("the_file", file) ;
  await post_my_shop_launched_book_cover_image_api(form_data) ;
  temp_result = global_space.get_temp_result() ;
  set_my_shop_launched_books_updating_detail_status_info_poparea_attribute(temp_result) ;
  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").style.display = "block" ;
  document.getElementById("button-my-shop-launched-books-detail-edited-poparea-new-cover-image").disabled = false;
}

async function post_my_shop_launched_book_cover_image_api(data)
{
  let url = "/api/v1/shop/my-shop/launched-books-cover-image/" ;
  let detail ;
  detail = global_space.get_my_shop_launched_book_detail() ;
  url += localStorage.getItem("user_id") + "/" + detail ["book_id"] ;
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        //"Content-type": "application/json; charset=UTF-8"
        "X-Csrf-Token": load_csrf_token()
      },
      body: data
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json() ;
    
    global_space.set_temp_result(retrieve_body) ;
    return ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function get_my_shop_orders_list_api()
{
  let url = "/api/v1/shop/my-shop/orders/" ;
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
    global_space.set_my_shop_orders_list(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_my_shop_orders_list_generating_pdf()
{
  let url = "/api/v1/shop/my-shop/orders/" ;
  url += localStorage.getItem("user_id") ;
  url += "/pdf" ;
  document.getElementById("shop-page-button-orders-list-generating-pdf").disabled = true ;
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
    const retrieve_body = await response.blob () ;
    const objectURL = URL.createObjectURL(retrieve_body) ;
    document.getElementById("shop-page-button-orders-list-generating-pdf").disabled = false ;
    window.open(objectURL, '_blank').focus() ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }

  /*
  var url = "/api/v1/shop/my-shop/orders/" ;
  url += localStorage.getItem("user_id") ;
  url += "/pdf" ;
  window.open(url, '_blank').focus() ;
  */
}
