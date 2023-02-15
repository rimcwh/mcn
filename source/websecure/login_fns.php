<?php
require_once (__DIR__ . '/authentication/jwt_fns.php') ;
require_once (__DIR__ . '/authentication/jwt_fns_for_csrf.php') ;
function valid_variable ($arr, $string_var_name, $max_len, $rule)
{
    $q = $arr [$string_var_name] ;
    if (strlen ($q) > $max_len)
    {
        return -1 ;
    }
    if (preg_match ($rule, $q))
    {
        return -2 ;
    }
    return 0 ;
}

function sql_query_reading_account_and_password ($data)
{
    // for check login data
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_account ($db_server, $db_user_name, $db_password, $db_name) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    try
    {
        // #cn_1201
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1201" . mysqli_connect_error ()) ;
        }
        $query = '
        SELECT
            `serial_number`
            , `account_id`
            , `email`
            , `status`
            , `wrong_password_count`
            , `lock_time`
            , `theme`
        FROM `account` WHERE
            `account_id` = ?
            AND
            `password` = SHA2(?, 256)
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ss', $data ['account_id'], $data ['password']) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        $rows_number = 'Number of accounts: ' . $stmt -> num_rows ;
        if ($stmt -> num_rows === 0)
        {
            http_response_code (403) ;
            $result = array (
                'status' => 'failed',
                'message' => '帳號密碼錯誤，請重新輸入。 Login failed.'
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
        $stmt -> bind_result ($col_serial_number, $col_account_id, $col_email, $col_status, $col_wrong_password_count, $col_lock_time, $col_theme) ;
        
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;

        $payload_plaintext = [] ;
        $jwt = \JwtAuthFns\generate_jwt ($col_serial_number, $payload_plaintext) ;
        //setcookie ('jwt', $jwt, 0, '/', 'mcnsite.ddns.net', true, true) ; // save cookie
        setcookie ('jwt', $jwt, 0, '/', 'mcn.sytes.net', true, true) ; // save cookie
        
        $full_csrf_token = \JwtAuthFnsForCsrf\generate_jwt_for_csrf ($payload_plaintext) ;
        $jwt_part = explode ('.', $full_csrf_token) ;
        header ('X-Csrf-Token: ' . $jwt_part [2]) ;

        $result = array (
            'status' => 'success',
            'message' => 'ok',
            'serial_number' => $col_serial_number,
            'jwt' => $jwt,
        ) ;
        echo json_encode ($result) ;
        exit ;
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
