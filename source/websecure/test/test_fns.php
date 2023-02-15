<?php
    function valid_variable ($arr, $string_var_name, $max_len, $rule)
    {
        $q = $arr [$string_var_name] ;
        if (strlen ($q) > $max_len)
        {
            return -1 ;
        }
        if (preg_match ($rule, $q))
        {
            return -2 ;
        }
        return 0 ;
    }
?>
