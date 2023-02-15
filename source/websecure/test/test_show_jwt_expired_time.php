<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;

function test_show_jwt_expired_time_main ()
{
    //echo '<body style = "color: #ddd ; background-color: #000 ; font-size: 2rem ;">' ;
    
    $ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    //\JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $pp = $ret ['jwt_decode'] ;
    $ppa = (array) $pp ; // 把 pp 轉成 array
    $un = '' ;
    $un = $pp -> exp ;
    //$ret += ['$pp' => $pp] ;
    //$ret += ['expired-time' => $un ['iss']] ;
    
    //echo json_encode ($ret) ;
    //echo var_dump ($pp) ;
    //echo json_encode ($un) ;
    
    $ppa ['iat_date'] = $ppa ['iat'] . ' ' . date ('Y-m-d h:i:s A', $pp -> iat) ;
    $ppa ['exp_date'] = $ppa ['exp'] . ' ' . date ('Y-m-d h:i:s A', $pp -> exp) ;
    $ppa ['now'] = time () . ' ' . date ('Y-m-d h:i:s A') ;
    
    echo json_encode ($ppa) ;
    
    /*
    echo '<br /><br />' ;
    echo 'sn: ' . $pp -> sn . '<br />' ;
    echo '<br /><br />' ;
    echo $pp -> iat . ' = ' . date ('Y-m-d h:i:s A', $pp -> iat) . ' (iat)' . '<br />' ;
    echo $pp -> exp . ' = ' . date ('Y-m-d h:i:s A', $pp -> exp) . ' (exp)' . '<br />' ;
    echo '<br /><br />' ;
    echo date ('Y-m-d h:i:s A') ;
    */
    
    //echo '</body>' ;
    return 0 ;
}
?>
