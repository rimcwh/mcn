
function check_request_result_for_round_detail()
{
  let round_detail ;
  round_detail = global_space.get_single_round_detail() ;

  if (typeof round_detail != "object")
  {
    process_request_failed_for_round_detail() ;
    return -1 ;
  }
  if (! round_detail.hasOwnProperty("status"))
  {
    process_request_failed_for_round_detail() ;
    return -1 ;
  }
  if (round_detail ["status"] != "success")
  {
    process_request_failed_for_round_detail() ;
    return -1 ;
  }
  return 1 ;
}

function process_request_failed_for_round_detail()
{
  document.getElementById("div-game-ongoing-extra-info").innerHTML = "請重新連線。" ;
  document.getElementById("div-game-ongoing-extra-info").style.display = "block" ;
  document.getElementById("div-game-ongoing-overlay").style.display = "block" ;
}

async function get_single_round_detail_api(round_id)
{
  let url = "/api/v1/bingo/rounds/" ;
  url += round_id ;
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
    global_space.set_single_round_detail(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function edit_html_code_for_round_determine_grid()
{
  var round_detail ;
  var text ;
  var array_temp ;
  var loop_max ;
  var element_name_part ;
  round_detail = global_space.get_single_round_detail() ;
  text = "" ;

  if (round_detail ["attendance"] === 4)
  {
    document.getElementById("div-game-ongoing-determine-grid-player4-info-id").innerHTML = round_detail ["player4_account_id"] ;
  }
  if (round_detail ["attendance"] >= 3)
  {
    document.getElementById("div-game-ongoing-determine-grid-player3-info-id").innerHTML = round_detail ["player3_account_id"] ;
  }
  if (round_detail ["attendance"] >= 2)
  {
    document.getElementById("div-game-ongoing-determine-grid-player2-info-id").innerHTML = round_detail ["player2_account_id"] ;
  }
  document.getElementById("div-game-ongoing-determine-grid-player1-info-id").innerHTML = round_detail ["player1_account_id"] ;

  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      document.getElementById("div-game-ongoing-determine-grid-player" + i + "-info-self-flag").innerHTML = "自己" ;
      break ;
    }
  }

  for (i = 1 ; i <= 4 ; i ++)
  {
    temp = "　 " ;
    if (round_detail ["player" + i + "_grid_status"] === "R")
    {
      temp = "決定好了" ;
    }
    document.getElementById("div-game-ongoing-determine-grid-player" + i + "-info-status").innerHTML = temp ;
  }

  if (round_detail ["bingo_size"] === 5)
  {
    document.getElementById("div-game-ongoing-determine-grid-size-6-wrap").style.display = "none" ;
    document.getElementById("div-game-ongoing-determine-grid-size-5-wrap").style.display = "block" ;
  }
  else
  {
    document.getElementById("div-game-ongoing-determine-grid-size-5-wrap").style.display = "none" ;
    document.getElementById("div-game-ongoing-determine-grid-size-6-wrap").style.display = "block" ;
  }

  loop_max = round_detail ["bingo_size"] * round_detail ["bingo_size"] ;
  if (typeof round_detail ["self-grid"] === "string")
  {
    array_temp = round_detail ["self-grid"].split(',') ;

    element_name_part = "td-game-ongoing-determine-grid-size-" + round_detail ["bingo_size"] + "-cell-" ;

    for (i = 0 ; i < loop_max ; i ++)
    {
      document.getElementById(element_name_part + i).innerHTML = array_temp [i] ;
    }
    document.getElementById("button-round-ongoing-determine-grid-random-grid").disabled = true ;
    document.getElementById("button-round-ongoing-determine-grid-reset-number").disabled = true ;
    document.getElementById("button-round-ongoing-determine-gird-send-grid").disabled = true ;
    document.getElementById("div-game-ongoing-determine-grid-text-A").innerHTML = "盤面完成" ;
    document.getElementById("div-game-ongoing-determine-grid-text-B").innerHTML = "請等待其他玩家完成盤面" ;
    return ;
  }

  edit_html_code_for_determine_grid_text_A() ;

  array_temp = global_space.get_determine_grid_cells() ;

  check_grid_complete() ;

  document.getElementById("div-game-ongoing-main").style.display = "none" ;
  document.getElementById("div-game-ongoing-determine-grid").style.display = "block" ;

  return ;

  {
    var output ;
    output = "array: " + array_temp + "<br />" ;
    output += "array length: " + array_temp.length + "<br /><br />" ;
    array_temp = global_space.get_determine_grid_stack() ;
    output += "stack: " + array_temp + "<br />" ;
    output += "stack length: " + array_temp.length + "<br /><br />" ;
    output += JSON.stringify(round_detail) ;
    //output = Math.floor(Math.random() * 100) ;
    document.getElementById("div-game-ongoing-determine-grid-test-area").innerHTML = output ;
  }
  return ;




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
  if (list ["room_status" + index] === "P")
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

function clicked_determine_grid_size_5_cell(cell)
{
  var array_temp ;
  var flag_temp ;
  var current_number ;
  var stack_temp ;
  flag_temp = global_space.get_determine_grid_cell_flag() ;
  if (flag_temp [cell] === 1)
  {
    return ;
  }
  array_temp = global_space.get_determine_grid_cells() ;
  current_number = global_space.get_determine_grid_current_number() ;

  flag_temp [cell] = 1 ;
  array_temp [cell] = current_number ;

  document.getElementById("td-game-ongoing-determine-grid-size-5-cell-" + cell).innerHTML = current_number ;

  current_number ++ ;
  global_space.set_determine_grid_current_number(current_number) ;
  global_space.set_determine_grid_cells(array_temp) ;
  global_space.set_determine_grid_cell_flag(flag_temp) ;

  stack_temp = global_space.get_determine_grid_stack() ;
  stack_temp.push(cell) ;
  global_space.set_determine_grid_stack(stack_temp) ;

  edit_html_code_for_determine_grid_text_A() ;

  check_grid_complete() ;
}

function clicked_determine_grid_size_6_cell(cell)
{
  var array_temp ;
  var flag_temp ;
  var current_number ;
  var stack_temp ;
  flag_temp = global_space.get_determine_grid_cell_flag() ;
  if (flag_temp [cell] === 1)
  {
    return ;
  }
  array_temp = global_space.get_determine_grid_cells() ;
  current_number = global_space.get_determine_grid_current_number() ;

  flag_temp [cell] = 1 ;
  array_temp [cell] = current_number ;

  document.getElementById("td-game-ongoing-determine-grid-size-6-cell-" + cell).innerHTML = current_number ;

  current_number ++ ;
  global_space.set_determine_grid_current_number(current_number) ;
  global_space.set_determine_grid_cells(array_temp) ;
  global_space.set_determine_grid_cell_flag(flag_temp) ;

  stack_temp = global_space.get_determine_grid_stack() ;
  stack_temp.push(cell) ;
  global_space.set_determine_grid_stack(stack_temp) ;

  edit_html_code_for_determine_grid_text_A() ;

  check_grid_complete() ;
}

function clicked_reset_single_number()
{
  var stack_temp ;
  var array_temp ;
  var flag_temp ;
  var current_number ;
  var process_cell ;
  var round_detail ;
  var size ;
  stack_temp = global_space.get_determine_grid_stack() ;
  if (stack_temp.length <= 0)
  {
    return ;
  }

  process_cell = stack_temp.pop() ;
  array_temp = global_space.get_determine_grid_cells() ;
  flag_temp = global_space.get_determine_grid_cell_flag() ;
  current_number = global_space.get_determine_grid_current_number() ;
  round_detail = global_space.get_single_round_detail() ;
  size = round_detail ["bingo_size"] ;

  flag_temp [process_cell] = 0 ;
  array_temp [process_cell] = null ;
  current_number -- ;

  document.getElementById("td-game-ongoing-determine-grid-size-" + size + "-cell-" + process_cell).innerHTML = "" ;

  global_space.set_determine_grid_current_number(current_number) ;
  global_space.set_determine_grid_cells(array_temp) ;
  global_space.set_determine_grid_stack(stack_temp) ;

  edit_html_code_for_round_determine_grid() ;
}

function clicked_random_grid_all()
{
  var round_detail ;
  var array_buffer ;
  var grid_max ;
  var random_value ;
  var swap_temp ;
  var stack_temp ;
  var flag_temp ;
  var element_id_part ;
  round_detail = global_space.get_single_round_detail() ;
  grid_max = round_detail ["bingo_size"] * round_detail ["bingo_size"] ;

  array_buffer = [] ;
  for (i = 0 ; i < grid_max ; i ++)
  {
    array_buffer.push(i + 1) ;
  }

  for (i = 0 ; i < grid_max ; i ++)
  {
    random_value = Math.floor(Math.random() * grid_max) ;
    swap_temp = array_buffer [i] ;
    array_buffer [i] = array_buffer [random_value] ;
    array_buffer [random_value] = swap_temp ;
  }

  global_space.set_determine_grid_current_number(grid_max + 1) ;
  global_space.set_determine_grid_cells(array_buffer) ;

  stack_temp = [] ;
  for (i = 0 ; i < grid_max ; i ++)
  {
    stack_temp.push(null) ;
  }
  for (i = 0 ; i < grid_max ; i ++)
  {
    for (j = 0 ; j < grid_max ; j ++)
    {
      if (array_buffer [j] === i + 1)
      {
        stack_temp [i] = j ;
        break ;
      }
    }
  }
  global_space.set_determine_grid_stack(stack_temp) ;

  flag_temp = [] ;
  for (i = 0 ; i < grid_max ; i ++)
  {
    flag_temp.push(1) ;
  }
  global_space.set_determine_grid_cell_flag(flag_temp) ;


  if (round_detail ["bingo_size"] === 5)
  {
    element_id_part = "td-game-ongoing-determine-grid-size-5-cell-" ;
  }
  else
  {
    element_id_part = "td-game-ongoing-determine-grid-size-6-cell-" ;
  }
  for (i = 0 ; i < grid_max ; i ++)
  {
    document.getElementById(element_id_part + i).innerHTML = array_buffer [i] ;
  }

  edit_html_code_for_determine_grid_text_A() ;

  check_grid_complete() ;
}

function edit_html_code_for_determine_grid_text_A()
{
  var round_detail ;
  var array_buffer ;
  var grid_max ;
  var random_value ;
  var swap_temp ;
  var stack_temp ;
  var flag_temp ;
  var element_id_part ;
  var current_number ;
  var output ;
  round_detail = global_space.get_single_round_detail() ;
  current_number = global_space.get_determine_grid_current_number() ;
  output = "" ;
  if ((round_detail ["bingo_size"] * round_detail ["bingo_size"] + 1) === current_number)
  {
    output = "盤面完成！" ;
    document.getElementById("div-game-ongoing-determine-grid-text-A").innerHTML = output ;
    return ;
  }
  output = "點擊空白格子，安排號碼 " + current_number ;
  document.getElementById("div-game-ongoing-determine-grid-text-A").innerHTML = output ;
  return ;
}

function check_grid_complete()
{
  var round_detail ;
  var current_number ;

  round_detail = global_space.get_single_round_detail() ;
  current_number = global_space.get_determine_grid_current_number() ;

  if (current_number === (round_detail ["bingo_size"] * round_detail ["bingo_size"] + 1))
  {
    document.getElementById("button-round-ongoing-determine-gird-send-grid").disabled = false ;
  }
  else
  {
    document.getElementById("button-round-ongoing-determine-gird-send-grid").disabled = true ;
  }
}

async function clicked_send_grid()
{
  var round_detail ;
  var grid ;
  var result ;

  round_detail = global_space.get_single_round_detail() ;

  grid = global_space.get_determine_grid_cells() ;

  await patch_round_grid_api(round_detail ["round_id"], grid) ;

  result = global_space.get_request_round_grid_result() ;

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

  //alert(JSON.stringify(result)) ;
  // update screen
  await load_player_status() ;
}

async function patch_round_grid_api(round_id, grid)
{
  let url = "/api/v1/bingo/rounds/" ;
  url += round_id ;
  vbody = {
    "grid": grid
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
    global_space.set_request_round_grid_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function edit_html_code_for_round_ongoing()
{
  var round_detail ;
  var size ;
  var element_name_part, element_a_name_part, element_b_name_part ;
  var loop_max ;
  var array_temp ;
  var used_temp ;
  var player_number ;
  var target_number ;
  var disabled_flag ;
  var circled_cell ;
  var flag_temp ;
  var count_temp ;
  var i, j ;
  var temp ;
  round_detail = global_space.get_single_round_detail() ;

  if (round_detail ["round_status"] === "O")
  {
    temp = "遊戲進行中" ;
  }
  else if (round_detail ["round_status"] === "F")
  {
    temp = "遊戲已結束" ;
  }
  document.getElementById("div-game-ongoing-main-title").innerHTML = temp ;

  size = round_detail ["bingo_size"] ;
  if (size === 5)
  {
    document.getElementById("div-game-ongoing-grid-size-6-wrap").style.display = "none" ;
    document.getElementById("div-game-ongoing-grid-size-5-wrap").style.display = "block" ;
  }
  else
  {
    document.getElementById("div-game-ongoing-grid-size-5-wrap").style.display = "none" ;
    document.getElementById("div-game-ongoing-grid-size-6-wrap").style.display = "block" ;
  }

  array_temp = round_detail ["self-grid"].split(',') ;
  loop_max = size * size ;
  element_name_part = "span-text-game-ongoing-grid-size-" + size + "-cell-" ;
  for (i = 0 ; i < loop_max ; i ++)
  {
    document.getElementById(element_name_part + i).innerHTML = array_temp [i] ;
  }

  /*if (previous_cell_value >= 0 && < size * size)
  {
    // 處理 temp circled cell
    element_name_part = "span-decorated-game-ongoing-grid-size-" + size + "-cell-" + previous_cell_value ;
    document.getElementById(element_name_part).style.display = "none" ;
  }*/
  circled_cell = global_space.get_circled_cell() ;
  if ((circled_cell >= 0) && (circled_cell < size * size))
  {
    element_name_part = "span-decorated-game-ongoing-grid-size-" + size + "-cell-" + circled_cell ;
    document.getElementById(element_name_part).style.display = "inline-block" ;
    document.getElementById(element_name_part).style.border = "3px dashed var(--color-grid-cell-temp-picked-border)" ;
  }

  if (typeof round_detail ["used_number"] === "string")
  {
    element_a_name_part = "span-decorated-game-ongoing-grid-size-" + size + "-cell-" ;
    element_b_name_part = "span-text-game-ongoing-grid-size-" + size + "-cell-" ;
    used_temp = round_detail ["used_number"].split(',') ;
    for (i = 0 ; i < used_temp.length ; i ++)
    {
      target_number = used_temp [i] ;
      for (j = 0 ; j < loop_max ; j ++)
      {
        if (used_temp [i] === array_temp [j])
        {
          document.getElementById(element_a_name_part + j).style.border = "3px solid #00b000" ;
          document.getElementById(element_a_name_part + j).style.display = "inline-block" ;
          document.getElementById(element_b_name_part + j).style.color = "var(--color-grid-cell-picked)" ;
          break ;
        }
      }
    }
    // init circle number array
    flag_temp = [] ;
    for (i = 0 ; i < loop_max ; i ++)
    {
      flag_temp.push(0) ;
    }
    // flag circle number
    for (i = 0 ; i < used_temp.length ; i ++)
    {
      for (j = 0 ; j < loop_max ; j ++)
      {
        if (used_temp [i] === array_temp [j])
        {
          flag_temp [j] = 1 ;
        }
      }
    }
    // check linking line
    for (i = 0 ; i < size ; i ++)
    {
      count_temp = 1 ;
      for (j = 0 ; j < size ; j ++)
      {
        if (flag_temp [i * size + j] === 0)
        {
          count_temp = 0 ;
          break ;
        }
      }
      if (count_temp === 1)
      {
        element_name_part = "span-grid-size-" + size + "-line-row-" ;
        document.getElementById(element_name_part + i).style.display = "block" ;
      }

      count_temp = 1 ;
      for (j = 0 ; j < size ; j ++)
      {
        if (flag_temp [j * size + i] === 0)
        {
          count_temp = 0 ;
          break ;
        }
      }
      if (count_temp === 1)
      {
        element_name_part = "span-grid-size-" + size + "-line-col-" ;
        document.getElementById(element_name_part + i).style.display = "block" ;
      }
    }

    count_temp = 1 ;
    for (i = 0 ; i < size ; i ++)
    {
      if (flag_temp [i * size + i] === 0)
      {
        count_temp = 0 ;
        break ;
      }
    }
    if (count_temp === 1)
    {
      element_name_part = "span-grid-size-" + size + "-line-cross-" ;
      document.getElementById(element_name_part + "a").style.display = "block" ;
    }

    count_temp = 1 ;
    for (i = 0 ; i < size ; i ++)
    {
      if (flag_temp [(i + 1) * size - 1 - i] === 0)
      {
        count_temp = 0 ;
        break ;
      }
    }
    if (count_temp === 1)
    {
      element_name_part = "span-grid-size-" + size + "-line-cross-" ;
      document.getElementById(element_name_part + "b").style.display = "block" ;
    }
  }

  player_number = 0 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      player_number = i ;
      break ;
    }
  }
  if (round_detail ["round_status"] === "O")
  {
    if (round_detail ["whose_turn"] === player_number)
    {
      temp = "輪到您，請決定一個號碼。" ;
      disabled_flag = global_space.get_button_round_ongoing_send_circled_number_setting_disabled_flag() ;
      if (disabled_flag === 0)
      {
        document.getElementById("button-round-ongoing-main-send-circled-number").disabled = false ;
        global_space.set_button_round_ongoing_send_circled_number_setting_disabled_flag(1) ;
      }
    }
    else
    {
      temp = "等待玩家 " + String.fromCharCode(64 + round_detail ["whose_turn"]) + " 決定號碼" ;
      document.getElementById("button-round-ongoing-main-send-circled-number").disabled = true ;
      global_space.set_button_round_ongoing_send_circled_number_setting_disabled_flag(0) ;
    }
    document.getElementById("div-game-ongoing-text-A").innerHTML = temp ;
  }
  else if (round_detail ["round_status"] === "F")
  {
    temp = "" ;
    for (i = 0 ; i < round_detail ["winner"].length ; i ++)
    {
      temp += "玩家 " + String.fromCharCode(64 + round_detail ["winner"].charCodeAt(i) - 48) + " " + round_detail ["player" + round_detail ["winner"] [i] + "_account_id"] + " " ;
    }
    temp += "獲勝" ;
    document.getElementById("div-game-ongoing-text-A").innerHTML = temp ;
    document.getElementById("div-game-ongoing-text-B").innerHTML = "　" ;
    document.getElementById("button-round-ongoing-main-send-circled-number").style.display = "none" ;
    document.getElementById("button-round-ongoing-main-back-to-room").style.display = "inline-block" ;
    document.getElementById("button-round-ongoing-main-checking").style.display = "block" ;
  }

  document.getElementById("div-game-ongoing-main-player1-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player2-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player3-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player4-info-status").innerHTML = "　" ;
  if (round_detail ["attendance"] === 4)
  {
    document.getElementById("div-game-ongoing-main-player4-info-id").innerHTML = round_detail ["player4_account_id"] ;
    document.getElementById("div-game-ongoing-main-player4-info-status").innerHTML = "連線 " + round_detail ["player4_line"] + " 條" ;
  }
  if (round_detail ["attendance"] >= 3)
  {
    document.getElementById("div-game-ongoing-main-player3-info-id").innerHTML = round_detail ["player3_account_id"] ;
    document.getElementById("div-game-ongoing-main-player3-info-status").innerHTML = "連線 " + round_detail ["player3_line"] + " 條" ;
  }
  if (round_detail ["attendance"] >= 2)
  {
    document.getElementById("div-game-ongoing-main-player2-info-id").innerHTML = round_detail ["player2_account_id"] ;
    document.getElementById("div-game-ongoing-main-player2-info-status").innerHTML = "連線 " + round_detail ["player2_line"] + " 條" ;
  }
  document.getElementById("div-game-ongoing-main-player1-info-id").innerHTML = round_detail ["player1_account_id"] ;
  document.getElementById("div-game-ongoing-main-player1-info-status").innerHTML = "連線 " + round_detail ["player1_line"] + " 條" ;

  document.getElementById("div-game-ongoing-main-player" + player_number + "-info-self-flag").innerHTML = "自己" ;

  /*if (! (document.getElementById("div-game-ongoing-main-test-area").innerHTML === JSON.stringify(round_detail)))
  {
    document.getElementById("div-game-ongoing-main-test-area").innerHTML = JSON.stringify(round_detail) ;
  }*/

  document.getElementById("div-game-ongoing-determine-grid").style.display = "none" ;
  document.getElementById("div-game-ongoing-main").style.display = "block" ;
}

async function clicked_grid_size_5_cell(cell)
{
  var round_detail ;
  var player_number ;
  var i ;
  var loop_max ;
  var used_flag ;
  var used_number ;
  var self_grid ;
  var circled_cell ;
  var element_name ;
  var element ;
  round_detail = global_space.get_single_round_detail() ;

  player_number = 0 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      player_number = i ;
      break ;
    }
  }
  if (! (round_detail ["whose_turn"] === player_number))
  {
    return ;
  }

  loop_max = round_detail ["bingo_size"] * round_detail ["bingo_size"] ;
  used_flag = [] ;
  for (i = 0 ; i < loop_max ; i ++)
  {
    used_flag.push(0) ;
  }
  used_flag.push(0) ; // +1 element for 1 ~ loop max

  if (typeof round_detail ["used_number"] === "string")
  {
    used_number = round_detail ["used_number"].split(',') ;
    for (i = 0 ; i < used_number.length ; i ++)
    {
      used_flag [used_number [i]] = 1 ;
    }
  }

  self_grid = round_detail ["self-grid"].split(',') ;

  if (used_flag [parseInt (self_grid [cell])] === 1)
  {
    return ;
  }

  circled_cell = global_space.get_circled_cell() ;
  if ((circled_cell >= 0) && (circled_cell < round_detail ["bingo_size"] * round_detail ["bingo_size"]))
  {
    element_name = "span-decorated-game-ongoing-grid-size-" + round_detail ["bingo_size"] + "-cell-" + circled_cell ;
    element = document.getElementById(element_name) ;
    if (element.style.border === "3px dashed var(--color-grid-cell-temp-picked-border)")
    {
      document.getElementById(element_name).style.display = "none" ;
      document.getElementById(element_name).style.border = "3px solid #00b000" ;
    }
  }
  element_name = "span-decorated-game-ongoing-grid-size-" + round_detail ["bingo_size"] + "-cell-" + cell ;
  document.getElementById(element_name).style.display = "inline-block" ;
  document.getElementById(element_name).style.border = "3px dashed var(--color-grid-cell-temp-picked-border)" ;

  global_space.set_circled_cell(cell) ;

  global_space.set_circled_number(parseInt(self_grid [cell])) ;
  document.getElementById("div-game-ongoing-text-B").innerHTML = "選擇號碼：" + parseInt(self_grid [cell]) ;
}

async function clicked_grid_size_6_cell(cell)
{
  var round_detail ;
  var player_number ;
  var i ;
  var loop_max ;
  var used_flag ;
  var used_number ;
  var self_grid ;
  var circled_cell ;
  var element_name ;
  var element ;
  round_detail = global_space.get_single_round_detail() ;

  player_number = 0 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      player_number = i ;
      break ;
    }
  }
  if (! (round_detail ["whose_turn"] === player_number))
  {
    return ;
  }

  loop_max = round_detail ["bingo_size"] * round_detail ["bingo_size"] ;
  used_flag = [] ;
  for (i = 0 ; i < loop_max ; i ++)
  {
    used_flag.push(0) ;
  }
  used_flag.push(0) ; // +1 element for 1 ~ loop max

  if (typeof round_detail ["used_number"] === "string")
  {
    used_number = round_detail ["used_number"].split(',') ;
    for (i = 0 ; i < used_number.length ; i ++)
    {
      used_flag [used_number [i]] = 1 ;
    }
  }

  self_grid = round_detail ["self-grid"].split(',') ;

  if (used_flag [parseInt (self_grid [cell])] === 1)
  {
    return ;
  }

  circled_cell = global_space.get_circled_cell() ;
  if ((circled_cell >= 0) && (circled_cell < round_detail ["bingo_size"] * round_detail ["bingo_size"]))
  {
    element_name = "span-decorated-game-ongoing-grid-size-" + round_detail ["bingo_size"] + "-cell-" + circled_cell ;
    element = document.getElementById(element_name) ;
    if (element.style.border === "3px dashed var(--color-grid-cell-temp-picked-border)")
    {
      document.getElementById(element_name).style.display = "none" ;
      document.getElementById(element_name).style.border = "3px solid #00b000" ;
    }
  }
  element_name = "span-decorated-game-ongoing-grid-size-" + round_detail ["bingo_size"] + "-cell-" + cell ;
  document.getElementById(element_name).style.display = "inline-block" ;
  document.getElementById(element_name).style.border = "3px dashed var(--color-grid-cell-temp-picked-border)" ;

  global_space.set_circled_cell(cell) ;

  global_space.set_circled_number(parseInt(self_grid [cell])) ;
  document.getElementById("div-game-ongoing-text-B").innerHTML = "選擇號碼：" + parseInt(self_grid [cell]) ;
}

async function clicked_send_circled_number()
{
  var round_detail ;
  var circled_number ;
  var request_result ;

  document.getElementById("button-round-ongoing-main-send-circled-number").disabled = true ;

  round_detail = global_space.get_single_round_detail() ;

  circled_number = global_space.get_circled_number() ;

  await patch_round_picked_number_api(round_detail ["round_id"], circled_number) ;

  request_result = global_space.get_request_round_picked_number_result() ;

  if (! (typeof request_result === "object"))
  {
    alert("request_result not object!") ;
    document.getElementById("button-round-ongoing-main-send-circled-number").disabled = false ;
    return ;
  }
  if (! request_result.hasOwnProperty("status"))
  {
    alert("failed, request_result do not have property status!") ;
    document.getElementById("button-round-ongoing-main-send-circled-number").disabled = false ;
    return ;
  }
  if (! (request_result ["status"] === "success"))
  {
    alert("失敗\n" + request_result ["message"]) ;
    document.getElementById("button-round-ongoing-main-send-circled-number").disabled = false ;
    return ;
  }
  global_space.set_circled_number(0) ;
  document.getElementById("div-game-ongoing-text-B").innerHTML = "　" ;

  // update screen
  await load_player_status() ;
}

async function patch_round_picked_number_api(round_id, picked_number)
{
  let url = "/api/v1/bingo/rounds/" ;
  url += round_id ;
  vbody = {
    "picked_number": picked_number
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
    global_space.set_request_round_picked_number_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function edit_html_code_for_round_finish()
{
  var check_finish ;
  check_finish = global_space.get_check_finish() ;
  if (check_finish === 0)
  {
    // show room detail
    return ;
  }
  edit_html_code_for_round_ongoing() ;
}

async function clicked_back_to_room()
{
  var request_result ;
  var player_status ;
  await patch_player_status_place_api("back_room_from_finished_game") ;
  request_result = global_space.get_request_player_status_place_result() ;
  if (! (typeof request_result === "object"))
  {
    alert("failed, request_result not object!") ;
    return ;
  }
  if (! request_result.hasOwnProperty("status"))
  {
    alert("failed, request_result do not have property status!") ;
    return -1 ;
  }
  if (! (request_result ["status"] === "success"))
  {
    alert("失敗\n" + request_result ["message"]) ;
    return ;
  }
  global_space.set_button_round_ongoing_send_circled_number_setting_disabled_flag(0) ;
  player_status = global_space.get_player_status() ;
  await get_single_room_detail_api(player_status ["room_id"]) ;
  room_detail = global_space.get_single_room_detail() ;

  if (! (typeof room_detail === "object"))
  {
    alert("failed, room_detail not object!") ;
    return ;
  }
  if (! room_detail.hasOwnProperty("status"))
  {
    alert("failed, room_detail do not have property status!") ;
    return -1 ;
  }
  if (room_detail ["status"] != "success")
  {
    alert(room_detail ["message"]) ;
    return -1 ;
  }

  document.getElementById("button-round-ongoing-main-back-to-room").style.display = "none" ;
  document.getElementById("button-round-ongoing-main-checking").style.display = "none" ;
  // show room detail
  edit_html_code_for_room_detail() ;
  document.getElementById("div-rooms-list").style.display = "block" ;
  document.getElementById("div-room-detail-overlay").style.display = "block" ;
  document.getElementById("div-game-ongoing-overlay").style.display = "none" ;
  document.getElementById("div-game-ongoing-main").style.display = "none" ;

  init_determine_grid_element() ;
  init_ongoing_element() ;
}

function clicked_checking()
{
  edit_html_code_for_round_checking() ;
  global_space.set_game_checking_back_place("G") ;

  document.getElementById("div-game-ongoing-main").style.display = "none" ;
  document.getElementById("div-game-checking-overlay").style.display = "block" ;

  return ;
}

function edit_html_code_for_round_checking()
{
  var round_detail ;
  var element ;
  var i ;
  var self_number ;
  var text_counting_linking_line ;

  round_detail = global_space.get_single_round_detail() ;

  document.getElementById("div-game-checking-self-grid-title").innerHTML = "您的盤面" ;

  edit_html_code_for_checking_other_grid_info() ;

  init_game_checking_single_grid(5, "#div-game-checking-self-grid-size-5-wrap") ;
  init_game_checking_single_grid(6, "#div-game-checking-self-grid-size-6-wrap") ;
  init_game_checking_single_grid(5, "#div-game-checking-other-player-A-grid-size-5-wrap") ;
  init_game_checking_single_grid(6, "#div-game-checking-other-player-A-grid-size-6-wrap") ;
  init_game_checking_single_grid(5, "#div-game-checking-other-player-B-grid-size-5-wrap") ;
  init_game_checking_single_grid(6, "#div-game-checking-other-player-B-grid-size-6-wrap") ;
  init_game_checking_single_grid(5, "#div-game-checking-other-player-C-grid-size-5-wrap") ;
  init_game_checking_single_grid(6, "#div-game-checking-other-player-C-grid-size-6-wrap") ;

  document.getElementById("div-game-checking-self-grid-size-5-wrap").style.display = "none" ;
  document.getElementById("div-game-checking-self-grid-size-6-wrap").style.display = "none" ;
  document.getElementById("div-game-checking-other-player-A-grid-size-5-wrap").style.display = "none" ;
  document.getElementById("div-game-checking-other-player-A-grid-size-6-wrap").style.display = "none" ;
  document.getElementById("div-game-checking-other-player-B-grid-size-5-wrap").style.display = "none" ;
  document.getElementById("div-game-checking-other-player-B-grid-size-6-wrap").style.display = "none" ;
  document.getElementById("div-game-checking-other-player-C-grid-size-5-wrap").style.display = "none" ;
  document.getElementById("div-game-checking-other-player-C-grid-size-6-wrap").style.display = "none" ;

  document.getElementById("div-game-checking-self-grid-size-" + round_detail ["bingo_size"] + "-wrap").style.display = "block" ;
  document.getElementById("div-game-checking-other-player-A-grid-size-" + round_detail ["bingo_size"] + "-wrap").style.display = "block" ;
  document.getElementById("div-game-checking-other-player-B-grid-size-" + round_detail ["bingo_size"] + "-wrap").style.display = "block" ;
  document.getElementById("div-game-checking-other-player-C-grid-size-" + round_detail ["bingo_size"] + "-wrap").style.display = "block" ;

  self_number = 1 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      self_number = i ;
      break ;
    }
  }

  text_counting_linking_line = "" ;
  for (i = 1 ; i <= round_detail ["max_attendance"] ; i ++)
  {
    if (self_number === i)
    {
      text_counting_linking_line += "您的連線數：0<br /><br />" ;
    }
    else
    {
      text_counting_linking_line += "玩家 " + String.fromCharCode(64 + i) + " 的連線數：0<br /><br />" ;
    }
  }

  document.getElementById("div-game-checking-counting-linking-line").innerHTML = text_counting_linking_line ;

  document.getElementById("button-game-checking-next-step").disabled = false ;
  document.getElementById("button-game-checking-previous-step").disabled = true ;

  edit_html_code_for_game_checking_grid_text() ;

  global_space.set_game_checking_step(-1) ;
  edit_html_code_for_game_checking_record() ;
}

function edit_html_code_for_checking_other_grid_info()
{
  var round_detail ;
  var i ;
  var self_number ;
  var temp ;
  round_detail = global_space.get_single_round_detail() ;
  self_number = 1 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      self_number = i ;
      break ;
    }
  }

  document.getElementById("div-game-checking-other-player-A-grid-title").innerHTML = "　" ;
  document.getElementById("div-game-checking-other-player-B-grid-title").innerHTML = "　" ;
  document.getElementById("div-game-checking-other-player-C-grid-title").innerHTML = "　" ;

  i = 1 ;
  if (i === self_number)
  {
    i ++ ;
  }
  temp = "玩家 " + String.fromCharCode(64 + i) + " " + round_detail ["player" + i + "_account_id"] ;
  document.getElementById("div-game-checking-other-player-A-grid-title").innerHTML = temp ;
  i++ ;

  if (i === self_number)
  {
    i ++ ;
  }
  if (i > round_detail ["max_attendance"])
  {
    return ;
  }
  temp = "玩家 " + String.fromCharCode(64 + i) + " " + round_detail ["player" + i + "_account_id"] ;
  document.getElementById("div-game-checking-other-player-B-grid-title").innerHTML = temp ;
  i++ ;

  if (i === self_number)
  {
    i ++ ;
  }
  if (i > round_detail ["max_attendance"])
  {
    return ;
  }
  temp = "玩家 " + String.fromCharCode(64 + i) + " " + round_detail ["player" + i + "_account_id"] ;
  document.getElementById("div-game-checking-other-player-C-grid-title").innerHTML = temp ;
  i++ ;
}

function edit_html_code_for_game_checking_grid_text()
{
  var round_detail ;
  var size ;
  var cell_max ;
  var wrap_id ;
  var i, j ;
  var td_element ;
  var text_element ;
  var decorated_element ;
  var collection ;
  var self_number ;
  var array_temp ;
  var temp ;
  round_detail = global_space.get_single_round_detail() ;
  size = round_detail ["bingo_size"] ;
  cell_max = size * size ;

  self_number = 1 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      self_number = i ;
      break ;
    }
  }

  wrap_id = "#div-game-checking-self-grid-size-" + size + "-wrap" ;
  array_temp = round_detail ["self-grid"].split(',') ;
  for (i = 0 ; i < cell_max ; i ++)
  {
    row = parseInt(i / size) + 1 ;
    col = i % size + 1 ;
    selector_pattern_part = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
    td_element = document.querySelector(selector_pattern_part) ;
    /*collection = td_element.children ;

    text_element = collection [0] ;
    decorated_element = collection [1] ;*/
    text_element = td_element.firstElementChild ;
    decorated_element = td_element.lastElementChild ;
    text_element.innerHTML = array_temp [i] ;
  }

  j = 1 ;
  if (j === self_number)
  {
    j ++ ;
  }
  if (j > round_detail ["max_attendance"])
  {
    return ;
  }
  wrap_id = "#div-game-checking-other-player-A-grid-size-" + size + "-wrap" ;
  array_temp = round_detail ["grid" + j].split(',') ;
  for (i = 0 ; i < cell_max ; i ++)
  {
    row = parseInt(i / size) + 1 ;
    col = i % size + 1 ;
    selector_pattern_part = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
    td_element = document.querySelector(selector_pattern_part) ;

    text_element = td_element.firstElementChild ;
    decorated_element = td_element.lastElementChild ;
    text_element.innerHTML = array_temp [i] ;
  }
  j ++ ;

  if (j === self_number)
  {
    j ++ ;
  }
  if (j > round_detail ["max_attendance"])
  {
    return ;
  }
  wrap_id = "#div-game-checking-other-player-B-grid-size-" + size + "-wrap" ;
  array_temp = round_detail ["grid" + j].split(',') ;
  for (i = 0 ; i < cell_max ; i ++)
  {
    row = parseInt(i / size) + 1 ;
    col = i % size + 1 ;
    selector_pattern_part = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
    td_element = document.querySelector(selector_pattern_part) ;

    text_element = td_element.firstElementChild ;
    decorated_element = td_element.lastElementChild ;
    text_element.innerHTML = array_temp [i] ;
  }
  j ++ ;

  if (j === self_number)
  {
    j ++ ;
  }
  if (j > round_detail ["max_attendance"])
  {
    return ;
  }
  wrap_id = "#div-game-checking-other-player-C-grid-size-" + size + "-wrap" ;
  array_temp = round_detail ["grid" + j].split(',') ;
  for (i = 0 ; i < cell_max ; i ++)
  {
    row = parseInt(i / size) + 1 ;
    col = i % size + 1 ;
    selector_pattern_part = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
    td_element = document.querySelector(selector_pattern_part) ;

    text_element = td_element.firstElementChild ;
    decorated_element = td_element.lastElementChild ;
    text_element.innerHTML = array_temp [i] ;
  }
  j ++ ;
}

function edit_html_code_for_game_checking_record()
{
  var round_detail ;
  var step_number ;
  var self_number ;
  var output ;
  var i ;
  var text_temp ;
  var player_temp ;
  var array_temp ;
  var game_checking_counting_linking_line ;
  var max_line ;
  var winner ;
  var text_winner ;
  round_detail = global_space.get_single_round_detail() ;
  step_number = global_space.get_game_checking_step() ;

  self_number = 1 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      self_number = i ;
      break ;
    }
  }

  array_temp = round_detail ["used_number"].split(',') ;
  output = "遊戲開始" ;
  for (i = 0 ; i <= step_number ; i ++)
  {
    if (i % round_detail ["max_attendance"] === (self_number - 1))
    {
      player = "您" ;
    }
    else
    {
      player = "玩家 " + String.fromCharCode(65 + (i % round_detail ["max_attendance"])) + " " ;
    }
    text_temp = "第 " + (i + 1) + " 步，" + player + "選擇號碼 " + array_temp [i] + "<br />" ;
    output = text_temp + output ;
  }
  if (step_number === (array_temp.length - 1))
  {
    output = "遊戲結束<br />" + output ;
    game_checking_counting_linking_line = global_space.get_game_checking_counting_linking_line() ;

    max_line = -1 ;
    for (i = 0 ; i < round_detail ["max_attendance"] ; i ++)
    {
      if (game_checking_counting_linking_line [i] > max_line)
      {
        max_line = game_checking_counting_linking_line [i] ;
      }
    }

    winner = "" ;
    for (i = 0 ; i < round_detail ["max_attendance"] ; i ++)
    {
      if (game_checking_counting_linking_line [i] === max_line)
      {
        winner += (i + 1) ;
      }
    }

    text_winner = "" ;
    for (i = 0 ; i < winner.length ; i ++)
    {
      text_winner += "玩家 " + String.fromCharCode(64 + parseInt(winner [i])) + " " ;
      if (parseInt(winner [i]) === self_number)
      {
        text_winner += "(您) " ;
      }
      text_winner += "獲勝<br />" ;
    }
    output = text_winner + output ;
  }
  document.getElementById("div-game-checking-record").innerHTML = output ;
}

function edit_html_code_for_game_checking_grid_action()
{
  var round_detail ;
  var self_number ;
  var used_array ;
  var grid_array ;
  var wrap_id ;
  var size ;
  var i ;
  var sign_count ;
  var self_line_count ;
  var line_count ;
  var text_player ;
  var text_counting_linking_line ;
  var game_checking_counting_linking_line ;
  round_detail = global_space.get_single_round_detail() ;

  self_number = 1 ;
  for (i = 1 ; i <= 4 ; i ++)
  {
    if (round_detail ["player" + i + "_id"].toString() === localStorage.getItem("user_id"))
    {
      self_number = i ;
      break ;
    }
  }

  size = round_detail ["bingo_size"] ;

  sign_count = 1 ;
  game_checking_counting_linking_line = [] ;
  text_counting_linking_line = "" ;
  for (i = 1 ; i <= round_detail ["max_attendance"] ; i ++)
  {
    if (i === self_number)
    {
      grid_array = round_detail ["self-grid"].split(",") ;
      wrap_id = "#div-game-checking-self-grid-size-" + size + "-wrap" ;
      text_player = "您" ;
    }
    else
    {
      grid_array = round_detail ["grid" + i].split(",") ;
      wrap_id = "#div-game-checking-other-player-" + String.fromCharCode(64 + sign_count) + "-grid-size-" + size + "-wrap" ;
      text_player = "玩家 " + String.fromCharCode(64 + i) + " " ;
      sign_count ++ ;
    }
    edit_html_code_for_game_checking_grid_init_decorated(wrap_id) ;
    edit_html_code_for_game_checking_grid_decorating(wrap_id, grid_array) ;
    edit_html_code_for_game_checking_grid_init_linking_line(wrap_id) ;
    line_count = edit_html_code_for_game_checking_grid_calc_linking_line(wrap_id, grid_array) ;
    text_counting_linking_line += text_player + "的連線數：" + line_count + "<br /><br />" ;

    game_checking_counting_linking_line [i - 1] = line_count ;
  }

  document.getElementById("div-game-checking-counting-linking-line").innerHTML = text_counting_linking_line ;
  global_space.set_game_checking_counting_linking_line(game_checking_counting_linking_line) ;
  return ;

  /*
  grid_array = round_detail ["self-grid"].split(",") ;
  wrap_id = "#div-game-checking-self-grid-size-" + size + "-wrap" ;
  edit_html_code_for_game_checking_grid_init_decorated(wrap_id) ;
  edit_html_code_for_game_checking_grid_decorating(wrap_id, grid_array) ;
  edit_html_code_for_game_checking_grid_init_linking_line(wrap_id) ;
  self_line_count = edit_html_code_for_game_checking_grid_calc_linking_line(wrap_id, grid_array) ;


  i = 1 ;
  if (self_number === i)
  {
    text_counting_linking_line += "您的連線數：" + self_line_count + "<br /><br />" ;
    i ++ ;
  }
  if (i > round_detail ["max_attendance"])
  {
    document.getElementById("div-game-checking-counting-linking-line").innerHTML = text_counting_linking_line ;
    return ;
  }
  grid_array = round_detail ["grid" + i].split(",") ;
  wrap_id = "#div-game-checking-other-player-A-grid-size-" + size + "-wrap" ;
  edit_html_code_for_game_checking_grid_init_decorated(wrap_id) ;
  edit_html_code_for_game_checking_grid_decorating(wrap_id, grid_array) ;
  edit_html_code_for_game_checking_grid_init_linking_line(wrap_id) ;
  line_count = edit_html_code_for_game_checking_grid_calc_linking_line(wrap_id, grid_array) ;
  text_counting_linking_line += "玩家 " + String.fromCharCode(64 + i) + " 的連線數：" + line_count + "<br /><br />" ;
  i ++ ;

  if (self_number === i)
  {
    text_counting_linking_line += "您的連線數：" + self_line_count + "<br /><br />" ;
    i ++ ;
  }
  if (i > round_detail ["max_attendance"])
  {
    document.getElementById("div-game-checking-counting-linking-line").innerHTML = text_counting_linking_line ;
    return ;
  }
  grid_array = round_detail ["grid" + i].split(",") ;
  wrap_id = "#div-game-checking-other-player-B-grid-size-" + size + "-wrap" ;
  edit_html_code_for_game_checking_grid_init_decorated(wrap_id) ;
  edit_html_code_for_game_checking_grid_decorating(wrap_id, grid_array) ;
  edit_html_code_for_game_checking_grid_init_linking_line(wrap_id) ;
  line_count = edit_html_code_for_game_checking_grid_calc_linking_line(wrap_id, grid_array) ;
  text_counting_linking_line += "玩家 " + String.fromCharCode(64 + i) + " 的連線數：" + line_count + "<br /><br />" ;
  i ++ ;

  if (self_number === i)
  {
    text_counting_linking_line += "您的連線數：" + self_line_count + "<br /><br />" ;
    i ++ ;
  }
  if (i > round_detail ["max_attendance"])
  {
    document.getElementById("div-game-checking-counting-linking-line").innerHTML = text_counting_linking_line ;
    return ;
  }
  grid_array = round_detail ["grid" + i].split(",") ;
  wrap_id = "#div-game-checking-other-player-C-grid-size-" + size + "-wrap" ;
  edit_html_code_for_game_checking_grid_init_decorated(wrap_id) ;
  edit_html_code_for_game_checking_grid_decorating(wrap_id, grid_array) ;
  edit_html_code_for_game_checking_grid_init_linking_line(wrap_id) ;
  line_count = edit_html_code_for_game_checking_grid_calc_linking_line(wrap_id, grid_array) ;
  text_counting_linking_line += "玩家 " + String.fromCharCode(64 + i) + " 的連線數：" + line_count + "<br /><br />" ;
  i ++ ;

  if (self_number === i)
  {
    text_counting_linking_line += "您的連線數：" + self_line_count + "<br /><br />" ;
    i ++ ;
  }
  document.getElementById("div-game-checking-counting-linking-line").innerHTML = text_counting_linking_line ;
  */
}

function edit_html_code_for_game_checking_grid_init_decorated(wrap_id)
{
  var round_detail ;
  var size, cell_max ;
  var i ;
  var row, col ;
  var selector_pattern_part ;
  round_detail = global_space.get_single_round_detail() ;
  size = round_detail ["bingo_size"] ;
  cell_max = size * size ;

  for (i = 0 ; i < cell_max ; i ++)
  {
    row = parseInt(i / size) + 1 ;
    col = i % size + 1 ;
    selector_pattern_part = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
    td_element = document.querySelector(selector_pattern_part) ;
    text_element = td_element.firstElementChild ;
    decorated_element = td_element.lastElementChild ;

    text_element.style.color = "var(--color-grid-cell-normal)" ;

    decorated_element.style.border = "3px solid #00b000" ;
    decorated_element.style.display = "none" ;
  }
}

function edit_html_code_for_game_checking_grid_decorating(wrap_id, grid_array)
{
  var round_detail ;
  var step_number ;
  var self_number ;
  var used_array ;
  var size, cell_max ;
  var i, j ;
  var selector_pattern_part ;
  var row, col ;
  round_detail = global_space.get_single_round_detail() ;

  step_number = global_space.get_game_checking_step() ;

  size = round_detail ["bingo_size"] ;
  cell_max = size * size ;

  used_array = round_detail ["used_number"].split(',') ;

  for (i = 0 ; i < step_number ; i ++)
  {
    for (j = 0 ; j < cell_max ; j ++)
    {
      if (used_array [i] === grid_array [j])
      {
        break ;
      }
    }
    row = parseInt(j / size) + 1 ;
    col = j % size + 1 ;
    selector_pattern_part = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
    td_element = document.querySelector(selector_pattern_part) ;
    text_element = td_element.firstElementChild ;
    decorated_element = td_element.lastElementChild ;

    text_element.style.color = "var(--color-grid-cell-picked)" ;

    decorated_element.style.border = "3px solid #00b000" ;
    decorated_element.style.display = "block" ;
  }

  if (step_number === -1)
  {
    return ;
  }

  for (j = 0 ; j < cell_max ; j ++)
  {
    if (used_array [step_number] === grid_array [j])
    {
      break ;
    }
  }
  row = parseInt(j / size) + 1 ;
  col = j % size + 1 ;
  selector_pattern_part = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
  td_element = document.querySelector(selector_pattern_part) ;
  text_element = td_element.firstElementChild ;
  decorated_element = td_element.lastElementChild ;

  text_element.style.color = "var(--color-grid-cell-picked)" ;

  decorated_element.style.border = "3px dashed var(--color-grid-cell-temp-picked-border)" ;
  decorated_element.style.display = "block" ;
}

function edit_html_code_for_game_checking_grid_init_linking_line(wrap_id)
{
  var round_detail ;
  var size ;
  var pattern_part ;
  var element ;
  var i ;
  round_detail = global_space.get_single_round_detail() ;
  size  = round_detail ["bingo_size"] ;

  for (i = 0 ; i < size ; i ++)
  {
    pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-" ;
    element = document.querySelector(pattern_part + "row-" + i) ;
    element.style.display = "none" ;

    pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-" ;
    element = document.querySelector(pattern_part + "col-" + i) ;
    element.style.display = "none" ;
  }
  pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-cross-" ;
  element = document.querySelector(pattern_part + "a") ;
  element.style.display = "none" ;

  pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-cross-" ;
  element = document.querySelector(pattern_part + "b") ;
  element.style.display = "none" ;
}

function edit_html_code_for_game_checking_grid_calc_linking_line(wrap_id, grid_array)
{
  var round_detail ;
  var step_number ;
  var self_number ;
  var used_array ;
  var size, cell_max ;
  var i, j ;
  var row, col ;
  var flag_array ;
  var count ;
  var temp ;
  var pattern_part ;
  var element ;
  round_detail = global_space.get_single_round_detail() ;

  step_number = global_space.get_game_checking_step() ;

  size = round_detail ["bingo_size"] ;
  cell_max = size * size ;

  used_array = round_detail ["used_number"].split(',') ;

  flag_array = [] ;
  for (i = 0 ; i < cell_max ; i ++)
  {
    flag_array.push(0) ;
  }

  for (i = 0 ; i <= step_number ; i ++)
  {
    for (j = 0 ; j < cell_max ; j ++)
    {
      if (used_array [i] === grid_array [j])
      {
        flag_array [j] = 1 ;
        break ;
      }
    }
  }

  count = 0 ;
  for (i = 0 ; i < size ; i ++)
  {
    temp = 1 ;
    for (j = 0 ; j < size ; j ++)
    {
      if (flag_array [i * size + j] === 0)
      {
        temp = 0 ;
        break ;
      }
    }
    if (temp === 1)
    {
      pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-" ;
      element = document.querySelector(pattern_part + "row-" + i) ;
      element.style.display = "block" ;
    }
    count += temp ;
    //$result ['cll'] .= 'r' . $i . '_' . $temp . ' ' ;

    temp = 1 ;
    for (j = 0 ; j < size ; j ++)
    {
      if (flag_array [j * size + i] === 0)
      {
        temp = 0 ;
        break ;
      }
    }
    if (temp === 1)
    {
      pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-" ;
      element = document.querySelector(pattern_part + "col-" + i) ;
      element.style.display = "block" ;
    }
    count += temp ;
    //$result ['cll'] .= 'c' . $i . '_' . $temp . ' ' ;
  }
  
  temp = 1 ;
  for (i = 0 ; i < size ; i ++)
  {
    if (flag_array [i * size + i] === 0)
    {
      temp = 0 ;
      break ;
    }
  }
  if (temp === 1)
  {
    pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-cross-" ;
    element = document.querySelector(pattern_part + "a") ;
    element.style.display = "block" ;
  }
  count += temp ;

  temp = 1 ;
  for (i = 0 ; i < size ; i ++)
  {
    if (flag_array [(i + 1) * size - 1 - i] === 0)
    {
      temp = 0 ;
      break ;
    }
  }
  if (temp === 1)
  {
    pattern_part = wrap_id + " .span-game-checking-grid-size-" + size + "-line-cross-" ;
    element = document.querySelector(pattern_part + "b") ;
    element.style.display = "block" ;
  }
  count += temp ;
  return count ;
}

function clicked_game_checking_next_step()
{
  var round_detail ;
  var step_number ;
  var array_temp ;
  round_detail = global_space.get_single_round_detail() ;
  array_temp = round_detail ["used_number"].split(',') ;

  step_number = global_space.get_game_checking_step() ;

  step_number ++ ;
  global_space.set_game_checking_step(step_number) ;
  edit_html_code_for_game_checking_grid_action() ;
  edit_html_code_for_game_checking_record() ;

  if (step_number === array_temp.length -1)
  {
    document.getElementById("button-game-checking-next-step").disabled = true ;
  }
  else
  {
    document.getElementById("button-game-checking-next-step").disabled = false ;
  }
  if (step_number === -1)
  {
    document.getElementById("button-game-checking-previous-step").disabled = true ;
  }
  else
  {
    document.getElementById("button-game-checking-previous-step").disabled = false ;
  }
}

function clicked_game_checking_previous_step()
{
  var round_detail ;
  var step_number ;
  var array_temp ;
  round_detail = global_space.get_single_round_detail() ;
  array_temp = round_detail ["used_number"].split(',') ;

  step_number = global_space.get_game_checking_step() ;

  step_number -- ;
  global_space.set_game_checking_step(step_number) ;
  edit_html_code_for_game_checking_grid_action() ;
  edit_html_code_for_game_checking_record() ;

  if (step_number === array_temp.length -1)
  {
    document.getElementById("button-game-checking-next-step").disabled = true ;
  }
  else
  {
    document.getElementById("button-game-checking-next-step").disabled = false ;
  }
  if (step_number === -1)
  {
    document.getElementById("button-game-checking-previous-step").disabled = true ;
  }
  else
  {
    document.getElementById("button-game-checking-previous-step").disabled = false ;
  }
}

async function patch_player_status_place_api(place)
{
  let url = "/api/v1/bingo/players/" ;
  url += localStorage.getItem("user_id") ;
  vbody = {
    "place": place
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
    global_space.set_request_player_status_place_result(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function init_determine_grid_element()
{
  var array_temp ;
  global_space.set_determine_grid_current_number(1) ;
  global_space.set_determine_grid_stack([]) ;

  array_temp = [] ;
  for (i = 0 ; i < 36 ; i ++)
  {
    array_temp.push(null) ;
  }
  global_space.set_determine_grid_cells(array_temp) ;

  array_temp = [] ;
  for (i = 0 ; i < 36 ; i ++)
  {
    array_temp.push(0) ;
  }
  global_space.set_determine_grid_cell_flag(array_temp) ;

  element_name_part = "td-game-ongoing-determine-grid-size-5-cell-" ;
  for (i = 0 ; i < 25 ; i ++)
  {
    document.getElementById(element_name_part + i).innerHTML = "" ;
  }
  element_name_part = "td-game-ongoing-determine-grid-size-6-cell-" ;
  for (i = 0 ; i < 36 ; i ++)
  {
    document.getElementById(element_name_part + i).innerHTML = "" ;
  }
  document.getElementById("button-round-ongoing-determine-grid-random-grid").disabled = false ;
  document.getElementById("button-round-ongoing-determine-grid-reset-number").disabled = false ;
  document.getElementById("button-round-ongoing-determine-gird-send-grid").disabled = true ;
  document.getElementById("div-game-ongoing-determine-grid-text-A").innerHTML = "盤面完成" ;
  document.getElementById("div-game-ongoing-determine-grid-text-B").innerHTML = "" ;

  document.getElementById("div-game-ongoing-determine-grid-player1-info-self-flag").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player2-info-self-flag").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player3-info-self-flag").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player4-info-self-flag").innerHTML = "　" ;

  document.getElementById("div-game-ongoing-determine-grid-player1-info-id").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player2-info-id").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player3-info-id").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player4-info-id").innerHTML = "　" ;

  document.getElementById("div-game-ongoing-determine-grid-player1-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player2-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player3-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-determine-grid-player4-info-status").innerHTML = "　" ;
}

function init_ongoing_element()
{
  var i ;

  global_space.set_previous_circled_cell(-1) ;
  global_space.set_circled_cell(-1) ;

  for (i = 0 ; i < 25 ; i ++)
  {
    document.getElementById("span-text-game-ongoing-grid-size-5-cell-" + i).style.color = "var(--color-grid-cell-normal)" ;
    document.getElementById("span-decorated-game-ongoing-grid-size-5-cell-" + i).style.display = "none" ;
  }
  for (i = 0 ; i < 36 ; i ++)
  {
    document.getElementById("span-text-game-ongoing-grid-size-6-cell-" + i).style.color = "var(--color-grid-cell-normal)" ;
    document.getElementById("span-decorated-game-ongoing-grid-size-6-cell-" + i).style.display = "none" ;
  }

  for (i = 0 ; i < 5 ; i ++)
  {
    document.getElementById("span-grid-size-5-line-row-" + i).style.display = "none" ;
    document.getElementById("span-grid-size-5-line-col-" + i).style.display = "none" ;
  }
  for (i = 0 ; i < 6 ; i ++)
  {
    document.getElementById("span-grid-size-6-line-row-" + i).style.display = "none" ;
    document.getElementById("span-grid-size-6-line-col-" + i).style.display = "none" ;
  }
  document.getElementById("span-grid-size-5-line-cross-a").style.display = "none" ;
  document.getElementById("span-grid-size-5-line-cross-b").style.display = "none" ;
  document.getElementById("span-grid-size-6-line-cross-a").style.display = "none" ;
  document.getElementById("span-grid-size-6-line-cross-b").style.display = "none" ;

  document.getElementById("button-round-ongoing-main-send-circled-number").style.display = "inline-block" ;

  document.getElementById("div-game-ongoing-main-player1-info-self-flag").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player2-info-self-flag").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player3-info-self-flag").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player4-info-self-flag").innerHTML = "　" ;

  document.getElementById("div-game-ongoing-main-player1-info-id").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player2-info-id").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player3-info-id").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player4-info-id").innerHTML = "　" ;

  document.getElementById("div-game-ongoing-main-player1-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player2-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player3-info-status").innerHTML = "　" ;
  document.getElementById("div-game-ongoing-main-player4-info-status").innerHTML = "　" ;
}

function init_game_checking_single_grid(size, wrap_id)
{
  var pattern ;
  var i ;
  var element ;
  var cell_max ;
  var row, col ;
  var td_element, text_element, decorated_element ;

  pattern = "" ;
  pattern += wrap_id ;
  pattern += " " ;
  pattern += ".span-game-checking-grid-size-" + size + "-line-" ;

  for (i = 0 ; i < size ; i ++)
  {
    element = document.querySelector(pattern + "row-" + i) ;
    element.style.display = "none" ;
    element = document.querySelector(pattern + "col-" + i) ;
    element.style.display = "none" ;
  }

  element = document.querySelector(pattern + "cross-a") ;
  element.style.display = "none" ;

  element = document.querySelector(pattern + "cross-b") ;
  element.style.display = "none" ;

  cell_max = size * size ;
  for (i = 0 ; i < cell_max ; i ++)
  {
    row = parseInt(i / size) + 1 ;
    col = i % size + 1 ;
    pattern = wrap_id + " tr:nth-child(" + row + ") td:nth-child(" + col + ")" ;
    td_element = document.querySelector(pattern) ;
    text_element = td_element.firstElementChild ;
    decorated_element = td_element.lastElementChild ;
    /*collection = td_element.children ;
    text_element = collection [0] ;
    decorated_element = collection [1] ;*/
    text_element.innerHTML = "" ;
    text_element.style.color = "var(--color-grid-cell-normal)" ;
    decorated_element.style.display = "none" ;
  }
}

function clicked_game_checking_leave()
{
  var game_checking_back_place ;
  game_checking_back_place = global_space.get_game_checking_back_place() ;

  document.getElementById("div-game-checking-overlay").style.display = "none" ;
  if (game_checking_back_place === "G")
  {
    document.getElementById("div-game-ongoing-main").style.display = "block" ;
  }
  else if (game_checking_back_place === "N")
  {
    document.getElementById("div-history-record-list").style.display = "block" ;
  }
}

async function clicked_history_record()
{
  let clicked ;
  let history_record_list ;
  clicked = global_space.get_selected_tab() ;
  document.getElementById("button-" + clicked).className = "button-not-selected" ;
  document.getElementById("div-" + clicked).style.display = "none" ;

  global_space.set_selected_tab("history-record-list") ;
  document.getElementById("button-history-record-list").className = "button-selected" ;
  document.getElementById("div-history-record-list").style.display = "block" ;

  await get_history_record_list_api() ;
  history_record_list = global_space.get_history_record_list() ;
  
  if (typeof history_record_list != "object")
  {
    alert("連線錯誤，請重新連線！") ;
    return -1 ;
  }
  if (! history_record_list.hasOwnProperty("status"))
  {
    alert("連線錯誤，請重新連線！") ;
    return -1 ;
  }
  if (history_record_list ["status"] != "success")
  {
    alert("連線錯誤，請重新連線！") ;
    return -1 ;
  }

  if (history_record_list ["record_number"] === 0)
  {
    document.getElementById("div-history-record-list-title").style.display = "none" ;
    document.getElementById("ul-history-record-list").style.display = "none" ;

    document.getElementById("div-history-record-list-none-data").innerHTML = "查無資料" ;
    document.getElementById("div-history-record-list-none-data").style.display = "block" ;
    return ;
  }
  //alert(JSON.stringify(history_record_list)) ;

  edit_html_code_for_history_record_list() ;

  document.getElementById("div-history-record-list-none-data").style.display = "none" ;

  document.getElementById("div-history-record-list-title").style.display = "block" ;
  document.getElementById("ul-history-record-list").style.display = "block" ;
}

async function get_history_record_list_api()
{
  let url = "/api/v1/bingo/players/" ;
  url += localStorage.getItem("user_id") + "/rounds" ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8",
      },
    }) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    global_space.set_history_record_list(retrieve_body) ;
    return ;
  }
  catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}

function edit_html_code_for_history_record_list()
{
  var code ;
  var history_record_list ;
  var i ;
  history_record_list = global_space.get_history_record_list() ;

  code = "" ;
  for (i = 0 ; i < history_record_list ["record_number"] ; i ++)
  {
    code += "<li class = \"li-history-record-list-single-item\">" ;
    code += "<button onclick = \"clicked_history_record_detail(\'" + history_record_list ["round_id" + i] + "\')\" class = \"button-history-record-list-single-item\">查看</button>" ;
    code += "<span class = \"span-history-record-list-single-item\">" + history_record_list ["round_id" + i] + "</span>" ;
    code += "</li>" ;
  }
  document.getElementById("ul-history-record-list").innerHTML = code ;
}

async function clicked_history_record_detail(round_id)
{
  var result ;
  await get_single_round_detail_api(round_id) ;

  result = global_space.get_single_round_detail() ;
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

  edit_html_code_for_round_checking() ;
  global_space.set_game_checking_back_place("N") ;

  document.getElementById("div-history-record-list").style.display = "none" ;
  document.getElementById("div-game-checking-overlay").style.display = "block" ;

  return ;
}
