<?php

require 'lib/tcpdf_min/tcpdf.php';

$app->get('/report/:projectnumber', function ($projectnumber) use ($twig,$app) {
    global $Webaddr;
    global $CAaddr;
    global $SIaddr;
    
    global $Webprojectconfirm;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $error = 0;
    
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);
    if (!isset($project)) {
        //project is not exist
        $error = 1;
    }
    
    if ($error == 0) {
        //check user role
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
    		default:
    		    $error = 2;
    		    break;
    	}
    }
    
    if ($error == 0) {
        //check if project status as finished
        if (!$result["finishproject"]) {
            $error = 3;
        }
    }
    
    if ($error == 0) {
        //get all party identity
        $identity = "";
        
        $creatoridentity = json_decode($project->get("creatoridentity"), true);
        $clientidentity = json_decode($project->get("clientidentity"), true);
        
        //parse identity
        $creatoridentity = parseidentitytotext($creatoridentity);
        $clientidentity = parseidentitytotext($clientidentity);
        
    
    }
    
    if ($error == 0) {
        //search document
    	$documents = new WebDocument();
    	$documentlist = $documents->findDocumentsbyproject($project);
    	
    	$documentstructure = array();
    	foreach ($documentlist as $document) {
    	    $documentnumber =  $document->get('documentnumber');
    		$documentname =  $document->get('documentname');
    		$documentcreator =  $document->get('creator');
    		$documentsigner =  $document->get('signer');
    		$currentmilestone = $document->get('milestone')-1;
    		$originalfile =  $document->get('originalfile');
    		$signedfile =  $document->get('signedfile');
    		$originalhash =  $document->get('originalhash');
    		$signedhash =  $document->get('signedhash');
    		$createdtime =  $document->get('modified');
    		$signedtime =  $document->get('signedtime');
    		$signature =  $document->get('signature');
    		
    		//define array if doesnt exist
    		if (!isset($documentstructure[$currentmilestone])) {
    			$documentstructure[$currentmilestone] = "";
    		}
    		
    		$documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. 
            "Document Name : ".$documentname."\r\n".
            "Document Number : ".$documentnumber."\r\n".
            "Creator : ".$documentcreator."\r\n".
            "Signer : ".$documentsigner."\r\n".
            "Original File URL : ".$originalfile->getURL()."\r\n".
            "Original File Hash : ".$originalhash."\r\n".
            "Created at : ".$createdtime." WIB\r\n";
            if ($signature) {
                $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. 
                "Status : Signed\r\n".
                "Signed File URL : ".$signedfile->getURL()."\r\n".
                "Signed File Hash : ".$signedhash."\r\n".
                "Signature : ".$signature."\r\n".
                "Signed at : ".$signedtime." WIB\r\n";
            } else {
                $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. 
                "Status : Not Signed\r\n";
            }
            
            //search comment
        	$comments = new WebComment();
        	$commentlist = $comments->findCommentsbydocument($documentnumber);
        	
            if ($commentlist != null) {
                //echo "there is comment";
                $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. 
                "Document ".$documentnumber." Comment : \r\n";
                
                foreach ($commentlist as $comment) {
                    $poster = $comment->get('poster');
                    $modified = $comment->get('modified');
                    $commenttext = $comment->get('comment');
                    $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. "By ".$poster." : ".$commenttext." (at time ".$modified." WIB)\r\n";
                    $file = $comment->get('file');
                    if ($file != "") {
                        $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. "Comment file URL : ".$file->getURL()."\r\n";
                    }
                }
            }
            
            $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. "\r\n";
    	}
    }
    
	switch ($error) {
	    case 0:
	        //create report
    	    $current_date = new DateTime("now");
        	$time = $current_date->format('Y-m-d H:i:s');
            
            //begin of report
            $content = "Disclaimer\r\n";
            $content = $content. "----------------\r\n";
            $content = $content. "This is a report of finished project.\r\n";
            $content = $content. "This document contain all information which can be used as a proof.\r\n";
            
            $content = $content. "\r\nServer Information\r\n";
            $content = $content. "----------------\r\n";
            $content = $content. "Web Address : ".$Webaddr."\r\n";
            $content = $content. "CA Address : ".$CAaddr."\r\n";
            $content = $content. "SI Address : ".$SIaddr."\r\n";
            
            $content = $content. "\r\nUser Information\r\n";
            $content = $content. "----------------\r\n";
            $content = $content. "Creator\r\n";
            $content = $content. $creatoridentity;
            $content = $content. "\r\nClient\r\n";
            $content = $content. $clientidentity;
            
            $content = $content. "\r\n\r\nProject Information\r\n";
            $content = $content. "----------------\r\n";
            $content = $content. "Project Name : ".$result["projectname"]."\r\n";
            $content = $content. "Project Number : ".$result["projectnumber"]."\r\n";
            $content = $content. "Creator : ".$result["creator"]."\r\n";
            $content = $content. "Client : ".$result["client"]."\r\n";
            $content = $content. "Created at : ".$result["modified"]." WIB\r\n";
            $content = $content. "Ended at : ".$result["ended"]." WIB\r\n\r\n";
            
            $milestonelist = $result["milestone"];
            
            $milestonenumber = 0;
            foreach($milestonelist as $milestone) {
                $content = $content. "Milestone Name : ".$milestone."\r\n";
                $content = $content. "----------------\r\n";
                if (isset($documentstructure[$milestonenumber])) {
                    $content = $content. $documentstructure[$milestonenumber];
                } else {
                    $content = $content. "Empty Milestone\r\n\r\n";
                }
                
                $milestonenumber++;
            }
            
            $content = $content. "This report is generated in ".$time." WIB as user ".$idnumber.".";
            
            $content = nl2br($content);

        	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        	
        	// set default monospaced font
        	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        	
        	// set margins
        	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        	
        	// set auto page breaks
        	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        	
        	// ---------------------------------------------------------
        	
        	// set font
        	$pdf->SetFont('helvetica', '', 12);
        	
        	// add a page
        	$pdf->AddPage();
        	
        	// print a line of text
        	$pdf->writeHTML($content, true, 0, true, 0);
            
            $pdf->lastPage();
            
        	$pdf->Output("project report ".$time.".pdf", 'D');
            
            echo $content;
            
	        break;
	    case 1:
	        echo "Error : Project is not exist!";
	        die();
	        break;
	    case 2:
	        echo "Error : You are not involved in this project";
	        die();
	        break;
	    case 3:
	        echo "Error : Project is not finished";
	        die();
	        break;
	}

});

function parseidentitytotext($identity) {
    $identity =  "NIK : ". $identity["nik"] . "\r\n".
             "Nama : ". $identity["nama"] . "\r\n".
             "Tempat/Tgl Lahir : ". $identity["ttl"] . "\r\n".
             "Jenis Kelamin : ". $identity["jeniskelamin"] . "\r\n".
             "Gol Darah : ". $identity["goldarah"] . "\r\n".
             "Alamat : ". $identity["alamat"] . "\r\n".
             "RT/RW : ". $identity["rtrw"] . "\r\n".
             "Kel/Desa : ". $identity["keldesa"] . "\r\n".
             "Kecamatan : ". $identity["kecamatan"] . "\r\n".
             "Agama : ". $identity["agama"] . "\r\n".
             "Status Perkawinan : ". $identity["statperkawinan"] . "\r\n".
             "Pekerjaan : ". $identity["pekerjaan"] . "\r\n".
             "Kewarganegaraan : ". $identity["kewarganegaraan"] . "\r\n".
             "Berlaku Hingga : ". $identity["berlaku"] . "\r\n";   
             
    return $identity;
}