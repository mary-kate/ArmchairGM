<?php

/**
 * autoload classes
 */
global $wgAutoloadClasses, $IP;
$wgAutoloadClasses["WikiaApiQuery"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQuery.php";
$wgAutoloadClasses["WikiaApiQueryConfGroups"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryConfGroups.php";
$wgAutoloadClasses["WikiaApiQueryDomains"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryDomains.php";
#$wgAutoloadClasses["WikiaQuickForm"] = "extensions/wikia/WikiaQuickForm/WikiaQuickForm.php";
#$wgAutoloadClasses["WikiaWebStats"]  = "extensions/wikia/WikiaStats/WikiaWebStats.php";
$wgAutoloadClasses["WikiaApiQueryPopularPages"]  = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryPopularPages.php";
$wgAutoloadClasses["WikiaApiFormatTemplate"]  = "$IP/extensions/wikia/WikiaApi/WikiaApiFormatTemplate.php";
$wgAutoloadClasses["WikiaApiQueryVoteArticle"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryVoteArticle.php";
$wgAutoloadClasses["WikiaApiQueryWrite"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryWrite.php";
$wgAutoloadClasses["WikiaApiQueryMostAccessPages"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryMostAccessPages.php";
#$wgAutoloadClasses["WikiSerializer"] = "extensions/wikia/WikiSerializer/WikiSerializer.php";
#$wgAutoloadClasses["EasyTemplate"]  = "extensions/wikia/WikiaQuickForm/EasyTemplate.php";
$wgAutoloadClasses["WikiaApiQueryLastEditPages"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryLastEditPages.php";
$wgAutoloadClasses["WikiaApiQueryTopEditUsers"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryTopEditUsers.php";
$wgAutoloadClasses["WikiaApiQueryMostVisitedPages"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryMostVisitedPages.php";
$wgAutoloadClasses["WikiaApiQueryReferers"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryReferers.php";
$wgAutoloadClasses["WikiaApiQueryBestArticles"] = "$IP/extensions/wikia/WikiaApi/WikiaApiQueryBestArticles.php";
#$wgAutoloadClasses["ApiRecentChangesCombined"] = "extensions/wikia/RecentChangesCombined/ApiRecentChangesCombined.php";
$wgAutoloadClasses["ApiFeaturedContent"] = "$IP/extensions/wikia/FeaturedContent/ApiFeaturedContent.php";
$wgAutoloadClasses["ApiPartnerWikiConfig"] = "$IP/extensions/wikia/FeaturedContent/ApiPartnerWikiConfig.php";
#$wgAutoloadClasses["WikiaApiAjaxLogin"] = "extensions/wikia/WikiaApi/WikiaApiAjaxLogin.php";


/**
 * registered API methods
 */
global $wgApiQueryListModules;
$wgApiQueryListModules["wkconfgroups"] = "WikiaApiQueryConfGroups";
$wgApiQueryListModules["wkdomains"] = "WikiaApiQueryDomains";
$wgApiQueryListModules["wkpoppages"] = "WikiaApiQueryPopularPages";
$wgApiQueryListModules["wkvoteart"] = "WikiaApiQueryVoteArticle";
$wgApiQueryListModules["wkaccessart"] = "WikiaApiQueryMostAccessPages";
$wgApiQueryListModules["wkbestpages"] = "WikiaApiQueryBestArticles";
$wgApiQueryListModules["wkeditpage"] = "WikiaApiQueryLastEditPages";
$wgApiQueryListModules["wkedituser"] = "WikiaApiQueryTopEditUsers";
$wgApiQueryListModules["wkmostvisit"] = "WikiaApiQueryMostVisitedPages";
$wgApiQueryListModules["wkreferer"] = "WikiaApiQueryReferers";

/**
 * registered Format names
 */
global $wgApiMainListFormats;
$wgApiMainListFormats["wktemplate"] = "WikiaApiFormatTemplate";

/*
 * reqistered API modules
 */
global $wgApiMainListModules;
$wgApiMainListModules["insert"] = "WikiaApiQueryWrite";
$wgApiMainListModules["update"] = "WikiaApiQueryWrite";
$wgApiMainListModules["delete"] = "WikiaApiQueryWrite";
#$wgApiMainListModules["recentchangescombined"] = "ApiRecentChangesCombined";
$wgApiMainListModules["featuredcontent"] = "ApiFeaturedContent";
$wgApiMainListModules["partnerwikiconfig"] = "ApiPartnerWikiConfig";
#$wgApiMainListModules["ajaxlogin"] = "WikiaApiAjaxLogin";


?>
