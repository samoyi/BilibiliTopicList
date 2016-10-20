

<meta charset="utf-8">
<pre>
<?php
// http://www.cnblogs.com/phperbar/archive/2011/07/29/2120660.html
// http://www.jb51.net/article/43095.htm
$url = "http://www.bilibili.com/topic/1575.html";


require "BilibiliCrawler.class.php";
$bilibiliCrawler = new BilibiliCrawler();

var_dump( $bilibiliCrawler->getTopicList() );




?>
</pre>