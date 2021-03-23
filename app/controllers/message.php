<?php
class Message {
    function new($data = null)
    {
        $recipientID = htmlentities(strip_tags($data['username']));
        $db = new DB();

        $recipient = $db->get("SELECT ID FROM users WHERE username='$recipientID'");

        $content = htmlentities(strip_tags($_POST['content']));
        if($content != "")
        {
            if($recipient != "")
            {
                if($recipient['ID'] != $_SESSION['UserID'])
                {  
                    $currentUser = $_SESSION['UserID'];
                    $contact = $db->get("SELECT contact1, contact2, status FROM contacts WHERE contact1 = '$currentUser' AND contact2 = '".$recipient['ID']."' OR contact1 = '".$recipient['ID']."' AND contact2 = '$currentUser'");

                    if($contact['status'] == 1)
                    {
                        $public_key = $db->get("SELECT public_key FROM users WHERE ID=".$recipient['ID']);
                        openssl_public_encrypt($content, $encrypted_content, $public_key['public_key']);
                        //$content = base64_encode($encrypted_content);

                        $timestamp = date('Y-m-d H:i:s');
                        $table = "messages";                    
                        $insertdata['sent_timestamp'] = $timestamp;
                        $insertdata['modified_timestamp'] = $timestamp;
                        $insertdata['content'] = $content;
                        $insertdata['status'] = 0;

                        $messageID = $db->insert($table, $insertdata);

                        $table = "messagelinks";
                        $insertdata = null;
                        $insertdata['MessageID'] = $messageID;
                        $insertdata['SenderID'] = $_SESSION['UserID'];
                        $insertdata['RecipientID'] = $recipient['ID'];

                        $db->insert($table, $insertdata);
                    }
                }
            }
        }
        else
        {
            header('Location: /messenger/'.$recipientID);
        }
    }

    function feed($data = null)
    {
        $currentUser = $_SESSION['UserID'];
        $db = new DB();
        if(isset($data['username']))
        {
            $contactUsername = $data['username'];
            $contactID = $db->get("SELECT ID FROM users WHERE username = '$contactUsername'");
            $messagelinks = $db->getAll("SELECT * FROM messagelinks WHERE RecipientID = '$currentUser' AND SenderID = '".$contactID['ID']."'");
            $messages = [];
            $read_messages = [];
            foreach($messagelinks as $messagelink)
            {
                $message = $db->get("SELECT * FROM messages WHERE ID = '".$messagelink['MessageID']."' AND status = 0");
                if($message != "")
                {
                    array_push($messages, $message);
                    array_push($read_messages, $message['ID']);
                }
            }
            
            if(isset($messages[0]))
            {
                foreach($messages[0] as $key => $value)
                {
                    $messages[0][$key] = htmlentities(strip_tags($value));
                }
            }

            echo json_encode($messages);

            if($data['read'])
            {
                foreach($read_messages as $read_message)
                {
                    unset($data);
                    $table = "messages";
                    $data['status'] = 1;
                    $data['update_filter'] = "ID = ".htmlentities(strip_tags($read_message));

                    $db->update($table, $data);
                }
            }
        }
    }

    function index($data = null)
    {
        $currentUser = $_SESSION['UserID'];
        $db = new DB();
        if(isset($data['username']))
        {
            $contactUsername = $data['username'];
            $contactID = $db->get("SELECT ID FROM users WHERE username = '$contactUsername'");
            $messagelinks = $db->getAll("SELECT * FROM messagelinks WHERE SenderID = '$currentUser' AND RecipientID = '".$contactID['ID']."' OR RecipientID = '$currentUser' AND SenderID = '".$contactID['ID']."'");
            $messages = [];
            foreach($messagelinks as $messagelink)
            {
                $message = $db->get("SELECT * FROM messages WHERE ID = '".$messagelink['MessageID']."'");
                array_push($messages, $message);
            }

            echo json_encode($messages);
        }
    }
}
?>