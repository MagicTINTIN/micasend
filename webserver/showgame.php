<?php
include "db.php";

if(isset($_GET['player1']) AND !empty($_GET['player1'])  AND isset($_GET['player2']) AND !empty($_GET['player2']))
{
	$player1 = htmlspecialchars($_GET['player1']);
	$player2 = htmlspecialchars($_GET['player2']);

	$req = $db->prepare("SELECT id, matrix, nextmoove, sequence FROM game WHERE (player1 = ? AND player2 = ?) OR (player2 = ? AND player1 = ?)");
	$req->execute(array($player1, $player2, $player1, $player2));
	$result = $req->fetchAll(PDO::FETCH_ASSOC);

	$m_db = str_split($result[0]["matrix"], 9);

	$requser1 = $db->prepare("SELECT pseudo FROM user WHERE id = ?");
    $requser1->execute(array($player1));
    $user1 = $requser1->fetchAll();

    $requser2 = $db->prepare("SELECT pseudo FROM user WHERE id = ?");
    $requser2->execute(array($player2));
    $user2 = $requser2->fetchAll();

    if($result[0]["nextmoove"] == $player1) {
    	$nextplayer = $user1[0]["pseudo"];
    } else {
    	$nextplayer = $user2[0]["pseudo"];    	
    }

	echo "\\n\\n Partie entre \\033[33m".$user1[0]["pseudo"]."\\033[0m et \\033[33m".$user2[0]["pseudo"]."\\033[0m\\n \\033[33m(".$result[0]["id"].") \\033[0m";
	echo " C'est au tour de \\033[33m".$nextplayer."\\033[0m de jouer !\\n\\n\\n";

	for($y=0;$y<14;$y++) {
		echo " ";
		if($y == 0) {
			echo "0 | A B C | D E F | G H I |";
		} elseif($y == 1 OR $y == 5 OR $y == 9 OR $y == 13) {
			echo "---------------------------";
		} else {
			for($x=0;$x<14;$x++) {
				if($x == 0) {
					if($y > 9) {
						echo $y-3;
					} elseif($y > 5) {
						echo $y-2;
					} else {
						echo $y-1;
					}
					echo " ";
				} elseif($x == 1 OR $x == 5 OR $x == 9 OR $x == 13) {
					echo "| ";
				} else {
					if($x > 9) {
						$x_int = $x-4;
					} elseif($x > 5) {
						$x_int = $x-3;
					} else {
						$x_int = $x-2;
					}
					if($y > 9) {
						$y_int = $y-4;
					} elseif($y > 5) {
						$y_int = $y-3;
					} else {
						$y_int = $y-2;
					}
					echo str_split($m_db[$y_int])[$x_int];
					echo " ";
				}
			}
		}
		echo "\\n";
	}

	echo "\\n\\n \\033[36mSequence : \\033[0m";
	for($i=0; $i<count(str_split($result[0]["sequence"], 2));$i++) {
		echo str_split($result[0]["sequence"], 2)[$i];
		if($i < count(str_split($result[0]["sequence"], 2))-1) {
			echo " ; ";
		}
	}
}

?>