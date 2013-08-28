<?php
if (!defined('MEDIAWIKI')) die();
/**
 * HTML parsing, form parsing, and link extraction functionality
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Ando Saabas <ando@cs.ioc.ee> - original code/ideas
 * @author Peter Valicek <sonny2@gmx.de> - form parser
 * @author Tomasz Klim <tomek@wikia.com> - fixes, porting to PHP5, inheritance etc.
 * @copyright Copyright (C) 2007 Tomasz Klim, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['other'][] = array(
	'name' => 'WikiParser',
	'description' => 'html+form parsing and link extraction functionality',
	'author' => 'Tomasz Klim'
);


class WikiParser
{
    var $pattern_href = "/(href)\s*=\s*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?~=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?(\s*rel\s*=\s*[\'\"]?(nofollow)[\'\"]?)?/i";
    var $pattern_frame = "(frame[^>]*src[[:blank:]]*)=[[:blank:]]*[\'\"]?(([[a-z]{3,5}://(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?";
    var $pattern_location = "(window[.]location)[[:blank:]]*=[[:blank:]]*[\'\"]?(([[a-z]{3,5}://(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?";
    var $pattern_httpequiv = "(http-equiv=['\"]refresh['\"] *content=['\"][0-9]+;url)[[:blank:]]*=[[:blank:]]*[\'\"]?(([[a-z]{3,5}://(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?";
    var $pattern_windowopen = "(window[.]open[[:blank:]]*[(])[[:blank:]]*[\'\"]?(([[a-z]{3,5}://(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?";
    var $baseurl;
    var $filter = false;
    var $leave = false;
    var $strip = false;
    var $debug = false;
    var $index_host = false;
    var $index_meta = false;

    var $ext = array( "gif", "jpg", "jpeg", "bmp", "rtf", "ps", "arj", "zip", "gz", "tar", "exe", "tgz", "bz", "bz2",
		      "wav", "mp3", "au", "aiff", "bin", "mpg", "mpeg", "mov", "qt", "tif", "tiff", "tar", "z", "avi",
		      "ram", "ra", "arc", "gzip", "hqx", "sit", "sea", "uu", "png", "css", "ico", "cl", "jar", "rar" );

    var $apache_indexes = array( "N=A", "N=D", "M=A", "M=D", "S=A", "S=D", "D=A", "D=D", "C=N;O=A", "C=M;O=A",
				 "C=S;O=A", "C=D;O=A", "C=N;O=D", "C=M;O=D", "C=S;O=D", "C=D;O=D" );

    var $entities1 = array (
	"&amp;"    => "&",
	"&apos;"   => "'",
	"&THORN;"  => "\xde",
	"&szlig;"  => "\xdf",
	"&agrave;" => "\xe0",
	"&aacute;" => "\xe1",
	"&acirc;"  => "\xe2",
	"&atilde;" => "\xe3",
	"&auml;"   => "\xe4",
	"&aring;"  => "\xe5",
	"&aelig;"  => "\xe6",
	"&ccedil;" => "\xe7",
	"&egrave;" => "\xe8",
	"&eacute;" => "\xe9",
	"&ecirc;"  => "\xea",
	"&euml;"   => "\xeb",
	"&igrave;" => "\xec",
	"&iacute;" => "\xed",
	"&icirc;"  => "\xee",
	"&iuml;"   => "\xef",
	"&eth;"    => "\xf0",
	"&ntilde;" => "\xf1",
	"&ograve;" => "\xf2",
	"&oacute;" => "\xf3",
	"&ocirc;"  => "\xf4",
	"&otilde;" => "\xf5",
	"&ouml;"   => "\xf6",
	"&oslash;" => "\xf8",
	"&ugrave;" => "\xf9",
	"&uacute;" => "\xfa",
	"&ucirc;"  => "\xfb",
	"&uuml;"   => "\xfc",
	"&yacute;" => "\xfd",
	"&thorn;"  => "\xfe",
	"&yuml;"   => "\xff",
	"&THORN;"  => "\xde",
	"&szlig;"  => "\xdf",
	"&Agrave;" => "\xe0",
	"&Aacute;" => "\xe1",
	"&Acirc;"  => "\xe2",
	"&Atilde;" => "\xe3",
	"&Auml;"   => "\xe4",
	"&Aring;"  => "\xe5",
	"&Aelig;"  => "\xe6",
	"&Ccedil;" => "\xe7",
	"&Egrave;" => "\xe8",
	"&Eacute;" => "\xe9",
	"&Ecirc;"  => "\xea",
	"&Euml;"   => "\xeb",
	"&Igrave;" => "\xec",
	"&Iacute;" => "\xed",
	"&Icirc;"  => "\xee",
	"&Iuml;"   => "\xef",
	"&ETH;"    => "\xf0",
	"&Ntilde;" => "\xf1",
	"&Ograve;" => "\xf2",
	"&Oacute;" => "\xf3",
	"&Ocirc;"  => "\xf4",
	"&Otilde;" => "\xf5",
	"&Ouml;"   => "\xf6",
	"&Oslash;" => "\xf8",
	"&Ugrave;" => "\xf9",
	"&Uacute;" => "\xfa",
	"&Ucirc;"  => "\xfb",
	"&Uuml;"   => "\xfc",
	"&Yacute;" => "\xfd",
	"&Yhorn;"  => "\xfe",
	"&Yuml;"   => "\xff" );

    var $entities2 = array (
	"&amp;"    => "&",
	"&apos;"   => "'",
	"&THORN;"  => "t",
	"&szlig;"  => "s",
	"&agrave;" => "a",
	"&aacute;" => "a",
	"&acirc;"  => "a",
	"&atilde;" => "a",
	"&auml;"   => "a",
	"&aring;"  => "a",
	"&aelig;"  => "a",
	"&ccedil;" => "c",
	"&egrave;" => "e",
	"&eacute;" => "e",
	"&ecirc;"  => "e",
	"&euml;"   => "e",
	"&igrave;" => "i",
	"&iacute;" => "i",
	"&icirc;"  => "i",
	"&iuml;"   => "i",
	"&eth;"    => "e",
	"&ntilde;" => "n",
	"&ograve;" => "o",
	"&oacute;" => "o",
	"&ocirc;"  => "o",
	"&otilde;" => "o",
	"&ouml;"   => "o",
	"&oslash;" => "o",
	"&ugrave;" => "u",
	"&uacute;" => "u",
	"&ucirc;"  => "u",
	"&uuml;"   => "u",
	"&yacute;" => "y",
	"&thorn;"  => "t",
	"&yuml;"   => "y",
	"&THORN;"  => "t",
	"&szlig;"  => "s",
	"&Agrave;" => "a",
	"&Aacute;" => "a",
	"&Acirc;"  => "a",
	"&Atilde;" => "a",
	"&Auml;"   => "a",
	"&Aring;"  => "a",
	"&Aelig;"  => "a",
	"&Ccedil;" => "c",
	"&Egrave;" => "e",
	"&Eacute;" => "e",
	"&Ecirc;"  => "e",
	"&Euml;"   => "e",
	"&Igrave;" => "i",
	"&Iacute;" => "i",
	"&Icirc;"  => "i",
	"&Iuml;"   => "i",
	"&ETH;"    => "e",
	"&Ntilde;" => "n",
	"&Ograve;" => "o",
	"&Oacute;" => "o",
	"&Ocirc;"  => "o",
	"&Otilde;" => "o",
	"&Ouml;"   => "o",
	"&Oslash;" => "o",
	"&Ugrave;" => "u",
	"&Uacute;" => "u",
	"&Ucirc;"  => "u",
	"&Uuml;"   => "u",
	"&Yacute;" => "y",
	"&Yhorn;"  => "t",
	"&Yuml;"   => "y" );


    function setBaseUrl     ( $param ) {  $this->baseurl       = $param;  }
    function setFilter      ( $param ) {  $this->filter        = $param;  }
    function setLeave       ( $param ) {  $this->leave         = $param;  }
    function setStrip       ( $param ) {  $this->strip         = $param;  }
    function setDebug       ( $param ) {  $this->debug         = $param;  }
    function setIndexHost   ( $param ) {  $this->index_host    = $param;  }
    function setIndexMeta   ( $param ) {  $this->index_meta    = $param;  }


    // fix built-in arrays
    function __construct() {
	foreach ( $this->apache_indexes as $element ) {
	    $this->apache_indexes[$element] = 1;
	}
    }


    // this will work in console mode only
    function dump( $desc, $table, $chunk, $link ) {
	if ( $this->debug ) {
	    echo "-----------------------------------------------------------------------------------------------\n";
	    echo "$desc\nchunk: $chunk\n\nlink: $link\n";
	    print_r( $table );
	    echo "\n\n";
	}
    }


    // remove the file part from an url (to build an url from an url and given relative path)
    function parent_url( $url ) {

	// split current url into parts
	$parts = parse_url( $url );

	// http://staff.wikia.com/wiki/Main_Page?oldid=1 -> /wiki/Main_Page
	$path = $parts['path'];

	//   /wiki/Main_Page -> Main_Page  (filter out this part)
	$regs = array();
	if ( eregi( '([^/]+)$', $path, $regs ) ) {
	    // TODO: Warning: eregi_replace(): REG_BADRPT in /var/www/wiki/products/webrobot/class.parser.php on line 242
	    //       this is probably caused by un-escaped special characters
	    $path = @eregi_replace( $regs[1] . '$', "", $path );
	}

	// http://staff.wikia.com/wiki/
	return $parts['scheme'] . "://" . $parts['host'] . $path;
    }


    // checks if url is legal, relative to the main url
    function filter_url( $url, $original_url ) {

	// fix &amp; and spaces
	$url = str_replace( "&amp;", "&", $url );
	$url = str_replace( " ", "%20", $url );

	// filter out non-html (non-indexable) extensions
	if ( $this->filter ) {
	    foreach ( $this->ext as $excl ) {
		if ( eregi( "\.$excl$", $url ) ) {
		    return '';
		}
	    }
	}

	// filter out urls ending with backslash (local?)
	if ( substr( $url, -1 ) == "\\" ) {
	    return '';
	}

	// filter out email addresses, javascript links, and usenet references
	if ( eregi( "[/]?mailto:|[/]?javascript:|[/]?news:", $url ) ) {
	    return '';
	}

	// split current url into parts
	$url_parts = @parse_url( $url );

	if ( !$url_parts ) {
	    return '';
	}

	// filter out Apache fancy indexes
	if ( isset( $url_parts['query'] ) ) {
	    if ( $this->apache_indexes[$url_parts['query']] ) {
		return '';
	    }
	}

	// guess scheme (http, https or empty)
	$scheme = ( isset( $url_parts['scheme'] ) ? $url_parts['scheme'] : '' );

	// only http and https links are followed
	if ( $scheme != 'http' && $scheme != 'https' && $scheme != '' ) {
	    return '';
	}

	// parent url might be used to build an url from relative path
	$parent_url = $this->parent_url( $original_url );

	// split parent url into parts
	$parent_parts = parse_url( $parent_url );

	// if url begins with path, e.g. /wiki/Main_Page (is relative),
	// convert it to absolute, using parts of parent url
	if ( substr( $url, 0, 1 ) == '/' ) {
	    $url = $parent_parts['scheme'] . "://" . $parent_parts['host'] . $url;

	// url has only file name, even without the complete path. simply
	// combine it with parent url
	} elseif ( !isset( $url_parts['scheme'] ) ) {
	    $url = $parent_url . $url;
	}

	// again split just fixed url into parts
	$url_parts = parse_url( $url );

	// http://staff.wikia.com/wiki/Main_Page?oldid=1 -> /wiki/Main_Page
	$path = ( $url_parts['path'] != '' ? $url_parts['path'] : "/" );

	// filter out /../
	$regs = array();
	while ( ereg( "[^/]*/[.]{2}/", $path, $regs ) ) {
	    $path = str_replace( $regs[0], "", $path );
	}

	// filter out relative path instructions like ../ etc 
	$path = ereg_replace( "/+", "/", $path );
	$path = ereg_replace( "^[.]/", "", $path );
	$path = ereg_replace( "[^/]*/[.]{2}/", "", $path );
	$path = ereg_replace( "^[.]/", "", $path );
	$path =  str_replace( "./", "", $path );

	// form the final url
	$url = $url_parts['scheme'] . "://" . $url_parts['host'] . $path . ( isset( $url_parts['query'] ) ? "?" : "" ) . $url_parts['query'];

	// immediately return url, if we're allowed to leave current domain
	if ( $this->leave ) {
	    return $url;
	}

	// strip session ids from query
	// TODO: change this expression *not* to remove '?' sign from query (or inject it back)
	//       maybe: (1) remove session id, (2) remove &&, (3) remove ?&
	if ( $this->strip ) {
	    $url = preg_replace( "/(\?|&)(PHPSESSID|JSESSIONID|ASPSESSIONID|sid)=[0-9a-zA-Z]+$/", "", $url );
	}

	// if we're restricted to current domain, check if the current url
	// contains it, and return either url, or empty string, if not
	return ( strpos( $url, $this->baseurl ) ? $url : '' );
    }


    // extract links from html
    function process( $input_text, $original_url ) {
	$chunklist = array();
	$links = array();
	$regs = array();

	$chunklist = explode( ">", $input_text );
	foreach ( $chunklist as $chunk ) {

	    if ( stristr( $chunk, "href" ) ) {
		while ( preg_match( $this->pattern_href, $chunk, $regs ) ) {

		    // we ignore nofollow tag: isset($regs[10])
		    $url = $this->filter_url( $regs[2], $original_url );
		    if ( $url != '' ) {  $links[$url]++;  }

		    $this->dump( "href", $regs, $chunk, $url );
		    $chunk = substr( $chunk, strpos( $chunk, $regs[0] ) + strlen( $regs[0] ) );
		}
	    }

	    if ( stristr( $chunk, "frame" ) && stristr( $chunk, "src" ) ) {
		while ( eregi( $this->pattern_frame, $chunk, $regs ) ) {

		    $url = $this->filter_url( $regs[2], $original_url );
		    if ( $url != '' ) {  $links[$url]++;  }

		    $this->dump( "frame-src", $regs, $chunk, $url );
		    $chunk = str_replace( $regs[0], "", $chunk );
		}
	    }

	    if ( stristr( $chunk, "window" ) && stristr( $chunk, "location" ) ) {
		while ( eregi( $this->pattern_location, $chunk, $regs ) ) {

		    $url = $this->filter_url( $regs[2], $original_url );
		    if ( $url != '' ) {  $links[$url]++;  }

		    $this->dump( "window-location", $regs, $chunk, $url );
		    $chunk = str_replace( $regs[0], "", $chunk );
		}
	    }

	    if ( stristr( $chunk, "http-equiv" ) ) {
		while ( eregi( $this->pattern_httpequiv, $chunk, $regs ) ) {

		    $url = $this->filter_url( $regs[2], $original_url );
		    if ( $url != '' ) {  $links[$url]++;  }

		    $this->dump( "http-equiv", $regs, $chunk, $url );
		    $chunk = str_replace( $regs[0], "", $chunk );
		}
	    }

	    if ( stristr( $chunk, "window" ) && stristr( $chunk, "open" ) ) {
		while ( eregi( $this->pattern_windowopen, $chunk, $regs ) ) {

		    $url = $this->filter_url( $regs[2], $original_url );
		    if ( $url != '' ) {  $links[$url]++;  }

		    $this->dump( "window-open", $regs, $chunk, $url );
		    $chunk = str_replace( $regs[0], "", $chunk );
		}
	    }
	}

	return $links;
    }


    // extract metadata from html
    function metadata( $input_text ) {

	// extract <head> ... </head> part from the complete file
	$first = strpos( strtolower( $input_text ), "<head" );
	$next = strpos( strtolower( $input_text ), "</head>" );
	$headdata = ( $next > $first ? substr( $input_text, $first, $next -1 ) : '' );

	// extract metadata from <head> ... </head>
	$description = "";
	$robots = "";
	$keywords = "";
	$title = "";
	$res = array();
	if ( $headdata != "" ) {
	    preg_match( "/<meta +name *=[\"']?robots[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res );
	    if ( isset( $res ) ) {  $robots = $res[1];  }

	    preg_match( "/<meta +name *=[\"']?description[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res );
	    if ( isset( $res ) ) {  $description = $res[1];  }

	    preg_match( "/<meta +name *=[\"']?keywords[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res );
	    if ( isset( $res ) ) {  $keywords = $res[1];  }

	    eregi( "<title *>([^<>]*)</title *>", $headdata, $res );
	    if ( isset( $res ) ) {  $title = $res[1];  }

	    // leave keywords and description as lists of words, while treating robots as list of commands
	    $keywords = preg_replace( "/[, ]+/", " ", $keywords );
	    $robots = explode( ",", strtolower( $robots ) );
	    $nofollow = 0;
	    $noindex = 0;
	    foreach ( $robots as $x ) {
		if ( trim( $x ) == "noindex"  ) {  $noindex  = 1;  }
		if ( trim( $x ) == "nofollow" ) {  $nofollow = 1;  }
	    }
	    $data['description'] = addslashes( $description );
	    $data['keywords'] = addslashes( $keywords );
	    $data['title'] = addslashes( $title );
	    $data['nofollow'] = $nofollow;
	    $data['noindex'] = $noindex;
	}

	return $data;
    }


    // convert html+metadata to text
    function clean( $input_text, $original_url ) {

	// split current url into parts
	$parts = parse_url( $original_url );

	// remove filename from path
	$path = eregi_replace( '([^/]+)$', "", $parts['path'] );

	// remove stylesheets
	$text = eregi_replace( "<link rel[^<>]*>", " ", $input_text );

	// remove comments
	$first = strpos( $text, "<!--" );
	$count = 0;
	while ( !( $first === false ) && $count < 200 ) {
	    $count++;
	    $next = strpos( $text, "-->" );
	    $text = str_replace( substr( $text, $first, $next - $first + 3 ), " ", $text );
	    $first = strpos( $text, "<!--" );
	}

	// remove scripts
	$first = strpos( strtolower( $text ), "<script" );
	$count = 0;
	while ( !( $first === false ) && $count < 20 ) {
	    $count++;
	    $next = strpos( strtolower( $text ), "</script" );
	    $text = str_replace( substr( $text, $first, $next - $first + 9 ), " ", $text );
	    $first = strpos( strtolower( $text ), "<script" );
	}

	$headdata = $this->metadata( $text );

	// remove styles
	$text = eregi_replace( "(<style[^>]*>[^<>]*</style>)", " ", $text );

	// create spaces between tags, so that remove tags doesnt concatenate strings
	$text = preg_replace( "/<[\w ]+>/", "\\0 ", $text );
	$text = preg_replace( "/<\/[\w ]+>/", "\\0 ", $text );
	$text = preg_replace( "/\s+/", " ", $text );
	$text = strip_tags( $text );
	$text = preg_replace( "/&nbsp;/", " ", $text );
	$text = preg_replace( "/ +/", " ", $text );

	// concatenate page text with metadata
	$fulltext = $text;
	$text .= ' ' . $headdata['title'];

	if ( $this->index_host ) {
	    $text = $text . ' ' . $parts['host'] . ' ' . $path;
	}
	if ( $this->index_meta ) {
	    $text = $text . ' ' . $headdata['keywords'];
	}

	// replace standard html codes with their char equivalents
	foreach ( $this->entities1 as $char ) {
	    $text = eregi_replace( $char[0], $char[1], $text );
	    //echo "entity " . $char[0] . " - " . $char[1] . "\n";
	}

	// replace codes with ascii chars
	$text = ereg_replace( '&#([0-9]+);', chr('\1'), $text );
	$text = strtolower( $text );

	// remove unknown html codes
	$text = ereg_replace( "&[a-z]{1,6};", " ", $text );

	// remove some special characters
	$text = preg_replace( "/[\*\^\+\?\\\.\[\]\^\$\|\{\)\(\}~!\"\/@#£$%&=`´;><:,]+/", " ", $text );
	$text = preg_replace( "/ +/", " ", $text );

	$data['fulltext'] = addslashes( $fulltext );
	$data['content'] = addslashes( $text );
	$data['description'] = $headdata['description'];
	$data['keywords'] = $headdata['keywords'];
	$data['title'] = $headdata['title'];
	$data['directory'] = $path;
	$data['nofollow'] = $headdata['nofollow'];
	$data['noindex'] = $headdata['noindex'];

	return $data;
    }


    // find field name and value from html-form substring
    function get_form_name( $string ) {
	if ( preg_match( "/name=[\"']?([\w\s]*)[\"']?[\s>]/i", $string, $match ) ) {
	    return preg_replace( "/\"'/", "", trim( $match[1] ) );
	}
    }

    function get_form_value( $string ) {
	if ( preg_match( "/value=(\"([^\"]*)\"|'([^']*)'|[^>\s]*)([^>]*)?>/is", $string, $match ) ) {
	    return str_replace( '"', '', trim( $match[1] ) );
	}
    }


    // parse forms in html page into one big array
    function parse_form( $input_text ) {
	$result = array();
	$form_counter = 0;

	$text = str_replace( ">", ">\n", $input_text );
	$text = str_replace( "\r\n", "\n", $text );

	// TODO: detect additional whitespace in form tag
	if ( preg_match_all("/<form.*>.+<\/form>/isU", $text, $forms ) ) {
	    foreach ( $forms[0] as $form ) {
		$field_counter = 0;
		$button_counter = 0;

		// Form Details like method, action ..
		// TODO: detect addictional whitespace before = sign
		preg_match( "/<form.*name=[\"']?([\w\s]*)[\"']?[\s>]/i", $form, $match );
		$result[$form_counter]['name'] = preg_replace( "/[\"'<>]/", "", $match[1] );

		// TODO: translate to absolute url
		preg_match( "/<form.*action=(\"([^\"]*)\"|'([^']*)'|[^>\s]*)([^>]*)?>/is", $form, $match );
		$result[$form_counter]['action'] = preg_replace( "/[\"'<>]/", "", $match[1] );

		preg_match( "/<form.*method=[\"']?([\w\s]*)[\"']?[\s>]/i", $form, $match );
		$result[$form_counter]['method'] = preg_replace( "/[\"'<>]/", "", $match[1] );

		preg_match( "/<form.*enctype=(\"([^\"]*)\"|'([^']*)'|[^>\s]*)([^>]*)?>/is", $form, $match );
		$result[$form_counter]['enctype'] = preg_replace( "/[\"'<>]/", "", $match[1] );

		// <input type=hidden entries
		if ( preg_match_all( "/<input.*type=[\"']?hidden[\"']?.*>$/im", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			$result[$form_counter]['fields'][$field_counter++] = array (
				'type'  => 'hidden',
				'name'  => $this->get_form_name( $match ),
				'value' => $this->get_form_value( $match )
			);
		    }
		}

		// <input type=text entries
		if ( preg_match_all( "/<input.*type=[\"']?text[\"']?.*>/iU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			$result[$form_counter]['fields'][$field_counter++] = array (
				'type'  => 'text',
				'name'  => $this->get_form_name( $match ),
				'value' => $this->get_form_value( $match )
			);
		    }
		}

		// <input type=password entries
		if ( preg_match_all( "/<input.*type=[\"']?password[\"']?.*>/iU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			$result[$form_counter]['fields'][$field_counter++] = array (
				'type'  => 'password',
				'name'  => $this->get_form_name( $match ),
				'value' => $this->get_form_value( $match )
			);
		    }
		}

		// <textarea entries
		if ( preg_match_all( "/<textarea.*>.*<\/textarea>/isU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			// TODO: check, if the second preg_match is really required
			preg_match( "/<textarea.*>(.*)<\/textarea>/isU", $match, $textarea_value );
			$result[$form_counter]['fields'][$field_counter++] = array (
				'type'  => 'textarea',
				'name'  => $this->get_form_name( $match ),
				'value' => $textarea_value[1]
			);
		    }
		}

		// <input type=checkbox entries
		if ( preg_match_all( "/<input.*type=[\"']?checkbox[\"']?.*>/iU", $form, $matches ) ) {
		    $values = array();
		    foreach ( $matches[0] as $match ) {
			$name = $this->get_form_name( str_replace( "[]", "", $match ) );
			$value = $this->get_form_value( $match );  // TODO: detect also <label for=xxx>
			$values[$name][$value] = ( preg_match( "/checked/i", $match ) ? true : false );
		    }
		    foreach ( $values as $name => $list ) {
			$result[$form_counter]['fields'][$field_counter++] = array (
				'type'   => 'checkbox',
				'name'   => $name,
				'values' => $list
			);
		    }
		    unset( $values );
		}

		// <input type=radio entries
		if ( preg_match_all( "/<input.*type=[\"']?radio[\"']?.*>/iU", $form, $matches ) ) {
		    $values = array();
		    foreach ( $matches[0] as $match ) {
			$name = $this->get_form_name( $match );
			$value = $this->get_form_value( $match );
			$values[$name][$value] = ( preg_match( "/checked/i", $match ) ? true : false );
		    }
		    foreach ( $values as $name => $list ) {
			$result[$form_counter]['fields'][$field_counter++] = array (
				'type'   => 'radio',
				'name'   => $name,
				'values' => $list
			);
		    }
		    unset( $values );
		}

		// <input type=submit entries
		if ( preg_match_all( "/<input.*type=[\"']?submit[\"']?.*>/iU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			$result[$form_counter]['buttons'][$button_counter++] = array (
				'type'  => 'submit',
				'name'  => $this->get_form_name( $match ),
				'value' => $this->get_form_value( $match )
			);
		    }
		}

		// <input type=button entries
		if ( preg_match_all( "/<input.*type=[\"']?button[\"']?.*>/iU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			$result[$form_counter]['buttons'][$button_counter++] = array (
				'type'	=> 'button',
				'name'	=> $this->get_form_name( $match ),
				'value'	=> $this->get_form_value( $match )
			);
		    }
		}

		// <input type=reset entries
		if ( preg_match_all( "/<input.*type=[\"']?reset[\"']?.*>/iU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			$result[$form_counter]['buttons'][$button_counter++] = array (
				'type'  => 'reset',
				'name'  => $this->get_form_name( $match ),
				'value' => $this->get_form_value( $match )
			);
		    }
		}

		// <input type=image entries
		if ( preg_match_all( "/<input.*type=[\"']?image[\"']?.*>/iU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {
			$result[$form_counter]['buttons'][$button_counter++] = array (
				'type'  => 'image',
				'name'  => $this->get_form_name( $match ),
				'value' => $this->get_form_value( $match )
			);
		    }
		}

		// <input type=select entries
		// Here I have to go on step around to grep at first all select
		// names and then the content. Seems not to work in an other way
		if ( preg_match_all( "/<select.*>.+<\/select>/isU", $form, $matches ) ) {
		    foreach ( $matches[0] as $match ) {

			$match_hack = preg_replace( "/<option/i", "</option>\n<option", $match );
			if ( preg_match_all( "/<option.*>.+<\/option/isU", $match_hack, $all_options ) ) {

			    $values = array();
			    foreach ( $all_options[0] as $option ) {
				preg_match( "/<option.*>(.*)<\/option/isU", $option, $option_labels );
				$value = $this->get_form_value( $option );
				$values[$value]['label'] = trim( $option_labels[1] );
				$values[$value]['checked'] = ( preg_match( "/selected/i", $option ) ? true : false );
			    }
			    $result[$form_counter]['fields'][$field_counter++] = array (
				    'type'   => 'select',
				    'name'   => $this->get_form_name( $match ),
				    'values' => $values
			    );
			    unset( $values );
			}
		    }
		}

		// Update the form counter if we have more then 1 form in the HTML table
		$form_counter++;
	    }
	}
	return $result;
    }
}


?>
