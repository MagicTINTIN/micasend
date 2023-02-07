<?php
include "index.php";
// http://127.0.0.1/micasend/web/msg.php?message=lol&sender=test
if(isset($_GET['message']) AND !empty($_GET['message']) AND isset($_GET['sender']) AND !empty($_GET['sender']))
{
	$msg = htmlspecialchars($_GET['message']);
	$sender = htmlspecialchars($_GET['sender']);
	$certif = 0;

	if(isset($_GET['token']) AND !empty($_GET['token'])) {
		$token = htmlspecialchars($_GET['token']);
		$requser = $db->prepare("SELECT id, token FROM user WHERE pseudo = ?");
        $requser->execute(array($sender));
        $result = $requser->rowcount();
        if ($result == 1) { //l'utilisateur existe t-il ?
            $user = $requser->fetch();
            if($user[1] == $token) { //le token est-il bon ?
            	//utilisateur certifié
            	$certif=$user[0];
            }
        }
	}

	$reqins = $db->prepare("INSERT INTO msg(content, sender, id_certified_user, date_time) VALUES(?, ?, ?, ?)");
	$reqins->execute(array($msg, $sender, $certif, date("Y-m-d H:i:s", time())));
	header('Location: msg.php');
}

$req = $db->query("SELECT * FROM msg LIMIT 20 BY id DESC");
$result = $req->fetchAll(PDO::FETCH_ASSOC);

if(isset($_GET['getmsg']) AND !empty($_GET['getmsg'])) {
	$getmsg = htmlspecialchars($_GET['getmsg']);
	if($getmsg == "id") {
		for($i=0; $i<count($result); $i++) {
			echo $result[$i]["id"];
			if($i < (count($result)-1)) {
				echo " ";
			}
		}
	} if($getmsg == "bash") {
		for($i=0; $i<count($result); $i++) {
			//foreach message

			$requser = $db->prepare("SELECT id, rank FROM user WHERE id = ?");
		    $requser->execute(array($result[$i]["id_certified_user"]));
		    $r = $requser->fetch();
		    if($r[1] > 0) {
		    	if($r[1] == 15) {
		    		echo "\\033[37m [\\033[31mAdmin\\033[37m]";
		    	}
		    	if($r[1] == 12) {
		    		echo "\\033[37m [\\033[31mMod\\033[37m]";
		    	}
		    	if($r[1] == 11) {
		    		echo "\\033[37m [\\033[31mBot\\033[37m]";
		    	}
		    }
		    if(!empty($r)) {
		    	echo "\\033[32m \\033[01m".$result[$i]["sender"]." \\033[0m";
		    } else {
		    	echo "\\033[31m ".$result[$i]["sender"]." \\033[0m";
		    }
		    echo $result[$i]["date_time"];
		    echo "\\n";
			echo "\\033[34m ".htmlspecialchars_decode(str_replace("§", " ", $result[$i]["content"]))."\\033[0m";
			if($i < (count($result)-1)) {
				echo "\\n\\n";
			}
		}
	} elseif($getmsg == "bashmod") {
		for($i=0; $i<count($result); $i++) {
			//foreach message

			$requser = $db->prepare("SELECT id, rank FROM user WHERE id = ?");
		    $requser->execute(array($result[$i]["id_certified_user"]));
		    $r = $requser->fetch();
		    if($r[1] > 0) {
		    	if($r[1] == 15) {
		    		echo "\\033[37m [\\033[31mAdmin\\033[37m]";
		    	}
		    	if($r[1] == 12) {
		    		echo "\\033[37m [\\033[31mMod\\033[37m]";
		    	}
		    	if($r[1] == 11) {
		    		echo "\\033[37m [\\033[31mBot\\033[37m]";
		    	}
		    }
		    if(!empty($r)) {
		    	echo "\\033[32m \\033[01m".$result[$i]["sender"]."\\033[33m(".$r[0].") \\033[0m";
		    } else {
		    	echo "\\033[31m ".$result[$i]["sender"]." \\033[0m";
		    }
		    echo $result[$i]["date_time"];
			echo " \\033[33m(".$result[$i]["id"].") \\033[0m";
		    echo "\\n";
			echo "\\033[34m ".htmlspecialchars_decode(str_replace("§", " ", $result[$i]["content"]))."\\033[0m";
			if($i < (count($result)-1)) {
				echo "\\n\\n";
			}
		}
	/*
	} elseif ($getmsg == "content") {
		for($i=0; $i<count($result); $i++) {
			echo $result[$i]["content"];
			if($i < (count($result)-1)) {
				echo " ";
			}
		}
	} elseif ($getmsg == "sender") {
		for($i=0; $i<count($result); $i++) {
			echo $result[$i]["sender"];
			if($i < (count($result)-1)) {
				echo " ";
			}
		}
	} elseif ($getmsg == "date_time") {
		for($i=0; $i<count($result); $i++) {
			echo str_replace(" ","§",$result[$i]["date_time"]);
			if($i < (count($result)-1)) {
				echo " ";
			}
		}
	} elseif ($getmsg == "id_certified_user") {
		for($i=0; $i<count($result); $i++) {
			echo $result[$i]["id_certified_user"];
			if($i < (count($result)-1)) {
				echo " ";
			}
		}
	} elseif ($getmsg == "rank") {
		for($i=0; $i<count($result); $i++) {
			$requser = $db->prepare("SELECT rank FROM user WHERE id = ?");
		    $requser->execute(array($result[$i]["id_certified_user"]));
		    $r = ($requser->fetch())[0];
		    if(!empty($r)) {
		    	echo $r;
		    } else {
		    	echo 0;
		    }
		    if($i < (count($result)-1)) {
				echo " ";
			}
		}
	*/} elseif ($getmsg == "json") {
		echo "[";
		for($i=0; $i<count($result); $i++) {
			$requser = $db->prepare("SELECT rank FROM user WHERE id = ?");
		    $requser->execute(array($result[$i]["id_certified_user"]));
		    $r = ($requser->fetch())[0];
		    if(empty($r)) {
		    	$r=0;
		    }
			echo '{"content":"'.$result[$i]["content"].'", "sender":"'.$result[$i]["sender"].'", "date_time":"'.$result[$i]["date_time"].'", "id_certified_user":"'.$result[$i]["id_certified_user"].'", "rank":"'.$r.'"}';
			if($i < (count($result)-1)) {
				echo ",";
			}
		}
		echo "]";
	} else {
		for($i=0; $i<count($result); $i++) {
			echo '{"'.$result[$i]["content"].'";"'.$result[$i]["sender"].'";"'.$result[$i]["date_time"].'";"'.$result[$i]["id_certified_user"].'"}<br>';
		}
	}
}

?>