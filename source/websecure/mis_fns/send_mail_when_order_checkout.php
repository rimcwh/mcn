<?php
    $order_serial_number = intval ($argv [1]) ;

    require ('send_mail_when_order_checkout_sub.php') ;
    send_mail_when_order_checkout_sub_main ($order_serial_number) ;
    exit ;
?>
