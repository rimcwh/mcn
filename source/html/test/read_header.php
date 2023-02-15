<?php
    header ('X-CSRF-token: joe.sHi.j.ge') ;

    foreach (getallheaders() as $name => $value)
    {
        echo "$name: $value" . '<br />';
    }
?>
