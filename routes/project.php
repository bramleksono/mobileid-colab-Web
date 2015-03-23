<?php

//Parse Backend
use Parse\ParseObject;
use Parse\ParseQuery;

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
	
	$controller = new WebController($idnumber);
	$list = $controller->showProject();
	
	$creatorcontent = $list["creatortext"];
	$clientcontent = $list["clienttext"];
	
	$emptystatus = false;
	$creatorstatus = true;
	$clientstatus = true;
	
	//logic to show project list
	if ($creatorcontent == "") {
		$creatorstatus = false;
	}
	if ($clientcontent == "") {
		$clientstatus = false;
	}	
	if (($creatorstatus == false) && ($clientstatus == false)) {
		$emptystatus = true;
	}
	
	$display=array(
		'pagetitle' => 'Project List - MobileID Web',
	    'heading' => 'Project List',
	    'username' => $username,
	    'idnumber' => $idnumber,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
		'creator' => $creatorstatus,
		'client' => $clientstatus,
		'empty' => $emptystatus,
		'creatorcontent' => $creatorcontent,
		'clientcontent' => $clientcontent
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
			echo "CA Initial Check".PHP_EOL;
			echo "ID Number " . $idnumber . " with initial " . $result["initial"];			
		} else {
			echo "CA Initial Check".PHP_EOL;
			echo "Failed. Reason : ".$result["reason"];
		}
	} else 
		echo "Failed. Reason : Cannot connect to CA";
});

$app->post('/newproject/create', function () use($app,$twig) {
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
    
	$projectname = $_POST["projectname"];
	$clientid = $_POST["clientid"];
	
	unset($_POST["projectname"]);
	unset($_POST["clientid"]);
	
	//get milestone
	$stack = array();
	foreach ($_POST as $milestone) {
		array_push($stack, $milestone);
	}
	
	$stack = json_encode($stack);
	
	$form = array(	"creator" => $idnumber, "projectname" => $projectname, "client" => $clientid, "milestone" => $stack);
	
	//send to controller
	$controller = new WebController($idnumber);
	$result = $controller->createProject($form);

	if ($result) {
		echo "Project created";
	} else {
		echo "Cannot save to database";
	}
	
	//echo "Total Milestone = ".count($stack);
});

$app->get('/project/:projectnumber', function ($projectnumber) use ($twig) {
	echo $projectnumber;
});