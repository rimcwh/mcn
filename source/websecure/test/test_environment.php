<?php

require_once (__DIR__ . '/../../webconfig/other_pw/set_env_password.php') ;

function test_environment ()
{
    $request_body = file_get_contents ('php://input') ;
    $data = json_decode ($request_body, true) ;
    $result = array () ;
    $result ['status'] = 'testing environment' ;
    $result ['a'] = $data ['text-a'] ;
    $result ['b'] = $data ['text-b'] ;
    $result ['c'] = $data ['text-c'] ;
    set_env_password ($pw1, $pw2, $pw3) ;
    if (0 != strcmp ($data ['text-a'], $pw1))
    {
        echo 'hi' ;
        exit ;
    }
    if (0 != strcmp ($data ['text-b'], $pw2))
    {
        echo 'hihi' ;
        exit ;
    }
    if (0 != strcmp ($data ['text-c'], $pw3))
    {
        echo 'hihihi' ;
        exit ;
    }
    echo 'error_log: ' . ini_get('error_log') ;
    echo '<br /><br />' ;
    //echo phpinfo () ;
}
?>
