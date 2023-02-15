<?php
require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
function test_jwt ($uri)
{
    header ('Access-Control-Allow-Origin: *') ;
    header ('Content-Type: application/json; charset=UTF-8') ;
    header ('Access-Control-Allow-Methods: GET') ;
    header ('Access-Control-Max-Age: 3600') ;
    header ('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With') ;
    // ref https://www.techiediaries.com/php-jwt-authentication-tutorial/
    
    /*header ("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/") ;
    header ("Content-Type: application/json; charset=UTF-8") ;
    header ("Access-Control-Allow-Methods: POST") ;
    header ("Access-Control-Max-Age: 3600") ;
    header ("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With") ;
    // ref https://codeofaninja.com/rest-api-authentication-example-php-jwt-tutorial/#File_Structure
    
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    header("Access-Control-Allow-Headers: access");
    // ref https://www.w3jar.com/php-login-and-registration-restful-api/*/
    
    $h = getallheaders () ;
    if ($h === false)
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "request header 沒有資料。 Login failed.")) ;
        exit ;
    }

    if (! (
        array_key_exists ('Authorization', $h)
        &&
        preg_match ('/Bearer\s(\S+)/', $h ['Authorization'], $matches)
        )
    )
    {
        $result = array (
            'status' => 'failed',
            'message' => 'header 沒有抓到 auth'
        ) ;
        echo json_encode ($result, JSON_UNESCAPED_UNICODE) ;
        exit ;
    }

    $result = \JwtAuthFns\jwt_decode ($matches [1]) ;
    echo json_encode ($result, JSON_UNESCAPED_UNICODE) ;

    exit ;

    mysqli_report (MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT) ;



    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_account ($db_server, $db_user_name, $db_password, $db_name) ;
    @$db = new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
    if (mysqli_connect_errno ())
    {
        http_response_code (403) ;
        $result = array (
            'message' => 'Error: Could not connect to database. Please try again later. ' . $db -> connect_error
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    $query = "SELECT account_id FROM account WHERE account_id = ?" ;
    //$query = "UPDATE account SET email = 'Doe03' WHERE account_id = 'test03'" ;
    try
    {
        $stmt = $db -> prepare ($query) ;
    }
    catch (\Exception $e)
    {
        $result = array (
            'message' => 'here is catch block: ' . $e -> getMessage (),
            'db_error' => $db -> error
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    $stmt = $db -> prepare ($query) ;
    if (! $stmt)
    {
        //$db -> close () ; // 會報錯，觀調連接，就不能看 error QQ
        $msg = 'here is ! $stmt QQ......prepare() failed: ' . htmlspecialchars ($db -> error) ;
        http_response_code (403) ;
        $result = array (
            'message' => $msg,
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    //$stmt -> bind_param ('s', $account_id) ;
    try
    {
        // #bp_4545
        $yen = @ $stmt -> bind_param ('sii', $account_id, $aa1, $aa2, $aa3, $aa4) ; // 最前面多 @ 就不會在 apache error.log 報警告了
        //$yen = $stmt -> bind_param ('s', $account_id) ;
        //$yen = $stmt -> bind_param ('s') ;
        if ($yen === false)
        {
            //throw new Exception ("bind_param failed at #bp_4545") ;
        }

    }
    catch (\Exception $e)
    {
        $result = array (
            'message' => 'here is catch block: (test for bind_param) ' . $e -> getMessage (),
            'stmt_error' => $stmt -> error
        ) ;
        echo json_encode ($result) ;
        exit ;

    }
    /*if ($yen === false)
    {
        $result = array (
            'message' => 'here is false block ',
            'stmt_error' => $stmt -> error
        ) ;
        echo json_encode ($result) ;
        exit ;

    }*/
    $exe_ret = 0 ;
    try
    {
        $exe_ret = $stmt -> execute () ;
        if (! $exe_ret)
        {
            $msg = $stmt -> error ;
            $stmt -> close () ;
            $db -> close () ;
            $result = array (
                'message' => 'here is execute if block: ' . $msg,
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
    }
    catch (\Exception $e)
    {
        if ($exe_ret === false)
        {
            $jjo = '$exe_ret === false!' ;
        }
        else
        {
            $jjo = '$exe_ret != false......' ;
        }
        $result = array (
            'status' => 'error',
            'message' => 'Server SQL Error',
            'stmt error' => $stmt -> error,
            'e getmessage' => $e -> getMessage (),
            'mysqli_errno' => $db -> connect_error,
            'mysqli_error' => $db -> error,
            'error_code' => $e -> getCode (),
            'error_line' => $e -> getLine (),
            'error_file' => $e -> getFile (),
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ($error_log_msg . "Date: " . date ("Y-m-d, h:i:s A") ."\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
    $stmt -> store_result () ;
    $rows_number = 'Number of accounts: ' . $stmt -> num_rows ;
    $stmt -> bind_result ($col1) ;
    $qs = array () ;
    $ii = 0 ;
    while ($stmt -> fetch ())
    {
        $ug = 'index ' . $ii ;
        $qss = array ($ug => $col1) ;
        $qs = $qs + $qss ;
        $ii ++ ;
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
    $db -> close () ;


    $result = array (
        'status' => 'success',
        'message' => 'ok',
        'DIR' => __DIR__,
        'row number:' => $rows_number,
    ) ;
    echo json_encode ($result + $qs) ;
    //echo $qs ;
}
?>
