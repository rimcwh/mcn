<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function shop_shopping_cart_main ($uri)
{
    $path_segment = parse_uri_one_layer ($uri) ;
    
    if (0 == strcmp ('', $path_segment))
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'POST')
        {
            post_shopping_cart () ;
        }
        exit ;
    }
    else
    {
        $segment1 = $path_segment ;
        $path_segment = parse_uri_one_layer ($uri) ;
        if (0 != strcmp ('', $path_segment))
        {
            $segment2 = $path_segment ;
            if ($_SERVER ['REQUEST_METHOD'] == 'DELETE')
            {
                delete_shopping_cart_item ($segment1, $segment2) ;
            }
        }
        else
        {
            if ($_SERVER ['REQUEST_METHOD'] == 'GET')
            {
                get_shopping_cart ($segment1) ;
            }
        }
    }
    
    exit ;
}

function post_shopping_cart ()
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;*/
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;
    
    $request_body = file_get_contents ('php://input') ;
    
    if ($request_body === false)
    {
        $result = array (
            'status' => 'failed',
            'message' => 'get data error.'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    if (empty ($request_body))
    {
        $result = array (
            'status' => 'failed',
            'message' => 'data not found.'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    // 把 php://input 從 string 轉成一個 associative array
    $data = json_decode ($request_body, true) ;
    
    if (! (isset ($data ['book_sn'])))
    {
        $result = array (
            'status' => 'failed',
            'message' => '找不到欄位：書籍 ID'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    if (empty ($data ['book_sn']))
    {
        $result = array (
            'status' => 'failed',
            'message' => '欄位：書籍 ID => 沒有輸入資料'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    if (! (isset ($data ['number'])))
    {
        $result = array (
            'status' => 'failed',
            'message' => '找不到欄位：數量'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    if (empty ($data ['number']))
    {
        $result = array (
            'status' => 'failed',
            'message' => '欄位：數量 => 沒有輸入資料'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    $data ['number'] = (int) $data ['number'] ;
    
    if ($data ['number'] < 0)
    {
        $result = array (
            'status' => 'failed',
            'message' => '欄位：數量 => 數值小於零'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    if ($data ['number'] > 9)
    {
        $result = array (
            'status' => 'failed',
            'message' => '欄位：數量 => 數值太大'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    
    
    $data ['sn'] = $sn_from_jwt ;
    
    // 做 sql
    sql_query_creating_shopping_cart ($data) ;
}

function sql_query_creating_shopping_cart ($data)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    // 去參考 Fatal error: Uncaught exception 'mysqli_sql_exception' with message 'No index used in query/prepared statement'
    // https://stackoverflow.com/questions/5580039/fatal-error-uncaught-exception-mysqli-sql-exception-with-message-no-index-us

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    try
    {
        // #cn_1701
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1701" . mysqli_connect_error ()) ;
        }
        $query = 'SELECT * FROM shopping_cart WHERE orderer_id = ? AND book_id = ?' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $data ['sn'], $data ['book_sn']) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $result = array (
            'status' => '',
            'message' => ''
        ) ;
        
        if ($stmt -> num_rows > 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = '購物車已有書籍' ;
            echo json_encode ($result) ;
            exit ;
        }
        
        $query = 'INSERT INTO shopping_cart (orderer_id, book_id, number) VALUES (?, ?, ?)' ;
        $stmt = $db -> prepare ($query) ;
        $limit = 10000 ;
        $stmt -> bind_param ('iii', 
                             $data ['sn'],
                             $data ['book_sn'],
                             $data ['number']) ;
        $stmt -> execute () ;
        
        if ($stmt -> affected_rows > 0)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'created ok' ;
        }
        else
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'created failed' ;
        }
        echo json_encode ($result) ;
        exit ;
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

function get_shopping_cart ($uri)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if (intval ($uri) != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    // 做 sql
    sql_query_reading_shopping_cart ($uri) ;
    // 或許要這樣用 sql_query_reading_shopping_cart (intval ($uri)) ; // 轉 int
}

function sql_query_reading_shopping_cart ($sn)
{
    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    // 去參考 Fatal error: Uncaught exception 'mysqli_sql_exception' with message 'No index used in query/prepared statement'
    // https://stackoverflow.com/questions/5580039/fatal-error-uncaught-exception-mysqli-sql-exception-with-message-no-index-us

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    try
    {
        // #cn_1702
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1702" . mysqli_connect_error ()) ;
        }
        $query = '
        SELECT 
            sc.`shopping_cart_serial_number`
            , bo.`title`
            , bo.`price`
            , sc.`number`
            , sc.`book_id`
        FROM `shopping_cart` AS sc 
        INNER JOIN `book_info` AS bo 
            ON bo.`book_serial_number` = sc.`book_id` 
        WHERE sc.`orderer_id` = ?
        ' ;
        
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $sn) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $result ['record_number'] = $stmt -> num_rows ;
        
        $stmt -> bind_result ($col_sc_id, $col_title, $col_price, $col_number, $col_book_id) ;
        
        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $result ['sc_id' . $i] = $col_sc_id ;
            $result ['title' . $i] = $col_title ;
            $result ['price' . $i] = $col_price ;
            $result ['number' . $i] = $col_number ;
            $result ['book_id' . $i] = $col_book_id ;
            $i ++ ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        $result ['status'] = 'success' ;
        echo json_encode ($result) ;
        exit ;
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

function delete_shopping_cart_item ($user_id, $sc_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    if ($user_id != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }
    $result = array () ;
    sql_query_deleting_shopping_cart_item ($user_id, $sc_id, $result) ;

    $result ['qq'] = 'bmbp' ;
    echo json_encode ($result) ;
}

function sql_query_deleting_shopping_cart_item ($user_id, $sc_id, & $data)
{
    /*
     *
     */
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_1703
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1703" . mysqli_connect_error ()) ;
        }

        $query = '
        SELECT
            `book_id`
        FROM `shopping_cart`
        WHERE `orderer_id` = ? AND `shopping_cart_serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $user_id, $sc_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        if ($stmt -> num_rows != 1)
        {
            $data ['status'] = 'failed' ;
            $data ['message'] = 'no matching data' ;
            return ;
        }

        $stmt -> free_result () ;
        $stmt -> close () ;

        $query = '
        DELETE
        FROM `shopping_cart`
        WHERE `orderer_id` = ? AND `shopping_cart_serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $user_id, $sc_id) ;
        $stmt -> execute () ;

        if ($stmt -> affected_rows <= 0)
        {
            throw new \Exception ("Could not delete record at database table shopping_cart" . mysqli_connect_error ()) ;
            exit ;
        }

        $db -> close () ;
        
        $data ['status'] = 'success' ;
        $data ['message'] = 'query ok' ;
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
