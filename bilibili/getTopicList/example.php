<?php



require "../BilibiliCrawler.class.php";
$bilibiliCrawler = new BilibiliCrawler();





$result = $bilibiliCrawler->getTopicList($_GET["from"], $_GET["to"]);


foreach($result as $value)
{
	echo '<li><a href="' .$value[3]. '" title = "' . $value[2] . '" target="_blank" >第' . $value[0] . '期 ' . $value[1] . '</a></li>';
}




?>