<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function bingo_rooms_main ($uri)
{
    if ($uri === '')
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_rooms_list () ;
            exit ;
        }
        if ($_SERVER ['REQUEST_METHOD'] == 'POST')
        {
            post_new_room () ;
            exit ;
        }
    }

    $path_segment = parse_uri_one_layer ($uri) ;
    $segment1 = $path_segment ;

    if (strlen ($uri) <= 0)
    {
        if (0 == strcmp ('quickly-join-room', $segment1))
        {
            get_quickly_join_room () ;
            exit ;
        }
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_single_room_detail (intval ($segment1)) ;
            exit ;
        }
        if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
        {
            patch_single_room_detail (intval ($segment1)) ;
            exit ;
        }
    }

    $path_segment = parse_uri_one_layer ($uri) ;
    $segment2 = $path_segment ;
    if (0 == strcmp ('participants', $segment2))
    {
        if ($uri === '')
        {
            if ($_SERVER ['REQUEST_METHOD'] == 'POST')
            {
                post_participant (intval ($segment1)) ;
                exit ;
            }
            if ($_SERVER ['REQUEST_METHOD'] == 'DELETE')
            {
                delete_participant (intval ($segment1)) ;
            }
        }
    }
}

function get_rooms_list ()
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    sql_query_reading_rooms_list ($result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_rooms_list (& $data)
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
        // #cn_3101
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3101" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT 
                `room_id`
                , `round_id`
                , `room_status`
                , `bingo_size`
                , `attendance`
                , `max_attendance`
                , `private`
                , `password`
            FROM `room_info`
        ' ;

        $stmt = $db -> prepare ($query) ;
        //$stmt -> bind_param ('i', $limit) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        $data ['record_number'] = $stmt -> num_rows ;

        $stmt -> bind_result (
            $col_room_id
            , $col_round_id
            , $col_room_status
            , $col_bingo_size
            , $col_attendance
            , $col_max_attendance
            , $col_private
            , $col_password
        ) ;

        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $data ['room_id' . $i] = $col_room_id ;
            $data ['round_id' . $i] = $col_round_id ;
            $data ['room_status' . $i] = $col_room_status ;
            $data ['bingo_size' . $i] = $col_bingo_size ;
            $data ['attendance' . $i] = $col_attendance ;
            $data ['max_attendance' . $i] = $col_max_attendance ;
            $data ['private' . $i] = $col_private ;
            $data ['password' . $i] = $col_password ;

            $i ++ ;
        }

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

function get_single_room_detail ($room_id)
{
    $ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;

    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    sql_query_reading_single_room_detail ($room_id, $sn_from_jwt, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_single_room_detail ($room_id, $user_id, & $result)
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
        // #cn_3103
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3103" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT 
                ps.`serial_number`
                , ps.`account_id`
                , ps.`room_id`
                , ri.`round_id`
                , ri.`room_status`
                , ri.`bingo_size`
                , ri.`attendance`
                , ri.`max_attendance`
                , ri.`private`
                , ri.`password`
                , ri.`room_leader`
                , ri.`player1_id`
                , ri.`player2_id`
                , ri.`player3_id`
                , ri.`player4_id`
                , ri.`player1_ready_status`
                , ri.`player2_ready_status`
                , ri.`player3_ready_status`
                , ri.`player4_ready_status`
                , ri.`player1_position`
                , ri.`player2_position`
                , ri.`player3_position`
                , ri.`player4_position`
                , ri.`position1`
                , ri.`position2`
                , ri.`position3`
                , ri.`position4`
            FROM `player_status` AS ps
            INNER JOIN `room_info` AS ri
                ON ps.`room_id` = ri.`room_id`
            WHERE
                ps.`serial_number` = ?
                AND ps.`room_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $user_id, $room_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        $result ['record_number'] = $stmt -> num_rows ;

        if ($record_number === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find player id' ;
            return ;
        }

        $stmt -> bind_result (
            $col_user_id
            , $col_self_account_id
            , $col_room_id
            , $col_round_id
            , $col_room_status
            , $col_bingo_size
            , $col_attendance
            , $col_max_attendance
            , $col_private
            , $col_password
            , $col_room_leader
            , $col_player1_id
            , $col_player2_id
            , $col_player3_id
            , $col_player4_id
            , $col_player1_ready_status
            , $col_player2_ready_status
            , $col_player3_ready_status
            , $col_player4_ready_status
            , $col_player1_position
            , $col_player2_position
            , $col_player3_position
            , $col_player4_position
            , $col_position1
            , $col_position2
            , $col_position3
            , $col_position4
        ) ;

        while ($stmt -> fetch ())
        {
        }

        $result ['self_account_id'] = $col_self_account_id ;
        $result ['room_id'] = $col_room_id ;
        $result ['round_id'] = $col_round_id ;
        $result ['room_status'] = $col_room_status ;
        $result ['bingo_size'] = $col_bingo_size ;
        $result ['attendance'] = $col_attendance ;
        $result ['max_attendance'] = $col_max_attendance ;
        $result ['private'] = $col_private ;
        //$result ['password'] = $col_password ;
        $result ['room_leader'] = $col_room_leader ;
        $result ['player1_id'] = $col_player1_id ;
        $result ['player2_id'] = $col_player2_id ;
        $result ['player3_id'] = $col_player3_id ;
        $result ['player4_id'] = $col_player4_id ;
        $result ['player1_ready_status'] = $col_player1_ready_status ;
        $result ['player2_ready_status'] = $col_player2_ready_status ;
        $result ['player3_ready_status'] = $col_player3_ready_status ;
        $result ['player4_ready_status'] = $col_player4_ready_status ;
        $result ['player1_position'] = $col_player1_position ;
        $result ['player2_position'] = $col_player2_position ;
        $result ['player3_position'] = $col_player3_position ;
        $result ['player4_position'] = $col_player4_position ;
        $result ['position1'] = $col_position1 ;
        $result ['position2'] = $col_position2 ;
        $result ['position3'] = $col_position3 ;
        $result ['position4'] = $col_position4 ;

        $stmt -> free_result () ;
        $stmt -> close () ;

        $col_players_info = array () ;
        $col_players_info ['attendance'] = $col_attendance ;
        $col_players_info ['player1_id'] = $col_player1_id ;
        $col_players_info ['player2_id'] = $col_player2_id ;
        $col_players_info ['player3_id'] = $col_player3_id ;
        $col_players_info ['player4_id'] = $col_player4_id ;
        $col_players_info ['player1_account_id'] = NULL ;
        $col_players_info ['player2_account_id'] = NULL ;
        $col_players_info ['player3_account_id'] = NULL ;
        $col_players_info ['player4_account_id'] = NULL ;
        $col_players_info ['player1_place'] = NULL ;
        $col_players_info ['player2_place'] = NULL ;
        $col_players_info ['player3_place'] = NULL ;
        $col_players_info ['player4_place'] = NULL ;
        $ret = query_reading_players_info_which_in_same_room ($db, $col_players_info, $result) ;
        if ($ret === -1)
        {
            return ;
        }

        $db -> close () ;

        $result ['player1_account_id'] = $col_players_info ['player1_account_id'] ;
        $result ['player2_account_id'] = $col_players_info ['player2_account_id'] ;
        $result ['player3_account_id'] = $col_players_info ['player3_account_id'] ;
        $result ['player4_account_id'] = $col_players_info ['player4_account_id'] ;

        $result ['player1_place'] = $col_players_info ['player1_place'] ;
        $result ['player2_place'] = $col_players_info ['player2_place'] ;
        $result ['player3_place'] = $col_players_info ['player3_place'] ;
        $result ['player4_place'] = $col_players_info ['player4_place'] ;

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

function post_new_room ()
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = 'test' ;
    $result ['message'] = 'here is post new room' ;

    //$sn_from_jwt = $ret ['jwt_decode'] -> sn ;

    sql_query_creating_room ($sn_from_jwt, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_creating_room ($user_id, & $result)
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
        // #cn_3106
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3106" . mysqli_connect_error ()) ;
        }

        $ret = query_creating_room_checking_player_status ($db, $user_id, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $created_room_id = 1 ;
        // determine room_id which to be created
        creating_room_generating_room_id ($db, $created_room_id, $result) ;

        $ret = query_creating_room ($db, $user_id, $created_room_id, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $ret = query_creating_room_updating_player_status ($db, $user_id, $created_room_id, $result) ;
        if ($ret === -1)
        {
            // do recovery
            query_creating_room_recovery ($db, $created_room_id, $result) ;
            return -1 ;
        }

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

function query_creating_room_checking_player_status (& $db, $user_id, & $result)
{
    $query = '
    SELECT `place`
    FROM `player_status`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $user_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not find player id' ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_place
    ) ;

    while ($stmt -> fetch ())
    {
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    if (! ($col_place === 'N'))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'deny' ;
        return -1 ;
    }

    return 1 ;
}

function creating_room_generating_room_id (& $db, & $created_room_id, & $result)
{
    $col = [] ;
    query_creating_room_getting_room_list ($db, $col, $result) ;

    /*for ($i = 0 ; $i < $col ['record_number'] ; $i ++)
    {
        $result ['id' . $i] = $col ['id' . $i] ;
    }*/

    $created_room_id = 1 ;

    $i = 0 ;
    while ($i < $col ['record_number'])
    {
        if ($created_room_id === $col ['id' . $i])
        {
            $created_room_id ++ ;
            $i ++ ;
        }
        else
        {
            break ;
        }
    }
}

function query_creating_room_getting_room_list (& $db, & $col, & $result)
{
    $query = '
    SELECT `room_id`
    FROM `room_info`
    ORDER BY `room_id` ASC
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $col ['record_number'] = $stmt -> num_rows ;

    if ($stmt -> num_rows === 0)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not find player id' ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_room_id
    ) ;

    $i = 0 ;
    while ($stmt -> fetch ())
    {
        $col ['id' . $i] = $col_room_id ;
        $i ++ ;
    }

    $stmt -> free_result () ;
    $stmt -> close () ;
}

function query_creating_room (& $db, $user_id, $created_room_id, & $result)
{
    $query = '
    INSERT INTO `room_info`
    (
        `room_id`
        , `round_id`
        , `room_status`
        , `bingo_size`
        , `attendance`
        , `max_attendance`
        , `private`
        , `room_leader`
        , `player1_id`
        , `player1_position`
        , `position1`
    )
    VALUES
    (
    ' ;
    $temp = '' ;
    $temp .= '?' ; // room_id
    $temp .= ', ?' ; // round_id
    $temp .= ', ?' ; // room_status
    $temp .= ', ?' ; // bingo_size
    $temp .= ', ?' ; // attendance
    $temp .= ', ?' ; // max_attendance
    $temp .= ', ?' ; // private
    $temp .= ', ?' ; // room_leader
    $temp .= ', ?' ; // player1_id
    $temp .= ', ?' ; // player1_position
    $temp .= ', ?' ; // position1

    $query .= $temp . ')' ;

    $col = array () ;
    $col ['room_id'] = $created_room_id ;
    $col ['round_id'] = NULL ;
    $col ['room_status'] = 'M' ;
    $col ['bingo_size'] = 5 ;
    $col ['attendance'] = 1 ;
    $col ['max_attendance'] = 4 ;
    $col ['private'] = 0 ;
    $col ['room_leader'] = '1' ;
    $col ['player1_id'] = $user_id ;
    $col ['player1_position'] = '1' ;
    $col ['position1'] = '1' ;

    $stmt = $db -> prepare ($query) ;

    $temp = '' ;
    $temp .= 'i' ; // int room_id
    $temp .= 's' ; // char round_id
    $temp .= 's' ; // char room_status
    $temp .= 'i' ; // int bingo_size
    $temp .= 'i' ; // int attendance
    $temp .= 'i' ; // int max_attendance
    $temp .= 'i' ; // int private
    $temp .= 's' ; // char room_leader
    $temp .= 'i' ; // int player1_id
    $temp .= 's' ; // char player1_position
    $temp .= 's' ; // char position1

    $stmt -> bind_param ($temp
        , $col ['room_id']
        , $col ['round_id']
        , $col ['room_status']
        , $col ['bingo_size']
        , $col ['attendance']
        , $col ['max_attendance']
        , $col ['private']
        , $col ['room_leader']
        , $col ['player1_id']
        , $col ['player1_position']
        , $col ['position1']
    ) ;

    // attendance = 1
    // player1_id = user_id
    // player1_position = 1
    // position1 = 1
    // private = 0 ref: update room field
    // 
    // round_id = null
    // room_status = M
    // bingo_size = 5
    // room_leader = '1'
    // 

    $stmt -> execute () ;

    if ($stmt -> affected_rows > 0)
    {
        $stmt -> close () ;
        $result ['status'] = 'success' ;
        $result ['message'] = 'created ok' ;
    }
    else
    {
        $stmt -> close () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'created failed' ;
        return -1 ;
    }
}

function query_creating_room_recovery (& $db, $created_room_id, & $result)
{
    $query = '
    DELETE FROM `room_info` WHERE `room_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $created_room_id) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create record recovery, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_creating_new_round_recovery' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }

    $stmt -> close () ;

    return 1 ;
}

function query_creating_room_updating_player_status (& $db, $user_id, $room_id, & $result)
{
    $query = '
    UPDATE `player_status`
    SET
        `place` = \'R\'
        , `room_id` = ?
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('ii'
        , $room_id
        , $user_id
    ) ;
    $stmt -> execute () ;
    if ($stmt -> affected_rows <= 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_deleting_room_participant_part_C' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    $stmt -> close () ;
    return 1 ;
}

function get_quickly_join_room ()
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    sql_query_updating_quickly_join_room ($sn_from_jwt, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_quickly_join_room ($user_id, & $result)
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
        // #cn_3107
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3107" . mysqli_connect_error ()) ;
        }

        $join_room_id = 0 ;
        $ret = query_updating_quickly_join_room_reading_room ($db, $join_room_id, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $result ['join_id'] = $join_room_id ;

        $db -> close () ;

        $ret = sql_query_updating_room_participant ($join_room_id, '', $user_id, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;

        return 1 ;
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

function query_updating_quickly_join_room_reading_room (& $db, & $join_room_id, & $result)
{
    $query = '
    SELECT `room_id`
    FROM `room_info`
    WHERE
        `room_status` = \'M\'
        AND
        `private` = 0
        AND
        `attendance` < `max_attendance`
    LIMIT 1
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not find player id' ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_room_id
    ) ;

    while ($stmt -> fetch ())
    {
    }

    $join_room_id = $col_room_id ;

    $stmt -> free_result () ;
    $stmt -> close () ;
}

function patch_single_room_detail ($room_id)
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

    sql_query_updating_single_room_detail ($room_id, $sn_from_jwt, $request_body, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_single_room_detail ($room_id, $user_id, $data, & $result)
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
        // #cn_3104
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3104" . mysqli_connect_error ()) ;
        }

        $col_load = array () ;
        $ret = updating_single_room_detail_load_room_detail ($db, $user_id, $room_id, $col_load, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        if (! ($col_load ['room_status'] === 'M'))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'room status not on matching' ;
            return -1 ;
        }

        /*if (! ($col_load ['round_id'] === null))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'game is ongoing' ;
            return -1 ;
        }*/
        // 應該換成查看 player_status 的 place 是不是在 room 裡面

        $col_load ['previous_round_id'] = $col_load ['round_id'] ;
        $col_load ['previous_player1_ready_status'] = $col_load ['player1_ready_status'] ;
        $col_load ['previous_player2_ready_status'] = $col_load ['player2_ready_status'] ;
        $col_load ['previous_player3_ready_status'] = $col_load ['player3_ready_status'] ;
        $col_load ['previous_player4_ready_status'] = $col_load ['player4_ready_status'] ;

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

function updating_single_room_detail_load_room_detail (& $db, $user_id, $room_id, & $col, & $result)
{
    $query = '
        SELECT 
            `room_id`
            , `round_id`
            , `room_status`
            , `bingo_size`
            , `attendance`
            , `max_attendance`
            , `private`
            , `password`
            , `room_leader`
            , `player1_id`
            , `player2_id`
            , `player3_id`
            , `player4_id`
            , `player1_ready_status`
            , `player2_ready_status`
            , `player3_ready_status`
            , `player4_ready_status`
            , `player1_position`
            , `player2_position`
            , `player3_position`
            , `player4_position`
            , `position1`
            , `position2`
            , `position3`
            , `position4`
        FROM `room_info`
        WHERE
            `room_id` = ?
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
    $bind_type .= 'i' ; // for room_id
    $bind_type .= 'i' ; // for user_id 1
    $bind_type .= 'i' ; // for user_id 2
    $bind_type .= 'i' ; // for user_id 3
    $bind_type .= 'i' ; // for user_id 4

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ($bind_type
        , $room_id
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
        $col ['room_id']
        , $col ['round_id']
        , $col ['room_status']
        , $col ['bingo_size']
        , $col ['attendance']
        , $col ['max_attendance']
        , $col ['private']
        , $col ['password']
        , $col ['room_leader']
        , $col ['player1_id']
        , $col ['player2_id']
        , $col ['player3_id']
        , $col ['player4_id']
        , $col ['player1_ready_status']
        , $col ['player2_ready_status']
        , $col ['player3_ready_status']
        , $col ['player4_ready_status']
        , $col ['player1_position']
        , $col ['player2_position']
        , $col ['player3_position']
        , $col ['player4_position']
        , $col ['position1']
        , $col ['position2']
        , $col ['position3']
        , $col ['position4']
    ) ;

    while ($stmt -> fetch ())
    {
    }
    $stmt -> free_result () ;
    $stmt -> close () ;

    return 1 ;
}

function updating_single_room_detail_calc_player_number (& $col, $user_id)
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

function updating_single_room_detail_process_field (& $db, $user_id, $room_id, $data, & $col_load, & $result)
{
    if (isset ($data ['player_ready_status']))
    {
        $ret = updating_single_room_detail_player_ready_status ($db, $room_id, $data, $col_load, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }
        return 1 ;
    }
    if (isset ($data ['privacy']))
    {
        if ($col_load ['player' . $col_load ['room_leader'] . '_id'] != $user_id)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'not room leader, denied' ;
            return -1 ;
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
            return -1 ;
        }
    }
    if (isset ($data ['password']))
    {
        if ($col_load ['player' . $col_load ['room_leader'] . '_id'] != $user_id)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'not room leader, denied' ;
            return -1 ;
        }
        if (strlen ($data ['password']) > 20)
        {
            $result ['status'] = 'failed'; 
            $result ['message'] = 'password too long' ;
            return -1 ;
        }

        $ret = stmt_updating_single_room_single_column ($db, $room_id, 's', 'password', $data ['password']) ;

        if ($ret === -1)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            return -1 ;
        }
        return 1 ;
    }
    if (isset ($data ['bingo_size']))
    {
        if ($col_load ['player' . $col_load ['room_leader'] . '_id'] != $user_id)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'not room leader, denied' ;
            return -1 ;
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
            return -1 ;
        }
    }
    if (isset ($data ['max_attendance']))
    {
        if ($col_load ['player' . $col_load ['room_leader'] . '_id'] != $user_id)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'not room leader, denied' ;
            return -1 ;
        }
        if ($data ['max_attendance'] < 2)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'invalid number, denied' ;
            return -1 ;
        }
        if ($data ['max_attendance'] > 4)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'invalid number, denied' ;
            return -1 ;
        }
        if ($col_load ['attendance'] > $data ['max_attendance'])
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'attendance number too small, denied' ;
            return -1 ;
        }
        $ret = stmt_updating_single_room_single_column ($db, $room_id, 'i', 'max_attendance', $data ['max_attendance']) ;

        if ($ret === -1)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            return -1 ;
        }

        $col_load ['max_attendance'] = $data ['max_attendance'] ;
        if ($col_load ['attendance'] === $data ['max_attendance'])
        {
            $ret_temp = check_all_player_ready ($col_load) ;
            $result ['pr_r'] = $ret_temp ;
            if ($ret_temp === 1)
            {
                // create_new_game_with_complete_ready_status
                $ret = create_new_game_with_complete_ready_status ($db, $room_id, $col_load, $result) ;
                if ($ret === -1)
                {
                    return -1 ;
                }
            }
        }
    }
}

function updating_single_room_detail_player_ready_status (& $db, $room_id, $data, & $col, & $result)
{
    $value_temp = 'P' ;
    if ($data ['player_ready_status'] === 'R')
    {
        $value_temp = 'R' ; // r for ready
    }
    $field_name = 'player' . $col ['_player_number'] . '_ready_status' ;
    $ret = stmt_updating_single_room_single_column ($db, $room_id, 's', $field_name, $value_temp) ;

    if ($ret === -1)
    {
        $result ['status'] = 'success' ;
        $result ['message'] = 'no change' ;
        return -1 ;
    }

    $col [$field_name] = $data ['player_ready_status'] ;

    if ($data ['player_ready_status'] === 'R')
    {
        if ($col ['attendance'] === $col ['max_attendance'])
        {
            $ret_temp = check_all_player_ready ($col) ;
            if ($ret_temp === 1)
            {
                // create_new_game_with_complete_ready_status
                $ret = create_new_game_with_complete_ready_status ($db, $room_id, $col, $result) ;
                if ($ret === -1)
                {
                    return -1 ;
                }
            }
        }
    }
    return 1 ;
}

function check_all_player_ready ($col)
{
    for ($i = 1 ; $i <= $col ['max_attendance'] ; $i ++)
    {
        if (! ($col ['player' . $i . '_ready_status'] === 'R'))
        {
            return -1 ;
        }
    }
    return 1 ;
}

function create_new_game_with_complete_ready_status (& $db, $room_id, $col, & $result)
{
    // check all player not in other round, 檢查反而會有其他麻煩, 應該換成查看 player_status 的 place 是不是在 room 裡面
    /*$ret = check_all_player_round_status ($db, $col, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/

    // generate round_id, with playerx_id
    $id_part1 = date ('YmdHis') ;
    $id_part2 = $col ['player' . $col ['room_leader'] . '_id'] ;
    $id_complete = $id_part1 . '_' . $id_part2 ;
    $result ['_id'] = $id_complete ;

    $col_round = array () ;
    $col_round ['round_id'] = $id_complete ;
    $col_round ['round_status'] = 'P' ; // P for prepare 準備中 (安排自己的格子)
    $col_round ['bingo_size'] = $col ['bingo_size'] ;
    $col_round ['attendance'] = $col ['attendance'] ;
    $col_round ['max_attendance'] = $col ['max_attendance'] ;
    $col_round ['player1_id'] = $col ['player1_id'] ;
    $col_round ['player2_id'] = $col ['player2_id'] ;
    $col_round ['player3_id'] = $col ['player3_id'] ;
    $col_round ['player4_id'] = $col ['player4_id'] ;

    // create new round
    $ret = sql_query_creating_new_round ($db, $col_round, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }

    // change room_info table room_status and round_id
    $ret = sql_query_updating_room_info_new_round ($db, $room_id, $id_complete, $col ['attendance'], $result) ;
    if ($ret === -1)
    {
        sql_query_creating_new_round_recovery ($db, $col_round, $result) ;
        return -1 ;
    }

    // change player_status table round_id and place, 因為是最後一步，所以不需要 recovery
    $ret = sql_query_updating_player_status_new_round ($db, $col_round, $result) ;
    if ($ret === -1)
    {
        // 因為 round_id 有外鍵 foreign key，要先把 room_info 的拔掉，才能 delete QQ
        sql_query_updating_room_info_new_round_recovery ($db, $room_id, $col, $result) ;
        sql_query_creating_new_round_recovery ($db, $col_round, $result) ;
        return -1 ;
    }

    // all ok
    return 1 ;
}

function check_all_player_round_status (& $db, $col, & $result)
{
    $query = '
        SELECT 
            `account_id`
            , `round_id`
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
    $stmt -> bind_param ('iiii', $col ['player1_id'], $col ['player2_id'], $col ['player3_id'], $col ['player4_id']) ;
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
        $col_account_id
        , $col_round_id
    ) ;

    while ($stmt -> fetch ())
    {
        if (! ($col_round_id === NULL))
        {
            $stmt -> free_result () ;
            $stmt -> close () ;
            $result ['status'] = 'failed' ;
            $result ['message'] = 'some member in other round' ;
            return -1 ;
        }
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
}

function sql_query_creating_new_round (& $db, $col, & $result)
{
    $query = '
        INSERT INTO `game_round` (
            `round_id`
            , `round_status`
            , `bingo_size`
            , `attendance`
            , `max_attendance`
            , `player1_id`
            , `player2_id`
            , `player3_id`
            , `player4_id`
        )
        VALUES (
    ' ;
    $query .= '?' ; // round_id
    $query .= ', ?' ; // round_status
    $query .= ', ?' ; // bingo_size
    $query .= ', ?' ; // attendance
    $query .= ', ?' ; // max_attendance
    $query .= ', ?' ; // player1_id
    $query .= ', ?' ; // player2_id
    $query .= ', ?' ; // player3_id
    $query .= ', ?' ; // player4_id
    $query .= ')' ; // end values

    $bind_type = '' ;
    $bind_type .= 's' ; // for round_id
    $bind_type .= 's' ; // for round_status
    $bind_type .= 'i' ; // for bingo_size
    $bind_type .= 'i' ; // for attendance
    $bind_type .= 'i' ; // for max_attendance
    $bind_type .= 'i' ; // for player1_id
    $bind_type .= 'i' ; // for player2_id
    $bind_type .= 'i' ; // for player3_id
    $bind_type .= 'i' ; // for player4_id

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ($bind_type
        , $col ['round_id']
        , $col ['round_status']
        , $col ['bingo_size']
        , $col ['attendance']
        , $col ['max_attendance']
        , $col ['player1_id']
        , $col ['player2_id']
        , $col ['player3_id']
        , $col ['player4_id']
    ) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create record, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_creating_new_round' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }

    return 1 ;
}

function sql_query_creating_new_round_recovery (& $db, $col, & $result)
{
    $query = '
        DELETE FROM `game_round` WHERE `round_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('s', $col ['round_id']) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create record recovery, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_creating_new_round_recovery' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }

    $stmt -> close () ;

    return 1 ;
}

function sql_query_updating_room_info_new_round (& $db, $room_id, $round_id, $attendance, & $result)
{
    $room_status = 'O' ; // O for ongoing

    $sub_query = ', `player1_ready_status` = \'P\'' ;
    if ($attendance >= 2)
    {
        $sub_query .= ', `player2_ready_status` = \'P\'' ;
    }
    if ($attendance >= 3)
    {
        $sub_query .= ', `player3_ready_status` = \'P\'' ;
    }
    if ($attendance === 4)
    {
        $sub_query .= ', `player4_ready_status` = \'P\'' ;
    }

    $query = '
        UPDATE `room_info`
        SET
            `round_id` = ?
            , `room_status` = ?
            ' . $sub_query . '
        WHERE
            `room_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;

    $stmt -> bind_param ('ssi', $round_id, $room_status, $room_id) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_updating_room_info_new_round' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }

    $stmt -> close () ;
    
    return 1 ;
}

function sql_query_updating_room_info_new_round_recovery (& $db, $room_id, $col, & $result)
{
    $room_status = 'M' ; // M for matching

    $type_param = 'ss' ;
    $vars_param = [] ;

    $i = 0 ;
    $vars_param [$i] = & $col ['previous_round_id'] ; $i ++ ;
    $vars_param [$i] = & $room_status ; $i ++ ;

    $type_param .= 's' ;
    $sub_query = ', `player1_ready_status` = ?' ;
    $vars_param [$i] = & $col ['previous_player1_ready_status'] ; $i ++ ;
    if ($col ['attendance'] >= 2)
    {
        $type_param .= 's' ;
        $sub_query .= ', `player2_ready_status` = ?' ;
        $vars_param [$i] = & $col ['previous_player2_ready_status'] ; $i ++ ;
    }
    if ($col ['attendance'] >= 3)
    {
        $type_param .= 's' ;
        $sub_query .= ', `player3_ready_status` = ?' ;
        $vars_param [$i] = & $col ['previous_player3_ready_status'] ; $i ++ ;
    }
    if ($col ['attendance'] === 4)
    {
        $type_param .= 's' ;
        $sub_query .= ', `player4_ready_status` = ?' ;
        $vars_param [$i] = & $col ['previous_player4_ready_status'] ; $i ++ ;
    }

    $vars_param [$i] = & $room_id ; $i ++ ;

    $type_param .= 'i' ;

    $query = '
        UPDATE `room_info`
        SET
            `round_id` = ?
            , `room_status` = ?' . $sub_query . '
        WHERE
            `room_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;

    //$stmt -> bind_param ($type_param, $round_id, $room_status, $room_id) ;
    $stmt -> bind_param ($type_param, ...$vars_param) ;
    $stmt -> execute () ;

    $result ['__i_test'] = $db -> info ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record recovery, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_updating_room_info_new_round_recovery' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }

    $stmt -> close () ;
    
    return 1 ;
}

function sql_query_updating_player_status_new_round (& $db, $col, & $result)
{
    $place = 'G' ;
    $query = '
        UPDATE `player_status`
        SET
            `round_id` = ?
            , `place` = ?
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

    $stmt -> bind_param ('ssiiii'
        , $col ['round_id']
        , $place
        , $col ['player1_id']
        , $col ['player2_id']
        , $col ['player3_id']
        , $col ['player4_id']
    ) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record, but affected_rows gets 0, it should greater than 0, file: bingo_rooms_fns.php, in func sql_query_updating_player_status_new_round' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }
    if ($stmt -> affected_rows != $col ['attendance'])
    {
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record, but affected_rows not equal attendance, file: bingo_rooms_fns.php, in func sql_query_updating_player_status_new_round' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }

    $stmt -> close () ;
    
    return 1 ;
}

function updating_single_room_detail_check_permission (& $db, $user_id, $room_id, & $result)
{
    $query = '
        SELECT 
            `room_id`
            , `player1_id`
            , `player2_id`
            , `player3_id`
            , `player4_id`
            , `room_leader`
            , `attendance`
            , `max_attendance`
        FROM `room_info`
        WHERE
            `room_id` = ?
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
    $stmt -> bind_param ('iiiii', $room_id, $user_id, $user_id, $user_id, $user_id) ;
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
        $col_room_id
        , $col_player1_id
        , $col_player2_id
        , $col_player3_id
        , $col_player4_id
        , $col_room_leader
        , $col_attendance
        , $col_max_attendance
    ) ;

    while ($stmt -> fetch ())
    {
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
}

function stmt_updating_single_room_single_column (& $db, $room_id, $data_type, $field_name, $data)
{
    $query = '
        UPDATE `room_info`
            SET ' . $field_name . ' = ?
        WHERE
            `room_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ($data_type . 'i', $data, $room_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> affected_rows != 1)
    {
        return -1 ;
    }
    return 0 ;
}

function query_reading_players_info_which_in_same_room ($db, & $col, & $result)
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
    $stmt -> bind_param ('iiii', $col ['player1_id'], $col ['player2_id'], $col ['player3_id'], $col ['player4_id']) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do read player_status column account_id and place, but affected_rows gets 0, it should be greater than 1, file: bingo_rooms_fns.php, in func query_reading_players_info_which_in_same_room' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }
    if ($stmt -> num_rows != $col ['attendance'])
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do read player_status column account_id and place, but affected_rows not equal attendance, file: bingo_rooms_fns.php, in func query_reading_players_info_which_in_same_room' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_serial_number
        , $col_account_id
        , $col_place
    ) ;

    while ($stmt -> fetch ())
    {
        for ($i = 1 ; $i < 5 ; $i ++)
        {
            if ($col ['player' . $i . '_id'] === $col_serial_number)
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

function post_participant ($room_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $request_body = file_get_contents ('php://input') ;
    if ($request_body === false)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'get data error.' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (empty ($request_body))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'data not found.' ;
        echo json_encode ($result) ;
        exit ;
    }
    $str_request_body = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    $result ['room_id'] = $room_id ;
    $result ['user_id'] = $sn_from_jwt ;
    $result ['pw'] = $str_request_body ['password'] ;
    $result ['type'] = gettype ($sn_from_jwt) ;
    sql_query_updating_room_participant ($room_id, $str_request_body ['password'], $sn_from_jwt, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_room_participant ($room_id, $password, $user_id, & $result)
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
        // #cn_3102
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3102" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT
                `place`
                , `room_id`
                , `round_id`
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
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find player id' ;
            return -1 ;
        }

        $stmt -> bind_result (
            $col_place
            , $col_room_id
            , $col_round_id
        ) ;
        while ($stmt -> fetch ())
        {
        }

        if (! ($col_place === 'N'))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'deny' ;
            return -1 ;
        }
        if (! ($col_round_id === NULL))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'your game is start, can not join room' ;
            return -1 ;
        }
        if (! ($col_room_id === NULL))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'you are in room, can not join room' ;
            return -1 ;
        }

        $query = '
            SELECT 
                `room_id`
                , `room_status`
                , `attendance`
                , `max_attendance`
                , `private`
                , `password`
                , `room_leader`
                , `position1`
                , `position2`
                , `position3`
                , `position4`
            FROM `room_info`
            WHERE `room_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $room_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        if ($record_number === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find room' ;
            return -1 ;
        }

        $stmt -> bind_result (
            $col_room_id
            , $col_room_status
            , $col_attendance
            , $col_max_attendance
            , $col_private
            , $col_password
            , $col_room_leader
            , $col_position1
            , $col_position2
            , $col_position3
            , $col_position4
        ) ;
        while ($stmt -> fetch ())
        {
        }

        if ($col_room_status != 'M')
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'game is ongoing, can not join' ;
            return -1 ;
        }

        if ($col_private === 1)
        {
            if ($col_password != $password)
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = 'wrong password' ;
                return -1 ;
            }
        }

        if ($col_attendance >= $col_max_attendance)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'max attendace, can not join' ;
            return -1 ;
        }

        $room_data = array () ;
        $room_data ['position1'] = $col_position1 ;
        $room_data ['position2'] = $col_position2 ;
        $room_data ['position3'] = $col_position3 ;
        $room_data ['position4'] = $col_position4 ;

        $room_leader = $col_room_leader ;
        if ($col_room_leader === null)
        {
            $room_leader = strval ($col_attendance + 1) ;
        }

        // can join room
        $ret = 0 ;
        $ret = sql_join_room ($room_id, $user_id, $col_attendance + 1, $room_data, $room_leader, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;

        return 1 ;
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

function sql_join_room ($room_id, $user_id, $order, $room_data, $room_leader, & $result)
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
        // #cn_3102-1
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3102-1" . mysqli_connect_error ()) ;
        }

        $position_column = '' ;
        $position_number = '' ;
        if ($room_data ['position4'] === null)
        {
            $position_column = 'position4' ;
            $position_number = '4' ;
        }
        if ($room_data ['position3'] === null)
        {
            $position_column = 'position3' ;
            $position_number = '3' ;
        }
        if ($room_data ['position2'] === null)
        {
            $position_column = 'position2' ;
            $position_number = '2' ;
        }
        if ($room_data ['position1'] === null)
        {
            $position_column = 'position1' ;
            $position_number = '1' ;
        }
        $result ['position_column'] = $position_column ;

        $str_position = strval ($order) ;

        $query = '
            UPDATE `room_info`
            SET
                `attendance` = ?
                , `player' . $order . '_id` = ?
                , `player' . $order . '_position` = ?
                , `' . $position_column . '` = ?
                , `room_leader` = ?
            WHERE
                `room_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('iisssi', $order, $user_id, $position_number, $str_position, $room_leader, $room_id) ;
        $stmt -> execute () ;
        if ($stmt -> affected_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'update room_info failed' ;
            $result ['query'] = $query ;
            $result ['order'] = $order ;
            $result ['user_id'] = $user_id ;
            $result ['room_id'] = $room_id ;
            $result ['room_leader'] = $room_leader ;
            $result ['affected_rows'] = $stmt -> affected_rows ;
            return -1 ;
        }

        $query = '
            UPDATE `player_status`
            SET
                `room_id` = ?
                , `place` = \'R\'
            WHERE
                `serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $room_id, $user_id) ;
        $stmt -> execute () ;
        if ($stmt -> affected_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'update player_status failed' ;
            return -1 ;
        }

        return 1 ;
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

function delete_participant ($room_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    /*$log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- testing log msg, room_id: ' . $room_id . "\n" ;
    error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
    $result = array () ;
    $result ['status'] = 'testing' ;
    $result ['message'] = 'testing log' ;
    echo json_encode ($result) ;
    return ;*/

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    sql_query_deleting_room_participant ($room_id, $sn_from_jwt, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_deleting_room_participant ($room_id, $user_id, & $result)
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
        // #cn_3105
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_3105" . mysqli_connect_error ()) ;
        }

        // #do player status checking
        $col_A = array () ;
        $ret = sql_query_deleting_room_participant_part_A ($db, $user_id, $col_A, $result) ;
        if ($ret === -1)
        {
            return ;
        }

        // #01
        $col_after_B_process = array () ;
        $ret = sql_query_deleting_room_participant_part_B ($db, $user_id, $room_id, $col_after_B_process, $result) ;
        if ($ret === -1)
        {
            return ;
        }

        // #02
        $col_backup = array () ;
        $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_backup, $result) ;
        if ($ret === -1)
        {
            return ;
        }

        // #03 測試用備份，上線後，取消執行
        /*$col_before_C_update = array () ;
        $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_before_C_update, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'load record before C (update) failed' ; // 上線時，不使用此欄
            return ;
        }*/

        // #do part C update with param #01 $col_after_B_process
        /*$ret = sql_query_deleting_room_participant_part_C ($db, $room_id, $col_after_B_process, $result) ;
        if ($ret === -1)
        {
            return ;
        }*/
        // set have update record flag true

        // #04 測試用備份，上線後，取消執行
        /*$col_after_C_update = array () ;
        $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_after_C_update, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'load record after C (update) failed' ; // 上線時，不使用此欄
            return ;
        }*/

        // #05 測試用備份，上線後，取消執行
        /*$col_before_D_delete = array () ;
        $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_before_D_delete, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'load record before D (delete) failed' ; // 上線時，不使用此欄
            return ;
        }*/

        // #do part D delete
        /*$ret = sql_query_deleting_room_participant_part_D ($db, $room_id, $result) ;
        if ($ret === -1)
        {
            //$result ['message_extra'] = 'D (delete room record) failed' ; // 上線時，不使用此欄

            // 需執行復原
            return ;
        }*/
        // set have delete record flag true

        // #do check record existed
        /*$ret = sql_query_deleting_room_participant_check_record_existed ($db, $room_id, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'after delete, record still existed!! this is abnormal' ; // 上線時，不使用此欄
            return ;
        }*/

        // #do part D delete recovery with param #05 $col_before_D_delete
        /*$ret = sql_query_deleting_room_participant_part_D_recovery ($db, $room_id, $col_before_D_delete, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'recover deleting record (part D) failed' ; // 上線時，不使用此欄
            return ;
        }*/

        // #05-r $col_after_D_delete_recovery
        /*$col_after_D_delete_recovery = array () ;
        $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_after_D_delete_recovery, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'load record after D (delete) recovery failed' ; // 上線時，不使用此欄
            return ;
        }*/

        // #do part C update recovery with param #03 $col_before_C_update
        /*$ret = sql_query_deleting_room_participant_part_C_recovery ($db, $room_id, $col_before_C_update, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'recover updating record (part C) failed' ; // 上線時，不使用此欄
            return ;
        }*/

        // #03-r $col_after_C_update_recovery
        /*$col_after_C_update_recovery = array () ;
        $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_after_C_update_recovery, $result) ;
        if ($ret === -1)
        {
            $result ['message_extra'] = 'load record after C (update) recovery failed' ; // 上線時，不使用此欄
            return ;
        }*/

        // sql_query_deleting_room_participant_compare_col_data ($col_backup, $col_after_B_process, $col_before_C_update, $col_after_C_update, $col_before_D_delete, $col_after_C_update_recovery, $col_after_D_delete_recovery) ;

        // #do output cols
        /*$result ['#01_$col_after_B_process'] = $col_after_B_process ;
        $result ['#02_$col_backup'] = $col_backup ;
        $result ['#03_$col_before_C_update'] = $col_before_C_update ;
        $result ['#04_$col_after_C_update'] = $col_after_C_update ;
        $result ['#05_$col_before_D_delete'] = $col_before_D_delete ;
        $result ['#05-r_$col_after_D_delete_recovery'] = $col_after_D_delete_recovery ;
        $result ['#03-r_$col_after_C_update_recovery'] = $col_after_C_update_recovery ;
        return ;*/

        $change_record_behavior = 0 ;
        if (
            ($col_after_B_process ['attendance'] === 0)
            &&
            ($room_id < 1000)
        )
        {
            // 目前還沒有 1000 以下的房間 = =||| 測不了
            // do part D delete
            $ret = sql_query_deleting_room_participant_part_D ($db, $room_id, $result) ;
            if ($ret === -1)
            {
                return ;
            }

            // #do check record existed
            $ret = sql_query_deleting_room_participant_check_record_existed ($db, $room_id, $result) ;
            if ($ret === -1)
            {
                return ;
            }
            $change_record_behavior = 1 ;
        }
        else
        {
            // do part C update

            // #do part C update with param #01 $col_after_B_process
            $ret = sql_query_deleting_room_participant_part_C ($db, $room_id, $col_after_B_process, $result) ;
            if ($ret === -1)
            {
                // 資料沒有被變過，不需復原
                return ;
            }
        }

        // #06
        $col_backup_player_status = array () ;
        $ret = sql_query_deleting_room_participant_backup_player_status ($db, $user_id, $col_backup_player_status, $result) ;
        if ($ret === -1)
        {
            if ($change_record_behavior === 1)
            {
                // recover delete
                $ret = sql_query_deleting_room_participant_part_D_recovery ($db, $room_id, $col_backup, $result) ;
                if ($ret === -1)
                {
                    return ;
                }
            }
            else
            {
                // recover update
                $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_backup, $result) ;
                if ($ret === -1)
                {
                    return ;
                }
            }
            return ;
        }

        // do part E update player status
        $ret = sql_query_deleting_room_participant_part_E ($db, $user_id, $result) ;
        if ($ret === -1)
        {
            if ($change_record_behavior === 1)
            {
                // recover delete
                $ret = sql_query_deleting_room_participant_part_D_recovery ($db, $room_id, $col_backup, $result) ;
                if ($ret === -1)
                {
                    return ;
                }
            }
            else
            {
                // recover update
                $ret = sql_query_deleting_room_participant_backup_room_info ($db, $room_id, $col_backup, $result) ;
                if ($ret === -1)
                {
                    return ;
                }
            }
            return ;
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

function sql_query_deleting_room_participant_compare_col_data ($col_backup, $col_after_B_process, $col_before_C_update, $col_after_C_update, $col_before_D_delete, $col_after_C_update_recovery, $col_after_D_delete_recovery)
{
    // #vs backup vs part_C_update_before
    // #02 $col_backup 相同 #03 $col_before_C_update
    /*if ($col_backup === $col_before_C_update)
    {
        $result ['f._$col_backup_v.s._$col_before_C_update'] = '=== as expected' ;
    }
    else
    {
        $result ['f._$col_backup_v.s._$col_before_C_update'] = '! === not expected' ;
    }
    if ($col_backup == $col_before_C_update)
    {
        $result ['n._$col_backup_v.s._$col_before_C_update'] = '== as expected' ;
    }
    else
    {
        $result ['n._$col_backup_v.s._$col_before_C_update'] = '! == not expected' ;
    }*/

    // #vs after_C_update vs after_B_process
    // #04 $col_after_C_update 相同 #01 $col_after_B_process
    /*if ($col_after_C_update === $col_after_B_process)
    {
        $result ['f._$col_after_C_update_v.s._$col_after_B_process'] = '=== as expected' ;
    }
    else
    {
        $result ['f._$col_after_C_update_v.s._$col_after_B_process'] = '! === not expected' ;
    }
    if ($col_after_C_update == $col_after_B_process)
    {
        $result ['n._$col_after_C_update_v.s._$col_after_B_process'] = '== as expected' ;
    }
    else
    {
        $result ['n._$col_after_C_update_v.s._$col_after_B_process'] = '! == not expected' ;
    }*/

    // #vs before_D_delete vs after_C_update
    // #05 $col_before_D_delete 相同 #04 $col_after_C_update
    /*if ($col_before_D_delete === $col_after_C_update)
    {
        $result ['f._$col_before_D_delete_v.s._$col_after_C_update'] = '=== as expected' ;
    }
    else
    {
        $result ['f._$col_before_D_delete_v.s._$col_after_C_update'] = '! === not expected' ;
    }
    if ($col_before_D_delete == $col_after_C_update)
    {
        $result ['n._$col_before_D_delete_v.s._$col_after_C_update'] = '== as expected' ;
    }
    else
    {
        $result ['n._$col_before_D_delete_v.s._$col_after_C_update'] = '! == not expected' ;
    }*/

    // #vs after_D_delete_recovery vs before_D_delete
    // #05-r $col_after_D_delete_recovery 相同 #05 $col_before_D_delete
    /*if ($col_after_D_delete_recovery === $col_before_D_delete)
    {
        $result ['f._$col_after_D_delete_recovery_v.s._$col_before_D_delete'] = '=== as expected' ;
    }
    else
    {
        $result ['f._$col_after_D_delete_recovery_v.s._$col_before_D_delete'] = '! === not expected' ;
    }
    if ($col_after_D_delete_recovery == $col_before_D_delete)
    {
        $result ['n._$col_after_D_delete_recovery_v.s._$col_before_D_delete'] = '== as expected' ;
    }
    else
    {
        $result ['n._$col_after_D_delete_recovery_v.s._$col_before_D_delete'] = '! == not expected' ;
    }*/

    // #vs after_C_update_recovery vs before_C_update
    // #03-r $col_after_C_update_recovery 相同 #03 $col_before_C_update
    /*if ($col_after_C_update_recovery === $col_before_C_update)
    {
        $result ['f._$col_after_C_update_recovery_v.s._$col_before_C_update'] = '=== as expected' ;
    }
    else
    {
        $result ['f._$col_after_C_update_recovery_v.s._$col_before_C_update'] = '! === not expected' ;
    }
    if ($col_after_C_update_recovery == $col_before_C_update)
    {
        $result ['n._$col_after_C_update_recovery_v.s._$col_before_C_update'] = '== as expected' ;
    }
    else
    {
        $result ['n._$col_after_C_update_recovery_v.s._$col_before_C_update'] = '! == not expected' ;
    }*/
}

function sql_query_deleting_room_participant_backup_room_info (& $db, $room_id, & $col, & $result)
{
    // sql_query_backingup
    // 備份資料，預防之後需要復原資料
    try
    {
        $query = '
            SELECT 
                `room_id`
                , `round_id`
                , `room_status`
                , `bingo_size`
                , `attendance`
                , `max_attendance`
                , `private`
                , `password`
                , `room_leader`
                , `player1_id`
                , `player2_id`
                , `player3_id`
                , `player4_id`
                , `player1_ready_status`
                , `player2_ready_status`
                , `player3_ready_status`
                , `player4_ready_status`
                , `player1_position`
                , `player2_position`
                , `player3_position`
                , `player4_position`
                , `position1`
                , `position2`
                , `position3`
                , `position4`
            FROM `room_info`
            WHERE `room_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $room_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        if ($record_number === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find room' ;
            return -1 ;
        }

        $stmt -> bind_result (
            $col ['room_id']
            , $col ['round_id']
            , $col ['room_status']
            , $col ['bingo_size']
            , $col ['attendance']
            , $col ['max_attendance']
            , $col ['private']
            , $col ['password']
            , $col ['room_leader']
            , $col ['player1_id']
            , $col ['player2_id']
            , $col ['player3_id']
            , $col ['player4_id']
            , $col ['player1_ready_status']
            , $col ['player2_ready_status']
            , $col ['player3_ready_status']
            , $col ['player4_ready_status']
            , $col ['player1_position']
            , $col ['player2_position']
            , $col ['player3_position']
            , $col ['player4_position']
            , $col ['position1']
            , $col ['position2']
            , $col ['position3']
            , $col ['position4']
        ) ;
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        return 1 ;
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

function sql_query_deleting_room_participant_part_A (& $db, $user_id, & $col, & $result)
{
    // sql_query_reading_player_status 
    // 抓 player_status table 查看 player 是不是有在某個 room 裡面
    try
    {
        $query = '
            SELECT
                `place`
                , `room_id`
                , `round_id`
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
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find player id' ;
            return -1 ;
        }

        $stmt -> bind_result (
            $col_place
            , $col_room_id
            , $col_round_id
        ) ;
        while ($stmt -> fetch ())
        {
        }

        $stmt -> free_result () ;
        $stmt -> close () ;
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

    if (! ($col_place === 'R'))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'you are not in room detail page' ;
    }

    /*if (! ($col_round_id === NULL))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'your game is start, can not join room' ;
        return -1 ;
    }*/

    /*if (($col_room_id === NULL))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'you are not in room, can not leave room' ;
        return -1 ;
    }*/
    return 1 ;
}

function sql_query_deleting_room_participant_part_B (& $db, $user_id, $room_id, & $col, & $result)
{
    // sql_query_reading_room_info
    // 抓 room_info 的各項 field 拿來計算 update 的資料
    // $col 是 input 參數，也是 output 參數
    // process record
    try
    {
        $query = '
            SELECT 
                `room_id`
                , `round_id`
                , `room_status`
                , `bingo_size`
                , `attendance`
                , `max_attendance`
                , `private`
                , `password`
                , `room_leader`
                , `player1_id`
                , `player2_id`
                , `player3_id`
                , `player4_id`
                , `player1_ready_status`
                , `player2_ready_status`
                , `player3_ready_status`
                , `player4_ready_status`
                , `player1_position`
                , `player2_position`
                , `player3_position`
                , `player4_position`
                , `position1`
                , `position2`
                , `position3`
                , `position4`
            FROM `room_info`
            WHERE `room_id` = ?
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
        $stmt -> bind_param ('iiiii', $room_id, $user_id, $user_id, $user_id, $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        if ($record_number === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find room' ;
            return -1 ;
        }

        //$col = array () ;
        $stmt -> bind_result (
            $col ['room_id']
            , $col ['round_id']
            , $col ['room_status']
            , $col ['bingo_size']
            , $col ['attendance']
            , $col ['max_attendance']
            , $col ['private']
            , $col ['password']
            , $col ['room_leader']
            , $col ['player1_id']
            , $col ['player2_id']
            , $col ['player3_id']
            , $col ['player4_id']
            , $col ['player1_ready_status']
            , $col ['player2_ready_status']
            , $col ['player3_ready_status']
            , $col ['player4_ready_status']
            , $col ['player1_position']
            , $col ['player2_position']
            , $col ['player3_position']
            , $col ['player4_position']
            , $col ['position1']
            , $col ['position2']
            , $col ['position3']
            , $col ['position4']
        ) ;
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;

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

    if ($col ['room_status'] != 'M')
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'game is ongoing, can not leave' ;
        return -1 ;
    }

    $player_number = 0 ;
    if ($user_id === $col ['player1_id'])
    {
        $player_number = 1 ;
    }
    if ($user_id === $col ['player2_id'])
    {
        $player_number = 2 ;
    }
    if ($user_id === $col ['player3_id'])
    {
        $player_number = 3 ;
    }
    if ($user_id === $col ['player4_id'])
    {
        $player_number = 4 ;
    }

    if ($col ['player' . $player_number . '_ready_status'] === 'R')
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'you are getting ready, can not leave room' ;
        return -1 ;
    }

    $position_number = $col ['player' . $player_number . '_position'] ;

    if ($col ['room_leader'] === strval ($player_number))
    {
        $col ['room_leader'] = '1' ;
    }
    else
    {
        if (intval ($col ['room_leader']) > $player_number)
        {
            $col ['room_leader'] = strval (intval ($col ['room_leader']) - 1) ;
        }
    }

    $index = $player_number ;
    while ($index < $col ['attendance'])
    {
        $col ['player' . $index . '_id'] = $col ['player' . ($index + 1) . '_id'] ;
        $col ['player' . $index . '_ready_status'] = $col ['player' . ($index + 1) . '_ready_status'] ;
        $col ['player' . $index . '_position'] = $col ['player' . ($index + 1) . '_position'] ;
        $index ++ ;
    }
    $col ['player' . $col ['attendance'] . '_id'] = NULL ;
    $col ['player' . $col ['attendance'] . '_ready_status'] = NULL ;
    $col ['player' . $col ['attendance'] . '_position'] = NULL ;
    $col ['attendance'] -= 1 ;

    $col ['position1'] = NULL ;
    $col ['position2'] = NULL ;
    $col ['position3'] = NULL ;
    $col ['position4'] = NULL ;

    for ($i = 1 ; $i <= $col ['attendance'] ; $i ++)
    {
        $col ['position' . $col ['player' . $i . '_position']] = $i ;
    }

    if ($col ['attendance'] > 0)
    {
        return 1 ;
    }
    if ($room_id < 1000)
    {
        return 1 ;
    }

    // init room_info
    $col ['round_id'] = NULL ;
    $col ['room_status'] = 'M' ;
    $col ['bingo_size'] = 5 ;
    $col ['max_attendance'] = 4 ;
    $col ['private'] = 0 ;
    $col ['password'] = '' ;

    return 1 ;
}

function sql_query_deleting_room_participant_part_C (& $db, $room_id, & $col, & $result)
{
    // sql_query_updating_room_info
    // 修改 room_info 退出房間後，player 資料的異動
    // update record
    try
    {
        $query = '
            UPDATE `room_info`
            SET
                `round_id` = ?
                , `attendance` = ?
                , `room_leader` = ?
                , `player1_id` = ?
                , `player2_id` = ?
                , `player3_id` = ?
                , `player4_id` = ?
                , `player1_ready_status` = ?
                , `player2_ready_status` = ?
                , `player3_ready_status` = ?
                , `player4_ready_status` = ?
                , `player1_position` = ?
                , `player2_position` = ?
                , `player3_position` = ?
                , `player4_position` = ?
                , `position1` = ?
                , `position2` = ?
                , `position3` = ?
                , `position4` = ?
            WHERE
                `room_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('sisiiiissssssssssssi'
            , $col ['round_id']
            , $col ['attendance']
            , $col ['room_leader']
            , $col ['player1_id']
            , $col ['player2_id']
            , $col ['player3_id']
            , $col ['player4_id']
            , $col ['player1_ready_status']
            , $col ['player2_ready_status']
            , $col ['player3_ready_status']
            , $col ['player4_ready_status']
            , $col ['player1_position']
            , $col ['player2_position']
            , $col ['player3_position']
            , $col ['player4_position']
            , $col ['position1']
            , $col ['position2']
            , $col ['position3']
            , $col ['position4']
            , $col ['room_id']
        ) ;
        $stmt -> execute () ;
        if ($stmt -> affected_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'Server SQL Error' ;

            $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_deleting_room_participant_part_C' . "\n" ;
            error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
            return -1 ;
        }
        $stmt -> close () ;
        return 1 ;
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

function sql_query_deleting_room_participant_part_D (& $db, $room_id, & $result)
{
    // sql_query_deleting_room_info
    // 刪除該房間的 record
    // delete record
    try
    {
        $query = '
            DELETE FROM `room_info`
            WHERE
                `room_id` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $room_id) ;
        $stmt -> execute () ;
        if ($stmt -> affected_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'Server SQL Error' ;

            $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do delete record, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_deleting_room_participant_part_D' . "\n" ;
            error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
            return -1 ;
        }
        $stmt -> close () ;
        return 1 ;
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

function sql_query_deleting_room_participant_part_C_recovery (& $db, $room_id, & $col, & $result)
{
    // sql_query_updatingup
    // 復原資料，執行階段錯誤，放棄修改資料，進行還原
    try
    {
        $query = '
            UPDATE `room_info`
            SET
                `room_id` = ?
                , `round_id` = ?
                , `room_status` = ?
                , `bingo_size` = ?
                , `attendance` = ?
                , `max_attendance` = ?
                , `private` = ?
                , `password` = ?
                , `room_leader` = ?
                , `player1_id` = ?
                , `player2_id` = ?
                , `player3_id` = ?
                , `player4_id` = ?
                , `player1_ready_status` = ?
                , `player2_ready_status` = ?
                , `player3_ready_status` = ?
                , `player4_ready_status` = ?
                , `player1_position` = ?
                , `player2_position` = ?
                , `player3_position` = ?
                , `player4_position` = ?
                , `position1` = ?
                , `position2` = ?
                , `position3` = ?
                , `position4` = ?
            WHERE
                `room_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('issiiiissiiiissssssssssssi'
            , $col ['room_id']
            , $col ['round_id']
            , $col ['room_status']
            , $col ['bingo_size']
            , $col ['attendance']
            , $col ['max_attendance']
            , $col ['private']
            , $col ['password']
            , $col ['room_leader']
            , $col ['player1_id']
            , $col ['player2_id']
            , $col ['player3_id']
            , $col ['player4_id']
            , $col ['player1_ready_status']
            , $col ['player2_ready_status']
            , $col ['player3_ready_status']
            , $col ['player4_ready_status']
            , $col ['player1_position']
            , $col ['player2_position']
            , $col ['player3_position']
            , $col ['player4_position']
            , $col ['position1']
            , $col ['position2']
            , $col ['position3']
            , $col ['position4']
            , $room_id
        ) ;
        $stmt -> execute () ;
        if ($stmt -> affected_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'Server SQL Error' ;

            $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record (recover), but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_deleting_room_participant_part_C_recovery' . "\n" ;
            error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
            return -1 ;
        }
        $stmt -> close () ;
        return 1 ;
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

function sql_query_deleting_room_participant_check_record_existed (& $db, $room_id, & $result)
{
    // sql_query some record existed ?
    // 刪除資料後，檢查看該筆 record 還存在嗎？
    try
    {
        $query = '
            SELECT 
                `room_id`
                , `round_id`
                , `room_status`
                , `bingo_size`
                , `attendance`
                , `max_attendance`
                , `private`
                , `password`
                , `room_leader`
                , `player1_id`
                , `player2_id`
                , `player3_id`
                , `player4_id`
                , `player1_ready_status`
                , `player2_ready_status`
                , `player3_ready_status`
                , `player4_ready_status`
                , `player1_position`
                , `player2_position`
                , `player3_position`
                , `player4_position`
                , `position1`
                , `position2`
                , `position3`
                , `position4`
            FROM `room_info`
            WHERE `room_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $room_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        $result ['deleting_select_record_number'] = $record_number ;
        $stmt -> free_result () ;
        $stmt -> close () ;
        if ($record_number === 0)
        {
            //$result ['deleting_select_record_number_result'] = 'ok' ; // 測試用，上線後不必輸出
            return 1 ;
        }

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, after delete record, record still existed, file: bingo_rooms_fns.php, in func sql_query_deleting_room_participant_check_record_existed' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
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
        error_log ('[Date: ' . date ('Y-m-d, h:i:s A') . '] --- ' . $error_log_msg . "\n", 3, '/var/weblog/sql-errors.log') ;
        exit ;
    }
}

function sql_query_deleting_room_participant_part_D_recovery (& $db, $room_id, & $col, & $result)
{
    // sql_query_insert_into
    // 復原資料，執行階段錯誤，先前已經把該 record 刪掉了，進行插入 record 還原
    try
    {
        $query = '
            INSERT INTO `room_info` (
                `room_id`
                , `round_id`
                , `room_status`
                , `bingo_size`
                , `attendance`
                , `max_attendance`
                , `private`
                , `password`
                , `room_leader`
                , `player1_id`
                , `player2_id`
                , `player3_id`
                , `player4_id`
                , `player1_ready_status`
                , `player2_ready_status`
                , `player3_ready_status`
                , `player4_ready_status`
                , `player1_position`
                , `player2_position`
                , `player3_position`
                , `player4_position`
                , `position1`
                , `position2`
                , `position3`
                , `position4`
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('issiiiissiiiissssssssssss'
            , $col ['room_id']
            , $col ['round_id']
            , $col ['room_status']
            , $col ['bingo_size']
            , $col ['attendance']
            , $col ['max_attendance']
            , $col ['private']
            , $col ['password']
            , $col ['room_leader']
            , $col ['player1_id']
            , $col ['player2_id']
            , $col ['player3_id']
            , $col ['player4_id']
            , $col ['player1_ready_status']
            , $col ['player2_ready_status']
            , $col ['player3_ready_status']
            , $col ['player4_ready_status']
            , $col ['player1_position']
            , $col ['player2_position']
            , $col ['player3_position']
            , $col ['player4_position']
            , $col ['position1']
            , $col ['position2']
            , $col ['position3']
            , $col ['position4']
        ) ;
        $stmt -> execute () ;
        if ($stmt -> affected_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'Server SQL Error' ;

            $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do insert into record (recover), but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_deleting_room_participant_part_D_recovery' . "\n" ;
            error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
            return -1 ;
        }
        $stmt -> close () ;
        return 1 ;
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

function sql_query_deleting_room_participant_part_E (& $db, $user_id, & $result)
{
    // sql_query_updating_player_status
    // 修改 player_status 退出房間後，player 資料的異動
    // update record
    try
    {
        $query = '
            UPDATE `player_status`
            SET
                `place` = \'N\'
                , `room_id` = NULL
                , `round_id` = NULL
            WHERE
                `serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $user_id) ;
        $stmt -> execute () ;
        if ($stmt -> affected_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'Server SQL Error' ;

            $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do update record, but affected_rows gets 0, it should be 1, file: bingo_rooms_fns.php, in func sql_query_deleting_room_participant_part_E' . "\n" ;
            error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
            return -1 ;
        }
        $stmt -> close () ;
        return 1 ;
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

function sql_query_deleting_room_participant_backup_player_status (& $db, $user_id, & $col, & $result)
{
    // sql_query_backingup
    // 備份資料，預防之後需要復原資料
    try
    {
        $query = '
            SELECT 
                `room_id`
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
            $result ['status'] = 'failed' ;
            $result ['message'] = 'do not find player' ;
            return -1 ;
        }

        $stmt -> bind_result (
            $col ['room_id']
        ) ;
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        return 1 ;
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

?>
