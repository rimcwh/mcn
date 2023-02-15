<?php
    $file_pointer = "/var/webupload/1660305283-0u90a1x0.webp" ;
    exit ;
    if (! unlink($file_pointer))
    {
        echo ("$file_pointer cannot be deleted due to an error") ;
    }
    else
    {
        echo ("$file_pointer has been deleted");
    }
?>