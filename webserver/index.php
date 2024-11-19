<?php session_start();
// include_once("db.php");
include_once("utils.php");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/vars.css">
    <link rel="stylesheet" href="./styles/common.css">

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>MicaSend<?php if (isConnected()) echo " - Connection" ?></title>

    <meta name="author" content="MagicTINTIN,baptistereb">
    <meta name="description" content="The future of online chatting!">

    <link rel="icon" type="image/x-icon" href="images/favicon.png">

    <meta property="og:type" content="website" />
    <meta property="og:title" content="Micasend">
    <meta property="og:description" content="The future of online chatting!">

    <meta property="og:image" content="https://micasend.magictintin.fr/images/favicon.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="Micasend's logo">

    <meta property="og:url" content="https://micasend.magictintin.fr/" />
    <meta data-react-helmet="true" name="theme-color" content="#207DFE" />
</head>

<body>
    <?php
    if (isConnected()) { ?>
        <header>
            <a href="./"></a>
        </header>
        <main>
            <section id="messages">

            </section>
            <section></section>
        </main>

        <footer>
            <div>
                <form method="post">
                    <input type="submit" name="disconnect" value="Log out">
                </form>
                <span id="onlineVersion">[WEB] MicaSend - v1.0</span>
            </div>
        </footer>
    <?php } else {
    ?>
        <section id="connection">
            <br>
            <div id="connectionBlock">
                <form method="post">
                    <div id="connectImgDiv">
                        <img src="images/icon.png" id="connectionImg">
                    </div>
                    <input class="input" type="text" id="username" name="username" placeholder="Username" required autocomplete="on">
                    <input class="input" type="password" id="token" name="token" placeholder="Token (optional)" autocomplete="on">
                    <input class="button" type="submit" name="connect" value="Log in">
                </form>
            </div>
            <span id="onlineVersionConnection">[WEB] MicaSend - v1.0</span>
        </section>
    <?php
    } ?>
</body>

</html>