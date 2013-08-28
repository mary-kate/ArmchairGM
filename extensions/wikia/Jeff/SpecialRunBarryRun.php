<?php

$wgExtensionFunctions[] = 'wfSpecialRunBarryRun';

function wfSpecialRunBarryRun(){
  global $wgUser,$IP;
  include_once("includes/SpecialPage.php");


class RunBarryRun extends SpecialPage {

	
	function RunBarryRun(){
		UnlistedSpecialPage::UnlistedSpecialPage("RunBarryRun");
	}
	
	function execute($value){
		global $wgRequest, $wgOut, $wgStyleVersion, $wgUser, $wgParser, $IP;
		
		require_once("$IP/extensions/ListPages/ListPagesClass.php");
		
		$output .= "\n<style>\n
			#bonds-run h2 {
				margin:5px 0px 10px 0px !important;
				padding:0px 0px 3px 0px !important;
				border-bottom:1px solid #dcdcdc;
			}
			
			#bonds-run .showdetails a {
				display:block;
				padding:0px 0px 3px 0px;
				font-size:12px !important; 
				font-weight:normal !important;
				text-decoration:underline !important;
			}
			
			#bonds-run a:hover {
				background-color:#fff;
				color:#89C46F;
			}
		</style>\n";
		
		$output .= "<div id=\"bonds-run\"><table width=\"1000\"><tr>
		<td width=\"550\" valign=\"top\">
			<iframe frameborder=\"0\" height=\"800\" width=\"550\" src=\"/extensions/wikia/Jeff/bonds.html\"></iframe>
		</td>
		<td width=\"260\" valign=\"top\" style=\"padding-right:30px\">";
		
		
		$list = new ListPages();
		$list->setCategory("News,Opinions,Projects,Game Recaps,Open Thread,Showdowns,Questions");
		$list->setShowCount(10);
		$list->setOrder("PublishedDate");
		$list->setShowPublished("YES");
		$list->setBool("ShowCtg","NO");
		$list->setBool("ShowDate","NO");
		$list->setBool("ShowStats","NO");
		$list->setBool("ShowNav","NO");
	
		$output .= "<h2>Don't Miss</h2>";
		$output .= $list->DisplayList();
		
		$list = new ListPages();
		$list->setCategory("News, Opinions,Questions, ArmchairGM Announcements");
		$list->setShowCount(10);
		$list->setOrder("New");
		$list->setBool("ShowCtg","NO");
		$list->setBool("ShowDate","NO");
		$list->setBool("ShowStats","NO");
		$list->setBool("ShowNav","NO");
		
		$output .= "<h2 style=\"margin-top:25px !important;\">New Stories</h2>";
		$output .= $list->DisplayList();
		
		/*
		$output_lp = "<ListPages>
		category=Opinions
		order=PublishedDate
		Published=Yes
		Level=1
		count=6
		showblurb=300
		BlurbFontSize=Small
		ShowPicture=Yes
		</ListPages>";
		
		$popts = $wgOut->parserOptions();
		$popts->setTidy(true);
		$page_title = Title::makeTitleSafe( NS_MAIN, "RunBarryRun_lp" );
		$p_output = $wgParser->parse($output_lp, &$page_title, $popts, true, true, null);
		$output .= $p_output->getText();
		*/

		$output .= "</td>
		<td align=\"right\" width=\"160\" valign=\"top\">";
		
		
		$output_ad .= "<playerprofilead></playerprofilead>";
		
		$popts = $wgOut->parserOptions();
		$popts->setTidy(true);
		$page_title = Title::makeTitleSafe( NS_MAIN, "RunBarryRun_ad" );
		$p_output = $wgParser->parse($output_ad, &$page_title, $popts, true, true, null);
		$output .= $p_output->getText();

		$output .= "</td></tr></table>";
		$output .= "</div>";
		$title = "ArmchairGM Exclusive - Run, Barry, Run!";
		
		
		$wgOut->setPageTitle($title);
		$wgOut->addHTML($output);

		

	}
}

SpecialPage::addPage( new RunBarryRun );



}

?>
