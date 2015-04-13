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

	if (isset($_SESSION['slim.flash']['error'])) {
		$alert=array('alert' => $_SESSION['slim.flash']['error']);
		$display = array_merge($display, $alert);
	}

	if (isset($_SESSION['slim.flash']['info'])) {
		$alert=array('info' => $_SESSION['slim.flash']['info']);
		$display = array_merge($display, $alert);
	}
	
	echo $twig->render('projectlist.html',$display);
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
	$idnumber = $_POST["userid"];
	$controller = new WebController($idnumber);
	$controller->getInitial($idnumber);
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
	
	$controller = new WebController($idnumber);
	$result = $controller->createProject($form);

	if ($result) {
		echo "Project created";
	} else {
		echo "Cannot save to database";
	}
});

$app->get('/project/:projectnumber', function ($projectnumber) use ($twig) {
	header('Access-Control-Allow-Origin: *');  
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
    $project = $controller->unparsedProject($projectnumber);
	$result = $controller->parseProject($project);
	$role = $controller->checkRole($result, $idnumber);
	
	$roletext ="";
	switch ($role) {
		case 1:
			$roletext = "as Creator";
			break;
		case 2:
			$roletext = "as Client";
			break;
	}
	
    $approval = $controller->getApproval($result);
    
    $header = '<p><b>Project Name : '.$result["projectname"].'</b></p><p><b>Creator : '.$result["creator"].'</b></p><p><b>Client : '.$result["client"].'</b></p><p><b>Current Milestone : '.$result["currentmilestone"].'</b></p><p><b>Modified : '.$result["modified"].'</b></p>';
    
    if ($approval[0]) {
        // show project structure
        //search document
        $documentstructure = $controller->getDocumentsfromProject($project);
        
        $milestonedropdown ='';
        $milestonenumber = $result["milestonenumber"];
        $milestone = $result["milestone"];
        for ($i = $milestonenumber-1; $i >= 0; $i--) {
            if (!isset($documentstructure[$i])) {
                $documentstructure[$i] = "<b>No Document Created</b>";
            }
            $milestonedropdown = 	$milestonedropdown.
                                    '<div class="panel panel-default">
                                        <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$i.'">'.$milestone[$i].'</a></h4>
                                        </div>
                                        <div id="collapse'.$i.'" class="panel-collapse">
                                        <div class="panel-body">
                                          <table class="table">
                                            <thead>
                                              <tr>
                                                <th>#</th>
                                                <th>Document Name</th>
                                                <th>Download</th>
                                                <th>Signature</th>
                                              </tr>
                                            </thead>
                                            <tbody>'.
                                            $documentstructure[$i].
                                            '</tbody>
                                            </table>
                                            </div>
                                        </div>
                                    </div>';
        }
        
        $display=array(
            'pagetitle' => 'Project List - MobileID Web',
            'heading' => 'Project Detail '. $roletext,
            'username' => $username,
            'idnumber' => $idnumber,
            'headingcontent' => $header,
            'projectname' => $result["projectname"],
            'projectnumber' => $projectnumber,
            'currentmilestone' => $result["currentmilestone"],
            'milestonenumber' => $milestonenumber,
            'license' => 'Mobile ID Web Application',
            'milestonedropdown' => $milestonedropdown,
            'year' => '2015',
            'author' => 'Bramanto Leksono',
        );

        echo $twig->render('projectdetail.html',$display);       
        
    } else {
        // show approval menu
        $form = '';
        for ($user = 0; $user <= 1; $user++) {
            if ($user == 0) {
                $approvaloffset = 1;
                $identityoffset = 3;
                $form[$user] =     '<td>Creator</td>
                                    <td>'.$result["creator"].'</td>
                                    <td><button id="clickMe" class="btn btn-default" type="button" value="'. $result["creator"].'" onclick="';
            } else if ($user == 1) {
                $approvaloffset = 2;
                $identityoffset = 4;
                $form[$user] =     '<td>Client</td>
                                    <td>'.$result["client"].'</td>
                                    <td><button id="clickMe" class="btn btn-default" type="button" value="'. $result["client"].'" onclick="';
            }
            
            //identity viewer
            if ($approval[$identityoffset]) {
                $form[$user] = $form[$user]. 'viewidentity(this);">View</button></td>';
            } else {
                $form[$user] = $form[$user]. 'sendidentityreq(this);">Resend Request</button></td>';
            }
            
            //approval status
            if ($approval[$approvaloffset]) {
                $form[$user] = $form[$user]. '<td>Approved</td>';
            } else {
                $form[$user] = $form[$user]. '<td>Empty Approval</td>';
            }
        }
        
        $showapprovalbutton = true;
        //check if user already approved
        switch ($role) {
            case 1:
                if ($approval[1]) {
                    $showapprovalbutton = false;
                }
                break;
            case 2:
                if ($approval[2]) {
                    $showapprovalbutton = false;
                }
                break;
        }
        
        $display=array(
            'pagetitle' => 'Project List - MobileID Web',
            'heading' => 'Project Detail '. $roletext,
            'username' => $username,
            'idnumber' => $idnumber,
            'headingcontent' => $header,
            'projectname' => $result["projectname"],
            'projectnumber' => $projectnumber,
            'creatorcontent' => $form[0],
            'clientcontent' => $form[1],
            'approval' => $showapprovalbutton,
            'currentmilestone' => $result["currentmilestone"],
            'milestonenumber' => $milestonenumber,
            'license' => 'Mobile ID Web Application',
            'year' => '2015',
            'author' => 'Bramanto Leksono',
        );
        
        echo $twig->render('projectapproval.html',$display);           
    }
});

$app->post('/project/next', function () use($app) {
    /*
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        $app->redirect('/');
        die();
    }
    */
    
	$idnumber = "1231230509890001";
    $username = "Bramanto Leksono";
    
    $projectnumber = $_POST["projectnumber"];
	$controller = new WebController($idnumber);
	$project = $controller->nextMilestone($projectnumber);
	$app->redirect('/project');
});

$app->post('/project/confirm', function () use($app) {
    /*
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        $app->redirect('/');
        die();
    }
    */
    
	$idnumber = "1231230509890001";
    $username = "Bramanto Leksono";
    
    $projectnumber = $_POST["projectnumber"];
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);
	$result = $controller->parseProject($project);
	$role = $controller->checkRole($result, $idnumber);
	
	$roletext ="";
	switch ($role) {
		case 1:
			$roletext = "creatoridentity";
			break;
		case 2:
			$roletext = "clientidentity";
			break;
	}
    
    $value = "1";
    $project->set($roletext, $value);
    $project->save();
    
	$app->redirect('/project/'.$projectnumber);
});