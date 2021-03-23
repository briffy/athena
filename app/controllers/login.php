<?php
class Login {
    function show()
    {
        if(!isset($_SESSION['UserID']))
        {
            if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
            {
                $str = "/var/www/athena.briffy.net/app/templates/login.mobile.template.php";
            }
            else 
            {
                $str = "/var/www/athena.briffy.net/app/templates/login.template.php";
            }
        }
        else
        {
            header("Location: /messenger");
        }

        include($str);
            
    }

    function process()
    {
        if(!isset($_SESSION['UserID']))
        {
            $username = htmlentities(strip_tags($_POST['username']));
            $password = htmlentities(strip_tags($_POST['password']));

            $db = new DB();
            
            $user = $db->get("SELECT * FROM users WHERE username='$username'");

            if(isset($username) && isset($password))
            {
                if(password_verify($password, $user['password']))
                {
                    $_SESSION['UserID'] = htmlentities(strip_tags($user['ID']));
                    $date = time() + 2678400;
                    $table = "auth_tokens";
                    $cookie_hash = bin2hex(random_bytes(24));

                    $cookie_data['userID'] = $user['ID'];
                    $cookie_data['token'] = $cookie_hash;
                    setcookie('auth-token', serialize($cookie_data), $date, '/');
                    $data['userID'] = $user['ID'];
                    $data['token'] = password_hash($cookie_hash, PASSWORD_DEFAULT);
                    $data['expiry'] = date('Y-m-d H:i:s', $date);

                    $authID = $db->insert($table, $data);

                    header('Location: /messenger');
                }
            }
            else
            {
                header('Location:/login');
            }
        }
        else
        {            
            header('Location: /messenger');
        }
    }
}
?>