<?php
include "db.php";


//ptite fonction généré par chatGPT qui prend en entrée une variable $cases = array("x","x"," "...)
function estPartieTerminee($cases) {
  // Vérifie les lignes
  for ($i = 0; $i < 3; $i++) {
    if ($cases[$i*3] == $cases[$i*3+1] && $cases[$i*3+1] == $cases[$i*3+2] && $cases[$i*3] != " ") {
      return true;
    }
  }

  // Vérifie les colonnes
  for ($i = 0; $i < 3; $i++) {
    if ($cases[$i] == $cases[$i+3] && $cases[$i+3] == $cases[$i+6] && $cases[$i] != " ") {
      return true;
    }
  }

  // Vérifie les diagonales
  if ($cases[0] == $cases[4] && $cases[4] == $cases[8] && $cases[0] != " ") {
    return true;
  }
  if ($cases[2] == $cases[4] && $cases[4] == $cases[6] && $cases[2] != " ") {
    return true;
  }

  // Vérifie s'il reste des cases vides
  for ($i = 0; $i < 9; $i++) {
    if ($cases[$i] == " ") {
      return false;
    }
  }

  // Si aucune condition précédente n'a été remplie, cela signifie que la partie est nulle
  return "nul";
}



if(isset($_GET['player1']) AND !empty($_GET['player1'])  AND isset($_GET['player2']) AND !empty($_GET['player2']) AND isset($_GET['adminpseudo']) AND !empty($_GET['adminpseudo']) AND isset($_GET['admintoken']) AND !empty($_GET['admintoken']) AND isset($_GET['coup']) AND !empty($_GET['coup']))
{
	$player1 = htmlspecialchars($_GET['player1']);
	$player2 = htmlspecialchars($_GET['player2']);
	//$id = htmlspecialchars($_GET['id']);	
	$admintoken = htmlspecialchars($_GET['admintoken']);
	$adminpseudo = htmlspecialchars($_GET['adminpseudo']);
	$coup = htmlspecialchars($_GET['coup']);

	$req = $db->prepare("SELECT id, player1, player2, matrix, nextmoove, sequence FROM game WHERE (player1 = ? AND player2 = ?) OR (player2 = ? AND player1 = ?)");
	$req->execute(array($player1, $player2, $player1, $player2));
	$result = $req->fetchAll(PDO::FETCH_ASSOC);

	$requser = $db->prepare("SELECT id, token FROM user WHERE pseudo = ?");
    $requser->execute(array($adminpseudo));
    $resultu = $requser->rowcount();
    if ($resultu == 1) { //l'utilisateur existe t-il ?
        $user = $requser->fetch();
        if($user[1] == $admintoken) { //le token est-il bon ?
     		if($user[0] == $result[0]["nextmoove"]) { //est-ce à son tour de jouer
     			if(!in_array($coup, str_split($result[0]["sequence"], 2))) { //vérifier si le coup à déjà été joué
     				$coup = str_split($coup);
	     			$char_to_int = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
	     			if(in_array($coup[0], $char_to_int)) {
	     				if(((int) $coup[1]) < 10 AND ((int) $coup[1]) > 0) {
	     					$coup_x = (int) array_search($coup[0], $char_to_int)+1; //position y du coup
	     					$coup_y = (int) $coup[1]-1; 							  //position x du coup
	     					//dernier coup joué : array_pop(str_split($result[0]["sequence"], 2));
	     					$newmatrix = str_split($result[0]["matrix"]);

	     					var_dump($result[0]);
	     					if($user[0] == $result[0]["player1"]) {
	     						$char = "x";
	     					} elseif($user[0] == $result[0]["player2"]) {
	     						$char = "o";
	     					} else {
	     						header("Location: Degage_bien_loing");
	     					}

	     					echo $newmatrix[9*($coup_y)+$coup_x-1] = $char;

	     					if($result[0]["nextmoove"] == $player1) {
	     						$nextmoove = $player2;
	     					} else {
	     						$nextmoove = $player1;
	     					}

	     					$requp = $db->prepare('UPDATE game SET matrix = ?, nextmoove = ?, sequence = ? WHERE id = ?');
							$requp->execute(array(implode($newmatrix), $nextmoove, $result[0]["sequence"].$coup[0].$coup[1], $result[0]["id"]));
	     				}
	     			}
     			}
     		}
        }
    }
	
}

?>