<?php

require_once (__DIR__ . '/../authentication/jwt_fns.php') ;
require_once (__DIR__ . '/../authentication/authenticate_flow.php') ;

function shop_books_main ($uri)
{
    if ($_SERVER ['REQUEST_METHOD'] == 'POST')
    {
        post_book ($uri) ;
    }
    
    if ($_SERVER ['REQUEST_METHOD'] == 'GET')
    {
        get_books ($uri) ;
    }
}

function generate_random_string_8_bytes ()
{
    srand (time ()) ;
    $string = '0123456789abcdefghijklmnopqrstuvwxyz' ;
    $len_max = strlen ($string) - 1 ;
    $s = '' ;
    for ($i = 0 ; $i < 8 ; $i ++)
    {
        $r = rand (0, $len_max) ;
        $s = $s . substr ($string, $r, 1) ;
    }
    return $s ;
}

function check_new_book_basic_fields (& $new_book_fields)
{
    if (! (isset ($_POST ['book_price'])))
    {
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = '找不到欄位：價格' ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }
    if (empty ($_POST ['book_price']))
    {
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = '欄位：價格 => 沒有輸入資料' ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }
    
    $new_book_fields ['price'] = abs (intval ($_POST ['book_price'])) ;
    
    if (! (isset ($_POST ['book_title'])))
    {
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = '找不到欄位：書名' ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }
    if (empty ($_POST ['book_title']))
    {
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = '欄位：書名 => 沒有輸入資料' ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }

    require_once (__DIR__ . '/../mis_fns/cut_utf8_string.php') ;
    /*$ttnnpp = CutUtf8String\cut_utf8_string ($_POST ['book_title'], 120) ;
    $test_arr = array () ;
    $test_arr ['ori'] = $_POST ['book_title'] ;
    $test_arr ['cut'] = $ttnnpp ;
    $test_arr ['len'] = strlen ($ttnnpp) ;
    $test_arr ['message'] = 'test' ;
    $test_arr ['sub0'] = substr ($_POST ['book_title'], 0, 0) ;
    echo json_encode ($test_arr) ;
    exit ;*/

    // substr 抓最前面 120 char
    //$new_book_fields ['title'] = substr ($_POST ['book_title'], 0, 120) ;
    $new_book_fields ['title'] = CutUtf8String\cut_utf8_string ($_POST ['book_title'], 120) ;

    
    if (
        isset ($_POST ['book_author'])
        &&
        (! empty ($_POST ['book_author']))
        )
    {
        $new_book_fields ['set_author_flag'] = 1 ;
        
        // substr 抓最前面 60 char
        //$new_book_fields ['author'] = substr ($_POST ['book_author'], 0, 60) ;
        $new_book_fields ['author'] = CutUtf8String\cut_utf8_string ($_POST ['book_author'], 60) ;
    }
    
    if (
        isset ($_POST ['book_publication_date'])
        &&
        (! empty ($_POST ['book_publication_date']))
        )
    {
        if (! preg_match ('/\d{4}-\d{2}-\d{2}$/', $_POST ['book_publication_date']))
        {
            $new_book_fields ['result'] ['status'] = 'failed' ;
            $new_book_fields ['result'] ['message'] = '日期格式錯誤' ;
            echo json_encode ($new_book_fields ['result']) ;
            exit ;
        }
        $publication_date = $_POST ['book_publication_date'] ;
        $year_string = substr ($publication_date, 0, 4) ;
        $year = intval ($year_string) ;
        $month_string = substr ($publication_date, 5, 2) ;
        $month = intval ($month_string) ;
        if ($month < 1 || $month > 12)
        {
            $result ['month_value'] = $month ;
            $new_book_fields ['result'] ['status'] = 'failed' ;
            $new_book_fields ['result'] ['message'] = '月份不正確' ;
            echo json_encode ($new_book_fields ['result']) ;
            exit ;
        }
        $day_string = substr ($publication_date, 8, 2) ;
        $day = intval ($day_string) ;
        if ($day < 1 || $day > 31)
        {
            $result ['day_value'] = $day ;
            $new_book_fields ['result'] ['status'] = 'failed' ;
            $new_book_fields ['result'] ['message'] = '日期不正確' ;
            echo json_encode ($new_book_fields ['result']) ;
            exit ;
        }
        $new_book_fields ['set_publication_date_flag'] = 1 ;
        $new_book_fields ['publication_date'] = $_POST ['book_publication_date'] ;
    }
    
    if (
        isset ($_POST ['book_isbn'])
        &&
        (! empty ($_POST ['book_isbn']))
        )
    {
        $new_book_fields ['set_isbn_flag'] = 1 ;
        
        // substr 抓最前面 20 char
        $new_book_fields ['isbn'] = substr ($_POST ['book_isbn'], 0, 20) ;
    }
    
    if (
        isset ($_POST ['book_intro'])
        &&
        (! empty ($_POST ['book_intro']))
        )
    {
        $new_book_fields ['set_intro_flag'] = 1 ;
        
        // substr 抓最前面 十萬個 char
        //$new_book_fields ['intro'] = substr ($_POST ['book_intro'], 0, 100000) ;
        $new_book_fields ['intro'] = CutUtf8String\cut_utf8_string ($_POST ['book_intro'], 100000) ;
    }
}

function resize_webp_image ($file, $w, $h, $crop=FALSE)
{
    list ($width, $height) = getimagesize ($file) ;
    $r = $width / $height ;
    if ($crop)
    {
        if ($width > $height)
        {
            $width = ceil ($width - ($width * abs ($r - $w / $h))) ;
        }
        else
        {
            $height = ceil ($height - ($height * abs ($r - $w / $h))) ;
        }
        $new_width = $w ;
        $new_height = $h ;
    }
    else
    {
        if ($w / $h > $r)
        {
            $new_width = $h * $r ;
            $new_height = $h ;
        }
        else
        {
            $new_height = $w / $r ;
            $new_width = $w ;
        }
    }
    $src = imagecreatefromwebp ($file) ;
    $dst = imagecreatetruecolor ($new_width, $new_height) ;
    imagecopyresampled ($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height) ;
    return $dst ;
}

function resave_uploaded_img (& $new_book_fields)
{
    if (! isset ($_FILES ['the_file']))
    {
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = '沒有 the_file 資料' ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }
    
    if (! (is_uploaded_file ($_FILES ['the_file'] ['tmp_name'])))
    {
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = 'Problem: Possible file upload attack. Filename: ' . $_FILES ['the_file'] ['name'] ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }
    
    $generating_filename = time () . '-' . generate_random_string_8_bytes () ;
    $uploaded_file = '/var/webupload/' . $generating_filename . '.webp' ;
    $temp_filename = $_FILES ['the_file'] ['tmp_name'] ;
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
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = '不接受上傳檔案的格式' ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }
    
    // 能執行到這邊，代表一定有執行過 $im = @imagecreatefromXXXX
    if(! $im)
    {
        //echo 'Problem: file is not an image.' ;
        $new_book_fields ['result'] ['status'] = 'failed' ;
        $new_book_fields ['result'] ['message'] = '上傳的圖片檔案可能有損毀' ;
        echo json_encode ($new_book_fields ['result']) ;
        exit ;
    }
    imagewebp ($im, $uploaded_file) ;
    imagedestroy ($im) ;
    // $new_book_fields ['cover_filename'] = $uploaded_file ;
    $new_book_fields ['cover_filename'] = $generating_filename . '.webp' ;
    $resize_file = '/var/webupload/' . $generating_filename . '-thumbnail.webp' ;
    
    $im = resize_webp_image ($uploaded_file, 260, 260) ;
    imagewebp ($im, $resize_file) ;
    imagedestroy ($im) ;
    $new_book_fields ['thumbnail_filename'] = $generating_filename . '-thumbnail.webp' ;
    //$new_book_fields ['thumbnail_filename'] = $resize_file ;

    /*$new_book_fields ['result'] ['status'] = 'success' ;
    $new_book_fields ['result'] ['message'] = 'ok' ;
    echo json_encode ($result) ;*/
}

function post_book ($uri)
{
    /*$ret = \JwtAuthFns\jwt_decode ($_COOKIE ['jwt']) ;
    \JwtAuthFns\check_jwt_decode_retrieve ($ret) ;*/
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;

    $new_book_fields = array () ;
    $new_book_fields ['result'] = array () ;
    $new_book_fields ['result'] ['status'] = '' ;
    $new_book_fields ['result'] ['message'] = '' ;
    
    $new_book_fields ['provider'] = $sn_from_jwt ;//intval ($ret ['jwt_decode'] -> sn) ;
    
    $new_book_fields ['price'] = 0 ;
    
    $new_book_fields ['title'] = '' ;
    
    $new_book_fields ['set_author_flag'] = 0 ;
    $new_book_fields ['author'] = null ;
    
    $new_book_fields ['set_publication_date_flag'] = 0 ;
    $new_book_fields ['publication_date'] = null ;
    
    $new_book_fields ['set_isbn_flag'] = 0 ;
    $new_book_fields ['isbn'] = null ;
    
    $new_book_fields ['set_intro_flag'] = 0 ;
    $new_book_fields ['intro'] = null ;
    
    check_new_book_basic_fields ($new_book_fields) ;
    
    resave_uploaded_img ($new_book_fields) ;
    
    sql_query_creating_book_info ($new_book_fields) ;
    
    echo json_encode ($new_book_fields) ;
    //echo json_encode ($new_book_fields ['result']) ;
    exit ;
}

function sql_query_creating_book_info ($data)
{
    require_once (__DIR__ . '/../db_link/dbconnect_w_shop.php') ;
    $db_server = '' ;
    $db_user_name = '' ;
    $db_password = '' ;
    $db_name = '' ;
    dbconnect_w_shop ($db_server, $db_user_name, $db_password, $db_name) ;
    // 去參考 Fatal error: Uncaught exception 'mysqli_sql_exception' with message 'No index used in query/prepared statement'
    // https://stackoverflow.com/questions/5580039/fatal-error-uncaught-exception-mysqli-sql-exception-with-message-no-index-us

    //mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR) ;
    
    try
    {
        // #cn_1501
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1501 " . mysqli_connect_error ()) ;
        }
        //$query = 'SELECT * FROM book_info LIMIT ?' ;
        $query = 'INSERT INTO book_info (provider, price, title, author, publication_date, isbn, thumbnail_filename, cover_filename, introduction) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)' ;
        $stmt = $db -> prepare ($query) ;
        $limit = 10000 ;
        $stmt -> bind_param ('iisssssss', 
                             $data ['provider'],
                             $data ['price'],
                             $data ['title'],
                             $data ['author'],
                             $data ['publication_date'],
                             $data ['isbn'],
                             $data ['thumbnail_filename'],
                             $data ['cover_filename'],
                             $data ['intro']) ;
        $stmt -> execute () ;
        
        if ($stmt -> affected_rows > 0)
        {
            $data ['result'] ['status'] = 'success' ;
            $data ['result'] ['message'] = 'created ok' ;
        }
        else
        {
            $data ['result'] ['status'] = 'failed' ;
            $data ['result'] ['message'] = 'created failed' ;
        }
        echo json_encode ($data ['result']) ;
        exit ;
        
        $stmt -> store_result () ;
        $data ['result'] ['record_number'] = $stmt -> num_rows ;
        if ($stmt -> num_rows === 0)
        {
            $data ['result'] ['status'] = 'success' ;
            $data ['result'] ['message'] = 'connect to db ok' ;
            echo json_encode ($data) ;
            exit ;
        }
        
        $stmt -> bind_result ($col_book_serial_number, $col_provider, $col_price, $col_title, $col_author, $col_publication_date, $col_isbn, $col_thumbnail_filename, $col_cover_filename, $col_intro) ;
        
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        $data ['col_book_serial_number'] = $col_book_serial_number ;
        $data ['gettype col_book_serial_number'] = gettype ($col_book_serial_number) ;
        
        $data ['col_provider'] = $col_provider ;
        $data ['gettype col_provider'] = gettype ($col_provider) ;
        
        $data ['col_price'] = $col_price ;
        $data ['gettype col_price'] = gettype ($col_price) ;
        
        $data ['col_title'] = $col_title ;
        $data ['gettype col_title'] = gettype ($col_title) ;
        
        $data ['col_author'] = $col_author ;
        $data ['gettype col_author'] = gettype ($col_author) ;
        
        $data ['col_publication_date'] = $col_publication_date ;
        $data ['gettype col_publication_date'] = gettype ($col_publication_date) ;
        
        $data ['col_isbn'] = $col_isbn ;
        $data ['gettype col_isbn'] = gettype ($col_isbn) ;
        
        $data ['col_thumbnail_filename'] = $col_thumbnail_filename ;
        $data ['gettype col_thumbnail_filename'] = gettype ($col_thumbnail_filename) ;
        
        $data ['col_cover_filename'] = $col_cover_filename ;
        $data ['gettype col_cover_filename'] = gettype ($col_cover_filename) ;
        
        $data ['col_intro'] = $col_intro ;
        $data ['gettype col_intro'] = gettype ($col_intro) ;
        
        $data ['result'] ['status'] = 'success' ;
        $data ['result'] ['message'] = 'connect to db ok' ;
        echo json_encode ($data) ;
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

function get_books ($uri)
{
    $sn_from_jwt = 0 ;
    $ret = \AuthFlow\authenticate_flow ($sn_from_jwt) ;
    if ($uri == '')
    {
        sql_query_reading_books_list () ;
    }
    else
    {
        sql_query_reading_book_info (intval ($uri)) ;
    }
}

function sql_query_reading_books_list ()
{
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
        // #cn_1502
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1502 " . mysqli_connect_error ()) ;
        }
        $query = '
            SELECT `book_serial_number`, `title` 
            FROM `book_info` 
            WHERE `status` = 1 
            ORDER BY `book_serial_number` DESC
            LIMIT ?
        ' ;
        $stmt = $db -> prepare ($query) ;
        $limit = 200 ;
        $stmt -> bind_param ('i', $limit) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $result ['record_number'] = $stmt -> num_rows ;
        
        $stmt -> bind_result ($col_book_serial_number, $col_title) ;
        
        $i = 0 ;
        while ($stmt -> fetch ())
        {
            $result ['b_sn' . $i] = $col_book_serial_number ;
            $result ['b_ti' . $i] = htmlspecialchars ($col_title) ;
            $i ++ ;
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;
        echo json_encode ($result) ;
        exit ;
        
        $stmt -> store_result () ;
        $data ['result'] ['record_number'] = $stmt -> num_rows ;
        if ($stmt -> num_rows === 0)
        {
            $data ['result'] ['status'] = 'success' ;
            $data ['result'] ['message'] = 'connect to db ok' ;
            echo json_encode ($data) ;
            exit ;
        }
        
        $stmt -> bind_result ($col_book_serial_number, $col_provider, $col_price, $col_title, $col_author, $col_publication_date, $col_isbn, $col_thumbnail_filename, $col_cover_filename, $col_intro) ;
        
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;
        
        $data ['col_book_serial_number'] = $col_book_serial_number ;
        $data ['gettype col_book_serial_number'] = gettype ($col_book_serial_number) ;
        
        $data ['col_provider'] = $col_provider ;
        $data ['gettype col_provider'] = gettype ($col_provider) ;
        
        $data ['col_price'] = $col_price ;
        $data ['gettype col_price'] = gettype ($col_price) ;
        
        $data ['col_title'] = $col_title ;
        $data ['gettype col_title'] = gettype ($col_title) ;
        
        $data ['col_author'] = $col_author ;
        $data ['gettype col_author'] = gettype ($col_author) ;
        
        $data ['col_publication_date'] = $col_publication_date ;
        $data ['gettype col_publication_date'] = gettype ($col_publication_date) ;
        
        $data ['col_isbn'] = $col_isbn ;
        $data ['gettype col_isbn'] = gettype ($col_isbn) ;
        
        $data ['col_thumbnail_filename'] = $col_thumbnail_filename ;
        $data ['gettype col_thumbnail_filename'] = gettype ($col_thumbnail_filename) ;
        
        $data ['col_cover_filename'] = $col_cover_filename ;
        $data ['gettype col_cover_filename'] = gettype ($col_cover_filename) ;
        
        $data ['col_intro'] = $col_intro ;
        $data ['gettype col_intro'] = gettype ($col_intro) ;
        
        $data ['result'] ['status'] = 'success' ;
        $data ['result'] ['message'] = 'connect to db ok' ;
        echo json_encode ($data) ;
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

function sql_query_reading_book_info ($data)
{
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
        // #cn_1503
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1503" . mysqli_connect_error ()) ;
        }
        $query = 'SELECT `provider`, `price`, `title`, `author`, `publication_date`, `isbn`, `cover_filename`, `introduction` FROM book_info WHERE `book_serial_number` = ?' ;
        $query = '
        SELECT 
            bo.`provider`, 
            ba.`name`, 
            bo.`price`, 
            bo.`title`, 
            bo.`author`, 
            bo.`publication_date`, 
            bo.`isbn`, 
            bo.`cover_filename`, 
            bo.`introduction` 
        FROM `book_info` AS bo 
        INNER JOIN `basic_info` AS ba 
        ON bo.`provider` = ba.`basic_info_serial_number` 
        WHERE bo.`book_serial_number` = ?' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $data) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        
        $result ['record_number'] = $stmt -> num_rows ;
        if ($stmt -> num_rows <= 0)
        {
            throw new \Exception ("sql_query_reading_book_info result zero row !") ;
        }
        
        $stmt -> bind_result ($col_provider, $col_name, $col_price, $col_title, $col_author, $col_publication_date, $col_isbn, $col_cover_filename, $col_introduction) ;
        
        $i = 0 ;
        while ($stmt -> fetch ())
        {
        }
        
        $result ['book_serial_number'] = htmlspecialchars ($data) ;
        $result ['provider'] = htmlspecialchars ($col_provider) ;
        //$result ['provider_name'] = $col_name ;
        $result ['price'] = htmlspecialchars ($col_price) ;
        $result ['title'] = htmlspecialchars ($col_title) ;
        $result ['author'] = htmlspecialchars ($col_author) ;
        $result ['publication_date'] = htmlspecialchars ($col_publication_date) ;
        $result ['isbn'] = htmlspecialchars ($col_isbn) ;
        $result ['introduction'] = str_replace ("\r\n", "", nl2br (htmlspecialchars ($col_introduction), false)) ;
        
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

    require_once (__DIR__ . '/../db_link/dbconnect_r_account.php') ;
    dbconnect_r_account ($db_server, $db_user_name, $db_password, $db_name) ;
    mysqli_report (MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL) ;
    try
    {
        // #cn_1504
        $db = @ new \mysqli ($db_server, $db_user_name, $db_password, $db_name) ;
        if (mysqli_connect_errno ())
        {
            throw new \Exception ("Could not connect to database at #cn_1504" . mysqli_connect_error ()) ;
        }
        $query = 'SELECT `account_id` FROM `account` WHERE `serial_number` = ?' ;
        $stmt = $db -> prepare ($query) ;
        $stmt -> bind_param ('i', $col_provider) ;
        $stmt -> execute () ;
        $stmt -> store_result () ;
        $rows_number = 'Number of accounts: ' . $stmt -> num_rows ;
        if ($stmt -> num_rows === 0)
        {
            http_response_code (403) ;
            $result = array (
                'status' => 'failed',
                'message' => '沒有資料。'
            ) ;
            echo json_encode ($result) ;
            exit ;
        }
        $stmt -> bind_result ($col_account_id) ;
        
        while ($stmt -> fetch ())
        {
        }
        $stmt -> free_result () ;
        $stmt -> close () ;
        $db -> close () ;


        $result ['provider_name'] = $col_account_id ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'query ok' ;

        echo json_encode ($result) ;
        exit ;
    }
    catch (\Exception $e)
    {
        http_response_code (403) ;
        $result = array (
            'status' => 'error',
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
