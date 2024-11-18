<?php
function read_password()
{
    $real_path = __DIR__.DIRECTORY_SEPARATOR.".passwd";
    $file = fopen($real_path, "r") or die("Unable to open DB password file!");;
    $password = fgets($file);
    fclose($file);
    return trim($password);
}
$db = new PDO('mysql:host=127.0.0.1;dbname=micasend','user', read_password());
?>