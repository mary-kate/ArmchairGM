
	function view_ratings(user,pg,options){
		var url = "index.php?action=ajax";
		var pars = 'rs=wfGetListRatings&rsargs[]=' + user + '&rsargs[]=' + pg + '&rsargs[]=' + options["shw"] + '&rsargs[]=' + options["ctg"];
		var myAjax = new Ajax.Updater(
			"test1",
			url, 
			{
				method: 'post', 
				parameters: pars
			});
	}		

