<?php

function sendjson($data,$url) {
	//curl less method
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/json\r\n",
			'method'  => 'POST',
			'content' => $data,
		),
	);

	$context  = stream_context_create($options);
	return $result = @file_get_contents($url, false, $context);
}

function sendfile($filepath,$content,$url) {
    //This needs to be the full path to the file you want to send.
	$file_name_with_full_path = realpath($filepath);
	$data['content'] = $content;
	$data['file_contents'] = '@'.$file_name_with_full_path;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result=curl_exec ($ch);
	curl_close ($ch);
	return $result;
}