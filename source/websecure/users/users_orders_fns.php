<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function users_orders_main ($uri, $sn_from_uri)
{
    if ($uri === '')
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'POST')
        {
            post_users_orders ($sn_from_uri) ;
            exit ;
        }
    }
    return ;
}

function post_users_orders ($sn_from_uri)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    if ($sn_from_uri != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'access denied' ;
        echo json_encode ($result) ;
        return -1 ;
    }

    $temp = file_get_contents ('php://input') ;

    if ($temp === false)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'get data error.' ;
        echo json_encode ($result) ;
        return -1 ;
    }

    if (empty ($temp))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'data not found.' ;
        echo json_encode ($result) ;
        return -1 ;
    }

    // 把 php://input 從 string 轉成一個 associative array
    $request_body = json_decode ($temp, true) ;

    if (! (isset ($request_body ['order_from'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not set order_from field!' ;
        echo json_encode ($result) ;
        return -1 ;
    }

    if ($request_body ['order_from'] === 'shopping_cart')
    {
        // test start cut
        /*$ret = query_reading_related_account_info ($sn_from_uri, $result) ;
        echo json_encode ($result) ;
        return 1 ;*/
        // test end cut
        sql_query_creating_order_from_shopping_cart ($sn_from_uri, $request_body, $result) ;
        echo json_encode ($result) ;
        return 1 ;
    }
}

function send_notification ($order_serial_number, & $s_result)
{
    $s_result ['before_time'] = date ('Y/m/d H:i:s') ;

    $exe_result = '' ;

    $shell_cmd = 'php ../../../../websecure/mis_fns/send_mail_when_order_checkout.php ' . $order_serial_number . ' > /dev/null 2>&1 &' ;
    //$shell_cmd = 'php ../../../../websecure/mis_fns/send_mail_when_order_checkout.php ' . $order_serial_number ;
    
    //exec ($shell_cmd, $exe_result) ;
    exec ($shell_cmd) ;

    $s_result ['exe_result'] = $exe_result ;

    $s_result ['after_time'] = date ('Y/m/d H:i:s') ;
}

function sql_query_creating_order_from_shopping_cart ($serial_number, & $data, & $result)
{
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
        // #cn_1901
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1901" . mysqli_connect_error ()) ;
        }

        $col_sc = [] ;
        $ret = query_reading_shopping_cart ($db, $serial_number, $col_sc, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $col_oci = [] ;
        $col_oci ['order_serial_number'] = 0 ;
        $col_oci ['name'] = $data ['name'] ;
        $col_oci ['tel'] = $data ['tel'] ;
        $col_oci ['address'] = $data ['address'] ;
        $col_oci ['total'] = $col_sc ['total'] ;
        $ret = query_creating_order_contact_info ($db, $serial_number, $col_oci, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $col_od = [] ;
        for ($i = 0 ; $i < $col_sc ['record_number'] ; $i ++)
        {
            $col_od ['book_id'] = $col_sc ['book_id' . $i] ;
            $col_od ['title'] = $col_sc ['title' . $i] ;
            $col_od ['number'] = $col_sc ['number' . $i] ;
            $col_od ['price'] = $col_sc ['price' . $i] ;
            $ret = query_creating_order_detail ($db, $col_oci ['order_serial_number'], $col_od, $result) ;
        }

        $ret = query_deleting_shopping_cart ($db, $serial_number, $result) ;
        $db -> close () ;

        $send_result = [] ;
        send_notification ($col_oci ['order_serial_number'], $send_result) ;

        $result ['send_result'] = $send_result ;

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

function query_reading_shopping_cart (& $db, $serial_number, & $col, & $result)
{
    $query = '
    SELECT
        bi.`provider`
        , sc.`book_id`
        , bi.`title`
        , bi.`price`
        , sc.`number`
    FROM `shopping_cart` AS sc
    INNER JOIN `book_info` AS bi
    ON bi.`book_serial_number` = sc.`book_id`
    WHERE sc.`orderer_id` = ?
    ' ;
    
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;
    
    $col ['record_number'] = $stmt -> num_rows ;

    if ($stmt -> num_rows <= 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'shopping cart is empty!' ;
        return -1 ;
    }
    
    $stmt -> bind_result ($col_provider, $col_book_id, $col_title, $col_price, $col_number) ;

    $col ['total'] = 0 ;
    $i = 0 ;
    while ($stmt -> fetch ())
    {
        $col ['provider' . $i] = $col_provider ;
        $col ['book_id' . $i] = $col_book_id ;
        $col ['title' . $i] = $col_title ;
        $col ['price' . $i] = $col_price ;
        $col ['number' . $i] = $col_number ;
        $col ['total'] += $col_price * $col_number ;
        $i ++ ;
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
    return 1 ;
}

function query_creating_order_contact_info (& $db, $serial_number, & $col, & $result)
{
    $query = '
    INSERT INTO `order_contact_info`
    (
        orderer_id
        , name
        , tel
        , address
        , total
    )
    VALUES (?, ?, ?, ?, ?)' ;
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('isssi'
        , $serial_number
        , $col ['name']
        , $col ['tel']
        , $col ['address']
        , $col ['total']) ;

    $stmt -> execute () ;
    if ($stmt -> affected_rows <= 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create record, but num_rows equal zero, file: users_orders_fns.php, in func query_creating_order_contact_info' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }

    $col ['order_serial_number'] = $db -> insert_id ; // Returns the ID generated by an INSERT or UPDATE query on a table with a column having the AUTO_INCREMENT attribute.

    $stmt -> free_result () ;
    $stmt -> close () ;
    return 1 ;
}

function query_creating_order_detail (& $db, $order_serial_number, & $col, & $result)
{
    $query = '
    INSERT INTO `order_detail` (
        `order_serial_number`
        , `book_id`
        , `title`
        , `number`
        , `price`)
    VALUES (?, ?, ?, ?, ?)
    ' ;
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('iisii', 
        $order_serial_number
        , $col ['book_id']
        , $col ['title']
        , $col ['number']
        , $col ['price']) ;
    $stmt -> execute () ;
    if ($stmt -> affected_rows <= 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create record, but num_rows equal zero, file: users_orders_fns.php, in func query_creating_order_detail' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }

    $stmt -> close () ;

    return 1 ;
}

function query_deleting_shopping_cart (& $db, $serial_number, & $result)
{
    $query = '
    DELETE FROM `shopping_cart` 
    WHERE `orderer_id` = ?
    ' ;
    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;

    $stmt -> execute () ;
    if ($stmt -> affected_rows <= 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do delete record, but num_rows equal zero, file: users_orders_fns.php, in func query_deleting_shopping_cart' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
        return -1 ;
    }

    $stmt -> close () ;

    return 1 ;
}

function query_reading_related_account_info ($serial_number, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    $col = [] ;

    try
    {
        // #cn_1901
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1901" . mysqli_connect_error ()) ;
        }

        $query = '
SELECT boi.`provider`, bai.`name`, bai.`email_notification`
FROM `shopping_cart` AS sc
INNER JOIN `book_info` AS boi
    ON boi.`book_serial_number` = sc.`book_id`
INNER JOIN `basic_info` AS bai
    ON boi.`provider` = bai.`basic_info_serial_number`
WHERE sc.`orderer_id` = ?
GROUP BY boi.`provider`
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $serial_number) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $col ['record_number'] = $stmt -> num_rows ;

        if ($stmt -> num_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'shopping cart is empty!' ;
            return -1 ;
        }
        
        $stmt -> bind_result ($col_provider, $col_name, $col_email_notification) ;

        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $col ['provider' . $i] = $col_provider ;
            $col ['name' . $i] = $col_name ;
            $col ['email_notification' . $i] = $col_email_notification ;
            $i ++ ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;

        $db -> close () ;

        $result ['col'] = $col ;
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

    $col_2 = [] ;
    $col_3 = [] ;
    require_once (__DIR__ . '/../db_link/dbconnect_w_account.php') ;
    dbconnect_w_account ($db_server, $db_user_name, $db_password, $db_name) ;
    try
    {
        // #cn_1901
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1901" . mysqli_connect_error ()) ;
        }

        $query = '
SELECT
    `serial_number`
    , `email`
    , `email_verified_status`
FROM `account`
WHERE `serial_number` = ?
;' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $serial_number) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $col_2 ['record_number'] = $stmt -> num_rows ;

        if ($stmt -> num_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'shopping cart is empty!' ;
            return -1 ;
        }
        
        $stmt -> bind_result ($col_serial_number, $col_email, $col_email_verified_status) ;

        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $col_2 ['serial_number' . $i] = $col_serial_number ;
            $col_2 ['email' . $i] = $col_email ;
            $col_2 ['email_verified_status' . $i] = $col_email_verified_status ;
            $i ++ ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;

        //$db -> close () ;

        $result ['col_2'] = $col_2 ;

        $array_data = [] ;
        for ($i = 0 ; $i < $col ['record_number'] ; $i ++)
        {
            $array_data [$i] = & $col ['provider' . $i] ;
        }
        $param_data = '?' ;
        for ($i = 1 ; $i < $col ['record_number'] ; $i ++)
        {
            $param_data .= ',?' ;
        }
        $type_data = '' ;
        for ($i = 0 ; $i < $col ['record_number'] ; $i ++)
        {
            $type_data .= 'i' ;
        }

        $query = '
SELECT
    `serial_number`
    , `email`
    , `email_verified_status`
FROM `account`
WHERE `serial_number` IN (' . $param_data . ')
' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ($type_data, ...$array_data) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $col_3 ['record_number'] = $stmt -> num_rows ;

        if ($stmt -> num_rows <= 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'shopping cart is empty!' ;
            return -1 ;
        }
        
        $stmt -> bind_result ($col_serial_number, $col_email, $col_email_verified_status) ;

        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $col_3 ['serial_number' . $i] = $col_serial_number ;
            $col_3 ['email' . $i] = $col_email ;
            $col_3 ['email_verified_status' . $i] = $col_email_verified_status ;
            $i ++ ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;

        $db -> close () ;

        $result ['col_3'] = $col_3 ;

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

?>
