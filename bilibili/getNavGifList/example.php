<meta charset="utf-8">
<pre>
<?php



require "../BilibiliCrawler.class.php";
$bilibiliCrawler = new BilibiliCrawler();



$result = $bilibiliCrawler->downloadNavGif();





//file_put_contents('BilibiliNavIcon/ddd.gif', file_get_contents('http://i2.hdslb.com/icon/63fa935082a99224a253eda60ef01961.gif'));

print_r($result);




/*$result = $bilibiliCrawler->getTopicList($_GET["from"], $_GET["to"]);


foreach($result as $value)
{
	echo '<li><a href="' .$value[3]. '" title = "' . $value[2] . '" target="_blank" >第' . $value[0] . '期 ' . $value[1] . '</a></li>';
}*/




?>
</pre>