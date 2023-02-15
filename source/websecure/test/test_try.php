<?php

require (__DIR__ . '/../mis_fns/process_sql_error.php') ;

function test_try_main ()
{
    $result = array () ;
    $result ['status'] = 'try' ;
    $result ['message'] = 'test try php' ;
    
    $bind_type = '' ;
    $bind_type .= 'i' ;
    $bind_type .= 's' ;
    $bind_type .= 'i' ;
    $bind_type .= 'i' ;
    $bind_type .= 's' ;
    
    $result ['bind_type'] = $bind_type ;
    
    
    //tttn ($result) ;
    
    echo json_encode ($result) ;
    exit ;
    
}

function tttn (& $result)
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
        // #cn_3104
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test" . mysqli_connect_error ()) ;
        }

        keke ($db, $result) ;

        $db -> close () ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;

        return ;
    }
    catch (\Exception $e)
    {
        ProcessSqlError\process_sql_error ($e) ;
    }

}

function keke (& $db, & $result)
{
    $query = '
        SELECT 
            `room_id_G`
            , `player1_id`
            , `player2_id`
            , `player3_id`
            , `player4_id`
            , `room_leader`
            , `attendance`
            , `max_attendance`
        FROM `room_info`
        WHERE `room_id` = ?
    ' ;

    $room_id = 1001 ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $room_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $record_number = $stmt -> num_rows ;

    if ($record_number === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'data not find' ;
        return ;
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

?>
