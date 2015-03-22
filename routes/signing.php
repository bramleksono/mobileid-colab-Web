<?php

$app->get('/signing', function () use($app,$twig) {
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: ./");
        die();
    }

	echo $greet = "Welcome, ".$username.". Your document are listed below.";
	
});