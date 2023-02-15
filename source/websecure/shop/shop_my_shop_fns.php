<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function shop_my_shop_main ($uri)
{
    $result = array () ;
    $result ['uri'] = $uri ;
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

    if ($folder == '')
    {
        echo 'uri is empty, should create record with POST method' ;
        exit ;
    }

    if ($folder == 'orders')
    {
        shop_my_shop_route_orders ($uri) ;
    }

    if ($folder == 'launched-books')
    {
        shop_my_shop_route_launched_books ($uri) ;
    }
    if ($folder == 'launched-books-status')
    {
        shop_my_shop_route_launched_books_status ($uri) ;
    }
    if ($folder == 'launched-books-cover-image')
    {
        shop_my_shop_route_launched_books_cover_image ($uri) ;
    }
    echo json_encode ($result) ;
    exit ;
}

function shop_my_shop_route_orders ($uri)
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
    $user_id = intval ($folder) ;
    $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /

    if ($uri == '')
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_shop_my_shop_orders_list ($user_id) ;
        }
    }

    if ($uri == 'pdf')
    {
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_shop_my_shop_orders_list_generating_pdf ($user_id) ;
        }
    }
}

function shop_my_shop_route_launched_books ($uri)
{
    $pos = strpos ($uri, '/') ;
    if (false === $pos)
    {
        $folder = $uri ;
        $user_id = intval ($folder) ;
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_shop_my_shop_launched_books_list ($user_id) ;
            exit ;
        }
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
        $user_id = intval ($folder) ;
        $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
        $book_id = intval ($uri) ;
        /*$result ['status'] = 'testing' ;
        $result ['message'] = 'route user id and book id!' ;
        $result ['user_id'] = $user_id ;
        $result ['book_id'] = $book_id ;
        echo json_encode ($result) ;
        exit ;*/
        if ($_SERVER ['REQUEST_METHOD'] == 'GET')
        {
            get_shop_my_shop_launched_book_detail ($user_id, $book_id) ;
            exit ;
        }
        if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
        {
            patch_shop_my_shop_launched_book_detail ($user_id, $book_id) ;
            exit ;
        }
    }
    $result ['status'] = 'testing' ;
    $result ['message'] = 'hello here is my shop launched' ;
    $result ['after_uri'] = $uri ;
    $result ['int_after_uri'] = intval ($uri) ;
    echo json_encode ($result) ;
    exit ;
}

function shop_my_shop_route_launched_books_status ($uri)
{
    $pos = strpos ($uri, '/') ;
    if (false === $pos)
    {
        exit ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
        $user_id = intval ($folder) ;
        $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
        $book_id = intval ($uri) ;
        /*$result ['status'] = 'testing' ;
        $result ['message'] = 'route user id and book id!' ;
        $result ['user_id'] = $user_id ;
        $result ['book_id'] = $book_id ;
        echo json_encode ($result) ;
        exit ;*/
        if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
        {
            patch_shop_my_shop_launched_book_status ($user_id, $book_id) ;
        }
    }
}

function shop_my_shop_route_launched_books_cover_image ($uri)
{
    $pos = strpos ($uri, '/') ;
    if (false === $pos)
    {
        exit ;
    }
    else
    {
        $folder = substr ($uri, 0, $pos) ;
        $user_id = intval ($folder) ;
        $uri = substr ($uri, strlen ($folder) + 1) ; // + 1 因為還要拿掉後面的 sign /
        $book_id = intval ($uri) ;
        /*$result ['status'] = 'testing' ;
        $result ['message'] = 'route user id and book id!' ;
        $result ['user_id'] = $user_id ;
        $result ['book_id'] = $book_id ;
        echo json_encode ($result) ;
        exit ;*/
        if ($_SERVER ['REQUEST_METHOD'] == 'POST')
        {
            patch_shop_my_shop_launched_book_cover_image ($user_id, $book_id) ; // 直接用 patch 的 function 就可以了
        }
        if ($_SERVER ['REQUEST_METHOD'] == 'PATCH')
        {
            patch_shop_my_shop_launched_book_cover_image ($user_id, $book_id) ;
        }
    }
}

function get_shop_my_shop_orders_list ($user_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;


    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;

    if ($user_id != $sn_from_jwt)
    {
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }
    sql_query_reading_shop_my_shop_orders_list ($user_id, $result) ;

    $result ['status'] = 'success' ;
    $result ['message'] = 'query ok' ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_shop_my_shop_orders_list ($user_id, & $data)
{
    /*
        $query = '
            SELECT
                od.`order_serial_number`
                , oci.`orderer_id`
                , oci.`order_date`
                , oci.`name`
                , oci.`tel`
                , oci.`address`
                , od.`book_id`
                , bi.`isbn`
                , od.`title`
                , od.`number`
                , od.`price`
            FROM `order_detail` AS od
            INNER JOIN `book_info` AS bi
                ON od.`book_id` = bi.`book_serial_number`
            INNER JOIN `order_contact_info` AS oci
                ON od.`order_serial_number` = oci.`order_serial_number`
            WHERE bi.`provider` = ?
            ORDER BY oci.`order_date` DESC' ;
     */

    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    try
    {
        // #cn_2105
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2105" . mysqli_connect_error ()) ;
        }
        $query = '
            SELECT
                od.`order_serial_number`
                , oci.`orderer_id`
                , oci.`order_date`
                , oci.`name`
                , oci.`tel`
                , oci.`address`
                , od.`book_id`
                , bi.`isbn`
                , od.`title`
                , od.`number`
                , od.`price`
            FROM `order_detail` AS od
            INNER JOIN `book_info` AS bi
                ON od.`book_id` = bi.`book_serial_number`
            INNER JOIN `order_contact_info` AS oci
                ON od.`order_serial_number` = oci.`order_serial_number`
            WHERE bi.`provider` = ?
            ORDER BY oci.`order_date` DESC
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $user_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $data ['record_number'] = $stmt -> num_rows ;
        
        $stmt -> bind_result (
            $col_order_serial_number,
            $col_orderer_id,
            $col_order_date,
            $col_name,
            $col_tel,
            $col_address,
            $col_book_id,
            $col_isbn,
            $col_title,
            $col_number,
            $col_price
        ) ;
        
        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $data ['order_id' . $i] = $col_order_serial_number ;
            $data ['orderer_id' . $i] = $col_orderer_id ;
            $data ['order_date' . $i] = $col_order_date ;
            $data ['name' . $i] = htmlspecialchars ($col_name) ;
            $data ['tel' . $i] = htmlspecialchars ($col_tel) ;
            $data ['address' . $i] = htmlspecialchars ($col_address) ;
            $data ['book_id' . $i] = $col_book_id ;
            $data ['isbn' . $i] = htmlspecialchars ($col_isbn) ;
            $data ['title' . $i] = htmlspecialchars ($col_title) ;
            $data ['number' . $i] = $col_number ;
            $data ['price' . $i] = $col_price ;
            $i ++ ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function get_shop_my_shop_orders_list_generating_pdf ($user_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;


    if ($user_id != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    $data = array () ;
    
    sql_query_reading_shop_my_shop_orders_list ($user_id, $data) ;

    set_orders_list_pdf_attribute ($data) ;
    generate_orders_list_pdf ($data) ;

    exit ;
}

function set_pdf_basic_attribute (& $pdf)
{
    // set document information
    $pdf -> setCreator (PDF_CREATOR) ;
    $pdf -> setAuthor ('mcn') ;
    $pdf -> setTitle ('shop') ;
    $pdf -> setSubject ('orders list') ;
    $pdf -> setKeywords ('') ;

    // set default header data
    $pdf -> setHeaderData ('', 0, 'mcnsite', '[ my shop ] [ orders list ]', array (0, 64, 255), array (0, 64, 128)) ;
    $pdf -> setFooterData (array (0, 64, 0), array (0, 64, 128)) ;

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->setMargins(PDF_MARGIN_LEFT, /*PDF_MARGIN_TOP*/18, PDF_MARGIN_RIGHT);
    $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->setFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM - 12);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

    // ---------------------------------------------------------

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // Set font
    // dejavusans is a UTF-8 Unicode font, if you only need to
    // print standard ASCII chars, you can use core fonts like
    // helvetica or times to reduce file size.
    //$pdf->setFont('dejavusans', '', 14, '', true);
    
    
    $pdf->setFont('msjhbd', '', 12, '', true);
    //$pdf -> setFont ('sourcehansanstcnormal', '', 12, '', true) ;
    //$pdf -> setFont ('sourcehansanstcbold', '', 12, '', true) ;
}

function set_orders_list_pdf_attribute (& $data)
{
    $data ['order_id_width'] = 40 ;
    $data ['order_date_width'] = 30 ;
    $data ['name_width'] = 25 ;
    $data ['tel_width'] = 30 ;
    $data ['address_width'] = 142 ;

    $data ['book_id_width'] = 20 ;
    $data ['isbn_width'] = 40 ;
    $data ['book_title_width'] = 167 ;
    $data ['price_width'] = 20 ;
    $data ['number_width'] = 20 ;
}

function generate_orders_list_pdf_order_contact_info_header (& $pdf, & $data)
{
    $pdf -> setFont ('msjhbd', '', 12, '', true) ;
    $pdf -> SetFillColor (160, 160, 160) ;

    $pdf -> startTransaction () ;

    $x_previous = $pdf -> GetX () ;
    $y_previous = $pdf -> GetY () ;
    $x_now = $x_previous ;
    $y_now = $y_previous ;

    $pdf -> Cell ($data ['order_id_width'], 11, '', 1, 0, '', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $pdf -> SetXY ($x_previous, $y_previous) ;
    $output_text = '訂單' ;
    $pdf -> Cell ($data ['order_id_width'], NULL, $output_text, 0, 0, 'C') ;

    $pdf -> SetXY ($x_previous, $y_previous + 5.5) ;
    $output_text = '編號' ;
    $pdf -> Cell ($data ['order_id_width'], NULL, $output_text, 0, 0, 'C') ;

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['order_date_width'], 11, '', 1, 0, '', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $pdf -> SetXY ($x_previous, $y_previous) ;
    $output_text = '訂單' ;
    $pdf -> Cell ($data ['order_date_width'], NULL, $output_text, 0, 0, 'C') ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $pdf -> SetXY ($x_previous, $y_previous + 5.5) ;
    $output_text = '日期' ;
    $pdf -> Cell ($data ['order_date_width'], NULL, $output_text, 0, 0, 'C') ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $output_text = '姓名' ;
    $pdf -> Cell ($data ['name_width'], 11, $output_text, 1, 0, 'C', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $x_previous = $x_now ;
    $output_text = '電話' ;
    $pdf -> Cell ($data ['tel_width'], 11, $output_text, 1, 0, 'C', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $x_previous = $x_now ;
    $output_text = '地址' ;
    $pdf -> Cell ($data ['address_width'], 11, $output_text, 1, 0, 'C', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }
    $pdf -> Ln () ;
}

function generate_orders_list_pdf_order_contact_info_data (& $pdf, & $data, $index)
{
    $pdf -> setFont ('msjh', '', 12, '', true) ;
    //$pdf -> setFont ('sourcehansanstcnormal', '', 12, '', true) ;
    
    $pdf -> startTransaction () ;

    $x_previous = $pdf -> GetX () ;
    $y_previous = $pdf -> GetY () ;
    $x_now = $x_previous ;
    $y_now = $y_previous ;

    $pdf -> Cell ($data ['order_id_width'], 11, $data ['order_id' . $index], 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_data ($pdf, $data, $index) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> Cell ($data ['order_date_width'], 11, '', 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_data ($pdf, $data, $index) ;
        return ;
    }

    $pdf -> SetXY ($x_previous, $y_previous) ;
    $order_date_text = $data ['order_date' . $index] ;
    $pos = strpos ($order_date_text, ' ') ;
    $output_text = substr ($order_date_text, 0, $pos) ;
    $pdf -> Cell ($data ['order_date_width'], NULL, $output_text, 0, 0, 'C') ; // draw rect

    $pdf -> SetXY ($x_previous, $y_previous + 5.5) ;
    $output_text = substr ($order_date_text, strlen ($output_text) + 1) ;
    $pdf -> Cell ($data ['order_date_width'], NULL, $output_text, 0, 0, 'C') ; // draw rect

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['name_width'], 11, $data ['name' . $index], 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_data ($pdf, $data, $index) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['tel_width'], 11, $data ['tel' . $index], 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_data ($pdf, $data, $index) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $output_text = $data ['address' . $index] ;
    $text_width = $pdf -> GetStringWidth ($output_text, 'msjh', '', 12) ;
    //$text_width = $pdf -> GetStringWidth ($output_text, 'sourcehansanstcnormal', '', 12) ;
    if ($text_width <= $data ['address_width'])
    {
        $pdf -> Cell ($data ['address_width'], 11, $output_text, 1, 0, 'L', false, '', 0, false) ; // draw rect
    }
    else
    {
        $pdf -> writeHTMLCell ($data ['address_width'], 11, NULL, NULL, htmlspecialchars ($output_text), 1, 0, 0, true, '', false) ;
    }
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_data ($pdf, $data, $index) ;
        return ;
    }
    $pdf -> Ln () ;
}

function generate_orders_list_pdf_order_detail_header (& $pdf, & $data)
{
    $pdf -> setFont ('msjhbd', '', 12, '', true) ;
    $pdf -> SetFillColor (222, 222, 222) ;

    $pdf -> startTransaction () ;

    $x_previous = $pdf -> GetX () ;
    $y_previous = $pdf -> GetY () ;
    $x_now = $x_previous ;
    $y_now = $y_previous ;

    $pdf -> Cell ($data ['book_id_width'], 11, '', 1, 0, '', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_detail_header ($pdf, $data) ;
        return ;
    }

    $pdf -> SetXY ($x_previous, $y_previous) ;
    $output_text = '書籍' ;
    $pdf -> Cell ($data ['book_id_width'], NULL, $output_text, 0, 0, 'C') ;

    $pdf -> SetXY ($x_previous, $y_previous + 5.5) ;
    $output_text = '編號' ;
    $pdf -> Cell ($data ['book_id_width'], NULL, $output_text, 0, 0, 'C') ;

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['isbn_width'], 11, 'ISBN', 1, 0, 'C', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['book_title_width'], 11, '書名', 1, 0, 'C', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['price_width'], 11, '單價', 1, 0, 'C', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['number_width'], 11, '數量', 1, 0, 'C', TRUE) ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
        return ;
    }
    $pdf -> Ln () ;
}

function generate_orders_list_pdf_order_detail_data (& $pdf, & $data, $index)
{
    $pdf -> setFont ('msjh', '', 12, '', true) ;
    //$pdf -> setFont ('sourcehansanstcnormal', '', 12, '', true) ;

    $pdf -> startTransaction () ;

    $x_previous = $pdf -> GetX () ;
    $y_previous = $pdf -> GetY () ;
    $x_now = $x_previous ;
    $y_now = $y_previous ;

    $pdf -> Cell ($data ['book_id_width'], 11, $data ['book_id' . $index], 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_detail_data ($pdf, $data, $index) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['isbn_width'], 11, $data ['isbn' . $index], 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_detail_data ($pdf, $data, $index) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $output_text = $data ['title' . $index] ;
    $text_width = $pdf -> GetStringWidth ($output_text, 'msjh', '', 12) ;
    //$text_width = $pdf -> GetStringWidth ($output_text, 'sourcehansanstcnormal', '', 12) ;
    if ($text_width <= $data ['book_title_width'])
    {
        $pdf -> Cell ($data ['book_title_width'], 11, $output_text, 1, 0, 'L', false, '', 0, false) ; // draw rect
    }
    else
    {
        $pdf -> writeHTMLCell ($data ['book_title_width'], 11, NULL, NULL, htmlspecialchars ($output_text), 1, 0, 0, true, '', false) ;
    }
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_detail_data ($pdf, $data, $index) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['price_width'], 11, $data ['price' . $index], 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_detail_data ($pdf, $data, $index) ;
        return ;
    }

    $x_previous = $x_now ;
    $pdf -> SetXY ($x_previous, $y_previous) ;
    $pdf -> Cell ($data ['number_width'], 11, $data ['number' . $index], 1, 0, 'C') ; // draw rect
    $x_now = $pdf -> GetX () ;
    $y_now = $pdf -> GetY () ;
    if ($y_previous > $y_now)
    {
        $pdf = $pdf -> rollbackTransaction () ;
        $pdf -> AddPage ('L') ;
        generate_orders_list_pdf_order_detail_data ($pdf, $data, $index) ;
        return ;
    }
    $pdf -> Ln () ;
}

function generate_orders_list_pdf (& $data)
{
    require (__DIR__ . '/../pdf/pdf_fns.php') ;
    // create new PDF document
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false) ;
    
    set_pdf_basic_attribute ($pdf) ;

    //$pdf -> SetFillColor (222, 222, 222) ;

    $pdf->setFont('msjh', '', 12, '', true) ;
    //$pdf -> setFont ('sourcehansanstcnormal', '', 12, '', true) ;
    //$pdf -> setFont ('cid0ct', '', 12, '', true) ;
    //$pdf -> setFont ('droidsansfallback', '', 12, '', true) ;

    $pdf -> AddPage('L') ;

    $same_order_id = 0 ;
    $next_order_same = 0 ;
    for ($i = 0 ; $i < $data ['record_number'] ; $i ++)
    {
        if ($i != 0)
        {
            if ($data ['order_id' . $i] == $data ['order_id' . ($i - 1)])
            {
                $same_order_id = 1 ;
            }
            else
            {
                $same_order_id = 0 ;
            }
        }

        if ($same_order_id == 0)
        {
            if ($i != 0)
            {
                $pdf -> Cell (NULL, NULL, '', 0, 1) ;
            }
            generate_orders_list_pdf_order_contact_info_header ($pdf, $data) ;
            generate_orders_list_pdf_order_contact_info_data ($pdf, $data, $i) ;
            generate_orders_list_pdf_order_detail_header ($pdf, $data) ;
        }
        generate_orders_list_pdf_order_detail_data ($pdf, $data, $i) ;
    }

    ob_end_clean () ;

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $pdf->Output('example_001.pdf', 'I') ;
}

function get_shop_my_shop_launched_books_list ($sn)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/
    
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if ($sn != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    sql_query_reading_shop_my_shop_launched_books_list ($sn_from_jwt) ;


    $result = array () ;
    $result ['status'] = 'testing' ;
    $result ['message'] = 'here is get_shop_my_shop_launched_books_list' ;
    $result ['sn'] = $sn ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_shop_my_shop_launched_books_list ($sn)
{
    /*
        $query = '
            SELECT
                `book_serial_number`,
                `title`,
                `isbn`
            FROM `book_info`
            WHERE `provider` = ?
        ' ;
     */

    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    try
    {
        // #cn_2101
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2101" . mysqli_connect_error ()) ;
        }
        $query = '
            SELECT
                `book_serial_number`,
                `title`,
                `isbn`
            FROM `book_info`
            WHERE `provider` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $sn) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $result ['record_number'] = $stmt -> num_rows ;
        
        $stmt -> bind_result ($col_book_serial_number, $col_title, $col_isbn) ;
        
        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $result ['book_id' . $i] = $col_book_serial_number ;
            $result ['title' . $i] = htmlspecialchars ($col_title) ;
            $result ['isbn' . $i] = htmlspecialchars ($col_isbn) ;
            $i ++ ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
        echo json_encode ($result) ;
        exit ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function get_shop_my_shop_launched_book_detail ($user_id, $book_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if ($user_id != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }
    sql_query_reading_shop_my_shop_launched_book_detail ($user_id, $book_id) ;
}

function sql_query_reading_shop_my_shop_launched_book_detail ($user_id, $book_id)
{
    /*
        $query = '
            SELECT
                `book_serial_number`, -- book_id
                `provider`, -- user_id
                `title`,
                `author`,
                `publication_date`,
                `isbn`,
                `price`,
                `introduction`
            FROM `book_info`
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
     */

    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    try
    {
        // #cn_2101
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2101" . mysqli_connect_error ()) ;
        }
        $query = '
            SELECT
                `book_serial_number`, -- book_id
                `provider`, -- user_id
                `status`,
                `title`,
                `author`,
                `publication_date`,
                `isbn`,
                `price`,
                `introduction`
            FROM `book_info`
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $user_id, $book_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        if ($stmt -> num_rows != 1)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'permission denied' ;
            echo json_encode ($result) ;
            exit ;
        }
        
        $result ['record_number'] = $stmt -> num_rows ;

        $stmt -> bind_result (
            $col_book_serial_number,
            $col_provider,
            $col_book_status,
            $col_title,
            $col_author,
            $col_publication_date,
            $col_isbn,
            $col_price,
            $col_introduction
        ) ;
        
        while ($stmt -> fetch ())
        {
            $result ['book_id'] = $col_book_serial_number ;
            $result ['provider'] = $col_provider ;
            $result ['book_status'] = $col_book_status ;
            $result ['title'] = htmlspecialchars ($col_title) ;
            $result ['author'] = htmlspecialchars ($col_author) ;
            $result ['publication_date'] = htmlspecialchars ($col_publication_date) ;
            $result ['isbn'] = htmlspecialchars ($col_isbn) ;
            $result ['price'] = htmlspecialchars ($col_price) ;
            $result ['intro'] = htmlspecialchars ($col_introduction) ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
        echo json_encode ($result) ;
        exit ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function patch_shop_my_shop_launched_book_detail ($user_id, $book_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if ($user_id != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    
    $request_body = file_get_contents ('php://input') ;
    // 要寫判斷 === false 還有 ! isset 還有 empty ( 應該要弄成一個 function 哪！ )

    $data = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array

    $updating_book_fields = array () ;

    require_once (__DIR__ . '/../mis_fns/cut_utf8_string.php') ;

    if (
        (isset ($data ['title']))
        &&
        (! empty ($data ['title']))
    )
    {
        $updating_book_fields ['title'] = CutUtf8String\cut_utf8_string ($data ['title'], 120) ;
    }
    if (
        (isset ($data ['author']))
        &&
        (! empty ($data ['author']))
    )
    {
        $updating_book_fields ['author'] = CutUtf8String\cut_utf8_string ($data ['author'], 60) ;
    }
    if (
        (isset ($data ['publication_date']))
        &&
        (! empty ($data ['publication_date']))
    )
    {
        if (! preg_match ('/\d{4}-\d{2}-\d{2}$/', $data ['publication_date']))
        {
            $result = array () ;
            $result ['status'] = 'failed' ;
            $result ['message'] = '日期格式錯誤' ;
            echo json_encode ($result) ;
            exit ;
        }
        $publication_date = $data ['publication_date'] ;
        $year_string = substr ($publication_date, 0, 4) ;
        $year = intval ($year_string) ;
        $month_string = substr ($publication_date, 5, 2) ;
        $month = intval ($month_string) ;
        if ($month < 1 || $month > 12)
        {
            $result = array () ;
            $result ['status'] = 'failed' ;
            $result ['message'] = '月份不正確' ;
            echo json_encode ($result) ;
            exit ;
        }
        $day_string = substr ($publication_date, 8, 2) ;
        $day = intval ($day_string) ;
        if ($day < 1 || $day > 31)
        {
            $result = array () ;
            $result ['status'] = 'failed' ;
            $result ['message'] = '日期不正確' ;
            echo json_encode ($result) ;
            exit ;
        }
        $updating_book_fields ['publication_date'] = $data ['publication_date'] ;
    }
    if (
        (isset ($data ['isbn']))
        &&
        (! empty ($data ['isbn']))
    )
    {
        $updating_book_fields ['isbn'] = substr ($data ['isbn'], 0, 20) ;
    }
    if (
        (isset ($data ['price']))
        &&
        (!empty ($data ['price']))
    )
    {
        $updating_book_fields ['price'] = abs (intval ($data ['price'])) ;
    }
    if (
        (isset ($data ['intro']))
        &&
        (! empty ($data ['intro']))
    )
    {
        $updating_book_fields ['intro'] = CutUtf8String\cut_utf8_string ($data ['intro'], 100000) ;
    }




    $result = array () ;
    $result ['status'] = 'testing' ;
    $result ['message'] = 'here is patch detail' ;
    $result ['book_id'] = $book_id ;
    $result ['title'] = $updating_book_fields ['title'] ;
    $result ['author'] = $updating_book_fields ['author'] ;
    $result ['publication_date'] = $updating_book_fields ['publication_date'] ;
    $result ['isbn'] = $updating_book_fields ['isbn'] ;
    $result ['price'] = $updating_book_fields ['price'] ;
    //$result ['intro'] = $updating_book_fields ['intro'] ;
    if (empty ($data ['author']))
    {
        $result ['empty_author'] = 'true' ;
    }
    else
    {
        $result ['empty_author'] = 'false' ;
    }

    sql_query_updating_shop_my_shop_launched_book_detail ($user_id, $book_id, $updating_book_fields) ;

    echo json_encode ($result) ;
    exit ;
}

function sql_query_updating_shop_my_shop_launched_book_detail ($user_id, $book_id, $data)
{
    /*
        $query = '
            UPDATE `book_info`
            SET
                `title` = IFNULL (?, `title`),
                `author` = IFNULL (?, `author`),
                `publication_date` = IFNULL (?, `publication_date`),
                `isbn` = IFNULL (?, `ISBN`),
                `price` = IFNULL (?, `price`),
                `introduction` = IFNULL (?, `introduction`)
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
     */
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    try
    {
        // #cn_2102
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2102" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT
                `isbn`
            FROM `book_info`
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $user_id, $book_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        if ($stmt -> num_rows != 1)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'permission denied' ;
            echo json_encode ($result) ;
            exit ;
        }
        $stmt -> close () ;

        $query = '
            UPDATE `book_info`
            SET
                `title` = IFNULL (?, `title`),
                `author` = IFNULL (?, `author`),
                `publication_date` = IFNULL (?, `publication_date`),
                `isbn` = IFNULL (?, `ISBN`),
                `price` = IFNULL (?, `price`),
                `introduction` = IFNULL (?, `introduction`)
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'ssssisii', 
            $data ['title'], 
            $data ['author'], 
            $data ['publication_date'], 
            $data ['isbn'], 
            $data ['price'], 
            $data ['intro'], 
            $user_id, 
            $book_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        //$result ['num_rows'] = $stmt -> num_rows ;
        //$result ['affected_rows'] = $stmt -> affected_rows ;
        //$result ['dc_insert_id'] = $stmt -> insert_id ;
        //$result ['param_count'] = $stmt -> param_count ;
        //$result ['dc_field_count'] = $stmt -> field_count ;
        //$result ['dc_errno'] = $stmt -> errno ;
        //$result ['dc_error'] = $stmt -> error ;
        //$result ['dc_error_list'] = $stmt -> error_list ;
        //$result ['sqlstate'] = $stmt -> sqlstate ;
        //$result ['id'] = $stmt -> id ;
        
        if ($stmt -> affected_rows != 1)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            echo json_encode ($result) ;
            exit ;
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
        echo json_encode ($result) ;
        exit ;
        
        $result ['record_number'] = $stmt -> num_rows ;

        $stmt -> bind_result (
            $col_book_serial_number,
            $col_provider,
            $col_title,
            $col_author,
            $col_publication_date,
            $col_isbn,
            $col_price,
            $col_introduction
        ) ;
        
        while ($stmt -> fetch ())
        {
            $result ['book_id'] = $col_book_serial_number ;
            $result ['provider'] = $col_provider ;
            $result ['title'] = htmlspecialchars ($col_title) ;
            $result ['author'] = htmlspecialchars ($col_author) ;
            $result ['publication_date'] = htmlspecialchars ($col_publication_date) ;
            $result ['isbn'] = htmlspecialchars ($col_isbn) ;
            $result ['price'] = htmlspecialchars ($col_price) ;
            $result ['intro'] = htmlspecialchars ($col_introduction) ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
        echo json_encode ($result) ;
        exit ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function patch_shop_my_shop_launched_book_status ($user_id, $book_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if ($user_id != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    
    $request_body = file_get_contents ('php://input') ;
    // 要寫判斷 === false 還有 ! isset 還有 empty ( 應該要弄成一個 function 哪！ )

    $data = json_decode ($request_body, true) ; // input 從 string 轉成一個 associative array

    $fields = array () ;

    
    if (isset ($data ['status']))
    {
        $fields ['status'] = intval ($data ['status']) ;
        if ($fields ['status'] != 1)
        {
            $fields ['status'] = 0 ;
        }
    }
    else
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = '找不到欄位：status' ;
        echo json_encode ($result) ;
        exit ;
    }

    sql_query_updating_shop_my_shop_launched_book_status ($user_id, $book_id, $fields) ;
    exit ;
}

function sql_query_updating_shop_my_shop_launched_book_status ($user_id, $book_id, $data)
{
    /*
        $query = '
            UPDATE `book_info`
            SET
                `status` = ?
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
     */
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    try
    {
        // #cn_2103
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2103" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT
                `isbn`
            FROM `book_info`
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $user_id, $book_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        if ($stmt -> num_rows != 1)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'permission denied' ;
            echo json_encode ($result) ;
            exit ;
        }
        $stmt -> close () ;

        $query = '
            UPDATE `book_info`
            SET
                `status` = ?
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'iii', 
            $data ['status'], 
            $user_id, 
            $book_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        //$result ['num_rows'] = $stmt -> num_rows ;
        //$result ['affected_rows'] = $stmt -> affected_rows ;
        //$result ['dc_insert_id'] = $stmt -> insert_id ;
        //$result ['param_count'] = $stmt -> param_count ;
        //$result ['dc_field_count'] = $stmt -> field_count ;
        //$result ['dc_errno'] = $stmt -> errno ;
        //$result ['dc_error'] = $stmt -> error ;
        //$result ['dc_error_list'] = $stmt -> error_list ;
        //$result ['sqlstate'] = $stmt -> sqlstate ;
        //$result ['id'] = $stmt -> id ;
        
        if ($stmt -> affected_rows != 1)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            echo json_encode ($result) ;
            exit ;
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
        echo json_encode ($result) ;
        exit ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

function check_upload_img_method_field ()
{
    if (! isset ($_POST ['method_for']))
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = '沒有欄位 method_for' ;
        echo json_encode ($result) ;
        exit ;
    }

    if (strtoupper ($_POST ['method_for']) != 'PATCH')
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = '欄位 method_for 的值不正確' ;
        echo json_encode ($result) ;
        exit ;
    }
}

function check_upload_img_file_field ($field_name)
{
    if (! isset ($_FILES [$field_name]))
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = '沒有 ' . $field_name . ' 欄位' ;
        echo json_encode ($result) ;
        exit ;
    }
    
    if (! (is_uploaded_file ($_FILES [$field_name] ['tmp_name'])))
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'Problem: Possible file upload attack. Filename: ' . $_FILES [$field_name] ['name'] ;
        echo json_encode ($result) ;
        exit ;
    }
}

function resave_upload_img ($temp_filename, $saving_filename)
{
    $finfo = finfo_open (FILEINFO_MIME_TYPE) ;
    $file_type = finfo_file ($finfo, $temp_filename) ;
    $correct_type = 0 ;
    if (strcmp ($file_type, 'image/png') == 0)
    {
        $correct_type = 1 ;
        $im = @imagecreatefrompng ($temp_filename) ;
    }
    if (strcmp ($file_type, 'image/jpeg') == 0)
    {
        $correct_type = 1 ;
        $im = @imagecreatefromjpeg ($temp_filename) ;
    }
    if (strcmp ($file_type, 'image/webp') == 0)
    {
        $correct_type = 1 ;
        $im = @imagecreatefromwebp ($temp_filename) ;
    }
    if ($correct_type == 0)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = '不接受上傳檔案的格式' ;
        echo json_encode ($result) ;
        exit ;
    }
    
    // 能執行到這邊，代表一定有執行過 $im = @imagecreatefromXXXX
    if(! $im)
    {
        //echo 'Problem: file is not an image.' ;
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = '上傳的圖片檔案可能有損毀' ;
        echo json_encode ($result) ;
        exit ;
    }
    imagewebp ($im, $saving_filename) ; // save image as webp
    imagedestroy ($im) ;
}

function copy_binary_file ($obj, $src)
{
    $handle = fopen ($src, "rb") ;
    $contents = fread ($handle, filesize ($src)) ;
    fclose ($handle) ;

    $handle = fopen ($src, "wb") ;
}

function patch_shop_my_shop_launched_book_cover_image ($user_id, $book_id)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;
    $sn_from_jwt = $ret ['jwt_decode'] -> sn ;*/

    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    if ($user_id != $sn_from_jwt)
    {
        $result = array () ;
        $result ['status'] = 'failed' ;
        $result ['message'] = 'denied' ;
        echo json_encode ($result) ;
        exit ;
    }

    $data = sql_query_reading_shop_my_shop_launched_book_cover_path ($user_id, $book_id) ;

    check_upload_img_method_field () ;
    check_upload_img_file_field ('the_file') ;

    require_once (__DIR__ . '/shop_books_fns.php') ;
    $generating_filename = time () . '-' . generate_random_string_8_bytes () ;
    
    $uploaded_file = '/var/webupload/' . $generating_filename . '.webp' ;
    $temp_filename = $_FILES ['the_file'] ['tmp_name'] ;
    resave_upload_img ($temp_filename, $uploaded_file) ;
    
    $resize_file = '/var/webupload/' . $generating_filename . '-thumbnail.webp' ;
    $im = resize_webp_image ($uploaded_file, 260, 260) ;
    imagewebp ($im, $resize_file) ;
    imagedestroy ($im) ;

    copy ($uploaded_file, '/var/webupload/' . $data ['cover']) ;
    copy ($resize_file, '/var/webupload/' . $data ['thumbnail']) ;
    if (! unlink ($uploaded_file))
    {
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $uploaded_file . 'cannot be deleted due to an error' . "\n", 3, "/var/weblog/exe-errors.log") ;
    }
    if (! unlink ($resize_file))
    {
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $resize_file . 'cannot be deleted due to an error' . "\n", 3, "/var/weblog/exe-errors.log") ;
    }

    $result = array () ;
    $result ['status'] = 'success' ;
    $result ['message'] = 'ok' ;
    echo json_encode ($result) ;
    exit ;
}

function sql_query_reading_shop_my_shop_launched_book_cover_path ($user_id, $book_id)
{
    /*
        $query = '
            UPDATE `book_info`
            SET
                `status` = ?
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
     */
    require_once (__DIR__ . '/../db_link/dbconnect_r_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_r_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    
    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    $result = array () ;
    $result ['status'] = '' ;
    $result ['message'] = '' ;
    
    try
    {
        // #cn_2104
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_2104" . mysqli_connect_error ()) ;
        }

        $query = '
            SELECT
                `cover_filename`,
                `thumbnail_filename`
            FROM `book_info`
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;

        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('ii', $user_id, $book_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        if ($stmt -> num_rows != 1)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'permission denied' . ' user_id: ' . $user_id . ' book_id: ' . $book_id ;
            echo json_encode ($result) ;
            exit ;
        }
        $stmt -> bind_result ($col_cover_filename, $col_thumbnail_filename) ;
        while ($stmt -> fetch ())
        {
        }
        $stmt -> close () ;

        $data = array () ;
        $data ['cover'] = $col_cover_filename ;
        $data ['thumbnail'] = $col_thumbnail_filename ;
        return $data ;

        $query = '
            UPDATE `book_info`
            SET
                `status` = ?
            WHERE `provider` = ? AND `book_serial_number` = ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param (
            'iii', 
            $data ['status'], 
            $user_id, 
            $book_id) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;

        //$result ['num_rows'] = $stmt -> num_rows ;
        //$result ['affected_rows'] = $stmt -> affected_rows ;
        //$result ['dc_insert_id'] = $stmt -> insert_id ;
        //$result ['param_count'] = $stmt -> param_count ;
        //$result ['dc_field_count'] = $stmt -> field_count ;
        //$result ['dc_errno'] = $stmt -> errno ;
        //$result ['dc_error'] = $stmt -> error ;
        //$result ['dc_error_list'] = $stmt -> error_list ;
        //$result ['sqlstate'] = $stmt -> sqlstate ;
        //$result ['id'] = $stmt -> id ;
        
        if ($stmt -> affected_rows != 1)
        {
            $result ['status'] = 'success' ;
            $result ['message'] = 'no change' ;
            echo json_encode ($result) ;
            exit ;
        }

        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
        echo json_encode ($result) ;
        exit ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'failed',
            'message' => 'Server SQL Error',
        ) ;
        echo json_encode ($result) ;
        $error_log_msg = $e -> getMessage () . ' --- ' .
            'error code: [' . $e -> getCode () . '] --- ' .
            'error line: [' . $e -> getLine () . '] --- ' .
            'error file: ' . $e -> getFile () . ' --- ';
        error_log ("[Date: " . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n", 3, "/var/weblog/sql-errors.log") ;
        exit ;
    }
}

?>

