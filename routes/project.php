<?php

$app->get('/project', function () use($app,$twig) {
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

	if ($result[0]) {
	    $app->flash('info', 'Begin verification process (Mulai tahap verifikasi).');
	    
	    //send message to phone
        $idnumberlist = array($clientid);
        $messagecontroller = new WebMessage();
        $messagetext = "You are now added to ".$projectname." project by user ".$idnumber.".";
        $messagecontroller->sendmessageto($idnumberlist, $messagetext);
	    
	    $projectnumber = $result[1];
	    
	    //save to record
	    $record = new WebRecord();
	    $record->recordproject($idnumber, $projectname, $projectnumber,"create", "");

	    $app->redirect('/project/'.$projectnumber);
	} else {
	    $errormessage = 'Cannot save to database.';
	    $app->flash('error', $errormessage);
	    $app->redirect('/project');
	    
	    //save to record
	    $record = new WebRecord();
	    $record->recordproject($idnumber, $projectname, $projectnumber,"failed", $errormessage);
	}
});

$app->get('/project/:projectnumber', function ($projectnumber) use ($twig,$app) {
    global $Webaddr;
    global $Webprojectconfirm;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);
    if (!isset($project)) {
        //project is not exist
        $app->redirect('/project');
    }
    
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
    
    $iscreator = false;
    if ($result["creator"] == $idnumber) {
        $iscreator = true;
    }
    
    // check if both party already approve project
    if ($approval[0]) {
        // show project structure

        $documentstructure = $controller->getDocumentsfromProject($project, $idnumber, $result["finishproject"]);
        
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
                                        <div class="panel-body">'.
                                            $documentstructure[$i].
                                        '</div>
                                        </div>
                                    </div>';
        }
        
        global $Webprojectreport, $Webprojectlog;
        $reporturl = $Webprojectreport."/".$projectnumber;
        $logturl = $Webprojectlog."/".$projectnumber;
           
        $display=array(
            'pagetitle' => 'Project List - MobileID Web',
            'heading' => 'Project Detail '. $roletext,
            'username' => $username,
            'idnumber' => $idnumber,
            'headingcontent' => $header,
            'projectname' => $result["projectname"],
            'projectnumber' => $projectnumber,
            'iscreator' => $iscreator,
            'currentmilestone' => $result["currentmilestone"],
            'milestonenumber' => $milestonenumber,
            'license' => 'Mobile ID Web Application',
            'milestonedropdown' => $milestonedropdown,
            'finished' => $result["finishproject"],
            'reporturl' => $reporturl,
            'logurl' => $logturl,
            'year' => '2015',
            'author' => 'Bramanto Leksono',
        );

        if (isset($_SESSION['slim.flash']['info'])) {
    		$info=array('info' => $_SESSION['slim.flash']['info']);
    		$display = array_merge($display, $info);
    	}
    	
    	if (isset($_SESSION['slim.flash']['error'])) {
    		$info=array('alert' => $_SESSION['slim.flash']['error']);
    		$display = array_merge($display, $info);
    	}

        echo $twig->render('projectdetail.html',$display);       
        
    } else {
        // show approval menu
        $form = '';
        for ($user = 0; $user <= 1; $user++) {
            
            //only show button for themself
            if ($user == 0) {
                $approvaloffset = 1;
                $identityoffset = 3;
                
                $creatoridentitybutton = '<button id="clickMe" class="btn btn-default" type="button" value="'. $result["creator"].'" onclick="';
                if ($approval[$identityoffset]) {
                    $creatoridentitybutton = $creatoridentitybutton. 'viewidentity(this);">View</button>';
                } else {
                    $creatoridentitybutton = $creatoridentitybutton. 'sendidentityreq(this);">Send Request</button></td>';
                }
                
                $form[$user] =     '<td>Creator</td>
                                    <td>'.$result["creator"].'</td>
                                    <td>';
                //show identity request button to self, show view identity for all
                if (($iscreator) || ($approval[$identityoffset])) {
                    $form[$user] = $form[$user] . $creatoridentitybutton;
                }
                
            } else if ($user == 1) {
                $approvaloffset = 2;
                $identityoffset = 4;
                
                $clientidentitybutton = '<button id="clickMe" class="btn btn-default" type="button" value="'. $result["client"].'" onclick="';
                if ($approval[$identityoffset]) {
                    $clientidentitybutton = $clientidentitybutton. 'viewidentity(this);">View</button>';
                } else {
                    $clientidentitybutton = $clientidentitybutton. 'sendidentityreq(this);">Send Request</button></td>';
                }
                $form[$user] =     '<td>Client</td>
                                    <td>'.$result["client"].'</td>
                                    <td>';                    
                //show identity request button to self, show view identity for all
                if ((!$iscreator) || ($approval[$identityoffset])) {
                    $form[$user] = $form[$user] . $clientidentitybutton;
                }
                
            }
            
            $form[$user] = $form[$user] . '</td>';
            
            //approval status
            if ($approval[$approvaloffset]) {
                $form[$user] = $form[$user]. '<td>Approved</td>';
            } else {
                $form[$user] = $form[$user]. '<td>Empty Approval</td>';
            }
        }
        
        $showapprovalbutton = true;
        //hide button condition : user already send approval or never send identity
        switch ($role) {
            case 1:
                if (($approval[1]) || (!$approval[3])) {
                    $showapprovalbutton = false;
                }
                break;
            case 2:
                if (($approval[2]) || (!$approval[4])) {
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
            'iscreator' => $iscreator,
            'creatorcontent' => $form[0],
            'clientcontent' => $form[1],
            'approval' => $showapprovalbutton,
            'Webprojectconfirmaddr' => $Webprojectconfirm,
            'currentmilestone' => $result["currentmilestone"],
            'license' => 'Mobile ID Web Application',
            'year' => '2015',
            'author' => 'Bramanto Leksono',
        );
        
        if (isset($_SESSION['slim.flash']['info'])) {
    		$info=array('info' => $_SESSION['slim.flash']['info']);
    		$display = array_merge($display, $info);
    	}
    	
    	if (isset($_SESSION['slim.flash']['error'])) {
    		$info=array('alert' => $_SESSION['slim.flash']['error']);
    		$display = array_merge($display, $info);
    	}
	
        echo $twig->render('projectapproval.html',$display);           
    }
});

$app->post('/project/next', function () use($app) {
    global $Webaddr;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $projectnumber = $_POST["projectnumber"];
    $milestonename = $_POST["milestonename"];
	$controller = new WebController($idnumber);
	$project = $controller->nextMilestone($projectnumber, $milestonename);
	
	//show message
	$app->flash('info', 'Milestone '.$milestonename.' created.');
	
	//save to record
	$record = new WebRecord();
	$record->recordmilestone($idnumber, $milestonename, $projectnumber, "next", "");
});

$app->post('/project/confirm', function () use($app) {
    global $Webaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $projectnumber = $_POST["projectnumber"];
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);
	$result = $controller->parseProject($project);
	$role = $controller->checkRole($result, $idnumber);
	
	$roletext ="";
	switch ($role) {
		case 1:
			$roletext = "creatorapproval";
			break;
		case 2:
			$roletext = "clientapproval";
			break;
	}
    
    $value = "1";
    $project->set($roletext, $value);
    $project->save();
    
    //save to record
	$record = new WebRecord();
	$record->recordproject($idnumber, $result["projectname"], $result["projectnumber"],"confirm", "");
    
    //reload project
    $project = $controller->unparsedProject($projectnumber);
	$result = $controller->parseProject($project);
    
    //check if both party has sent confirmation
    if (($result["creatorapproval"] == "1") && ($result["clientapproval"] == "1")) {
        //send message to phone
        $idnumberlist = array($result["creator"], $result["client"]);
        $messagecontroller = new WebMessage();
        $messagetext = "Project ".$result["projectname"]." begin.";
        $messagecontroller->sendmessageto($idnumberlist, $messagetext);
        
        //save to record
		$record = new WebRecord();
		$record->recordproject($idnumber, $result["projectname"], $result["projectnumber"],"start", "");
		
		$app->flash('info', 'Project begin (Memulai proyek).');
    } else {
        $app->flash('info', 'Wait for other person approval (Tunggu approval pihak lain).');
    }
    
    $app->redirect('/project/'.$result['projectnumber']);
});

$app->post('/project/milestone/delete', function () use($app) {
    global $Webaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $projectnumber = $_POST["projectnumber"];
	$controller = new WebController($idnumber);
	$project = $controller->unparsedProject($projectnumber);
	$result = $controller->parseProject($project);
	$role = $controller->checkRole($result, $idnumber);
    
    if ($role == 1) {
        $deleteresult = $controller->deleteMilestone($projectnumber);
        
        switch ($deleteresult[0]) {
            case 0:
                $app->flash('error', 'Cannot delete milestone. Document exist in milestone.');
                //echo  'Cannot delete. Document exist in milestone.';
                break;
            case 1:
                $app->flash('info', 'Milestone deleted.');
                //echo 'Milestone '.$result[1].' deleted.';
                
                //save to record
    		    $record = new WebRecord();
    		    $record->recordmilestone($idnumber, $deleteresult[1], $projectnumber, "delete");
    		    
                break;
            case 2:
                $errormessage = 'Cannot delete milestone. This is the 1st milestone.';
                $app->flash('error', $errormessage);
                //echo 'Cannot delete. This is the 1st milestone.';
                
                //save to record
    		    $record = new WebRecord();
    		    $record->recordmilestone($idnumber, $deleteresult[1], $projectnumber, "failed", $errormessage);
                break;
        }

    } else {
        $app->flash('error', 'You are not project creator');
    }
});

$app->post('/project/finish', function () use($app) {
    global $Webaddr;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $projectnumber = $_POST["projectnumber"];
	$controller = new WebController($idnumber);
	
    $project = $controller->unparsedProject($projectnumber);
	$result = $controller->parseProject($project);
	$role = $controller->checkRole($result, $idnumber);
	
    if ($role == 1) {
        $project = $controller->finishProject($projectnumber);
        $app->flash('info', 'Project ended');
            
    	//send message to phone
        $idnumberlist = array($result["creator"], $result["client"]);
        $messagecontroller = new WebMessage();
        $messagetext = "Project ".$result["projectname"]." has ended.";
        $messagecontroller->sendmessageto($idnumberlist, $messagetext);
    
    	//save to record
    	$record = new WebRecord();
    	$record->recordproject($idnumber, $result["projectname"], $projectnumber,"finish", "");

    } else {
        $errormessage = 'You are not project creator';
        $app->flash('error', $errormessage);
        
        //save to record
    	$record = new WebRecord();
    	$record->recordproject($idnumber, $result["projectname"], $projectnumber,"failed", $errormessage);
    }
});

$app->post('/project/delete', function () use($app) {
    global $Webaddr;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
 
    $projectnumber = $_POST["projectnumber"];
	$controller = new WebController($idnumber);
	
    $project = $controller->unparsedProject($projectnumber);
	$result = $controller->parseProject($project);
	$role = $controller->checkRole($result, $idnumber);
    
    if ($role == 1) {
        $deleteresult = $controller->deleteProject($projectnumber);
        
        switch ($deleteresult) {
            case 0:
                $app->flash('error', 'Cannot delete project. Document exist in project.');
                //echo  'Cannot delete. Document exist in milestone.';
                break;
            case 1:
                $app->flash('info', 'Project deleted');
                
                //send message to phone
                $idnumberlist = array($result["creator"], $result["client"]);
                $messagecontroller = new WebMessage();
                $messagetext = "Project ".$result["projectname"]." deleted.";
                $messagecontroller->sendmessageto($idnumberlist, $messagetext);
                
                //save to record
            	$record = new WebRecord();
            	$record->recordproject($idnumber, $result["projectname"], $projectnumber,"delete", "");
                break;
        }
        
    } else {
        $errormessage = 'You are not project creator';
        $app->flash('error', $errormessage);
        
        //save to record
    	$record = new WebRecord();
    	$record->recordproject($idnumber, $result["projectname"], $projectnumber,"failed", $errormessage);
    }
});