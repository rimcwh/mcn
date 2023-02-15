<?php
namespace Bingo
{
function main ($uri)
{
    if ($uri === '')
    {
        //echo 'uri === empty<br />' ;
    }
    if ($uri == '')
    {
        //echo 'uri == empty<br />' ;
    }
    if (strlen ($uri) === 0)
    {
        //echo 'len === 0<br />' ;
    }

    $path_segment = parse_uri_one_layer ($uri) ;

    if (0 == strcmp ('rooms', $path_segment))
    {
        require ('bingo/bingo_rooms_fns.php') ;
        bingo_rooms_main ($uri) ;
        exit ;
    }
    
    if (0 == strcmp ('rounds', $path_segment))
    {
        require ('bingo/bingo_rounds_fns.php') ;
        bingo_rounds_main ($uri) ;
        exit ;
    }
    
    if (0 == strcmp ('players', $path_segment))
    {
        require ('bingo/bingo_players_fns.php') ;
        bingo_players_main ($uri) ;
        exit ;
    }

    exit ;
}
}
?>
