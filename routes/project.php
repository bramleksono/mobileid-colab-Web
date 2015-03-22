<?php

$app->get('/project', function () use($app,$twig) {
    /*
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: ./");
        die();
    }
    */
	$idnumber = "1231230509890001";
    $username = "Bramanto Leksono";
	$greet = "Below are your current project";
	
	$display=array(
		'pagetitle' => 'Project List - MobileID Web',
	    'heading' => 'Project List',
	    'subheading' => $greet,
	    'username' => $username,
	    'idnumber' => $idnumber,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
	);
	
	echo $twig->render('project.html',$display);
	
});

$app->get('/newproject', function () use($app,$twig) {
    /*
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: ./");
        die();
    }
    */
	$idnumber = "1231230509890001";
    $username = "Bramanto Leksono";
	
	$display=array(
		'pagetitle' => 'Project List - MobileID Web',
	    'heading' => 'Create New Project',
	    'username' => $username,
	    'idnumber' => $idnumber,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
	);
	
	echo $twig->render('newproject.html',$display);
	
});

$app->post('/newproject/getinitial', function () {
	global $CAuserinitial;
	
	$idnumber = $_POST["userid"];
	
	if ($idnumber == "") {
		echo "Failed. Reason : You must provide ID Number";
		die();
	}
	
	
	$form = array ("userinfo" => array ("nik" => $idnumber));
	$form = json_encode($form);
	
	$result = sendjson($form,$CAuserinitial);
	$result = json_decode($result, true);
	
	if ($result) {
		if ($result["success"]) {
			$result = json_decode($result, true);
			echo "CA Initial Check".PHP_EOL;
			echo "ID Number " . $idnumber . " with initial " . $result["initial"];			
		} else {
			echo "CA Initial Check".PHP_EOL;
			echo "Failed. Reason : ".$result["reason"];
		}
	} else 
		echo "Failed. Reason : Cannot connect to CA";
});

$app->post('/tesform', function () use($app,$twig) {
	
	$projectname = $_POST["projectname"];
	
	unset($_POST["projectname"]);
	
	$stack = array();
	foreach ($_POST as $milestone) {
		array_push($stack, $milestone);
	}
	
	var_dump($stack);
	
	echo "Total Milestone = ".count($stack);
});