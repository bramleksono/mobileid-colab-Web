<?php

$app->post('/session_starter', function () use($app) {
    global $Webaddr;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
});


$app->get('/force_session_starter', function () use($app) {
    $_SESSION["idnumber"] = "1231230509890001";
    $_SESSION["name"] = "Bramanto Leksono";
    echo "You now login as ". $_SESSION["name"];
});

$app->get('/session_destroyer', function () use($app) {
    // Unset all of the session variables.
    $_SESSION = array();
    $app->redirect('/home');
});