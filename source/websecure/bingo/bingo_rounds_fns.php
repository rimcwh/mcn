<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function bingo_rounds_main ($uri)
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

    if (strlen ($uri) <= 0)
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_single_round_detail ($segment1) ;
            exit ;
        }
        if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
        {
            patch_single_round_detail ($segment1) ;
            exit ;
        }
    }
}

function get_single_round_detail ($round_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    sql_query_reading_single_round_detail ($round_id, $sn_from_jwt, $result) ;

    echo json_encode ($result) ;

    exit ;
}

function sql_query_reading_single_round_detail ($round_id, $user_id, & $result)
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
        // #cn_3301
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3301" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT 
                `round_id`
                , `round_status`
                , `bingo_size`
                , `attendance`
                , `max_attendance`
                , `player1_id`
                , `player2_id`
                , `player3_id`
                , `player4_id`
                , `grid1`
                , `grid2`
                , `grid3`
                , `grid4`
                , `used_number`
                , `whose_turn`
                , `winner`
            FROM `game_round`
            WHERE
                `round_id` = ?
                AND (
                    `player1_id` = ?
                    OR
                    `player2_id` = ?
                    OR
                    `player3_id` = ?
                    OR
                    `player4_id` = ?
                )
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('siiii', $round_id, $user_id, $user_id, $user_id, $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        $data ['record_number'] = $stmt -> num_rows ;

        if ($stmt -> num_rows === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find round' ;
            return ;
        }

        $col = array () ;
        $stmt -> bind_result (
            $col ['round_id']
            , $col ['round_status']
            , $col ['bingo_size']
            , $col ['attendance']
            , $col ['max_attendance']
            , $col ['player1_id']
            , $col ['player2_id']
            , $col ['player3_id']
            , $col ['player4_id']
            , $col ['grid1']
            , $col ['grid2']
            , $col ['grid3']
            , $col ['grid4']
            , $col ['used_number']
            , $col ['whose_turn']
            , $col ['winner']
        ) ;

        while ($stmt -> fetch ())
        {
        }

        $result ['round_id'] = $col ['round_id'] ;
        $result ['round_status'] = $col ['round_status'] ;
        $result ['bingo_size'] = $col ['bingo_size'] ;
        $result ['attendance'] = $col ['attendance'] ;
        $result ['max_attendance'] = $col ['max_attendance'] ;
        $result ['player1_id'] = $col ['player1_id'] ;
        $result ['player2_id'] = $col ['player2_id'] ;
        $result ['player3_id'] = $col ['player3_id'] ;
        $result ['player4_id'] = $col ['player4_id'] ;
        $result ['player1_account_id'] = NULL ;
        $result ['player2_account_id'] = NULL ;
        $result ['player3_account_id'] = NULL ;
        $result ['player4_account_id'] = NULL ;
        if ($col ['round_status'] === 'F')
        {
            $result ['grid1'] = $col ['grid1'] ;
            $result ['grid2'] = $col ['grid2'] ;
            $result ['grid3'] = $col ['grid3'] ;
            $result ['grid4'] = $col ['grid4'] ;
            calc_linking_line ($col, $result) ;
            $result ['player1_line'] = $col ['_player1_line'] ;
            if ($col ['max_attendance'] >= 2)
            {
                $result ['player2_line'] = $col ['_player2_line'] ;
            }
            if ($col ['max_attendance'] >= 3)
            {
                $result ['player3_line'] = $col ['_player3_line'] ;
            }
            if ($col ['max_attendance'] === 4)
            {
                $result ['player4_line'] = $col ['_player4_line'] ;
            }
        }
        elseif ($col ['round_status'] === 'P')
        {
            $temp = 'R' ;
            if ($col ['grid1'] === NULL)
            {
                $temp = 'P' ;
            }
            $result ['player1_grid_status'] = $temp ;

            $temp = 'R' ;
            if ($col ['grid2'] === NULL)
            {
                $temp = 'P' ;
            }
            $result ['player2_grid_status'] = $temp ;

            $temp = 'R' ;
            if ($col ['grid3'] === NULL)
            {
                $temp = 'P' ;
            }
            $result ['player3_grid_status'] = $temp ;

            $temp = 'R' ;
            if ($col ['grid4'] === NULL)
            {
                $temp = 'P' ;
            }
            $result ['player4_grid_status'] = $temp ;
        }
        else
        {
            calc_linking_line ($col, $result) ;
            $result ['player1_line'] = $col ['_player1_line'] ;
            if ($col ['max_attendance'] >= 2)
            {
                $result ['player2_line'] = $col ['_player2_line'] ;
            }
            if ($col ['max_attendance'] >= 3)
            {
                $result ['player3_line'] = $col ['_player3_line'] ;
            }
            if ($col ['max_attendance'] === 4)
            {
                $result ['player4_line'] = $col ['_player4_line'] ;
            }
        }
        round_detail_calc_player_number ($col, $user_id) ;
        $result ['self-grid'] = $col ['grid' . $col ['_player_number']] ;
        $result ['used_number'] = $col ['used_number'] ;
        $result ['whose_turn'] = $col ['whose_turn'] ;
        $result ['winner'] = $col ['winner'] ;

        $stmt -> free_result () ;
        $stmt -> close () ;

        $col_player = array () ;
        $col_player ['attendance'] = $col ['attendance'] ;
        $col_player ['p1'] = $col ['player1_id'] ;
        $col_player ['p2'] = $col ['player2_id'] ;
        $col_player ['p3'] = $col ['player3_id'] ;
        $col_player ['p4'] = $col ['player4_id'] ;

        $ret = sql_query_reading_single_round_account_id ($db, $col_player, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }
        $result ['player1_account_id'] = $col_player ['player1_account_id'] ;
        $result ['player2_account_id'] = $col_player ['player2_account_id'] ;
        $result ['player3_account_id'] = $col_player ['player3_account_id'] ;
        $result ['player4_account_id'] = $col_player ['player4_account_id'] ;

        $db -> close () ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;

        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $simply_output = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($simply_output) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function sql_query_reading_single_round_account_id (& $db, & $col, & $result)
{
    $query = '
        SELECT
            `serial_number`
            , `account_id`
            , `place`
        FROM `player_status`
        WHERE
            `serial_number` = ?
            OR
            `serial_number` = ?
            OR
            `serial_number` = ?
            OR
            `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('iiii', $col ['p1'], $col ['p2'], $col ['p3'], $col ['p4']) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        // error
        $stmt -> free_result () ;
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do read record, but num_rows equal zero, file: bingo_rounds_fns.php, in func sql_query_reading_single_round_account_id' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    if ($stmt -> num_rows != $col ['attendance'])
    {
        // error
        $stmt -> free_result () ;
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do read record, but num_rows not equal attendance, file: bingo_rounds_fns.php, in func sql_query_reading_single_round_account_id' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }

    $stmt -> bind_result ($col_serial_number, $col_account_id, $col_place) ;

    $col ['player1_account_id'] = NULL ;
    $col ['player2_account_id'] = NULL ;
    $col ['player3_account_id'] = NULL ;
    $col ['player4_account_id'] = NULL ;
    $col ['player1_place'] = NULL ;
    $col ['player2_place'] = NULL ;
    $col ['player3_place'] = NULL ;
    $col ['player4_place'] = NULL ;

    while ($stmt -> fetch ())
    {
        for ($i = 1 ; $i <= 4 ; $i ++)
        {
            if ($col_serial_number === $col ['p' . $i])
            {
                $col ['player' . $i . '_account_id'] = $col_account_id ;
                $col ['player' . $i . '_place'] = $col_place ;
                break ;
            }
        }
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    return 1 ;
}

function patch_single_round_detail ($round_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

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

    $ret = sql_query_updating_single_round_detail ($round_id, $sn_from_jwt, $request_body, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_single_round_detail ($round_id, $user_id, $data, & $result)
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
        // #cn_3302
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3302" . mysqli_connect_error ()) ;
        }

        $col_load = array () ;
        $ret = updating_single_round_detail_load_round_detail ($db, $user_id, $round_id, $col_load, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        if ($col_load ['round_status'] === 'F')
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'round is finished' ;
            return -1 ;
        }

        round_detail_calc_player_number ($col_load, $user_id) ;

        $ret = updating_single_round_detail_process_field ($db, $user_id, $round_id, $data, $col_load, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;
        return ;

        if (! ($col_load ['room_status'] === 'M'))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'room status not on matching' ;
            return -1 ;
        }

        if (! ($col_load ['round_id'] === null))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'game is ongoing' ;
            return -1 ;
        }

        updating_single_room_detail_calc_player_number ($col_load, $user_id) ;

        $ret = updating_single_room_detail_process_field ($db, $user_id, $room_id, $data, $col_load, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        /*if (isset ($data ['player_ready_status']))
        {
            $value_temp = 'P' ;
            if ($data ['player_ready_status'] === 'R')
            {
                $value_temp = 'R' ; // r for ready
            }
            $ret = stmt_updating_single_room_single_column ($db, $room_id, 's', 'player' . $player_number . '_ready_status', $value_temp) ;

            if ($ret === -1)
            {
                $result ['status'] = 'success' ;
                $result ['message'] = 'no change' ;
                return ;
            }
        }
        if (isset ($data ['privacy']))
        {
            if ($player_number != $col_room_leader)
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'not room leader, denied' ;
                return ;
            }
            $value_temp = 0 ;
            if ($data ['privacy'] === 1)
            {
                $value_temp = 1 ;
            }
            $ret = stmt_updating_single_room_single_column ($db, $room_id, 'i', 'private', $value_temp) ;

            if ($ret === -1)
            {
                $result ['status'] = 'success' ;
                $result ['message'] = 'no change' ;
                return ;
            }
        }
        if (isset ($data ['bingo_size']))
        {
            if ($player_number != $col_room_leader)
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'not room leader, denied' ;
                return ;
            }
            $value_temp = 5 ;
            if ($data ['bingo_size'] === 6)
            {
                $value_temp = 6 ;
            }
            $ret = stmt_updating_single_room_single_column ($db, $room_id, 'i', 'bingo_size', $value_temp) ;

            if ($ret === -1)
            {
                $result ['status'] = 'success' ;
                $result ['message'] = 'no change' ;
                return ;
            }
        }
        if (isset ($data ['max_attendance']))
        {
            if ($player_number != $col_room_leader)
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'not room leader, denied' ;
                return ;
            }
            if ($data ['max_attendance'] < 2)
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'invalid number, denied' ;
                return ;
            }
            if ($data ['max_attendance'] > 4)
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'invalid number, denied' ;
                return ;
            }
            if ($col_attendance > $data ['max_attendance'])
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'attendance number too small, denied' ;
                return ;
            }
            $ret = stmt_updating_single_room_single_column ($db, $room_id, 'i', 'max_attendance', $data ['max_attendance']) ;

            if ($ret === -1)
            {
                $result ['status'] = 'success' ;
                $result ['message'] = 'no change' ;
                return ;
            }
        }*/

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

function updating_single_round_detail_load_round_detail (& $db, $user_id, $round_id, & $col, & $result)
{
    $query = '
        SELECT 
            `round_id`
            , `round_status`
            , `bingo_size`
            , `attendance`
            , `max_attendance`
            , `player1_id`
            , `player2_id`
            , `player3_id`
            , `player4_id`
            , `grid1`
            , `grid2`
            , `grid3`
            , `grid4`
            , `used_number`
            , `whose_turn`
            , `winner`
        FROM `game_round`
        WHERE
            `round_id` = ?
            AND (
                `player1_id` = ?
                OR
                `player2_id` = ?
                OR
                `player3_id` = ?
                OR
                `player4_id` = ?
            )
    ' ;

    $bind_type = '' ;
    $bind_type .= 's' ; // for round_id
    $bind_type .= 'i' ; // for user_id 1
    $bind_type .= 'i' ; // for user_id 2
    $bind_type .= 'i' ; // for user_id 3
    $bind_type .= 'i' ; // for user_id 4

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ($bind_type
        , $round_id
        , $user_id // user_id 1
        , $user_id // user_id 2
        , $user_id // user_id 3
        , $user_id // user_id 4
    ) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $record_number = $stmt -> num_rows ;

    if ($record_number === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'data not find' ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col ['round_id']
        , $col ['round_status']
        , $col ['bingo_size']
        , $col ['attendance']
        , $col ['max_attendance']
        , $col ['player1_id']
        , $col ['player2_id']
        , $col ['player3_id']
        , $col ['player4_id']
        , $col ['grid1']
        , $col ['grid2']
        , $col ['grid3']
        , $col ['grid4']
        , $col ['used_number']
        , $col ['whose_turn']
        , $col ['winner']
    ) ;

    while ($stmt -> fetch ())
    {
    }
    $stmt -> free_result () ;
    $stmt -> close () ;

    return 1 ;
}

function round_detail_calc_player_number (& $col, $user_id)
{
    $num = 0 ;

    if ($user_id === $col ['player1_id'])
    {
        $num = 1 ;
    }
    if ($user_id === $col ['player2_id'])
    {
        $num = 2 ;
    }
    if ($user_id === $col ['player3_id'])
    {
        $num = 3 ;
    }
    if ($user_id === $col ['player4_id'])
    {
        $num = 4 ;
    }
    $col ['_player_number'] = $num ;
}

function updating_single_round_detail_process_field (& $db, $user_id, $round_id, $data, & $col, & $result)
{
    if ($col ['round_status'] === 'P')
    {
        if (isset ($data ['grid']))
        {
            // set grid check if null
            // using _player_number   _player_number
            $ret = updating_single_round_detail_grid ($db, $round_id, $data, $col, $result) ;
            if ($ret === -1)
            {
                return -1 ;
            }
            return 1 ;
        }
    }
    if ($col ['round_status'] === 'O')
    {
        if (isset ($data ['picked_number']))
        {
            $ret = updating_single_round_detail_picked_number ($db, $round_id, $data, $col, $result) ;
            if ($ret === -1)
            {
                return -1 ;
            }
            return 1 ;
        }
    }
    $result ['status'] = 'failed' ;
    $result ['message'] = 'invalid operation' ;
    return -1 ;
}

function updating_single_round_detail_grid (& $db, $round_id, $data, & $col, & $result)
{
    if (! ($col ['grid' . $col ['_player_number']] === NULL))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'grid already existed' ;
        return -1 ;
    }

    $grid_max = $col ['bingo_size'] * $col ['bingo_size'] ;
    $array_temp = array () ;
    // check valid grid
    for ($i = 0 ; $i < $grid_max ; $i ++)
    {
        array_push ($array_temp, 0) ;
    }

    for ($i = 0 ; $i < $grid_max ; $i ++)
    {
        if (
            ($data ['grid'] [$i] < 1)
            ||
            ($data ['grid'] [$i] > $grid_max)
        )
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'invalid grid' ;
            return -1 ;
        }
        $array_temp [$data ['grid'] [$i] - 1] = 1 ;
    }

    for ($i = 0 ; $i < $grid_max ; $i ++)
    {
        if ($array_temp [$i] === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'invalid grid' ;
            return -1 ;
        }
    }
    $grid_string = implode (',', $data ['grid']) ;

    $field_name = 'grid' . $col ['_player_number'] ;
    $ret = stmt_updating_single_round_single_column ($db, $round_id, 's', $field_name, $grid_string) ;
    if ($ret === -1)
    {
        return -1 ;
    }

    $col [$field_name] = $grid_string ;
    $temp = check_all_player_grid_ready ($col) ;
    if ($temp === 1)
    {
        // 開局
        $ret = init_starting_round ($db, $round_id, $col, $result) ;
        if ($ret === -1)
        {
            // do recover
            stmt_updating_single_round_single_column ($db, $round_id, 's', $field_name, NULL) ;
            return -1 ;
        }
    }
    return 1 ;
}

function check_all_player_grid_ready ($col)
{
    $count = 0 ;
    for ($i = 1 ; $i <= $col ['attendance'] ; $i ++)
    {
        if (! ($col ['grid' . $i] === NULL))
        {
            $count ++ ;
        }
    }
    if ($count === $col ['attendance'])
    {
        return 1 ;
    }
    return -1 ;
}

function init_starting_round (& $db, $round_id, $col, & $result)
{
    $round_status_value = 'O' ;
    $whose_turn_value = 1 ;

    $query = '
        UPDATE `game_round`
        SET
            `round_status` = ?
            , `whose_turn` = ?
        WHERE `round_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('sis', $round_status_value, $whose_turn_value, $round_id) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows != 1)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do init starting round, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func init_starting_round' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    return 1 ;
}

function updating_single_round_detail_picked_number (& $db, $round_id, $data, & $col, & $result)
{
    if (! ($col ['whose_turn'] === $col ['_player_number']))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'deny, not your turn' ;
        return -1 ;
    }
    $grid_max = $col ['bingo_size'] * $col ['bingo_size'] ;
    if (
        ($data ['picked_number'] < 1)
        ||
        ($data ['picked_number'] > $grid_max)
    )
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'invalid number' ;
        return -1 ;
    }
    $old_used_number = $col ['used_number'] ;
    $new_used_number = '' ;
    if (! ($col ['used_number'] === NULL))
    {
        $used_number = explode (',', $col ['used_number']) ;
        for ($i = 0 ; $i < count ($used_number) ; $i ++)
        {
            if (intval ($used_number [$i]) === $data ['picked_number'])
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'duplicated number' ;
                return -1 ;
            }
        }
        $new_used_number = $col ['used_number'] . ',' ;
    }
    $new_used_number .= $data ['picked_number'] ;
    $col ['used_number'] = $new_used_number ;

    $old_round_status = $col ['round_status'] ;

    $old_whose_turn = $col ['whose_turn'] ;

    $col ['whose_turn'] ++ ;
    //$result ['m_m'] = $col ['whose_turn'] ;
    if ($col ['whose_turn'] > $col ['max_attendance'])
    {
        $col ['whose_turn'] = 1 ;
    }

    $old_winner = $col ['winner'] ;

    calc_linking_line ($col, $result) ;

    // check circle number, have finish game ?
    // change used_number ; whose_turn ; round_status

    $ret = query_updating_single_round_picked_number ($db, $round_id, $col ['round_status'], $col ['used_number'], $col ['whose_turn'], $col ['winner'], $result) ;
    if ($ret === -1)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update game_round column round_status, used_number and whose_turn, but affected_rows gets 0, it should be 1, file: bingo_rounds_fns.php, in func updating_single_round_detail_picked_number' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }

    if ($col ['round_status'] === 'F')
    {
        $ret = query_updating_room_status_by_round_id ($db, $round_id, 'M', $result) ;
        if ($ret === -1)
        {
            query_updating_single_round_picked_number ($db, $round_id, $old_round_status, $old_used_number, $old_whose_turn, $old_winner, $result) ;
            return -1 ;
        }
    }

    return 1 ;
}

function calc_linking_line (& $col, & $result)
{
    $max_count = 0 ;
    //$result ['cll'] = '' ;
    if ($col ['max_attendance'] === 4)
    {
        //$result ['cll'] .= 'D: ' ;
        $count = calc_linking_line_single_grid ($col ['bingo_size'], $col ['grid4'], $col ['used_number'], $result) ;
        $col ['_player4_line'] = $count ;
        //$result ['cll'] .= ' . ' ;
        if ($count > $max_count)
        {
            $max_count = $count ;
        }
    }
    if ($col ['max_attendance'] >= 3)
    {
        //$result ['cll'] .= 'C: ' ;
        $count = calc_linking_line_single_grid ($col ['bingo_size'], $col ['grid3'], $col ['used_number'], $result) ;
        $col ['_player3_line'] = $count ;
        //$result ['cll'] .= ' . ' ;
        if ($count > $max_count)
        {
            $max_count = $count ;
        }
    }
    if ($col ['max_attendance'] >= 2)
    {
        //$result ['cll'] .= 'B: ' ;
        $count = calc_linking_line_single_grid ($col ['bingo_size'], $col ['grid2'], $col ['used_number'], $result) ;
        $col ['_player2_line'] = $count ;
        //$result ['cll'] .= ' . ' ;
        if ($count > $max_count)
        {
            $max_count = $count ;
        }
    }
    //$result ['cll'] .= 'A: ' ;
    $count = calc_linking_line_single_grid ($col ['bingo_size'], $col ['grid1'], $col ['used_number'], $result) ;
    //$result ['cll'] .= ' . ' ;
    $col ['_player1_line'] = $count ;
    if ($count > $max_count)
    {
        $max_count = $count ;
    }

    if ($max_count >= $col ['bingo_size'])
    {
        $col ['round_status'] = 'F' ;
        $temp = '' ;
        for ($i = 1 ; $i <= $col ['max_attendance'] ; $i ++)
        {
            if ($col ['_player' . $i . '_line'] === $max_count)
            {
                $temp .= $i ;
            }
        }
        $col ['winner'] = $temp ;
    }
}

function calc_linking_line_single_grid ($size, $grid_string, $used_number, & $result)
{
    if ($used_number === NULL)
    {
        return 0 ;
    }

    $used = explode (',', $used_number) ;

    $grid = explode (',', $grid_string) ;

    $grid_max = $size * $size ;

    $flag = array () ;

    for ($i = 0 ; $i < $grid_max ; $i ++)
    {
        array_push ($flag, 0) ;
    }

    for ($i = 0 ; $i < count ($used) ; $i ++)
    {
        for ($j = 0 ; $j < $grid_max ; $j ++)
        {
            if ($used [$i] === $grid [$j])
            {
                $flag [$j] = 1 ;
                break ;
            }
        }
    }

    $count = 0 ;
    for ($i = 0 ; $i < $size ; $i ++)
    {
        $temp = 1 ;
        for ($j = 0 ; $j < $size ; $j ++)
        {
            if ($flag [$i * $size + $j] === 0)
            {
                $temp = 0 ;
                break ;
            }
        }
        $count += $temp ;
        //$result ['cll'] .= 'r' . $i . '_' . $temp . ' ' ;

        $temp = 1 ;
        for ($j = 0 ; $j < $size ; $j ++)
        {
            if ($flag [$j * $size + $i] === 0)
            {
                $temp = 0 ;
                break ;
            }
        }
        $count += $temp ;
        //$result ['cll'] .= 'c' . $i . '_' . $temp . ' ' ;
    }

    $temp = 1 ;
    for ($i = 0 ; $i < $size ; $i ++)
    {
        if ($flag [$i * $size + $i] === 0)
        {
            $temp = 0 ;
            break ;
        }
    }
    $count += $temp ;
    //$result ['cll'] .= 'x0_' . $temp . ' ' ;

    $temp = 1 ;
    for ($i = 0 ; $i < $size ; $i ++)
    {
        if ($flag [($i + 1) * $size - 1 - $i] === 0)
        {
            $temp = 0 ;
            break ;
        }
    }
    $count += $temp ;
    //$result ['cll'] .= 'x1_' . $temp . ' ' ;

    return $count ;
}

function query_updating_single_round_picked_number ($db, $round_id, $round_status, $used_number, $whose_turn, $winner, $result)
{
    // 這個 function 的 recovery 只需要換參數，不用另外開 function
    $query = '
        UPDATE `game_round`
        SET
            `round_status` = ?
            , `used_number` = ?
            , `whose_turn` = ?
            , `winner` = ?
        WHERE
            `round_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('ssiss', $round_status, $used_number, $whose_turn, $winner, $round_id) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows != 1)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update game_round picked number, but affected_rows not equal 1, it should be equal 1, file: bingo_rounds_fns.php, in func query_updating_single_round_picked_number' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    $stmt -> close () ;
    return 1 ;
}

function stmt_updating_player_status_check_result ($db, $col, $result)
{
    $query = '
        UPDATE `player_status`
        SET
            `check_result` = 1
        WHERE
            `serial_number` = ?
            OR
            `serial_number` = ?
            OR
            `serial_number` = ?
            OR
            `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('iiii', $col ['player1_id'], $col ['player2_id'], $col ['player3_id'], $col ['player4_id']) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update player_status column check_result, but affected_rows gets 0, it should be greater than 1, file: bingo_rounds_fns.php, in func stmt_updating_player_status_check_result' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    if ($stmt -> affected_rows != $col ['attendance'])
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update player_status column check_result, but affected_rows not equal attendance number, it should be equal attendance number, file: bingo_rounds_fns.php, in func stmt_updating_player_status_check_result' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    $stmt -> close () ;
    return 0 ;
}

function query_updating_room_status_by_round_id (& $db, $round_id, $round_status, & $result)
{
    $query = '
    UPDATE `room_info`
    SET `room_status` = ?
    WHERE `round_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('ss', $round_status, $round_id) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update room_info column room_status, but affected_rows gets 0, it should be equal 1, file: bingo_rounds_fns.php, in func query_updating_room_status_by_round_id' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    $stmt -> close () ;
    return 1 ;
}

function stmt_updating_single_round_single_column (& $db, $round_id, $data_type, $field_name, $data)
{
    $query = '
        UPDATE `game_round`
            SET ' . $field_name . ' = ?
        WHERE
            `round_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ($data_type . 's', $data, $round_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> affected_rows != 1)
    {
        return -1 ;
    }
    return 0 ;
}

?>
