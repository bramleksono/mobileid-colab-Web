<?php

$app->post('/signing/document', function () use($app) {
    global $CAdocument;
    global $Webaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $documentcontroller = new WebDocument();
    $document = $documentcontroller->fetchDocumentDB($_POST["documentnumber"]);
    $result = $documentcontroller->parseDocument($document);
    
    if ($result["signer"] == $idnumber) {
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
        $result = sendjson($form, $CAdocument);
        $result = json_decode($result, true);
        if ($result["success"]) {
            $app->flash('info', 'Request sent. Check your device to confirm signing request.');
            
            //save to record
            $record = new WebRecord();
        	$record->recordsigning($idnumber, $_POST["documentnumber"], "request");
    	
        } else {
            $app->flash('error', $result["reason"]);
        }
        
    }
    else {
        echo "Cannot proceed. You are not signer.";    
    }    
});

$app->post('/signing/verifydocument', function () {

	$idnumber = $_POST["signerid"];
	$controller = new WebController($idnumber);
	$controller->verifySignature($_POST);

});