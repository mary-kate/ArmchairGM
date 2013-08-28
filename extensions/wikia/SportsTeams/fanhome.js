		
		function vote_status(id,vote){
			/*
			//Effect.Fade('user-status-vote-'+id, {duration:1.0, fps:32});
					onSuccess: function(originalRequest) {
						//Effect.Appear('user-status-vote-'+id, {duration:2.0, fps:32});
					}
			});
			*/
			
			var sUrl = 'index.php?action=ajax';
			var pars = 'rs=wfVoteUserStatus&rsargs[]=' + id + '&rsargs[]=' + vote;
			var callback =
			{
			  success: function(t) {
				$El('user-status-vote-'+id).set('innerHTML', t.responseText);
			  }
			}
			var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, pars);
			
			
		}

		function detEnter(e) {
			var keycode;
			if (window.event) keycode = window.event.keyCode;
			else if (e) keycode = e.which;
			else return true;
			if (keycode == 13){
				add_status()
				return false;
			} else return true;
		}

		

		var posted = 0;
		function add_status(){
			if($("user_status_text").value && !posted){
				posted = 1;
				
				var sUrl = "index.php?action=ajax";
				var pars = 'rs=wfAddUserStatusNetwork&rsargs[]=' + __sport_id__ + '&rsargs[]=' + __team_id__ + '&rsargs[]=' + escape($("user_status_text").value) + '&rsargs[]=' + __updates_show__
				
					
			var callback =
			{
			  success: function(t) {
				$El('network-updates').set('innerHTML', t.responseText);
				posted = 0;
				$("user_status_text").value='';
			  }
			}
			var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, pars);

			}
		}
		 
		function delete_message(id){
			if(confirm('Are you sure you want to delete this thought?')){
				
				var sUrl = "index.php?action=ajax";
				var pars = 'rs=wfDeleteUserStatus&rsargs[]=' + id;
				
				
			var callback =
			{
			  success: function(t) {
				window.location = __user_status_link__;
			  }
			}
			var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, pars);

			}
			
		}
		
			
			
	// generates markers for the higher zoom levels			   
	function createTopMarker(point, caption, map){
		var marker = new GMarker(point);
		marker.map = map;
		
		
		GEvent.addListener(marker, "mouseover", 
		       function() { 
			       
			       var bb  = this.map.getBounds();
			       
			       // if the point isn't visible in the map don't do anything
			       if(! bb.contains(this.getPoint())){
				       return;
			       }
			       
			       // if the point is visible:
			       // figure out the relative offset for the div and then pop it up
			       
			       var baseMapCoords = this.map.fromContainerPixelToLatLng( new GPoint(0,0), true );
			       var baseDivPix = this.map.fromLatLngToDivPixel( baseMapCoords ); 
			       var placemarkDivPix = this.map.fromLatLngToDivPixel( this.getPoint() );
			       var c = new GPoint(placemarkDivPix.x-baseDivPix.x, placemarkDivPix.y-baseDivPix.y);
			       
			       var divLeft = c.x - 230 + "px";
			       var divTop = c.y - 105 + "px";
			       
			       $("gMapInfo").innerHTML = caption;
			       
			       
			       $El("gMapInfo").setStyle("display", "block");
			       $El("gMapInfo").setStyle("left", divLeft);
			       $El("gMapInfo").setStyle("top", divTop);
			}
		       
		       );
		
		// hide the div on mouseout
		GEvent.addListener(marker, "mouseout", 
				function() {	$El("gMapInfo").setStyle("display", "none");	});
		
		// onClick - pan+zoom the map onto this marker
		GEvent.addListener(marker, "click", 
				function(){	
					$El("gMapInfo").setStyle("display", "none");
					this.map.setCenter( this.getPoint(), 7);	
					});
		
		return marker;
		
	}
	
	
	// generates an icon for the current team/network
	function getTeamIcon(){
		
		  var icon = new GIcon();
		  var iconImage = new Image();
		  
		  iconImage.src = "'" + __team_image__ + "'";
		  
		  
		  // probably should fix this:
		  // there should be an actual error when Image() fails
		  
		  if(iconImage.height <= 0){
			 return G_DEFAULT_ICON;
		  }
		  
		  icon.image = "'" + __team_image__ + "'";
		  
		  // once we get shadows un-comment this and set the right shadow
		  /* icon.shadow = 'http://www.eecs.tufts.edu/~adatta02/shadow-34_l.png';
		  icon.shadowSize = new GSize(100, 50); */
		  
		  icon.iconSize = new GSize(50, (50 * iconImage.height) / iconImage.width );
		  icon.iconAnchor = new GPoint(50, (50 * iconImage.height) / iconImage.width >> 1);
		  
		  return icon;
	}
	
	// generates markers for individual users
	function createMarker(point, caption, URL, map) {
		
		var marker = new GMarker(point, { icon: getTeamIcon() } );
		marker.map = map;
		marker.url = URL;
		
		// just incase
		caption = caption.replace(/<script>/i, "script");
		
		GEvent.addListener(marker, "mouseover", 
		       function() { 
			       
			       var bb  = this.map.getBounds();
			       
			       // if the point isn't visible just exit
			       if(! bb.contains(this.getPoint())){
				  return;
			       } 
			       
			       // figure out where to offset the info-div
			       var baseMapCoords = this.map.fromContainerPixelToLatLng( new GPoint(0,0), true );
			       var baseDivPix = this.map.fromLatLngToDivPixel( baseMapCoords ); 
			       var placemarkDivPix = this.map.fromLatLngToDivPixel( this.getPoint() );
			       var c = new GPoint(placemarkDivPix.x-baseDivPix.x, placemarkDivPix.y-baseDivPix.y);
			       
			       
			       var divLeft = c.x - 260 + "px";
			       var divTop = c.y - 110 + "px";
			       
			       $("gMapInfo").innerHTML = caption;
			       
			       
			       $El("gMapInfo").setStyle("display", "block");
			       $El("gMapInfo").setStyle("left", divLeft);
			       $El("gMapInfo").setStyle("top", divTop);
			       
				} );
		
		// when the icon is clicked, load the fan's profile page
		GEvent.addListener(marker, "click", 
			function() {	window.location = this.url;	} );
		
		// hide the info-div on mouse-out
		GEvent.addListener(marker, "mouseout", 
		       function() {	$("gMapInfo").setStyle("display", "none");	});
		
		return marker;
	}
