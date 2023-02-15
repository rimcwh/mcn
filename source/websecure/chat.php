<?php
//declare (encoding = 'UTF-8') ;
namespace Chat
{
//require_once ('db_link/dbconnect_r_chat.php') ;
function main ($uri)
{
    $pos = strpos ($uri, '/') ;
    if (false === $pos)
    {
        $folder = $uri ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
    }
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /

    if (0 == strcmp ('setting', $folder))
    {
        require ('chat/chat_setting_fns.php') ;
        chat_setting_main ($uri) ;
        exit ;
    }
    
    if (0 == strcmp ('public-room', $folder))
    {
        require ('chat/chat_public_room_fns.php') ;
        chat_public_room_main ($uri) ;
        exit ;
    }
    exit ;
}
}
?>
