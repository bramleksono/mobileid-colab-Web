<?php

$app->get('/document', function () use($app,$twig) {
    global $Webaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
	$controller = new WebController($idnumber);
	$list = $controller->showDocument();
	
	$documentcontent = $list["signerdocument"];
	
	$emptystatus = false;

	//logic to show project list
	if (!$documentcontent) {
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
		'empty' => $emptystatus,
		'documentcontent' => $documentcontent
	);

	echo $twig->render('documentlist.html',$display);
});


$app->get('/document/:documentnumber', function ($documentnumber) use ($twig) {
    global $Webdocumentreceive;
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
    $document = $documentcontroller->fetchDocumentDB($documentnumber);
    $result = $documentcontroller->parseDocument($document);
    
    $project = $documentcontroller->getProject($result["project"]);
    $projectname = $project->get('projectname');
    $projectnumber = $project->get('projectnumber');
    $finishproject = $project->get('finishproject');
    $milestone = json_decode($project->get('milestone'), true);
    $milestonenumber = $result["milestone"];
    $fileurl = $result["originalfile"]->getURL();
    
    //construct document info
    $projectadrress = $Webaddr."/project/".$projectnumber;
    $header = '<a href="'.$projectadrress.'"><p><b>Project Name : '.$projectname.'</b></p></a><p><b>Creator : '.$result["creator"].'</b></p><p><b>Signer : '.$result["signer"].'</b></p><p><b>Milestone : '.$milestone[$milestonenumber-1].'</b></p><p><b>Modified : '.$result["modified"].'</b></p>';
    $header = $header.'<p><b>Document Name : '.$result["documentname"].'</b></p><p><b>Document Description : '.$result["description"].'</b></p><p><b>Original Document Hash : '.$result["originalhash"].'</b></p><a href="'.$fileurl.'" target="_blank" class="btn btn-default btn-sm"><b>Download Document</b></a></p>';
    
    $signingmenu = "";
    //construct signing menu
    if ($result["signature"]) {
        $signedurl = $result["signedfile"]->getURL();
        //file has been signed. display info
        $signingmenu = '<p><b>Signed Hash : '.$result["signedhash"].'</b></p><p><b>Signature : '.$result["signature"].'</b></p><p><b>Signed Time  : '.$result["signedtime"].' WIB</b></p>';
        $signingmenu = $signingmenu. '<a href="'.$signedurl.'" target="_blank" class="btn btn-default btn-sm"><b>Download Signed Document</b></a>  <button class="btn btn-default btn-sm docverify-btn" type="submit"><b>Verify</b></button>';
    } else {
        //if project already finished. hide sign button
        $signingmenu = "Not yet signed";
        if ((!$finishproject) && ($result["signer"] == $idnumber)) {
            //create sign button            
            $signingmenu = $signingmenu. '<br><button class="btn btn-primary docsign-btn" type="submit">Sign</button>';
        }
    }
    
    $display=array(
		'pagetitle' => 'Project List - MobileID Web',
	    'heading' => 'Document Detail ',
	    'username' => $username,
	    'idnumber' => $idnumber,
	    'headingcontent' => $header,
	    'signingcontent' => $signingmenu,
	    'projectname' => $projectname,
	    'projectnumber' => $projectnumber,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
		'projectname' => $projectname,
		'documentnumber' => $documentnumber,
		'signerid' => $result["signer"],
		'fileurl' => $fileurl,
		'filehash' => $result["originalhash"],
		'documentname' => $result["documentname"],
		'description' => $result["description"],
		'signedhash' => $result["signedhash"],
		'signedtime' => $result["signedtime"],
		'signature' => $result["signature"],
		'callback' => $Webdocumentreceive,
	);
	
	echo $twig->render('documentdetail.html',$display);
    
});

$app->post('/document/create', function () use($app,$twig) {
    global $Webaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $projectname = $_POST["projectname"];
    $projectnumber = $_POST["projectnumber"];
    $currentmilestone = $_POST["currentmilestone"];
    $milestonenumber = $_POST["milestonenumber"];
    
    $controller = new WebController($idnumber);
    
	$display=array(
		'pagetitle' => 'Project List - MobileID Web',
	    'heading' => 'Create New Document',
	    'idnumber' => $idnumber,
	    'username' => $username,
	    'projectname' => $projectname,
	    'projectnumber' => $projectnumber,
	    'currentmilestone' => $currentmilestone,
	    'milestonenumber' => $milestonenumber,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
	);
	
	echo $twig->render('newdocument.html',$display);
});

$app->post('/document/process', function () use($app,$twig) {
    global $Webaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $target_dir = "temp/";
    $filename = basename( $_FILES["uploadFile"]["name"]);
    $target_dir = $target_dir . $filename;
    $uploadOk=1;
    
    if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $target_dir)) {
        $message = "The file ". $filename. " has been uploaded.";
    } else {
        $message = "Sorry, there was an error uploading your file.";
    }
    
    $filehash = fileHash($target_dir);
    
    $file = file_get_contents($target_dir, true);
    
    $projectnumber = $_POST["projectnumber"];
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);    
    
    $_POST["project_objectid"] = $project->getObjectId();
    $_POST["creator"] = $idnumber;
    $_POST["filehash"] = $filehash;
    $_POST["signer"] = $controller->setIDNumberbyRole($project, $_POST["signer"]);
    
    $controller = new WebDocument;
    $file = $controller->uploadFile($file, $filename);
    $result = $controller->createDocument($_POST);
    
    if ($result) {
        //echo "Document created!";
        $app->flash('info', 'Document created.');
    } else {
        //echo "Failed to save document!";
        $app->flash('error', 'Failed to save document.');
    }
	$app->redirect('/project/'.$projectnumber);
});

$app->post('/document/receive', function () use($app) {
    //sample query : {"signedtime":"2015-03-28 07:15:29","signedhash":"1a1f05a1f6cf823fab41f69c0a00f64a64d5874d08265b9ffd5624092b5534a9","signature":"lftadtwwszA9DN7s6VDXOzHRPRowUV6AFRH4mWeKY//GPQ1mDulI1Wesrf4AzniN53W7+mwehjAF4gXLTV5MG68xUwoVKFKN2fK90kLSsnBmxPAJ1nxRVMmizZV3MbYZOLYyHj6IvIpaO00b7PgThTsqCncIH7hnHIdSpEx2ugp6Y3dcpAjqR9h/bRGO+btvRSsDsnuBMbajmRcKoUCGuj0S3G7QZGLmx6ifHLFqfl9Rzm+7wtfskKUbG93UqXM0DdnuiswMtwtQ4/LfHoOCLZezF5R4uVjd2sj/aga9J74W2zFhv5GMGba7Tmc6rC8ilVoUJsk2dQDkV0x/PAjcffMXz+Hiil9B/utM54hsZAa9nfVk3nhX30rkgRjUimdrwquRtfJrZUQitvO27WYx4TPie8wQMHj92l+XyLgPmC8sf7EiVKWBf13JTUA5eCOGZA9/txVb8ItTAn65vMokARzjEJqhEdihRFTfu+zjUErznMAzJD+Qk3wHTLM/PTbXI8lI6aOb+d7H6FSU+rca5/WbuyRkMIxcwgb1X1r79Zk7vD3QLykGV0v52ogxuRwa2CFRgX1Dt/eivollcQyAEYuxcX2evaPhlUkHMtLOKgyMPoi2rwdFU/+4DdzMimHlQ3dzgAYvOc2/XIYkX9DBSztaBgOF1LdFg4fIjm1XkBM=","documentnumber":"28007529dd4734671c2060cbcb7c2b85"}
    
    $error = 1;
    
    $body = json_decode($_POST["content"], true);
    
    $documentnumber = $body["documentnumber"];

    $documentcontroller = new WebDocument();
    $document = $documentcontroller->fetchDocumentDB($documentnumber);
    
    if ($document) {
        $error = 0;
    } else {
        echo "invalid document number";
    }
    
    if ($error == 0) {
        //upload file to parse
        $uploaddir = realpath('./') . '/temp/';
        
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0755, true);
        }
        
        $filename = basename($_FILES['file_contents']['name']);
        $uploadfile = $uploaddir . $filename;
    
        if (move_uploaded_file($_FILES['file_contents']['tmp_name'], $uploadfile)) {   
            //echo "File Upload success";
            $error = 0;
        } else {
        	//echo "Possible file upload attack!";
        }
    }
    
    if ($error == 0) {
        //upload signed document to parase
        $file = file_get_contents($uploadfile, true);
        $fileurl = $documentcontroller->uploadFile($file, $filename);
        //update database
        $result = $documentcontroller->saveSignedDocument($body, $document);
    }
});

$app->get('/document/remove/:documentnumber', function ($documentnumber) use($app) {
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
    $document = $documentcontroller->fetchDocumentDB($documentnumber);
    
    $result = $documentcontroller->parseDocument($document);
    $project = $documentcontroller->getProject($result["project"]);
    $projectnumber = $project->get('projectnumber');
    
    if ($document) {
        $creator = $document->get('creator');
        $signature = $document->get('signature');

        if (($idnumber == $creator) && (!$signature)) {
            $document->destroy();
            $app->flash('info', 'Document deleted.');
        } else {
            $app->flash('error', 'Document must be removed by creator.');
            //only creator can delete document
        }
    } else {
        $app->flash('error', 'Document is not exist.');
    }
    $app->redirect('/project/'.$projectnumber);
});

$app->get('/document/comment/:documentnumber', function ($documentnumber) use ($twig) {
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
    $document = $documentcontroller->fetchDocumentDB($documentnumber);
    $result = $documentcontroller->parseDocument($document);    
    $documentname = $result["documentname"];

	$display=array(
		'pagetitle' => 'Project List - MobileID Web',
	    'heading' => 'Add Comment',
	    'idnumber' => $idnumber,
	    'username' => $username,
	    'documentnumber' => $documentnumber,
	    'documentname' => $documentname,
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
	);
	
	echo $twig->render('newcomment.html',$display);
});

$app->post('/document/comment/process', function () use($app,$twig) {
    global $Webaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    //comment : always, file : optional
    $controller = new WebComment;
    
    if ($_FILES["uploadFile"]["tmp_name"] != "") {
        //process uploaded file
        $target_dir = "temp/";
        $filename = basename( $_FILES["uploadFile"]["name"]);
        $target_dir = $target_dir . $filename;
        $uploadOk=1;
        
        if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $target_dir)) {
            $message = "The file ". $filename. " has been uploaded.";
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
        $file = file_get_contents($target_dir, true);
        $fileurl = $controller->uploadFile($file, $filename);
        var_dump($fileurl);
    }
    
    //save to database
    $form["documentnumber"] = $_POST["documentnumber"];
    $form["comment"] = $_POST["comment"];
    $form["poster"] = $idnumber;
    
    $result = $controller->createComment($form);
    
    //get project number
    $documentcontroller = new WebDocument();
    $document = $documentcontroller->fetchDocumentDB($_POST["documentnumber"]);
    $result = $documentcontroller->parseDocument($document);
    $project = $documentcontroller->getProject($result["project"]);
    $projectnumber = $project->get('projectnumber');
	$app->redirect('/project/'.$projectnumber);
});