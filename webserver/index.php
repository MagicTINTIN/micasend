<?php session_start();
include_once("db.php");
include_once("utils.php");

if (isset($_POST["connect"]) && isset($_POST["username"])) {
    $_SESSION["username"] = htmlspecialchars($_POST["username"]);

    if (isset($_POST["token"])) {
        $_SESSION["token"] = htmlspecialchars($_POST["token"]);
    }

    header("Refresh:0");
    exit();
}

if (isset($_POST["disconnect"])) {
    disconnect();
}

if (isset($_POST['message']) && isConnected()) {
    if (empty($_POST['message'])) {
        header("Refresh:0");
        exit();
    }

    $msg = htmlspecialchars((string) $_POST['message']);
    $msg = substr($msg, 0, 1023);
    $sender = htmlspecialchars($_SESSION['username']);
    $sender = substr($sender, 0, 25);
    $certif = 0;

    if (isset($_SESSION['token']) and !empty($_SESSION['token'])) {
        $token = htmlspecialchars($_SESSION['token']);
        $requser = $db->prepare("SELECT id, token FROM user WHERE pseudo = ?");
        $requser->execute(array($sender));
        $result = $requser->rowcount();
        if ($result == 1) { //l'utilisateur existe t-il ?
            $user = $requser->fetch();
            if ($user[1] == $token) { //le token est-il bon ?
                //utilisateur certifié
                $certif = $user[0];
            }
        }
    }
    $msg = str_replace(" ", "§", $msg);
    $msg = preg_replace('/[\x00-\x1F\x7F]/u', '', $msg);

    $reqins = $db->prepare("INSERT INTO msg(content, sender, id_certified_user, date_time) VALUES(?, ?, ?, ?)");
    $reqins->execute(array($msg, $sender, $certif, date("Y-m-d H:i:s", time())));

    header("Refresh:0");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/vars.css">
    <link rel="stylesheet" href="./styles/common.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>MicaSend<?php if (isConnected()) echo " - Connection" ?></title>

    <meta name="author" content="MagicTINTIN,baptistereb">
    <meta name="description" content="The future of online chatting!">

    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">

    <meta property="og:type" content="website" />
    <meta property="og:title" content="MicaSend">
    <meta property="og:description" content="The future of online chatting!">

    <meta property="og:image" content="https://micasend.magictintin.fr/images/favicon.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="MicaSend's logo">

    <meta property="og:url" content="https://micasend.magictintin.fr/" />
    <meta data-react-helmet="true" name="theme-color" content="#DB68FD" />
</head>

<body>
    <?php
    if (isConnected()) { ?>
        <header>
            <div>
                <a href="./" id="titleLink">
                    <img src="images/favicon.png" id="headerIcon">
                    <h3>MicaSend</h3>
                </a>
            </div>
        </header>
        <main>
            <section id="messages">
                <?php include("printMessagesPart.php"); ?>
            </section>
            <section id="sendNew">
                <!-- <form method="post" id="mainForm">
                    <input type="text" id="mainInput" placeholder="Write your message here" name="message" autocomplete="off" autofocus="yes">
                    <input type="submit" name="submitNewMessage" id="mainSubmit" value="/>">
                </form> -->
                <div id="mainForm">
                    <input type="text" id="mainInput" placeholder="Write your message here" name="message" autocomplete="off" autofocus="yes">
                    <button id="mainSubmit">/></button>
                </div>
            </section>
        </main>

        <footer>
            <div>
                <form method="post">
                    <span><?php echo $_SESSION["username"] ?></span>
                    <span>|</span>
                    <input type="submit" name="disconnect" value="Log out">
                </form>
                <span id="onlineVersion">MicaSend web 1.0</span>
            </div>
        </footer>
        <script>
            let socket;
            let tries = 0;

            document.getElementById("mainInput").addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    document.getElementById("mainSubmit").click();
                }
            });

            document.getElementById("mainSubmit").addEventListener("click", () => {
                sendMicsendMessage();
            });

            function sendMicsendMessage() {
                let text = document.getElementById("mainInput").value;
                if (text == undefined || text.length == "" || text.length == 0) return;
                fetch("./msg.php", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'message': text,
                            'sender': '<?php echo $_SESSION['username']; ?>',
                            <?php if (isset($_SESSION['token']) && !empty($_SESSION['token'])) { ?> 'token': '<?php echo $_SESSION['token']; ?>'
                            <?php } ?>
                        })
                    }).then(response => response.text())
                    .then(data => {
                        // console.log("FETCH RES:", data);
                        sendMsg("new micasend message");
                        document.getElementById("mainInput").value = "";
                    })
                    .catch(e => console.error("ERROR:", e));
            }

            number_unread_messages = 0;
            hidden_window = document.hidden;

            const connect = function() {
                // Return a promise, which will wait for the socket to open
                return new Promise((resolve, reject) => {

                    // const socketUrl = `wss://magictintin.fr:8443`
                    const socketUrl = `wss://msws.magictintin.fr:8443`

                    socket = new WebSocket(socketUrl);

                    socket.onopen = (e) => {
                        // Connection message
                        // socket.send("new micasend message"
                        //     //     JSON.stringify({
                        //     //     "from": "micasend",
                        //     //     "type": "load",
                        //     //     "loaded": true
                        //     // })
                        // );
                        // connection established
                        resolve();
                        sendMsg("ping");
                    }

                    socket.onmessage = (data) => {
                        // console.log('websocket sent', data); // data.data
                        if (data.data.includes("new message notification")) {
                            $('#messages').load('printMessagesPart.php');

                            number_unread_messages++;
                            if (document.hidden || hidden_window) {
                                document.title = "(" + number_unread_messages + ") MicaSend";
                            }
                        }
                        // sendMsg('playerQuit');
                        // socket.close();
                    }

                    socket.onclose = (e) => {
                        // Return an error if any occurs
                        // console.log('Disconnected from websocket', e);
                        console.log("Reconnecting to websocket...");
                        $('#messages').load('printMessagesPart.php');
                        setTimeout(() => {
                            connect();
                        }, 1000);
                    }

                    socket.onerror = (e) => {
                        // Return an error if any occurs
                        console.log(e);
                        resolve();
                        // Try to connect again
                        if (tries < 3) {
                            tries++;
                            setTimeout(() => {
                                connect();
                            }, 1000);
                        } else
                            console.log("REFRESH THE PAGE");

                    }
                });
            }

            // check if a websocket is open
            const isOpen = function(ws) {
                return ws.readyState === ws.OPEN
            }

            // function sendMsg(type = 'ping') {
            //     if (isOpen(socket)) {
            //         socket.send(JSON.stringify({
            //             "from": "micasend",
            //             "type": type,
            //             "senttime": Date.now()
            //         }));
            //         console.log(`${type} sent to server`);
            //     }
            // }

            window.onfocus = () => {
                number_unread_messages = 0;
                document.title = "MicaSend";
                hidden_window = false
            };
            window.onblur = () => hidden_window = true;

            function sendMsg(message = 'ping') {
                if (isOpen(socket)) {
                    socket.send(`micasend:${message}`);
                    console.log(`${message} sent to server`);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Connect to the websocket
                connect();
            });
        </script>
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
            <span id="onlineVersionConnection">MicaSend web 1.0</span>
        </section>
    <?php
    } ?>
</body>

</html>