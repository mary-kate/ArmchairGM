<?php
/**
 * TagAsCategory
 *
 * A folksonomy extension for MediaWiki
 *
 * Copyright (c) 2007, Molecular
 * BSD License
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 *      * Redistributions of source code must retain the above copyright notice, this list of
 *        conditions and the following disclaimer.
 *
 *      * Redistributions in binary form must reproduce the above copyright notice, this list
 *        of conditions and the following disclaimer in the documentation and/or other materials
 *        provided with the distribution.
 *
 *      * Neither the name of Molecular the names of its contributors may be used
 *        to endorse or promote products derived from this software without specific prior
 *        written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
 * AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @version 1
 * @copyright 2007 Molecular
 * @author Glenn Barnett
 * @author Paul Irish
 * @link http://www.molecular.com/ Molecular
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 *
 * To activate this functionality, place this file in your extensions/ 
 * subdirectory, and add the following line to LocalSettings.php:
 *     require_once("$IP/extensions/TagAsCategory/TagAsCategory.php");
 *
 * We also recommend changing the "Categories" label to "Tags".  This can be 
 * done by going to the "Special:Allmessages" page, and changing the
 * value of "pagecategories" from "{{PLURAL:$1|Category|Categories}}" to 
 * "{{PLURAL:$1|Tags|Tags}}" .
 */
 
if(! defined( 'MEDIAWIKI' ) ) {
   echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
   die( -1 );
} else {
 
    $wgExtensionCredits['other'][] = array(
        'name' => 'TagAsCategory',
        'author' =>'Molecular', 
        'url' => 'http://www.molecular.com',
        'description' => 'This Extension provides "folksonomy"-type tagging to articles, using Categories as the underlying functionality'
    );
 
    $wgHooks['ArticleViewHeader'][] = 'articleShowTagForm';
    $wgHooks['UnknownAction'][] = 'tagAction';
 
function articleShowTagForm(&$article)
{
 
    global $wgOut;
    global $wgScript;   
    global $wgMessageCache;
 
    # add localized messages for the "Add Tag" and "(none)" interface strings
    $wgMessageCache->addMessages(
        array (
            'addtag' => 'Add Tag',
            'notags' => '(none)'
        )
    ); 
 
    # load the localized messages into string variables
    $sAddTag = wfMsg('addtag');
    $sNoTags = wfMsg('notags');
    $sTags = wfMsg('pagecategories');               # defined by default as "{{PLURAL:$1|Category|Categories}}"
    $sCategoriesLink = wfMsg('pagecategorieslink'); # defined by default as "Special:Categories"
 
    # determine the URL for the form action
    $actionUrl = htmlspecialchars( $wgScript );        
 
    # generate the form HTML 
    $tagFormHTML=<<<ENDFORM
        <!-- TagAsCategory extension START -->
        <form action="{$actionUrl}" method="get" id="tagform" style="display: none;">
            <input type="hidden" name="title" value="{$article->mTitle->getPrefixedDBkey()}">
            <input type="hidden" name="action" value="tag">
            <input type="text" name="tag" value="" size="15" />    
            <input type="submit" value="{$sAddTag}" />
        </form>
        <script type="text/javascript">
 
        function moveTaggerBox(){
		 
            if (document.getElementById('categories')){
                // if the Article is already in one or more categories, 
                // 'catlinks' will exist
                document.getElementById('categories').appendChild(document.getElementById('tagform'));
            }
            else 
            {
                // if 'catlinks' doesn't exist (the article is in 0 categories)
                // we must recreate the HTML 
                var div1=document.createElement('div');
                div1.setAttribute('id','categories');
                var p1=document.createElement('p');
                div1.appendChild(p1);
                var a1=document.createElement('a');
                a1.setAttribute('href',escape('{$sCategoriesLink}'));
                a1.setAttribute('title','{$sCategoriesLink}');
                p1.appendChild(a1);
                var txt1=document.createTextNode('{$sTags}');
                a1.appendChild(txt1);
                var txt2=document.createTextNode(': {$sNoTags}');
                p1.appendChild(txt2);
 
                document.getElementById('bodyContent').appendChild(div1);
                div1.appendChild(document.getElementById('tagform'));
                }
 
            document.getElementById('tagform').style.display = ''; // we defined it as hidden. show it once it's placed correctly
        }
 
        hookEvent("load", moveTaggerBox); // hookEvent() defined in wikibits.js
 
        </script>
 
        <!-- TagAsCategory extension END -->
ENDFORM;
 
    $wgOut->addHTML($tagFormHTML);
    return true;
}     
 
    function tagAction($action, $article) {
 
        global $wgUser; 
        global $wgRequest; 
 
        if($action == 'tag' && $wgUser->isAllowed('edit'))
        {
            # set wgUser??
            $content = $article->getContent();
 
            $newCategoryString = "[[Category:" . $wgRequest->getVal('tag') . "]]";
 
            // if $content doesn't already contain the new Category string...
            if (strpos($content, '$newCategoryString') == false) {
 
                // edit the content, appending the new Category string
                // (with newlines) at the bottom of the article.
                //
                // additionally, flag the edit as an 'update', and suppress
                // its inclusion in Recent Changes
 
                $article->doEdit(
                    $content . "\n" . $newCategoryString . "\n",
                    "Added tag: '" . $wgRequest->getVal('tag') . "'",
                    EDIT_UPDATE | EDIT_SUPPRESS_RC
                );
 
            }
 
            // view the article, and abort further processing by mediawiki (false)
            $article->view();
            return false;
        }
        else
        {
            // we don't have edit rights -- abort the operation, restoring
            // control to the default mediawiki behavior (true)
            return true;
        }
    }
}
 
?>