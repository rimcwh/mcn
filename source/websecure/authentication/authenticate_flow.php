<?php
namespace AuthFlow
{

require_once (__DIR__ . '/jwt_fns.php') ;

require_once (__DIR__ . '/jwt_fns_for_csrf.php') ;

function authenticate_flow (& $sn_from_jwt)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    if (! (isset ($_COOKIE ['jwt'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'not login!' ;
        echo json_encode ($result) ;
        exit ;
    }
    $jwt_ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;

    if ($jwt_ret ['status'] === 'failed')
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = $ret ['message'] ;
        echo json_encode ($result, JSON_UNESCAPED_UNICODE) ;
        exit ;
    }

    $sn_from_jwt = $jwt_ret ['jwt_decode'] -> sn ;

    $csrf_token_part = '' ;
    $headers = getallheaders () ;
    if (isset ($headers ['X-Csrf-Token']))
    {
        $csrf_token_part = $headers ['X-Csrf-Token'] ;
    }
    if ($csrf_token_part === '')
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'csrf token invalid!' ;
        echo json_encode ($result) ;
        exit ;
    }

    $cookie_jwt_part = explode ('.', $_COOKIE ['jwt']) ;
    $csrf_token = $cookie_jwt_part [0] . '.' . $cookie_jwt_part [1] . '.' . $csrf_token_part ;

    $csrf_ret = \JwtAuthFnsForCsrf\jwt_decode_for_csrf ($csrf_token) ;

    if ($jwt_ret ['new_token'] === 1)
    {
        $new_full_csrf_token = \JwtAuthFnsForCsrf\generate_jwt_for_csrf ($jwt_ret ['payload_plaintext']) ;
        $jwt_part = explode ('.', $new_full_csrf_token) ;
        header ('X-Update-Csrf-Token: 1') ;
        header ('X-New-Csrf-Token: ' . $jwt_part [2]) ;
        $result ['nn_new_token'] = 'yes' ;
    }
    $result ['jwt_ret'] = $jwt_ret ;
    return $result ;
    return 1 ;
}

function authenticate_only_jwt (& $sn_from_jwt)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    if (! (isset ($_COOKIE ['jwt'])))
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'not login!' ;
        echo json_encode ($result) ;
        exit ;
    }
    $jwt_ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;

    if ($jwt_ret ['status'] === 'failed')
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = $ret ['message'] ;
        echo json_encode ($result, JSON_UNESCAPED_UNICODE) ;
        exit ;
    }

    $sn_from_jwt = $jwt_ret ['jwt_decode'] -> sn ;
    return 1 ;
}

}
?>
