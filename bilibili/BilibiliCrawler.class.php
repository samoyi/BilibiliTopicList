
<?php
// http://www.cnblogs.com/phperbar/archive/2011/07/29/2120660.html
// http://www.jb51.net/article/43095.htm


class BilibiliCrawler
{

	// 公共数据 ----------------------------------------------------

	// 网页标题中共有的部分，位于有效标题之后
	protected $sCommonPartInTitle = ' - 哔哩哔哩弹幕视频网 - \( ゜- ゜\)つロ  乾杯~  - bilibili';

	// 导航栏右侧的动图数据文件
	protected $sNavGifInfo = 'http://www.bilibili.com/index/index-icon.json';




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

	// 获取网页有效标题 （实际标题删掉后面的$sCommonPartInTitle部分）
	protected function getTitle( $url )
	{
		$content = $this->curl_get($url, true);
		if( preg_match("/<title>(.*)$this->sCommonPartInTitle<\/title>/", $content, $aMatches) )
		{
			// TODO 为什么aMatches[0]是空的
			return $aMatches[1];	
		}
	}

	// 获取网页描述
	protected function getDescription( $url )
	{
		$content = $this->curl_get($url, true);
		if( preg_match("/description\"\s+content=\"(.+)\"/", $content, $aMatches) )
		{
			return $aMatches[1];	
		}
		
	}



	// 具体功能 ----------------------------------------------------


	// 获取所有话题的链接、标题和描述
	/*	
	 *  话题文件的命名方式是按照数字从0.html到最新。
	 *  哔哩哔哩的话题分为两种：第一种是综合话题；第二种是版块话题，例如频道精选和周末放映室
	 *  版块话题和综合话题使用相同序号系统命名文件，但版块话题会在html文件至上多一层 v2 路径 
	 *  综合话题url如 http://www.bilibili.com/topic/1582.html
	 *  版块话题url如 http://www.bilibili.com/topic/v2/1583.html
	 *  这里不作区分，会查询这两种路径
	 */
	/*
	 *  还没有发布的文件不会匹配到，但中间也存在某些不存在的文件，同样无法匹配
	 *  所以不能判断哪一个是最新的一期，需要传入最新一期的序号nLatestTopicIndex，从最新的开始降序搜索
	 *  nLatestTopicIndex也可以传入较小的其他期号，则只从该期向前降序搜索
	 *  第二个参数指示搜索终点期，默认是第一期
	 */
	/*
	 *  返回一个数组
	 *  每个数组项是一个topic的信息，格式同样为数组。四项分别为：当期序号、当前标题、当期描述、当期链接
	 *  并不是每一期都有描述
	 */
	/*
	 *  应如example所示的分次请求分次显示。如果一次查询过多将会导致超过脚本执行最长时限
	 */
	public function getTopicList($nLatestTopicIndex, $nEndTopicIndex=1)
	{
		
		$aTopicInfoList = array(); // 最终返回的数组
		for($i=$nLatestTopicIndex; $i>$nEndTopicIndex-1; $i--) // 循环所给区间
		{
			$url = 'http://www.bilibili.com/topic/' . $i . '.html';
			$urlv2 = 'http://www.bilibili.com/topic/v2/' . $i . '.html';
			$result = array($nLatestTopicIndex, "", "", $url); // 每个主题的信息
			if( $title = $this->getTitle($url) )
			{
				$result[0] = $i;
				$result[1] = $title;	
			}
			elseif( $title = $this->getTitle($urlv2) )
			{
				$result[0] = $i;
				$result[1] = $title;	
				$result[3] = $urlv2;
			}
			else // 该主题不存在，直接跳出
			{
				continue;
			}

			if( $des = $this->getDescription($url) ) // 如果有描述则写入描述
			{
				$result[2] = $des;
			}
			$aTopicInfoList[] = $result; // 档条主题信息写入结果数组中的最新项
		}
		return $aTopicInfoList;
	}





	// 下载所有导航栏右边的动图
	/*
	 *	所有动图的信息都保存在 sNavGifInfo 文件中
	 *  通过将title的字符编码从utf-8改成gbk，试图将所有文件按照其在 NavGifInfo 的 title属性命名
	 *  中文和假名都可以作为文件名，但目前发现某些符号会转换失败从而保存失败，例如 (..•˘_˘•..) TODO
	 *  保存失败的存储在返回的关联数组中，键为title，值为对应的url。需要手动下载这些图片并保存
	 */
	protected function getNavGifList()// 获取所有导航栏右边的动图
	{				
		$jsopObj = json_decode( $this->curl_get($this->sNavGifInfo, true) );
		$aFix = $jsopObj->fix;
		$aGif = array();


		foreach($aFix as $aFixItem)
		{
			$url = $aFixItem->icon;
			$name = $aFixItem->title;
			$aGif[$name] = $url;	
		}
		return $aGif;
	}
	public function downloadNavGif() // 实际下载
	{				
		$aGif = $this->getNavGifList();
		$aDownloadFail = array();

		if( !is_dir('BilibiliNavIcon/') ) 
		{
			mkdir('BilibiliNavIcon/');
		}
		foreach($aGif as $name=>$url)
		{
			if( @$gbkname=iconv("utf-8","gbk",$name) ) // TODO 原理
			{
				file_put_contents('BilibiliNavIcon/' . $gbkname . '.gif', file_get_contents($url));	
			}
			else
			{
				$aDownloadFail[$name] = $url;
			}
		}
		return $aDownloadFail;
	}
}
?>