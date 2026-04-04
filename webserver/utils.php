<?php

function isConnected(): bool
{
    return isset($_SESSION["username"]) && !empty($_SESSION["username"]);// && isset($_SESSION["token"]);
}

function disconnect(): void
{
    unset($_SESSION["username"], $_SESSION["token"], $_SESSION["rank"]);
    header("Refresh:0");
    exit();
}

function RandomString()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < 30; $i++) {
        $randstring .= $characters[rand(0, strlen($characters))];
    }
    return $randstring;
}

function createFromWhoAccount($db, $whoID, $whoUsername) : string {
    $requser = $db->prepare("SELECT * FROM user WHERE quisuisje_id = ? OR pseudo = ?");
    $requser->execute(array($whoID, $whoUsername));
    $result = $requser->rowcount();
    if ($result > 0)
        return "";

    $newtoken = RandomString();
    $reqins = $db->prepare("INSERT INTO user(pseudo, token, rank, eloncertification, quisuisje_id) VALUES(?, ?, ?, ?, ?)");
	$reqins->execute(array($whoUsername, $newtoken, 1, 0, $whoID));
    return $newtoken;       
}
