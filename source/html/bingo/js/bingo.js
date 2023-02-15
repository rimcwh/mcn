
var global_space = (function ()
{
  var selected_tab = "" ; //shared variable available only inside your module
  var continued_request_timer ;
  var hidden_changed_password_result_timer ;
  var init_password_timer_flag ;
  var rooms_list ;
  var room_field_index = 1 ;
  var rooms_list_current_page = 1 ;
  var rooms_list_max_page = 1 ;
  var single_room_detail ;
  var player_status ;
  var player_ready_status ;
  var request_bingo_size_result ;
  var request_privacy_result ;
  var request_max_attendance_result ;
  var request_leave_room_result ;
  var request_new_room_result ;
  var request_quickly_join_room_result ;
  var single_round_detail ;
  var determine_grid_current_number ;
  var determine_grid_cells ;
  var determine_grid_stack ;
  var determine_grid_cell_flag ;
  var request_round_grid_result ;
  var request_round_picked_number_result ;
  var circled_number ;
  var circled_cell ;
  var previous_circled_cell ;
  var button_round_ongoing_send_circled_number_setting_disabled_flag ;
  var check_finish ;
  var request_player_status_place_result ;
  var game_checking_step ;
  var game_checking_counting_linking_line ;
  var game_checking_back_place ;
  var rr_patch_room_detail_password ;
  var history_record_list ;
  var temp_result ;

  return { // 這邊的左大括號不能寫在下一行，會報錯，後面的都不執行 = =|||
    get_selected_tab : function () {
      return selected_tab ; // this function can access my_var
    },
    set_selected_tab : function (value)
    {
      selected_tab = value ; // this function can also access my_var
    },
    get_continued_request_timer : function ()
    {
      return continued_request_timer ; // this function can access my_var
    },
    set_continued_request_timer : function (value)
    {
      continued_request_timer = value ; // this function can also access my_var
    },
    get_hidden_changed_password_result_timer : function ()
    {
      return hidden_changed_password_result_timer ;
    },
    set_hidden_changed_password_result_timer : function (value)
    {
      hidden_changed_password_result_timer = value ;
    },
    get_init_password_timer_flag : function ()
    {
      return init_password_timer_flag ;
    },
    set_init_password_timer_flag : function (value)
    {
      init_password_timer_flag = value ;
    },
    get_rooms_list : function () {
      return rooms_list ; // this function can access my_var
    },
    set_rooms_list : function (value)
    {
      rooms_list = value ; // this function can also access my_var
    },
    get_room_field_index : function () {
      return room_field_index ; // this function can access my_var
    },
    set_room_field_index : function (value)
    {
      room_field_index = value ; // this function can also access my_var
    },
    get_rooms_list_current_page : function () {
      return rooms_list_current_page ; // this function can access my_var
    },
    set_rooms_list_current_page : function (value)
    {
      rooms_list_current_page = value ; // this function can also access my_var
    },
    get_rooms_list_max_page : function () {
      return rooms_list_max_page ; // this function can access my_var
    },
    set_rooms_list_max_page : function (value)
    {
      rooms_list_max_page = value ; // this function can also access my_var
    },
    get_single_room_detail : function () {
      return single_room_detail ; // this function can access my_var
    },
    set_single_room_detail : function (value)
    {
      single_room_detail = value ; // this function can also access my_var
    },
    get_player_status : function () {
      return player_status ; // this function can access my_var
    },
    set_player_status : function (value)
    {
      player_status = value ; // this function can also access my_var
    },
    get_player_ready_status : function () {
      return player_ready_status ;
    },
    set_player_ready_status : function (value)
    {
      player_ready_status = value ;
    },
    get_request_bingo_size_result : function () {
      return request_bingo_size_result ;
    },
    set_request_bingo_size_result : function (value)
    {
      request_bingo_size_result = value ;
    },
    get_request_privacy_result : function () {
      return request_privacy_result ;
    },
    set_request_privacy_result : function (value)
    {
      request_privacy_result = value ;
    },
    get_request_max_attendance_result : function () {
      return request_max_attendance_result ;
    },
    set_request_max_attendance_result : function (value)
    {
      request_max_attendance_result = value ;
    },
    get_request_leave_room_result : function () {
      return request_leave_room_result ;
    },
    set_request_leave_room_result : function (value)
    {
      request_leave_room_result = value ;
    },
    get_request_new_room_result : function () {
      return request_new_room_result ;
    },
    set_request_new_room_result : function (value)
    {
      request_new_room_result = value ;
    },
    get_request_quickly_join_room_result : function () {
      return request_quickly_join_room_result ;
    },
    set_request_quickly_join_room_result : function (value)
    {
      request_quickly_join_room_result = value ;
    },
    get_single_round_detail : function () {
      return single_round_detail ;
    },
    set_single_round_detail : function (value)
    {
      single_round_detail = value ;
    },
    get_determine_grid_current_number : function()
    {
      return determine_grid_current_number ;
    },
    set_determine_grid_current_number : function(value)
    {
      determine_grid_current_number = value ;
    },
    get_determine_grid_cells : function()
    {
      return determine_grid_cells ;
    },
    set_determine_grid_cells : function(value)
    {
      determine_grid_cells = value ;
    },
    get_determine_grid_stack : function()
    {
      return determine_grid_stack ;
    },
    set_determine_grid_stack : function(value)
    {
      determine_grid_stack = value ;
    },
    get_determine_grid_cell_flag : function()
    {
      return determine_grid_cell_flag ;
    },
    set_determine_grid_cell_flag : function(value)
    {
      determine_grid_cell_flag = value ;
    },
    get_request_round_grid_result : function()
    {
      return request_round_grid_result ;
    },
    set_request_round_grid_result : function(value)
    {
      request_round_grid_result = value ;
    },
    get_request_round_picked_number_result : function()
    {
      return request_round_picked_number_result ;
    },
    set_request_round_picked_number_result : function(value)
    {
      request_round_picked_number_result = value ;
    },
    get_circled_number : function()
    {
      return circled_number ;
    },
    set_circled_number : function(value)
    {
      circled_number = value ;
    },
    get_previous_circled_cell : function()
    {
      return previous_circled_cell ;
    },
    set_previous_circled_cell : function(value)
    {
      previous_circled_cell = value ;
    },
    get_button_round_ongoing_send_circled_number_setting_disabled_flag : function()
    {
      return button_round_ongoing_send_circled_number_setting_disabled_flag ;
    },
    set_button_round_ongoing_send_circled_number_setting_disabled_flag : function(value)
    {
      button_round_ongoing_send_circled_number_setting_disabled_flag = value ;
    },
    get_circled_cell : function()
    {
      return circled_cell ;
    },
    set_circled_cell : function(value)
    {
      circled_cell = value ;
    },
    get_check_finish : function()
    {
      return check_finish ;
    },
    set_check_finish : function(value)
    {
      check_finish = value ;
    },
    get_request_player_status_place_result : function()
    {
      return request_player_status_place_result ;
    },
    set_request_player_status_place_result : function(value)
    {
      request_player_status_place_result = value ;
    },
    get_game_checking_step : function()
    {
      return game_checking_step ;
    },
    set_game_checking_step : function(value)
    {
      game_checking_step = value ;
    },
    get_game_checking_counting_linking_line : function()
    {
      return game_checking_counting_linking_line ;
    },
    set_game_checking_counting_linking_line : function(value)
    {
      game_checking_counting_linking_line = value ;
    },
    get_game_checking_back_place : function()
    {
      return game_checking_back_place ;
    },
    set_game_checking_back_place : function(value)
    {
      game_checking_back_place = value ;
    },
    get_rr_patch_room_detail_password : function()
    {
      return rr_patch_room_detail_password ;
    },
    set_rr_patch_room_detail_password : function(value)
    {
      rr_patch_room_detail_password = value ;
    },
    get_history_record_list : function()
    {
      return history_record_list ;
    },
    set_history_record_list : function(value)
    {
      history_record_list = value ;
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

window.onload = async function ()
{
  var ret ;
  var timer ;
  save_csrf_token() ;
  if (localStorage.getItem("theme") === null)
  {
    localStorage.setItem("theme", "theme-light") ;
  }
  document.documentElement.className = localStorage.getItem("theme") ;
  
  global_space.set_selected_tab("rooms-list") ;
  //global_space.set_my_shop_selected_tab("checking-orders") ;
  
  /*document.getElementById("div-book-detail-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      clicked_book_detail_close() ;
    }
  });

  document.getElementById("div-my-shop-launched-books-detail-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      clicked_my_shop_launched_books_detail_edited_poparea_close() ;
    }
  });

  
  document.getElementById("div-shopping-cart-checkout-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-shopping-cart-checkout-overlay").style.display = "none" ;
    }
  });

  document.getElementById("div-my-shop-launched-books-detail-status-info-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-my-shop-launched-books-detail-status-info-overlay").style.display = "none" ;
    }
  });

  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").style.display = "none" ;
    }
  });
  
  document.getElementById("div-my-order-status-info-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-my-order-status-info-overlay").style.display = "none" ;
    }
  });
  */

  global_space.set_button_round_ongoing_send_circled_number_setting_disabled_flag(0) ;

  global_space.set_check_finish(1) ;

  global_space.set_circled_number(0) ;
  global_space.set_previous_circled_cell(-1) ;
  global_space.set_circled_cell(-1) ;

  global_space.set_init_password_timer_flag(0) ;

  init_determine_grid_element() ;

  ret = await load_player_status() ;

  /*if (ret === -1)
  {
    clicked_rooms_list() ;
  }*/

  timer = setInterval (timer_continued_request, 1000) ;
  global_space.set_continued_request_timer(timer) ;
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

function clicked_back_to_lobby()
{
  window.location.href = "../lobby/lobby.html" ;
}

async function timer_continued_request()
{
  await load_player_status() ;
}

async function load_player_status()
{
  let ret ;
  var player_status ;
  var room_detail ;
  var round_detail ;

  await get_player_status_api() ;
  player_status = global_space.get_player_status() ;

  if (typeof player_status != "object")
  {
    alert("請重新連線！") ;
    return -1 ;
  }
  if (! player_status.hasOwnProperty("status"))
  {
    return -1 ;
  }
  if (player_status ["status"] != "success")
  {
    return -1 ;
  }

  if (player_status ["place"] === "C")
  {

  }
  else if (player_status ["place"] === "G")
  {
    await get_single_round_detail_api(player_status ["round_id"]) ;
    ret = check_request_result_for_round_detail() ;
    if (ret === -1)
    {
      return -1 ;
    }

    if (document.getElementById("div-game-ongoing-extra-info").style.display != "none")
    {
      document.getElementById("div-game-ongoing-extra-info").style.display = "none" ;
    }

    round_detail = global_space.get_single_round_detail() ;

    if (round_detail ["round_status"] === "P")
    {
      edit_html_code_for_round_determine_grid() ;
    }
    else if (round_detail ["round_status"] === "O")
    {
      edit_html_code_for_round_ongoing() ;
    }
    else if (round_detail ["round_status"] === "F")
    {
      edit_html_code_for_round_finish() ;
    }

    document.getElementById("div-rooms-list").style.display = "none" ;
    document.getElementById("div-game-ongoing-overlay").style.display = "block" ;
    return 1 ;
  }
  else if (player_status ["place"] === "R")
  {
    await get_single_room_detail_api(player_status ["room_id"]) ;
    room_detail = global_space.get_single_room_detail() ;

    if (! room_detail.hasOwnProperty("status"))
    {
      return -1 ;
    }
    if (room_detail ["status"] != "success")
    {
      return -1 ;
    }

    // show room pop area
    edit_html_code_for_room_detail() ;
    document.getElementById("div-room-detail-overlay").style.display = "block" ;
    return 1 ;
  }
  else
  {
    // === "N"
    await get_rooms_list_api() ;
    edit_html_code_for_rooms_list() ;
    return 1 ;
  }
}

async function get_player_status_api()
{
  let url = "/api/v1/bingo/players/" ;
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
    global_space.set_player_status(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

