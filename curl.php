<?php

function send_post($uri, $data, $headers)
{
	$process = curl_init();
	curl_setopt($process, CURLOPT_HEADER, 0);
	curl_setopt($process, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)");
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($process, CURLOPT_TIMEOUT, 20);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($process, CURLOPT_POST, true);
	curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($process, CURLOPT_POSTFIELDS, $data);
	curl_setopt($process, CURLOPT_URL, $uri);

	$out = curl_exec($process); 
	curl_close($process);
	return $out;
}

function send_post_headers($uri, $data, $headers, $cookie_file_path=null)
{
	$process = curl_init();

	// if ($cookie_file_path != null) {
	// 	curl_setopt($process, CURLOPT_COOKIEJAR, $cookie_file_path);
	// 	curl_setopt($process, CURLOPT_COOKIEFILE, $cookie_file_path);
	// }
	
	curl_setopt($process, CURLOPT_HEADER, 1);
	curl_setopt($process, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)");
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($process, CURLOPT_TIMEOUT, 20);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($process, CURLOPT_POST, true);
	curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($process, CURLOPT_POSTFIELDS, $data);
	curl_setopt($process, CURLOPT_URL, $uri);

	$out = curl_exec($process); 
	$headers = curl_getinfo($process);

	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $out, $matches);
	$cookies = array();
	foreach($matches[1] as $item) {
	    parse_str($item, $cookie);
	    $cookies = array_merge($cookies, $cookie);
	}

	curl_close($process);
	return [
		"body" => $out,
		"headers" => $headers,
		"cookie" => $cookies
	];
}

?>