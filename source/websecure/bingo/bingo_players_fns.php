<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function bingo_players_main ($uri)
{
    if ($uri === '')
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            exit ;
        }
    }

    $path_segment = parse_uri_one_layer ($uri) ;
    $segment1 = $path_segment ;
    
    if ($uri === '')
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_player_status (intval ($segment1)) ;
            exit ;
        }
        if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
        {
            patch_player_status (intval ($segment1)) ;
            exit ;
        }
    }
    
    $path_segment = parse_uri_one_layer ($uri) ;
    $segment2 = $path_segment ;
    
    if ($uri === '')
    {
        if ($segment2 === 'rounds')
        {
            if ($_SERVER ['REQUEST_METHOD'] == 'GET')
            {
                get_history_record_list (intval ($segment1)) ;
            }
        }
    }
}

function get_player_status ($user_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    if ($user_id != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    sql_query_reading_player_status ($user_id, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_player_status ($user_id, & $data)
{
    require_once (__DIR__ . '/../db_link/dbconnect_r_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_3201
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3201" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT 
                `account_id`
                , `room_id`
                , `round_id`
                , `place`
            FROM `player_status`
            WHERE `serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        if ($record_number === 0)
        {
            $data ['status'] = 'failed' ;
            $data ['message'] = 'do not find player id' ;
            return ;
        }

        $stmt -> bind_result (
            $col_account_id
            , $col_room_id
            , $col_round_id
            , $col_place
        ) ;

        while ($stmt -> fetch ())
        {
        }

        $data ['account_id'] = $col_account_id ;
        $data ['room_id'] = $col_room_id ;
        $data ['round_id'] = $col_round_id ;
        $data ['place'] = $col_place ;

        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        $data ['status'] = 'success' ;
        $data ['message'] = 'ok' ;

        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function patch_player_status ($user_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    if ($user_id != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    $temp = file_get_contents ('php://input') ;

    if ($temp === false)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'get data error.' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (empty ($temp))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'data not found.' ;
        echo json_encode ($result) ;
        exit ;
    }

    // 把 php://input 從 string 轉成一個 associative array
    $request_body = json_decode ($temp, true) ;

    sql_query_updating_player_status ($user_id, $request_body, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_player_status ($user_id, $data, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_3202
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3202" . mysqli_connect_error ()) ;
        }

        $ret = updating_player_status_process_field ($db, $user_id, $data, $result) ;
        $db -> close () ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;
        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function updating_player_status_process_field ($db, $user_id, $data, & $result)
{
    if (isset ($data ['place']))
    {
        if ($data ['place'] === 'back_room_from_finished_game')
        {
            $old_place = '' ;
            $ret = query_reading_data_checking_back_room_from_finished_game_by_single_user ($db, $user_id, $old_place, $result) ;
            if ($ret === -1)
            {
                return -1 ;
            }
            $place = $data ['place'] ;
        }
        else
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'invalid data' ;
            return -1 ;
        }
        $ret = update_player_status_column_place ($db, $user_id, 'R', $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }
        return 1 ;
    }
}

function query_reading_data_checking_back_room_from_finished_game_by_single_user ($db, $user_id, & $col_place, $result)
{
    $query = '
        SELECT
            ps.`place`
            , gr.`round_status`
        FROM `player_status` AS ps
        INNER JOIN `game_round` AS gr
            ON ps.`round_id` = gr.`round_id`
        WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $user_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;
    if ($stmt -> num_rows != 1)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not find player id' ;
        return -1 ;
    }

    $stmt -> bind_result ($col_place, $col_round_status) ;
    while ($stmt -> fetch ())
    {
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    if (! ($col_round_status === 'F'))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'deny' ;
        return -1 ;
    }

    if (! (($col_place === 'G') || ($col_place === 'C')))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'deny' ;
        return -1 ;
    }

    return 1 ;
}

function update_player_status_column_place ($db, $user_id, $place, & $result)
{
    $query = '
        UPDATE `player_status`
        SET `place` = ?
        WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('si', $place, $user_id) ;
    $stmt -> execute () ;
    if ($stmt -> affected_rows != 1)
    {
        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record, but affected_rows gets 0, it should be 1, file: bingo_players_fns.php, in func update_player_status_column_place' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $matched_rows = 0 ;
        $changed_rows = 0 ;
        sscanf ($db -> info, '%*s%*s%d%*s%d', $matched_rows, $changed_rows) ;
        if ($matched_rows === 1)
        {
            $result ['status'] = 'abnormal' ;
            $result ['message'] = 'data not change' ;
        }
        else
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find player id' ;
        }
        $stmt -> close () ;
        return -1 ;
    }
    $stmt -> close () ;
    return 0 ;
}

function get_history_record_list ($user_id)
{
    $ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;

    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    if ($user_id != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    sql_query_reading_history_record_list ($user_id, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_history_record_list ($user_id, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_r_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_3203
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3203" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT 
                `round_id`
            FROM `game_round`
            WHERE
                `player1_id` = ?
                OR
                `player2_id` = ?
                OR
                `player3_id` = ?
                OR
                `player4_id` = ?
            ORDER BY `round_id` DESC
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('iiii', $user_id, $user_id, $user_id, $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $result ['record_number'] = $stmt -> num_rows ;
        if ($stmt -> num_rows === 0)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'ok' ;
            return ;
        }

        $stmt -> bind_result (
            $col_round_id
        ) ;

        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $result ['round_id' . $i] = $col_round_id ;
            $i ++ ;
        }

        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;

        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

?>
