<?php
function test_aa_main ()
{
    $request_body = file_get_contents ('php://input') ;
    $data = json_decode ($request_body, true) ;
    $result = array () ;
    $result ['status'] = 'testing environment' ;
    $result ['a'] = $data ['text-a'] ;
    $result ['b'] = $data ['text-b'] ;
    $result ['c'] = $data ['text-c'] ;
    if (0 != strcmp ($data ['text-a'], '5g^y5gYp!&sejW23'))
    {
        echo 'hi' ;
        exit ;
    }
    if (0 != strcmp ($data ['text-b'], '9B7PbXwDY9xBkh%x'))
    {
        echo 'hihi' ;
        exit ;
    }
    if (0 != strcmp ($data ['text-c'], 'rhKhrroHhA266!ez'))
    {
        echo 'hihihi' ;
        exit ;
    }
    //echo ini_get('error_log') ;
    echo '<br /><br />' ;
    echo 'pass<br /><br />' ;
    //add_account () ;
}

function add_account ()
{
    $temp = '' ;
    //for ($i = 10 ; $i <= 99 ; $i ++)
    {
        $sn_id = 200 ;
        if ($i < 10)
        {
            $temp = '0' . $i ;
        }
        else
        {
            $temp = $i ;
        }
        //$ac_id = 'user' . $temp ;
        //$pw = 'user' . $temp ;
        $ac_id = 'mcn' ;
        $pw = 'mcn' ;
        echo 'sn_id: ' . $sn_id . '<br />' ;
        echo 'ac_id: ' . $ac_id . '<br />' ;
        echo 'pw: ' . $pw . '<br />' ;
        add_account_for_site_account ($sn_id, $ac_id, $pw) ;
        add_account_for_chat ($sn_id, $ac_id, $pw) ;
        add_account_for_shop ($sn_id, $ac_id, $pw) ;
        add_account_for_bingo ($sn_id, $ac_id, $pw) ;
    }
}

function add_account_for_site_account ($sn_id, $ac_id, $pw)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_account.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_account ($db_server, $db_user_name, $db_password, $db_name) ;

    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_test_01
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test_01" . mysqli_connect_error ()) ;
        }

        $query = '
        INSERT INTO `account` (
            `account_id`
            , `password`
            , `theme`
        )
        VALUES (
            ?
            , SHA2(?, 256)
            , 1
        )
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ss', $ac_id, $pw) ;
        $stmt -> execute () ;

        if ($stmt -> affected_rows != 1)
        {
            echo 'affected_rows != 1' ;
        }
        //echo $db -> info . '<br />' ;
        printf ("%s\n", $db -> info) ;

        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $simply_output = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($simply_output) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function add_account_for_chat ($sn_id, $ac_id, $pw)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_chat.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_chat ($db_server, $db_user_name, $db_password, $db_name) ;

    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_test_02
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test_02" . mysqli_connect_error ()) ;
        }

        $query = '
        INSERT INTO `account_setting` (
            `serial_number`
            , `nickname`
        )
        VALUES (
            ?
            , ?
        )
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('is', $sn_id, $ac_id) ;
        $stmt -> execute () ;

        if ($stmt -> affected_rows != 1)
        {
            echo 'affected_rows != 1' ;
        }
        echo $db -> info . '<br />' ;

        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $simply_output = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($simply_output) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function add_account_for_shop ($sn_id, $ac_id, $pw)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;

    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_test_03
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test_03" . mysqli_connect_error ()) ;
        }

        $query = '
        INSERT INTO `basic_info` (
            `basic_info_serial_number`
            , `name`
        )
        VALUES (
            ?
            , ?
        )
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('is', $sn_id, $ac_id) ;
        $stmt -> execute () ;

        if ($stmt -> affected_rows != 1)
        {
            echo 'affected_rows != 1' ;
        }
        echo $db -> info . '<br />' ;

        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $simply_output = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($simply_output) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function add_account_for_bingo ($sn_id, $ac_id, $pw)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_bingo.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_bingo ($db_server, $db_user_name, $db_password, $db_name) ;

    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;

    try
    {
        // #cn_test_04
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_test_04" . mysqli_connect_error ()) ;
        }

        $query = '
        INSERT INTO `player_status` (
            `serial_number`
            , `account_id`
        )
        VALUES (
            ?
            , ?
        )
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('is', $sn_id, $ac_id) ;
        $stmt -> execute () ;

        if ($stmt -> affected_rows != 1)
        {
            echo 'affected_rows != 1' ;
        }
        echo $db -> info . '<br />' ;

        return ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $simply_output = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($simply_output) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

?>
