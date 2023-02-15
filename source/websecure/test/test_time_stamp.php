<?php

function test_time_stamp_main ($uri)
{
    $result = array () ;
    $result ['status'] = 'testing' ;
    $result ['message'] = 'here is test time stamp main' ;
    $result ['route'] = $uri ;
    
    $result ['date1'] = date ('h:i:s') ;
    $result ['time1'] = 'now: ' . time () ;
    
    $time_start = microtime (true) ;
    $result ['micro_time1'] = 'now: ' . $time_start ;
    
    for ($i = 0 ; $i < 90000 ; $i ++)
    {
        link_sql ($result, $uri) ;
    }
    
    $time_end = microtime (true) ;
    $result ['micro_time2'] = 'now: ' . $time_end ;
    
    $result ['diff'] = $time_end - $time_start ;
    $result ['d_t'] = $time_end - $_SERVER ['REQUEST_TIME_FLOAT' ] ;
    
    $result ['uri'] = $uri ;
    
    //sql_query_test_temp (7, $result) ;
    
    echo json_encode ($result) ;
    exit ;
}

function link_sql (& $result, $uri)
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
        // #cn_test_temp
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test_stamp" . mysqli_connect_error ()) ;
        }
        for ($i = 0 ; $i < 1 ; $i ++)
        {
            if ($uri === 'a')
            {
                query_a ($db, $result) ;
            }
            else if ($uri === 'b')
            {
                query_b ($db, $result) ;
            }
        }
        $db -> close () ;
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

function query_a (& $db, & $result)
{
    $result ['extra_info'] = 'query_a' ;
    
    $user_id = 9 ;
    
    $query = '
    SELECT ps.`place`, gr.`round_status`
    FROM `player_status` AS ps
    INNER JOIN `game_round` AS gr
        ON ps.`round_id` = gr.`round_id`
    WHERE
        (ps.`serial_number` = ?)
        AND
        ((ps.`place` = \'G\') OR (ps.`place` = \'C\'))
        AND
        (gr.`round_status` = \'F\')
    ' ;
    
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $user_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;
    
    $result ['rows_number'] = $stmt -> num_rows ;
    
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
    
    $result ['place'] = $col_place ;
    $result ['round_status'] = $col_round_status ;
    
    return 1 ;
}

function query_b (& $db, & $result)
{
    $result ['extra_info'] = 'query_b' ;
    
    $user_id = 9 ;
    
    $query = '
    SELECT ps.`place`, gr.`round_status`
    FROM `player_status` AS ps
    INNER JOIN `game_round` AS gr
        ON ps.`round_id` = gr.`round_id`
    WHERE
        ps.`serial_number` = ?
    ' ;
    
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $user_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;
    
    $result ['rows_number'] = $stmt -> num_rows ;
    
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
    
    if (($col_place === 'G') || ($col_place === 'C'))
    {
        $result ['place'] = $col_place ;
    }
    else
    {
        $result ['place'] = 'oh no' ;
    }
    if ($col_round_status === 'F')
    {
        $result ['round_status'] = $col_round_status ;
    }
    else
    {
        $result ['round_status'] = 'oh no' ;
    }
    
    return 1 ;
}

function sql_query_test_temp ($user_id, & $data)
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
        // #cn_test_temp
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test_temp" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT
                `account_id`
            FROM `player_status`
            WHERE
                `serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        $data ['record_number'] = $stmt -> num_rows ;

        if ($record_number === 0)
        {
            $data ['status'] = 'failed' ;
            $data ['message'] = 'do not find player id' ;
            return ;
        }

        $stmt -> bind_result (
            $col_account_id
        ) ;

        while ($stmt -> fetch ())
        {
        }
        
        $data ['account_id_' . $user_id] = $col_account_id ;
        
        $stmt -> free_result () ;
        $stmt -> close () ;
        
        $user_id = 8 ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $record_number = $stmt -> num_rows ;
        $data ['record_number'] = $stmt -> num_rows ;

        if ($record_number === 0)
        {
            $data ['status'] = 'failed' ;
            $data ['message'] = 'do not find player id' ;
            return ;
        }

        $stmt -> bind_result (
            $col_account_id
        ) ;

        while ($stmt -> fetch ())
        {
        }
        
        $data ['account_id_' . $user_id] = $col_account_id ;
        
        $stmt -> free_result () ;
        $stmt -> close () ;
        
        $db -> close () ;
        
        $data ['account_id_null'] = NULL ;

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


?>
