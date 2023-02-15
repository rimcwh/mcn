<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function shop_my_order_main ($uri)
{
    $path_segment = parse_uri_one_layer ($uri) ;
    $segment1 = $path_segment ;

    if (intval ($segment1) > 0)
    {
        if ($uri == '')
        {
            if ($_SERVER ['REQUEST_METHOD'] == 'GET')
            {
                get_my_order (intval ($segment1)) ;
            }
            exit ;
        }
        $path_segment = parse_uri_one_layer ($uri) ;
        $segment2 = $path_segment ;
        if ($uri == '')
        {
            exit ;
        }
        $path_segment = parse_uri_one_layer ($uri) ;
        $segment3 = $path_segment ;
        if ($uri == '')
        {
            if ($_SERVER ['REQUEST_METHOD'] == 'DELETE')
            {
                delete_my_order_item (intval ($segment1), intval ($segment2), intval ($segment3)) ;
                exit ;
            }
            exit ;
        }
    }
    $result = array () ;
    $result ['status'] = 'byebye' ;
    echo json_encode ($result) ;
}

function get_my_order ($user_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if ($user_id != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    sql_query_reading_my_order ($result, $sn_from_jwt) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_my_order (& $data, $user_id)
{
    /*
        SELECT
            oci.`order_serial_number`
            , oci.`order_date`
            , oci.`name`
            , oci.`tel`
            , oci.`address`
            , oci.`total`
            , od.`book_id`
            , od.`title`
            , od.`number`
            , od.`price`
        FROM `order_detail` AS od
        INNER JOIN `order_contact_info` AS oci
        ON od.`order_serial_number` = oci.`order_serial_number`
        WHERE oci.`order_serial_number` IN (
            SELECT `order_serial_number`
            FROM `order_contact_info`
            WHERE `orderer_id` = ?
        )
        ORDER BY oci.`order_date` DESC, od.`book_id` DESC
     */ 
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
        // #cn_2001
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2001" . mysqli_connect_error ()) ;
        }
        $query = '
        SELECT
            oci.`order_serial_number`
            , oci.`order_date`
            , oci.`name`
            , oci.`tel`
            , oci.`address`
            , oci.`total`
            , od.`book_id`
            , od.`title`
            , od.`number`
            , od.`price`
        FROM `order_detail` AS od
        INNER JOIN `order_contact_info` AS oci
        ON od.`order_serial_number` = oci.`order_serial_number`
        WHERE oci.`order_serial_number` IN (
            SELECT `order_serial_number`
            FROM `order_contact_info`
            WHERE `orderer_id` = ?
        )
        ORDER BY oci.`order_date` DESC, od.`book_id` DESC
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $data = array () ;
        $data ['record_number'] = $stmt -> num_rows ;
        
        $stmt -> bind_result (
            $col_order_serial_number, 
            $col_order_date, 
            $col_name, 
            $col_tel, 
            $col_address, 
            $col_total,
            $col_book_id,
            $col_title,
            $col_number,
            $col_price
        ) ;

        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $data ['order_serial_number' . $i] = $col_order_serial_number ;
            $data ['order_date' . $i] = $col_order_date ;
            $data ['name' . $i] = htmlspecialchars ($col_name) ;
            $data ['tel' . $i] = htmlspecialchars ($col_tel) ;
            $data ['address' . $i] = htmlspecialchars ($col_address) ;
            $data ['total' . $i] = $col_total ;
            $data ['book_id' . $i] = $col_book_id ;
            $data ['title' . $i] = htmlspecialchars ($col_title) ;
            $data ['number' . $i] = $col_number ;
            $data ['price' . $i] = $col_price ;
            $i ++ ;
        }
        //echo json_encode ($data) ;
        //exit ;
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        $data ['status'] = 'success' ;
        $data ['message'] = 'query ok' ;
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

function delete_my_order_item ($user_id, $order_id, $book_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if ($user_id != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    sql_query_deleting_my_order_item ($result, $sn_from_jwt, $order_id, $book_id) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_deleting_my_order_item (& $data, $user_id, $order_id, $book_id)
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
        // #cn_2002
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2002" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT
                oci.`order_serial_number`
                , oci.`orderer_id`
                , od.`book_id`
            FROM `order_contact_info` AS oci
            INNER JOIN `order_detail` AS od
                ON oci.`order_serial_number` = od.`order_serial_number`
            WHERE oci.`orderer_id` = ?
                AND oci.`order_serial_number` = ?
                AND od.`book_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('iii', $user_id, $order_id, $book_id) ;
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

        $data ['status'] = 'testing' ;
        $data ['message'] = 'number rows != 1, here is in query deleting' ;

        // before deleting query count
        $query = '
            SELECT COUNT(`order_serial_number`)
            FROM `order_detail`
            WHERE `order_serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $order_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $stmt -> bind_result ($col_count_before) ;
        $stmt -> fetch () ;

        //$data ['order_count_before'] = $col_count ;

        $stmt -> free_result () ;
        $stmt -> close () ;

        // delete order_detail data
        $query = '
            DELETE FROM `order_detail`
            WHERE `order_serial_number` = ?
                AND `book_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $order_id, $book_id) ;
        $stmt -> execute () ;

        if ($stmt -> affected_rows <= 0)
        {
            throw new \Exception ("Could not delete record at database table order_detail" . mysqli_connect_error ()) ;
            exit ;
        }

        $stmt -> free_result () ;
        $stmt -> close () ;

        // after deleting query count
        $query = '
            SELECT COUNT(`order_serial_number`)
            FROM `order_detail`
            WHERE `order_serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $order_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        $stmt -> bind_result ($col_count_after) ;
        $stmt -> fetch () ;

        //$data ['order_count_after'] = $col_count ;

        $stmt -> free_result () ;
        $stmt -> close () ;

        if ($col_count_after > 0)
        {
            // recount total price
            $query = '
                SELECT
                    `order_serial_number`
                    , `book_id`
                    , `number`
                    , `price`
                FROM `order_detail`
                WHERE `order_serial_number` = ?
            ' ;

            $stmt = $db -> prepare ($query) ;
            $stmt -> bind_param ('i', $order_id) ;
            $stmt -> execute () ;
            $stmt -> store_result () ;

            $stmt -> bind_result (
                $col_order_serial_number
                , $col_book_id
                , $col_number
                , $col_price
            ) ;

            $recount_total = 0 ;
            while ($stmt -> fetch ())
            {
                $recount_total += $col_price * $col_number ;
            }

            //$data ['recount_total'] = $recount_total ;

            $stmt -> free_result () ;
            $stmt -> close () ;

            // update new total to database
            $query = '
                UPDATE `order_contact_info`
                SET
                    `total` = ?
                WHERE `order_serial_number` = ?
                    AND `orderer_id` = ?
            ' ;

            $stmt = $db -> prepare ($query) ;
            $stmt -> bind_param ('iii', $recount_total, $order_id, $user_id) ;
            $stmt -> execute () ;
            $stmt -> store_result () ;

            if ($stmt -> affected_rows != 1)
            {
            }

            $stmt -> free_result () ;
            $stmt -> close () ;

            $data ['status'] = 'success' ;
            $data ['message'] = 'delete ok' ;
            return ;
        }

        // delete order_detail data
        $query = '
            DELETE FROM `order_contact_info`
            WHERE `order_serial_number` = ?
                AND `orderer_id` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $order_id, $user_id) ;
        $stmt -> execute () ;

        if ($stmt -> affected_rows <= 0)
        {
            throw new \Exception ("Could not delete record at database table order_contact_info" . mysqli_connect_error ()) ;
            exit ;
        }

        $stmt -> free_result () ;
        $stmt -> close () ;

        $db -> close () ;

        $data ['status'] = 'success' ;
        $data ['message'] = 'delete whole order ok' ;

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
