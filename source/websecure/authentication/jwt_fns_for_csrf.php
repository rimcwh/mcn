<?php
namespace JwtAuthFnsForCsrf
{

require (__DIR__ . '/../../php_packages/vendor/autoload.php') ;
require (__DIR__ . '/../../webconfig/ecc_key/read_for_csrf_ecc_private_key.php') ;
require (__DIR__ . '/../../webconfig/ecc_key/read_for_csrf_ecc_public_key.php') ;

function get_completed_csrf_token ()
{
    
}

function get_csrf_token_from_header ()
{
    $csrf_token = '' ;
    $headers = getallheaders () ;
    if (isset ($headers ['X-Csrf-Token']))
    {
        $csrf_token = $headers ['X-Csrf-Token'] ;
    }
    return $csrf_token ;
}

function generate_jwt_for_csrf ($payload_plaintext)
{
    $privateKey = '' ;
    \ReadEccKey\read_for_csrf_ecc_private_key ($privateKey) ;
    
    return \Firebase\JWT\JWT::encode ($payload_plaintext, $privateKey, 'ES256') ;
}

function jwt_decode_for_csrf ($jwt_token)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    if (strlen ($jwt_token) > 1024)
    {
        // JWT token invalid!
        $result ['status'] = 'failed' ;
        $result ['message'] = 'csrf token invalid!' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (substr_compare ($jwt_token, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.', 0, strlen ('eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.')) != 0)
    {
        // JWT header invalid!
        $result ['status'] = 'failed' ;
        $result ['message'] = 'csrf token invalid!' ;
        echo json_encode ($result) ;
        exit ;
    }
    $publicKey = '' ;
    \ReadEccKey\read_for_csrf_ecc_public_key ($publicKey) ;
    try
    {
        $decode = \Firebase\JWT\JWT::decode ($jwt_token, new \Firebase\JWT\Key ($publicKey, 'ES256')) ;
        if (property_exists ($decode, 'iss') === false)
        {
            // JWT payload do not have iss field
            throw new \Exception ('csrf token invalid!') ;
        }
        if (property_exists ($decode, 'iat') === false)
        {
            // JWT payload do not have iat field
            throw new \Exception ('csrf token invalid!') ;
        }
        if (property_exists ($decode, 'exp') === false)
        {
            // JWT payload do not have exp field
            throw new \Exception ('csrf token invalid!') ;
        }
        if (property_exists ($decode, 'sn') === false)
        {
            // JWT payload do not have sn field
            throw new \Exception ('csrf token invalid!') ;
        }
        if (property_exists ($decode, 'rs') === false)
        {
            // JWT do not have rs field
            throw new \Exception ('csrf token invalid!') ;
        }

        if (! ($decode -> iss === 'https://mcnsite.ddns.net'))
        {
            // JWT payload field iss invalid!
            throw new \Exception ('csrf token invalid!') ;
        }

        if (strlen ($decode -> rs) != 10)
        {
            // JWT payload field rs invalid!
            throw new \Exception ('csrf token invalid!') ;
        }

        if ($decode -> iat - 30 > time ())
        {
            // JWT time not in
            throw new \Exception ('csrf token invalid!') ;
        }
        if ($decode -> exp + 30 < time ())
        {
            // JWT time out
            throw new \Exception ('csrf token invalid!') ;
        }

        $stamp = time () ;
        if (($decode -> iat < $stamp)
            &&
            ($decode -> exp > $stamp)
            &&
            ($decode -> exp - $stamp < 900) // 至少經過 5 分鐘
        )
        {
            //$new_jwt = \JwtAuthFns\generate_jwt ($decode -> sn) ;
            //setcookie ('jwt', $new_jwt, 0, '/', 'mcnsite.ddns.net', true, true) ; // 把新的 JWT token 存到 cookie
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;
        $result ['jwt_decode'] = $decode ;
        return $result ;
    }
    catch (\Exception $e)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = $e -> getMessage () ;
        echo json_encode ($result) ;
        exit ;
    }
}

}
?>
