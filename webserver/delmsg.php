<?php
include "index.php";

function RandomString()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < 30; $i++) {
        $randstring .= $characters[rand(0, strlen($characters))];
    }
    return $randstring;
}

// exemple : 
// adduser.php?insert=lenomducompte&rank=0&elon=quelquechose_ici_implique_elon_certif&adminpseudo=Rubiks&admintoken=1234

if(isset($_GET['del']) AND !empty($_GET['del']) AND isset($_GET['adminpseudo']) AND !empty($_GET['adminpseudo'])  AND isset($_GET['admintoken']) AND !empty($_GET['admintoken']) )
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
        	if($user[2] >= 12) { //l'uitilisateur est au minimum modérateur ?
        		$iddel = htmlspecialchars($_GET['del']);
        		$reqins = $db->prepare("DELETE FROM msg WHERE id = ?");
				$reqins->execute(array($iddel));
        	}
        }
    }
}
