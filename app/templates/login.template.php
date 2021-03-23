<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="css/reset.css" />
  <link rel="stylesheet" type="text/css" href="css/common.css" />
</head>

<body>
    <main>
        <h1>_login</h1>
        <?php
        if(isset($_SESSION['UserID']))
        {
            header('Location: /messenger');
        }
        else
        {
        ?>
        <form method="post" action="login">
            <label for="username">Username</label>
            <input type="text" name="username" />
            <label for="password">Password</label>
            <input type="password" name="password" />
            <input type="submit" />
        </form>
        <?php
        }
        ?>
    </main>
</body>
</html>