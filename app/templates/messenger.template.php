<?php
$currentUser = $_SESSION['UserID'];
$db = new DB();
?>
<!doctype html>

<html lang="en">
    <head>
        <?php
            echo '<title>Athena Messenger';
            if(isset($data['username']))
            {
                echo ' - '.$data['username'];
            }
            echo '</title>';
        ?>
        <link rel="shortcut icon" type="image/jpg" href="/images/favicon.png" />
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="manifest" href="/manifests/mobile/manifest.json">
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="/css/messenger.css" />
        <script src="/scripts/index.js"></script>
        <script type="text/javascript">

        const soundEffect = new Audio();

        soundEffect.src = '/audio/notification.mp3';

        function checkSubmit(e) {
                if(e && e.keyCode == 13) {
                    <?php echo "var url = '/messenger/message/new/".$data['username']."';\n"; ?>
                    var request = new XMLHttpRequest();
                    request.open('POST', url, true);
                    var message_form = document.getElementById("message_form");
                    request.send(new FormData(message_form));
                    location.reload();
                }
            }
            function ContactRequest(e) {
                var url ="/messenger/contact/new";
                var request = new XMLHttpRequest();
                request.open('POST', url, true);
                var add_contact_form = document.getElementById("add_contact_form");
                request.send(new FormData(add_contact_form));
                document.getElementById("add_contact_username").value = "";
                location.reload();
            }

            function ContactProcess(e) {
                var url = e.parentElement.action;
                var request = new XMLHttpRequest();
                request.open('POST', url, true);
                request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
                request.send("response="+e.value);
                location.reload(); 
            }
        var scrolled = false;
            function updateScroll(){
                if(!scrolled) {
                    var element = document.getElementById("messages");
                    element.scrollTop = element.scrollHeight;
                }
            }

            function getData() {
                var xhr = new XMLHttpRequest();                

                xhr.onload = function() {
                    if(xhr.status === 200) {                       
                        var messages = JSON.parse(xhr.responseText);
                        var output;

                        if(messages != "")
                        {
                            soundEffect.play();
                        }

                        messages.forEach(function(current) {
                            var new_message = document.createElement("li");
                            var message_timestamp = document.createElement("div");
                            var message_content = document.createElement("div");
                            new_message.setAttribute("class", "recipient");
                            message_timestamp.setAttribute("class", "message_timestamp");
                            message_content.setAttribute("class", "message_content");

                            message_timestamp.appendChild(document.createTextNode(current["sent_timestamp"]));
                            message_content.appendChild(document.createTextNode(current["content"]));

                            new_message.appendChild(message_timestamp);
                            new_message.appendChild(message_content);
                                             
                            document.getElementById("messages").appendChild(new_message);

                            updateScroll();
                        });
                    }
                };
                <?php echo "xhr.open('GET', '/messenger/message/feed/".$data['username']."/1', true);" ?>
                xhr.send(null);
                
            }

            function Scrolled()
            {
                scrolled = true;
            }

            function getContactNotificationFeed()
            {
                var contacts = document.getElementById("contacts_list").getElementsByTagName("li");
                var current_notifications = {};
                var total_notifications = {};

                for(let x = 0; x < contacts.length; x++)
                {
                    contact_name = contacts[x].getElementsByClassName("contact_name")[0].innerHTML;
                    contact_notifications_base = contacts[x].getElementsByClassName("notification_bubble");

                    if(contact_notifications_base[0] != undefined)
                    {
                        contact_notifications = contact_notifications_base[0].innerHTML;
                    }
                    else
                    {
                        contact_notifications = 0;
                    }

                    current_notifications[contact_name] = contact_notifications;
                }
                Object.keys(current_notifications).forEach(function(current){
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', '/messenger/message/feed/'+current+'/0', true);
                    xhr.send(null);

                    xhr.onload = function() {
                        if(xhr.status === 200) {                       
                            var notifications = JSON.parse(xhr.responseText);
                            total_notifications[current] = notifications.length;
                            
                            for(notification in total_notifications)
                            {
                                if(notification === current)
                                {
                                    if(total_notifications[notification] > current_notifications[current])
                                    {
                                        if(current_notifications[current] != 0)
                                        {
                                            document.getElementById(current+"-notifications").innerHTML = total_notifications[notification];
                                        }
                                        else
                                        {
                                            var new_notification_bubble = document.createElement("div");
                                            new_notification_bubble.setAttribute("class", "notification_bubble");
                                            new_notification_bubble.setAttribute("id", current+"-notifications");
                                            var root = document.getElementById(current+"-container").getElementsByTagName("a");
                                            root[0].appendChild(new_notification_bubble);
                                            document.getElementById(current+"-notifications").innerHTML = total_notifications[notification];
                                        }

                                        navigator.serviceWorker.ready.then(function(reg) { 
                                            navigator.serviceWorker.controller.postMessage({'type': 'notification', 'user': current, 'message': notifications[notifications.length -1]['content']});
                                        });

                                        soundEffect.play();
                                    }
                                }
                            }
                        }
                    }
                });
            }


            async function DecryptMessages()
            {

                var messages = document.getElementById("messages").getElementsByTagName("li");

                var indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB || window.shimIndexedDB;
                var open = indexedDB.open("main", 1);
                open.onupgradeneeded = function() {
                    
                    
                };
                var private_key;

                open.onsuccess = async function() {
                    // Start a new transaction
                    var db = open.result;
                    var tx = db.transaction("private_keys", "readwrite");
                    var store = await tx.objectStore("private_keys");

                    private_key = await store.get(0);

                    // Close the db when the transaction is done
                    tx.oncomplete = function(data) {
                        db.close();
                        private_key = private_key.result;

                        console.log(messages);
                        for(message in messages)
                        {
                            console.log(message.innerHTML);
                        }
                        console.log(private_key.result);
                    };                    
                }
                
               
            }

            window.onload = function() {
                document.getElementById("messageInput").focus();
                updateScroll();
                var xhr = new XMLHttpRequest();
                <?php echo "xhr.open('GET', '/messenger/message/feed/".$data['username']."/1', true);" ?>
                xhr.send(null);
                var notification_bubble = document.getElementById("<?php echo $data['username']; ?>-notifications");

                if(notification_bubble !== "" && notification_bubble !== undefined && notification_bubble !== null)
                {
                    notification_bubble.parentNode.removeChild(notification_bubble);
                }

                if(Notification.permission != "granted" && Notification.permission != "denied")
                {
                    var notification_request = document.createElement('div');
                    var notification_request_label = document.createElement('div');
                    var notification_button = document.createElement('div');
                    notification_request.id = "notification_request";
                    notification_request_label.id = "notification_request_label";
                    notification_button.id = "notification_button";
                    notification_request_label.innerHTML = "Do you want push notifications?";
                    notification_button.innerHTML = "<button id=\"permission-btn\" onclick=\"main()\">request</button>";

                    notification_request.appendChild(notification_request_label);
                    notification_request.appendChild(notification_button);
                    document.body.appendChild(notification_request);
                }
                swRegistration = registerServiceWorker();

                DecryptMessages();
            }

            

            setInterval(getData, 1000*0.5);
            setInterval(getContactNotificationFeed, 1000*3);
            </script>
    </head>

    <body>
        <div id="wrapper"> 
            <nav>
                <section id="user_profile">
                    <div id="avatar_container">
                        <?php
                            $currentUsername = $db->get("SELECT username FROM users WHERE ID =".$currentUser);
                            global $config;
                            $avatar_location = $config['PUBLIC_DIRECTORY']."images/avatars/".$currentUsername[0].".png";
                            
                            if(file_exists($avatar_location))
                            {
                                echo '<img src="/images/avatars/'.$currentUsername[0].'.png" />';
                            }
                            else
                            {
                                echo '<img src="/images/noavatar.png" />';
                            }        
                        ?>
                    </div>
                </section>   

                <section id="add_contact">
                    <h1>_add contact</h1>
                    <form action="/messenger/contact/new" method="post" id="add_contact_form" onSubmit="event.preventDefault(); ContactRequest(event); ">
                        <input type="text" name="username" id="add_contact_username" value="<enter username>" onfocus="if(this.value == '<enter username>') { this.value=''; }" onfocusout="if(this.value == '') { this.value='<enter username>'; }" />
                    </form>
                </section>

                <section id="contacts">
                    <h1>_contacts</h1>
                    <ul id="contacts_list">
                        <?php
                            
                            $contacts = $db->getAll("SELECT contact1, contact2 FROM contacts WHERE status = 1 AND contact1 = '$currentUser' OR contact2 = '$currentUser' AND status = 1");

                            $notifications = $db->getAll("SELECT COUNT(messagelinks.ID) as total, SenderID FROM messagelinks LEFT JOIN messages ON messagelinks.messageID=messages.ID WHERE messagelinks.RecipientID=".$currentUser." AND messages.status = 0 GROUP BY(messagelinks.SenderID)");

                            foreach($contacts as $key => $value)
                            {
                                if($value['contact1'] != $_SESSION['UserID'])
                                {
                                    $contacts[$key]['userID'] = $value['contact1'];
                                }
                                elseif($value['contact2'] != $_SESSION['UserID'])
                                {
                                    $contacts[$key]['userID'] = $value['contact2'];
                                }

                                unset($contacts[$key]['contact1']);
                                unset($contacts[$key]['contact2']);

                                $lastMessageTimestamp = $db->get("SELECT sent_timestamp FROM messages LEFT JOIN messagelinks ON messagelinks.messageID = messages.ID WHERE messagelinks.RecipientID=".$currentUser." AND messagelinks.SenderID=".$contacts[$key]['userID']." OR messagelinks.RecipientID=".$contacts[$key]['userID']." AND messagelinks.SenderID=".$currentUser." ORDER BY sent_timestamp DESC LIMIT 1");
                                $contacts[$key]['last_message_timestamp'] = strtotime($lastMessageTimestamp[0]);

                                if(count($notifications) == 0)
                                {
                                    foreach($contacts as $key => $value)
                                    {
                                         $contacts[$key]['notifications'] = 0;
                                    }
                                }
                                else 
                                {
                                    foreach($notifications as $notification)
                                    {
                                        if($notification['SenderID'] == $contacts[$key]['userID'])
                                        {
                                            $contacts[$key]['notifications'] = $notification["total"];
                                        }
                                    }

                                    if(!isset($contacts[$key]['notifications']))
                                    {
                                        $contacts[$key]['notifications'] = 0;
                                    }
                                }       
                            }

                            
                            function custom_sort($a, $b)
                            {
                                return $a['last_message_timestamp']<$b['last_message_timestamp'];
                            }

                            usort($contacts, "custom_sort");
                            if(count($contacts) > 0)
                            {
                                
                                foreach($contacts as $contact)
                                {
                                    $contactDetails = $db->get("SELECT username FROM users WHERE ID = ".$contact['userID']);
                                    if($contactDetails['username'] == $data['username'])
                                    {
                                        echo '<li class="selectedContact" id="'.$contactDetails['username'].'-container">';
                                        
                                        $contact_avatar = $config['PUBLIC_DIRECTORY']."images/avatars/".$contactDetails['username'].".png";

                                        if(file_exists($contact_avatar))
                                        {
                                            $contact_avatar_location = '/images/avatars/'.$contactDetails['username'].'.png';
                                            echo '<div class="contact_avatar" style="background-image: url(\''.$contact_avatar_location.'\');">&nbsp;</div>';
                                        }
                                        else
                                        {
                                            $contact_avatar_location = '/images/noavatar.png';
                                            echo '<div class="contact_avatar" style="background-image: url(\''.$contact_avatar_location.'\');">&nbsp;</div>';
                                        }

                                        echo '<a href="/messenger/'.$contactDetails['username'].'"><div class="contact_name">'.$contactDetails['username'].'</div>';

                                        if($contact['notifications'] > 0)
                                        {
                                            echo '<div class="notification_bubble" id="'.$contactDetails['username'].'-notifications">'.$contact['notifications'].'</div>';
                                        }
                                        echo '</a></li>';
                                    }
                                    else
                                    {
                                        echo '<li id="'.$contactDetails['username'].'-container">';
                                        
                                        $contact_avatar = $config['PUBLIC_DIRECTORY']."images/avatars/".$contactDetails['username'].".png";

                                        if(file_exists($contact_avatar))
                                        {
                                            $contact_avatar_location = '/images/avatars/'.$contactDetails['username'].'.png';
                                            echo '<div class="contact_avatar" style="background-image: url(\''.$contact_avatar_location.'\');">&nbsp;</div>';
                                        }
                                        else
                                        {
                                            $contact_avatar_location = '/images/noavatar.png';
                                            echo '<div class="contact_avatar" style="background-image: url(\''.$contact_avatar_location.'\');">&nbsp;</div>';
                                        }

                                        echo '<a href="/messenger/'.$contactDetails['username'].'"><div class="contact_name">'.$contactDetails['username'].'</div>';

                                        if($contact['notifications'] > 0)
                                        {
                                            echo '<div class="notification_bubble" id="'.$contactDetails['username'].'-notifications">'.$contact['notifications'].'</div>';
                                        }
                                        echo '</a></li>';
                                    }
                                }
                            }
                            else
                            {
                                echo '<li>None</li>';
                            } 

                        ?>
                    </ul>
                </section>

                <?php
                $check_contact_requests = $db->getAll("SELECT users.username, contacts.contact1, contacts.contact2 FROM users LEFT JOIN contacts ON users.ID = contacts.contact1 WHERE status = 0 AND contacts.contact2 = '".$currentUser."' OR status = 0 AND contacts.contact1 = '".$currentUser."'");
                if(isset($check_contact_requests[0]) && $check_contact_requests[0] != "")
                {
                ?>
                <section id="contact_requests">
                    <h1>_contact requests</h1>
                    <ul id="contact_requests_list">
                        <?php
                            foreach($check_contact_requests as $contact_request)
                            {
                                if($contact_request['contact1'] == $currentUser)
                                {
                                    $contact_username = $db->get("SELECT username FROM users WHERE ID=".$contact_request['contact2']);

                                    echo '<li><div class="contact_request_name">'.$contact_username[0].'</div>';
                                    echo '<div class="contact_request_actions">Pending...</div></li>';
                                }
                                else
                                {
                                    echo '<li><div class="contact_request_name">'.$contact_request['username'].'</div>';
                                    echo '<div class="contact_request_actions"><form action="/messenger/contact/process/'.$contact_request['username'].'" method="post" id="process_contact_form"><input type="submit" name="response" value="O" id="contact_accept" title="Accept" onclick="event.preventDefault(); ContactProcess(this);" /><input type="submit" name="response" value="X" title="Deny" id="contact_deny" onclick="event.preventDefault(); ContactProcess(this);"  /><input type="submit" name="response" value="!" id="contact_block" title="Block" onclick="event.preventDefault(); ContactProcess(this);" /></form></li>';
                                }
                            }
                        ?>
                    </ul>
                </section>
                <?php
                }
                ?>
            </nav>
            
            <main>
                <ul id="messages">
                <?php
                    if(isset($data['username']))
                    {
                        $contactUsername = htmlentities(strip_tags($data['username']));
                        $contactID = $db->get("SELECT ID FROM users WHERE username = '$contactUsername'");
                        $messagelinks = $db->getAll("SELECT * FROM messagelinks WHERE SenderID = '$currentUser' AND RecipientID = '".$contactID['ID']."' OR RecipientID = '$currentUser' AND SenderID = '".$contactID['ID']."'");

                        foreach($messagelinks as $messagelink)
                        {
                            $message = $db->get("SELECT * FROM messages WHERE ID = '".$messagelink['MessageID']."'");
                        
                            if($messagelink['SenderID'] == $_SESSION['UserID'])
                            {
                                echo '<li class="sender"><div class="message_timestamp">'.$message['sent_timestamp'].'</div><div class="message_content">'.$message['content'].'</div></li>';
                            }
                            else
                            {
                                echo '<li class="recipient"><div class="message_timestamp">'.$message['sent_timestamp'].'</div><div class="message_content">'.$message['content'].'</div></li>';
                            }
                        }
                    }
                ?>
                </ul>
                <?php
                if(isset($contactUsername))
                {
                    echo '<form id="message_form" enctype="multipart/form-data"  method="post" action="/messenger/message/new/'.$data['username'].'" onSubmit="formSubmit(this)">';
                    echo '<textarea id="messageInput" name="content" onKeyPress="return checkSubmit(event)"></textarea>';
                    echo '</form>';

                }
                ?>
                
            </main>
            
            <?php
            if(isset($contactUsername))
            {
            ?>
            <section id="recipient_profile">
                    <div id="avatar_container">
                        <?php
                            $avatar_location = $config['PUBLIC_DIRECTORY']."images/avatars/".$contactUsername.".png";
                            if(file_exists($avatar_location))
                            {
                                echo '<img src="/images/avatars/'.$contactUsername.'.png" />';
                            }
                            else
                            {
                                echo '<img src="/images/noavatar.png" />';
                            }                     
                        ?>
                    </div>

                    <div id="files_container">
                        <h1>_files</h1>
                        None.
                    </div>
             </section>   
             <?php
            }
            ?>
        </div>
        <br class="clear" />
    </body>
</html>