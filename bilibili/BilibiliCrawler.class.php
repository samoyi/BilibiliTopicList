
<?php
// http://www.cnblogs.com/phperbar/archive/2011/07/29/2120660.html
// http://www.jb51.net/article/43095.htm


class BilibiliCrawler
{

	// 公共函数 ----------------------------------------------------
	protected function curl_get($url, $gzip=false)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		if($gzip) curl_setopt($curl, CURLOPT_ENCODING, "gzip"); // 关键在这里
		$content = curl_exec($curl);
		curl_close($curl);
		return $content;
	}


	protected function streamCrawling($url)
	{
		// Create a stream
		$opts = array(
			'http'=>array(
			'method'=>"GET",
			'header'=>"Accept-language: en\r\n" .
			"Cookie: foo=bar\r\n"
			)
		);

		$context = stream_context_create($opts);

		// Open the file using the HTTP headers set above
		$file = file_get_contents($url, false, $context);
		return $file;
	}





	// 数据 ----------------------------------------------------
	protected $sFirstTopicUrl = "http://www.bilibili.com/topic/1.html";


	public function getTopicList()
	{

		$result = $this->curl_get($this->sFirstTopicUrl, true);
		$array = array();
		preg_match("/<title>[\s\S]*?itle>/", $result, $array);
		return $array;
	}
}




?>

   