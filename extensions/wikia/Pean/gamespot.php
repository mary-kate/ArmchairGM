<?php

$wgExtensionFunctions[] = 'wfSpecialGamespotTest';

function wfSpecialGamespotTest(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class GamespotTest extends SpecialPage {

	const NO_OF_STORIES = 5;
	const CACHE_PERIOD  = 324000; // 90 mins.
	
	function GamespotTest(){
		UnlistedSpecialPage::UnlistedSpecialPage("GamespotTest");
	}
	
	function execute(){
		global $wgRequest, $IP, $wgOut, $wgUser, $wgMemc;
		
		
		global $wgMemc, $wgPartnerWikiName;
		
		$output = "";
		$output .= '<a href="http://www.gamespot.com"><img style="float:left;" src="http://images.wikia.com/common/skins/quartz/gamespot/images/gamespot_logo_box.gif"/></a>&nbsp; updates';
		
		//HARDCODE IN GAMESPOT PARTNER NAME
		$wgPartnerWikiName = "halo";
		$key  =  wfMemcKey('widget:gamespot:feed', $wgPartnerWikiName);
		$data = $wgMemc->get($key);
		$wgMemc->delete($key);
		wfDebug(sprintf("Gamespot widget: from cache: %s\n", print_r($data, true)));
		
		if (empty($data)){
			global $wgPartnerWikiData;
			
			//HARD CODE IN GAMESPOT FEED
			$wgPartnerWikiData["feed"] = "http://feeds.gamespot.com/feeds/wikia.php?hud=wikia&name=halo";
			$wgPartnerWikiData['feed-more'] = "http://www.gamespot.com/xbox360/action/halo3/news.html?mode=all&om_act=convert&om_clk=gsupdates&tag=updates;all";
		
			if (!empty($wgPartnerWikiData['feed'])){
				
				$url = $wgPartnerWikiData['feed'];
				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_HEADER         => false,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL            => $url,
				));
				$data = curl_exec($ch);
				wfDebug(sprintf("Gamespot Widget: gs feed: %s\n", $data));

				$data = $this->parseGamespotFeed($data);
				wfDebug(sprintf("Gamespot Widget: parsed feed: %s\n", print_r($data, true)));
			}

			$wgMemc->add($key, $data, self::CACHE_PERIOD);
		}
		$output .= "<ul>";
		
		if (!count($data)){
			$output .= "no updates available";
		}else{
			$i = 0;
			foreach ($data as $row){
				$output .= "<li>";
				$output .= "<a href=\"" . htmlspecialchars($row['gs_story_link']) . "\" title=\"" . htmlspecialchars($row['headline']) . "\">" . htmlspecialchars($row['headline']) . "</a>";
				$output .= "<br />";
				
				if (0 == $i){
					$output .= htmlspecialchars($row['deck']);
					$output .= "<div class=\"widgetGamespotDate\">";
					$output .= wfTimestamp(TS_RFC2822, $row['post_date']);
					$output .= "</div>";
				}
				$i++;
				$output .= "</li>";
			}
		
		}
		$output .= "</ul>";
		$more = $wgPartnerWikiData['feed-more'];
		if (!empty($more)){
			$output .= "<a href=\"{$more}\">See more GameSpot Updates &raquo;</a>";
		}
		
		$wgOut->addHTML($output);
		

		
	}
	
	static public function parseGamespotFeed($feed)
	{
		wfProfileIn(__METHOD__);

		$allowed_tags = array('headline', 'deck', 'gs_story_link', 'post_date', 'story_id');

		$data = array();
		if (preg_match_all('/<story>(.+)<\/story>/sU', $feed, $preg, PREG_SET_ORDER))
		{
			foreach ($preg as $match)
			{
				$row = array();
				if (preg_match_all('/<([^\/][^>]+)>([^<]+)<\/([^>]+)>/sU', $match[1], $preg2, PREG_SET_ORDER))
				{
					foreach ($preg2 as $match2)
					{
						if (($match2[1] == $match2[3]) && in_array($match2[1], $allowed_tags))
						{
							$row[$match2[1]] = str_replace(array('&lt;', '&gt;', '&amp;'), array('<', '>', '&'), $match2[2]);
						}
					}
				}

				$data[] = $row;
			}
		}

		$data = array_slice($data, 0, self::NO_OF_STORIES);

		wfProfileOut(__METHOD__);
		return $data;
	}
  
}

SpecialPage::addPage( new GamespotTest );

}

?>