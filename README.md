# mcn &middot; [![GitHub license](https://img.shields.io/badge/license-MIT-green)](./License)

[網站連結](https://mcn.sytes.net/)

[賓果遊戲 demo &#91; YouTube 連結 &#93;](https://www.youtube.com/watch?v=fDWZ6i602ms)

[email 收信、新增書籍、購物車、查看訂單 pdf、聊天室 demo
 &#91; YouTube 連結 &#93;](https://www.youtube.com/watch?v=8mVKRml16-I)

PHP 實作聊天室、購物車、線上 bingo 連線遊戲<br>

## 網站地圖
[網站地圖 完整版](./docs/site_maps/mcn_site_map_full.drawio.webp)

![site_map_part_a](./docs/site_maps/mcn_site_map_part_A.drawio.webp)

![site_map_part_b](./docs/site_maps/mcn_site_map_part_B.drawio.webp)

![site_map_part_c](./docs/site_maps/mcn_site_map_part_C.drawio.webp)

![site_map_part_d](./docs/site_maps/mcn_site_map_part_D.drawio.webp)

## 伺服器環境
採用 LAMP 環境 Linux (Debian)、Apache、MySQL (MariaDB)、PHP<br>

前後端分離架構

前端相關程式碼 source/html

後端相關程式碼 source/websecure

## PHP 相關 package
使用 PHP composer 管理 package<br>
使用到的 package 有 JWT 與 TCPDF<br>

### 設定微軟正黑體
在 Linux 主機上，安裝好 TCPDF package 以後<br>
進入目錄<br>
`/var/php_packages/vendor/tecnickcom/tcpdf/tools/`<br>
將 res/font/ 裡的 msjh.ttf 與 msjhbd.ttf 複製到該目錄<br>

執行指令<br>
`php tcpdf_addfont.php -i msjh.ttf`<br>
`php tcpdf_addfont.php -i msjhbd.ttf`<br>

若成功，在目錄 `/var/php_packages/vendor/tecnickcom/tcpdf/fonts`  會發現新增了以下檔案：<br>
`msjhbd.ctg.z`<br>
`msjhbd.php`<br>
`msjhbd.z`<br>
`msjh.ctg.z`<br>
`msjh.php`<br>
`msjh.z`<br>

之後就能在 PHP 程式碼上，指定使用「微軟正黑體」印在 PDF 檔。

## License
[MIT License](./License)