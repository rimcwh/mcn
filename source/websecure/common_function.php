<?php
namespace CommonFns
{

function check_sn_is_same ($sn1, $sn2)
{
    if (! (strval ($sn1) === $sn2))
    {
        // error
        $result = array (
            'status' => 'failed',
            'message' => 'serial number not matched.'
        ) ;
        echo json_encode ($result) ;
        exit ;
    }
}

}
?>
