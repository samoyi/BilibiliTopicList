

<meta charset="utf-8">
<?php
// http://www.cnblogs.com/phperbar/archive/2011/07/29/2120660.html
// http://www.jb51.net/article/43095.htm
$url = "http://www.bilibili.com/topic/1575.html";


echo curl_get($url, true);

function curl_get($url, $gzip=false)
{
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	if($gzip) curl_setopt($curl, CURLOPT_ENCODING, "gzip"); // 关键在这里
	$content = curl_exec($curl);
	curl_close($curl);
	return $content;
}

?>

   