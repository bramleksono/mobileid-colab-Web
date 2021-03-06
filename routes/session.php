<?php

$app->post('/session_starter', function () use($app) {
    
    $_SESSION["idnumber"] = $_POST["idnumber"];
    $_SESSION["name"] = $_POST["name"];
    
	//save to record
	$record = new WebRecord();
	$record->recordlogin($_POST["idnumber"], "success");
});

$app->get('/force_session_starter', function () use($app) {
    $_SESSION["idnumber"] = "1231230509890001";
    $_SESSION["name"] = "Bramanto Leksono";
    echo "You now login as ". $_SESSION["name"];
});

$app->get('/session_destroyer', function () use($app) {

    //save to record
	$record = new WebRecord();
	if (isset($_SESSION["idnumber"])) {
    	$record->recordlogin($_SESSION["idnumber"], "logout");
	}

    // Unset all of the session variables.
    $_SESSION = array();
    $app->redirect('/home');
});