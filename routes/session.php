<?php

$app->post('/session_starter', function () use($app) {
    if(isset($_POST["idnumber"])){
        $_SESSION["idnumber"] = $_POST["idnumber"];
        $_SESSION["name"] = $_POST["name"];
        // print $_POST["no_ktp"];
    }
    else{
        header("Location: ./home");
        die();
    }
});

$app->get('/session_destroyer', function () use($app) {
    // Unset all of the session variables.
    $_SESSION = array();
    header("Location: ./");
    die();
});