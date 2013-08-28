<?php




$wgExtensionFunctions[] = 'wfMLBmedia';


function wfMLBmedia() {

	global $wgUser,$IP;
	include_once("includes/SpecialPage.php");

	class MLBMedia extends SpecialPage {
	
		function MLBMedia(){
			SpecialPage::SpecialPage("MLBMedia");
		}
		
		function xml_to_array($xml) {
			$fils = 0;
			$tab = false;
			$array = array();
			
			foreach($xml->children() as $key => $value) {
				$child = self::xml_to_array($value);
	
				//To deal with the attributes
				//foreach ($node->attributes() as $ak => $av) {
				//	$child[$ak] = (string)$av;
				//}
				//Let see if the new child is not in the array
				if ($tab == false && in_array($key, array_keys($array))) {
					//If this element is already in the array we will create an indexed array
					$tmp = $array[$key];
					$array[$key] = NULL;
					$array[$key][] = $tmp;
					$array[$key][] = $child;
					$tab = true;
				} elseif($tab == true) {
					//Add an element in an existing array
					$array[$key][] = $child;
				} else {
					//Add a simple element
					$array[$key] = $child;
				}
				$fils++;
			}
	
			if($fils==0) {
				return (string)$xml;
			}
			return $array;
		}
		
		function execute(){
			set_time_limit(0);
		        global $IP, $wgRequest, $wgStyleVersion, $wgOut, $wgUser,$wgYoutubeAPIKey;
			$dbr =& wfGetDB( DB_MASTER );
			$title = Title::makeTitle(NS_MAIN,"Mlb youtube");
			$article_text .= "\n";
			$article_text .= "==Players with Youtube search results==\n\n";
			
			$sql = "select distinct player_name as player
				from
				 mlb_players_info 
				LEFT JOIN mlb_fielding_stats on mlb_players_info.player_id = mlb_fielding_stats.player_id 
				WHERE mlb_fielding_stats.year > 1980  order by lastname  ";
			$res = $dbr->query($sql);	
			while ( $row = $dbr->fetchObject( $res ) ) {
				$players[] = $row->player;
			}
			$dbr->freeResult($res);
			$article = new Article($title);
			$article_text = $article->getContent();
			//$article->doEdit( "", "Mlb youtube");
			$x = 1;
			foreach($players as $player){
				if($x > 2157){
					echo $player . '<br>';
				$player_search = urlencode("\"".$player."\"");
				$api_call = "http://gdata.youtube.com/feeds/videos?start-index=1&max-results=50&vq={$player_search}";
				$api_return = file_get_contents( $api_call );
				 
				$videos = array();
				if($api_return!=null){
					$xml = new SimpleXMLElement($api_return);
					$videos = $this->xml_to_array($xml) ;
				}
				$found = count($videos["entry"]);
				if($found>0){
					if($found==50){
						$number = '50+';
					}else{
						$number = $found;
					}
					$article_text .= "$x. [[$player]] ($number found)\n\n";
				}
				$article->doEdit( $article_text, "Mlb youtube");
				}
				$x++;
			}
			
			//$wgOut->addHTML("<a href=\"index.php?title={$title->getText()}\">(link)</a>");
		}
		
		
	}
	SpecialPage::addPage( new MLBMedia );
}


