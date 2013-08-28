<?php


$wgCustomVariables = array('LASTTHREEDAYS','VIEWMAINHEADING','VIEWCATEGORY');

$wgHooks['MagicWordMagicWords'][]          = 'wfAddCustomVariable';
$wgHooks['MagicWordwgVariableIDs'][]       = 'wfAddCustomVariableID';
$wgHooks['LanguageGetMagic'][]             = 'wfAddCustomVariableLang';
$wgHooks['ParserGetVariableValueSwitch'][] = 'wfGetCustomVariable';

function wfAddCustomVariable(&$magicWords) {
        foreach($GLOBALS['wgCustomVariables'] as $var) $magicWords[] = "MAG_$var";
        return true;
        }

function wfAddCustomVariableID(&$variables) {
        foreach($GLOBALS['wgCustomVariables'] as $var) $variables[] = constant("MAG_$var");
        return true;
        }

function wfAddCustomVariableLang(&$langMagic, $langCode = 0) {
        foreach($GLOBALS['wgCustomVariables'] as $var) {
                $magic = "MAG_$var";
                $langMagic[defined($magic) ? constant($magic) : $magic] = array(0,$var);
                }
        return true;
        }

function wfGetCustomVariable(&$this,&$cache,&$index,&$ret) {
	global $wgSiteView;
        switch ($index) {
                case MAG_VIEWCATEGORY:
                        if($wgSiteView->getOpenservingType() == 1){
				//$category = "Opinions by User {$wgSiteView->getOwnerUsername()}";
			} else{
				$category = "Opinions by {$wgSiteView->getDomainName()} Readers";
			}
			$ret =  $category;
                        break;
		case MAG_VIEWMAINHEADING:
			 if($wgSiteView->getOpenservingType() == 1){
				 $heading = "new articles";
			 } else{
				 $heading = "popular articles";
			 }
			 $ret = $heading;
			 break;
		 case MAG_LASTTHREEDAYS:
			  $dates[date("F j, Y", time() )] = 1; //gets today's date string
			  for($x=1;$x<=2;$x++){
				$time_ago = time() - (60 * 60 * 24 * $x);
				$date_string = date("F j, Y", $time_ago);
				$dates[$date_string] = 1;
			  }
			  $dates_array = $dates;
			  $date_categories = "";
			  foreach ($dates_array as $key => $value) {
				if($date_categories)$date_categories .=",";
				$date_categories .= str_replace(",","\,",$key);
			  }
			  $ret = $date_categories;
                        break;
                }
        return true;
        }

?>
