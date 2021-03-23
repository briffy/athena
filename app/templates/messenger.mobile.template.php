<?php
$currentUser = $_SESSION['UserID'];
$db = new DB();
global $config;
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
        <link rel="stylesheet" type="text/css" href="/css/messenger-mobile.css" />
        <script src="/scripts/index.js"></script>
        <script type="text/javascript">
        var clickedOnce = false;
       
            window.addEventListener('touchstart', () => {
                if(!clickedOnce)
                {
                    document.getElementById('audio').muted = true;
                    document.getElementById('audio').play();
                    clickedOnce = true;
                }
           });

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
                            document.getElementById('audio').muted = false;
                            document.getElementById('audio').play();
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
                    contact_name = contacts[x].getElementsByClassName("contact_username")[0].innerHTML;
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
                            swRegistration = registerServiceWorker();

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
                                        
                                        document.getElementById('audio').muted = false;
                                        document.getElementById('audio').play();
                                    }
                                }
                            }
                        }
                    }
                }); 
                                             
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
            }

            setInterval(getData, 1000*0.5);
            setInterval(getContactNotificationFeed, 1000*3);
            </script>
    </head>
    <body>
    <audio id="audio" src="/audio/notification.mp3"></audio>
    <section id="contacts">
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
                            }
                            else
                            {
                                $contact_avatar_location = '/images/noavatar.png';
                            }

                            echo '<a href="/messenger/'.$contactDetails['username'].'" style="background-image: url(\''.$contact_avatar_location.'\');"><div class="contact_username">'.$contactDetails['username'].'</div>';

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
                            }
                            else
                            {
                                $contact_avatar_location = '/images/noavatar.png';
                            }

                            echo '<a href="/messenger/'.$contactDetails['username'].'" style="background-image: url(\''.$contact_avatar_location.'\');"><div class="contact_username">'.$contactDetails['username'].'</div>';

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
        </main>

        <div id="message_form_container">
        <?php
            if(isset($contactUsername))
            {
                echo '<form id="message_form" enctype="multipart/form-data"  method="post" action="/messenger/message/new/'.$data['username'].'" onSubmit="formSubmit(this)">';
                echo '<textarea id="messageInput" name="content" onKeyPress="return checkSubmit(event)"></textarea>';
                echo '</form>';

            }
            ?>
        </div>
    </body>
</html>