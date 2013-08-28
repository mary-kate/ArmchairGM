<?php

//-------------------------------------------------------------------
// This page can lookup a Movie or Actor and find the corresponding IMDb id.
//
// It is designed to be used in conjuction with the AddButtonExtension
// and Template:IMDb while editing a page, to enable linking back to
// imdb.com
//
// This page does a search using IMDb's web service and displays
// the matching results.  When a link is clicked it triggers a call
// back to the current page passing in the picked id.
//
// Copyright 2007 IMDb, all rights reserved, without warranty.
//-------------------------------------------------------------------


// Get the query (the text that was selected):
$original_query = $_GET["q"];
$encoded_query = urlencode($original_query);

// REST Request Constants:
$access_key = '15JN7BKQ96NK62JRM2R2';
$secret_key = 'IvcVeuIiG9S3sFMzgzzTLfZvGHBFYDDUTJjkd1xC';
$action = 'Search';

// Build the REST request (see the IMDb Info Service API documentation).
$timestamp = gmdate('Y-m-d\TH:i:s\Z');
$signature =
  urlencode(
    base64_encode(
      hash_hmac('sha1', $action . $timestamp, $secret_key, TRUE)));

$url = "http://webservice.imdb.com/?Action=$action&"
  . "AWSAccessKeyId=$access_key&Timestamp=$timestamp&Signature=$signature&" 
  . "Both=$encoded_query";

// Use curl to make the HTTP request:
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

$result = curl_exec($curl);

if ($result == FALSE) { // Handle serious errors:
?>
<html>
<head><title>Error</title></head>
<body>
<h1>Error</h1>
<p>An error occurred.  Could not process the request for: 
<b><?php echo $original_query  ?></b></p>
</body>
</html>
<?php
}
else { // handle the HTTP results
?>
<html>
<head>
 <title>IMDb Lookup</title>
 <script type="text/javascript">
   function pick(resource_id) {
     window.opener.insertIMDbTag(resource_id);
     window.close();
   }
 </script>
 <style type="text/css">
    div { font-weight: bold; }
 </style>
</head>
<body>
<?php
  $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $xml = new SimpleXMLElement($result); 
  if ($http_code != 200 || !$xml) {
?>
<h1>Try Again</h1>
<p>An error occurred processing your request.  Please try again in
another minute.</p>
<?php 
  } 
  else {
    if (!$xml->SearchResults || count($xml->SearchResults->Result) == 0) {
?>
<p><b>No Matches</b></p>
<p>We were unable to find any matches for: <?php echo $original_query ?></p>
<?php
    }
    // Finally, we have a good result, display it:
    else {
      foreach ($xml->SearchResults->Result as $search_result) {
	echo '<div>';
	if ((string) $search_result->Category == 'Popular') {
	  echo "Popular ", $search_result->Type, "s";
	}
	else {
	  echo $search_result->Type, "s ($search_result->Category Matches)";
	}
	echo "</div>\n";
	echo "<ul>";
	if ($search_result->Type == 'Name') {
	  foreach ($search_result->NameResult as $name) {
	    echo "<li><a onclick=\"pick('$name->NameId')\" ",
  	         "href=\"$name->NameId\">$name->Name</a>";

	    if ($name->Job || $name->Credit->Title) {
	      $credit = $name->Credit->Title;
	      if ($credit && $name->Credit->Year) {
		$credit .= " (" . $name->Credit->Year . ")";
	      }
	      echo " (", implode(", ", array($name->Job, $credit)), ")";
	    }
	    echo "</li>";
	  }
	}
	else { // Title
	  foreach ($search_result->TitleResult as $title) {
	    echo "<li><a onclick=\"pick('$title->TitleId')\" " . 
	      "href=\"$title->TitleId\">$title->Title</a> ($title->Year)</li>";
	  }
	}
	echo "</ul>";
      } // end foreach Result
    }
  }
?>
</body>
</html>
<?php
}
?>
