<?php

$app->post('/session_starter', function () use($app,$twig) {
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