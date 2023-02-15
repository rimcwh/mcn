<?php

function parse_uri_one_layer (& $uri)
{
    $pos = strpos ($uri, '/') ;
    if (false === $pos)
    {
        $path_segment = $uri ;
    }
    else
    {
        $path_segment = substr ($uri, 0, $pos) ;
    }
    if (strlen ($uri) < (strlen ($path_segment) + 1))
    {
        $uri = '' ;
    }
    else
    {
        $uri = substr ($uri, strlen ($path_segment) + 1) ; // + 1 因為還要拿掉後面的 sign /
    }
    return $path_segment ;
}

?>
