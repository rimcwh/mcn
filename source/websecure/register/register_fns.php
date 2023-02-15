<?php
//require_once (__DIR__ . '/../db_link/dbconnect_r_account.php') ;
//require_once (__DIR__ . '/../db_link/dbconnect_w_account.php') ;

function register_main ($uri)
{
    if ($uri === '')
    {
        if ($_SERVER ['REQUEST_METHOD'] === 'POST')
        {
            post_register () ;
            exit ;
        }
    }

    $path_segment = parse_uri_one_layer ($uri) ;
    if ($uri === '')
    {
        if (0 === strcmp ('captcha', $path_segment))
        {
            if ($_SERVER ['REQUEST_METHOD'] === 'POST')
            {
                post_captcha () ;
                exit ;
            }
        }
    }

    $segment1 = $path_segment ;
    $path_segment = parse_uri_one_layer ($uri) ;
    if ($uri === '')
    {
        if (0 === strcmp ('checking-account-id', $segment1))
        {
            if ($_SERVER ['REQUEST_METHOD'] === 'GET')
            {
                $account_id = $path_segment ;
                get_checking_account_id ($path_segment) ;
                exit ;
            }
        }
    }
}

function guidv4 ($data = null)
{
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    //$data = $data ?? openssl_random_pseudo_bytes (16) ;
    $data = $data ?? random_bytes (16) ;
    assert (strlen ($data) == 16) ;

    // Set version to 0100
    $data [6] = chr (ord ($data [6]) & 0x0f | 0x40) ;
    // Set bits 6-7 to 10
    $data [8] = chr (ord ($data [8]) & 0x3f | 0x80) ;

    // Output the 36 character UUID.
    //return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)) ;
    return bin2hex ($data) ;
}

function generate_captcha_img ($captcha_string, & $result)
{
    $image = imagecreatetruecolor (200, 50) ;
    imageantialias ($image, true) ;
    $colors = [] ;
    $red = rand (125, 175) ;
    $green = rand (125, 175) ;
    $blue = rand (125, 175) ;
    for ($i = 0 ; $i < 5 ; $i ++)
    {
          $colors [] = imagecolorallocate ($image, $red - 20 * $i, $green - 20 * $i, $blue - 20 * $i) ;
    }
    imagefill ($image, 0, 0, $colors [0]) ;

    for ($i = 0 ; $i < 10 ; $i ++)
    {
        imagesetthickness ($image, rand (2, 10)) ;
        $line_color = $colors [rand (1, 4)] ;
        imagerectangle ($image, rand (-10, 190), rand (-10, 10), rand (-10, 190), rand (40, 60), $line_color) ;
    }

    $black = imagecolorallocate ($image, 0, 0, 0) ;
    $white = imagecolorallocate ($image, 255, 255, 255) ;
    $textcolors = [$black, $white] ;

    //$fonts = [dirname(__FILE__).'\fonts\Acme.ttf', dirname(__FILE__).'\fonts\Ubuntu.ttf', dirname(__FILE__).'\fonts\Merriweather.ttf', dirname(__FILE__).'\fonts\PlayfairDisplay.ttf'];

    //$font = '/usr/share/fonts/truetype/mingliu/MINGLIU.ttf' ;
    $font = '/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf' ;

    //$permitted_chars = 'ABCDEFGHKLMNPQRSTUVWXYZ' ;
    $string_length = 6 ;
    //$captcha_string = generate_string ($permitted_chars, $string_length) ;

    for($i = 0 ; $i < $string_length ; $i ++)
    {
        $letter_space = 170 / $string_length ;
        $initial = 15 ;

        //imagettftext ($image, 24, rand (-15, 15), $initial + $i * $letter_space, rand (25, 45), $textcolors [rand(0, 1)], $fonts [array_rand ($fonts)], $captcha_string [$i]) ;
        imagettftext ($image, 24, rand (-15, 15), $initial + $i * $letter_space, rand (25, 45), $textcolors [rand(0, 1)], $font, $captcha_string [$i]) ;

    }

    /*header ('Content-type: image/webp') ;
    imagewebp ($image) ;*/

    // output as base64
    ob_start () ;
    imagewebp ($image) ;
    $image_data = ob_get_contents () ;
    ob_end_clean () ;
    imagedestroy ($image) ;
    $result ['imgdata'] = base64_encode ($image_data) ;
}

function generate_string ($input, $strength = 10)
{
    $input_length = strlen ($input) ;
    $random_string = '' ;
    for ($i = 0 ; $i < $strength ; $i ++)
    {
        $random_character = $input [mt_rand (0, $input_length - 1)] ;
        $random_string .= $random_character ;
    }

    return $random_string ;
}

function post_captcha ()
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    sql_query_creating_captcha ($result) ;
    echo json_encode ($result) ;
}

function sql_query_creating_captcha (& $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_account.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_account ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_4101
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_4101" . mysqli_connect_error ()) ;
        }

        query_deleting_expired_captcha ($db, $result) ;

        $captcha_string = '' ;
        $ret = query_creating_captcha ($db, $captcha_string, $result) ;
        //$ret = query_reading_captcha ($db, $col_load, $result) ;
        if ($ret === -1)
        {
            return -1 ;
        }

        generate_captcha_img ($captcha_string, $result) ;

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

function query_deleting_expired_captcha (& $db, & $result)
{
    $expired_time = time () - 1200 ;
    $query = '
    DELETE FROM `captcha_info` WHERE `time` < ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $expired_time) ;
    $stmt -> execute () ;

    $stmt -> close () ;
}

function query_creating_captcha (& $db, & $captcha_string, & $result)
{
    $permitted_chars = 'ABCDEFGHKLMNPQRSTUVWXYZ' ;
    $string_length = 6 ;
    $captcha_string = generate_string ($permitted_chars, $string_length) ;

    $uuid_text = guidv4 () ;
    $uuid = hex2bin ($uuid_text) ;
    $time = time () ;

    $query = '
    INSERT INTO `captcha_info`
    VALUES (
        ?
        , ?
        , ?
    )
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('ssi', $uuid, $captcha_string, $time) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create record, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_captcha' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        return -1 ;
    }

    $stmt -> close () ;

    $result ['uuid'] = $uuid_text ;
    $result ['time'] = $time ;
    return 1 ;
}

function query_reading_captcha (& $db, $col, & $result)
{
    $query = '
    SELECT `time`, `status`, `captcha`, `uuid` FROM `captcha_info`
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not find round' ;
        return -1 ;
    }

    /*$stmt -> bind_result (
        $col_uuid
        , $col_captcha
        , $col_time
        , $col_status
    ) ;*/
    $stmt -> bind_result (
        $col_time
        , $col_status
        , $col_captcha
        , $col_uuid
    ) ;
    while ($stmt -> fetch ())
    {
    }

    $result ['uuid'] = bin2hex ($col_uuid) ;
    $result ['captcha'] = $col_captcha ;
    $result ['time'] = $col_time ;
    $result ['db_status'] = $col_status ;

    $stmt -> free_result () ;
    $stmt -> close () ;

    return 1 ;
}

function check_account_id_valid (& $account_id, & $result)
{
    if ($account_id === '')
    {
        $result ['status'] = 'error' ;
        $result ['message'] = 'empty is invalid' ;return -1 ;
    }

    if (preg_match ('/[^\w.\-@]/', $account_id))
    {
        $result ['status'] = 'error' ;
        $result ['message'] = 'accound id is invalid' ;
        return -1 ;
    }
    // substr 抓最前面 50 char
    $account_id = substr ($account_id, 0, 50) ;
    return 1 ;
}

function get_checking_account_id ($account_id)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    $result ['before_a_id'] = $account_id ;
    $result ['uri'] = $_SERVER ['REQUEST_URI'] ;

    $ret = check_account_id_valid ($account_id, $result) ;
    $result ['after_a_id'] = $account_id ;
    if ($ret === -1)
    {
        echo json_encode ($result) ;
        return -1 ;
    }

    $ret = sql_query_reading_checking_account_id ($account_id, $result) ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_checking_account_id ($account_id, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_r_account.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_account ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_4102
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_4102" . mysqli_connect_error ()) ;
        }

        $ret = query_reading_checking_account_id ($db, $account_id, $result) ;
        if ($ret === 1)
        {
            $result ['can_register'] = 'YES' ;
        }
        if ($ret === 2)
        {
            $result ['can_register'] = 'NO' ;
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

function query_reading_checking_account_id (& $db, $account_id, & $result)
{
    $query = '
    SELECT `serial_number` FROM `account`
    WHERE `account_id` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('s', $account_id) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        return 1 ;
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
    return 2 ;
}

function post_register ()
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

    if (! (isset ($request_body ['account_id'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not set accound id field!' ;
        echo json_encode ($result) ;
        return -1 ;
    }
    $ret = check_account_id_valid ($request_body ['account_id'], $result) ;
    if ($ret === -1)
    {
        echo json_encode ($result) ;
        return -1 ;
    }
    if (! (isset ($request_body ['password'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not set password field!' ;
        echo json_encode ($result) ;
        exit ;
    }
    if ($request_body ['password'] === '')
    {
        $result ['status'] = 'error' ;
        $result ['message'] = 'empty password is invalid' ;
        echo json_encode ($result) ;
        return -1 ;
    }
    // substr 抓最前面 50 char
    $request_body ['password'] = substr ($request_body ['password'], 0, 50) ;

    if (! (isset ($request_body ['captcha_string'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not set captcha string field!' ;
        echo json_encode ($result) ;
        exit ;
    }
    // substr 抓最前面 6 char
    $request_body ['captcha_string'] = substr ($request_body ['captcha_string'], 0, 6) ;
    if (! (isset ($request_body ['captcha_uuid'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not set captcha uuid field!' ;
        echo json_encode ($result) ;
        exit ;
    }
    if (! (isset ($request_body ['captcha_time'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'do not set captcha time field!' ;
        echo json_encode ($result) ;
        exit ;
    }

    create_account ($request_body, $result) ;

    //$result ['body'] = $request_body ;

    echo json_encode ($result) ;
    exit ;
}

function query_reading_checking_captcha (& $db, $captcha_string, $captcha_uuid, $captcha_time, & $result)
{
    $query = '
    SELECT
        `captcha`
        , HEX(`uuid`)
    FROM `captcha_info`
    WHERE
        HEX(`uuid`) = ?
        AND
        `time` = ?
        AND
        `captcha` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;

    $full_uuid = strtoupper ($captcha_uuid) . '00000000000000000000000000000000' ;
    $stmt -> bind_param ('sis', $full_uuid, $captcha_time, $captcha_string) ;

    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 1)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        return 1 ;
    }
    $stmt -> free_result () ;
    $stmt -> close () ;
    return 2 ;

    $stmt -> bind_result (
        $col_captcha
        , $col_uuid
    ) ;

    while ($stmt -> fetch ())
    {
    }
    $result ['g_captcha'] = $col_captcha ;
    $result ['g_uuid'] = $col_uuid ;

    $stmt -> free_result () ;
    $stmt -> close () ;
}

function query_reading_account_table_auto_increment (& $db, & $col_auto_increment, & $result)
{
    $query = '
    SELECT `AUTO_INCREMENT`
    FROM  INFORMATION_SCHEMA.TABLES
    WHERE
        TABLE_SCHEMA = \'site_account\'
        AND
        TABLE_NAME   = \'account\' ;
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows === 0)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        
        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do reading table\'s auto_increment value, but num_rows gets 0, it should be 1, file: register_fns.php, in func query_reading_account_table_auto_increment' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return 2 ;
    }
    $stmt -> bind_result (
        $col_auto_increment
    ) ;

    while ($stmt -> fetch ())
    {
    }
    
    $stmt -> free_result () ;
    $stmt -> close () ;
    return 1 ;
}

function query_test_insert (& $db, $account_id, $password, $auto_increment, & $result)
{
    $query = '
    INSERT INTO `account`
    (
        `serial_number`
        , `account_id`
        , `password`
        , `theme`
    )
    VALUES
    (
        ?
        , ?
        , SHA2(?, 256)
        , 0
    )
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'iss'
        , $auto_increment
        , $account_id
        , $password
    ) ;

    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $stmt -> close () ;
        $result ['test'] = 'affected rows 0, failed' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function create_account ($data, & $result)
{
    $db = [] ;
    $db ['site_account'] = NULL ;
    $db ['chat'] = NULL ;
    $db ['shop'] = NULL ;
    $db ['bingo'] = NULL ;
    connect_database_site_account ($db ['site_account'], $result) ;

    $ret = register_checking_captcha (
        $db ['site_account']
        , $data ['captcha_string']
        , $data ['captcha_uuid']
        , $data ['captcha_time']
        , $result
    ) ;
    if ($ret === -1)
    {
        ($db ['site_account']) -> close () ;
        return -1 ;
    }

    $serial_number = 0 ;
    $ret = create_account_for_site_account ($db, $serial_number, $data ['account_id'], $data ['password'], $result) ;
    if ($ret === -1)
    {
        ($db ['site_account']) -> close () ;
        return -1 ;
    }

    connect_database_chat ($db ['chat'], $result) ;

    $ret = create_account_for_chat ($db, $data ['account_id'], $serial_number, $result) ;
    if ($ret === -1)
    {
        ($db ['site_account']) -> close () ;
        ($db ['chat']) -> close () ;
        return -1 ;
    }

    connect_database_shop ($db ['shop'], $result) ;
    $ret = create_account_for_shop ($db, $data ['account_id'], $serial_number, $result) ;
    if ($ret === -1)
    {
        ($db ['site_account']) -> close () ;
        ($db ['chat']) -> close () ;
        ($db ['shop']) -> close () ;
        return -1 ;
    }

    connect_database_bingo ($db ['bingo'], $result) ;
    $ret = create_account_for_bingo ($db, $data ['account_id'], $serial_number, $result) ;
    if ($ret === -1)
    {
        ($db ['site_account']) -> close () ;
        ($db ['chat']) -> close () ;
        ($db ['shop']) -> close () ;
        ($db ['bingo']) -> close () ;
        return -1 ;
    }

    $result ['status'] = 'success' ;
    $result ['message'] = 'ok' ;

    ($db ['site_account']) -> close () ;
    ($db ['chat']) -> close () ;
    ($db ['shop']) -> close () ;
    ($db ['bingo']) -> close () ;

    return 1 ;
}

function connect_database_site_account (& $db, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_account.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_account ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_4111
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_4111" . mysqli_connect_error ()) ;
        }
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

function connect_database_chat (& $db, & $result)
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
        // #cn_4112
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_4112" . mysqli_connect_error ()) ;
        }
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

function connect_database_shop (& $db, & $result)
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
        // #cn_4113
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_4113" . mysqli_connect_error ()) ;
        }
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

function connect_database_bingo (& $db, & $result)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_4114
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_4114" . mysqli_connect_error ()) ;
        }
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

function register_checking_captcha (& $db, $captcha_string, $captcha_uuid, $captcha_time, & $result)
{
    query_deleting_expired_captcha ($db, $result) ;

    $ret = query_reading_checking_captcha ($db, $captcha_string, $captcha_uuid, $captcha_time, $result) ;
    if ($ret === 2)
    {
        $result ['status'] = 'error' ;
        $result ['message'] = 'captcha is wrong!' ;
        return -1 ;
    }
    if ($ret === -1)
    {
        return -1 ;
    }

    return 1 ;
}

function create_account_for_site_account (& $db, & $serial_number, $account_id, $password, & $result)
{
    $ret = query_reading_checking_account_id ($db ['site_account'], $account_id, $result) ;
    if ($ret === 2)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'account id aleady existed!' ;
        return -1 ;
    }

    $ret = query_reading_account_table_auto_increment ($db ['site_account'], $serial_number, $result) ;
    if ($ret === 2)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }
    // $serial_number = 301 ; // 原本是 202，手動先改成 301以後，新增 record，會變成 302

    // $serial_number = 200 ; // 測試重複的資料，前端會說 sql error，後端查 log 會找到 Duplicate entry '200' for key 'PRIMARY'

    /*$result ['D.T.'] ['sn'] = $serial_number ;

    $ret = before_create_main_table_detailed_process ($db ['site_account'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/

    $ret = query_creating_account_for_main_table ($db ['site_account'], $account_id, $password, $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }

    /*$ret = after_create_main_table_detailed_process ($db ['site_account'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/
    return 1 ;
}

function query_creating_account_for_main_table (& $db, $account_id, $password, $serial_number, & $result)
{
    $query = '
    INSERT INTO `account`
    (
        `serial_number`
        , `account_id`
        , `password`
        , `theme`
    )
    VALUES
    (
        ?
        , ?
        , SHA2(?, 256)
        , 0
    )
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'iss'
        , $serial_number
        , $account_id
        , $password
    ) ;

    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for main table, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_main_table' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function query_creating_account_for_main_table_recovery (& $db, $serial_number, & $result)
{
    $query = '
    DELETE FROM `account`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for main table recovery, query delete, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_main_table_recovery' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function dev_testing_query_creating_account_for_main_table (& $db, $serial_number, & $result)
{
    $query = '
    SELECT
        `serial_number`
        , `account_id`
    FROM `account`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows != 1)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_serial_number
        , $col_account_id
    ) ;

    while ($stmt -> fetch ())
    {
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    $result ['serial_number'] = $col_serial_number ;
    $result ['account_id'] = $col_account_id ;
    return 1 ;
}

function before_create_main_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_main_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'before create account for main table, query by sn have record!' ;
        $result ['D.T.'] ['before_create_main_account_query'] = $debug_result ;
        return -1 ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
        $result ['D.T.'] ['before_create_main_account_query'] = $debug_result ;
    }
    return 1 ;
}

function after_create_main_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_main_table ($db, $serial_number, $debug_result) ;
    if ($ret === -1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for main table, query by sn have no record' ;
        $result ['D.T.'] ['after_create_main_account_query'] = $debug_result ;
        return -1 ;
    }
    $debug_result ['status'] = 'success' ;
    $debug_result ['message'] = 'ok' ;
    $result ['D.T.'] ['after_create_main_account_query'] = $debug_result ;
    return 1 ;
}

function recover_main_table_detailed_process (& $db, $serial_number, & $result)
{
    query_creating_account_for_main_table_recovery ($db, $serial_number, $result) ;
    return ;

    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_main_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for main table recovery, query by sn have record' ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
    }
    $result ['D.T.'] ['after_create_main_account_recovery_query'] = $debug_result ;
}

function create_account_for_chat (& $db, $account_id, $serial_number, & $result)
{
    /*$ret = before_create_chat_table_detailed_process ($db ['chat'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/

    $ret = query_creating_account_for_chat_table ($db ['chat'], $account_id, $serial_number, $result) ;
    if ($ret === -1)
    {
        recover_main_table_detailed_process ($db ['site_account'], $serial_number, $result) ;
        return -1 ;
    }

    /*$ret = after_create_chat_table_detailed_process ($db ['chat'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/
}

function query_creating_account_for_chat_table (& $db, $account_id, $serial_number, & $result)
{
    $query = '
    INSERT INTO `account_setting`
    (
        `serial_number`
        , `nickname`
    )
    VALUES
    (
        ?
        , ?
    )
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'is'
        , $serial_number
        , $account_id
    ) ;

    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for chat table, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_chat_table' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function query_creating_account_for_chat_table_recovery (& $db, $serial_number, & $result)
{
    $query = '
    DELETE FROM `account_setting`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for chat table recovery, query delete, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_chat_table_recovery' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function dev_testing_query_creating_account_for_chat_table (& $db, $serial_number, & $result)
{
    $query = '
    SELECT
        `serial_number`
        , `nickname`
    FROM `account_setting`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows != 1)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_serial_number
        , $col_nickname
    ) ;

    while ($stmt -> fetch ())
    {
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    $result ['serial_number'] = $col_serial_number ;
    $result ['nickname'] = $col_nickname ;
    return 1 ;
}

function before_create_chat_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_chat_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'before create account for chat table, query by sn have record!' ;
        $result ['D.T.'] ['before_create_chat_account_query'] = $debug_result ;
        return -1 ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
        $result ['D.T.'] ['before_create_chat_account_query'] = $debug_result ;
    }
    return 1 ;
}

function after_create_chat_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_chat_table ($db, $serial_number, $debug_result) ;
    if ($ret === -1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for chat table, query by sn have no record' ;
        $result ['D.T.'] ['after_create_chat_account_query'] = $debug_result ;
        return -1 ;
    }
    $debug_result ['status'] = 'success' ;
    $debug_result ['message'] = 'ok' ;
    $result ['D.T.'] ['after_create_chat_account_query'] = $debug_result ;
    return 1 ;
}

function recover_chat_table_detailed_process (& $db, $serial_number, & $result)
{
    query_creating_account_for_chat_table_recovery ($db, $serial_number, $result) ;
    return ;

    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_chat_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for chat table recovery, query by sn have record' ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
    }
    $result ['D.T.'] ['after_create_chat_account_recovery_query'] = $debug_result ;
}

function create_account_for_shop (& $db, $account_id, $serial_number, & $result)
{
    /*$ret = before_create_shop_table_detailed_process ($db ['shop'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/

    $ret = query_creating_account_for_shop_table ($db ['shop'], $account_id, $serial_number, $result) ;
    if ($ret === -1)
    {
        recover_main_table_detailed_process ($db ['site_account'], $serial_number, $result) ;
        recover_chat_table_detailed_process ($db ['chat'], $serial_number, $result) ;
        return -1 ;
    }

    /*$ret = after_create_shop_table_detailed_process ($db ['shop'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/
}

function query_creating_account_for_shop_table (& $db, $account_id, $serial_number, & $result)
{
    $query = '
    INSERT INTO `basic_info`
    (
        `basic_info_serial_number`
        , `name`
    )
    VALUES
    (
        ?
        , ?
    )
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'is'
        , $serial_number
        , $account_id
    ) ;

    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for shop table, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_shop_table' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function query_creating_account_for_shop_table_recovery (& $db, $serial_number, & $result)
{
    $query = '
    DELETE FROM `basic_info`
    WHERE `basic_info_serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for shop table recovery, query delete, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_shop_table_recovery' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function dev_testing_query_creating_account_for_shop_table (& $db, $serial_number, & $result)
{
    $query = '
    SELECT
        `basic_info_serial_number`
        , `name`
    FROM `basic_info`
    WHERE `basic_info_serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows != 1)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_basic_info_serial_number
        , $col_name
    ) ;

    while ($stmt -> fetch ())
    {
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    $result ['serial_number'] = $col_basic_info_serial_number ;
    $result ['name'] = $col_name ;
    return 1 ;
}

function before_create_shop_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_shop_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'before create account for shop table, query by sn have record!' ;
        $result ['D.T.'] ['before_create_shop_account_query'] = $debug_result ;
        return -1 ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
        $result ['D.T.'] ['before_create_shop_account_query'] = $debug_result ;
    }
    return 1 ;
}

function after_create_shop_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_shop_table ($db, $serial_number, $debug_result) ;
    if ($ret === -1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for shop table, query by sn have no record' ;
        $result ['D.T.'] ['after_create_shop_account_query'] = $debug_result ;
        return -1 ;
    }
    $debug_result ['status'] = 'success' ;
    $debug_result ['message'] = 'ok' ;
    $result ['D.T.'] ['after_create_shop_account_query'] = $debug_result ;
    return 1 ;
}

function recover_shop_table_detailed_process (& $db, $serial_number, & $result)
{
    query_creating_account_for_shop_table_recovery ($db, $serial_number, $result) ;
    return ;

    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_shop_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for shop table recovery, query by sn have record' ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
    }
    $result ['D.T.'] ['after_create_shop_account_recovery_query'] = $debug_result ;
}

function create_account_for_bingo (& $db, $account_id, $serial_number, & $result)
{
    /*$ret = before_create_bingo_table_detailed_process ($db ['bingo'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/

    $ret = query_creating_account_for_bingo_table ($db ['bingo'], $account_id, $serial_number, $result) ;
    if ($ret === -1)
    {
        recover_main_table_detailed_process ($db ['site_account'], $serial_number, $result) ;
        recover_chat_table_detailed_process ($db ['chat'], $serial_number, $result) ;
        recover_shop_table_detailed_process ($db ['shop'], $serial_number, $result) ;
        return -1 ;
    }

    /*$ret = after_create_bingo_table_detailed_process ($db ['bingo'], $serial_number, $result) ;
    if ($ret === -1)
    {
        return -1 ;
    }*/
}

function query_creating_account_for_bingo_table (& $db, $account_id, $serial_number, & $result)
{
    $query = '
    INSERT INTO `player_status`
    (
        `serial_number`
        , `account_id`
    )
    VALUES
    (
        ?
        , ?
    )
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param (
        'is'
        , $serial_number
        , $account_id
    ) ;

    $stmt -> execute () ;

    if ($stmt -> affected_rows === 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for bingo table, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_bingo_table' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function query_creating_account_for_bingo_table_recovery (& $db, $serial_number, & $result)
{
    $query = '
    DELETE FROM `player_status`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;

    if ($stmt -> affected_rows <= 0)
    {
        $stmt -> close () ;

        $log_text = '[Date: ' . date ('Y-m-d, h:i:s A') .  '] --- abnormal, do create account for bingo table recovery, query delete, but affected_rows gets 0, it should be 1, file: register_fns.php, in func query_creating_account_for_bingo_table_recovery' . "\n" ;
        error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;

        $result ['status'] = 'failed' ;
        $result ['message'] = 'Server SQL Error' ;
        return -1 ;
    }

    $stmt -> close () ;
    return 1 ;
}

function dev_testing_query_creating_account_for_bingo_table (& $db, $serial_number, & $result)
{
    $query = '
    SELECT
        `serial_number`
        , `account_id`
    FROM `player_status`
    WHERE `serial_number` = ?
    ' ;

    $stmt = $db -> prepare ($query) ;
    $stmt -> bind_param ('i', $serial_number) ;
    $stmt -> execute () ;
    $stmt -> store_result () ;

    if ($stmt -> num_rows != 1)
    {
        $stmt -> free_result () ;
        $stmt -> close () ;
        return -1 ;
    }

    $stmt -> bind_result (
        $col_serial_number
        , $col_account_id
    ) ;

    while ($stmt -> fetch ())
    {
    }

    $stmt -> free_result () ;
    $stmt -> close () ;

    $result ['serial_number'] = $col_serial_number ;
    $result ['account_id'] = $col_account_id ;
    return 1 ;
}

function before_create_bingo_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_bingo_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'before create account for bingo table, query by sn have record!' ;
        $result ['D.T.'] ['before_create_bingo_account_query'] = $debug_result ;
        return -1 ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
        $result ['D.T.'] ['before_create_bingo_account_query'] = $debug_result ;
    }
    return 1 ;
}

function after_create_bingo_table_detailed_process (& $db, $serial_number, & $result)
{
    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_bingo_table ($db, $serial_number, $debug_result) ;
    if ($ret === -1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for bingo table, query by sn have no record' ;
        $result ['D.T.'] ['after_create_bingo_account_query'] = $debug_result ;
        return -1 ;
    }
    $debug_result ['status'] = 'success' ;
    $debug_result ['message'] = 'ok' ;
    $result ['D.T.'] ['after_create_bingo_account_query'] = $debug_result ;
    return 1 ;
}

function recover_bingo_table_detailed_process (& $db, $serial_number, & $result)
{
    query_creating_account_for_bingo_table_recovery ($db, $serial_number, $result) ;
    return ;

    $debug_result = [] ;
    $debug_result ['status'] = '' ;
    $debug_result ['message'] = '' ;
    $ret = dev_testing_query_creating_account_for_bingo_table ($db, $serial_number, $debug_result) ;
    if ($ret === 1)
    {
        $debug_result ['status'] = 'failed' ;
        $debug_result ['message'] = 'after create account for bingo table recovery, query by sn have record' ;
    }
    else
    {
        $debug_result ['status'] = 'success' ;
        $debug_result ['message'] = 'ok' ;
    }
    $result ['D.T.'] ['after_create_bingo_account_recovery_query'] = $debug_result ;
}

?>
