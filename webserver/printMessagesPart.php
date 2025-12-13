<?php @session_start();
include_once("db.php");

$req = $db->query("SELECT * FROM msg ORDER BY id DESC LIMIT 50");
$result = $req->fetchAll(PDO::FETCH_ASSOC);
$result = array_reverse($result);

function msg_decode(string $content) : string {
    return htmlspecialchars(htmlspecialchars_decode(str_replace(array("\\", "/", "<span>", "</span>"), "", str_replace("§", " ", $content))));
}

foreach (array_reverse($result) as $key => $value) {
    //foreach message:
    $requser = $db->prepare("SELECT id, rank FROM user WHERE id = ?");
    $requser->execute(array($value["id_certified_user"]));
    $r = $requser->fetch();
    $props = strlen($value["properties"]) > 0 ? explode(",", $value["properties"]) : [];
    $styleProps = [];
    $replyingTo = -1;
    foreach ($props as $_ => $p) {
        if ($p == "hidden") {
            if (!isset($_SESSION["rank"]) || $_SESSION["rank"] < 1 || $_SESSION["username"] != $value["sender"])
                continue 2;
            else
                array_push($styleProps, "hidden");
        } elseif (str_starts_with($p, "r:"))
            $replyingTo = (int) substr($p, 2);
        elseif (str_starts_with($p, "s:"))
            array_push($styleProps, substr($p, 2));
    }

    #print_r($r);
    #echo '<pre>'; print_r($r); echo '</pre>';

    echo "<div class=\"message\" id=\"msgN" . $value["id"] . "\">";
    if ($replyingTo > -1) {
        $req = $db->prepare("SELECT sender, content FROM msg WHERE id = :id");
        $req->execute(['id' => $replyingTo]);
        $msgs = $req->fetchAll();
        if (sizeof($msgs) > 0)
            echo "<span class='replyingTo' title='". $msgs[0]["sender"] . " - ". msg_decode($msgs[0]["content"])."'>⤷<span class='replySender'>" . $msgs[0]["sender"] . "</span>" . msg_decode($msgs[0]["content"]) . "</span>";
    }

    echo "<span class=\"msgAuthor ";

    if ($r && $r[1] > 0) {
        if ($r[1] == 16) {
            echo "msgAuthOwner" . "\">" . "<span class=\"msgAuthorBadge\">OWNER</span> ";
        } else if ($r[1] == 15) {
            echo "msgAuthAdmin" . "\">" . "<span class=\"msgAuthorBadge\">ADMIN</span> ";
        } else if ($r[1] == 12) {
            echo "msgAuthMod" . "\">" . "<span class=\"msgAuthorBadge\">MOD</span> ";
        } else if ($r[1] == 11) {
            echo "msgAuthBot" . "\">" . "<span class=\"msgAuthorBadge\">BOT</span> ";
        } else {
            echo "msgAuthVerified" . "\">" . "<span class=\"msgAuthorBadge\">v</span> ";
        }
    } else {
        echo "msgAuthNormal" . "\">" . "";
    }
    echo $value["sender"] . "</span><span class=\"msgContent";
    foreach ($styleProps as $_ => $s)
        echo " msgStyle" . ucfirst($s);
    echo "\">";
    echo msg_decode($value["content"]);
    echo "</span><span class=\"msgDatetime\">" . (str_contains($_SERVER['QUERY_STRING'], "debug") ? ("[" . $value["id"] . "] ") : "") . $value["date_time"] . "</span></div>";
}
