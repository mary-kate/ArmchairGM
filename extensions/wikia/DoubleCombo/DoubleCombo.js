
	
	
function update_combo(id,func){
	var callback = {
		success: function( originalRequest ) {
			if(originalRequest.responseText){
				var opts = "";
				 $(id).options.length = 0;
				 opts = eval('(' + originalRequest.responseText + ')');

				 add_combo_option(id,0,"-")
				 for(x=0;x<=opts["options"].length-1;x++){
					add_combo_option(id,opts["options"][x].id,opts["options"][x].name)
				 }
	
			}else{
				alert("error")
			}
		}
	};
	var request = YAHOO.util.Connect.asyncRequest('GET', func, callback, "");
}

function add_combo_option(id,value,name){
	var option = new Option(name,value);
	try{
		$(id).add(option,null);
	}catch (e){
		$(id).add(option,-1);
	}	
}

	
	