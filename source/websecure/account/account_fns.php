<?php
require_once (__DIR__ . '/../db_link/dbconnect_r_account.php') ;
require_once (__DIR__ . '/../db_link/dbconnect_w_account.php') ;
function get_account_info ($sn)
{
    //header ('Access-Control-Allow-Origin: *') ;
    header ('Content-Type: application/json; charset=UTF-8') ;
    header ('Access-Control-Allow-Methods: GET') ;
    header ('Access-Control-Max-Age: 3600') ;
    header ('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With') ;

    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    sql_query_reading_account_info ($sn, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_account_info ($sn, & $result)
{
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_account ($db_server, $db_user_name, $db_password, $db_name) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    try
    {
        // #cn_1301
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1301" . mysqli_connect_error ()) ;
        }
        $query = '
        SELECT
            `account_id`
            , `email`
            , `email_verified_status`
            , `theme`
        FROM `account`
        WHERE `serial_number` = ?
        ' ;
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
            return -1 ;
        }
        $stmt -> bind_result ($col_account_id, $col_email, $col_email_verified_status, $col_theme) ;
        
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;
        $result ['account_id'] = $col_account_id ;
        $result ['email'] = $col_email ;
        $result ['email_verified_status'] = $col_email_verified_status ;
        $result ['theme'] = $col_theme ;
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
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function patch_account_info ($sn)
{
    /*header ('Access-Control-Allow-Origin: *') ;
    header ('Content-Type: application/json; charset=UTF-8') ;
    header ('Access-Control-Allow-Methods: PATCH') ;
    header ('Access-Control-Max-Age: 3600') ;
    header ('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With') ;*/

    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    $temp = file_get_contents ('php://input') ;

    if ($temp === false)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'get data error.' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (empty ($temp))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'data not found.' ;
        echo json_encode ($result) ;
        exit ;
    }

    // 把 php://input 從 string 轉成一個 associative array
    $request_body = json_decode ($temp, true) ;

    /*$request_body = file_get_contents ('php://input') ;
    // 要寫判斷 === false 還有 ! isset 還有 empty ( 應該要弄成一個 function 哪！ )

    $data = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array
     */

    $data = [] ;
    $data ['sn'] = $sn ;
    $data ['update_theme'] = 0 ;
    $data ['update_email'] = 0 ;
    $data ['update_password'] = 0 ;

    if (isset ($request_body ['password-old'])
        && isset ($request_body ['password-new'])
        && isset ($request_body ['password-double-check']))
    {
        if ($request_body ['password-old'] === '')
        {
            $result ['status'] = 'error' ;
            $result ['message'] = 'empty password-old is invalid' ;
            echo json_encode ($result) ;
            return -1 ;
        }
        if (strlen ($request_body ['password-old']) > 50)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'password-old too long' ;
            echo json_encode ($result) ;
            return -1 ;
        }

        if ($request_body ['password-new'] === '')
        {
            $result ['status'] = 'error' ;
            $result ['message'] = 'empty password-new is invalid' ;
            echo json_encode ($result) ;
            return -1 ;
        }
        if (strlen ($request_body ['password-new']) > 50)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'password-new too long' ;
            echo json_encode ($result) ;
            return -1 ;
        }

        if ($request_body ['password-double-check'] === '')
        {
            $result ['status'] = 'error' ;
            $result ['message'] = 'empty password-double-check is invalid' ;
            echo json_encode ($result) ;
            return -1 ;
        }
        if (strlen ($request_body ['password-double-check']) > 50)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'password-double-check too long' ;
            echo json_encode ($result) ;
            return -1 ;
        }

        if ($request_body ['password-old'] === $request_body ['password-new'])
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'password-old equal password-new' ;
            echo json_encode ($result) ;
            return -1 ;
        }

        if ($request_body ['password-new'] != $request_body ['password-double-check'])
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'new password not equal' ;
            echo json_encode ($result) ;
            return -1 ;
        }

        $data ['password-old'] = $request_body ['password-old'] ;
        $data ['password-new'] = $request_body ['password-new'] ;
        $data ['password-double-check'] = $request_body ['password-double-check'] ;
        $data ['update_password'] = 1 ;
    }

    if (isset ($request_body ['email']))
    {
        if (preg_match ('/[^\w.\-@]/', $request_body ['email']))
        {
            $result ['status'] = 'error' ;
            $result ['message'] = 'email is invalid' ;
            echo json_encode ($result) ;
            exit ;
        }
        if (strlen ($request_body ['email']) > 320)
        {
            $result ['status'] = 'error' ;
            $result ['message'] = 'email too long!' ;
            echo json_encode ($result) ;
            exit ;
        }
        $data ['email'] = $request_body ['email'] ;
        $data ['update_email'] = 1 ;
    }

    if (isset ($request_body ['theme']))
    {
        $data ['theme'] = $request_body ['theme'] ;
        $data ['update_theme'] = 1 ;
    }

    sql_query_updating_account_info ($data, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_account_info (& $data, & $result)
{
    $change_email_flag = 0 ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_account ($db_server, $db_user_name, $db_password, $db_name) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    try
    {
        // #cn_1302
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1302" . mysqli_connect_error ()) ;
        }

        $ret = query_updating_account_info ($db, $data, $change_email_flag, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $db -> close () ;

        if ($change_email_flag === 1)
        {
            $ret = sql_query_updating_email_notification ($data ['sn'], $result) ;
            if ($ret === -1)
            {
                return -1 ;
            }
        }

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
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function query_updating_account_info (& $db, & $data, & $change_email_flag, & $result)
{
    if ($data ['update_theme'] === 1)
    {
        $query = '
        UPDATE `account`
        SET `theme` = ?
        WHERE `serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'ii'
            , $data ['theme']
            , $data ['sn']
        ) ;
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
            $result ['message'] = 'update not match item' ;
            return -1 ;
        }
        if ($changed_rows === 0)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            return -1 ;
        }
        $stmt -> close () ;
        return 1 ;
    }

    if ($data ['update_email'] === 1)
    {
        $query = '
        SELECT
            `email`
        FROM `account`
        WHERE `serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'i'
            , $data ['sn']
        ) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        if ($stmt -> num_rows === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'not match item' ;
            return -1 ;
        }
        $stmt -> bind_result ($col_email) ;
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;

        if ($col_email === $data ['email'])
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            return -1 ;
        }

        $query = '
        UPDATE `account`
        SET
            `email` = ?
            , `email_verified_status` = 0
        WHERE
            `serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'si'
            , $data ['email']
            , $data ['sn']
        ) ;
        $stmt -> execute () ;
        sscanf ($db -> info, '%*s%*s%d%*s%d', $matched_rows, $changed_rows) ;
        if ($matched_rows === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'update not match item' ;
            return -1 ;
        }
        if ($changed_rows === 0)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            return -1 ;
        }
        $stmt -> close () ;
        $change_email_flag = 1 ;
        $result ['email'] = $data ['email'] ;
    }

    if ($data ['update_password'] === 1)
    {
        $query = '
        SELECT
            `serial_number`
        FROM `account` WHERE
            `serial_number` = ?
            AND
            `password` = SHA2(?, 256)
            ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'is'
            , $data ['sn']
            , $data ['password-old']
        ) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        if ($stmt -> num_rows === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'password-old not correct' ;
            return -1 ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;

        //do update password
        $query = '
        UPDATE `account`
        SET
           `password` = SHA2(?, 256)
        WHERE
            `serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'si'
            , $data ['password-new']
            , $data ['sn']
        ) ;
        $stmt -> execute () ;
        sscanf ($db -> info, '%*s%*s%d%*s%d', $matched_rows, $changed_rows) ;
        if ($matched_rows === 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'update not match item' ;
            return -1 ;
        }
        if ($changed_rows === 0)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            return -1 ;
        }
        $stmt -> close () ;
    }

    return 1 ;
}

function post_verified_code_mail ($serial_number)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    sql_query_creating_verified_code_mail ($serial_number, $result) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_creating_verified_code_mail ($serial_number, & $result)
{
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_account ($db_server, $db_user_name, $db_password, $db_name) ;
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    try
    {
        // #cn_1303
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1303" . mysqli_connect_error ()) ;
        }

        $ret = query_reading_account_email_relation_column ($db, $serial_number, $col_account_id, $col_email, $col_email_verified_status, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        // 檢查是否已經驗證過 email，如果已經驗證過，直接跳出。
        if ($col_email_verified_status === 1)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'already verified, do not need verifying!' ;
            return -1 ;
        }

        // 檢查是否有上次發送驗證碼信的時間，要間隔 57 秒才能再寄信
        $time_stamp = 0 ;
        $col_number_rows = 0 ;
        query_reading_verified_code_mail_for_checking_time ($db, $serial_number, $time_stamp, $col_number_rows, $result) ;
        if (time () - $time_stamp <= 57)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'resending mail is cool down!' ;
            return -1 ;
        }

        $verified_code = generate_verified_code () ;

        $ret = check_email_valid ($col_email, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        if ($col_number_rows === 0)
        {
            $ret = query_creating_verified_code_mail ($db, $serial_number, $col_email, $verified_code, $result) ;
        }
        else
        {
            $ret = query_updating_verified_code_mail ($db, $serial_number, $col_email, $verified_code, $result) ;
        }
        if ($ret === -1)
        {
            return -1 ;
        }

        $db -> close () ;

        $smtp_code = send_verified_code_mail ($col_email, $col_account_id, $verified_code) ;

        $ret = check_smtp_code ($smtp_code, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

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
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function query_reading_account_email_relation_column (& $db, $serial_number, & $col_account_id, & $col_email, & $col_email_verified_status, & $result)
{
    $query = '
    SELECT
        `account_id`
        , `email`
        , `email_verified_status`
    FROM `account`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;
    if ($stmt -> num_rows === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'no matched item' ;
        return -1 ;
    }
    $stmt -> bind_result ($col_account_id, $col_email, $col_email_verified_status) ;

    while ($stmt -> fetch ())
    {
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
    return 1 ;
}

function query_reading_verified_code_mail_for_checking_time (& $db, $serial_number, & $time_stamp, & $col_number_rows, & $result)
{
    $query = '
    SELECT
        `serial_number`
        , `time`
    FROM `email_verification`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $col_number_rows = $stmt -> num_rows ;

    if ($stmt -> num_rows === 0)
    {
        return 1 ;
    }
    $stmt -> bind_result ($col_serial_number, $col_time_stamp) ;

    while ($stmt -> fetch ())
    {
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
    $time_stamp = $col_time_stamp ; // save to external variable
    return 1 ;
}

function generate_verified_code ()
{
    $permitted_chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789' ;
    $permitted_chars_length = strlen ($permitted_chars) ;
    
    $random_char = '' ;
    $output = '' ;
    for ($i = 0 ; $i < 8 ; $i ++)
    {
        $random_char = $permitted_chars [random_int (0, $permitted_chars_length - 1)] ;
        $output .= $random_char ;
    }
    return $output ;
}

function check_email_valid ($email, & $result)
{
    $ret = strpos ($email, '@') ;
    if ($ret === false)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'email address invalid!' ;
        return -1 ;
    }
    if ($ret === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'email address invalid! @ sign at head' ;
        return -1 ;
    }
    if ($ret === strlen ($email) -1)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'email address invalid! @ sign at tail' ;
        return -1 ;
    }
    return 1 ;
}

function query_creating_verified_code_mail (& $db, $serial_number, $email, $verified_code, & $result)
{

    $query = '
    INSERT INTO `email_verification`
    VALUES (?, ?, ?, ?)
    ' ;

    $time_stamp = time () ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'issi'
        , $serial_number
        , $email
        , $verified_code
        , $time_stamp
    ) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows < 1)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function query_updating_verified_code_mail (& $db, $serial_number, $email, $verified_code, & $result)
{
    $query = '
    UPDATE `email_verification`
    SET
        `email` = ?
        , `verified_code` = ?
        , `time` = ?
    WHERE `serial_number` = ?
    ' ;

    $time_stamp = time () ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'ssii'
        , $email
        , $verified_code
        , $time_stamp
        , $serial_number
    ) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows < 1)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function send_verified_code_mail ($email, $account_id, $verified_code)
{
    $to = $email ;
    $subject = 'E-mail 驗證' ;
    $content = '<html>
<head>
  <title>mcn site</title>
</head>
<body>
  親愛的會員 ' . $account_id . ' 您好。<br /><br />
  Email 驗證碼：<span style = \"color: red\">' . $verified_code . '</span><br /><br />
  請於 24 小時內到網站上進行驗證。<br />
  <br />
  ※本信件由系統自動發送，請勿直接回信，感謝您的配合！<br />
  <br />
若您並未要求此驗證碼，可以安全地忽略此電子郵件。可能有人誤輸入了您的電子郵件地址。<br />
  <br />
  mcnsite 敬上

</body>
</html>' ; // 想換行就用 \n
    
    $cmd = 'printf "Subject: ' . $subject . '\nTo: ' . $to . '\nFrom: no-reply@114-32-71-101.hinet-ip.hinet.net\n' . 'Content-Type: text/html; charset=UTF-8\n' . 'MIME-Version: 1.0\n' . $content . '" | sudo /usr/sbin/sendmail -i -v -Am -- ' . $to ;

    $ret = shell_exec ($cmd) ;
    $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  ']' . "\n" . $ret . "\n" ;
    error_log ($log_text, 3, '/var/weblog/mail.log') ;
    return $ret ;
}

function check_smtp_code ($smtp_code, & $result)
{
    $count = substr_count ($smtp_code, '>>>') ;
    if ($count === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'no such mail server' ;
        return -1 ;
    }
    try
    {
        $text_line = explode ("\n", $smtp_code) ;
    }
    catch (\Exception $e)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Error Server SMTP' ;
        return -1 ;
    }
    $length = count ($text_line) ;
    for ($i = 0 ; $i < $length ; $i ++)
    {
        $pos = strpos ($text_line [$i], ">>> DATA") ;
        if (! ($pos === false))
        {
            if ($i + 1 < $length)
            {
                $f1 = $text_line [$i + 1] ;
                $f1_head = substr ($f1, 0, 3) ;
                if ($f1_head [0] === '5')
                {
                    $result ['status'] = 'failed' ;
                    $result ['message'] = 'smtp error check user' ;
                    $result ['ex_msg'] = $f1 ;
                    return -1 ;
                }
            }
        }
        $pos = strpos ($text_line, ">>> .") ;
        if (! ($pos === false))
        {
            if ($i + 1 < $length)
            {
                $f2 = $text_line [$i + 1] ;
                $f2_head = substr ($f2, 0, 3) ;
                if ($f2_head [0] === '5')
                {
                    $result ['status'] = 'failed' ;
                    $result ['message'] = 'smtp error as spam' ;
                    $result ['ex_msg'] = $f2 ;
                    return -1 ;
                }
            }
        }
    }
    $result ['text_line_count'] = count ($text_line) ;
    return 1 ;
}

function patch_email_verification ($serial_number)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    $temp = file_get_contents ('php://input') ;

    if ($temp === false)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'get data error.' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (empty ($temp))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'data not found.' ;
        echo json_encode ($result) ;
        exit ;
    }

    // 把 php://input 從 string 轉成一個 associative array
    $request_body = json_decode ($temp, true) ;

    $data = $request_body ['verified-code'] ;
    if (strlen ($data) > 50)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'verified code too long!' ;
        echo json_encode ($result) ;
        exit ;
    }

    sql_query_checking_verified_code ($serial_number, $data, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_checking_verified_code ($serial_number, $verified_code, & $result)
{
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_account ($db_server, $db_user_name, $db_password, $db_name) ;
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    try
    {
        // #cn_1304
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1304" . mysqli_connect_error ()) ;
        }

        $ret = query_reading_account_email_relation_column ($db, $serial_number, $col_account_id, $col_email, $col_email_verified_status, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        // 檢查是否已經驗證過 email，如果已經驗證過，直接跳出。
        if ($col_email_verified_status === 1)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'already verified, do not need verifying!' ;
            return -1 ;
        }

        $col_verified_code = '' ;
        $ret = query_reading_verified_code ($db, $serial_number, $col_email, $col_verified_code, $col_time, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }
        if (time () - $col_time > 86400)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'verified code is expired, please send mail to get new verified code!' ;
            return -1 ;
        }

        $verified_code = strtoupper ($verified_code) ; // 轉大寫字母
        if ( !($verified_code === $col_verified_code))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'incorrect verified code' ;
            return -1 ;
        }

        $ret = query_updating_email_verified_status ($db, $serial_number, 1, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

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
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function query_reading_verified_code (& $db, $serial_number, $col_email, & $col_verified_code, & $col_time, & $result)
{
    $query = '
    SELECT
        `serial_number`
        , `verified_code`
        , `time`
    FROM `email_verification`
    WHERE
        `serial_number` = ?
        AND
        `email` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('is', $serial_number, $col_email) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'no item matched' ;
        return -1 ;
    }
    $stmt -> bind_result ($col_serial_number, $col_verified_code, $col_time) ;

    while ($stmt -> fetch ())
    {
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
    return 1 ;
}

function query_updating_email_verified_status (& $db, $serial_number, $verified_status, & $result)
{
    $query = '
    UPDATE `account`
    SET
        `email_verified_status` = ?
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'ii'
        , $verified_status
        , $serial_number
    ) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows < 1)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function sql_query_updating_email_notification ($serial_number, & $result)
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
        // #cn_1305
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1305" . mysqli_connect_error ()) ;
        }

        $query = '
        UPDATE `basic_info`
        SET
            `email_notification` = 0
        WHERE `basic_info_serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $serial_number) ;
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
            //$result ['status'] = 'success' ;
            //$result ['message'] = 'no change' ;
            //return -1 ;
        }
        $stmt -> close () ;
        $db -> close () ;

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
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

?>
