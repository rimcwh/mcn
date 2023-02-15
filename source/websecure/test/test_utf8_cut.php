<?php

function cut_utf8_string ($source, $byte_len_max)
{
    $i = $byte_len_max ;
    while ($i > 0)
    {
        $byte_cutting_string = substr ($source, 0, $i) ;
        $convert_temp = mb_convert_encoding ($byte_cutting_string, 'UTF-8') ;
        $qq = strstr ($source, $convert_temp) ;
        if (gettype ($qq) == 'string')
        {
            break ;
        }
        $i -- ;
    }
    return $byte_cutting_string ;
}

function test_utf8_cut ()
{
    $request_body = file_get_contents ('php://input') ;
    $data = json_decode ($request_body, true) ;
    $result = array () ;
    $result ['status'] = 'testing utf8 cut' ;
    $result ['data'] = $data ['data'] ;
    $cutted_string = cut_utf8_string ($data ['data'], 120) ;
    $result ['cut'] = $cutted_string ;
    $result ['len'] = strlen ($cutted_string) ;

    echo json_encode ($result) ;
}
?>
