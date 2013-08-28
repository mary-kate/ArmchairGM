//_____________________________________________________________________
//    copyright 2006 Assela Pathirana
//    UNDER GNU GPL
//____version .2 _________________________________________________________________
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
///////////////////////////////////////////////////////////////////////////////
//    Inspired by gallery2wiki by Andres Obrero <andres.obrero@transarte.com> 
///////////////////////////////////////////////////////////////////////////////

//Javascript for AddButtonExtension.php to be included in the edit pages. 
//--------------------------------

function addttButton(tagfunction,image,text){
  //get the element with id 'toolbar'	
	var tlbar = document.getElementById("toolbar");
  if(!tlbar){ 
		  var tlbar = document.getElementById("wpTextbox1___Frame");
		if(tlbar){
			var tlbar = document.getElementById("wikiPreview");
		}
	}
	if(!tlbar){
    //alert("some problem with AddButtonExtension");
  }
  else{
		var button = document.createElement("A");
		button.target = "_sup";
	  button.onclick=tagfunction;
		var img = document.createElement("IMG");
		img.src =  addbuttonextension_iconpath+"/"+image;
		img.style.cursor = "pointer";
		img.title = text;
		button.appendChild(img);
    if(is_safari || is_opera){ 
			tlbar.insertBefore(button, tlbar.lastChild);
    }else
    {
      tlbar.appendChild(button);
    }
		
  }
 }

/////////////////////////////////////////////////////
  // onload patch for macIE5 from Simon Willison ::: http://simon.incutio.com/archive/2004/05/26/ 
/////////////////////////////////////////////////////
  function addLoadEvent(func) {
      var oldonload = window.onload;
      if (typeof window.onload != "function") {
         window.onload = func;
      } else {
         window.onload = function() {
         oldonload();
         func();
     }
  }
}
function addButtons(){
  for (ii=0;ii<buttonpara.length;ii++){
    addttButton(buttonpara[ii][0],buttonpara[ii][1],buttonpara[ii][2]);
    }
    // addttButton(function insertTag(){ insertTags("<tt>","</tt>","TERMINAL TYPE"); },"ttbutton.jpg","Terminal Type");
}

addLoadEvent(addButtons);
///////////////////////////////////////////////////////////////////////////////////////////////////
////// END mainfile.js                          ///////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
