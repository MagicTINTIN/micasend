<?php session_start();
include_once("db.php");
include_once("utils.php");

if (isset($_POST["connect"]) && isset($_POST["username"])) {
    $_SESSION["username"] = htmlspecialchars($_POST["username"]);

    if (isset($_POST['token']) and !empty($_POST['token'])) {
        $_SESSION["token"] = htmlspecialchars($_POST["token"]);
        $token = htmlspecialchars($_SESSION['token']);
        $requser = $db->prepare("SELECT token, rank FROM user WHERE pseudo = ?");
        $requser->execute(array($_SESSION["username"]));
        $result = $requser->rowcount();
        if ($result == 1) { //l'utilisateur existe t-il ?
            $user = $requser->fetch();
            if ($user[0] == $token) { //le token est-il bon ?
                $_SESSION["rank"] = $user[1];
            }
        }
    }

    header("Refresh:0");
    exit();
}

if (isset($_POST["disconnect"])) {
    disconnect();
}

// include_once("postingMessage.php");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/vars.css">
    <link rel="stylesheet" href="./styles/common.css">
    <link rel="stylesheet" href="./styles/reallyincommon.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>MicaSend<?php if (!isConnected()) echo " - Connection" ?></title>

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
                <span id="replying" onclick="replyTo('','');">Replying to ...</span>

                <div id="mainForm">
                    <input type="hidden" name="replyID" value="" id="replyToInput">
                    <input type="text" id="mainInput" placeholder="Write your message here" name="message" autocomplete="off" autofocus="yes">
                    <button id="mainSubmit">/></button>
                </div>
            </section>
        </main>

        <footer>
            <div>
                <form method="post">
                    <span><?php
                            echo $_SESSION["username"];

                            if (isset($_SESSION["rank"]) && $_SESSION["rank"] > 0) {
                                $ranks = ["unverified", "V", "V", "V", "V", "V", "V", "V", "V", "V", "V", "BOT", "Mod", "SMod", "Dev", "Admin", "Owner"];
                                echo " <span class='msgAuthorBadge userBadge" . $ranks[$_SESSION["rank"]] . "'>" . $ranks[$_SESSION["rank"]] . "</span>";
                            }
                            ?></span>
                    <span>|</span>
                    <input type="submit" name="disconnect" value="Log out">
                </form>
                <span id="onlineVersion">MicaSend web 1.2</span>
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
                            'replyID': document.getElementById("replyToInput").value,
                            'sender': '<?php echo $_SESSION['username']; ?>',
                            <?php if (isset($_SESSION['token']) && !empty($_SESSION['token'])) { ?> 'token': '<?php echo $_SESSION['token']; ?>'
                            <?php } ?>
                        })
                    }).then(response => response.text())
                    .then(data => {
                        // console.log("FETCH RES:", data);
                        sendMsg("new micasend message");
                        document.getElementById("mainInput").value = "";
                        replyTo("");
                    })
                    .catch(e => console.error("ERROR:", e));
            }

            number_unread_messages = 0;
            hidden_window = document.hidden;

            const connect = function() {
                // Return a promise, which will wait for the socket to open
                return new Promise((resolve, reject) => {

                    const socketUrl = `wss://magictintin.fr/ws`

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
                            $('#messages').load('printMessagesPart.php<?php echo str_contains($_SERVER['QUERY_STRING'], "debug") ? "?debug" : "" ?>', () => currentlyReplyingTo());

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
                        $('#messages').load('printMessagesPart.php<?php echo str_contains($_SERVER['QUERY_STRING'], "debug") ? "?debug" : "" ?>', () => currentlyReplyingTo());
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

            function replyTo(idMsg, author = "") {
                if (idMsg) {
                    if (document.getElementById("replyToInput").value)
                        document.getElementById("msgN" + document.getElementById("replyToInput").value).classList.remove("replyingToMsg");
                    document.getElementById("msgN" + idMsg).classList.add("replyingToMsg");
                    document.getElementById("mainInput").placeholder = "Write your reply here";
                    document.getElementById("replying").innerText = "replying to " + author;
                    document.getElementById("replying").style.display = "block";
                } else {
                    document.getElementById("msgN" + document.getElementById("replyToInput").value).classList.remove("replyingToMsg");

                    document.getElementById("mainInput").placeholder = "Write your message here";
                    document.getElementById("replying").style.display = "none";
                }
                document.getElementById("replyToInput").value = idMsg;
            }

            function currentlyReplyingTo() {
                if (document.getElementById("replyToInput").value)
                    document.getElementById("msgN" + document.getElementById("replyToInput").value).classList.add("replyingToMsg");
            }
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
            <span id="onlineVersionConnection">MicaSend web 1.2</span>
        </section>
    <?php
    } ?>
</body>

</html>