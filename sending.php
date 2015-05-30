<?php

function sendpost($data,$url) {
	//curl less method
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);

	$context  = stream_context_create($options);
	return $result = @file_get_contents($url, false, $context);
}

$url = 'https://mobileid-colab-web-bramleksono.c9.io/verify/view';
$data = array('idnumber' => '1231230509890001', 'projectid' => 'c3d5d53f95317327e117a29086f3780c');

$result = sendpost($data, $url);
var_dump($result);