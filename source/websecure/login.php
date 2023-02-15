<?php
//declare (encoding = 'UTF-8') ;
namespace Login
{
require_once ('db_link/dbconnect_r_account.php') ;
require_once ('login_fns.php') ;
function main ($uri)
{
    //header ('Access-Control-Allow-Origin: *') ;
    //header ('Content-Type: application/json; charset=UTF-8') ;
    //header ('Access-Control-Allow-Methods: POST') ;
    //header ('Access-Control-Max-Age: 3600') ;
    //header ('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With') ;
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
    
    if ($_SERVER ['REQUEST_METHOD'] != 'POST')
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "request method 錯誤。 Login failed.")) ;
        exit ;
    }

    $h = getallheaders () ;
    if ($h === false)
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "request header 沒有資料。 Login failed.")) ;
        exit ;
    }

    $data = file_get_contents ('php://input') ;
    if ($data === false)
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "request body 沒有資料。 Login failed.")) ;
        exit ;
    }
    if (! isset ($data))
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "request body !isset。 Login failed.")) ;
        exit ;
    }
    if (empty ($data))
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "request body empty。 Login failed.")) ;
        exit ;
    }

    $arr = json_decode ($data, true) ;
    if ($arr === false)
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "request body 沒有資料。 Login failed.")) ;
        exit ;
    }

    if ($arr === null)
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "沒有資料，請重新輸入。 Login failed.")) ;
        exit ;
    }
    if (! isset ($arr ['account_id']))
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "帳號沒有設定，請重新輸入。 Login failed.")) ;
        exit ;
    }
    if (empty ($arr ['account_id']))
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "帳號沒有輸入，請重新輸入。 Login failed.")) ;
        exit ;
    }
    if (! isset ($arr ['password']))
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "密碼沒有設定，請重新輸入。 Login failed.")) ;
        exit ;
    }
    if (empty ($arr ['password']))
    {
        http_response_code (403) ;
        echo json_encode (array ("message" => "密碼沒有輸入，請重新輸入。 Login failed.")) ;
        exit ;
    }
    $ret = valid_variable ($arr, 'account_id', 50, '/[^\w.\-@]/') ;
    if ($ret === -1)
    {
        http_response_code (403) ;
        $result = array (
            'message' => '帳號太長，請輸入 50 字以內。 Login failed',
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    if ($ret === -2)
    {
        http_response_code (403) ;
        $result = array (
            'message' => '帳號中有不符合的字元，請輸入 英文數字底線或減號。 Login failed',
        ) ;
        echo json_encode ($result) ;
        exit ;
    }

    $ret = valid_variable ($arr, 'password', 50, '/[^\w_-]/') ;
    if ($ret === -1)
    {
        http_response_code (403) ;
        $result = array (
            'message' => '密碼太長，請輸入 50 字以內。 Login failed',
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
    if ($ret === -2)
    {
        http_response_code (403) ;
        $result = array (
            'message' => '密碼中有不符合的字元，請輸入 英文數字底線或減號。 Login failed',
        ) ;
        echo json_encode ($result) ;
        exit ;
    }

    sql_query_reading_account_and_password ($arr) ;
}
}
?>
