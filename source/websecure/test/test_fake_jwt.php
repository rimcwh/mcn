<?php

require_once (__DIR__ . '/../authentication/fake_jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/jwt_fns.php') ;

function test_fake_jwt_main ()
{
    $jwt_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJpc3MiOiJodHRwczovL21jbnNpdGUuZGRucy5uZXQiLCJpYXQiOjE2NzA1NzE0MTUsImV4cCI6MTY3MDU3NTAxNSwic24iOjM3MCwicnMiOiJybXBnMTRmc2Q4In0.7EyH-rhpX4Zgm0iMAmwQZuRmstYd8yj5Z90-sRaDVZh8SZ2ffBl97JLihAAtBqO29P63O6y9c7hqm7e8MgBuEA' ;
    $result = [] ;
    $result ['status'] = 'test fake jwt' ;
    //$result ['formal'] = \JwtAuthFns\jwt_decode ($jwt_token) ;
    $result ['test'] = \FakeJwtAuthFns\test_fake_jwt () ;
    echo json_encode ($result) ;
    exit ;
}

?>
