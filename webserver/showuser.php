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
        		$req = $db->query("SELECT * FROM user");
				$result = $req->fetchAll(PDO::FETCH_ASSOC);
        		for($i=0; $i<count($result); $i++) {
					echo $result[$i]["pseudo"]."(".$result[$i]["id"].")"
						."\\ntoken : ".$result[$i]["token"]
						."	rank : ".$result[$i]["rank"]
						."	ElonCertif : ".$result[$i]["eloncertification"];
					if($i < (count($result)-1)) {
						echo "\\n\\n";
					}
				}
        	}
        }
    }
}

?>