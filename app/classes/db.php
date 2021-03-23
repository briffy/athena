<?php
class DB {

    public $dbh;

    function __construct() {
        $params = parse_ini_file('/var/www/athena.briffy.net/app/config/db.conf');

        $connstr = "mysql:host=".$params['host'].";dbname=".$params['database'];

   
        $this->dbh = new PDO($connstr, $params['username'], $params['password']);
    }

    function get($query)
    {
        $query = htmlentities(strip_tags($query));

        $stmt = $this->dbh->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result;
    }

    function getAll($query)
    {
        $query = htmlentities(strip_tags($query));

        $stmt = $this->dbh->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function insert($table, $data)
    {
        $keys = [];
        $values = [];

        foreach($data as $key => $value)
        {
            array_push($keys, htmlentities(strip_tags($key)));
            array_push($values, htmlentities(strip_tags($value)));
        }

        $key_count = count($keys);

        $sql = "INSERT INTO $table (";

        for($x = 0; $x < $key_count; $x++)
        {
            if($x == 0)
            {
                $sql .= $keys[$x];
            }
            else
            {
                $sql .= ",".$keys[$x];
            }
        }

        $sql .= ") VALUES (";

        for($x = 0; $x < $key_count; $x++)
        {
            if($x == 0)
            {
                $sql .= "?";
            }
            else
            {
                $sql .= ",?";
            }
        }

        $sql .= ")";
        
        $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($values);

        return $this->dbh->lastInsertId();
    }

    function update($table, $data)
    {
        $update_filter = $data['update_filter'];
        unset($data['update_filter']);

        $keys = [];
        $values = [];

        foreach($data as $key => $value)
        {
            array_push($keys, htmlentities(strip_tags($key)));
            array_push($values, htmlentities(strip_tags($value)));
        }

        $key_count = count($keys);

        $sql = "UPDATE ".$table." SET ";

        for($x = 0; $x < $key_count; $x++)
        {
            if($x == 0)
            {
                $sql .= $keys[$x]."=?";
            }
            else
            {
                $sql .= ",".$keys[$x]."=?";
            }
        }

        $sql .= " WHERE ".$update_filter;

        $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($values);
    }

    function delete($table, $filter)
    {
        $sql = "DELETE FROM ".$table." WHERE ".htmlentities(strip_tags($filter));
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
    }
}
?>