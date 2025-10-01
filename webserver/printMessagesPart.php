<?php
include_once("db.php");


$req = $db->query("SELECT * FROM msg ORDER BY id DESC LIMIT 50");
$result = $req->fetchAll(PDO::FETCH_ASSOC);
$result = array_reverse($result);

foreach (array_reverse($result) as $key => $value) {
    //foreach message:
    $requser = $db->prepare("SELECT id, rank FROM user WHERE id = ?");
    $requser->execute(array($value["id_certified_user"]));
    $r = $requser->fetch();

    #print_r($r);
    #echo '<pre>'; print_r($r); echo '</pre>';

    echo "<div class=\"message\" id=\"msgN" . $value["id"] . "\"><span class=\"msgAuthor ";
    
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
    echo $value["sender"] . "</span><span class=\"msgContent\">";
    echo htmlspecialchars(htmlspecialchars_decode(str_replace(array("\\", "/", "<span>", "</span>"), "", str_replace("§", " ", $value["content"]))));
    echo "</span><span class=\"msgDatetime\">" . $value["date_time"] . "</span></div>";
}
