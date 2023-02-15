<?php
namespace Test
{
function main ($uri)
{
    $path_segment = parse_uri_one_layer ($uri) ;

    if (0 == strcmp ('test-show-jwt-expired-time', $path_segment))
    {
        require ('test/test_show_jwt_expired_time.php') ;
        test_show_jwt_expired_time_main () ;
        exit ;
    }
    
    if (0 == strcmp ('test_wrong_account_password', $path_segment))
    {
        // echo 'enter login route!<br />' ;
        require ('test/001_test_wrong_account_password.php') ;
        test_wrong_account_password () ;
        exit ;
    }
    if (0 == strcmp ('test_wrong_prepare_cmd_type', $path_segment))
    {
        // echo 'enter login route!<br />' ;
        require ('test/002_test_wrong_prepare_cmd_type.php') ;
        test_wrong_prepare_cmd_type () ;
        exit ;
    }
    if (0 == strcmp ('test_environment', $path_segment))
    {
        require ('test/test_environment.php') ;
        test_environment () ;
        exit ;
    }
    if (0 == strcmp ('test_utf8_cut', $path_segment))
    {
        // echo 'enter login route!<br />' ;
        require ('test/test_utf8_cut.php') ;
        test_utf8_cut () ;
        exit ;
    }
    if (0 == strcmp ('test-parse-uri', $path_segment))
    {
        require ('test/test_parse_uri.php') ;
        test_parse_uri ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-sql-data-type', $path_segment))
    {
        echo 'test sql<br /><br />' ;
        require ('test/test_sql_data_type.php') ;
        test_sql_data_type_main ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-temp', $path_segment))
    {
        require ('test/test_temp.php') ;
        test_temp_main ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-splat', $path_segment))
    {
        require ('test/test_splat.php') ;
        test_splat_main ($uri) ;
        exit ;
    }
    if ($path_segment === 'test-other-process')
    {
        require ('test/test_other_process.php') ;
        test_other_process_main ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-time-stamp', $path_segment))
    {
        require ('test/test_time_stamp.php') ;
        test_time_stamp_main ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-try', $path_segment))
    {
        require ('test/test_try.php') ;
        test_try_main ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-aa', $path_segment))
    {
        require ('test/test_aa.php') ;
        test_aa_main ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-single-room-detail', $path_segment))
    {
        require ('test/test_single_room_detail.php') ;
        test_single_room_detail_main ($uri) ;
        exit ;
    }
    if (0 == strcmp ('test-fake-jwt', $path_segment))
    {
        require ('test/test_fake_jwt.php') ;
        test_fake_jwt_main ($uri) ;
        exit ;
    }
}
}
?>
