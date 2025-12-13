<?php
include "db.php";
// http://127.0.0.1/micasend/web/msg.php?message=lol&sender=test
if (isset($_REQUEST['message']) and !empty($_REQUEST['message']) and isset($_REQUEST['sender']) and !empty($_REQUEST['sender'])) {
	$msg = htmlspecialchars((string) $_REQUEST['message']);
	$sender = htmlspecialchars($_REQUEST['sender']);
	$certif = 0;
	$rank = 0;
	$isCertified = false;

	if (isset($_REQUEST['token']) and !empty($_REQUEST['token'])) {
		$token = htmlspecialchars($_REQUEST['token']);
		$requser = $db->prepare("SELECT id, token, rank FROM user WHERE pseudo = ?");
		$requser->execute(array($sender));
		$result = $requser->rowcount();
		if ($result == 1) { //l'utilisateur existe t-il ?
			$user = $requser->fetch();
			if ($user[1] == $token) { //le token est-il bon ?
				//utilisateur certifié
				$certif = $user[0];
				$rank = $user[2];
				$isCertified = true;
			}
		}
	}

	if (str_starts_with($msg, "/")) {
		if (!$isCertified) {
			header('Location: msg.php');
			exit;
		}

		if ($rank < 16) {
			// is in safe mode
			$req = $db->prepare("SELECT * from msg WHERE (lower(content) LIKE '/safe ') DESC LIMIT 1;");
			$result = $req->fetchAll(PDO::FETCH_ASSOC);
		}

		$commmand_list = [
			// "/cmd" => ["message sent in chat", "style", min_level_permission, "websocket message"]
			"/bix_honk" => ["HONK", "shake", 10, "bix/goto:horn"],
			"/bix_tts " => ["# " . strtoupper(substr($msg, 9)), "tts", 10, "bix/goto:tts>" . substr($msg, 9)],
			"/safe " => [$msg, "hidden", 12, ""],
			"/test " => [substr($msg, 6), "rainbow", 1, ""],
		];

		foreach ($commmand_list as $key => $value) {
			if (!str_starts_with($msg, $key)) continue;

			if ($rank < $value[2]) {
				header('Location: msg.php');
				exit;
			}

			$msg = $value[0];
			if ($value[3] != "")
				@file_get_contents(
					"http://127.0.0.1:6442/push",
					false,
					stream_context_create(['http' => [
						'method' => 'POST',
						'header' => "Content-Type: text/plain\r\n",
						'content' => $value[3]
					]])
				);

			if ($msg == "") {
				header('Location: msg.php');
				exit;
			}
		}
	}

	$msg = str_replace(" ", "§", $msg);
	$msg = preg_replace('/[\x00-\x1F\x7F]/u', '', $msg);

	$reqins = $db->prepare("INSERT INTO msg(content, sender, id_certified_user, date_time) VALUES(?, ?, ?, ?)");
	$reqins->execute(array($msg, $sender, $certif, date("Y-m-d H:i:s", time())));

	@file_get_contents(
		"http://127.0.0.1:6442/push",
		false,
		stream_context_create(['http' => [
			'method' => 'POST',
			'header' => "Content-Type: text/plain\r\n",
			'content' => "micasend:new micasend message"
		]])
	);

	header('Location: msg.php');
}

$req = $db->query("SELECT * FROM msg ORDER BY id DESC LIMIT 20");
$result = $req->fetchAll(PDO::FETCH_ASSOC);
$result = array_reverse($result);

if (isset($_GET['getmsg']) and !empty($_GET['getmsg'])) {
	$getmsg = htmlspecialchars($_GET['getmsg']);
	if ($getmsg == "id") {
		for ($i = 0; $i < count($result); $i++) {
			echo $result[$i]["id"];
			if ($i < (count($result) - 1)) {
				echo " ";
			}
		}
	}
	if ($getmsg == "bash") {
		for ($i = 0; $i < count($result); $i++) {
			//foreach message

			$requser = $db->prepare("SELECT id, rank FROM user WHERE id = ?");
			$requser->execute(array($result[$i]["id_certified_user"]));
			$r = $requser->fetch();
			if (!empty($r) && $r[1] > 0) {
				if ($r[1] == 16) {
					echo "\\033[37m [\\033[31mOwner\\033[37m]";
				}
				if ($r[1] == 15) {
					echo "\\033[37m [\\033[31mAdmin\\033[37m]";
				}
				if ($r[1] == 12) {
					echo "\\033[37m [\\033[31mMod\\033[37m]";
				}
				if ($r[1] == 11) {
					echo "\\033[37m [\\033[31mBot\\033[37m]";
				}
			}
			if (!empty($r)) {
				echo "\\033[32m \\033[01m" . $result[$i]["sender"] . " \\033[0m";
			} else {
				echo "\\033[31m " . $result[$i]["sender"] . " \\033[0m";
			}
			echo $result[$i]["date_time"];
			echo "\\n";
			echo "\\033[34m " . htmlspecialchars_decode(str_replace(array("\\", "/"), "", str_replace("§", " ", $result[$i]["content"]))) . "\\033[0m";
			if ($i < (count($result) - 1)) {
				echo "\\n\\n";
			}
		}
	} elseif ($getmsg == "bashmod") {
		for ($i = 0; $i < count($result); $i++) {
			//foreach message

			$requser = $db->prepare("SELECT id, rank FROM user WHERE id = ?");
			$requser->execute(array($result[$i]["id_certified_user"]));
			$r = $requser->fetch();
			if ($r[1] > 0) {
				if ($r[1] == 16) {
					echo "\\033[37m [\\033[31mOwner\\033[37m]";
				}
				if ($r[1] == 15) {
					echo "\\033[37m [\\033[31mAdmin\\033[37m]";
				}
				if ($r[1] == 12) {
					echo "\\033[37m [\\033[31mMod\\033[37m]";
				}
				if ($r[1] == 11) {
					echo "\\033[37m [\\033[31mBot\\033[37m]";
				}
			}
			if (!empty($r)) {
				echo "\\033[32m \\033[01m" . $result[$i]["sender"] . "\\033[33m(" . $r[0] . ") \\033[0m";
			} else {
				echo "\\033[31m " . $result[$i]["sender"] . " \\033[0m";
			}
			echo $result[$i]["date_time"];
			echo " \\033[33m(" . $result[$i]["id"] . ") \\033[0m";
			echo "\\n";
			echo "\\033[34m " . htmlspecialchars_decode(str_replace(array("\\", "/"), "", str_replace("§", " ", $result[$i]["content"]))) . "\\033[0m";
			if ($i < (count($result) - 1)) {
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
	*/
	} elseif ($getmsg == "json") {
		echo "[";
		for ($i = 0; $i < count($result); $i++) {
			$requser = $db->prepare("SELECT rank FROM user WHERE id = ?");
			$requser->execute(array($result[$i]["id_certified_user"]));
			$r = ($requser->fetch());
			if (empty($r)) {
				$r = 0;
			} else {
				$r = $r[0];
			}
			echo '{"id":"' . $result[$i]["id"] . '", "content":"' . str_replace('&quot;', '\"', $result[$i]["content"]) . '", "sender":"' . str_replace('&quot;', '\"', $result[$i]["sender"]) . '", "date_time":"' . $result[$i]["date_time"] . '", "id_certified_user":"' . $result[$i]["id_certified_user"] . '", "rank":"' . $r . '"}';
			if ($i < (count($result) - 1)) {
				echo ",";
			}
		}
		echo "]";
	} else {
		for ($i = 0; $i < count($result); $i++) {
			echo '{"' . $result[$i]["content"] . '";"' . $result[$i]["sender"] . '";"' . $result[$i]["date_time"] . '";"' . $result[$i]["id_certified_user"] . '"}<br>';
		}
	}
}
