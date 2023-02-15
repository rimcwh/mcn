<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/jwt_fns_for_csrf.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function chat_public_room_main ($uri)
{
    if ($_SERVER ['REQUEST_METHOD'] == 'GET')
    {
        get_chat_public_room ($uri) ;
    }
    if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
    {
        
    }
    if ($_SERVER ['REQUEST_METHOD'] == 'POST')
    {
        post_chat_public_room ($uri) ;
    }
    if ($_SERVER ['REQUEST_METHOD'] === 'DELETE')
    {
        delete_chat_public_room (intval ($uri)) ;
    }
}

function get_chat_public_room ($uri)
{
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;
    
    $data = array (
        'set-start' => 'n',
        'set-limit' => 'n'
    ) ;
    $request_body = file_get_contents ('php://input') ;
    // 要寫判斷 === false 還有 ! isset 還有 empty ( 應該要弄成一個 function 哪！ )
    if ($request_body === false)
    {
        
    }
    else
    {
        $arr = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array
        if (!($arr === null)) // 不知道為甚麼有時候會失敗......
        {
            if (array_key_exists ('start', $arr))
            {
                $data ['set-start'] = 'y' ;
                $data += ['start' => $arr ['start']] ;
            }
            if (array_key_exists ('limit', $arr))
            {
                $data ['set-limit'] = 'y' ;
                $data += ['limit' => $arr ['limit']] ;
            }
        }
    }
    
    /*$sn_from_jwt = $ret ['jwt_decode'] -> sn ;
    if (! (strval ($sn_from_jwt) === $uri))
    {
        // error
        $result = array (
            'status' => 'failed',
            'message' => 'serial number not matched.'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }*/

    sql_query_reading_chat_public_room ($data) ;
}

function sql_query_reading_chat_public_room ($data)
{
    require_once (__DIR__ . '/../db_link/dbconnect_r_chat.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_chat ($db_server, $db_user_name, $db_password, $db_name) ;
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    // 如果用 report_all 會讓下面的 select query 報錯 No index used in query/prepared statement
    // 去參考 https://stackoverflow.com/questions/5580039/fatal-error-uncaught-exception-mysqli-sql-exception-with-message-no-index-us
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    try
    {
        // #cn_1411
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1411" . mysqli_connect_error ()) ;
        }
        $query = 'SELECT * FROM public_chat_room_log' ;
        $query = '
        SELECT
        public_chat_room_log.message_id
        , public_chat_room_log.sent_from
        , account_setting.nickname
        , public_chat_room_log.message
        , public_chat_room_log.time
        FROM public_chat_room_log
        INNER JOIN account_setting ON public_chat_room_log.sent_from = account_setting.serial_number ' ;
        $query = '
        SELECT
            p.message_id
            , p.sent_from
            , a.nickname
            , p.message
            , p.time
        FROM public_chat_room_log AS p
        INNER JOIN account_setting AS a
            ON p.sent_from = a.serial_number
        ORDER BY p.message_id' ;
        $query_result = $db -> query ($query) ;
        
        $result = array (
            'status' => 'success',
        ) ;
        $i = 0 ;
        
        if ($query_result -> num_rows > 0)
        {
            $result += ['record_number' => $query_result -> num_rows] ;
            while ($row = $query_result -> fetch_assoc ())
            {
                $result += ['msg_id' . $i => $row ['message_id']] ;
                $result += ['sent_from' . $i => $row ['sent_from']] ;
                $result += ['nickname' . $i => htmlspecialchars ($row ['nickname'])] ;
                $result += ['message' . $i => htmlspecialchars ($row ['message'])] ;
                //$result += ['message' . $i => $row ['message']] ; // 模擬 xss 攻擊
                $result += ['time' . $i => $row ['time']] ;
                $i ++ ;
            }
        }
        echo json_encode ($result) ;
        exit ;
        
        /*$stmt = $db -> prepare ($query) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        $rows_number = 'Number of accounts: ' . $stmt -> num_rows ;*/
        /*if ($stmt -> num_rows === 0)
        {
            http_response_code (403) ;
            $result = array (
                'status' => 'failed',
                'message' => '沒有這個帳號的資料。'
            ) ;
            echo json_encode ($result) ;
            exit ;
        }*/
        $stmt -> bind_result ($col_serial_number, $col_sent_from, $col_message, $col_time) ;
        
        $result = array (
            'status' => 'success',
        ) ;
        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $result += ['sn' . $i => $col_serial_number ] ;
            $result += ['sent_from' . $i => $col_sent_from ] ;
            $result += ['message' . $i => $col_message ] ;
            $result += ['time' . $i => $col_time ] ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        /*$result = array (
            'status' => 'success',
            'message' => 'ok',
            'serial_number' => $col_serial_number,
            'nickname' => htmlspecialchars ($col_nickname),
            //'nickname' => $col_nickname,
            //'theme' => $col_theme,
            'description' => $col_description,
            'allow_info_public' => $col_allow_info_public,
            'allow_search_id' => $col_allow_search_id,
            'show_message_bottom_to_top' => $col_show_message_bottom_to_top,
        ) ;*/
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

function post_chat_public_room ($uri)
{
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;
    
    $request_body = file_get_contents ('php://input') ;
    //echo $request_body ;
    
    // 要寫判斷 === false 還有 ! isset 還有 empty ( 應該要弄成一個 function 哪！ )
    if ($request_body === false)
    {
        $result = array (
            'status' => 'failed',
            'message' => 'data not found.'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    else
    {
        $arr = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array
        if (array_key_exists ('sn', $arr))
        {
            require_once (__DIR__ . '/../common_function.php') ;
            \CommonFns\check_sn_is_same ($sn_from_jwt, $arr ['sn']) ;
            if (array_key_exists ('start', $arr))
            {
                $data ['set-start'] = 'y' ;
                $data += ['start' => $arr ['start']] ;
            }
        }
        else
        {
            $result = array (
                'status' => 'failed',
                'message' => 'serial number not found.'
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
        if (! (array_key_exists ('message_content', $arr)))
        {
            $result = array (
                'status' => 'failed',
                'message' => 'message content not found.'
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
    }
    
    if ($arr ['message_content'] === '')
    {
        $result = array (
            'status' => 'failed',
            'message' => 'message content empty'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    
    // uri 沒有傳 user_id 進來，不用檢查 jwt 的 sn 跟 uri 相等

    sql_query_creating_chat_public_room ($arr) ;
}

function sql_query_creating_chat_public_room ($data)
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
        // #cn_1412
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1412 " . mysqli_connect_error ()) ;
        }
        $query = 'INSERT INTO public_chat_room_log (sent_from, message) VALUES (?, ?)' ;
        $stmt = $db -> prepare ($query) ;
        $sn = intval ($data ['sn']) ;
        $stmt -> bind_param ('is', $sn, $data ['message_content']) ;
        $stmt -> execute () ;
        
        if ($stmt -> affected_rows > 0)
        {
            $result = array (
                'status' => 'success',
                'message' => 'created ok',
            ) ;
        }
        else
        {
            $result = array (
                'status' => 'failed',
                'message' => 'created failed',
            ) ;
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

function delete_chat_public_room ($message_id)
{
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    sql_query_deleting_chat_public_room ($sn_from_jwt, $message_id, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_deleting_chat_public_room ($serial_number, $message_id, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_chat.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_chat ($db_server, $db_user_name, $db_password, $db_name) ;
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_1413
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1413" . mysqli_connect_error ()) ;
        }

        $ret = query_deleting_chat_public_room ($db, $serial_number, $message_id, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $db -> close () ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;

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

function query_deleting_chat_public_room (& $db, $serial_number, $message_id, & $result)
{
    $query = '
    DELETE FROM `public_chat_room_log`
    WHERE
        `sent_from` = ?
        AND
        `message_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('ii', $serial_number, $message_id) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $stmt -> close () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;

    return 1 ;
}

?>
