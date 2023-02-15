
window.onload = async function ()
{
  let result ;

  if (localStorage.getItem("theme") === null)
  {
    localStorage.setItem("theme", "theme-light") ;
  }
  document.documentElement.className = localStorage.getItem("theme") ;

  await clicked_changing_captcha() ;
}

var global_space = (function ()
{
  var request_check_account_result ;
  var captcha_uuid ;
  var captcha_time ;
  var request_post_register_result ;
  var request_post_captcha_result ;
  var request_get_checking_account_id ;
  var selected_tab = "" ; //shared variable available only inside your module
  var request_bingo_size_result ;
  var request_privacy_result ;
  var request_max_attendance_result ;
  var request_leave_room_result ;
  var request_new_room_result ;
  var request_quickly_join_room_result ;
  var request_round_grid_result ;
  var request_round_picked_number_result ;
  var request_player_status_place_result ;
  var temp_result ;

  return { // 這邊的左大括號不能寫在下一行，會報錯，後面的都不執行 = =|||
    get_selected_tab : function () {
      return selected_tab ; // this function can access my_var
    },
    set_selected_tab : function (value)
    {
      selected_tab = value ; // this function can also access my_var
    },
    get_captcha_uuid : function () {
      return captcha_uuid ;
    },
    set_captcha_uuid : function (value)
    {
      captcha_uuid = value ;
    },
    get_captcha_time : function () {
      return captcha_time ;
    },
    set_captcha_time : function (value)
    {
      captcha_time = value ;
    },
    get_request_post_register_result : function () {
      return request_post_register_result ;
    },
    set_request_post_register_result : function (value)
    {
      request_post_register_result = value ;
    },
    get_request_post_captcha_result : function () {
      return request_post_captcha_result ;
    },
    set_request_post_captcha_result : function (value)
    {
      request_post_captcha_result = value ;
    },
    get_request_get_checking_account_id : function () {
      return request_get_checking_account_id ;
    },
    set_request_get_checking_account_id : function (value)
    {
      request_get_checking_account_id = value ;
    },
    get_temp_result : function()
    {
      return temp_result ;
    },
    set_temp_result : function(value)
    {
      temp_result = value ;
    }
  } ;

})() ;

function focus_account_id()
{
  document.getElementById("div-check-account").innerHTML = "等待輸入完成" ;
}

async function focusout_account_id()
{
  var input_value ;
  var filtered_value ;
  var filtered_account_id ;

  input_value = document.getElementById("account_id").value ;
  filtered_value = input_value.replace (/[^\w.\-@]/g, '') ; // hypen 減號之前需要一個 \ 進行轉義，不使用 \ 轉義的話，要把 hypen 放在 ] 之前
  filtered_value = filtered_value.substring (0, 50) ;
  filtered_account_id = filtered_value ;
  document.getElementById("account_id").value = filtered_account_id ;

  document.getElementById("div-check-account").innerHTML = "檢查中......" ;

  await get_checking_account_id() ;

  var result ;
  result = global_space.get_request_get_checking_account_id() ;
  //alert(JSON.stringify(result)) ;

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
    if (result ["message"] === "accound id is invalid")
    {
      document.getElementById("div-check-account").innerHTML = "帳號包含不合法符號！" ;
    }
    if (result ["message"] === "empty is invalid")
    {
      document.getElementById("div-check-account").innerHTML = "沒有輸入資料！" ;
    }
  }
  if (result ["status"] === "success")
  {
    if (result ["can_register"] === "YES")
    {
      document.getElementById("div-check-account").innerHTML = "帳號可以使用！" ;
    }
    else
    {
      document.getElementById("div-check-account").innerHTML = "該帳號已註冊，請使用其他帳號！" ;
    }
  }
}

async function clicked_checking_account_id()
{
  await focusout_account_id() ;
}

async function get_checking_account_id()
{
  let url = "/api/v1/register/checking-account-id/" ;
  let vbody ;
  let response_status ;
  let input_value ;
  let filtered_value ;
  let filtered_account_id ;
  input_value = document.getElementById("account_id").value ;
  filtered_value = input_value.replace(/[^\w.\-@]/g, '') ; // hypen 減號之前需要一個 \ 進行轉義，不使用 \ 轉義的話，要把 hypen 放在 ] 之前
  filtered_value = filtered_value.substring(0, 50) ;
  filtered_account_id = filtered_value ;
  vbody = {
    "account_id": filtered_account_id
  }
  url += filtered_account_id ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8",
      },
      //body: JSON.stringify(vbody)
      // GET method 在 fetch api 是不能有 body 的
    }) ;
    response_status = response.status ;
    const result = await response.json() ;
    global_space.set_request_get_checking_account_id(result) ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_register()
{
  let result ;
  await post_register() ;
  result = global_space.get_request_post_register_result() ;

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
    if (result ["message"] === "captcha is wrong!")
    {
      alert("驗證碼錯誤") ;
      await clicked_changing_captcha() ;
      return -1 ;
    }
    alert(result ["message"]) ;
    return -1 ;
  }
  if (result ["status"] === "failed")
  {
    if (result ["message"] === "account id aleady existed!")
    {
      alert("此帳號已存在，無法註冊!") ;
      return -1 ;
    }
    alert(result ["message"]) ;
    return -1 ;
  }

  if (result ["status"] === "success")
  {
    alert("註冊成功！\n\n前往登入頁面。") ;
    window.location.href = '../login/login.html' ;
    return ;
  }
  alert(JSON.stringify(result)) ;
}

async function post_register()
{
  let input_value, filtered_value ;
  let filtered_account_id, filtered_password ;
  let url = "/api/v1/register" ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
  input_value = document.getElementById("account_id").value ;
  filtered_value = input_value . replace (/[^\w.\-@]/g, '') ; // hypen 減號之前需要一個 \ 進行轉義，不使用 \ 轉義的話，要把 hypen 放在 ] 之前
  filtered_value = filtered_value . substring (0, 50) ;
  filtered_account_id = filtered_value ;
  document.getElementById("account_id").value = filtered_account_id ;
  // alert("account: " + filtered_account_id) ;
  input_value = document.getElementById("password").value ;
  filtered_value = input_value.replace(/[^\W\w. \-@]/g, '') ; // hypen 減號之前需要一個 \ 進行轉義，不使用 \ 轉義的話，要把 hypen 放在 ] 之前
  filtered_value = filtered_value.substring(0, 50) ;
  filtered_password = filtered_value ;
  input_value = document.getElementById("input-captcha").value ;
  vbody = {
    "account_id": filtered_account_id
    , "password": filtered_password
    , "captcha_string": input_value
    , "captcha_uuid": global_space.get_captcha_uuid()
    , "captcha_time": global_space.get_captcha_time()
  }
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8",
      },
      body: JSON.stringify (vbody)
    }) ;
    response_status = response.status ;
    const result = await response.json() ;
    global_space.set_request_post_register_result(result) ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_creating_captcha()
{
  let url = "/api/v1/register/captcha" ;
  let response_status ;
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8",
      },
    }) ;
    response_status = response.status ;
    const result = await response.json() ;
    let output ;
    output = JSON.stringify(result) ;
    document.getElementById("result") . innerHTML = output ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function post_captcha()
{
  let url = "/api/v1/register/captcha" ;
  let response_status ;
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8",
      },
    }) ;
    response_status = response.status ;
    const result = await response.json() ;
    global_space.set_request_post_captcha_result(result) ;
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_changing_captcha()
{
  await post_captcha() ;
  result = global_space.get_request_post_captcha_result() ;
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

  global_space.set_captcha_uuid(result ["uuid"]) ;
  global_space.set_captcha_time(result ["time"]) ;

  document.getElementById("img-captcha").src = "data:image/webp;base64," + result ["imgdata"] ;
}

