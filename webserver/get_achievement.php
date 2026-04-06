<?php
session_start();
include_once("db.php");
include_once("utils.php");

include_once("cestmoi/main.php");

if (!isset($_SESSION["username"], $_SESSION["rank"], $_USER) || $_SESSION["rank"] <= 0 || !$_USER) exit;

$req = $db->query("SELECT * FROM msg ORDER BY id DESC LIMIT 1");
$result = $req->fetchAll(PDO::FETCH_ASSOC);

$found = false;
foreach ($result as $key => $msg) {
    if ($msg["sender"] == $_SESSION["username"]) {
        $found = true;
        break;
    }
}

if (!$found) exit;

echo add_achievement("micasender");