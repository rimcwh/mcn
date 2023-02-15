<?php

function test_sql_data_type_main ($uri)
{
    if ($uri == '')
    {
        echo 'bye' ;
        exit ;
    }
    $path_segment = parse_uri_one_layer ($uri) ;
    echo '<body style = "background-color: #000 ; color: #ddd ; font-size: 3rem ;">' ;
    echo 'path_segment: ' . $path_segment . '<br /><br />' ;

    if ($path_segment == 'create')
    {
        echo 'if sentence into create route<br /><br />' ;
        sql_query_creating_test () ;
    }

    if ($path_segment == 'read')
    {
        echo 'if sentence into read route<br /><br />' ;
        sql_query_reading_test () ;
    }

    echo '</body>' ;
    return 0 ;
}

function sql_query_creating_test ()
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
        // #cn_test
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test" . mysqli_connect_error ()) ;
        }

        $i = 1001 ;
        
        for ($i = 1001 ; $i < 1051 ; $i ++)
        {
            $room_id = $i ;
            $room_status = 'M' ;
            echo 'room_status strlen: ' . strlen ($room_status) . '<br />' ;

            if (strlen ($room_status) > 1)
            {
                echo 'strlen > 1 ; cutting<br />' ;
                $room_status = substr ($room_status, 0, 1) ;
                echo 'after cutting: ' . $room_status . '<br />' ;
            }

            echo '<br />' ;
            $bingo_size = 5 ;
            $attendance = 1 ;
            $max_attendance = 4 ;
            if ($room_id < 1011)
            {
                $private = false ;
            }
            else
            {
                $private = true ;
            }

            $query = '
                INSERT INTO `room_info` (
                    `room_id`
                    , `room_status`
                    , `bingo_size`
                    , `attendance`
                    , `max_attendance`
                    , `private`
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ' ;

            $stmt = $db -> prepare ($query) ;
            $stmt -> bind_param ('isiiii'
                , $room_id
                , $room_status
                , $bingo_size
                , $attendance
                , $max_attendance
                , $private) ;
            $stmt -> execute () ;

            if ($stmt -> affected_rows > 0)
            {
                echo 'database record create ok!<br /><br />' ;
            }
            else
            {
                echo 'database record create failed!<br /><br />' ;
            }
        }
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

function sql_query_reading_test ()
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

        $query = '
            SELECT 
                `room_id`
                , `room_status`
                , `bingo_size`
                , `attendance`
                , `max_attendance`
                , `private`
            FROM `room_info`
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $limit) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;

        $stmt -> bind_result (
            $col_room_id
            , $col_room_status
            , $col_bingo_size
            , $col_attendance
            , $col_max_attendance
            , $col_private
        ) ;

        $i = 0 ;
        while ($stmt -> fetch ())
        {
            echo 'room_id' . $i . ': ' . $col_room_id . '<br />' ;
            echo 'room_status' . $i . ': ' . $col_room_status . '<br />' ;
            echo 'bingo_size' . $i . ': ' . $col_bingo_size . '<br />' ;
            echo 'attendance' . $i . ': ' . $col_attendance . '<br />' ;
            echo 'max_attendance' . $i . ': ' . $col_max_attendance . '<br />' ;
            echo 'private' . $i . ': ' . $col_private . '<br />' ;
            echo 'private type: ' . gettype ($col_private) . '<br />' ;
            if ($col_private === 0)
            {
                echo 'private === 0<br />' ;
            }
            if ($col_private === 1)
            {
                echo 'private === 1<br />' ;
            }
            echo 'sxs: ' . $col_bingo_size * $col_bingo_size . '<br />' ;
            echo '<br />' ;
            $i ++ ;
        }

        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

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
