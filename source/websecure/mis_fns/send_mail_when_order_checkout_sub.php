<?php

function send_mail_when_order_checkout_sub_main ($order_serial_number)
{
    $result = [] ;
    
    $ret = sql_query ($order_serial_number, $result) ;
    if ($ret === -1)
    {
        exit ;
    }

    if ($result ['col_oi'] ['record_number'] === 1)
    {
        send_mail_orderer ($order_serial_number
            , $result ['col_oi'] ['account_id']
            , $result ['col_oi'] ['email']) ;
    }

    for ($i = 0 ; $i < $result ['col_pi'] ['record_number'] ; $i ++)
    {
        send_mail_provider ($order_serial_number
            , $result ['col_pi'] ['account_id' . $i]
            , $result ['col_pi'] ['email' . $i]) ;
    }

    echo json_encode ($result) ;
    return ;
}

function send_mail (& $to, & $subject, & $content)
{
    $cmd = 'printf "Subject: ' . $subject . '\nTo: ' . $to . '\nFrom: no-reply@114-32-71-101.hinet-ip.hinet.net\n' . 'Content-Type: text/html; charset=UTF-8\n' . 'MIME-Version: 1.0\n' . $content . '" | sudo /usr/sbin/sendmail -i -v -Am -- ' . $to ;

    $ret = shell_exec ($cmd) ;
    $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  ']' . "\n" . $ret . "\n" ;
    error_log ($log_text, 3, '/var/weblog/mail.log') ;
}

function send_mail_orderer ($order_serial_number, & $account_id, & $email)
{
    $to = $email ;
    $subject = '[mcnsite] 收到您的訂單' ;
    $content = '<html>
<head>
  <title>mcn site</title>
</head>
<body>
  親愛的會員 ' . $account_id . ' 您好。<br /><br />
  系統收到您在本站書店下的訂單。<br /><br />
  您的訂單編號：<span style = \"color: red\">' . $order_serial_number . '</span><br /><br />
  <br />
  ※本信件由系統自動發送，請勿直接回信，感謝您的配合！<br />
  <br />
  若要取消系統通知信件，請至本站書店中設定取消 E-mail 通知。<br /><br />
  mcnsite 敬上
</body>
</html>' ; // 想換行就用 \n
    
    send_mail ($to, $subject, $content) ;
    return 1 ;
}

function send_mail_provider ($order_serial_number, & $account_id, & $email)
{
    $to = $email ;
    $subject = '[mcnsite] 您的訂單已成立' ;
    $content = '<html>
<head>
  <title>mcn site</title>
</head>
<body>
  親愛的會員 ' . $account_id . ' 您好。<br /><br />
  您賣的書籍，收到來自其他會員對您下的訂單。<br /><br />
  訂單編號：<span style = \"color: red\">' . $order_serial_number . '</span><br /><br />
  <br />
  ※本信件由系統自動發送，請勿直接回信，感謝您的配合！<br />
  <br />
  若要取消系統通知信件，請至本站書店中設定取消 E-mail 通知。<br /><br />
  mcnsite 敬上
</body>
</html>' ; // 想換行就用 \n
    
    send_mail ($to, $subject, $content) ;
    return 1 ;
}

function sql_query ($order_serial_number, & $result)
{
    $col_o = [] ;  // o for orderer
    $col_p = [] ;  // p for provider
    $provider_id = [] ;
    $col_oi = [] ; // o for orderer, i for info
    $col_pi = [] ; // p for provider, i for info
    $col_oi ['record_number'] = 0 ;
    $col_pi ['record_number'] = 0 ;

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
        // #cn_2201
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2201" . mysqli_connect_error ()) ;
        }

        $ret = query_orderer_id ($db, $order_serial_number, $col_o, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        $ret = query_provider_id ($db, $order_serial_number, $col_p, $result) ;

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

    for ($i = 0 ; $i < $col_p ['record_number'] ; $i ++)
    {
        $provider_id [$i] = & $col_p ['provider' . $i] ;
    }

    require_once (__DIR__ . '/../db_link/dbconnect_r_account.php') ;
    dbconnect_r_account ($db_server, $db_user_name, $db_password, $db_name) ;

    try
    {
        // #cn_2202
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2202" . mysqli_connect_error ()) ;
        }

        if ($col_o ['record_number'] === 1)
        {
            $ret = query_orderer_info ($db, $col_o ['orderer_id'], $col_oi, $result) ;
            if ($ret === -1)
            {
                return -1 ;
            }
        }

        if ($col_p ['record_number'] > 0)
        {
            $ret = query_provider_info ($db, $col_p ['record_number'], $provider_id, $col_pi, $result) ;
            if ($ret === -1)
            {
                return -1 ;
            }
        }

        $db -> close () ;
        $result ['col_o'] = $col_o ;
        $result ['col_p'] = $col_p ;
        $result ['col_oi'] = $col_oi ;
        $result ['col_pi'] = $col_pi ;
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

function query_orderer_id (& $db, $order_serial_number, & $col, & $result)
{
    $query = '
    SELECT
        oci.`orderer_id`
        , bai.`email_notification`
    FROM `order_contact_info` AS oci
    INNER JOIN `basic_info` AS bai
        ON oci.`orderer_id` = bai.`basic_info_serial_number`
    WHERE oci.`order_serial_number` = ?
        AND bai.`email_notification` = 1
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $order_serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $col ['record_number'] = $stmt -> num_rows ;
    if ($stmt -> num_rows <= 0)
    {
        return 1 ;
    }

    $stmt -> bind_result ($col_orderer_id, $col_email_notification) ;

    while ($stmt -> fetch ())
    {
    }
    $col ['orderer_id'] = $col_orderer_id ;
    $col ['email_notification'] = $col_email_notification ;

    $stmt -> free_result () ;
    $stmt -> close () ;

    return 1 ;
}

function query_provider_id (& $db, $order_serial_number, & $col, & $result)
{
    $query = '
    SELECT
        boi.`provider`
        , bai.`email_notification`
    FROM `order_detail` AS od
    INNER JOIN `book_info` AS boi
        ON boi.`book_serial_number` = od.`book_id`
    INNER JOIN `basic_info` AS bai
        ON boi.`provider` = bai.`basic_info_serial_number`
    WHERE od.`order_serial_number` = ?
        AND bai.`email_notification` = 1
    GROUP BY boi.`provider`
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $order_serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $col ['record_number'] = $stmt -> num_rows ;
    if ($stmt -> num_rows <= 0)
    {
        return 1 ;
    }

    $stmt -> bind_result ($col_provider, $col_email_notification) ;

    $i = 0 ;
    while ($stmt -> fetch ())
    {
        $col ['provider' . $i] = $col_provider ;
        $col ['email_notification' . $i] = $col_email_notification ;
        $i ++ ;
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    return 1 ;
}

function query_orderer_info (& $db, $orderer_id, & $col, & $result)
{
    $query = '
    SELECT
        `serial_number`
        , `account_id`
        , `email`
        , `email_verified_status`
    FROM `account`
    WHERE `serial_number` = ?
        AND
        `email_verified_status` = 1
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $orderer_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $col ['record_number'] = $stmt -> num_rows ;

    if ($stmt -> num_rows <= 0)
    {
        return 1 ;
    }

    $stmt -> bind_result ($col_serial_number, $col_account_id, $col_email, $col_email_verified_status) ;

    while ($stmt -> fetch ())
    {
    }
    $stmt -> free_result () ;
    $stmt -> close () ;

    $col ['serial_number'] = $col_serial_number ;
    $col ['account_id'] = $col_account_id ;
    $col ['email'] = $col_email ;
    $col ['email_verified_status'] = $col_email_verified_status ;

    return 1 ;
}

function query_provider_info (& $db, $provider_number, & $provider_id, & $col, & $result)
{
    $param_data = '?' ;
    for ($i = 1 ; $i < $provider_number ; $i ++)
    {
        $param_data .= ',?' ;
    }

    $type_data = '' ;
    for ($i = 0 ; $i < $provider_number ; $i ++)
    {
        $type_data .= 'i' ;
    }

    $query = '
    SELECT
        `serial_number`
        , `account_id`
        , `email`
        , `email_verified_status`
    FROM `account`
    WHERE `serial_number` IN (' . $param_data . ')
        AND
        `email_verified_status` = 1
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ($type_data, ...$provider_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    $col ['record_number'] = $stmt -> num_rows ;

    if ($stmt -> num_rows <= 0)
    {
        return 1 ;
    }

    $stmt -> bind_result ($col_serial_number, $col_account_id, $col_email, $col_email_verified_status) ;

    $i = 0 ;
    while ($stmt -> fetch ())
    {
        $col ['serial_number' . $i] = $col_serial_number ;
        $col ['account_id' . $i] = $col_account_id ;
        $col ['email' . $i] = $col_email ;
        $col ['email_verified_status' . $i] = $col_email_verified_status ;
        $i ++ ;
    }
    $stmt -> free_result () ;
    $stmt -> close () ;

    return 1 ;
}

?>
