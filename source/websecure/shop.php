<?php
namespace Shop
{
function main ($uri)
{
    $path_segment = parse_uri_one_layer ($uri) ;

    if (0 == strcmp ('books', $path_segment))
    {
        require ('shop/shop_books_fns.php') ;
        shop_books_main ($uri) ;
        exit ;
    }
    
    if (0 == strcmp ('img', $path_segment))
    {
        require ('shop/shop_img_fns.php') ;
        shop_img_main ($uri) ;
        exit ;
    }
    
    if (0 == strcmp ('shopping-cart', $path_segment))
    {
        require ('shop/shop_shopping_cart_fns.php') ;
        shop_shopping_cart_main ($uri) ;
        exit ;
    }
    
    if (0 == strcmp ('basic-info', $path_segment))
    {
        require ('shop/shop_basic_info_fns.php') ;
        shop_basic_info_main ($uri) ;
        exit ;
    }

    if (0 == strcmp ('my-order', $path_segment))
    {
        require ('shop/shop_my_order_fns.php') ;
        shop_my_order_main ($uri) ;
        exit ;
    }

    if (0 == strcmp ('my-shop', $path_segment))
    {
        require ('shop/shop_my_shop_fns.php') ;
        shop_my_shop_main ($uri) ;
        exit ;
    }

    exit ;
}
}
?>
