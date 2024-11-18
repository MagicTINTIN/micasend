<?php
include "db.php";

if(isset($_GET['adminpseudo']) AND !empty($_GET['adminpseudo'])  AND isset($_GET['admintoken']) AND !empty($_GET['admintoken']))
{
	$admintoken = htmlspecialchars($_GET['admintoken']);
	$adminpseudo = htmlspecialchars($_GET['adminpseudo']);

	$requser = $db->prepare("SELECT id, token, rank FROM user WHERE pseudo = ?");
    $requser->execute(array($adminpseudo));
    $result = $requser->rowcount();
    if ($result == 1) { //l'utilisateur existe t-il ?
        $user = $requser->fetch();
        if($user[1] == $admintoken) { //le token est-il bon ?
        	//utilisateur certifié
        	if($user[2] == 15) { //l'uitilisateur est admin ?
        		$reqins = $db->query("DELETE FROM msg");
        	}
        }
    }
}

?>