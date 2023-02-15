
async function clicked_rooms_list()
{
  let clicked ;
  let my_shop_clicked ;
  let rooms_list ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;
  
  // change css class
  global_space.set_selected_tab("rooms-list") ;
  document.getElementById("button-rooms-list").className = "button-selected" ;
  document.getElementById("div-rooms-list").style.display = "block" ;

  await get_rooms_list_api() ;

  //rooms_list = global_space.get_rooms_list() ;
  edit_html_code_for_rooms_list() ;
}

async function get_rooms_list_api()
{
  let url = "/api/v1/bingo/rooms/" ;
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
    global_space.set_rooms_list(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function get_single_room_detail_api(room_id)
{
  let url = "/api/v1/bingo/rooms/" ;
  url += room_id ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8",
      }
    }) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    global_space.set_single_room_detail(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function edit_html_code_for_rooms_list()
{
  var list ;
  var rn, i ;
  var output ;
  var field_index ;
  var current_page ;
  var max_page ;
  var start_index ;

  list = global_space.get_rooms_list() ;
  rn = list.record_number ;

  if (list.status != "success")
  {
    output = "連線失敗，請重新連線。" ;
    document.getElementById("div-rooms-list").innerHTML = output ;
    return ;
  }

  output = "" ;

  if (rn <= 0)
  {
    //output = "沒有遊戲房" ;
    //document.getElementById("div-rooms-list").innerHTML = output ;
    //return ;
  }

  max_page = Math.ceil(rn / 9) ;
  if (max_page < 1)
  {
    max_page = 1 ;
  }
  global_space.set_rooms_list_max_page(max_page) ;
  edit_html_code_for_pages() ;

  current_page = global_space.get_rooms_list_current_page() ;
  start_index = (current_page - 1) * 9 ;

  field_index = 1 ;
  global_space.set_room_field_index(1) ;
  for (i = start_index ; i < rn ; i ++)
  {
    field_index = global_space.get_room_field_index() ;
    if (field_index > 9)
    {
      break ;
    }
    edit_html_code_for_rooms_list_single_detail(i, field_index) ;
    field_index ++ ;
    global_space.set_room_field_index(field_index) ;
  }
  for (i = field_index ; i < 10 ; i ++)
  {
    document.getElementById("div-room-field-" + i).setAttribute("onclick", "") ;
    document.getElementById("span-room-id-" + i).innerHTML = "空房" ;
    document.getElementById("span-private-status-" + i).innerHTML = "　" ;
    document.getElementById("span-room-status-" + i).innerHTML = "　" ;
    document.getElementById("span-bingo-size-" + i).innerHTML = "　" ;
    document.getElementById("span-attendance-" + i).innerHTML = "　" ;
  }
  return ;
}

function edit_html_code_for_rooms_list_single_detail(index, field_index)
{
  var list ;
  var text ;
  list = global_space.get_rooms_list() ;
  text = "" ;

  text = "clicked_check_joining_room(" + list ["room_id" + index] + ", " + index + ")" ;
  document.getElementById("div-room-field-" + field_index).setAttribute("onclick", text) ;
  text = list ["room_id" + index] + " 房" ;
  document.getElementById("span-room-id-" + field_index).innerHTML = text ;
  if (list ["private" + index] === 1)
  {
    text = "私人遊戲房間" ;
  }
  else
  {
    text = "公開遊戲房間" ;
  }
  document.getElementById("span-private-status-" + field_index).innerHTML = text ;
  if (list ["room_status" + index] === "O")
  {
    text = "遊戲進行中" ;
  }
  else
  {
    text = "配對等待中" ;
  }
  document.getElementById("span-room-status-" + field_index).innerHTML = text ;
  if (list ["bingo_size" + index] === 5)
  {
    text = "5 x 5" ;
  }
  else if (list ["bingo_size" + index] === 6)
  {
    text = "6 x 6" ;
  }
  document.getElementById("span-bingo-size-" + field_index).innerHTML = text ;
  text = "人數 " + list ["attendance" + index] + " / " + list ["max_attendance" + index] ;
  document.getElementById("span-attendance-" + field_index).innerHTML = text ;
}

function edit_html_code_for_pages()
{
  var text ;
  text = "第 " + global_space.get_rooms_list_current_page() + " / " + global_space.get_rooms_list_max_page() + " 頁" ;
  document.getElementById("span-current-page").innerHTML = text ;
}

function clicked_next_page()
{
  var current_page ;
  var max_page ;
  current_page = global_space.get_rooms_list_current_page() ;
  max_page = global_space.get_rooms_list_max_page() ;
  if (current_page + 1 <= max_page)
  {
    current_page ++ ;
    global_space.set_rooms_list_current_page(current_page) ;
    edit_html_code_for_rooms_list() ;
  }
}

function clicked_previous_page()
{
  var current_page ;
  current_page = global_space.get_rooms_list_current_page() ;
  max_page = global_space.get_rooms_list_max_page() ;
  if (current_page - 1 >= 1)
  {
    current_page -- ;
    global_space.set_rooms_list_current_page(current_page) ;
    edit_html_code_for_rooms_list() ;
  }
}

function clicked_first_page()
{
  var current_page ;
  current_page = global_space.get_rooms_list_current_page() ;
  if (current_page != 1)
  {
    current_page = 1 ;
    global_space.set_rooms_list_current_page(current_page) ;
    edit_html_code_for_rooms_list() ;
  }
}

function clicked_last_page()
{
  var current_page ;
  var max_page ;
  current_page = global_space.get_rooms_list_current_page() ;
  max_page = global_space.get_rooms_list_max_page() ;
  if (current_page != max_page)
  {
    current_page = max_page ;
    global_space.set_rooms_list_current_page(current_page) ;
    edit_html_code_for_rooms_list() ;
  }
}

async function clicked_create_room()
{
  var result ;
  document.getElementById("button-create-room").disabled = true ;
  await post_new_room_api() ;
  result = global_space.get_request_new_room_result() ;
  //alert(JSON.stringify(result)) ;

  if (typeof result != "object")
  {
    alert("連線錯誤") ;
    document.getElementById("button-create-room").disabled = false ;
    return -1 ;
  }
  if (! result.hasOwnProperty("status"))
  {
    alert("連線錯誤") ;
    document.getElementById("button-create-room").disabled = false ;
    return -1 ;
  }
  if (result ["status"] != "success")
  {
    alert(result ["message"]) ;
    document.getElementById("button-create-room").disabled = false ;
    return -1 ;
  }

  // update screen
  await load_player_status() ;

  document.getElementById("button-create-room").disabled = false ;
}

async function post_new_room_api()
{
  let url = "/api/v1/bingo/rooms" ;
  let vbody ;
  let request_body ;
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
    const retrieve_body = await response.json () ;
    global_space.set_request_new_room_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_quickly_join_room()
{
  var request_result ;

  document.getElementById("button-join-room").disabled = true ;

  await get_quickly_join_room_api() ;
  request_result = global_space.get_request_quickly_join_room_result() ;

  if (! (typeof request_result === "object"))
  {
    alert("request_result not object!") ;
    document.getElementById("button-join-room").disabled = false ;
    document.getElementById("button-round-ongoing-main-send-circled-number").disabled = false ; // 這邊應該是上面的
    return ;
  }
  if (! request_result.hasOwnProperty("status"))
  {
    alert("failed, request_result do not have property status!") ;
    document.getElementById("button-join-room").disabled = false ;
    document.getElementById("button-round-ongoing-main-send-circled-number").disabled = false ;
    return ;
  }
  if (! (request_result ["status"] === "success"))
  {
    alert(request_result ["message"]) ;
    document.getElementById("button-join-room").disabled = false ;
    document.getElementById("button-round-ongoing-main-send-circled-number").disabled = false ;
    return ;
  }

  // update screen
  await load_player_status() ;

  // show poparea
  //document.getElementById("div-room-detail-overlay").style.display = "block" ;

  document.getElementById("button-join-room").disabled = false ;
}

async function get_quickly_join_room_api()
{
  let url = "/api/v1/bingo/rooms/quickly-join-room" ;
  let request_body ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        , "X-Csrf-Token": load_csrf_token()
      },
    }) ;
    resave_csrf_token(response.headers) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    global_space.set_request_quickly_join_room_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_check_joining_room(room_id, record_index)
{
  var list ;
  var text ;
  list = global_space.get_rooms_list() ;

  if (list ["private" + record_index] === 1)
  {
    text = "clicked_button_room_confirm_password_send(" + room_id + ")" ;
    document.getElementById("button-room-confirm-password-yes").setAttribute("onclick", text) ;
    document.getElementById("div-rooms-list-status-info-overlay").style.display = "block" ;
    return ;
  }

  await join_room(room_id, "") ;
}

async function clicked_button_room_confirm_password_send(room_id)
{
  var password ;
  password = document.getElementById("input-room-password").value ;
  await join_room(room_id, password) ;
}

async function join_room(room_id, password)
{
  var list ;
  var temp_result ;

  await post_join_room_api(room_id, password) ;
  temp_result = global_space.get_temp_result() ;
  if (! temp_result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
    return ;
  }
  if (temp_result ["status"] != "success")
  {
    if (temp_result ["message"] === "wrong password")
    {
      alert("密碼錯誤！") ;
      return ;
    }
    alert(temp_result ["message"]) ;
    return ;
  }

  document.getElementById("div-rooms-list-status-info-overlay").style.display = "none" ;

  await load_player_status() ;
  // show room poparea
  //document.getElementById("div-room-detail-overlay").style.display = "block" ;
}

function clicked_button_room_confirm_password_cancel()
{
  document.getElementById("div-rooms-list-status-info-overlay").style.display = "none" ;
}

async function post_join_room_api(room_id, password)
{
  let url = "/api/v1/bingo/rooms/" ;
  let vbody ;
  let request_body ;
  url += room_id + "/participants" ;
  vbody = {
    "password": password
  } ;
  request_body = JSON.stringify(vbody) ;
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
    const retrieve_body = await response.json () ;
    global_space.set_temp_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function edit_html_code_for_room_detail()
{
  var room_detail ;
  var temp ;
  var element ;
  var leader_id ;
  var player_number ;

  room_detail = global_space.get_single_room_detail() ;

  document.getElementById("div-room-detail-room-number").innerHTML = room_detail ["room_id"] + " 房" ;

  document.getElementById("div-room-detail-player1-info-leader-flag").innerHTML = "　" ;
  document.getElementById("div-room-detail-player2-info-leader-flag").innerHTML = "　" ;
  document.getElementById("div-room-detail-player3-info-leader-flag").innerHTML = "　" ;
  document.getElementById("div-room-detail-player4-info-leader-flag").innerHTML = "　" ;

  element = room_detail ["player" + room_detail ["room_leader"] + "_position"] ;
  document.getElementById("div-room-detail-player" + element + "-info-leader-flag").innerHTML = "室長" ;

  document.getElementById("div-room-detail-player1-info-id").innerHTML = "　" ;
  document.getElementById("div-room-detail-player2-info-id").innerHTML = "　" ;
  document.getElementById("div-room-detail-player3-info-id").innerHTML = "　" ;
  document.getElementById("div-room-detail-player4-info-id").innerHTML = "　" ;
  if ("string" === typeof room_detail ["player1_account_id"])
  {
    temp = room_detail ["player1_account_id"] ;
    element = room_detail ["player1_position"] ;
    document.getElementById("div-room-detail-player" + element + "-info-id").innerHTML = temp ;
  }

  if ("string" === typeof room_detail ["player2_account_id"])
  {
    temp = room_detail ["player2_account_id"] ;
    element = room_detail ["player2_position"] ;
    document.getElementById("div-room-detail-player" + element + "-info-id").innerHTML = temp ;
  }

  if ("string" === typeof room_detail ["player3_account_id"])
  {
    temp = room_detail ["player3_account_id"] ;
    element = room_detail ["player3_position"] ;
    document.getElementById("div-room-detail-player" + element + "-info-id").innerHTML = temp ;
  }

  if ("string" === typeof room_detail ["player4_account_id"])
  {
    temp = room_detail ["player4_account_id"] ;
    element = room_detail ["player4_position"] ;
    document.getElementById("div-room-detail-player" + element + "-info-id").innerHTML = temp ;
  }

  document.getElementById("div-room-detail-player1-info-status").innerHTML = "　" ;
  document.getElementById("div-room-detail-player2-info-status").innerHTML = "　" ;
  document.getElementById("div-room-detail-player3-info-status").innerHTML = "　" ;
  document.getElementById("div-room-detail-player4-info-status").innerHTML = "　" ;
  if (room_detail ["attendance"] === 4)
  {
    temp = "　" ;
    if (room_detail ["player4_ready_status"] === 'R')
    {
      temp = "準備好了" ;
    }
    element = room_detail ["player4_position"] ;
    document.getElementById("div-room-detail-player" + element + "-info-status").innerHTML = temp ;
  }
  if (room_detail ["attendance"] >= 3)
  {
    temp = "　" ;
    if (room_detail ["player3_ready_status"] === 'R')
    {
      temp = "準備好了" ;
    }
    element = room_detail ["player3_position"] ;
    document.getElementById("div-room-detail-player" + element + "-info-status").innerHTML = temp ;
  }
  if (room_detail ["attendance"] >= 2)
  {
    temp = "　" ;
    if (room_detail ["player2_ready_status"] === 'R')
    {
      temp = "準備好了" ;
    }
    element = room_detail ["player2_position"] ;
    document.getElementById("div-room-detail-player" + element + "-info-status").innerHTML = temp ;
  }
  temp = "　" ;
  if (room_detail ["player1_ready_status"] === 'R')
  {
    temp = "準備好了" ;
  }
  element = room_detail ["player1_position"] ;
  document.getElementById("div-room-detail-player" + element + "-info-status").innerHTML = temp ;

  if (room_detail ["private"] === 0)
  {
    document.getElementById("div-room-detail-privacy-text").innerHTML = "<br />公開房間<br /><br />" ;
    document.getElementById("radio-privacy-public").checked = true ;
    document.getElementById("div-room-detail-password-area-container").style.display = "none" ;
  }
  else
  {
    document.getElementById("div-room-detail-privacy-text").innerHTML = "<br />私人房間<br /><br />" ;
    document.getElementById("radio-privacy-private").checked = true ;
    document.getElementById("div-room-detail-password-area-container").style.display = "block" ;
  }

  if (room_detail ["bingo_size"] === 5)
  {
    document.getElementById("radio-bingo-size-5").checked = true ;
  }

  if (room_detail ["bingo_size"] === 6)
  {
    document.getElementById("radio-bingo-size-6").checked = true ;
  }

  document.getElementById("div-room-detail-bingo-size-text").innerHTML = "<br />大小：" + room_detail ["bingo_size"] + " x " + room_detail ["bingo_size"] + "<br /><br />" ;

  leader_id = room_detail ["player" + room_detail ["room_leader"] + "_id"].toString() ;
  if (localStorage.getItem("user_id") === leader_id)
  {
    document.getElementById("div-room-detail-privacy-text").style.display = "none" ;
    document.getElementById("div-room-detail-privacy-option").style.display = "block" ;

    document.getElementById("div-room-detail-bingo-size-text").style.display = "none" ;
    document.getElementById("div-room-detail-bingo-size-option").style.display = "block" ;

    document.getElementById("div-room-detail-max-attendance-option").style.display = "block" ;
    temp = "" ;

    //document.getElementById("button-room-detail-start-now").style.display = "inline-block" ;
  }
  else
  {
    document.getElementById("div-room-detail-privacy-option").style.display = "none" ;
    document.getElementById("div-room-detail-privacy-text").style.display = "block" ;

    document.getElementById("div-room-detail-bingo-size-option").style.display = "none" ;
    document.getElementById("div-room-detail-bingo-size-text").style.display = "block" ;

    document.getElementById("div-room-detail-max-attendance-option").style.display = "none" ;
    temp = "<br />" ;

    //document.getElementById("button-room-detail-start-now").style.display = "none" ;
  }

  document.getElementById("div-room-detail-attendance-text").innerHTML = temp + "目前 " + room_detail ["attendance"] + " / " + room_detail ["max_attendance"] + " 人" ;

  document.getElementById("radio-max-attendance-" + room_detail ["max_attendance"]).checked = true ;


  document.getElementById("radio-max-attendance-2").disabled = true ;
  document.getElementById("span-radio-label-max-attendance-2").style.textDecoration = "line-through #800000" ;
  document.getElementById("span-radio-label-max-attendance-2").style.color = "#800000" ;
  
  document.getElementById("radio-max-attendance-3").disabled = true ;
  document.getElementById("span-radio-label-max-attendance-3").style.textDecoration = "line-through #800000" ;
  document.getElementById("span-radio-label-max-attendance-3").style.color = "#800000" ;
  if (room_detail ["attendance"] <= 3)
  {
    document.getElementById("radio-max-attendance-3").disabled = false ;
    document.getElementById("span-radio-label-max-attendance-3").style.textDecoration = "initial" ;
    document.getElementById("span-radio-label-max-attendance-3").style.color = "inherit" ;
  }
  if (room_detail ["attendance"] <= 2)
  {
    document.getElementById("radio-max-attendance-2").disabled = false ;
    document.getElementById("span-radio-label-max-attendance-2").style.textDecoration = "initial" ;
    document.getElementById("span-radio-label-max-attendance-2").style.color = "inherit" ;
  }

  player_number = 0 ;
  if (room_detail ["attendance"] === 4)
  {
    if (localStorage.getItem("user_id") === room_detail ["player4_id"].toString())
    {
      player_number = 4 ;
    }
  }
  if (room_detail ["attendance"] >= 3)
  {
    if (localStorage.getItem("user_id") === room_detail ["player3_id"].toString())
    {
      player_number = 3 ;
    }
  }
  if (room_detail ["attendance"] >= 2)
  {
    if (localStorage.getItem("user_id") === room_detail ["player2_id"].toString())
    {
      player_number = 2 ;
    }
  }
  if (localStorage.getItem("user_id") === room_detail ["player1_id"].toString())
  {
    player_number = 1 ;
  }
  element = room_detail ["player" + player_number + "_ready_status"] ;
  if (element === "R")
  {
    document.getElementById("button-room-detail-get-ready").style.display = "none" ;
    document.getElementById("button-room-detail-not-ready").style.display = "inline-block" ;

    document.getElementById("button-room-detail-leave-room").disabled = true ;
  }
  else
  {
    document.getElementById("button-room-detail-not-ready").style.display = "none" ;
    document.getElementById("button-room-detail-get-ready").style.display = "inline-block" ;

    document.getElementById("button-room-detail-leave-room").disabled = false ;
  }
}

async function clicked_radio_privacy_public()
{
  let result ;
  let player_status ;

  await patch_room_detail_privacy(0) ;
  result = global_space.get_request_privacy_result() ;

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
  }
  else if (result ["status"] != "success")
  {
    alert("失敗\n" + result ["message"]) ;
  }
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  edit_html_code_for_room_detail() ;
}

async function clicked_radio_privacy_private()
{
  let result ;
  let player_status ;

  await patch_room_detail_privacy(1) ;
  result = global_space.get_request_privacy_result() ;

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
  }
  else if (result ["status"] != "success")
  {
    alert("失敗\n" + result ["message"]) ;
  }
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  edit_html_code_for_room_detail() ;
}

async function clicked_radio_bingo_size(size)
{
  let result ;
  let player_status ;

  await patch_room_detail_bingo_size(size) ;
  result = global_space.get_request_bingo_size_result() ;

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
  }
  else if (result ["status"] != "success")
  {
    alert("失敗\n" + result ["message"]) ;
  }
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  edit_html_code_for_room_detail() ;
}

async function clicked_radio_max_attendance(max)
{
  let result ;
  let player_status ;

  await patch_room_detail_max_attendance(max) ;
  result = global_space.get_request_max_attendance_result() ;

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
  }
  else if (result ["status"] != "success")
  {
    alert("失敗\n" + result ["message"]) ;
  }
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  edit_html_code_for_room_detail() ;

  // update screen
  await load_player_status() ;
}

async function clicked_get_ready()
{
  let result ;
  let player_status ;

  document.getElementById("button-room-detail-get-ready").disabled = true ;

  await patch_room_detail_player_ready_status("R") ;
  result = global_space.get_player_ready_status() ;

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
    document.getElementById("button-room-detail-get-ready").disabled = false ;
    return ;
  }

  if (result ["status"] != "success")
  {
    alert("失敗\n" + result ["message"]) ;
    document.getElementById("button-room-detail-get-ready").disabled = false ;
    return ;
  }
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  edit_html_code_for_room_detail() ;

  document.getElementById("button-room-detail-get-ready").style.display = "none" ;
  document.getElementById("button-room-detail-get-ready").disabled = false ;

  document.getElementById("button-room-detail-not-ready").style.display = "inline-block" ;

  // update screen
  await load_player_status() ;
}

async function clicked_not_ready()
{
  let result ;
  let player_status ;

  document.getElementById("button-room-detail-not-ready").disabled = true ;

  await patch_room_detail_player_ready_status("P") ;
  result = global_space.get_player_ready_status() ;

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
    document.getElementById("button-room-detail-not-ready").disabled = false ;
    return ;
  }

  if (result ["status"] != "success")
  {
    alert("失敗\n" + result ["message"]) ;
    document.getElementById("button-room-detail-not-ready").disabled = false ;
    return ;
  }
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  edit_html_code_for_room_detail() ;

  document.getElementById("button-room-detail-not-ready").style.display = "none" ;
  document.getElementById("button-room-detail-not-ready").disabled = false ;

  document.getElementById("button-room-detail-get-ready").style.display = "inline-block" ;
}

async function patch_room_detail_player_ready_status(player_status)
{
  let url = "/api/v1/bingo/rooms/" ;
  let vbody ;
  let request_body ;
  let room_detail ;
  room_detail = global_space.get_single_room_detail() ;
  url += room_detail ["room_id"] ;
  vbody = {
    "player_ready_status": player_status
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
    global_space.set_player_ready_status(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function patch_room_detail_privacy(privacy)
{
  let url = "/api/v1/bingo/rooms/" ;
  let vbody ;
  let request_body ;
  let room_detail ;
  room_detail = global_space.get_single_room_detail() ;
  url += room_detail ["room_id"] ;
  vbody = {
    "privacy": privacy
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
    global_space.set_request_privacy_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function patch_room_detail_bingo_size(size)
{
  let url = "/api/v1/bingo/rooms/" ;
  let vbody ;
  let request_body ;
  let room_detail ;
  room_detail = global_space.get_single_room_detail() ;
  url += room_detail ["room_id"] ;
  vbody = {
    "bingo_size": size
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
    global_space.set_request_bingo_size_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function patch_room_detail_max_attendance(max)
{
  let url = "/api/v1/bingo/rooms/" ;
  let vbody ;
  let request_body ;
  let room_detail ;
  room_detail = global_space.get_single_room_detail() ;
  url += room_detail ["room_id"] ;
  vbody = {
    "max_attendance": max
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
    global_space.set_request_max_attendance_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_leave_room()
{
  var result ;
  await delete_leave_room_api() ;
  result = global_space.get_request_leave_room_result() ;

  //document.getElementById("div-test-leave-room-temp").innerHTML = JSON.stringify(result) ;
  //alert(JSON.stringify(result)) ;
  //return ;

  if (! (typeof result === "object"))
  {
    alert("data error") ;
    return ;
  }

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
    document.getElementById("button-room-detail-not-ready").disabled = false ;
    return ;
  }

  if (result ["status"] != "success")
  {
    alert("not success, " + result ["message"]) ;
    document.getElementById("button-room-detail-not-ready").disabled = false ;
    return ;
  }

  document.getElementById("div-room-detail-overlay").style.display = "none" ;
  clicked_rooms_list() ;
}

async function delete_leave_room_api()
{
  let url = "/api/v1/bingo/rooms/" ;
  let vbody ;
  let request_body ;
  let room_detail ;
  room_detail = global_space.get_single_room_detail() ;
  url += room_detail ["room_id"] + "/participants" ;
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
    global_space.set_request_leave_room_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

async function clicked_change_password()
{
  let result ;
  let player_status ;
  let flag ;
  let timer ;

  await patch_room_detail_password() ;
  result = global_space.get_rr_patch_room_detail_password() ;

  if (! result.hasOwnProperty("status"))
  {
    alert("not have status field") ;
  }
  else if (result ["status"] != "success")
  {
    alert("失敗\n" + result ["message"]) ;
  }
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  edit_html_code_for_room_detail() ;

  if (result ["status"] !== "success")
  {
    return ;
  }

  document.getElementById("div-change-password-result").innerHTML = "修改密碼成功" ;

  flag = global_space.get_init_password_timer_flag() ;
  if (flag === 1)
  {
    clearInterval (global_space.get_hidden_changed_password_result_timer()) ;
  }
  global_space.set_init_password_timer_flag(1) ;
  timer = setInterval (timer_hide_password_result, 5000) ;
  global_space.set_hidden_changed_password_result_timer(timer) ;
}

async function patch_room_detail_password()
{
  let url = "/api/v1/bingo/rooms/" ;
  let input_value ;
  let vbody ;
  let request_body ;
  let room_detail ;
  room_detail = global_space.get_single_room_detail() ;
  url += room_detail ["room_id"] ;

  input_value = document.getElementById("input-room-detail-password").value ;
  if (input_value.length > 20)
  {
    //alert("失敗，密碼太長，請將密碼設定在 20 字元以內！") ; return ;
  }
  vbody = {
    "password": input_value
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
    global_space.set_rr_patch_room_detail_password(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function timer_hide_password_result()
{
  document.getElementById("div-change-password-result").innerHTML = "" ;
}

async function clicked_start_now()
{
  // 直接開局
}
