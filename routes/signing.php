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

$app->post('/signing/document', function () use($app) {
    global $CAdocument;
    
    //send query to CA
    $form = array(  'documentnumber' => $_POST["documentnumber"],
                    'projectname' => $_POST["projectname"],
            		'signerid' => $_POST["signerid"],
            		'fileurl' => $_POST["fileurl"],
            		'filehash' => $_POST["filehash"],
            		'documentname' => $_POST["documentname"],
            		'description' => $_POST["description"],
            		'callback' => $_POST["callback"]
            );
            
    $form = json_encode($form);

    //example output {"callback":"tes","description":"Tes","documentname":"Dokumen 3","filehash":"6659b399f20a5ec968741b2659a4c209417b12ca9ef20404d91c13aff31872a7","fileurl":"http://files.parsetfss.com/ec7e7074-b676-4984-92ba-13c0c26c2d0d/tfss-c45261a4-45ab-4846-9a42-90e990ed10cf-ProgLan-23213321-Tugas1.pdf","signerid":"1231230509890003","projectname":"Tes 1"}
    echo $result = sendjson($form, $CAdocument);
});

$app->post('/signing/verifydocument', function () {

	$idnumber = $_POST["signerid"];
	$controller = new WebController($idnumber);
	$controller->verifySignature($_POST);

});