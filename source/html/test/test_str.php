<?php
    echo '<body style = "color: #ddd ; background-color: #000 ; font-size: 2rem ;">' ;
    echo 'here is test str php<br />' ;
    
    
    $uri = $_SERVER ['REQUEST_URI'] ;
    $uri = '/api/v1////shop/my-shop/api/v1/123/' ;
    //$uri = '/api/v1/shop/my-shop/api/v1/123' ;
    echo 'uri: <br />' . $uri . '<br /><br />' ;
    
    $matched_uri_prefix = '/api/v1/' ;
    if (0 != substr_compare ($uri, $matched_uri_prefix, 0, strlen ($matched_uri_prefix)))
    {
        echo 'prefix not matched /api/v1/' ;
        exit ;
    }
    $uri = substr ($uri, strlen ('/api/v1/')) ; // 把 /api/v1/ 前綴拿掉
    echo 'substr strlen (\'/api/v1/\'): <br />' ;
    echo 'after substr /api/v1/, uri: <br />' . $uri . '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 1 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 1 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 1 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 2 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 2 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 2 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 3 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 3 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 3 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 4 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 4 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 4 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 5 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 5 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 5 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 6 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 6 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 6 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 7 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 7 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 7 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 8 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 8 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 8 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    echo '<br /><br />' ;
    
    $pos = strpos ($uri, '/') ;
    echo '第 9 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 9 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 9 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;

    $pos = strpos ($uri, '/') ;
    echo '第 10 次 pos: <br />' . $pos . '<br /><br />' ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
    if ($uri == '')
    {
        echo 'uri == empty<br />' ;
    }
    else
    {
        echo 'uri != empty<br />' ;
    }
    echo '第 10 次 cut, folder len: <br />' . strlen ($folder) . '<br /><br />' ;
    echo '第 10 次 cut to sign /, folder: <br />' . $folder . '<br />uri: <br />' . $uri . '<br /><br />' ;




    echo '</body>' ;
    exit ;
?>
