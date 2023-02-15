<?php
function test_single_room_detail_main ($uri)
{
    $result = array () ;
    $result ['status'] = 'testing environment' ;
    $result ['message'] = '' ;
    $result ['uri'] = $uri ;
    
    sql_query (intval ($uri), $result) ;
    
    echo json_encode ($result) ;
    exit ;
}

function sql_query ($room_id, & $result)
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
        // #cn_test
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test" . mysqli_connect_error ()) ;
        }

        $col = array () ;
        $ret = query_single_room_detail ($db, $col, $room_id, $result) ;
        if ($ret === -1)
        {
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

function query_single_room_detail (& $db, & $col, $room_id, & $result)
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
    FROM `room_info` WHERE `room_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $room_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $result ['record_number'] = $stmt -> num_rows ;

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
        $result ['room_id'] = $col ['room_id'] ;
        $result ['round_id'] = $col ['round_id'] ;
        $result ['room_status'] = $col ['room_status'] ;
        $result ['bingo_size'] = $col ['bingo_size'] ;
        $result ['attendance'] = $col ['attendance'] ;
        $result ['max_attendance'] = $col ['max_attendance'] ;
        $result ['private'] = $col ['private'] ;
        $result ['password'] = $col ['password'] ;
        $result ['room_leader'] = $col ['room_leader'] ;
        $result ['player1_id'] = $col ['player1_id'] ;
        $result ['player2_id'] = $col ['player2_id'] ;
        $result ['player3_id'] = $col ['player3_id'] ;
        $result ['player4_id'] = $col ['player4_id'] ;
        $result ['player1_ready_status'] = $col ['player1_ready_status'] ;
        $result ['player2_ready_status'] = $col ['player2_ready_status'] ;
        $result ['player3_ready_status'] = $col ['player3_ready_status'] ;
        $result ['player4_ready_status'] = $col ['player4_ready_status'] ;
        $result ['player1_position'] = $col ['player1_position'] ;
        $result ['player2_position'] = $col ['player2_position'] ;
        $result ['player3_position'] = $col ['player3_position'] ;
        $result ['player4_position'] = $col ['player4_position'] ;
        $result ['position1'] = $col ['position1'] ;
        $result ['position2'] = $col ['position2'] ;
        $result ['position3'] = $col ['position3'] ;
        $result ['position4'] = $col ['position4'] ;
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    //$result ['status'] = 'success' ;
    //$result ['message'] = 'ok' ;

    return ;
}

?>
