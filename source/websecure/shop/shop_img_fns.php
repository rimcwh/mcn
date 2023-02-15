<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;

function shop_img_main ($uri)
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
    
    if (0 == strcmp ('thumbnail', $folder))
    {
        get_shop_img_thumbnail ($uri) ;
        exit ;
    }
    
    if (0 == strcmp ('cover', $folder))
    {
        get_shop_img_cover ($uri) ;
        exit ;
    }
}

function get_shop_img_thumbnail ($uri)
{
    $ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    if ($ret ['status'] === 'failed')
    {
        exit ;
    }
    if ($uri == "")
    {
        //echo 'uri is empty bye' ;
        exit ;
    }
    
    $fn = sql_query_reading_thumbnail_filename (intval ($uri)) ;
    
    $full_fn = '/var/webupload/' . $fn ;
    
    header ("Content-type: image/webp") ;
    readfile ($full_fn) ;
    exit ;
}

function sql_query_reading_thumbnail_filename ($sn)
{
    //ob_start () ;
    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    //ob_end_clean () ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    
    /*$db_server = 'localhost' ;
    $db_user_name = 'r_shop' ;
    $db_password = 'qnqnrshop' ;
    $db_name = 'shop' ;*/
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    try
    {
        // #cn_1601
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1601" . mysqli_connect_error ()) ;
        }
        $query = 'SELECT `thumbnail_filename` FROM book_info WHERE book_serial_number = ?' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $sn) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        if ($stmt -> num_rows <= 0)
        {
            // error
            exit ;
        }
        
        $stmt -> bind_result ($col_thumbnail_filename) ;
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        return $col_thumbnail_filename ;
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

function get_shop_img_cover ($uri)
{
    $ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    if ($ret ['status'] === 'failed')
    {
        exit ;
    }
    if ($uri == "")
    {
        //echo 'uri is empty bye' ;
        exit ;
    }
    
    $fn = sql_query_reading_cover_filename (intval ($uri)) ;
    
    $full_fn = '/var/webupload/' . $fn ;
    
    header ("Content-type: image/webp") ;
    readfile ($full_fn) ;
    exit ;
}

function sql_query_reading_cover_filename ($sn)
{
    //ob_start () ;
    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    //ob_end_clean () ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    
    /*$db_server = 'localhost' ;
    $db_user_name = 'r_shop' ;
    $db_password = 'qnqnrshop' ;
    $db_name = 'shop' ;*/
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    try
    {
        // #cn_1602
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1602" . mysqli_connect_error ()) ;
        }
        $query = 'SELECT `cover_filename` FROM book_info WHERE book_serial_number = ?' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $sn) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        if ($stmt -> num_rows <= 0)
        {
            // error
            exit ;
        }
        
        $stmt -> bind_result ($col_cover_filename) ;
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        return $col_cover_filename ;
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
