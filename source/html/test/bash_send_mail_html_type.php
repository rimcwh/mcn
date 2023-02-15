<?php
    $to = 'testing@gmail.com' ;
    $subject = 'send mail test' ;
    $content = '<html>
<head>
  <title>mcn site</title>
</head>
<body>
  親愛的會員 xxx 您好。<br /><br />
  Email 認證碼：<span style = \"color: red\">225714</span><br /><br />
  請於 24 小時內到網站上進行驗證。<br />
  <br />
  ※本信件由系統自動發送，請勿直接回信，感謝您的配合！<br />
  <br />
  mcnsite 敬上

</body>
</html>' ; // 想換行就用 \n
    
    $cmd = 'printf "Subject: ' . $subject . '\nTo: ' . $to . '\nFrom: no-reply@114-32-71-101.hinet-ip.hinet.net\n' . 'Content-Type: text/html; charset=UTF-8\n' . 'MIME-Version: 1.0\n' . $content . '" | sudo /usr/sbin/sendmail -i -v -Am -- ' . $to ;
    
    echo '<body style = "background-color: #000 ; color: #ddd ; font-size: 2rem ;">' ;
    $result = shell_exec ('date') ;
    echo $result . '<br /><br />' ;
    $result = shell_exec ($cmd) ;
    echo '<pre>' . $result . '</pre>' ;
    
    echo '</body>' ;
?>
