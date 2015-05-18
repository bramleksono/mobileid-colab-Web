<?php

class WebMessage {
    private function constmessagetoCA($data) {
        $form = array(
    				'meta' => array(
    								'purpose' => 'sendmessage'),
    				'userinfo' => array(
    								'nik' => $data->userinfo->nik,
    								'message' => $data->userinfo->message)
                );
        return $form;    
    }
    
	public function sendmessageto($idnumberlist, $message) {
        global $CAmessaging;
        foreach ($idnumberlist as $idnumber) {
    		$reg = (object) array("userinfo" => (object) array("nik" => $idnumber, "message" => $message));
        	$reg = $this->constmessagetoCA($reg);
        	$reg = json_encode($reg);
        	sendjson($reg,$CAmessaging);
        }
	}
}