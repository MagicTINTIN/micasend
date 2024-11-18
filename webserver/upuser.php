<?php
include "db.php";

if(isset($_GET['user']) AND !empty($_GET['user']) AND isset($_GET['column']) AND !empty($_GET['column']) AND isset($_GET['value']) AND !empty($_GET['value']) AND isset($_GET['adminpseudo']) AND !empty($_GET['adminpseudo']) AND isset($_GET['admintoken']) AND !empty($_GET['admintoken']))
{
	$userup = (int) htmlspecialchars($_GET['user']);
	$column = htmlspecialchars($_GET['column']);
	$value = htmlspecialchars($_GET['value']);
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
        		// faire la requete
        		$req = $db->prepare('UPDATE user SET '.$column.' = ? WHERE id = ?');
				$req->execute(array($value, $userup));
        	}
        }
    }
}

?>