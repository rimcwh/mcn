<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;

function test_temp_main ()
{
    $ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;

    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    $result ['user_id'] = $sn_from_jwt ;
    
    sql_query_test_temp (7, $result) ;
    
    echo json_encode ($result) ;
    exit ;
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
