<?php
class Register {
    function show()
    {
        if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
        {
            $str = "/var/www/athena.briffy.net/app/templates/register.mobile.template.php";
        }
        else {
            $str = "/var/www/athena.briffy.net/app/templates/register.template.php";
        }

        include($str);
    }

    function process($data = null)
    {      
        
        $db = new DB();

        if($data['postback'] == 1)
        {

            $username = htmlentities(strip_tags($_POST['username']));
            $password = htmlentities(strip_tags($_POST['password']));

            $user = $db->get("SELECT * FROM users WHERE username = '".$username."'");

            if(isset($user) && isset($password))
            {
                if(password_verify($password, $user['password']))
                {
                    $data['public_key'] = $_POST['public_key'];
                    unset($data['postback']);
                    $data['update_filter'] = "ID =".$user['ID'];
                    $table = "users";

                    $db->update($table, $data);
                    unset($data);

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
                    $_SESSION['UserID'] = $user['ID'];
                    header("Location: /messenger");
                }
                else {
                    header("Location: /register");
                }
            }
            else {
                header("Location: /register");
            }
        }

        else 
        {
            $username = htmlentities(strip_tags($_POST['username']));
            $password = htmlentities(strip_tags($_POST['password']));
            $confirm_password = htmlentities(strip_tags($_POST['confirm_password']));

            
            $user = $db->get("SELECT ID FROM users WHERE username='$username'");

            if($user['ID'] != "")
            {
                echo 'Error: username already taken.';
            }
            else
            {
                if($password == $confirm_password)
                {
                    $data['username'] = htmlentities(strip_tags($username));
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                    $data['public_key'] = null;

                    $table = 'users';

                    $db = new DB();
                    $db->insert($table, $data);
                    ?>
                    <script type="text/javascript">
                    function arrayBufferToBase64String(arrayBuffer) {
                        var byteArray = new Uint8Array(arrayBuffer)
                        var byteString = ''
                        for (var i=0; i<byteArray.byteLength; i++) {
                            byteString += String.fromCharCode(byteArray[i])
                        }
                        return btoa(byteString)
                    }

                    function convertBinaryToPem(binaryData, label) {
                        var base64Cert = arrayBufferToBase64String(binaryData)
                        var pemCert = "-----BEGIN " + label + "-----\r\n"
                        var nextIndex = 0
                        var lineLength
                        while (nextIndex < base64Cert.length) {
                            if (nextIndex + 64 <= base64Cert.length) {
                            pemCert += base64Cert.substr(nextIndex, 64) + "\r\n"
                            } else {
                            pemCert += base64Cert.substr(nextIndex) + "\r\n"
                            }
                            nextIndex += 64
                        }
                        pemCert += "-----END " + label + "-----\r\n"
                        return pemCert
                    }

                    window.crypto.subtle.generateKey(
                    {
                        name: "RSA-OAEP",
                        modulusLength: 2048,
                        publicExponent: new Uint8Array([1, 0, 1]),
                        hash: "SHA-256"
                    },
                    true,
                    ["encrypt", "decrypt"]
                    ).then((keyPair) => {
                        
                        public_key_temp = keyPair.publicKey;
                        private_key_temp = keyPair.privateKey;

                        var indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB || window.shimIndexedDB;
                                var open = indexedDB.open("main", 1);
                                open.onupgradeneeded = function() {
                                    var db = open.result;
                                    var store = db.createObjectStore("private_keys");

                                    open.onsuccess = function() {
                                        // Start a new transaction
                                        var db = open.result;
                                        var tx = db.transaction("private_keys", "readwrite");
                                        var store = tx.objectStore("private_keys");

                                        store.put(private_key_temp, 0);


                                        // Close the db when the transaction is done
                                        tx.oncomplete = function() {
                                            db.close();
                                        };
                                    }
                                };

                            window.crypto.subtle.exportKey(
                                "spki",
                                public_key_temp
                            ).then((exported) => {
                                
                                public_key_pem = convertBinaryToPem(exported, "PUBLIC KEY");
                                console.log(public_key_pem);
                                const form = document.createElement('form');
                                form.method = "POST";
                                form.action = "/register/1"
                                const public_key = document.createElement('input');
                                const username = document.createElement('input');
                                const password = document.createElement('input');
                                public_key.type = "hidden";
                                public_key.name = "public_key";
                                public_key.value = public_key_pem;
                                console.log(public_key_pem);
                                password.type = "hidden";
                                password.name = "password";
                                password.value = "<?php echo $password; ?>";
                                username.type = "hidden";
                                username.name = "username";
                                username.value = "<?php echo $username; ?>";

                                form.appendChild(public_key);
                                form.appendChild(username);
                                form.appendChild(password);
                                document.body.appendChild(form);
                                form.submit();
                            }); 
                 
                    });

                    </script>
                    <?php
                }
                else
                {
                    echo 'Error: passwords didn\'t match.';
                }
            }
        }
    }
}
?>