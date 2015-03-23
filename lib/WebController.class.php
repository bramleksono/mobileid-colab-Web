<?php

require 'WebProject.class.php';  // Handling User Project Database

class WebController {
	private function getProjectNumber($time,$idnumber) {
        $string = $time.$idnumber;
    	return hash('sha1', $string);
    }
	
    public function WebController($idnumber) {
        $this->idnumber = $idnumber;
    }
    
    public function createProject($form) {
        $project = new WebProject();
        $time = $project->getTime();
        $idnumber = $this->idnumber;
        
        $projectnumber = $this->getProjectNumber($time,$idnumber);
        $form["projectnumber"] = $projectnumber;
        
        $milestone = json_decode($form["milestone"], true);
		$firstmilestone = $milestone[0];
        
        return $project->storeProjectDB($form,$firstmilestone);
    }
    
    public function showProject() {
        $idnumber = $this->idnumber;
        $project = new WebProject();
        
        //get list as creator
        $creatorcount = $project->fetchCreatorDB($idnumber);
        $creatortext = '';
        if ($creatorcount > 0) {
            $creatorlist = $project->listCreatorProject();
            
            $i = 1;
            
            foreach ($creatorlist as $list) {
                $projectname = $list->get('projectname');
                $client = $list->get('client');
                $currentmilestone = $list->get('currentmilestone');
                $modified = $list->get('modified');
                $projectnumber = $list->get('projectnumber');
                $creatortext = $creatortext.'<tr class="primary"><td>'.$i.'</td><td>'.$client.'</td><td><a href="project/'.$projectnumber.'">'.$projectname.'</td><td>'.$currentmilestone.'</td><td>'.$modified.'</td><td>';
                $i++;
            }
        }
        
        //get list as client
        $clientcount = $project->fetchClientDB($idnumber);
        $clienttext = '';
        if ($clientcount > 0) {
            $clientlist = $project->listClientProject();
            
            $i = 1;
            foreach ($clientlist as $list) {
                $projectname = $list->get('projectname');
                $creator = $list->get('creator');
                $currentmilestone = $list->get('currentmilestone');
                $modified = $list->get('modified');
                
                $clienttext = $clienttext.'<tr class="primary"><td>'.$i.'</td><td>'.$creator.'</td><td><a href="">'.$projectname.'</td><td>'.$currentmilestone.'</td><td>'.$modified.'</td><td>';
                $i++;
            }
        }
        
        $data = array(  "creatortext" => $creatortext,
                        "clienttext" =>$clienttext);
        return $data;
    }
}