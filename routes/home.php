<?php

$app->get('/home', function () use($app,$twig) {
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: ./");
        die();
    }
	$greet = "Welcome, ".$username.". Select menu to get started.";
	
	$display=array(
		'pagetitle' => 'Main Menu - MobileID Web',
	    'heading' => 'Directive',
	    'subheading' => $greet,
	    'username' => $username,
	    'idnumber' => $idnumber,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
	);
	echo $twig->render('home.html',$display);
});