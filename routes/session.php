<?php

$app->post('/session_starter', function () use($app) {
    
    $_SESSION["idnumber"] = $_POST["idnumber"];
    $_SESSION["name"] = $_POST["name"];
    
	//save to record
	$record = new WebRecord();
	$record->savelogin($_POST["idnumber"], "success");
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