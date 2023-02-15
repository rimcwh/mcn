<?php
namespace Logout
{
function main ($uri)
{
    setcookie ('jwt', '', 1, '/', 'mcnsite.ddns.net', true, true) ; // remove cookie
    $result = array (
        'status' => 'success',
        'message' => 'ok',
    ) ;
    echo json_encode ($result) ;
    exit ;
}
}
?>
