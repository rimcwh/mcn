<?php
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

    function resize_png_image ($file, $w, $h, $crop=FALSE)
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
        $src = imagecreatefrompng ($file) ;
        $dst = imagecreatetruecolor ($new_width, $new_height) ;
        imagecopyresampled ($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height) ;
        return $dst ;
    }

    function check_new_book_field (& $result)
    {
        if (! (isset ($_POST ['book_title'])))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = '找不到欄位：書名' ;
            echo json_encode ($result) ;
            exit ;
        }
        if (empty ($_POST ['book_title']))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = '欄位：書名 => 沒有輸入資料' ;
            echo json_encode ($result) ;
            exit ;
        }
        if (
            isset ($_POST ['book_publication_date'])
            &&
            (! empty ($_POST ['book_publication_date']))
            )
        {
            if (! preg_match ('/\d{4}-\d{2}-\d{2}$/', $_POST ['book_publication_date']))
            {
                $result ['status'] = 'failed' ;
                $result ['message'] = '日期格式錯誤' ;
                echo json_encode ($result) ;
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
                $result ['err_msg'] = '月份不正確' ;
                echo json_encode ($result) ;
                exit ;
            }
            $day_string = substr ($publication_date, 8, 2) ;
            $day = intval ($day_string) ;
            if ($day < 1 || $day > 31)
            {
                $result ['day_value'] = $day ;
                $result ['err_msg'] = '日期不正確' ;
                echo json_encode ($result) ;
                exit ;
            }
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

    function test_upload_public_directory_resave ()
    {
        $result = array () ;
        check_new_book_field ($result) ;
        
        if (! (is_uploaded_file ($_FILES ['the_file'] ['tmp_name'])))
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = 'Problem: Possible file upload attack. Filename: ' . $_FILES['the_file']['name'] ;
            echo json_encode ($result) ;
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
        if (strcmp ($file_type, 'image/gif') == 0)
        {
            $correct_type = 1 ;
            $im = @imagecreatefromgif ($temp_filename) ;
        }
        if (strcmp ($file_type, 'image/webp') == 0)
        {
            $correct_type = 1 ;
            $im = @imagecreatefromwebp ($temp_filename) ;
        }
        if ($correct_type == 0)
        {
            $result ['status'] = 'failed' ;
            $result ['message'] = '不接受上傳檔案的格式' ;
            echo json_encode ($result) ;
            exit ;
        }
        
        // 能執行到這邊，代表一定有執行過 $im = @imagecreatefromXXXX
        if(! $im)
        {
            //echo 'Problem: file is not an image.' ;
            $result ['status'] = 'failed' ;
            $result ['message'] = '上傳的圖片檔案可能有損毀' ;
            echo json_encode ($result) ;
            exit ;
        }
        imagewebp ($im, $uploaded_file) ;
        imagedestroy ($im) ;
        $resize_file = '/var/webupload/' . $generating_filename . '-thumbnail.webp' ;
        
        $im = resize_webp_image ($uploaded_file, 260, 260) ;
        imagewebp ($im, $resize_file) ;
        imagedestroy ($im) ;

        $result ['status'] = 'success' ;
        $result ['message'] = 'ok' ;
        echo json_encode ($result) ;
        exit ;
    }

    function test_upload_public_directory ()
    {
        $uploaded_file = '/var/www/html/pub_upload/' . $_FILES ['the_file'] ['name'] ;
        if (is_uploaded_file ($_FILES ['the_file'] ['tmp_name']))
        {
            if (! move_uploaded_file ($_FILES ['the_file'] ['tmp_name'], $uploaded_file))
            {
                echo 'Problem: Could not move file to destination directory.' ;
                exit ;
            }
        }
        else
        {
            echo 'Problem: Possible file upload attack. Filename: ' ;
            echo $_FILES['the_file']['name'];
            exit ;
        }
        echo 'File uploaded successfully.';
    }

    function test_upload ()
    {
        phpinfo () ;
        if ($_FILES['the_file']['error'] > 0)
        {
            echo 'Problem: ';
            switch ($_FILES['the_file']['error'])
            {
                case 1:  
                    echo 'File exceeded upload_max_filesize.';
                    break;
                case 2:  
                    echo 'File exceeded max_file_size.';
                    break;
                case 3:  
                    echo 'File only partially uploaded.';
                    break;
                case 4:  
                    echo 'No file uploaded.';
                    break;
                case 6:  
                    echo 'Cannot upload file: No temp directory specified.';
                    break;
                case 7:  
                    echo 'Upload failed: Cannot write to disk.';
                    break;
                case 8:
                    echo 'A PHP extension blocked the file upload.';
                    break;
            }
            exit;
        }
        
        // Does the file have the right MIME type?
        if ($_FILES['the_file']['type'] != 'image/png')
        {
            echo 'Problem: file is not a PNG image.';
            exit;
        }
        // put the file where we'd like it
        echo $_FILES ['the_file'] ['name'] . '<br /><br />' ;
        echo $_FILES ['the_file'] ['tmp_name'] . '<br /><br />' ;
        $uploaded_file = '/var/webupload/' . $_FILES ['the_file'] ['name'] ;
        if (is_uploaded_file ($_FILES ['the_file'] ['tmp_name']))
        {
            if (! move_uploaded_file ($_FILES ['the_file'] ['tmp_name'], $uploaded_file))
            {
                echo 'Problem: Could not move file to destination directory.' ;
                exit ;
            }
        }
        else
        {
            echo 'Problem: Possible file upload attack. Filename: ' ;
            echo $_FILES['the_file']['name'];
            exit ;
        }
        echo 'File uploaded successfully.';
    }


    /*echo '    Here is test upload . php<br />' ;
    echo '<br />' ;
    echo 'text A: ' . $_POST ['texta'] . '<br />' ;
    echo '<br />' ;
    echo 'text B: ' . $_POST ['textb'] . '<br />' ;
    echo '<br />' ;
    echo 'text C: ' . $_POST ['textc'] . '<br />' ;
    echo '<br />' ;
    $pw_flag = 1 ;
    if ($_POST ['texta'] == 'PC3bAefe3f7#mGi326^G')
    {
        echo 'text A ok<br /><br />' ;
    }
    else
    {
        echo 'text A OK!<br /><br />' ;
        $pw_flag = 0 ;
    }
    if ($_POST ['textb'] == 'wD%zDhYqiEwip4AiF!qk')
    {
        echo 'text B ok<br /><br />' ;
    }
    else
    {
        echo 'text B OK!<br /><br />' ;
        $pw_flag = 0 ;
    }
    if ($_POST ['textc'] == '6dHZk@pX7A9%mjcV4fdf')
    {
        echo 'text C ok<br /><br />' ;
    }
    else
    {
        echo 'text C OK!<br /><br />' ;
        $pw_flag = 0 ;
    }
    if ($pw_flag == 0)
    {
        echo 'hello php upload~' ;
        exit ;
    }
    echo 'continue~~<br /><br />' ;*/
    
    //test_upload () ;
    //test_upload_public_directory () ;
    test_upload_public_directory_resave () ;
?>
