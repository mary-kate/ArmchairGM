<?php

class YoutubeImport{
	
	private $api_url = "http://gdata.youtube.com/feeds/videos";

	
	var $results_per_page = 10;
	var $results_per_row = 5;


	
	function __construct() {
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
	
	public function getVideos( $page=1, $q  ){
		global $wgUser, $wgOut;
		$q = urlencode($q);
		$start_index = 1;
        	if($page>1)$start_index = $page * $this->results_per_page - ($this->results_per_page);
	
		$api_call = $this->api_url . "?start-index={$start_index}&max-results=" . ($this->results_per_page + 1 ) . "&vq={$q}";
	
		$api_return = file_get_contents( $api_call );
		 
		if($api_return){
			$xml = new SimpleXMLElement($api_return);
			$videos = $this->xml_to_array($xml) ;
		}
		if ($videos == null || !is_array($videos) || sizeof($videos) == 0   ) {
			$output = wfMsg("videoimport_novideosfound",$q);
			return $output;
		}
	 
		$output = "<div id=\"youtube-videos\" class=\"youtube-images\">";
		
		$x = 1;

        	foreach ($videos["entry"] as $video) {
			if($x <= $this->results_per_page){
				 
				$id = str_replace("http://gdata.youtube.com/feeds/videos/","",$video["id"]);
				
				
				$output .= "<div class=\"".(($x==$this->results_per_page)?"youtube-video-container no-border":"youtube-video-container")."\">
						<div class=\"youtube-checkbox\">
							<p><input onclick=toggle_video('{$id}') type=\"checkbox\" name=\"youtube_video_{$id}\"  value=\"{$id}\"  />" . wfMsg('videoimport_importthis')  . "</p>
						</div>
						<div class=\"youtube-video\">
							<object width=\"200px\" height=\"167px\">
							<param name=\"movie\" value=\"http://www.youtube.com/v/{$id}\" ></param>
							<embed src=\"http://www.youtube.com/v/{$id}\" type=\"application/x-shockwave-flash\" width=\"200px\" height=\"167px\">
								</embed>
							</object>
						</div>
						<div class=\"youtube-video-info\">
							<p><b>{$video["title"][0]}</b></p>
							<p><b>" . wfMsgForContent("videoimport_owner") . "</b>: <a href=\"http://www.youtube.com/user/{$video["author"][0]["name"]}\">{$video["author"][0]["name"]}</a></p>
							<p><b> " . wfMsgForContent("videoimport_date") . "</b>: {$video["published"]}</p>
						 </div>
						 <div class=\"cleared\"></div>
					</div>";
				
			}
			$x++;
		}
		$output .= "</div>";
		
		$output .= "<div class=\"video-import-navigation\">";
		
		if($page!=1){
			$output .= "<a href=\"javascript:get_results_page(" . ($page-1) . ",'" . ($q) . "')\">".wfMsgForContent( 'videoimport_previouspage')."</a>";
		}
			
		//We purposely sent $this->results_per_page+1 to the search via the API
		//If the results returned are greater than the results per page, we know there is a next page
		if( count($videos["entry"]) > $this->results_per_page){
			$output .= " <a href=\"javascript:get_results_page(" . ($page+1) . ",'" . ($q) . "')\">".wfMsgForContent( 'videoimport_nextpage')."</a>";
		
		}
		
		$output .= "</div>";
		
		return $output;
	}

	public function importVideo( $id, $search_term ,$categories){
		global $wgOut, $wgRequest, $IP;

		require_once( "$IP/extensions/wikia/Video/VideoClass.php" );
	
		
		if(!$search_term)$search_term="youtube-";
		$page_title = urldecode("{$search_term}-" . time() . "-" . rand(0, 999) );
		$page_title = str_replace("?", "", $page_title);
		$page_title = str_replace(":", "", $page_title);
		$page_title = preg_replace('/ [ ]*/', ' ', $page_title);
		$page_title = str_replace("\"", "", $page_title);
		$search_term = str_replace("\"", "", $search_term);
		$video = Video::newFromName($page_title);
		$video->addVideo("http://youtube.com/watch?v=".$id,"youtube",$search_term . "|" . $categories);
		return $page_title;

	}
	

}
?>


