<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="css/reset.css" />
  <link rel="stylesheet" type="text/css" href="css/mobile-login.css" />
</head>

<body>
    <main>
        <h1>_login</h1>
        <?php
        if(isset($_SESSION['UserID']))
        {
            echo '<p>You\'re already logged in, choom.</p>';
        }
        else
        {
        ?>
        <form method="post" action="login">
            <label for="username">_username</label>
            <input type="text" name="username" autocomplete="username" />
            <label for="password">_password</label>
            <input type="password" name="password" autocomplete="current-password" />
            <input type="submit" value="_submit" />
        </form>
        <?php
        }
        ?>
    </main>

    <a href="/register" id="register_link">Register?</a>
</body>
</html>