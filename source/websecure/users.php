<?php
namespace Users
{

function main ($uri)
{
    if ($uri === '')
    {
        exit ;
    }

    $path_segment = parse_uri_one_layer ($uri) ;
    $segment1 = $path_segment ;

    if ($uri === '')
    {
        exit ;
    }

    $path_segment = parse_uri_one_layer ($uri) ;
    $segment2 = $path_segment ;

    if ($uri === '')
    {
        if ($segment2 === 'orders')
        {
            require ('users/users_orders_fns.php') ;
            users_orders_main ($uri, intval ($segment1)) ;
            exit ;
        }
    }

    $pos = strpos ($uri, '/') ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /

    $data = array () ;
    $data ['user_id'] = intval ($folder) ;
    $data ['uri__'] = $uri ;

    $pos = strpos ($uri, '/') ;
    
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /

    $data ['uri__2'] = $uri ;

    if (0 == strcmp ('orders', $folder))
    {
        require ('users/users_orders_fns.php') ;
        users_orders_main ($uri, $data) ;
        exit ;
    }

    echo json_encode ($data) ;
    exit ;
}

}
?>
