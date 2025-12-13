<?php

function isConnected(): bool
{
    return isset($_SESSION["username"]) && !empty($_SESSION["username"]);// && isset($_SESSION["token"]);
}

function disconnect(): void
{
    unset($_SESSION["username"], $_SESSION["token"], $_SESSION["rank"]);
    header("Refresh:0");
    exit();
}
