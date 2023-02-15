<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Upload a File</title>
    <link rel = "stylesheet" href = "css/test_global.css">
    <script src = "js/test_global.js"></script>
  </head>
  <body>

<?php
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

    function test_upload_public_directory_resave ()
    {
        $uploaded_file = '/var/www/html/pub_upload/' . $_FILES ['the_file'] ['name'] ;
        if (is_uploaded_file ($_FILES ['the_file'] ['tmp_name']))
        {
            $fn = $_FILES ['the_file'] ['tmp_name'] ;
            echo 'temp: ' . $fn . '<br /><br />' ;
            $im = imagecreatefrompng ($fn) ;
            if(!$im)
            {
                echo 'Problem: file is not an image.' ;
                exit ;
            }
            imagepng ($im, $uploaded_file) ;
            imagedestroy ($im) ;
            $resize_file = '/var/www/html/pub_upload/[re]' . $_FILES ['the_file'] ['name'] ;
            $im = resize_png_image ($uploaded_file, 50, 50) ;
            imagepng ($im, $resize_file) ;
            imagedestroy ($im) ;
            
        }
        else
        {
            echo 'Problem: Possible file upload attack. Filename: ' ;
            echo $_FILES['the_file']['name'] ;
            exit ;
        }
        echo 'File uploaded successfully.' ;
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


    echo '    Here is test upload . php<br />' ;
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
    echo 'continue~~<br /><br />' ;
    
    //test_upload () ;
    //test_upload_public_directory () ;
    test_upload_public_directory_resave () ;
?>

  </body>
</html>