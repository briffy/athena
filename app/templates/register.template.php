<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="/css/reset.css" />
  <link rel="stylesheet" type="text/css" href="/css/messenger.css" />
</head>

<body>
    <main id="registerPage">
        <h1>_register</h1>
        <form method="post" action="register">
            <div class="inputGroup">
                <label for="username">Username:</label>
                <input type="text" name="username" />
            </div>

            <div class="inputGroup">
                <label for="password">Password:</label>
                <input type="password" name="password" />
            </div>

            <div class="inputGroup">
                <label for="confirm_password">Repeat password:</label>
                <input type="password" name="confirm_password" />
            </div>
            <input type="submit" />
        </form>
    </main>
</body>
</html>