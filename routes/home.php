<?php

$app->get('/home', function () use($app,$twig) {
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $name = $_SESSION["name"];
    }
    else{
        header("Location: ./");
        die();
    }
    echo $_SESSION["idnumber"]." ".$_SESSION["name"];
});