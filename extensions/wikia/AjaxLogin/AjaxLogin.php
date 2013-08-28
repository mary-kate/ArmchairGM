<?php
/*
 * Author : Egon (egon@wikia.com)
 *
 * Copyright Wikia Inc. 2007
 *
 *
 */

$wgUseAjax = True;
$wgAjaxExportList [] = 'ajaxLoginForm';
$wgAjaxExportList [] = 'AjaxLoginScriptCode';
$wgAjaxExportList [] = 'AjaxWindowCSS';

$wgAjaxExportList [] = 'cxValidateUserName';

$wgExtensionFunctions [] = 'wfAjaxLogin';

function wfAjaxLogin(){
	global $IP,$wgEnableAjaxLogin,$wgOut,$wgUser,$wgServer,$wgScriptPath,$wgScript,$wgSpecialPages,$wgMessageCache,$wgRequest;

	$slTitle = SpecialPage::getTitleFor( 'Userlogout' );
	$oTitle = Title::newFromText( $wgRequest->getVal( 'title' ) );
	
	if (isset($oTitle)){
	 if (!$wgEnableAjaxLogin || $_REQUEST['action'] == 'ajax' || (!$wgUser->isAnon() && !$oTitle->equals($slTitle) ) )
	 { return ;}
	}
	
	if (isset( $_REQUEST['returnto'] )){
		$title = $_REQUEST['returnto'];
	}else {
		$title = $wgRequest->getVal( 'title' );
	}
	$title = urlencode( $title);
	wfDebug("Egon title=$title\n");
	$text =	'<style type="text/css">/*<![CDATA[*/
		@import "'.$wgServer.$wgScript.'?action=ajax&rs=AjaxWindowCSS";
		/*]]>*/</style>'."\n";
	$text .= '<script type="text/javascript" src="'.$wgServer.$wgScript.'?action=ajax&rs=AjaxLoginScriptCode"></script>'."\n"; //&returnto='.$title.'
	$text .= "<!-- $title -->\n";
	$wgOut->addScript($text);
}

/*
 * Generates CSS code
 */
function AjaxWindowCSS(){
	Header ('Content-Type: text/css ');
	
	$text ='.loginWindow{
position:absolute;
top:5px;left:5px;
padding:5px;
background-color:#F8F8F8;
background-repeat: repeat-x;
border: 1px solid #aaa;
z-index:1000;
margin-top:10%;
margin-left:25%;
margin-right:35%;
}

.registerWindow{
position:absolute;
top:5px;left:5px;
padding:5px;
background-color:#F8F8F8;
background-repeat: repeat-x;
border: 1px solid #aaa;
z-index:1000;
margin-top:10%;
margin-left:25%;
margin-right:25%;
}';
	die($text);
}

/*
 * Generates AjaxLogin JavaScriptCode
 */
function AjaxLoginScriptCode(){
	//global $IP,$wgOut,$wgUser,$wgServer,$wgScript,$wgScriptPath,$wgSitename, $wgTitle,$wgArticlePath;
	//global $wgCookieExpiration, $wgCookiePath, $wgCookieDomain, $wgCookieSecure, $wgCookiePrefix;

	Header ('Content-Type: text/javascript ');
	$li_id = 'pt-login';
	$li_anonid = 'pt-anonlogin';
	$li_url =  '<a href="javascript:Login();">'.wfMsg('userlogin').'</a>';

	$text .= '
var body_var = null;
var logged=false;
var dblclick_enable=true;

var hide_back = true;
var login_back = "";
var login_border = "";

var ReturnTitle = wgPageName;
if (wgReturnTo){
 ReturnTitle = wgReturnTo;
}

function dblclickEdit(){
  if (dblclick_enable){
    var link_string = wgArticlePath;
    link_string = link_string.replace( "$1", wgPageName)  + "?action=edit";
    document.location = link_string;
  }

 return;
}

function CloseFunc(e){
	var targ;
	if (!e) var e = window.event;
	if (e.target) targ = e.target;
	else if (e.srcElement) targ = e.srcElement;
	if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;

	temp = targ.id.split("_");
	temp = temp[0] + "_editWindow";

	var editWindow = document.getElementById(temp);
	//editWindow.parentNode.removeChild(editWindow);
	editWindow.style.visibility = "hidden";

	if (logged){
		window.location.replace( window.location.href ); 
	}

    dblclick_enable=true;
}

function PurgeSite(){
    var link = wgServer + wgArticlePath;
    link = link.replace( "$1", escape(ReturnTitle))  + "?action=purge";
    
	var xmlHttp;
  	try
    {
	    // Firefox, Opera 8.0+, Safari
    	xmlHttp=new XMLHttpRequest();
    }catch (e){
			    // Internet Explorer
			  try{
			      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
			  }catch (e){
					      try{
						        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
					      }catch (e){
						        alert("Your browser does not support AJAX!");
						        return false;
							   }
      			}
			  }
    
	  xmlHttp.onreadystatechange=function()
      {
       if(xmlHttp.readyState==4)
        { return; }
      }

	xmlHttp.open("GET",link,true);
	xmlHttp.send(null);
}

function jsWindow(id,options) {
		var windowId = id + "_editWindow";

		if ( document.getElementById(windowId) ){
			document.getElementById(windowId).style.visibility = "visible";
			return  document.getElementById(windowId);
		}

		var editWindow = document.createElement(\'div\');
		editWindow.className=options.className;
		editWindow.id = windowId;
		
		var content = document.getElementById("bodyContent");
		
		if (content) {
			content.appendChild(editWindow); 
		}else {
			document.body.appendChild(editWindow); 
		}
		editWindow.style.visibility = "hidden";
        if (id=="AjaxLoginWindow"){
         editWindow.onkeypress=EnterLogin;
        }

		closeBox = "<div id=\'close\' onclick=\'CloseFunc(event);\' style=\'float:right;\'><span style=\'cursor:hand;cursor:pointer\'><img src=\'" + wgServer + wgScriptPath + "/extensions/wikia/AjaxLogin/closeWindow.gif\' id=\'" + id + "_img\' ></span></div>"
        editWindow.innerHTML = closeBox + \'<div id="\' + id + \'"></div>\';
		editWindow.style.visibility = "visible";
		//document.getElementById("close").onclick=CloseFunc;
		return editWindow;
}

function EnterLogin(e){
var keynum;
var keychar;

 if(window.event) // IE
 {
  keynum = e.keyCode
 }
 else if(e.which) // Netscape/Firefox/Opera
 {
  keynum = e.which
 }
 
 if (keynum==13){
  AjaxLoginAttempt();
 }
}

function ModifyLoginForm(form){
	var userLink = document.getElementById("userloginlink")
	if (userLink){
		userLink = userLink.childNodes[1];
		userLink.href = "javascript:Register();";
	}

	var LoginAttemptButton = document.getElementById("wpLoginattempt")
	if (LoginAttemptButton){
		LoginAttemptButton.onclick=AjaxLoginAttempt;
		LoginAttemptButton.type="button";
	}

	var MailPasswordButton = document.getElementById("wpMailmypassword");
	if (MailPasswordButton){
		MailPasswordButton.onclick=MailPasswordAttempt;
		MailPasswordButton.type="button";
	}

	var LoginInput = document.getElementById("wpName1");
	if (LoginInput){
		LoginInput.onkeypress=EnterLogin;
	}
}

function Hide_Back(){
     var Log_Window = document.getElementById("AjaxLoginWindow_editWindow");
     var Error_Div = false;

     if (Log_Window){
        Close_Button = Log_Window.firstChild;

        var div_array = Log_Window.getElementsByTagName("div");
        for (var i=0; i<div_array.length; i++) { 
          if (div_array[i].className=="errorbox") { 
           var Error_Div = div_array[i];
           break;
          }
        } 
    }


     var Log_Form = Log_Window.childNodes[1].getElementsByTagName("form")[0];//.firstChild;//.childNodes[1];
     if (Log_Form){
       if (Error_Div){
         //insertedElement = Log_Form.insertBefore(Error_Div, Log_Form.firstChild);
         //insertedElement = Log_Form.appendChild(Error_Div);
         insertedElement =  Log_Form.insertBefore(Error_Div, Log_Form.childNodes[1].nextSibling);
         insertedElement.style.marginBottom = "0.8em";
         insertedElement.style.marginTop = "0em";
         //insertedElement.style.paddingTop = "0em";
         //insertedElement.style.paddingBottom = "0em";
         //Log_Form.style.paddingBottom= "0em";
         //Log_Form.style.marginBottom= "0em";
       }
       if (Close_Button){
         insertedElement = Log_Form.insertBefore(Close_Button, Log_Form.firstChild);
       }
     }

   Log_Window.style.border="none";
   Log_Window.style.background="none";
}

function Show_Back(){
     var Log_Window = document.getElementById("AjaxLoginWindow_editWindow");
     if (Log_Window){
        Close_Button = Log_Window.firstChild;
     }

   Log_Window.style.border=login_border;
   Log_Window.style.background=login_back;
}

function LoginHandlerFunc(responseText) {
    logged = false;
    hide_back=true;
	var loginWindow = document.getElementById("AjaxLoginWindow");
	loginWindow.innerHTML  = responseText.responseText;

	//Modificating originall Form to fit AjaxLogin
	ModifyLoginForm(loginWindow);

    var loginForm = loginWindow.getElementsByTagName("form")[0]; //getting "userloginForm"
    if (loginForm){
       logged=false;
    }else{
       logged=true;
    }

    if (logged){
     hide_back=false;
    }

   // if (document.getElementById()){
   //  hide_back=false;
   // }

    if (hide_back){
      Hide_Back();
    }
    //else{
    //  Show_Back();
    //}

	if (logged){ 
		PurgeSite();
        setTimeout("window.location.replace( window.location.href );",5000);
	}
}

function ModifyRegisterForm(form){
	var userLink = document.getElementById("userloginlink")
	if (userLink){
		userLink = userLink.childNodes[1];
		userLink.href = "javascript:Login();";
	}

/*	var RegisterAttemptButton = document.getElementById("wpCreateaccount2");
	if (RegisterAttemptButton) {
		RegisterAttemptButton.onclick=AjaxRegisterAttempt;
		RegisterAttemptButton.type="button";
	}*/

	//Validateing feature made by Corfix
    var validate_code = document.getElementById("validate_login_code");
    if (validate_code){
      //alert(validate_code.innerHTML);
      eval(validate_code.innerHTML);
    }
	//End of Corfix feature

     var Reg_Window = document.getElementById("AjaxRegisterWindow_editWindow");
     if (Reg_Window){
        Close_Button = Reg_Window.firstChild;
     }

     if (Close_Button){
       var Reg_Form = Reg_Window.childNodes[1].firstChild.childNodes[1];
       if (Reg_Form){
        insertedElement = Reg_Form.insertBefore(Close_Button, Reg_Form.firstChild);
       }
     }
    
   Reg_Window.style.border="none";
   Reg_Window.style.background="none";
}

function RegHandlerFunc (responseText) {
	var registerWindow = document.getElementById("AjaxRegisterWindow");
	registerWindow.innerHTML  = responseText.responseText;
	PurgeSite();
	//Modificating originall Form to fit AjaxLogin
	ModifyRegisterForm(registerWindow);

    var loginForm = document.getElementById("userloginForm");
    if (loginForm){
       logged=false;
    }else{
       logged=true;
    }

	if (logged){ 
		PurgeSite();
	}
}

function Login(){
 dblclick_enable=false;

 if( document.getElementById("AjaxRegisterWindow_editWindow") ) 
	{ document.getElementById("AjaxRegisterWindow_editWindow").style.visibility = "hidden";}

 if( document.getElementById("AjaxLoginWindow_editWindow") ) { 
  document.getElementById("AjaxLoginWindow_editWindow").style.visibility = "visible";
 }else{
  LoginWindow = jsWindow(\'AjaxLoginWindow\',{className:"loginWindow"});
  loginWindow = document.getElementById("AjaxLoginWindow");
  loginWindow.innerHTML  = "<center><img src=\'" + wgServer + wgScriptPath + "/extensions/wikia/ImageTagging/progress-wheel.gif\' />&nbsp;Loading...</center>";
  sajax_do_call (\'ajaxLoginForm\', Array (\'type=login&returnto=\' + escape(ReturnTitle)) , LoginHandlerFunc );
 }
}

function Register(){
 if( document.getElementById("AjaxLoginWindow_editWindow") ) 
	{ document.getElementById("AjaxLoginWindow_editWindow").style.visibility = "hidden";}

 if( document.getElementById("AjaxRegisterWindow_editWindow") ) { 
   document.getElementById("AjaxRegisterWindow_editWindow").style.visibility = "visible";
 }else{
  RegisterWindow = jsWindow(\'AjaxRegisterWindow\',{className:"registerWindow"});
  var registerWindow = document.getElementById("AjaxRegisterWindow");
  registerWindow.innerHTML  = "<center><img src=\'" + wgServer + wgScriptPath + "/extensions/wikia/ImageTagging/progress-wheel.gif\' />&nbsp;Loading...</center>";

  sajax_do_call (\'ajaxLoginForm\', Array (\'type=signup&returnto=\' + escape(ReturnTitle)) , RegHandlerFunc );
 }
}

function AjaxLoginAttempt(){
    var tempPostBody = "&action=submitlogin&type=login";
    tempPostBody = tempPostBody + "&wpName=" + document.getElementById("wpName1").value;
    tempPostBody = tempPostBody + "&wpPassword=" + document.getElementById("wpPassword1").value;
    
    if (document.getElementById("wpRemember").checked){
      tempPostBody = tempPostBody + "&wpRemember=1";
    }

	tempPostBody = tempPostBody + "&returnto=" + escape(ReturnTitle);

    loginWindow = document.getElementById("AjaxLoginWindow_editWindow");

    var tempInner = "<div id=\'close\' onclick=\'CloseFunc(event);\' style=\'float: right;\'><span style=\'cursor: pointer;\'>"
    tempInner = tempInner + "<img src=\'" + wgServer + wgScriptPath + "/extensions/wikia/AjaxLogin/closeWindow.gif\' id=\'AjaxLoginWindow_img\' /></span></div>"
    tempInner = tempInner + "<div id=\'AjaxLoginWindow\'><center><img src=\'" + wgServer + wgScriptPath + "/extensions/wikia/ImageTagging/progress-wheel.gif\' />&nbsp;Login...</center>"
    tempInner = tempInner + "</div></div>"

     loginWindow.innerHTML = tempInner;
    //loginWindow.innerHTML  = "<center><img src=\'" + wgServer + wgScriptPath + "/extensions/wikia/ImageTagging/progress-wheel.gif\' />&nbsp;Login...</center>";

    Show_Back();

    sajax_do_call (\'ajaxLoginForm\', Array (tempPostBody) , LoginHandlerFunc );
}

function AjaxRegisterAttempt(){
    var tempPostBody = "&action=submitlogin&type=signup";
    tempPostBody = tempPostBody + "&wpName=" + document.getElementById("wpName2").value;
    tempPostBody = tempPostBody + "&wpPassword=" + document.getElementById("wpPassword2").value;
    tempPostBody = tempPostBody + "&wpRetype=" + document.getElementById("wpRetype").value;
    tempPostBody = tempPostBody + "&wpEmail=" + document.getElementById("wpEmail").value;
    tempPostBody = tempPostBody + "&wpRealName=" + document.getElementById("wpRealName").value;
    tempPostBody = tempPostBody + "&wpRemember=" + document.getElementById("wpRemember").checked;

    tempPostBody = tempPostBody + "&wpCreateaccount=wpCreateaccount";

	tempPostBody = tempPostBody + "&returnto=" + escape(ReturnTitle);

    var registerWindow = document.getElementById("AjaxRegisterWindow");
    registerWindow.innerHTML  = "<center><img src=\'" + wgServer + wgScriptPath + "/extensions/wikia/ImageTagging/progress-wheel.gif\' />&nbsp;Registering...</center>";

    //alert(tempPostBody);
    sajax_do_call (\'ajaxLoginForm\', Array ( tempPostBody ), RegHandlerFunc );
}

function MailPasswordAttempt(){
	//var tempPostBody = "&action=submitlogin&type=login";
    //tempPostBody = tempPostBody + "&wpName=" + document.getElementById("wpName1").value;
    //tempPostBody = tempPostBody + "&wpMailmypassword=new%20password";
	//tempPostBody = tempPostBody + "&returnto="  + escape(ReturnTitle);

	//sajax_do_call (\'ajaxLoginForm\', Array (tempPostBody) , LoginHandlerFunc );

	var Form = document.getElementsByTagName("form");

    var i=0;
    for(i=0; i< Form.length; i++){
     if (Form[i].name == "userlogin"){
      form_var=Form[i];
      break;
    }}

    form_var.wpLoginattempt="";
    form_var.action= wgScript + "?title=Special:Userlogin&action=submitlogin&type=login&wpMailmypassword=" + escape(\'E-mail password\');
    form_var.submit();
}

function AddAjaxLogin(){
body_var = document.body;
body_var.ondblclick = function () { dblclickEdit(); };

sajax_request_type = "POST";

var LiLink = document.getElementById("'.$li_anonid.'");

if (LiLink == undefined)
{ var LiLink = document.getElementById("'.$li_id.'") }

if (LiLink != undefined)
{ LiLink.innerHTML = \''.$li_url.'\' ;}
}

addOnloadHook(AddAjaxLogin);'
	;
	die($text);
}

/*
 * Generates Login/Register Form.
 *
 * @Author Egon (egon@wikia.com)
 *
 * @Return String
 *
 */
function ajaxLoginForm ($query){
	global $IP, $wgOut,$wgRequest, $wgUser, $wgServer,$wgArticlePath,  $wgScriptPath;

	Header ('Content-Type: text/xml ');
	require_once ( $IP.'/includes/SpecialUserlogin.php');

	foreach(split('&',$query) as $temp)
	{
		$temp2 = split('=',$temp);
		$_REQUEST[$temp2[0]] = $temp2[1];
	}

	if ($_REQUEST['wpRemember']=='false' || $_REQUEST['wpRemember']=='0'){
		unset($_REQUEST['wpRemember']);
	}else if ($_REQUEST['wpRemember']=='true'){
		$_REQUEST['wpRemember']=1;
	}
	
	$return_url = str_replace('$1', $_REQUEST['returnto'], $wgServer.$wgArticlePath);
	$urlArr[] = $return_url;
	
	$_REQUEST['returnto'] = urldecode($_REQUEST['returnto']);
	
	$return_title = Title::newFromText($_REQUEST['returnto']);
    $article = new Article($return_title);
    
	wfSpecialUserlogin();

	$temp_out=$wgOut->getHTML();
	
	//$wgUser->invalidateCache();
	//$wgUser->saveSettings();

	//SquidUpdate::purge( $urlArr );
	/*$return_title->InvalidateCache();
    $wgOut->SetSquidMaxAge (0);
    $wgOut->EnableClientCache (False);
    $return_title->InvalidateCache ();

    if ($wgUseSquid)
    {
       wfGetDB( DB_MASTER )->immediateCommit();
       SquidUpdate::NewSimplePurge ($return_title)->DoUpdate ();
    }
    
    $wgUser->setOption( 'nocache' , true);
    $wgUser->mTouched = time();
    
    $wgCacheEpoch = time();*/
    
    $article = new Article($return_title);
    $article->purge();
	$return_title->purgeSquid();
    $article->purge();
    
 	//return $wgOut->getHTML();
	return $temp_out;
}
?>