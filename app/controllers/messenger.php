<?php
class Messenger {
    function show($data = null)
    {
        if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
        {
            $str = "/var/www/athena.briffy.net/app/templates/messenger.mobile.template.php";
        }
        else
        {
            $str = "/var/www/athena.briffy.net/app/templates/messenger.template.php";
        }
        include($str);
    }
}
?>