<?php
    $to = 'testing@gmail.com' ;
    $subject = 'send mail test' ;
    $content = 'con line 1\ncon line 2\nsent from php' ; // 想換行就用 \n
    $headers = 'From: no-reply@114-32-71-101.hinet-ip.hinet.net' ;
    
    echo '<body style = "background-color: #000 ; color: #ddd ; font-size: 2rem ;">' ;
    $result = shell_exec ('date') ;
    echo $result . '<br /><br />' ;
    //$result = mail ($subject, $to, $content, $headers) ;
    echo 'result: ' ;
    if ($result === false)
    {
        echo 'false<br />' ;
    }
    else
    {
        echo 'not false<br />' ;
    }
    
    echo '</body>' ;
?>
