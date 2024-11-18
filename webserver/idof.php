<?php
include "db.php";
if(isset($_GET['pseudo']) AND !empty($_GET['pseudo'])) {
	$pseudo = htmlspecialchars($_GET['pseudo']);
	$req = $db->prepare("SELECT id FROM user WHERE pseudo = ?");
	$req->execute(array($pseudo));
	$result = $req->fetchAll(PDO::FETCH_ASSOC);
    echo $result[0]["id"];
}