<?php
function test_other_process_main ()
{
    $result = [] ;
    $result ['status'] = 'test other process' ;
    $result ['dir'] = __DIR__ ;
    //send_mail_when_order_checkout_sub_main () ;
    //echo json_encode ($result) ;
    $result ['before_time'] = date ('Y/m/d H:i:s') ;

    $exe_result = '' ;

    $shell_cmd = 'php ../../../../websecure/mis_fns/send_mail_when_order_checkout.php > /dev/null 2>&1 &' ;
    $shell_cmd = 'php ../../../../websecure/mis_fns/send_mail_when_order_checkout.php 18' ;
    
    exec ($shell_cmd, $exe_result) ;
    //exec ($shell_cmd) ;

    $result ['exe_result'] = $exe_result ;

    $result ['after_time'] = date ('Y/m/d H:i:s') ;

    echo json_encode ($result) ;
}

?>
