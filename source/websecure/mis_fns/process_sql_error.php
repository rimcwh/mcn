<?php
namespace ProcessSqlError
{

function process_sql_error ($e)
{
    http_response_code (403) ;
    
    $error_log_msg = $e -> getMessage () . ' --- ' .
        'error code: [' . $e -> getCode () . '] --- ' .
        'error line: [' . $e -> getLine () . '] --- ' .
        'error file: ' . $e -> getFile () . ' --- ';
    
    $log_text = '[Date: ' . date ("Y-m-d, h:i:s A") . '] --- ' . $error_log_msg . "\n" ;
    
    error_log ($log_text, 3, '/var/weblog/sql-errors.log') ;
    
    $simply_output = array (
        'status' => 'failed',
        'message' => 'Server SQL Error',
    ) ;
    echo json_encode ($simply_output) ;
    exit ;
}

}

?>
