<?php

if (!defined('MEDIAWIKI')) die();

$wgExtensionFunctions[] = 'wfYouTube';
$wgExtensionCredits['parserhook'][] = array
(
	'name'        => 'YouTube',
	//'version'     => '0.1',
	'author'      => 'Przemek Piotrowski <ppiotr@wikia.com>',
	'url'         => 'http://www.wikia.com/wiki/Help:YouTube',
	'description' => 'embeds YouTube movie (eg. &lt;youtube ytid="OdT9z-JjtJk"/&gt;)',
);

function wfYouTube() 
{
	global $wgParser;

	$wgParser->setHook('youtube', 'embedYouTube');
}

/*

TODO:
- user-defined attrs (array)

http://video.google.pl/videoplay?docid=8191780737516712253
<embed style="width:400px; height:326px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=8191780737516712253&hl=pl" flashvars=""> </embed>
<embed style="width:400px; height:326px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=5682610600698523988&hl=en-AU" flashvars=""> </embed>

*/

function embedYouTube_url2ytid($url)
{
	$id =& $url;

	if (preg_match('/^http:\/\/www\.youtube\.com\/watch\?v=(.+)$/', $url, $preg))
	{
		$id = $preg[1];
	} elseif (preg_match('/^http:\/\/www\.youtube\.com\/v\/([^&]+)(&autoplay=[0-1])?$/', $url, $preg))
	{
		$id = $preg[1];
	}

	preg_match('/([0-9A-Za-z_-]+)/', $id, $preg);
	$id = $preg[1];

	return $id;
}

function embedYouTube($input, $argv, &$parser)
{
	//$parser->disableCache();

	$ytid   = '';
	$width  = $width_max  = 425;
	$height = $height_max = 350;

	if (!empty($argv['ytid']))
	{
		$ytid = embedYouTube_url2ytid($argv['ytid']);
	} elseif (!empty($input))
	{
		$ytid = embedYouTube_url2ytid($input);
	}
	if (!empty($argv['width']) && ($width_max >= $argv['width']))
	{
		$width = $argv['width'];
	}
	if (!empty($argv['height']) && ($height_max >= $argv['height']))
	{
		$height = $argv['height'];
	}

	if (!empty($ytid))
	{
		$url = "http://www.youtube.com/v/{$ytid}";
		return "<object type=\"application/x-shockwave-flash\" data=\"{$url}\" width=\"{$width}\" height=\"{$height}\"><param name=\"movie\" value=\"{$url}\"/></object>";
	}
}

?>
