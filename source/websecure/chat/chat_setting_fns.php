<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/jwt_fns_for_csrf.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function chat_setting_main ($uri)
{
    if ($_SERVER ['REQUEST_METHOD'] == 'GET')
    {
        get_chat_setting ($uri) ;
    }
    if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
    {
        patch_chat_setting ($uri) ;
    }
}

function chat_setting_check_jwt_retrieve ($ret)
{
    if ($ret ['status'] === 'failed')
    {
        $result = array (
            'status' => 'failed',
            'message' => $ret ['message'],
        ) ;
        echo json_encode ($result, JSON_UNESCAPED_UNICODE) ;
        exit ;
    }
}

function chat_setting_check_jwt_sn_and_uri_sn ($sn_from_jwt, $sn_from_uri)
{
    if (! (strval ($sn_from_jwt) === $sn_from_uri))
    {
        // error
        $result = array (
            'status' => 'failed',
            'message' => 'serial number not matched.'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
}

function get_chat_setting ($uri)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if (! (strval ($sn_from_jwt) === $uri))
    {
        // error
        $result ['status'] = 'failed' ;
        $result ['message'] = 'serial number not matched.' ;
        echo json_encode ($result) ;
        exit ;
    }

    sql_query_reading_chat_setting ($sn_from_jwt, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_chat_setting ($sn, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_r_chat.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_chat ($db_server, $db_user_name, $db_password, $db_name) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    try
    {
        // #cn_1401
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1401" . mysqli_connect_error ()) ;
        }
        $query = 'SELECT * FROM account_setting WHERE serial_number = ?' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $sn) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        $rows_number = 'Number of accounts: ' . $stmt -> num_rows ;
        if ($stmt -> num_rows === 0)
        {
            http_response_code (403) ;
            $result ['status'] = 'failed' ;
            $result ['message'] = 'no item matched' ;
            echo json_encode ($result) ;
            exit ;
        }
        $stmt -> bind_result ($col_serial_number, $col_nickname, $col_description, $col_allow_info_public, $col_allow_search_id, $col_show_message_bottom_to_top) ;
        
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;
        $result ['serial_number'] = $col_serial_number ;
        $result ['nickname'] = htmlspecialchars ($col_nickname) ;
        $result ['description'] = htmlspecialchars ($col_description) ;
        $result ['allow_info_public'] = $col_allow_info_public ;
        $result ['allow_search_id'] = $col_allow_search_id ;
        $result ['show_message_bottom_to_top'] = $col_show_message_bottom_to_top ;
        
        return 1 ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result ['status'] = 'error' ;
        $result ['message'] = 'Server SQL Error' ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ' ;
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function patch_chat_setting ($uri)
{
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if (! (strval ($sn_from_jwt) === $uri))
    {
        // error
        $result ['status'] = 'failed' ;
        $result ['message'] = 'serial number not matched.' ;
        echo json_encode ($result) ;
        exit ;
    }
    
    //$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    //chat_setting_check_jwt_retrieve ($ret) ;
    
    //$sn_from_jwt = $ret ['jwt_decode'] -> sn ;
    chat_setting_check_jwt_sn_and_uri_sn (strval ($sn_from_jwt), $uri) ;

    $request_body = file_get_contents ('php://input') ;
    // 要寫判斷 === false 還有 ! isset 還有 empty ( 應該要弄成一個 function 哪！ )

    $data = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array
    $data ['user_id'] = $sn_from_jwt ;

    // 要做過濾 filter user input data


    // 進 sql 做 query
    sql_query_updating_chat_setting ($data) ;
}

function sql_query_updating_chat_setting ($data)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_chat.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_chat ($db_server, $db_user_name, $db_password, $db_name) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    try
    {
        // #cn_1402
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1402" . mysqli_connect_error ()) ;
        }
        $query = 'UPDATE account_setting SET nickname = ? WHERE serial_number = ?' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('si', $data ['nickname'], $data ['user_id']) ;
        if (! ($stmt -> execute ()))
        {
            // do not work
            $result = array (
                'status' => 'failed',
                'message' => 'stmt execute failed',
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
        
        if ($stmt -> affected_rows > 0)
        {
            $result = array (
                'status' => 'success',
                'message' => 'update to database!'
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
        else
        {
            $result = array (
                'status' => 'failed',
                'message' => 'update not match item or data not changed'
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
        $stmt -> close () ;
        $db -> close () ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'error',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ' ;
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

?>
