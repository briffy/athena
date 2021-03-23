<?php
class Routes {
    public $Items;

    function __construct()
    {
        $this->Items = [];
    }

    function new($URL, $Method, $Destination, $Auth)
    {
        $route = new Route($URL, $Method, $Destination, $Auth);
        array_push($this->Items, $route);
    }

    function get($url, $type)
    {
        $url = strip_tags(htmlentities($url));
        $type = strip_tags(htmlentities($type));

        $method_match = array_keys(array_column($this->Items, "Method"), $type);

        $regex = "/\/([^\/]*)/";
        $variable_regex = "/\/{([^\{\}\/]*)}/";
        $possible_matches = [];
        preg_match_all($regex, $url, $incoming);

        foreach($method_match as $method_key => $method_value)
        {
            foreach($this->Items[$method_value] as $route)
            {
                preg_match_all($regex, $route, $split_route);

                if(count($split_route[0]) == count($incoming[0]))
                {
                    array_push($possible_matches, $method_value);
                }
                unset($split_route);
            }
        }

        $end_match = false;

        foreach($possible_matches as $poss)
        {
            preg_match_all($regex, $this->Items[$poss]->URL, $route_matches);
            $success = $poss;
            
            foreach($incoming[1] as $key => $value)
            {
                if($value != $route_matches[1][$key])
                {
                    if(preg_match($variable_regex, $route_matches[0][$key], $variable_match))
                    {
                        $data[$variable_match[1]] = $value;
                    }
                    else
                    {
                        $success = false;
                    }
                }
            }

            if($success != false)
            {
                $end_match = $success;
            }
        }

        if(isset($end_match) && $end_match != false)
        {
            $output = $this->Items[$end_match];
            if(isset($data))
            {
                $output->Data = $data;
            }
            
        }
        else
        {
            $output = new Route(null, null, 404, null);
        }
        return $output;
    }
}
?>