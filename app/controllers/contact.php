<?php
class Contact {
    function new()
    {
        $db = new DB();
        $user = htmlentities(strip_tags($_POST['username']));
        $contactID = $db->get("SELECT ID FROM users WHERE username = '".$user."'");

        $currentUser = $_SESSION['UserID'];

        $check_existing = $db->get("SELECT COUNT(ID) FROM contacts WHERE contact1 = '".$currentUser."' AND contact2 = '".$contactID['ID']."' OR contact1 = '".$contactID['ID']."' AND contact2 = '".$currentUser."'");
        
        if($check_existing[0] == 0) {
            $data['contact1'] = $currentUser;
            $data['contact2'] = $contactID['ID'];
            $data['status'] = 0;

            $db->insert("contacts", $data);
        }
    }

    function process($data = null)
    {
        $currentUser = $_SESSION['UserID'];
        $db = new DB();
        $user = htmlentities(strip_tags($data['username']));
        unset($data['username']);
        $response = htmlentities(strip_tags($_POST['response']));

        if($response == "O")
        {
            $contactID = $db->get("SELECT ID FROM users WHERE username = '".$user."'");
            $data['status'] = 1;
            $data['update_filter'] = "contact1 = ".$contactID[0]." AND contact2 = ".$currentUser;
            $table = "contacts";
            $db->update($table, $data);
        }
        elseif($response == "X")
        {
            $table = "contacts";
            $contactID = $db->get("SELECT ID FROM users WHERE username = '".$user."'");

            $filter = "contact1 = ".$contactID[0]." AND contact2 = ".$currentUser;

            $db->delete($table, $filter);
        }
        elseif($response == "!")
        {
            $table = "contacts";
            $contactID = $db->get("SELECT ID FROM users WHERE username = '".$user."'");
            $data['status'] = 2;
            $data['update_filter'] = "contact1 = ".$contactID[0]." AND contact2 = ".$currentUser;
            $db->update($table, $data);
        }

    }
}