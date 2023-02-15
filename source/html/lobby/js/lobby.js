
var global_space = (function()
{
  // shared variable
  // available only inside your module
  var selected_button = "" ;
  var request_get_account_detail_result ;
  var request_patch_email_result ;
  var resend_timer ;
  var resend_countdown ;
  var email_verified_status ;
  var request_post_verified_code_mail_result ;
  var request_patch_email_verified_status_result ;
  var rr_patch_password ;

  return {
    get: function()
    {
      selected_button ;
    },
    set: function(value)
    {
      selected_button = value ;
    },
    get_request_get_account_detail_result : function ()
    {
      return request_get_account_detail_result ;
    },
    set_request_get_account_detail_result : function (value)
    {
      request_get_account_detail_result = value ;
    },
    get_request_patch_email_result : function ()
    {
      return request_patch_email_result ;
    },
    set_request_patch_email_result : function (value)
    {
      request_patch_email_result = value ;
    },
    get_resend_timer : function ()
    {
      return resend_timer ;
    },
    set_resend_timer : function (value)
    {
      resend_timer = value ;
    },
    get_request_post_verified_code_mail_result : function ()
    {
      return request_post_verified_code_mail_result ;
    },
    set_request_post_verified_code_mail_result : function (value)
    {
      request_post_verified_code_mail_result = value ;
    },
    get_request_patch_email_verified_status_result : function ()
    {
      return request_patch_email_verified_status_result ;
    },
    set_request_patch_email_verified_status_result : function (value)
    {
      request_patch_email_verified_status_result = value ;
    },
    get_email_verified_status : function ()
    {
      return email_verified_status ;
    },
    set_email_verified_status : function (value)
    {
      email_verified_status = value ;
    },
    get_rr_patch_password : function ()
    {
      return rr_patch_password ;
    },
    set_rr_patch_password : function (value)
    {
      rr_patch_password = value ;
    },
    get_resend_countdown : function ()
    {
      return resend_countdown ;
    },
    set_resend_countdown : function (value)
    {
      resend_countdown = value ;
    }
  } ;
})() ;

window.onload = async function ()
{
  var result ;
  var temp ;
  save_csrf_token() ;
  if (localStorage.getItem("theme") === null)
  {
    localStorage.setItem("theme", "theme-light") ;
  }
  document.documentElement.className = localStorage.getItem("theme") ;

  await get_account_detail() ;
  result = global_space.get_request_get_account_detail_result() ;

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
  if (result ["status"] != "success")
  {
    alert(result ["message"]) ;
    return -1 ;
  }

  document.getElementById("show-account").innerHTML = "帳號：" + result ["account_id"] ;
  if (result ["email"] === null)
  {
    temp = "未設定" ;
  }
  else
  {
    temp = result ["email"] ;
    //temp = "demo 中，暫時保密。"
  }
  document.getElementById("show-email").innerHTML = "E-mail：" + temp ;

  global_space.set_email_verified_status(result ["email_verified_status"]) ;
  display_element_with_email_verified_status() ;

  if (result ["theme"] === 0)
  {
    document.getElementById("checkbox-switch-theme").checked = false ;
    document.documentElement.className = "theme-light" ;
    localStorage.setItem("theme", "theme-light") ;
  }
  if (result ["theme"] === 1)
  {
    document.getElementById("checkbox-switch-theme").checked = true ;
    document.documentElement.className = "theme-dark" ;
    localStorage.setItem("theme", "theme-dark") ;
  }
}

window.onbeforeunload = function(event)
{
  
}

function save_csrf_token()
{
  var token ;
  token = "" ;
  if (
    ! (sessionStorage.getItem("csrf-token") === null)
    &&
    (localStorage.getItem("csrf-token") === null)
  )
  {
    token = sessionStorage.getItem("csrf-token") ;
    sessionStorage.removeItem("csrf-token") ;
    if (! (token === ""))
    {
      write_csrf_token(token) ;
      document.getElementById("div-csrf-token").innerText = token ;
    }
  }
  return 1 ;
}

function resave_csrf_token(headers)
{
  var update ;
  var token ;
  /*var tm ;
  tm = "" ;
  headers.forEach(
    (value, key) => tm += key + ":" + value + "  type=" + typeof value + "\n"
  );
  alert(tm) ;*/
  if (! (headers.has("x-update-csrf-token")))
  {
    return ;
  }
  update = headers.get("x-update-csrf-token") ;
  if (! (update === "1"))
  {
    return ;
  }
  if (! (headers.has("x-new-csrf-token")))
  {
    return ;
  }
  token = headers.get("x-new-csrf-token") ;
  write_csrf_token(token) ;
  document.getElementById("div-csrf-token").innerText = token ;
}

function load_csrf_token()
{
  return localStorage.getItem("csrf-token") ;
}

function write_csrf_token(token)
{
  localStorage.setItem("csrf-token", token) ;
}

function transfer_csrf_token()
{
  
}

function nav_chat()
{
  transfer_csrf_token() ;
  window.location.href = "../chat/chat.html" ;
}

function nav_shop()
{
  transfer_csrf_token() ;
  window.location.href = "../shop/shop.html" ;
}

function nav_bingo()
{
  transfer_csrf_token() ;
  window.location.href = "../bingo/bingo.html" ;
}

function display_element_with_email_verified_status()
{
  var verified_status ;
  verified_status = global_space.get_email_verified_status() ;
  if (verified_status === 1)
  {
    temp = "已認證" ;
    document.getElementById("button-show-email-verified-wrap").style.display = "none" ;
  }
  else
  {
    temp = "未認證" ;
    document.getElementById("div-email-verified-wrap").style.display = "none" ;
    document.getElementById("button-show-email-verified-wrap").style.display = "inline-block" ;
    document.getElementById("show-send-mail-error").innerHTML = "" ;
  }
  document.getElementById("show-email-verified").innerHTML = "E-mail 認證：" + temp ;
}

async function logout_account()
{
  try
  {
    url = "/api/v1/logout" ;
    const response = await fetch (url, {
      method: "GET",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8",
      },
      //body: 'account_id=' + encodeURIComponent (filtered_account_id) + '&password=' + encodeURIComponent (filtered_password),
      //body: JSON.stringify (vbody)
    }) ;
    localStorage.removeItem ("user_id") ;
    localStorage.removeItem ("csrf-token") ;
    window.location.replace ('../index.html') ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
  return ;
}

async function get_account_detail()
{
  let input_value, filtered_value ;
  let filtered_account_id, filtered_password ;
  let url = "/api/v1/account/" ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
  url += localStorage.getItem("user_id") ;
  try
  {
    const response = await fetch(url, {
      method: "GET",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      //body: 'account_id=' + encodeURIComponent (filtered_account_id) + '&password=' + encodeURIComponent (filtered_password),
      //body: JSON.stringify (vbody)
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json() ;
    global_space.set_request_get_account_detail_result(retrieve_body) ;
    return ;
    let output, temp ;
    output = JSON.stringify(retrieve_body) + "<br />" ;
    document.getElementById("show-account").innerHTML = "帳號：" + retrieve_body.account_id ;
    document.getElementById("show-email").innerHTML = "E-mail：" + retrieve_body.email ;//"demo 中，暫時保密。" ; //
    if (retrieve_body ["email_verified_status"] === 1)
    {
      temp = "已認證" ;
    }
    else
    {
      temp = "未認證" ;
    }
    document.getElementById("show-email-verified").innerHTML = "E-mail 認證：" + temp ;
    if (! retrieve_body.hasOwnProperty("theme"))
    {
      output = "[ 資料錯誤，refresh page ]" ;
    }
    if (retrieve_body.theme === 0)
    {
      document.getElementById("checkbox-switch-theme").checked = false ;
      document.documentElement.className = "theme-light" ;
      localStorage.setItem("theme", "theme-light") ;
    }
    if (retrieve_body.theme === 1)
    {
      document.getElementById("checkbox-switch-theme").checked = true ;
      document.documentElement.className = "theme-dark" ;
      localStorage.setItem("theme", "theme-dark") ;
    }
    return ;
    if (! result.hasOwnProperty ("Token"))
    {
      output += "has not Token<br />" ;
    }
    else
    {
      output += "has Token<br />" ;
    }
    output = result . status + "<br />" + "typeof: " + typeof result . status ;
    if (result . status == "success")
    {
      let base64Url = result . jwt . split (".") [1] ;
      let base64 = base64Url . replace (/-/g, "+") . replace (/_/g, "/") ;
      json_payload = decodeURIComponent (atob (base64) . split ("") . map (function (c) {
        return "%" + ("00" + c . charCodeAt (0) . toString (16)) . slice (-2) ;
      }) . join ("")) ;
      jwt_header = JSON.parse (window.atob (result.jwt.split('.')[0])) ;
      let uun = JSON.parse (json_payload) ;
      output = "parse sn: " + uun . sn ;
    }

    document.getElementById("result").innerHTML = output ;
    window.location.replace("../account/main.html") ;
  } catch (error)
  {
    console.log("Request Failed", error) ;
  }
}

async function switch_theme()
{
  var c ;
  c = document.getElementById("checkbox-switch-theme").checked ;
  if (c === true)
  {
    document.documentElement.className = "theme-dark" ;
    localStorage.setItem("theme", "theme-dark") ;
  }
  else
  {
    document.documentElement.className = "theme-light" ;
    localStorage.setItem("theme", "theme-light") ;
  }
  await update_theme_to_server() ;
}

async function update_theme_to_server()
{
  let url = "/api/v1/account/" ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
  var check_status ;
  let request_body ;
  check_status = document.getElementById("checkbox-switch-theme").checked ;
  if (check_status === true)
  {
    vbody = {
      "theme": 1
    } ;
  }
  else
  {
    vbody = {
      "theme": 0
    } ;
  }
  request_body = JSON.stringify(vbody) ;
  url += localStorage.getItem("user_id") ;
  try
  {
    const response = await fetch(url, {
      method: "PATCH",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      //body: 'account_id=' + encodeURIComponent (filtered_account_id) + '&password=' + encodeURIComponent (filtered_password),
      //body: JSON.stringify (vbody)
      body: request_body
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json() ;
    if (retrieve_body.status == "success")
    {
      //document.getElementById("show-result").innerHTML = "change OK, " + retrieve_body.message ;
      // 改 session 欄位
    }
    else
    {
      alert("失敗\n" + retrieve_body ["message"]) ;
      document.getElementById("show-result").innerHTML = "change failed, " + retrieve_body.message ;
    }
    return ;
    let output ;
    output = JSON.stringify(retrieve_body) + "<br />" ;
    document.getElementById("show-account").innerHTML = "帳號：" + retrieve_body.account_id ;
    document.getElementById("show-email").innerHTML = "E-mail：" + retrieve_body.email ;
    if (! retrieve_body.hasOwnProperty("theme"))
    {
      output = "[ 資料錯誤，refresh page ]" ;
    }
    if (retrieve_body.theme === 0)
    {
      document.getElementById("checkbox-switch-theme").checked = false ;
      document.documentElement.className = "theme-light" ;
    }
    if (retrieve_body.theme === 1)
    {
      document.getElementById("checkbox-switch-theme").checked = true ;
      document.documentElement.className = "theme-dark" ;
    }
    return ;
    if (! result.hasOwnProperty ("Token"))
    {
      output += "has not Token<br />" ;
    }
    else
    {
      output += "has Token<br />" ;
    }
    output = result . status + "<br />" + "typeof: " + typeof result . status ;
    if (result . status == "success")
    {
      let base64Url = result . jwt . split (".") [1] ;
      let base64 = base64Url . replace (/-/g, '+') . replace (/_/g, "/") ;
      json_payload = decodeURIComponent (atob (base64) . split ("") . map (function (c) {
        return "%" + ("00" + c . charCodeAt (0) . toString (16)) . slice (-2) ;
      }) . join ("")) ;
      jwt_header = JSON.parse (window.atob (result.jwt.split(".")[0])) ;
      let uun = JSON.parse (json_payload) ;
      output = "parse sn: " + uun . sn ;
    }

    document.getElementById("result").innerHTML = output ;
    window.location.replace("../account/main.html") ;
    /*if (0 != result.result)
    {
      document.getElementById ("result") . innerHTML = "failed: <br />" + result.msg + "<br />" ;
    }
    else
    {
      document.getElementById ("result") . innerHTML = result.msg + "<br />" ;
    }*/
    
  } catch (error)
  {
    console.log("Request Failed", error) ;
  }
}

function clicked_email_change()
{
  document.getElementById("button-email-change").style.display = "none" ;
  document.getElementById("div-change-email").style.display = "inline-block" ;
}

function clicked_email_cancel()
{
  document.getElementById("div-change-email").style.display = "none" ;
  document.getElementById("button-email-change").style.display = "inline-block" ;
}

function focusout_change_email()
{
  let input_value, filtered_value ;
  input_value = document.getElementById("input-change-email").value ;
  filtered_value = input_value.replace (/[^\w.\-@]/g, '') ; // hypen 減號之前需要一個 \ 進行轉義，不使用 \ 轉義的話，要把 hypen 放在 ] 之前
  filtered_value = filtered_value.substring (0, 320) ;
  //document.getElementById("input-change-email").value = filtered_value ;
}

async function clicked_email_save()
{
  let result ;
  await patch_account_field_email() ;
  result = global_space.get_request_patch_email_result() ;
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
  if (result ["status"] === "error")
  {
    if (result ["message"] === "email is invalid")
    {
      alert("失敗\n\n您的 email 不合法！") ;
      return ;
    }
    if (result ["message"] === "email too long!")
    {
      alert("失敗\n\n您的 email 太長！") ;
      return ;
    }
  }
  if (result ["status"] != "success")
  {
    alert(result ["message"]) ;
    return -1 ;
  }
  if (result ["status"] === "success")
  {
    if (result ["message"] === "no change")
    {
      alert("E-mail 相同，沒有改變！") ;
      return ;
    }
  }
  alert("E-mail 變更成功！") ;
  document.getElementById("div-change-email").style.display = "none" ;
  document.getElementById("button-email-change").style.display = "inline-block" ;
  document.getElementById("show-email").innerHTML = "E-mail：" + result ["email"] ;//"demo 中，暫時保密。" ;

  global_space.set_email_verified_status(0) ;
  display_element_with_email_verified_status() ;
  //document.getElementById("show-email-verified").innerHTML = "E-mail 認證：未認證" ;
}

async function patch_account_field_email()
{
  let input_value, filtered_value ;
  let url = "/api/v1/account/" ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
  url += localStorage.getItem("user_id") ;
  input_value = document.getElementById("input-change-email").value ;
  filtered_value = input_value.replace (/[^\w.\-@]/g, '') ; // hypen 減號之前需要一個 \ 進行轉義，不使用 \ 轉義的話，要把 hypen 放在 ] 之前
  filtered_value = filtered_value.substring (0, 320) ;
  vbody = {
    "email": input_value
    //"email": filtered_value
  }
  try
  {
    const response = await fetch (url, {
      method: "PATCH",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      body: JSON.stringify (vbody)
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const result = await response.json() ;
    global_space.set_request_patch_email_result(result) ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function clicked_show_email_verified_wrap()
{
  document.getElementById("button-show-email-verified-wrap").style.display = "none" ;
  document.getElementById("div-email-verified-wrap").style.display = "block" ;
}

async function clicked_send_verified_code_mail()
{
  var result ;
  var element ;
  var error_msg ;
  element = document.getElementById("button-send-verified-code-mail") ;
  element.disabled = true ;
  element.style.textDecorationLine = "line-through" ;
  document.getElementById("span-resend-remaining-time").innerHTML = "伺服器寄信處理中，請稍候......" ;

  await post_verified_code_mail() ;
  result = global_space.get_request_post_verified_code_mail_result() ;

  document.getElementById("span-resend-remaining-time").innerHTML = "" ;

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
  if (result ["status"] === "failed")
  {
    if (result ["message"] === "no such mail server")
    {
      alert("寄信失敗，查不到此 E-mail 伺服器，請確認您輸入的 E-mail 地址！") ;
      global_space.set_resend_countdown(60) ;
      global_space.set_resend_timer(setInterval(resend_timer, 1000)) ;
      return -1 ;
    }
    if (result ["message"] === "smtp error check user")
    {
      error_msg = "寄信失敗，可能此 E-mail 帳號不存在，請確認您輸入的 E-mail。\n\nSMTP 回報訊息：\n\n" + result ["ex_msg"] ;
      alert(error_msg) ;
      document.getElementById("show-send-mail-error").innerHTML = error_msg.replace(/\n/g, "<br />") ;
      global_space.set_resend_countdown(60) ;
      global_space.set_resend_timer(setInterval(resend_timer, 1000)) ;
      return -1 ;
    }
    if (result ["message"] === "smtp error as spam")
    {
      error_msg = "寄信失敗，該伺服器拒絕收件。\n\n建議您將 no-reply@mcnsite.ddns.net 加入信任帳號。\n\n或是寄一封信 ( 內容不拘 ) 到 no-reply@mcnsite.ddns.net\n\nSMTP 回報訊息：\n\n" + result ["ex_msg"] ;
      alert(error_msg) ;
      document.getElementById("show-send-mail-error").innerHTML = error_msg.replace(/\n/g, "<br />") ;
      global_space.set_resend_countdown(60) ;
      global_space.set_resend_timer(setInterval(resend_timer, 1000)) ;
      return -1 ;
    }
    alert(result ["message"]) ;
    global_space.set_resend_countdown(60) ;
    global_space.set_resend_timer(setInterval(resend_timer, 1000)) ;
    return -1 ;
  }
  alert("寄信完成，請到您的 E-mail 收信！") ;
  document.getElementById("show-send-mail-error").innerHTML = "" ;

  global_space.set_resend_countdown(60) ;
  global_space.set_resend_timer(setInterval(resend_timer, 1000)) ;
}

function resend_timer()
{
  var countdown ;
  var element ;
  countdown = global_space.get_resend_countdown() ;
  
  if (countdown > 0)
  {
    document.getElementById("span-resend-remaining-time").innerHTML = countdown + " 秒後，可重發驗證信。" ;
  }
  else
  {
    document.getElementById("span-resend-remaining-time").innerHTML = "可重發驗證信。" ;
    element = document.getElementById("button-send-verified-code-mail") ;
    element.disabled = false ;
    element.style.textDecorationLine = "none" ;
    clearInterval(global_space.get_resend_timer()) ; // stop timer
  }

  countdown -- ;
  global_space.set_resend_countdown(countdown) ;
}

async function post_verified_code_mail()
{
  let url = "/api/v1/account/" ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
  url += localStorage.getItem("user_id") + "/verified-code-mail/" ;
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const result = await response.json() ;
    global_space.set_request_post_verified_code_mail_result(result) ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_verified_email_address()
{
  var result ;
  var element ;
  element = document.getElementById("button-verified-email-address") ;
  element.disabled = true ;

  await patch_email_verified_status() ;
  result = global_space.get_request_patch_email_verified_status_result() ;

  if (typeof result != "object")
  {
    alert("連線錯誤") ;
    element.disabled = false ;
    return -1 ;
  }
  if (! result.hasOwnProperty("status"))
  {
    alert("連線錯誤") ;
    element.disabled = false ;
    return -1 ;
  }
  if (result ["status"] === "failed")
  {
    if (result ["message"] === "no item matched")
    {
      alert("失敗，驗證碼不存在，請先寄驗證碼到信箱。") ;
      element.disabled = false ;
      return -1 ;
    }
    if (result ["message"] === "verified code is expired, please send mail to get new verified code!")
    {
      alert("驗證碼過期，請重新寄驗證碼信，以獲得新的驗證碼！") ;
      element.disabled = false ;
      return -1 ;
    }
    if (result ["message"] === "incorrect verified code")
    {
      alert("驗證碼錯誤！") ;
      element.disabled = false ;
      return -1 ;
    }
    if (result ["message"] === "verified code too long!")
    {
      alert("失敗，輸入的資料太長！") ;
      element.disabled = false ;
      return -1 ;
    }
    alert(result ["message"]) ;
    element.disabled = false ;
    return -1 ;
  }

  if (result ["status"] === "success")
  {
    alert("驗證成功！") ;
    element.disabled = false ;
    document.getElementById("div-email-verified-wrap").style.display = "none" ;
    global_space.set_email_verified_status(1) ;
    display_element_with_email_verified_status() ;
  }
}

async function patch_email_verified_status()
{
  let url = "/api/v1/account/" ;
  let input_value ;
  let vbody ;
  let response_status ;
  let request_body ;
  url += localStorage.getItem("user_id") + "/email-verification/" ;
  input_value = document.getElementById("input-verified-code").value ;
  vbody = {
    "verified-code": input_value
  } ;
  request_body = JSON.stringify(vbody) ;
  try
  {
    const response = await fetch(url, {
      method: "PATCH",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      //body: JSON.stringify (vbody)
      body: request_body
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json() ;
    global_space.set_request_patch_email_verified_status_result(retrieve_body) ;
    return ;
  } catch (error)
  {
    console.log("Request Failed", error) ;
  }
}

function clicked_password_change()
{
  document.getElementById("button-password-change").style.display = "none" ;
  document.getElementById("div-change-password").style.display = "block" ;
}

function clicked_password_cancel()
{
  document.getElementById("div-change-password").style.display = "none" ;
  document.getElementById("button-password-change").style.display = "inline-block" ;
}

async function clicked_password_send()
{
  let url = "/api/v1/account/" ;
  let input_value_old ;
  let input_value_new ;
  let input_value_double_check ;
  let vbody ;
  let response_status ;
  let request_body ;
  url += localStorage.getItem("user_id") ;
  input_value_old = document.getElementById("input-change-password-old").value ;
  input_value_new = document.getElementById("input-change-password-new").value ;
  input_value_double_check = document.getElementById("input-change-password-double-check").value ;
  if (input_value_old.length > 50)
  {
    alert("失敗，舊密碼太長") ;
    return ;
  }
  if (input_value_new.length > 50)
  {
    alert("失敗，新密碼太長") ;
    return ;
  }
  if (input_value_new != input_value_double_check)
  {
    alert("失敗，密碼確認輸入不相同") ;
    return ;
  }
  vbody = {
    "password-old": input_value_old
    , "password-new": input_value_new
    , "password-double-check": input_value_double_check
  } ;
  request_body = JSON.stringify(vbody) ;
  try
  {
    const response = await fetch(url, {
      method: "PATCH",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      body: request_body
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json() ;
    global_space.set_rr_patch_password(retrieve_body) ;

    if (typeof retrieve_body != "object")
    {
      alert("連線錯誤") ;
      return -1 ;
    }
    if (! retrieve_body.hasOwnProperty("status"))
    {
      alert("連線錯誤") ;
      return -1 ;
    }
    if (retrieve_body ["status"] != "success")
    {
      if (retrieve_body ["message"] === "password-old not correct")
      {
        alert("失敗，舊密碼不正確！") ;
        return -1 ;
      }
      if (retrieve_body ["message"] === "password-old equal password-new")
      {
        alert("失敗，新密碼與舊密碼相同！") ;
        return -1 ;
      }
      alert(retrieve_body ["message"]) ;
      return -1 ;
    }
    alert("變更密碼成功！") ;
    clicked_password_cancel() ;
    return ;
  } catch (error)
  {
    console.log("Request Failed", error) ;
  }
}

