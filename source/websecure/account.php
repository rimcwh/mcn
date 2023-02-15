<?php
namespace Account
{
require_once (__DIR__ . '/authentication/jwt_fns.php') ;
require_once (__DIR__ . '/authentication/authenticate_flow.php') ;
require (__DIR__ . '/account/account_fns.php') ;

function main ($uri)
{
    /*if (! (isset ($_COOKIE ['jwt'])))
    {
        $result = [] ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'not login!' ;
        echo json_encode ($result) ;
        exit ;
    }*/

    //$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;
    /*$result = [] ;
    $result ['qq_ret'] = $ret ;
    echo json_encode ($result) ;
    exit ;*/

    $path_segment = parse_uri_one_layer ($uri) ;
    $segment1 = $path_segment ;



    //$sn_from_jwt = $ret ['jwt_decode'] -> sn ;
    if (! (strval ($sn_from_jwt) === $segment1))
    {
        // error
        $result = array (
            'status' => 'failed',
            'message' => 'serial number not matched.'
        ) ;
        exit ;
    }

    if ($uri === '')
    {
        if ($_SERVER ['REQUEST_METHOD'] === 'GET')
        {
            get_account_info ($sn_from_jwt) ;
            exit ;
        }
        if ($_SERVER ['REQUEST_METHOD'] === 'PATCH')
        {
            patch_account_info ($sn_from_jwt) ;
            exit ;
        }
    }

    $path_segment = parse_uri_one_layer ($uri) ;
    $segment2 = $path_segment ;
    if ($uri === '')
    {
        if ($segment2 === 'verified-code-mail')
        {
            if ($_SERVER ['REQUEST_METHOD'] === 'POST')
            {
                post_verified_code_mail ($sn_from_jwt) ;
                exit ;
            }
        }
        if ($segment2 === 'email-verification')
        {
            if ($_SERVER ['REQUEST_METHOD'] === 'PATCH')
            {
                patch_email_verification ($sn_from_jwt) ;
                exit ;
            }
        }
    }

    exit ;
}

}
?>
