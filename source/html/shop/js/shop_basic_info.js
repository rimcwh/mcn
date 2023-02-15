
async function clicked_basic_info ()
{
  let clicked ;
  let basic_info ;
  var element ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("shop-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  // change css class
  global_space.set_selected_tab("basic-info") ;
  document.getElementById("shop-page-button-basic-info").className = "button-selected" ;
  document.getElementById("div-basic-info").style.display = "block" ;

  await get_basic_info_api() ;
  basic_info = global_space.get_basic_info() ;
  document.getElementById("input-basic-info-name").value = basic_info.name ;
  document.getElementById("input-basic-info-tel").value = basic_info.tel ;
  document.getElementById("input-basic-info-address").value = basic_info.address ;

  element = document.getElementById("checkbox-send-mail") ;
  if (basic_info.email_verified_status === 0)
  {
    element.disabled = true ;
  }
  else
  {
    element.disabled = false ;
    if (basic_info ["email_notification"] === 1)
    {
      element.checked = true ;
    }
    else
    {
      element.checked = false ;
    }
  }

  return ;
}

async function clicked_basic_info_updating()
{
  var result ;
  await patch_basic_info_api() ;
  result = global_space.get_rr_patch_basic_info_api() ;

  if (typeof result != "object")
  {
    alert("連線錯誤") ;
    return -1 ;
  }
  if (! result.hasOwnProperty("status"))
  {
    alert("連線錯誤") ;
    return -1 ;
  }
  if (result ["status"] !== "success")
  {
    alert("失敗\n" + result ["message"]) ;
    return -1 ;
  }

  if (result ["message"] === "no change")
  {
    alert("資料沒有變動！") ;
    return 1 ;
  }

  alert("更新成功！") ;
}

async function get_basic_info_api()
{
  let url = "/api/v1/shop/basic-info/" ;
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
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    //alert (retrieve_body_string) ;
    global_space.set_basic_info(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function patch_basic_info_api()
{
  let url = "/api/v1/shop/basic-info/" ;
  let vbody ;
  let request_body ;
  let name ;
  let tel ;
  let address ;
  let checkbox_value ;
  let email_notification ;

  url += localStorage.getItem("user_id") ;

  name = document.getElementById("input-basic-info-name").value ;
  tel = document.getElementById("input-basic-info-tel").value ;
  address = document.getElementById("input-basic-info-address").value ;
  checkbox_value = document.getElementById("checkbox-send-mail").checked ;
  if (checkbox_value === true)
  {
    email_notification = 1 ;
  }
  else
  {
    email_notification = 0 ;
  }

  vbody = {
    "name": name
    , "tel": tel
    , "address": address
    , "email_notification": email_notification
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
    global_space.set_rr_patch_basic_info_api (retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}
