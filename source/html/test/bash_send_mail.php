<?php
    $to = 'testing@gmail.com' ;
    $subject = 'send mail test' ;
    $content = 'con line 1\ncon line 2QQ' ; // 想換行就用 \n
    
    $cmd = 'printf "Subject: ' . $subject . '\nTo: ' . $to . '\nFrom: no-reply@114-32-71-101.hinet-ip.hinet.net\n' . $content . '" | sudo /usr/sbin/sendmail -i -v -Am -- ' . $to ;
    
    echo '<body style = "background-color: #000 ; color: #ddd ; font-size: 2rem ;">' ;
    $result = shell_exec ('date') ;
    echo $result . '<br /><br />' ;
    //$result = shell_exec ($cmd) ;
    //echo '<pre>' . $result . '</pre>' ;
    
    echo '</body>' ;
?>
