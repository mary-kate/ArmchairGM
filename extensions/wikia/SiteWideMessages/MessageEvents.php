<?php

/*
 * Author : Egon (egon@wikia.com)
 * 
 * Copyright Wikia Inc. 2006
 *
 * 
*/

//require_once( "$IP/includes/UserMessages.php" );

/*
 * class MesssageEvents is designe to manage cheking wich (and when) Dissmis Notice should be send.
*/ 
class MessageEvents{

	private $mEvent_arguments; //Array of arguments for events.

	private $IllegalQuerys;  //Array of illegal comands in SELECT query, wich is used to determine users_ids at Events::DoCustomEvent function
	private $LanguageWords;	 //Array defining the "triger-script" language
	private $LanguageMacros; //Array defining wich macros (besides global) can be used in 'triger-script'
	private $Handlers;	 //Array defining wich Event should be tested for trigering, during particular Handler Run
	
	//constructor
	function MessageEvents(){
	 $this->IllegalQuerys = array();
	 $this->LanguageMacros = array();
	 $this->LanguageWords = array();
	 $this->mEvent_arguments = array();
	 $this->Handlers = array();
	 
	 $this->IllegalQuerys[]='update';
 	 $this->IllegalQuerys[]='delete';
	 $this->IllegalQuerys[]='drop';
	 $this->IllegalQuerys[]='alter';
	 $this->IllegalQuerys[]='insert';

	 $this->IllegalQuerys[]='eval';

	 $this->LanguageWords[] = 'if';
	 $this->LanguageWords[] = '{';
	 $this->LanguageWords[] = '}';
	 $this->LanguageWords[] = '&&';
	 $this->LanguageWords[] = '||';
	 $this->LanguageWords[] = '(';
	 $this->LanguageWords[] = ')';
	 $this->LanguageWords[] = 'date';
	 $this->LanguageWords[] = 'time()';
	 $this->LanguageWords[] = 'return';
	 $this->LanguageWords[] = 'True';
	 $this->LanguageWords[] = 'False';

	 $this->LanguageMacros['TrigerEvent'] = 'return True;';
	 $this->LanguageMacros['DontTrigerEvent'] = 'return False;';
	 
	 //wfDebug("Egon - MessageEvents::MessageEvents() finished.\n");
	}

	/*
	 * Adding new event to manage.
	 *
	 *@param name 		Name of event
	 *@param handler_name	Name of Handler for wich event should be tested for trigering
	 *@param args 		Arguments to be passed to event
	 *
	 *@return bool 	Does function finish correctly
	*/
	public function AddEvent($name, $handler_name, $args){
	 if (!is_array($args) || !isset($name) || !isset($handler_name) )
	 {
	  wfDebug("Incorrect arguments for Events::AddEvent call.\n");
	  return False;
	 }

	 $this->mEvent_arguments[$name] = $args;
	 $this->Handlers[$handler_name][] = $name;
	 
	// wfDebug("Egon - just added '$name' event for '$handler_name' hook and now got:\n");
	 /*$h_keys = array_keys($this->Handlers);
	 
	 foreach( $h_keys as $key){
	     wfDebug("$key => (\n");
    	     foreach($this->Handlers[$key] as $hname)
    	     { wfDebug("\t => $hname\n"); }
    	wfDebug(")\n");
	 } //*/

	 //wfDebug("Egon - just added '$name' event for '$handler_name' hook.\n");
	 return True;
	}

        /*
         * Launching events for particular Hook.
         *
         *@param handler_name   Name of Handler for wich Events, should be tested, for trigering
		 *
         *@return bool	Does function finish correctly
        */
      public function LaunchEvents($handler_name){
 	  $fname = "Events::LaunchEvent";
 	  
 	  //wfDebug("Egon - for handler='$handler_name', and for handlers:\n".$this->Handlers."\n we have: '".array_key_exists($handler_name, $this->Handlers)."'\n");

 	  if ( !isset($handler_name) || !array_key_exists($handler_name, $this->Handlers) )
	  {
	   wfDebug("Incorrect handler name:'$handler_name' in Events::LaunchEvents call.\n");
	   return False;
	  }

 	  $names = $this->Handlers[$handler_name];

	  foreach( $names as $name)
	     if ( !isset($this->mEvent_arguments[$name]) )
	     {
              wfDebug("Incorrect Event name:'$name' for handler '$handler_name' in Events::LaunchEvents call.\n");
	      return False;
	     }else if (!$this->ManageEvent($name, $this->mEvent_arguments[$name]))
	     { return False;}

	  return True;	  	
	}

	/*
	 * Manageing arguments for particular event.
	 *
	 *@param name		Name of Event
	 *@param arguments	Array of arguments for the Event
	 *
	 *
	 *@return bool		Does function finish correctly
	*/
	private function ManageEvent($name, $arguments){
	 $fname = "Events::ManageEvent";
	 $keys = array_keys($arguments);

	 $vEvent = NULL;
	 $event_type = NULL;

	 foreach( $keys as $key){
	   $argument = $arguments[$key];

	   switch ($key){
	   case 'mssg_id' : 		$vEvent[$key] = $argument; break;
	   case 'triger_function' : $vEvent[$key] = $argument; break;
       case 'ids_select' : 		$vEvent[$key] = $argument; break;
	   case 'event_group':      $vEvent[$key] = $argument; break;
	   case 'event_type' :		$event_type = $argument; break;
	   default : wfDebug("$fname - Unknow argument Key: '$key' for $name.\n"); return False;
	   }
	  }

	  if (!isset($vEvent['mssg_id'])){
                global $wgOut;
                $wgOut->showFatalError("No proper mssg_id was given for Event $name\n");
		return False;
 	  }

      if (!isset($event_type)){
		global $wgOut;
		$wgOut->showFatalError("No proper mssg_id was given for Event $name\n");
		return False;
	  }

	  if (isset($vEvent['triger_function']))
	  {
	  // if (!$this->Validate($name, &$vEvent['triger_select']))
	  // { return False;}

	   if ($this->TrigerEvent($name, $vEvent))
	   {
	    //Triger function says "Send Message"

            //chosing the type of event to do
	    switch ($event_type){
	      case 'single': 	return $this->DoSingleEvent($name, $vEvent);
	      case 'massive': 	return $this->DoMassiveEvent($name, $vEvent);
	      case 'group':	    return $this->DoGroupEvent($name, $vEvent);
	      case 'custom': 	return $this->DoCustomEvent($name, $vEvent);

	      default:	global $wgOut;
			$wgOut->showFatalError("No proper event_type was given for Event $name\n");
			return False;
	    }
	   }else
	   { //Just send message
	    return True;
	   }
	  }else
	  {
	    //No Triger function was set
	    wfDebug("$fname - no triger function was set for $name\n");
	    return False;
	  }
	}//end of ManageEvent


        /**
	  * Validateing SELECT string does it have any illegal commands
	  *
	  *@param name   Name of Event
	  *@param string String containing query pqrt after the "SELECT"
	  *
	  *@return booolTrue|False Does string is valid
	  */
	
	private function ValidateQuery($name, $string){
	  foreach( $this->IllegalQuerys as $illegal)
	    if (stripos($string,$illegal) !== False){
		wfDebug("Illegal '$illegal' command found in $name select.\n");
		return False;
	    }

	   return True;
	}


	/**
	 * Testing does the event should be triged
	 *
	 *@param name 		Name of event
	 *@param vEvent 	Parametrs for events
	 *
	 *@return bool		Does event should be trigered
	 */
	private function TrigerEvent($name, $vEvent){
	  global $wgUser;
	  $fname = 'Event::TrigerEvent';

	  $mssg_id = $vEvent['mssg_id'];
	  $string = $vEvent['triger_function'];
	  
    	  //wfDebug("Egon - $fname\n");

	  $usrMssg = new UserMessages();
	  $usrMssg->setUserInfo( $wgUser );

	  $mssgStatus = $usrMssg->getUserMssgStatus( $mssg_id );

	 // wfDebug("Egon - $name -> mssgStatus = $mssgStatus\n");

	  if ( $mssgStatus == MSSG_STATUS_NOEXIST || $mssgStatus == MSSG_STATUS_NOTACTIVE )
	  {
			if ($string != NULL ) 
			{
			     if (!$this->ValidateCode($name, &$string))
			 {
			     return False; 
			 }
			 else
			 {
			     return $this->Run($string);
			 }
	    }
	    else
	    { //no explitid conditions was set. Just send message. Used in "firstEdit"
		  return True;
		}
	  }
	  return False;
	}
	
	/*
	 * Cheking does code string contain legal code
	 *
	 *@param name		Name of Event
	 *@param code		String containing the code
	 *@param error		Error comunicat about wich word is illegal
	 *
	 *@return bool		Does it fits regules
	 */
	private function ValidateCode($name, $code, $error=null){
		$code = trim($code);
		$code_words = ereg_replace('[:space:]*', ' ', $code);
		$code_words = trim($code_words);
		$code_words = explode(' ', $code_words);

		$legal_words = $this->LanguageWords;
	
	       foreach($code_words as $word)
		if ($word!='' && $word!=' ')
		{
		 $word = trim($word);   //TODO: validateing
		 $is_legal_word = True; //False;
	 	 //$maxI = count($legal_words);	

		/* for($i=0 ; (($i<$maxI) && !$is_legal_word); $i++){
		  if (preg_match('(\(|{|)'.$legal_words[$i].'([:punct:]|)(\(|\)|)', $word))
		  { 
		   $is_legal_word = True; 
		  }
		 }*/
		 
		 if ($is_legal_word)
		 {
		  continue;
		 }else if (preg_match('(\$[_[:alnum:]]*?|)([:punct:]|)',$word)) 
		 {
		  continue;
		 }else if (in_array($word, array_keys($this->LangageMacros) ))
		 {
		  continue;
		 }else
		 {
		  $error = "Illegal '$word' word found in $code for $name.\n";
		  wfDebug($error);
		  return False;
		 }
		}

	  	return True;
	}

	/*
	 * Runing the code written in php to chek does the event should be Done
	 *
	 *@param code		String containing the code of function
	 *
	 *@return bool		Does the Event should be done
	*/
	private function Run($code){
		global $wgUser;
		$macros_keys = array_keys( $this->LanguageMacros );

		foreach($macros_keys as $key)
		 $code = str_replace($key, $this->LanguageMacros[$key], $code);
//		 $code = 'define("'.$key.'", "'.$this->LanguageMacros[$key]."\");\n" . $code;

		$code = $code . " ;";
		 
		//wfDebug("Egon - Runing Triger with code:\n====\n$code\n====\nEnd of code\n");
		
		$triger_function = create_function('$wgUser',  $code);
		$result = $triger_function($wgUser);
		//wfDebug("Egon wynik =$result\n");
		
		return $result;
	}

	/*
	 * Launching Event for single user
	 *
	 *@param name		Name of event
	 *@param vEvent		Arguments for Event
	 *
	 *return bool		Does Event finish correctly
	 */
	private function DoSingleEvent($name, $vEvent){
		global $wgUser;
		$fname = "Events:DoSingleEvent";
		$mssg_id = $vEvent['mssg_id'];                 //TODO \/ Errase -> this was only for testing purpose
		$message = MessageContainer::CreateFromId ($mssg_id, 'en');		
		if ($message === False)
		{ return False;}
		return MessageEditor::Send( $message, MessageEditor::SEND_MODE_USER, $wgUser->getName(), True);
	}

        /*
	 * Launching Event for all user on particular wikia
	 *
	 *@param name           Name of event
	 *@param vEvent         Arguments for Event
	 *
	 *return bool           Does Event finish correctly
	 */
	private function DoMassiveEvent($name, $vEvent){
		 $fname = "Events:DoMassiveEvent";
                 $mssg_id = $vEvent['mssg_id'];
		 $message = MessageContainer::CreateFromId ($mssg_id);
		 if ($message === False)
		 { return False;}
		 return MessageEditor::Send( $message, MessageEditor::SEND_MODE_ALL, '', True);
	}

        /*
	 * Launching Event for every all user on particular wikia AND in particular group
	 *
	 *@param name           Name of event
	 *@param vEvent         Arguments for Event
	 *
	 *return bool           Does Event finish correctly
	 */
	private function DoGroupEvent($name, $vEvent){
		 $fname = "Events:DoGroupEvent";
		 $mssg_id = $vEvent['mssg_id'];

		 if (!isset($vEvent['event_group']))
		 {
		  global $wgOut;
		  $wgOut->showFatalError("No proper event_type was given for Event $name\n");
		  return False;
		 }
		 $event_group = $vEvent['event_group'];
		 $message = MessageContainer::CreateFromId ($mssg_id);
		 if ($message === False)
		 { return False;}
		 return MessageEditor::Send( $message, MessageEditor::SEND_MODE_GROUP, $event_group, True);
	}

	/**
	 * Launching Event whit specific SELECT query
	 *
	 *@param name		Name of Event
	 *@param vEveny		Arguments for Event
	 *
	 *@return bool		Does Event finish correctly
	 */
	 private function DoCustomEvent($name, $vEvent){
                if (!$this->ValidateQuery($name, $vEvent['ids_select']))
	        { return False;}

	        global $wgUser;
	        $fname = 'Event::DoCustomEvent';

                $mssg_id = $vEvent['mssg_id'];
	        $string = $vEvent['ids_select'];

 	        // wfDebug("Egon - $fname\n");

		$usrMssg = new UserMessages();
		$usrMssg->setUserInfo( $wgUser );

		// wfDebug("Egon - $name -> mssgStatus = $mssgStatus\n");

		$db =& wfGetDB( DB_MASTER );
		global $wgSharedNewTalkDB;
		global $wgDBname;
		$query="SELECT user_id FROM ".USER_DB." WHERE $string;";
		
		$res = $db->query($query, $fname, True); //error won't show up on the screen but they will show up in log file

		$user_id_array = array();

		while ($resultObj = $db->fetchObject ($res)){
			$user_id_array[] = $resultObj->user_id;
		}
		
		$db->freeResult ($res);

		if ($user_id_array === array()){
		  //ToDo: does it realy can reurn whit True? (does function realy finished here correct?)
		  return True;
		}else{
		  //Eliminating '0' so the IPs whon't get any notice
		  if (in_array(0, $user_id_array))
		  {  unset($user_id_array[ array_search(0, $user_id_array) ]); }
                  
		  $message = MessageContainer::CreateFromId ($mssg_id);
		  if ($message === False)
		  { return False;}
		  return MessageEditor::Send( $message, MessageEditor::SEND_MODE_CUSTOM, $user_id_array, True);
		}
	}

}

/*
 * Global declarations wich are needed for rest of code
 */

$wgNoticeEvents; 

function AddEvents(){
    global $wgHooks;

    $wgHooks['ArticleAddContent'][] = 'AddDismissNotice';
    $wgHooks['ArticleLoadNoCache'][] = 'LoadWithDissmisMessages';

	//$wgHooks['ArticleSaveComplete'][] = 'firstEditDissmisNotice';

    $wgHooks['ArticleSaveComplete'][] = 'RunEventsCheckArticleSaveComplete';
    //$wgHooks['ArticleInsertComplete'][] = 'RunEventsCheckArticleInsertComplete';

    
    global $wgNoticeEvents;
    
    $wgNoticeEvents=new MessageEvents();
    //wfDebug("Egon - trying to add Events.\n");
   # $wgNoticeEvents->AddEvent('firstEdit', 'ArticleSaveComplete', 
   $wgNoticeEvents->AddEvent('firstEdit', 'ArticleSaveComplete', 
	array(
		'mssg_id' => FIRST_EDIT_MSSG , 
		'event_type' => 'single' ,
		'triger_function' => 'TrigerEvent'
	    ) 
    );

    $wgNoticeEvents->AddEvent('firstEdit', 'ArticleInsertComplete', 
	array(
		'mssg_id' => FIRST_EDIT_MSSG , 
		'event_type' => 'single' ,
		'triger_function' => 'TrigerEvent'
		) 
    );
    //wfDebug("Egon - finished adding Message Events \n");
    
    return;
}

AddEvents();

function AddDismissNotice($article, $action, $return)
{
    //wfDebug("Egon -Running 'AddDismissNotice' function \n");
     $return = '';
     
     if ($article->mTitle->getNamespace() == NS_USER_TALK && $action!='edit'  ) {
             $userMssg = new UserMessages();
	     $tmptext = $userMssg->getCurMessages( $article->mTitle );							                                  
	     if ($tmptext != false) {
	          $return .= "\r\n".$tmptext;
		 }
     }
     
   //  return $return;
}


function LoadWithDissmisMessages($article,$text){
        global $wgDissmisNotice, $wgOut;

        //If there are any Dissmis Notice when inserting content of Talk Page whit omition of the parser
        if ($wgDissmisNotice) {
                 # This is just a copy from a letter part of the code
                 # Display content, don't attempt to save to parser cache
                 # Don't show section-edit links on old revisions... this way lies madness.
                 if( !$article->isCurrent() ) {
                             $oldEditSectionSetting = $wgOut->mParserOptions->setEditSection( false );
                  }
                # Display content and don't save to parser cache
                $wgOut->addPrimaryWikiText( $text, $article, false );
                if( !$article->isCurrent() ) {
                          $wgOut->mParserOptions->setEditSection( $oldEditSectionSetting );
		 }

		return False;
        }else{
		return True;
	}
}

function RunEventsCheckArticleSaveComplete($article,$user,$text,$summary,$isminor,$watchthis){
	global $wgNoticeEvents;
    //wfDebug("Egon - Runing function 'RunEventCheckArticleSaveComplete' \n");
    return $wgNoticeEvents->LaunchEvents('ArticleSaveComplete');
}

function RunEventsCheckArticleInsertComplete($article,$user,$text,$summary,$isminor,$watchthis){
	global $wgNoticeEvents;
    //wfDebug("Egon - Runing function 'RunEventCheckArticleInsertComplete' \n");
	return $wgNoticeEvents->LaunchEvents('ArticleInsertComplete');
}


/*function firstEditDismissNotice($article,$user,$text,$summary,$isminor,$watchthis) {
  $fname = 'MessagesFunction::firstEditDissmisNotice';

  wfDebug("Egon - $fname\n");

  $usrMssg = new UserMessages();
  $usrMssg->setUserInfo( $user );
  $mssgStatus = $usrMssg->getUserMssgStatus( FIRST_EDIT_MSSG );

  wfDebug("Egon - firstEdit -> mssgStatus = $mssgStatus\n");

  if ( $mssgStatus == MSSG_STATUS_NOEXIST || $mssgStatus == MSSG_STATUS_NOTACTIVE ) {
      $usrMssg->setUserMssgStatus( FIRST_EDIT_MSSG, $mssgStatus, MSSG_STATUS_SHOW );

      $utp = $user->getTalkPage();
//    if ( wfRunHooks('ArticleEditUpdateNewTalk', array(&$utp)) ) {
      $user->setNewTalk( true );
//    }

      $db =& wfGetDB( DB_MASTER );
      global $wgSharedNewTalkDB;
      global $wgDBname;
      $query="INSERT INTO `$wgSharedNewTalkDB`.`shared_newtalks` (sn_user_id, sn_wiki) VALUES ('{$user->getID()}','$wgDBname');";

      $res = $db->query($query);
      
      $utp->invalidateCache();
      global $wgUseSquid;

      if ( $wgUseSquid ) {
            $update = SquidUpdate::newSimplePurge( $utp );
            $update->doUpdate();
      }
   }

  return;
}*/
?>
