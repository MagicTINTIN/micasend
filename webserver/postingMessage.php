<?php @session_start();
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