<?php
class Route {
    public $URL;
    public $Method;
    public $Destination;
    public $Auth;
    public $Data;

    function __construct($URL, $Method, $Destination, $Auth)
    {
        $this->URL = $URL;
        $this->Method = $Method;
        $this->Destination = $Destination;
        $this->Auth = $Auth;
    }

    function auth()
    {
        if($this->Auth)
        {
            if(!isset($_SESSION['UserID']))
            {   
                $cookie_data = unserialize($_COOKIE['auth-token']);
                foreach($cookie_data as $key => $value)
                {
                    $cookie_data[$key] = htmlentities(strip_tags($value));
                }
                if(!isset($cookie_data['userID']) && !isset($cookie_data['token']))
                {
                    $this->Destination = 403;
                }
                else 
                {
                    
                    $db = new DB();
                    $auth_tokens = $db->getAll("SELECT ID,userID,token,expiry FROM auth_tokens WHERE userID = ".$cookie_data['userID']);

                    foreach($auth_tokens as $token)
                    {
                        if(strtotime($token['expiry']) < time())
                        {
                            $table = "auth_tokens";
                            $filter = "ID =".$token['ID'];
                            $db->delete($table, $filter);
                            $token = null;
                        }

                        if($cookie_data['userID'] == $token['userID'] && password_verify($cookie_data['token'], $token['token']))
                        {
                            if((strtotime($token['expiry']) - time()) < 259200)
                            {
                                $table = "auth_tokens";
                                $date = time() + 2678400;
                                setcookie('auth-token', serialize($cookie_data), $date, '/');
                                $data['expiry'] = date('Y-m-d H:i:s', $date);
                                $data['update_filter'] = "ID = ".$token['ID'];

                                $authID = $db->update($table, $data);
                            }

                            $_SESSION['UserID'] = $cookie_data['userID'];
                        }

                    }

                    if(!isset($_SESSION['UserID']))
                    {  
                        $this->Destination = 403;
                    }
                }
            }
        }

        return $this;
    }
}
?>