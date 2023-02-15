<?php

function test_splat_main ()
{
    test_splat_read_2 () ;
    //test_splat_update_2 () ;
    //test_splat_mix () ;
    //
    //test_splat_read () ;
    //test_splat_update () ;
}

function test_splat_mix ()
{
    $result = [] ;
    $result ['status'] = 'testing-splat-mix' ;
    $result ['message'] = 'ok' ;

    require_once (__DIR__ . '/../db_link/dbconnect_w_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;

    $i = 0 ;
    $columns = [] ;
    $columns [$i] = 'round_id' ; $i ++ ;
    $columns [$i] = 'room_status' ; $i ++ ;
    $columns [$i] = 'bingo_size' ; $i ++ ;
    $columns [$i] = 'attendance' ; $i ++ ;
    $columns [$i] = 'max_attendance' ; $i ++ ;
    $columns [$i] = 'private' ; $i ++ ;
    $columns [$i] = 'password' ; $i ++ ;
    $columns [$i] = 'room_leader' ; $i ++ ;

    $columns [$i] = 'player1_id' ; $i ++ ;
    $columns [$i] = 'player2_id' ; $i ++ ;
    $columns [$i] = 'player3_id' ; $i ++ ;
    $columns [$i] = 'player4_id' ; $i ++ ;

    $columns [$i] = 'player1_ready_status' ; $i ++ ;
    $columns [$i] = 'player2_ready_status' ; $i ++ ;
    $columns [$i] = 'player3_ready_status' ; $i ++ ;
    $columns [$i] = 'player4_ready_status' ; $i ++ ;

    $columns [$i] = 'player1_position' ; $i ++ ;
    $columns [$i] = 'player2_position' ; $i ++ ;
    $columns [$i] = 'player3_position' ; $i ++ ;
    $columns [$i] = 'player4_position' ; $i ++ ;

    $columns [$i] = 'position1' ; $i ++ ;
    $columns [$i] = 'position2' ; $i ++ ;
    $columns [$i] = 'position3' ; $i ++ ;
    $columns [$i] = 'position4' ; $i ++ ;

    $flag_round_id       = 1 ;
    $flag_room_status    = 1 ;
    $flag_bingo_size     = 1 ;
    $flag_attendance     = 1 ;
    $flag_max_attendance = 1 ;
    $flag_private        = 1 ;
    $flag_password       = 1 ;
    $flag_room_leader    = 1 ;

    $flag_player1_id = 1 ;
    $flag_player2_id = 1 ;
    $flag_player3_id = 1 ;
    $flag_player4_id = 1 ;

    $flag_player1_ready_status = 1 ;
    $flag_player2_ready_status = 1 ;
    $flag_player3_ready_status = 1 ;
    $flag_player4_ready_status = 1 ;

    $flag_player1_position = 1 ;
    $flag_player2_position = 1 ;
    $flag_player3_position = 1 ;
    $flag_player4_position = 1 ;

    $flag_position1 = 1 ;
    $flag_position2 = 1 ;
    $flag_position3 = 1 ;
    $flag_position4 = 1 ;

    $sub_query = '' ;
    if ($flag_round_id === 1)
    {
        $sub_query .= ', `round_id`' ;
    }

    $sub_query = '' ;

    $col_temp = [] ;

    foreach ($columns as $x => $x_value)
    {
        $col_temp [$x_value] = NULL ;
        $v_temp = 'flag_' . $x_value ;
        //if (${'flag_' . $x_value} === 1)
        if ($$v_temp === 1)
        {
            $sub_query .= ', `' . $x_value . '`' ;
        }
    }


    $query = '
    SELECT
        `room_id`' . $sub_query . '
    FROM `room_info`
    WHERE
        `room_id` = ?
    ' ;

    $b_room_id = 1002 ;
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $b_room_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $i = 0 ;
    $temp_param = [] ;
    $temp_param [$i] = & $col_temp ['room_id'] ; $i ++ ;
    foreach ($columns as $x => $x_value)
    {
        $v_temp = 'flag_' . $x_value ;
        if ($$v_temp === 1)
        {
            $temp_param [$i] = & $col_temp [$x_value] ; $i ++ ;
        }
    }

    $stmt -> bind_result (...$temp_param) ;

    while ($stmt -> fetch ())
    {
    }

    foreach ($columns as $x => $x_value)
    {
        $v_temp = 'flag_' . $x_value ;
        if ($$v_temp === 1)
        {
            $result [$x_value] = $col_temp [$x_value] ;
        }
    }

    $result ['sub_query'] = $sub_query ;
    $result ['query'] = $query ;
    $result ['sql-info'] = $db -> info ;

    $stmt -> free_result () ;
    $stmt -> close () ;

    $test_null = '' ;
    if ($col_temp ['player4_ready_status'] === null)
    {
        $test_null = '=== null' ;
    }
    $test_null_type = gettype ($col_temp ['player4_ready_status']) ;

    $test_null_type_value = '' ;
    if ($test_null_type === 'null')
    {
        $test_null_type_value = '=== null' ;
    }

    $result ['test_null'] = $test_null ;
    $result ['test_null_type'] = $test_null_type ;
    $result ['test_null_type_value'] = $test_null_type_value ;


    $query = '
    UPDATE `room_info`
    SET `player2_ready_status` = NULL
    WHERE
        `room_id` = ?
    ' ;

    $b_room_id = 1002 ; // b for bind
    $vars_param [$i] = & $b_room_id ;

    $stmt = $db -> prepare ($query) ;
    //$stmt -> bind_param ('si', $col_temp ['player4_ready_status'], $b_room_id) ;
    $stmt -> bind_param ('i', $b_room_id) ;
    $stmt -> execute () ;

    $matched_rows = 0 ;
    $changed_rows = 0 ;
    sscanf ($db -> info, '%*s%*s%d%*s%d', $matched_rows, $changed_rows) ;

    $result ['sub_query'] = $sub_query ;
    $result ['query'] = $query ;
    $result ['vars_param'] = $vars_param ;
    $result ['sql-info'] = $db -> info ;
    $result ['matched_rows'] = $matched_rows ;
    $result ['changed_rows'] = $changed_rows ;
    $result ['type_matched_rows'] = gettype ($matched_rows) ;
    $result ['type_changed_rows'] = gettype ($changed_rows) ;
    $result ['affected-rows'] = $stmt -> affected_rows ;
    $result ['fff'] = $col_temp ['player4_ready_status'] ;


    echo json_encode ($result) ;
    $db -> close () ;
    exit ;
}

function test_splat_read_2 ()
{
    //$result = array () ;
    $result = [] ;
    $result ['status'] = 'testing-splat-read-2' ;
    $result ['message'] = 'ok' ;

    require_once (__DIR__ . '/../db_link/dbconnect_r_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;

    $i = 0 ;
    $columns = [] ;
    $columns [$i] = 'round_id' ; $i ++ ;
    $columns [$i] = 'room_status' ; $i ++ ;
    $columns [$i] = 'bingo_size' ; $i ++ ;
    $columns [$i] = 'attendance' ; $i ++ ;
    $columns [$i] = 'max_attendance' ; $i ++ ;
    $columns [$i] = 'private' ; $i ++ ;
    $columns [$i] = 'password' ; $i ++ ;
    $columns [$i] = 'room_leader' ; $i ++ ;

    $columns [$i] = 'player1_id' ; $i ++ ;
    $columns [$i] = 'player2_id' ; $i ++ ;
    $columns [$i] = 'player3_id' ; $i ++ ;
    $columns [$i] = 'player4_id' ; $i ++ ;

    $columns [$i] = 'player1_ready_status' ; $i ++ ;
    $columns [$i] = 'player2_ready_status' ; $i ++ ;
    $columns [$i] = 'player3_ready_status' ; $i ++ ;
    $columns [$i] = 'player4_ready_status' ; $i ++ ;

    $columns [$i] = 'player1_position' ; $i ++ ;
    $columns [$i] = 'player2_position' ; $i ++ ;
    $columns [$i] = 'player3_position' ; $i ++ ;
    $columns [$i] = 'player4_position' ; $i ++ ;

    $columns [$i] = 'position1' ; $i ++ ;
    $columns [$i] = 'position2' ; $i ++ ;
    $columns [$i] = 'position3' ; $i ++ ;
    $columns [$i] = 'position4' ; $i ++ ;

    $flag_round_id       = 1 ;
    $flag_room_status    = 1 ;
    $flag_bingo_size     = 0 ;
    $flag_attendance     = 0 ;
    $flag_max_attendance = 0 ;
    $flag_private        = 0 ;
    $flag_password       = 0 ;
    $flag_room_leader    = 0 ;

    $flag_player1_id = 1 ;
    $flag_player2_id = 1 ;
    $flag_player3_id = 1 ;
    $flag_player4_id = 1 ;

    $flag_player1_ready_status = 1 ;
    $flag_player2_ready_status = 1 ;
    $flag_player3_ready_status = 1 ;
    $flag_player4_ready_status = 1 ;

    $flag_player1_position = 1 ;
    $flag_player2_position = 1 ;
    $flag_player3_position = 1 ;
    $flag_player4_position = 1 ;

    $flag_position1 = 1 ;
    $flag_position2 = 1 ;
    $flag_position3 = 1 ;
    $flag_position4 = 1 ;

    $sub_query = '' ;
    if ($flag_round_id === 1)
    {
        $sub_query .= ', `round_id`' ;
    }

    $sub_query = '' ;

    $col_temp = [] ;

    foreach ($columns as $x => $x_value)
    {
        $col_temp [$x_value] = NULL ;
        $v_temp = 'flag_' . $x_value ;
        //if (${'flag_' . $x_value} === 1)
        if ($$v_temp === 1)
        {
            $sub_query .= ', `' . $x_value . '`' ;
        }
    }


    $query = '
    SELECT
        `room_id`' . $sub_query . '
    FROM `room_info`
    WHERE
        `room_id` = ?
    ' ;

    $b_room_id = 1002 ;
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $b_room_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $i = 0 ;
    $temp_param = [] ;
    $temp_param [$i] = & $col_temp ['room_id'] ; $i ++ ;
    foreach ($columns as $x => $x_value)
    {
        $v_temp = 'flag_' . $x_value ;
        if ($$v_temp === 1)
        {
            $temp_param [$i] = & $col_temp [$x_value] ; $i ++ ;
        }
    }

    $stmt -> bind_result (...$temp_param) ;

    while ($stmt -> fetch ())
    {
    }

    foreach ($columns as $x => $x_value)
    {
        $v_temp = 'flag_' . $x_value ;
        if ($$v_temp === 1)
        {
            $result [$x_value] = $col_temp [$x_value] ;
        }
    }

    $result ['sub_query'] = $sub_query ;
    $result ['query'] = $query ;
    $result ['sql-info'] = $db -> info ;

    $stmt -> free_result () ;
    $stmt -> close () ;
    $db -> close () ;

    $test_null = '' ;
    if ($col_temp ['player4_ready_status'] === null)
    {
        $test_null = '=== null' ;
    }
    $test_null_type = gettype ($col_temp ['player4_ready_status']) ;

    $test_null_type_value = '' ;
    if ($test_null_type === 'null')
    {
        $test_null_type_value = '=== null' ;
    }

    $result ['test_null'] = $test_null ;
    $result ['test_null_type'] = $test_null_type ;
    $result ['test_null_type_value'] = $test_null_type_value ;

    echo json_encode ($result) ;
    exit ;
}

function test_splat_update_2 ()
{
    $result = [] ;
    $result ['status'] = 'testing-splat-update-2' ;
    $result ['message'] = 'ok' ;

    require_once (__DIR__ . '/../db_link/dbconnect_w_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;

    $query = '
    UPDATE `room_info`
    SET `player2_ready_status` = \'P\'
    WHERE
        `room_id` = ?
    ' ;

    $b_room_id = 1002 ; // b for bind
    $vars_param [$i] = & $b_room_id ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $b_room_id) ;
    $stmt -> execute () ;

    $matched_rows = 0 ;
    $changed_rows = 0 ;
    sscanf ($db -> info, '%*s%*s%d%*s%d', $matched_rows, $changed_rows) ;

    $result ['sub_query'] = $sub_query ;
    $result ['query'] = $query ;
    $result ['vars_param'] = $vars_param ;
    $result ['sql-info'] = $db -> info ;
    $result ['matched_rows'] = $matched_rows ;
    $result ['changed_rows'] = $changed_rows ;
    $result ['type_matched_rows'] = gettype ($matched_rows) ;
    $result ['type_changed_rows'] = gettype ($changed_rows) ;
    $result ['affected-rows'] = $stmt -> affected_rows ;

    $stmt -> free_result () ;
    $stmt -> close () ;
    $db -> close () ;

    echo json_encode ($result) ;
    exit ;
}

function test_splat_read ()
{
    //$result = array () ;
    $result = [] ;
    $result ['status'] = 'testing-splat-read' ;
    $result ['message'] = 'ok' ;
    
    $str = 'age:30 weight:60kg height:178cm' ;
    //sscanf ($str,'age:%d weight:%dkg height:%dcm', $age, $weight, $height) ;
    $temp = [] ;
    $temp [count ($temp)] = & $age ;
    $temp [count ($temp)] = & $weight ;
    $temp [count ($temp)] = & $height ;
    sscanf ($str,'age:%d weight:%dkg height:%dcm', ...$temp) ;
    /*$result ['temp'] = $temp ;
    $result ['count'] = count ($temp) ;
    $result ['age'] = $age ;
    $result ['weight'] = $weight ;
    $result ['height'] = $height ;*/

    require_once (__DIR__ . '/../db_link/dbconnect_r_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;

    $i = 0 ;
    $columns = [] ;
    $columns [$i] = 'round_id' ; $i ++ ;
    $columns [$i] = 'room_status' ; $i ++ ;
    $columns [$i] = 'bingo_size' ; $i ++ ;
    $columns [$i] = 'attendance' ; $i ++ ;
    $columns [$i] = 'max_attendance' ; $i ++ ;
    $columns [$i] = 'private' ; $i ++ ;
    $columns [$i] = 'password' ; $i ++ ;
    $columns [$i] = 'room_leader' ; $i ++ ;

    $columns [$i] = 'player1_id' ; $i ++ ;
    $columns [$i] = 'player2_id' ; $i ++ ;
    $columns [$i] = 'player3_id' ; $i ++ ;
    $columns [$i] = 'player4_id' ; $i ++ ;

    $columns [$i] = 'player1_ready_status' ; $i ++ ;
    $columns [$i] = 'player2_ready_status' ; $i ++ ;
    $columns [$i] = 'player3_ready_status' ; $i ++ ;
    $columns [$i] = 'player4_ready_status' ; $i ++ ;

    $columns [$i] = 'player1_position' ; $i ++ ;
    $columns [$i] = 'player2_position' ; $i ++ ;
    $columns [$i] = 'player3_position' ; $i ++ ;
    $columns [$i] = 'player4_position' ; $i ++ ;

    $columns [$i] = 'position1' ; $i ++ ;
    $columns [$i] = 'position2' ; $i ++ ;
    $columns [$i] = 'position3' ; $i ++ ;
    $columns [$i] = 'position4' ; $i ++ ;

    $flag_round_id       = 1 ;
    $flag_room_status    = 1 ;
    $flag_bingo_size     = 1 ;
    $flag_attendance     = 1 ;
    $flag_max_attendance = 1 ;
    $flag_private        = 1 ;
    $flag_password       = 1 ;
    $flag_room_leader    = 1 ;

    $flag_player1_id = 1 ;
    $flag_player2_id = 1 ;
    $flag_player3_id = 1 ;
    $flag_player4_id = 1 ;

    $flag_player1_ready_status = 1 ;
    $flag_player2_ready_status = 1 ;
    $flag_player3_ready_status = 1 ;
    $flag_player4_ready_status = 1 ;

    $flag_player1_position = 1 ;
    $flag_player2_position = 1 ;
    $flag_player3_position = 1 ;
    $flag_player4_position = 1 ;

    $flag_position1 = 1 ;
    $flag_position2 = 1 ;
    $flag_position3 = 1 ;
    $flag_position4 = 1 ;

    $sub_query = '' ;
    if ($flag_round_id === 1)
    {
        $sub_query .= ', `round_id`' ;
    }

    $sub_query = '' ;

    $col_temp = [] ;

    foreach ($columns as $x => $x_value)
    {
        $col_temp [$x_value] = NULL ;
        $v_temp = 'flag_' . $x_value ;
        //if (${'flag_' . $x_value} === 1)
        if ($$v_temp === 1)
        {
            $sub_query .= ', `' . $x_value . '`' ;
        }
    }


    $query = '
    SELECT
        `room_id`' . $sub_query . '
    FROM `room_info`
    WHERE
        `room_id` = ?
    ' ;

    $b_room_id = 1001 ;
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $b_room_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $i = 0 ;
    $temp_param = [] ;
    $temp_param [$i] = & $col_temp ['room_id'] ; $i ++ ;
    foreach ($columns as $x => $x_value)
    {
        $v_temp = 'flag_' . $x_value ;
        if ($$v_temp === 1)
        {
            $temp_param [$i] = & $col_temp [$x_value] ; $i ++ ;
        }
    }

    $stmt -> bind_result (...$temp_param) ;

    while ($stmt -> fetch ())
    {
    }

    foreach ($columns as $x => $x_value)
    {
        $v_temp = 'flag_' . $x_value ;
        if ($$v_temp === 1)
        {
            $result [$x_value] = $col_temp [$x_value] ;
        }
    }

    $result ['sub_query'] = $sub_query ;
    $result ['query'] = $query ;
    $result ['sql-info'] = $db -> info ;

    $stmt -> free_result () ;
    $stmt -> close () ;
    $db -> close () ;

    echo json_encode ($result) ;
    exit ;
}

function test_splat_update ()
{
    $result = [] ;
    $result ['status'] = 'testing-splat-update' ;
    $result ['message'] = 'ok' ;

    require_once (__DIR__ . '/../db_link/dbconnect_w_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;

    $i = 0 ;
    $columns = [] ;
    $columns_type = [] ;
    $col_data = [] ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'round_id' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'room_status' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'bingo_size' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'attendance' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'max_attendance' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'private' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'password' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'room_leader' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'player1_id' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'player2_id' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'player3_id' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 'i' ;
    $columns [$i] = 'player4_id' ; $i ++ ;

    $col_data [$i] = 'P' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player1_ready_status' ; $i ++ ;
    $col_data [$i] = 'P' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player2_ready_status' ; $i ++ ;
    $col_data [$i] = 'P' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player3_ready_status' ; $i ++ ;
    $col_data [$i] = 'P' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player4_ready_status' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player1_position' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player2_position' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player3_position' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'player4_position' ; $i ++ ;

    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'position1' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'position2' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'position3' ; $i ++ ;
    $col_data [$i] = '' ;
    $columns_type [$i] = 's' ;
    $columns [$i] = 'position4' ; $i ++ ;

    $flag_round_id       = 0 ;
    $flag_room_status    = 0 ;
    $flag_bingo_size     = 0 ;
    $flag_attendance     = 0 ;
    $flag_max_attendance = 0 ;
    $flag_private        = 0 ;
    $flag_password       = 0 ;
    $flag_room_leader    = 0 ;

    $flag_player1_id = 0 ;
    $flag_player2_id = 0 ;
    $flag_player3_id = 0 ;
    $flag_player4_id = 0 ;

    $flag_player1_ready_status = 1 ;
    $flag_player2_ready_status = 1 ;
    $flag_player3_ready_status = 1 ;
    $flag_player4_ready_status = 1 ;

    $flag_player1_position = 0 ;
    $flag_player2_position = 0 ;
    $flag_player3_position = 0 ;
    $flag_player4_position = 0 ;

    $flag_position1 = 0 ;
    $flag_position2 = 0 ;
    $flag_position3 = 0 ;
    $flag_position4 = 0 ;

    $sub_query = '' ;

    $col_temp = [] ;
    $col_type = [] ;

    $vars_param = [] ;
    $type_param = '' ;
    $i = 0 ;
    foreach ($columns as $x => $x_value)
    {
        $v_temp = 'flag_' . $x_value ;
        if ($$v_temp === 1)
        {
            if ($sub_query === '')
            {
                $sub_query .= '`' . $x_value . '` = ?' ;
            }
            else
            {
                $sub_query .= ', `' . $x_value . '` = ?' ;
            }
            $type_param .= $columns_type [$x] ;
            $vars_param [$i] = & $col_data [$x] ; $i ++ ;
        }
    }

    $type_param .= 'i' ;

    $query = '
    UPDATE `room_info`
    SET ' . $sub_query . '
    WHERE
        `room_id` = ?
    ' ;

    $b_room_id = 1001 ; // b for bind
    $vars_param [$i] = & $b_room_id ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ($type_param, ...$vars_param) ;
    $stmt -> execute () ;

    $matched_rows = 0 ;
    $changed_rows = 0 ;
    sscanf ($db -> info, '%*s%*s%d%*s%d', $matched_rows, $changed_rows) ;

    $result ['sub_query'] = $sub_query ;
    $result ['query'] = $query ;
    $result ['vars_param'] = $vars_param ;
    $result ['sql-info'] = $db -> info ;
    $result ['matched_rows'] = $matched_rows ;
    $result ['changed_rows'] = $changed_rows ;
    $result ['type_matched_rows'] = gettype ($matched_rows) ;
    $result ['type_changed_rows'] = gettype ($changed_rows) ;
    $result ['affected-rows'] = $stmt -> affected_rows ;

    $stmt -> free_result () ;
    $stmt -> close () ;
    $db -> close () ;

    echo json_encode ($result) ;
    exit ;
}

?>
