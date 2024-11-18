<?php
include "db.php";

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

if(isset($_GET['insert']) AND !empty($_GET['insert']) AND isset($_GET['adminpseudo']) AND !empty($_GET['adminpseudo'])  AND isset($_GET['admintoken']) AND !empty($_GET['admintoken']) )
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
        		$newpseudo = htmlspecialchars($_GET['insert']);
        		$newtoken = RandomString();
        		$newrank = 0;
        		$neweloncertif = 0;
        		if(isset($_GET['rank']) AND !empty($_GET['rank'])) {
        			$newrank = (int) htmlspecialchars($_GET['rank']);
        			if($newrank > 15 OR $newrank < 0) {
        				$newrank = 0;
        			}
        		}
        		if(isset($_GET['elon']) AND !empty($_GET['elon'])) {
        			$neweloncertif = 1;
        		}
        		$reqins = $db->prepare("INSERT INTO user(pseudo, token, rank, eloncertification) VALUES(?, ?, ?, ?)");
				$reqins->execute(array($newpseudo, $newtoken, $newrank, $neweloncertif));
				echo $newtoken;
        	}
        }
    }
}
