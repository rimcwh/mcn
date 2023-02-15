<?php
namespace CutUtf8String
{

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

}

?>
