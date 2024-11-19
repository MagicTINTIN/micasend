<?php
include_once("db.php");


$req = $db->query("SELECT * FROM msg ORDER BY id DESC LIMIT 50");
$result = $req->fetchAll(PDO::FETCH_ASSOC);
$result = array_reverse($result);

for ($i = 0; $i < count($result); $i++) {
    //foreach message:
    $requser = $db->prepare("SELECT id, rank FROM user WHERE id = ?");
    $requser->execute(array($result[$i]["id_certified_user"]));
    $r = $requser->fetch();

    echo "<div class=\"message\" id=\"msgN" . $result[$i]["id"] . "\"><span class=\"msgAuthor ";
    if ($r[1] > 0) {
        if ($r[1] == 15) {
            echo "msgAuthAdmin" . "\">" . "<span class=\"msgAuthorBadge\">ADMIN</span> " ;
        }
        else if ($r[1] == 12) {
            echo "msgAuthMod" . "\">" . "<span class=\"msgAuthorBadge\">MOD</span> " ;
        }
        else if ($r[1] == 11) {
            echo "msgAuthBot" . "\">" . "<span class=\"msgAuthorBadge\">BOT</span> " ;
        }
        else {
            echo "msgAuthVerified" . "\">" . "<span class=\"msgAuthorBadge\">v</span> " ;
        }
    } else {
        echo "msgAuthNormal" . "\">" . "" ;
    }
    echo $result[$i]["sender"] . "</span><span class=\"msgContent\">";
    echo htmlspecialchars_decode(str_replace(array("\\", "/", "<span>", "</span>"), "", str_replace("§", " ", $result[$i]["content"])));
    echo "</span><span class=\"msgDatetime\">" . $result[$i]["date_time"] . "</span></div>";
}
