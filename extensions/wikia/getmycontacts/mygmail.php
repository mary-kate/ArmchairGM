<?

/////////////////////////////////////////////////////////////////////////////////////////
//                                                                                     //
//                                                                                     //
//                                                                                     //
//                        GMAIL CONTACT IMPORTING SCRIPT                               //
//                             COPYRIGHT RESERVED                                      //
//                                                                                     //
//            You may not distribute this software without prior permission            //
//                                                                                     //
//                                                                                     //
//                           WWW.GETMYCONTACTS.COM                                     //
//                                                                                     //
/////////////////////////////////////////////////////////////////////////////////////////


$username = "robsgapingmangina@gmail.com"; //$_POST["username"];

$password = "armchairgm"; //$_POST["password"];
$debug = 1;
$refering_site = "http://mail.google.com/mail/"; //setting the site for refer

$browser_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7"; //setting browser type

$path_to_cookie = realpath('gmailcookie.txt');


$setcookie = fopen($path_to_cookie, 'wb'); //this opens the file and resets it to zero length
fclose($setcookie);


echo '<body background="loading.gif">';


function curl_get($url,$follow, $debug){
global $path_to_cookie, $browser_agent;
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);              
curl_setopt($ch,CURLOPT_COOKIEJAR,$path_to_cookie);
curl_setopt($ch,CURLOPT_COOKIEFILE,$path_to_cookie);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,$follow);
curl_setopt($ch,CURLOPT_USERAGENT, $browser_agent);
$result=curl_exec($ch);
curl_close($ch);

if($debug==1){
echo "<textarea rows=30 cols=120>".$result."</textarea>";       
}
if($debug==2){
echo "<textarea rows=30 cols=120>".$result."</textarea>";       
echo $result;
}
return $result;
}

function curl_post($url,$postal_data,$follow, $debug){
global $path_to_cookie, $browser_agent;
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POST, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS,$postal_data);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);              
curl_setopt($ch,CURLOPT_COOKIEJAR,$path_to_cookie);
curl_setopt($ch,CURLOPT_COOKIEFILE,$path_to_cookie);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,$follow);
curl_setopt($ch,CURLOPT_USERAGENT, $browser_agent);

$result=curl_exec($ch);
curl_close($ch);

if($debug==1){
echo "<textarea rows=30 cols=120>".$result."</textarea>";       
}
if($debug==2){
echo "<textarea rows=30 cols=120>".$result."</textarea>";       
echo $result;
}
return $result;
}

//---------------------------------------------------STEP 1

#$url = "http://mail.google.com/mail/";

#$page_result =curl_get($url,1,0);


//---------------------------------------------------STEP 2
//https://www.google.com/accounts/ServiceLogin?service=mail&passive=true&rm=false&continue=https%3A%2F%2Fmail.google.com%2Fmail%2F%3Fnsr%3D1%26ui%3Dhtml%26zy%3Dl&ltmpl=default&ltmplcache=2
$postal_data ="ui=html&ltmplcache=2&continue=https%3A%2F%2Fmail.google.com%2Fmail%2F%3Fnsr%3D1%26ui%3Dhtml%26zy%3Dl%26amp%3Bui%3Dhtml%26amp%3Bzy%3Dl&service=mail&rm=false&ltmpl=default&Email=".$username."&Passwd=".$password;

$url = 'https://www.google.com/accounts/LoginAuth&service=mail';
$postal_data = "Email={$username}&Passwd={$password}";
$result =curl_post($url,$postal_data,0,0);

$result =curl_get("https://www.google.com/accounts/CheckCookie?chtml=LoginDoneHtml",0,0);
$result =curl_get("https://mail.google.com/mail/",0,0);

$url = "https://www.google.com/accounts/ServiceLogin?service=mail";
$postal_data ="ui=html&ltmplcache=2&continue=https%3A%2F%2Fmail.google.com%2Fmail%2F%3Fnsr%3D1%26ui%3Dhtml%26zy%3Dl%26amp%3Bui%3Dhtml%26amp%3Bzy%3Dl&service=mail&rm=false&ltmpl=default";
$page_result =curl_post($url,$postal_data,0,0);
echo $page_result;
exit();

echo $page_result;
exit();
$moved  = ereg("<H1>Moved Temporarily</H1>", $result );
if($moved){
	$result = preg_match('/A HREF="(.*?)"/', $result, $matches);
	$url = $matches[1];
}

$result =curl_get($url . "&ui=html",1,0);

echo $result;
exit();

// [pick up forwarding url]
preg_match_all("/location.replace.\"(.*?)\"/", $result, $matches);
$matches = $matches[1][0];

//---------------------------------------------------STEP 3

$url = $matches;
$result =curl_get($url,1,0);


//---------------------------------------------------STEP 4 - html only

$url = 'http://mail.google.com/mail/?ui=html&zy=n';
$result =curl_get($url,0,0);
echo $result;
exit();
$moved  = ereg("<H1>Moved Temporarily</H1>", $result );
if($moved){
	$result = preg_match('/A HREF="(.*?)"/', $result, $matches);
	$url = $matches[1];
}
$result =curl_get($url,1,0);
echo $result;
exit();
preg_match_all('/base href="(.*?)"/', $result, $matches);
 
$matches = $matches[1][0];


//---------------------------------------------------STEP 5 - open export contacts page

$url = 'https://mail.google.com/mail/?ui=1&ik=&view=sec&zx=';
$result =curl_get($url,1,0);

preg_match_all("/value=\"(.*?)\"/", $result, $matches);
$matches = $matches[1][0];


//---------------------------------------------------STEP 6 - download csv

$postal_data ='ui=1&at='.$matches.'&ecf=o&ac=Export Contacts';
$url = 'https://mail.google.com/mail/?view=fec';

$result = (curl_post($url,$postal_data,1,0));

				IF (empty($result)){

echo '<p align="center"><font face="Verdana" size="2"><b>No Details Found:</b> Please make sure you have entered correct login details and try again.</font></p><p align="center">';

				}ELSE{
//WRITING OUTPUT TO CSV FILE
				
				$myFile = $username;
				$fh = fopen($myFile, 'w') or die("{$myFile} can't open file");
				//echo $result;
				//echo '*******';
				fwrite($fh, $result);
				fclose($fh);

//*********************** | START OF HTML | ***********************************\\

// [header section - html]

$header = <<<headertext

<html>
<head>
<title>MY CONTACTS</title>
<script type="text/javascript"><!--

var formblock;
var forminputs;

function prepare() {
formblock= document.getElementById('form_id');
forminputs = formblock.getElementsByTagName('input');
}

function select_all(name, value) {
for (i = 0; i < forminputs.length; i++) {
// regex here to check name attribute
var regex = new RegExp(name, "i");
if (regex.test(forminputs[i].getAttribute('name'))) {
if (value == '1') {
forminputs[i].checked = true;
} else {
forminputs[i].checked = false;
}
}
}
}

if (window.addEventListener) {
window.addEventListener("load", prepare, false);
} else if (window.attachEvent) {
window.attachEvent("onload", prepare)
} else if (document.getElementById) {
window.onload = prepare;
}

//--></script>
</head>
<body>

headertext;

//echo $header;

// [RESULTS -TITLE HTML] 

$title = <<<titletext
    
titletext;

	echo $title;
 
// [RESULTS - START OF FORM]



	echo '<form id="form_id" name="myform" method="post" action="">';

	echo '<h1>Your contacts</h1>
		<p class="contacts-message">
			<span class="profile-on">Share Invite with your friends.  They will thank you.  The more friends you invite, the less bored you will be.</span>
		</p>
		<p class="contacts-message">
			<input type="submit" class="invite-form-button" value="Invite Your Friends" name="B1" /> <a href="javascript:toggleChecked()">uncheck all</a>
		</p>
			<div class="contacts-title-row">
				<p class="contacts-checkbox"></p>
				<p class="contacts-title">
					Friend\'s Name
				</p>
				<p class="contacts-title">
		  			Email
					</p>
					<div class="cleared"></div>
			</div>
			<div class="contacts">';

			//OPEING CSV FILE FOR PROCESSING
				$fp = fopen ($username,"r");
				while (!feof($fp)){
					$data = fgetcsv ($fp, 0, ","); //this uses the fgetcsv function to store the quote info in the array $data
					$dataname = $data[0];
					$email = $data[1];
					if (!$dataname){
						$dataname = $email;                 
					}
					if (!empty($email) && $dataname!="Name"){  //Skip table if email is blank
						$addresses[] = array("name"=>$dataname,"email"=>$email);
					}
				}
				
				if($addresses){
					usort($addresses, 'sortContacts');
				
					foreach ($addresses as $address){
						echo '<div class="contacts-row">
							<p class="contacts-checkbox">
								<input type="checkbox" name="list[]" value="'.$address["email"].'" checked>
							</p>
							<p class="contacts-cell">
								'.$address["name"].'
							</p>
							<p class="contacts-cell">
								'.$address["email"].'
							</p>
							<input type="hidden" name="sendersemail" size="20" value="'.$username.'">
							<div class="cleared"></div>
						</div>';
					}
				}

	echo '</div>';

$footer = <<<footertext

<p>
<input type="submit" class="invite-form-button" value="Invite Your Friends" name="B1" /> <a href="javascript:toggleChecked()">uncheck all</a>
</p>
</form>

footertext;

	echo $footer;


				unlink($username); //deleting csv file

				}
				
function sortContacts($x, $y){
	if ( strtoupper($x["name"]) == strtoupper($y["name"]) )
	 return 0;
	else if ( strtoupper($x["name"]) < strtoupper($y["name"]) )
	 return -1;
	else
	 return 1;
}

?>
















