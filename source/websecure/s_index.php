<?php
    require ('mis_fns/parse_uri_one_layer.php') ;

    //echo 'request_uri: ' . $_SERVER ['REQUEST_URI'] . '<br />' ;
    $uri = $_SERVER ['REQUEST_URI'] ;
    if (strlen ($uri) > 4000)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'url too long' ;
        echo json_encode ($result) ;
        exit ;
    }
    
    $matched_uri_prefix = '/api/v1/' ;
    if (0 != substr_compare ($uri, $matched_uri_prefix, 0, strlen ($matched_uri_prefix)))
    {
        echo 'prefix not matched /api/v1/' ;
        exit ;
    }
    $uri = substr ($uri, strlen ('/api/v1/')) ; // 把 前綴拿掉
    
    $path_segment = parse_uri_one_layer ($uri) ;
    
    if (0 == strcmp ('login', $path_segment))
    {
        require ('login.php') ;
        Login\main ($uri) ; // enter login route
        exit ;
    }

    if (0 == strcmp ('logout', $path_segment))
    {
        require ('logout.php') ;
        Logout\main ($uri) ; // enter logout route
        exit ;
    }

    if (0 == strcmp ('account', $path_segment))
    {
        require ('account.php') ;
        Account\main ($uri) ; // enter account route
        exit ;
    }

    if (0 == strcmp ('chat', $path_segment))
    {
        require ('chat.php') ;
        Chat\main ($uri) ; // enter chat route
        exit ;
    }

    if (0 == strcmp ('shop', $path_segment))
    {
        require ('shop.php') ;
        Shop\main ($uri) ; // enter shop route
        exit ;
    }

    if (0 == strcmp ('users', $path_segment))
    {
        require ('users.php') ;
        Users\main ($uri) ; // enter users route
        exit ;
    }

    if (0 == strcmp ('bingo', $path_segment))
    {
        require ('bingo.php') ;
        Bingo\main ($uri) ; // enter bingo route
        exit ;
    }

    if (0 == strcmp ('register', $path_segment))
    {
        require ('register.php') ;
        Register\main ($uri) ; // enter register route
        exit ;
    }

    if (0 == strcmp ('test', $path_segment))
    {
        require ('test.php') ;
        Test\main ($uri) ; // enter test route
        exit ;
    }

?>
