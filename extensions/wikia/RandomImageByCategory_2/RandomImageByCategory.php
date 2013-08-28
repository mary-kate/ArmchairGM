<?php
$wgExtensionFunctions[] = "wfRandomImageByCategory";

function wfRandomImageByCategory() {
    global $wgParser, $wgOut;
    $wgParser->setHook( "randomimagebycategory", "GetRandomImage" );
}
function GetRandomImage( $input, $args, &$parser ){

	$width = "150";
	$random_img_array = array (
	"<a href=\"http://fp016.sjc.wikia-inc.com/wiki/Image:Lordhoodhalo.JPG\"><img src=\"http://images2.wikia.nocookie.net/halo/images/thumb/7/7c/Lordhoodhalo.JPG/800px-Lordhoodhalo.JPG\" width='{$width}' alt='Featured Image' /></a>",
		"<a href=\"http://fp016.sjc.wikia-inc.com/wiki/Image:600px-Mongoose.jpg\"><img src=\"http://images2.wikia.nocookie.net/halo/images/c/cf/600px-Mongoose.jpg\" width='{$width}' alt='Featured Image' /></a>",
		"<a href=\"http://fp016.sjc.wikia-inc.com/wiki/Image:Clip_image004.jpg\"><img src=\"http://images2.wikia.nocookie.net/halo/images/a/aa/Clip_image004.jpg\" width='{$width}' alt='Featured Image' /></a>",
		"<a href=\"http://fp016.sjc.wikia-inc.com/wiki/Image:Control_Room_04_hall.jpg\"><img src=\"http://images2.wikia.nocookie.net/halo/images/0/0c/Control_Room_04_hall.jpg\" width='{$width}' alt='Featured Image' /></a>",
		"<a href=\"http://fp016.sjc.wikia-inc.com/wiki/Image:Halo1UltraElite20-22.jpg\"><img src=\"http://images2.wikia.nocookie.net/halo/images/6/60/Halo1UltraElite20-22.jpg\" width='{$width}' alt='Featured Image' /></a>",
		"<a href=\"http://fp016.sjc.wikia-inc.com/wiki/Image:One_Spartan_and_Three_Sangheilis.jpg\"><img src=\"http://images2.wikia.nocookie.net/halo/images/thumb/1/15/One_Spartan_and_Three_Sangheilis.jpg/800px-One_Spartan_and_Three_Sangheilis.jpg\" width='{$width}' alt='Featured Image' /></a>"
		);
		
	return $random_img_array[array_rand($random_img_array, 1)];
}

/*
function GetRandomImage( $input, $args, &$parser ){
	global $wgUser, $wgParser, $wgTitle, $wgOut, $wgMemc;

	$parser->disableCache();
	 
	$categories = trim($args["categories"]);
	$limit = $args["limit"];
	$width = $args["width"];
	
	if( !is_numeric($width) ) $width = 200;
	if( !is_numeric($limit) ) $limit = 10;
	
	$key = wfMemcKey( 'image', 'random', $limit, str_replace(" ","",$categories)  );
	$data = $wgMemc->get( $key );
	$image_list = array();
	if( !$data ){
		wfDebug( "Getting ramdom image list from db\n" );
		$p = new Parser();
		$ctg = $p->transformMsg( $categories, $wgOut->parserOptions() );
		$ctg = str_replace("\,","#comma#",$ctg);
		$aCat = explode(",", $ctg);
			
		foreach($aCat as $sCat){
			if($sCat!=""){
				$category_match[] = Title::newFromText(  trim( str_replace("#comma#",",",$sCat) )   )->getDBKey();	
			}
		}
		
		if( count( $category_match ) == 0 ) return "";
		
		$params['ORDER BY'] = 'page_id';
		if($limit)$params['LIMIT'] = $limit;
		
		$dbr =& wfGetDB( DB_MASTER );
		$res = $dbr->select( '`page` INNER JOIN `categorylinks` on cl_from=page_id', 'page_title', 
			array( 'cl_to' => $category_match, 'page_namespace' => NS_IMAGE ), __METHOD__, 
			$params
		);
		$image_list = array();
		while ( $row = $dbr->fetchObject($res) ) {
			$image_list[] = $row->page_title;
			
		}
		$wgMemc->set( $key, $image_list, 60 * 15 );
	}else{
		$image_list = $data;
		wfDebug( "Cache hit for ramdom image list\n" );
	}
	
	if( count($image_list) > 1)$random_image = $image_list[ array_rand( $image_list, 1) ];
	if( $random_image ) {
		$image_title = Title::makeTitle(NS_IMAGE, $random_image );
		$render_image = Image::newFromName( $random_image );
	
		$thumb_image = $render_image->getThumbNail( $width, 0, true );
		$thumbnail = "<a href=\"{$image_title->escapeFullURL()}\">{$thumb_image->toHtml()}</a>";
	}
	return $thumbnail;
	
}
*/
?>