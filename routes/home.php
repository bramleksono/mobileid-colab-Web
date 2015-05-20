<?php

$app->get('/home', function () use($app,$twig) {
    global $Webaddr;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
	$greet = "Welcome, ".$username.". Select menu to get started (Selamat datang, ".$username.". Pilih menu disamping untuk memulai).";
	
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