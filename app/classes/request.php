<?php
class Request {
    function Process($route)
    {
        if($route->Destination == 404)
        {
            http_response_code(404);
        }
        elseif($route->Destination == 403)
        {
            header("Location: /login");
        }
        else
        {
            $check_destination = "/([^@]*)@([^@]*)/";
            if(preg_match($check_destination, $route->Destination, $match))
            {
                $class_name = ucfirst($match[1]);
                $method_name = $match[2];

                $this->page = new $class_name;
                if(isset($route->Data))
                {
                    $this->page->$method_name($route->Data);
                }
                else
                {
                    $this->page->$method_name();
                }
            }
            else
            {
                $str = "/var/www/athena.briffy.net/app/templates/".$route->Destination.".template.php";
                include($str);
            }
        }
    }
}