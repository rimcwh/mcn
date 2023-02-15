<?php
namespace FakeJwtAuthFns
{
require (__DIR__ . '/../../php_packages/vendor/autoload.php') ;
require (__DIR__ . '/../../webconfig/ecc_key/read_test_fake_ecc_private_key.php') ;
require (__DIR__ . '/../../webconfig/ecc_key/read_test_fake_ecc_public_key.php') ;

function test_fake_jwt ()
{
    //return generate_jwt_literal () ;

    $jwt_token = '' ;
    $r = jwt_decode_test ($jwt_token) ;
    return $r ;
}

function generate_jwt_literal ()
{
    $privateKey = '' ;
    \ReadEccKey\read_test_fake_ecc_private_key ($privateKey) ;
    $iat = time () ;
    //$iat = 1670571415 ;
    $exp = $iat + 60 * 60 ;
    $rs = '' ;
    for ($i = 0 ; $i < 60 ; $i ++)
    {
        $rs .= generate_random_string () ;
    }
    $encode_body = array (
        'iss' => 'https://mcnsite.ddns.net',
        'iat' => $iat,
        'exp' => $exp,
        'sn' => 370,
        //'rs' => '', // 空字串
        //'rs' => 'rmpg14fsd', // 9碼字串
        //'rs' => 'rmpg14fsd8u', // 11碼字串
        'rs' => $rs, // 超長字串
        //'rs' => 'rmpg14fsd8' ,
    ) ;
    return \Firebase\JWT\JWT::encode ($encode_body, $privateKey, 'ES256') ;
}

function jwt_decode_test ($jwt_token)
{
    $result = [] ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    if (strlen ($jwt_token) > 1024)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'JWT token invalid!' ;
        echo json_encode ($result) ;
        exit ;
    }
    if (substr_compare ($jwt_token, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.', 0, strlen ('eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.')) != 0)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'JWT header invalid!' ;
        echo json_encode ($result) ;
        exit ;
    }
    $publicKey = '' ;
    \ReadEccKey\read_test_fake_ecc_public_key ($publicKey) ;
    try
    {
        $decode = \Firebase\JWT\JWT::decode ($jwt_token, new \Firebase\JWT\Key ($publicKey, 'ES256')) ;
        if (property_exists ($decode, 'iss') === false)
        {
            throw new \Exception ('JWT payload do not have iss field') ;
        }
        if (property_exists ($decode, 'iat') === false)
        {
            throw new \Exception ('JWT payload do not have iat field') ;
        }
        if (property_exists ($decode, 'exp') === false)
        {
            throw new \Exception ('JWT payload do not have exp field') ;
        }
        if (property_exists ($decode, 'sn') === false)
        {
            throw new \Exception ('JWT payload do not have sn field') ;
        }
        if (property_exists ($decode, 'rs') === false)
        {
            throw new \Exception ('JWT do not have rs field') ;
        }

        if (! ($decode -> iss === 'https://mcnsite.ddns.net'))
        {
            throw new \Exception ('JWT payload field iss invalid!') ;
        }

        if (strlen ($decode -> rs) != 10)
        {
            throw new \Exception ('JWT payload field rs invalid!') ;
        }

        if ($decode -> iat - 30 > time ())
        {
            throw new \Exception ('JWT time not in') ;
        }
        if ($decode -> exp + 30 < time ())
        {
            throw new \Exception ('JWT time out') ;
        }

        $stamp = time () ;
        if (($decode -> iat < $stamp)
            &&
            ($decode -> exp > $stamp)
            &&
            ($decode -> exp - $stamp < 900) // 至少經過 5 分鐘
        )
        {
            $new_jwt = \FakeJwtAuthFns\generate_jwt ($decode -> sn) ;
            setcookie ('jwt', $new_jwt, 0, '/', 'mcnsite.ddns.net', true, true) ; // save cookie
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

function generate_random_string ()
{
    $string = '0123456789abcdefghijklmnopqrstuvwxyz' ;
    $len_max = strlen ($string) - 1 ;
    $s = '' ;
    for ($i = 0 ; $i < 10 ; $i ++)
    {
        $r = rand (0, $len_max) ;
        $s = $s . substr ($string, $r, 1) ;
    }
    return $s ;
}

function generate_jwt ($sn)
{
    $privateKey = '' ;
    \ReadEccKey\read_ecc_private_key ($privateKey) ;
    $iat = time () ;
    $exp = $iat + 20 * 60 ;
    $encode_body = array (
        'iss' => 'https://mcnsite.ddns.net',
        'iat' => $iat,
        'exp' => $exp,
        'sn' => $sn,
        'rs' => generate_random_string () ,
    ) ;
    return \Firebase\JWT\JWT::encode ($encode_body, $privateKey, 'ES256') ;
}

function check_jwt_decode_retrieve ($ret)
{
    if ($ret ['status'] === 'failed')
    {
        $result = array (
            'status' => 'failed',
            'message' => $ret ['message'],
        ) ;
        echo json_encode ($result, JSON_UNESCAPED_UNICODE) ;
        exit ;
    }
}

function jwt_decode ($jwt_token)
{
    if (substr_compare ($jwt_token, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.', 0, strlen ('eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.')) != 0)
    {
        $result = array (
            'status' => 'failed',
            'message' => 'jwt title not correct'
        ) ;
        return $result ;
    }
    \ReadEccKey\read_test_fake_ecc_public_key ($publicKey) ;
    try
    {
        $decode = \Firebase\JWT\JWT::decode ($jwt_token, new \Firebase\JWT\Key ($publicKey, 'ES256')) ;
        if (property_exists ($decode, 'iat') === false)
        {
            throw new \Exception ('JWT do not have iat field') ;
        }
        if (property_exists ($decode, 'exp') === false)
        {
            throw new \Exception ('JWT do not have exp field') ;
        }
        if ($decode -> iat - 30 > time ())
        {
            throw new \Exception ('JWT time not in') ;
        }
        if ($decode -> exp + 30 < time ())
        {
            throw new \Exception ('JWT time out') ;
        }

        $stamp = time () ;
        if (($decode -> iat < $stamp)
            &&
            ($decode -> exp > $stamp)
            &&
            ($decode -> exp - $stamp < 900) // 至少經過 5 分鐘
        )
        {
            $new_jwt = \FakeJwtAuthFns\generate_jwt ($decode -> sn) ;
            setcookie ('jwt', $new_jwt, 0, '/', 'mcnsite.ddns.net', true, true) ; // save cookie
        }

        $result = array (
            'status' => 'success',
            'message' => 'ok',
            'jwt_decode' => $decode
        ) ;
        return $result ;
    }
    catch (\Exception $e)
    {
        $result = array (
            'status' => 'failed',
            'message' => $e -> getMessage ()
        ) ;
        return $result ;
    }
}

}
?>
