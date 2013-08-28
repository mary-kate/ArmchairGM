<?php
$wgSitesRSS = array(
		"ArmchairGM" => array(
			"display-name" => "ArmchairGM",
			"description" => "all sports. all you",
			"url" => "http://www.armchairgm.com",
			"logo" => "armchairfooter.png",
			"rss-url" => "http://feeds.feedburner.com/Armchairgm"
			),

		"Games" => array(
			"display-name" => "gaming.wikia.com",
			"description" => "",
			"url" => "http://gaming.wikia.com",
			"logo" => "gamingfooter.png",
			"rss-url" => "http://feeds.feedburner.com/gaming-wikia"
			),

		"politics" => array(
			"display-name" => "politics.wikia.com",
			"description" => "",
			"url" => "http://politics.wikia.com",
			"logo" => "politicsfooter.png",
			"rss-url" => "http://feeds.feedburner.com/politics-wikia"
			),

		"music" => array(
			"display-name" => "tunes.wikia.com",
			"description" => "",
			"url" => "http://tunes.wikia.com",
			"logo" => "tunesfooter.png",
			"rss-url" => "http://feeds.feedburner.com/tunes-wikia"
			),

		"health" => array(
			"display-name" => "health.wikia.com",
			"description" => "",
			"url" => "http://health.wikia.com",
			"logo" => "healthfooter.png",
			"rss-url" => "http://feeds.feedburner.com/health-wikia"
			),

		"Cars" => array(
			"display-name" => "cars.wikia.com",
			"description" => "",
			"url" => "http://cars.wikia.com",
			"logo" => "carsfooter.png",
			"rss-url" => "http://feeds.feedburner.com/cars-wikia"
			),

		"local" => array(
			"display-name" => "local.wikia.com",
			"description" => "",
			"url" => "http://local.wikia.com",
			"logo" => "localfooter.png",
			"rss-url" => "http://feeds.feedburner.com/local-wikia"
			),

		"Entertainment" => array(
			"display-name" => "entertainment.wikia.com",
			"description" => "",
			"url" => "http://entertainment.wikia.com",
			"logo" => "entertainmentfooter.png",
			"rss-url" => "http://feeds.feedburner.com/entertainment-wikia"
			)
			);


function rsspagefooter() {
	global $wgSitesRSS, $wgRSSFooter;
	
	if($wgRSSFooter){ // check if RSS Footer is enabled for this site
		$out = '<div class="rssfooter">';
		$out .= 'like what you read? check out some other <b>Wikia</b> content. its cool.';
		
		$rssCount = 0;
		foreach($wgRSSFooter as $footerItem){
			$site = $footerItem["site"];

			if($rssCount%2==0)$out .= '<div style="clear:both;"></div>'; //start new row
			
			$out .= '<div class="rssunit">';
			$out .= '<p align="center"><a href="' . $wgSitesRSS[$site]["url"] . '"><img src="extensions/Footer/images/' . $wgSitesRSS[$site]["logo"] . '" border="0" alt="' . $wgSitesRSS[$site]["display-name"] . '"/></a></p>';
			$rss = 	$wgSitesRSS[$site]["rss-url"] . "|short|notitle|max=";
			if($footerItem["max"]){
				$rss .= $footerItem["max"];
			}else{
				$rss .= "5";
			}

			$feed_item = renderRss($rss);
			$out .= $feed_item;
			$out .= '<div class="rsssubscribe"><a href="' . $wgSitesRSS[$site]["rss-url"] . '"><img src="extensions/Footer/images/rssicon.png" border="0" alt="rss-icon"/></a> subscribe with <a href="http://fusion.google.com/add?feedurl='.$wgSitesRSS[$site]["rss-url"] .'">Google</a>, 
				<a href="http://add.my.yahoo.com/rss?url=' . $wgSitesRSS[$site]["rss-url"] . '">MyYahoo</a>, or <a href="http://www.bloglines.com/sub/' . $wgSitesRSS[$site]["rss-url"] . '">Bloglines</a></div>';
			$out .= '</div>'; #end rssunit
			
			$rssCount++;
		}
		$out .= '<div style="clear:both;"></div>';
		$out .= '</div>'; #end rssfooter 
	}
	return $out;
}

?>
