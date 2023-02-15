
var global_space = (function ()
{
  var selected_tab = ''; //shared variable available only inside your module
  var account_setting ;
  var edit_setting_flag = 0 ;
  var load_msg_timer ;
  var last_msg_id = 0 ;
  var rr_get_public_room ;
  var rr_delete_message ;
  var changed_scroll_flag ;
  var scroll_value ;
  var temp_result ;

  function bar ()
  { // this function not available outside your module
    alert (my_var) ; // this function can access my_var
  }

  return { // 這邊的左大括號不能寫在下一行，會報錯，後面的都不執行 = =|||
    get_selected_tab : function () {
      return selected_tab ; // this function can access my_var
    },
    set_selected_tab : function (value)
    {
      selected_tab = value ; // this function can also access my_var
    },
    get_last_msg_id : function () {
      return last_msg_id ; // this function can access my_var
    },
    set_last_msg_id : function (value) {
      last_msg_id = value ; // this function can access my_var
    },
    get_account_setting : function ()
    {
      return account_setting ;
    },
    set_account_setting : function (value)
    {
      account_setting = value ;
    },
    get_rr_get_public_room : function ()
    {
      return rr_get_public_room ;
    },
    set_rr_get_public_room : function (value)
    {
      rr_get_public_room = value ;
    },
    get_rr_delete_message : function ()
    {
      return rr_delete_message ;
    },
    set_rr_delete_message : function (value)
    {
      rr_delete_message = value ;
    },
    get_changed_scroll_flag : function ()
    {
      return changed_scroll_flag ;
    },
    set_changed_scroll_flag : function (value)
    {
      changed_scroll_flag = value ;
    },
    get_scroll_value : function ()
    {
      return scroll_value ;
    },
    set_scroll_value : function (value)
    {
      scroll_value = value ;
    },
    get_temp_result : function () {
      return temp_result ;
    },
    set_temp_result : function (value) {
      temp_result = value ;
    },
    esf_get : function ()
    {
      return edit_setting_flag ;
    },
    esf_set : function (value)
    {
      edit_setting_flag = alue ;
    },
    get_load_msg_timer : function ()
    {
      return load_msg_timer ;
    },
    set_load_msg_timer : function (value)
    {
      load_msg_timer = value ;
    }
  } ;
})() ;

window.onload = async function ()
{
  save_csrf_token() ;
  if (localStorage.getItem("theme") === null)
  {
    localStorage.setItem("theme", "theme-light") ;
  }
  document.documentElement.className = localStorage.getItem("theme") ;
  
  global_space.set_selected_tab("account-setting") ;
  global_space.set_changed_scroll_flag(0) ;
  clicked_account() ;
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

function nav_lobby()
{
  transfer_csrf_token() ;
  window.location.href = "../lobby/lobby.html" ;
}

async function clicked_account()
{
  let clicked ;
  clearInterval(global_space.get_load_msg_timer()) ; // stop timer
  clicked = global_space.get_selected_tab() ;
  document.getElementById("chat-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  // change css class
  global_space.set_selected_tab("account-setting") ;
  document.getElementById("chat-page-button-account-setting").className = "button-selected" ;
  document.getElementById("div-account-setting").style.display = "block" ;
  await get_account_setting() ;
}

function clicked_public_room()
{
  let clicked ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("chat-page-button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  // change css class
  global_space.set_selected_tab("public-room") ;
  document.getElementById("chat-page-button-public-room").className = "button-selected" ;
  document.getElementById("div-public-room").style.display = "block" ;
  
  global_space.set_load_msg_timer (setInterval (test_timer, 3000)) ;
  get_public_room() ;
  return ;
}

function test_timer()
{
  get_public_room() ;
}

async function get_account_setting()
{
  let input_value, filtered_value ;
  let filtered_account_id, filtered_password ;
  let url = "/api/v1/chat/setting/" ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
  url += localStorage.getItem("user_id") ;
  try
  {
    const response = await fetch (url, {
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
    const retrieve_body = await response.json () ;
    global_space.set_account_setting(retrieve_body) ;
    let output ;
    output = JSON.stringify(retrieve_body) + "<br />" ;
    document.getElementById("show-nickname").innerHTML = "暱稱：" + retrieve_body.nickname ;

    return ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function click_edit_setting_button()
{
  var account_setting ;
  account_setting = global_space.get_account_setting() ;

  document.getElementsByClassName("account-setting-edit-button")[0].style.display = "none" ;
  document.getElementsByClassName("account-setting-save-button")[0].style.display = "inline-block" ;
  document.getElementsByClassName("account-setting-cancel-button")[0].style.display = "inline-block" ;
  document.getElementById("account-setting-display-data").style.display = "none" ;
  document.getElementById("account-setting-edit-data").style.display = "block" ;
  document.getElementById("edit-nickname").value = account_setting ["nickname"] ;
}

async function click_save_setting_button()
{
  let text = "" ;
  let text_cutting = "" ;
  let result ;
  let account_setting = {} ;
  text = document.getElementById("edit-nickname").value ;
  if (text.length > 50)
  {
    text_cutting = text.substring(0, 50) ;
  }
  else
  {
    text_cutting = text ;
  }
  await patch_account_setting_api(text_cutting) ;
  result = global_space.get_temp_result() ;
  if (result ["status"] === "success")
  {
    alert("變更成功！") ;
    account_setting ["nickname"] = text_cutting ;
    global_space.set_account_setting(account_setting) ;
    document.getElementsByClassName("account-setting-save-button")[0].style.display = "none" ;
    document.getElementsByClassName("account-setting-cancel-button")[0].style.display = "none" ;
    document.getElementsByClassName("account-setting-edit-button")[0].style.display = "inline-block" ;
    document.getElementById("edit-nickname").value = account_setting ["nickname"] ;
    document.getElementById("show-nickname").innerHTML = "暱稱：" + account_setting ["nickname"] ;
    document.getElementById("account-setting-edit-data").style.display = "none" ;
    document.getElementById("account-setting-display-data").style.display = "block" ;
    return ;
  }
  alert("變更失敗\n" + result ["message"]) ;
}

async function patch_account_setting_api(data)
{
  let url = "/api/v1/chat/setting/" ;
  url += localStorage.getItem("user_id") ;
  let vbody ;
  vbody = {
    "nickname": data
  }
  try
  {
    const response = await fetch (url, {
      method: "PATCH",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
      //body: 'account_id=' + encodeURIComponent (filtered_account_id) + '&password=' + encodeURIComponent (filtered_password),
      body: JSON.stringify(vbody)
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const result = await response.json() ;
    global_space.set_temp_result(result) ;
    return ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function click_cancel_setting_button ()
{
  document.getElementsByClassName("account-setting-save-button")[0].style.display = "none" ;
  document.getElementsByClassName("account-setting-cancel-button")[0].style.display = "none" ;
  document.getElementsByClassName("account-setting-edit-button")[0].style.display = "inline-block" ;
  document.getElementById("account-setting-edit-data").style.display = "none" ;
  document.getElementById("account-setting-display-data").style.display = "block" ;
}

async function get_public_room()
{
  let url = "/api/v1/chat/public-room" ;
  let vbody ;
  let response_status ;
  let rn ;
  let i ;
  let last_msg_id ;
  let output ;
  try
  {
    const response = await fetch (url, {
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
    const retrieve_body = await response.json () ;
    global_space.set_rr_get_public_room(retrieve_body) ;

    if (! (retrieve_body.status == "success"))
    {
      return ;
    }
    
    rn = retrieve_body.record_number ;
    if (rn <= 0)
    {
      return ;
    }
    last_msg_id = parseInt(retrieve_body ["msg_id" + (rn - 1)]) ; // 強制把 string 轉 int
    if (last_msg_id <= global_space.get_last_msg_id())
    {
      //return ;
    }
    global_space.set_last_msg_id(last_msg_id) ;
    
    output = "" ;
    
    for (i = 0 ; i < rn ; i ++)
    {
      if (retrieve_body ["sent_from" + i] === localStorage.getItem("user_id")) // msg 是自己發的
      {
        output += "<div class = \"single-message-title-align-right\"><span class = \"message-sent-from\">" + retrieve_body ["nickname" + i] + "</span><span class = \"message-time\">" + "&nbsp;&nbsp;" + retrieve_body ["time" + i] + "</span>&nbsp;&nbsp;<button class = \"button-message-delete\" onclick = \"delete_message(" + retrieve_body ["msg_id" + i] + ");\">刪除</button>" + "<br /><span class = \"message-arrow-align-right\"></span></div>" ;
        output += "<div class = \"single-message-block-align-right\"><div class = \"message-content-align-right\">" + retrieve_body ["message" + i].replace(/(?:\r\n|\r|\n)/g, '<br>') + "</div></div>" ;
      }
      else // msg 是別人發的
      {
        output += "<div class = \"single-message-title\"><span class = \"message-sent-from\">" + retrieve_body ["nickname" + i] + "</span><span class = \"message-time\">" + "&nbsp;&nbsp;" + retrieve_body ["time" + i] + "</span><br /><span class = \"message-arrow\"></span></div>" ;
        output += "<div class = \"single-message-block\"><div class = \"message-content\">" + retrieve_body ["message" + i].replace(/(?:\r\n|\r|\n)/g, '<br>') + "</div></div>" ;
      }
      
    }

    global_space.set_scroll_value(document.getElementById("public-room-message-display").scrollTop) ;

    document.getElementById("public-room-message-display").innerHTML = output ;

    if (global_space.get_changed_scroll_flag() === 0)
    {
      document.getElementById("public-room-message-display").scrollTop = document.getElementById("public-room-message-display").scrollHeight ;
      global_space.set_changed_scroll_flag(1) ;
    }
    else
    {
      document.getElementById("public-room-message-display").scrollTop = global_space.get_scroll_value() ;
    }
    
    return ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function post_public_room()
{
  let url = "/api/v1/chat/public-room" ;
  let vbody ;
  let response_status ;
  let rn ;
  let i ;
  let message_content ;
  message_content = document.getElementById("public-room-message-editor").value ;
  vbody = {
    "sn": localStorage.getItem("user_id"),
    "message_content": message_content
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
      //body: 'account_id=' + encodeURIComponent (filtered_account_id) + '&password=' + encodeURIComponent (filtered_password),
      body: JSON.stringify (vbody)
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    let output ;
    
    if (! (retrieve_body.status == "success"))
    {
      alert("失敗\n" + retrieve_body ["message"]) ;
      return ;
    }
    document.getElementById("public-room-message-editor").value = "" ;
    await get_public_room() ;
    return ;
    
    output = "" ;
    rn = retrieve_body.record_number ;
    for (i = 0 ; i < rn ; i ++)
    {
      if (retrieve_body ["sent_from" + i] === localStorage.getItem("user_id")) // msg 是自己發的
      {
        output += "<div class = \"single-message-title-align-right\"><span class = \"message-sent-from\">" + retrieve_body ["nickname" + i] + "</span><span class = \"message-time\">" + "&nbsp;&nbsp;" + retrieve_body ["time" + i] + "</span><br /><span class = \"message-arrow-align-right\"></span></div>" ;
        output += "<div class = \"single-message-block-align-right\"><div class = \"message-content-align-right\">" + retrieve_body ["message" + i] + "</div></div>" ;
      }
      else // msg 是別人發的
      {
        output += "<div class = \"single-message-title\"><span class = \"message-sent-from\">" + retrieve_body ["nickname" + i] + "</span><span class = \"message-time\">" + "&nbsp;&nbsp;" + retrieve_body ["time" + i] + "</span><br /><span class = \"message-arrow\"></span></div>" ;
        output += "<div class = \"single-message-block\"><div class = \"message-content\">" + retrieve_body ["message" + i] + "</div></div>" ;
      }
    }
    document.getElementById("public-room-message-display").innerHTML = output ;
    
    return ;
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
      json_payload = decodeURIComponent (atob (base64) . split ('') . map (function (c) {
        return "%" + ("00" + c . charCodeAt (0) . toString (16)) . slice (-2) ;
      }) . join ("")) ;
      jwt_header = JSON.parse (window.atob (result.jwt.split(".")[0])) ;
      let uun = JSON.parse (json_payload) ;
      output = "parse sn: " + uun . sn ;
    }

    document.getElementById ("result") . innerHTML = output ;
    window.location.replace ("../account/main.html") ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function delete_message(msg_id)
{
  let url = "/api/v1/chat/public-room/" ;
  let response_status ;
  url += msg_id ;
  try
  {
    const response = await fetch (url, {
      method: "DELETE",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    let output ;

    global_space.set_rr_delete_message(retrieve_body) ;
    
    if (! (retrieve_body ["status"] === "success"))
    {
      alert("失敗\n" + retrieve_body ["message"]) ;
      return ;
    }
    alert("成功") ;
    await get_public_room() ;
    return ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}
