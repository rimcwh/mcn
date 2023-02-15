<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function shop_basic_info_main ($uri)
{
    $pos = strpos ($uri, '/') ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    
    if ($folder == '')
    {
        echo 'uri is empty, should create record with POST method' ;
        exit ;
    }
    
    if ($_SERVER ['REQUEST_METHOD'] == 'GET')
    {
        get_shop_basic_info ($folder) ;
        exit ;
    }
    
    if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
    {
        patch_shop_basic_info ($folder) ;
        exit ;
    }
    
    $result = array () ;
    $result ['status'] = 'success' ;
    $result ['message'] = 'here is basic_info_main function' ;
    $result ['uri'] = $folder ;
    echo json_encode ($result) ;
    exit ;
}

function get_shop_basic_info ($uri)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    if (intval ($uri) != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    $ret_account_info = [] ;
    $ret_account_info ['status'] = '' ;
    $ret_account_info ['message'] = '' ;
    require_once (__DIR__ . '/../account/account_fns.php') ;
    $ret = sql_query_reading_account_info ($sn_from_jwt, $ret_account_info) ;
    if ($ret === -1)
    {
        echo json_encode ($ret_account_info) ;
        exit ;
    }

    sql_query_reading_shop_basic_info ($sn_from_jwt, $result) ;
    $result ['email_verified_status'] = $ret_account_info ['email_verified_status'] ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_shop_basic_info ($sn, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_1801
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1801" . mysqli_connect_error ()) ;
        }
        $query = '
        SELECT
            `name`
            , `tel`
            , `address`
            , `email_notification`
        FROM `basic_info`
        WHERE `basic_info_serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $sn) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        //$result ['record_number'] = $stmt -> num_rows ;
        
        $stmt -> bind_result ($col_name, $col_tel, $col_address, $col_email_notification) ;
        
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        $result ['name'] = htmlspecialchars ($col_name) ;
        $result ['tel'] = htmlspecialchars ($col_tel) ;
        $result ['address'] = htmlspecialchars ($col_address) ;
        $result ['email_notification'] = $col_email_notification ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
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

function patch_shop_basic_info ($uri)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    if (intval ($uri) != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    $request_body = file_get_contents ('php://input') ;
    // 要寫判斷 === false 還有 ! isset 還有 empty ( 應該要弄成一個 function 哪！ )

    $data = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array
    $data ['sn'] = $sn_from_jwt ;

    if (! (isset ($data ['name'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = '沒有欄位 => 姓名' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (empty ($data ['name']))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = '欄位空白 => 姓名' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (! (isset ($data ['tel'])))
    {
        $data ['tel'] = null ;
    }

    if (! (isset ($data ['address'])))
    {
        $data ['address'] = null ;
    }

    if (! (isset ($data ['email_notification'])))
    {
        $data ['email_notification'] = null ;
    }

    if ($data ['email_notification'] === 1)
    {
        $ret_account_info = [] ;
        $ret_account_info ['status'] = '' ;
        $ret_account_info ['message'] = '' ;
        require_once (__DIR__ . '/../account/account_fns.php') ;
        $ret = sql_query_reading_account_info ($sn_from_jwt, $ret_account_info) ;
        if ($ret === -1)
        {
            echo json_encode ($ret_account_info) ;
            exit ;
        }
        if ($ret_account_info ['email_verified_status'] === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'E-mail not verified, can not set E-mail notification' ;
            echo json_encode ($result) ;
            exit ;
        }
    }

    sql_query_updating_shop_basic_info ($data, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_shop_basic_info ($data, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    try
    {
        // #cn_1802
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1802" . mysqli_connect_error ()) ;
        }
        $query = '
        UPDATE `basic_info`
        SET
            `name` = ?
            , `tel` = ?
            , `address` = ?
            , `email_notification` = ?
        WHERE `basic_info_serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'sssii'
            , $data ['name']
            , $data ['tel']
            , $data ['address']
            , $data ['email_notification']
            , $data ['sn']) ;
        if (! ($stmt -> execute ()))
        {
            // do not work
            $result ['status'] = 'failed' ;
            $result ['message'] = 'stmt execute failed' ;
            return -1 ;
        }

        sscanf ($db -> info, '%*s%*s%d%*s%d', $matched_rows, $changed_rows) ;
        if ($matched_rows === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'no item matched' ;
            return -1 ;
        }
        if ($changed_rows === 0)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            return -1 ;
        }
        $stmt -> close () ;
        $db -> close () ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;
        return 1 ;
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
