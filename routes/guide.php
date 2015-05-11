<?php

$app->get('/guide', function () use($app,$twig) {
    global $Webaddr;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
	
	$display=array(
		'pagetitle' => 'Main Menu - MobileID Web',
	    'username' => $username,
	    'idnumber' => $idnumber,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
	);
	
	echo $twig->render('guide.html',$display);
});