<?php

$wgExtensionFunctions[] = 'wfSpecialCreateBadge';

function wfSpecialCreateBadge(){

	global $wgUser, $IP;

	class CreateBadge extends SpecialPage {
		
		function CreateBadge(){
			SpecialPage::SpecialPage("CreateBadge");
		}
		
		function execute(){
			global $wgRequest, $wgUser, $wgOut, $wgRequest, $wgSiteView, $IP;
			$wgOut->setPageTitle("Create Fan Badge");
			
			$wgOut->addHTML('
				<div> This is your template (modify away): </div>
				<div style="border: 1px solid rgb(0, 0, 0); margin: 1px; float: left;">
					<table cellspacing="0" style="background: lightgreen none repeat scroll 0%; width: 238px; color: rgb(0, 0, 0); -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;">
					<tbody>
						<tr>
							<td style="background: darkblue none repeat scroll 0%; width: 45px; height: 45px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; text-align: center; font-size: 14pt;">
								<font color="white">
								<b><a title="" class="image" id="tagImage" href="javascript:editImage()">
									<img width="35" height="55" longdesc="/index.php?title=Image:Armchair.gif" alt="Image:Armchair.gif" src="http://images.wikia.com/openserving/sports/images/9/90/Armchair.gif"/></a>
								</b>
								</font>
						</td>
						
						<td style="padding: 4pt; font-size: 8pt; line-height: 1.25em;"> 
							<div id="tagHTML">
								This user is an 
								<b><a title="ArmchairGM" href="/index.php?title=ArmchairGM">ArmchairGM</a></b> fan.
							</div>
					</td>
					</tr>
					</tbody>
					</table>
					</div>
				<div> </div>');
		}
		
	}
	
	SpecialPage::addPage( new CreateBadge );
}

?>
