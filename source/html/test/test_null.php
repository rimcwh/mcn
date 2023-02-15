<?php
    echo "<body style = 'background-color: #000 ; color: #ddd ; font-size: 2rem ;'>" ;
    
    // 設定成 null 喔！（null 大寫小寫好像都可以）
    $nnn = nulL ;
    
    // 會印出 $nnn = end （變數 $nnn 沒有實際輸出的內容）
    echo '$nnn = ' . $nnn . ' end<br /><br />' ;
    
    // 會印出 gettype: NULL
    echo 'gettype: ' . gettype ($nnn) . '<br /><br />' ;
    if ($nnn == 0)
    {
        // 條件會成立
        echo '$nnn == 0 is true!<br /><br />' ;
    }
    
    if ($nnn === 0)
    {
        // 條件不會成立，走 false
        echo '$nnn === 0 is true!<br /><br />' ;
    }
    else
    {
        // 會走到這邊
        echo '$nnn === 0 is false!<br /><br />' ;
    }
    
    // 會印出 intval: 0
    echo 'intval: ' . intval ($nnn) . '<br /><br />' ;
    
    // 會印出 (int) $nnn: 0
    echo '(int) $nnn: ' . (int) $nnn . '<br /><br />' ;
    
    echo "</body>" ;
?>
