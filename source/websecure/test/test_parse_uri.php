<?php

//require (__DIR__ . '/../mis_fns/parse_uri_one_layer.php') ;

function test_parse_uri ($uri)
{
    echo '<body style = "background-color: #000 ; color: #ddd ; font-size: 3rem ;">' ;
    echo 'here is test_parse_uri<br />' ;
    echo '<br />' ;
    echo 'uri: ' . $uri . '<br />' ;
    echo 'gettype uri: ' . gettype ($uri) . '<br />' ;
    echo '<br />' ;
    
    $path_segment = parse_uri_one_layer ($uri) ;
    echo '1: <br />' ;
    echo 'path_segment: ' . $path_segment . '<br />' ;
    echo 'gettype path_segment: ' . gettype ($path_segment) . '<br />' ;
    echo 'uri: ' . $uri . '<br />' ;
    echo 'gettype uri: ' . gettype ($uri) . '<br />' ;
    echo '<br />' ;
    if ($uri == '')
    {
        echo 'uri <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'uri <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $uri))
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    if ($path_segment == '')
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $path_segment))
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    echo '<br />' ;
    
    $path_segment = parse_uri_one_layer ($uri) ;
    echo '2: <br />' ;
    echo 'path_segment: ' . $path_segment . '<br />' ;
    echo 'gettype path_segment: ' . gettype ($path_segment) . '<br />' ;
    echo 'uri: ' . $uri . '<br />' ;
    echo 'gettype uri: ' . gettype ($uri) . '<br />' ;
    echo '<br />' ;
    if ($uri == '')
    {
        echo 'uri <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'uri <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $uri))
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    if ($path_segment == '')
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $path_segment))
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    echo '<br />' ;
    
    $path_segment = parse_uri_one_layer ($uri) ;
    echo '3: <br />' ;
    echo 'path_segment: ' . $path_segment . '<br />' ;
    echo 'gettype path_segment: ' . gettype ($path_segment) . '<br />' ;
    echo 'uri: ' . $uri . '<br />' ;
    echo 'gettype uri: ' . gettype ($uri) . '<br />' ;
    echo '<br />' ;
    if ($uri == '')
    {
        echo 'uri <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'uri <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $uri))
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    if ($path_segment == '')
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $path_segment))
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    echo '<br />' ;
    
    $path_segment = parse_uri_one_layer ($uri) ;
    echo '4: <br />' ;
    echo 'path_segment: ' . $path_segment . '<br />' ;
    echo 'gettype path_segment: ' . gettype ($path_segment) . '<br />' ;
    echo 'uri: ' . $uri . '<br />' ;
    echo 'gettype uri: ' . gettype ($uri) . '<br />' ;
    echo '<br />' ;
    if ($uri == '')
    {
        echo 'uri <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'uri <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $uri))
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    if ($path_segment == '')
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $path_segment))
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    echo '<br />' ;
    
    $path_segment = parse_uri_one_layer ($uri) ;
    echo '5: <br />' ;
    echo 'path_segment: ' . $path_segment . '<br />' ;
    echo 'gettype path_segment: ' . gettype ($path_segment) . '<br />' ;
    echo 'uri: ' . $uri . '<br />' ;
    echo 'gettype uri: ' . gettype ($uri) . '<br />' ;
    echo '<br />' ;
    if ($uri == '')
    {
        echo 'uri <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'uri <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $uri))
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'uri strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    if ($path_segment == '')
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[equl]</span> empty string<br /><br />' ;
    }
    else
    {
        echo 'path_segment <span style = "color: #ff0000 ; ">[not equl]</span> empty string<br /><br />' ;
    }
    if (0 == strcmp ('', $path_segment))
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[equl]</span> 0<br /><br />' ;
    }
    else
    {
        echo 'path_segment strcmp <span style = "color: #ff0000 ; ">[not equl]</span> 0<br /><br />' ;
    }
    echo '<br />' ;

    echo '</body>' ;
}

?>
