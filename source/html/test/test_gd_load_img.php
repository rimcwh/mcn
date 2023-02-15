<?php
    header ("Content-type: image/png") ;
    $im = imagecreatefrompng( '/var/webupload/8100461__1219.png') ;
    imagepng ($im) ;
    imagedestroy ($im) ;
    exit ;
?>