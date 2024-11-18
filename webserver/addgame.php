<?php
include "db.php";

if(isset($_GET['player1']) AND !empty($_GET['player1']) AND isset($_GET['player2']) AND !empty($_GET['player2']) AND isset($_GET['adminpseudo']) AND !empty($_GET['adminpseudo'])  AND isset($_GET['admintoken']) AND !empty($_GET['admintoken']) )
{
    $player1 = htmlspecialchars($_GET['player1']);
    $player2 = htmlspecialchars($_GET['player2']);
	$admintoken = htmlspecialchars($_GET['admintoken']);
	$adminpseudo = htmlspecialchars($_GET['adminpseudo']);

	$requser = $db->prepare("SELECT id, token, rank FROM user WHERE pseudo = ?");
    $requser->execute(array($adminpseudo));
    $result = $requser->rowcount();
    if ($result == 1) { //l'utilisateur existe t-il ?
        $user = $requser->fetch();
        if($user[1] == $admintoken) { //le token est-il bon ?
        	//utilisateur certifié
        	if($user[2] >= 12) { //l'uitilisateur est minimum modérateur ?
                if(rand(0,1) == 1) {
                    $firstmoove = $player1;
                } else {
                    $firstmoove = $player2;
                }
                $matrix = "";
                for($i=0; $i<81;$i++) {
                    $matrix .= ".";
                }
        		$reqins = $db->prepare("INSERT INTO game(player1, player2, matrix, nextmoove, sequence) VALUES(?, ?, ?, ?, ?)");
				$reqins->execute(array($player1, $player2, $matrix, $firstmoove, ""));
        	}
        }
    }
}
