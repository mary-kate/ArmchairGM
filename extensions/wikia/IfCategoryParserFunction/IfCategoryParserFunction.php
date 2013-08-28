<?php
$wgExtensionFunctions[] = 'wfIfCategoryParserFunctionSetup';

$wgHooks['LanguageGetMagic'][]       = 'wfIfCategoryParserFunctionSetup_Magic';

function wfIfCategoryParserFunctionSetup() {
        global $wgParser;

        $wgParser->setFunctionHook( 'ifcategory', 'wfIfCategoryParserFunction' );
}

function wfIfCategoryParserFunctionSetup_Magic( &$magicWords, $langCode ) {

        $magicWords['ifcategory'] = array( 0, 'ifcategory' );
        return true;
}

function wfIfCategoryParserFunction( &$parser, $param1 = '', $param2 = ''  ) {
	 global $wgArticle;
	global $wgOut;
	echo 'test;'.$wgArticle->categories;
	exit();
        //print_r($wgOut->getCategoryLinks());
	foreach($wgOut->getCategoryLinks() as $ctg){
		$out.=strip_tags($ctg);
	}
	
        return "dave";
}

?>