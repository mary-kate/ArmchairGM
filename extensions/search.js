var q = ""; // global of last run query

function comma(number) {
  
  number = '' + number;
  
  if (number.length > 3) {  
    var mod = number.length % 3;
    var output = (mod > 0 ? (number.substring(0,mod)) : '');
      
    for (i=0 ; i < Math.floor(number.length / 3); i++) {
      if ((mod == 0) && (i == 0)) {
        output += number.substring(mod+ 3 * i, mod + 3 * i + 3);
      } else {
        output+= ',' + number.substring(mod + 3 * i, mod + 3 * i + 3);
      }
    }
  
  return (output);
  
  }
  
  else return number;
}

function srfetch(query, start)
{
  q = query;
  if(q == ""){ return; }
  document.location.hash = "#"+q;

  // get results
  //alert("http://search.isc.swlabs.org/nutchsearch?query="+ q +"&hitsPerSite=2&lang=en&hitsPerPage=10&type=json&start=" + start);
  var s = document.createElement('script');
  s.src = "http://not-quite-ready-yet.index.swlabs.org/nutchsearch?query="+ q +"&hitsPerSite=1&lang=en&hitsPerPage=10&type=json&start=" + start;
  document.getElementsByTagName('head')[0].appendChild(s);

  // also get any mini article
        var s = document.createElement('script');
        s.src = "http://search.wikia.com/index.php?action=ajax&rs=wfGetArticleJSON&rsargs%5B%5D=Mini:" + q;
        document.getElementsByTagName('head')[0].appendChild(s);

  // also get any people
        var s = document.createElement('script');
        s.src = "http://alpha.search.wikia.com:8983/solr/select/?start=0&row=20&wt=json&json.wrf=processPeople&indent=true&q=" + q;
        document.getElementsByTagName('head')[0].appendChild(s);
}

function otherIndex(base)
{
  $("sr-results").innerHTML = "Loading results for '"+q+"'";
  var s = document.createElement('script');
  s.src = base + "?query="+ q +"&hitsPerSite=2&lang=en&hitsPerPage=10&type=json&start=0";
  document.getElementsByTagName('head')[0].appendChild(s);
  return false;
}

function hasher()
{
  if(document.location.hash == "#"+q)
  {
    return;
  }
  $("sr-results").innerHTML = "Loading results for '"+document.location.hash.substr(1)+"'";
  srfetch(document.location.hash.substr(1),0);
}
window.setInterval("hasher()",500);

function search(t)
{
  $("sr-results").innerHTML = "";
  srfetch($("q").value,0);
  if(e) e.returnValue = false;
  return false;
}

function sform(e)
{
  $("sr-results").innerHTML = "";
  srfetch($("q").value,0);
  if(e) e.returnValue = false;
  return false;
}

function smod(e)
{
  //$("sr-results").innerHTML += "Loading more results for '"+$("mq").value;
  $("sr-results").innerHTML = "Loading results for '"+$("mq").value.escapeHTML()+"'";
  srfetch($("mq").value,0);
  if(e) e.returnValue = false;
  try {if(e) e.preventDefault();} catch (ex) {}
  return false;
}


// break out results since we accidentially (and temporarily) overloaded this function
function processJSON(j)
{
  if(j)
  {
    if(j.search)
    {
      processResult(j.search);
    }else if(j.html){
      processMini(j);
    }
  }else{
    // only mini article so far doesn't return anything
        $("sr-mini-tr").innerHTML = '';
        $("sr-mini-controls").innerHTML = "";
	$("sr-mini-content").innerHTML = 'We do not have a mini article about "'+q.escapeHTML()+'".  Improve the search results for everyone by <a href="http://search.wikia.com/index.php?title=Mini:'+q+'&action=edit">starting</a> this article!';
  }
}

function processPeople(r)
{
  var docs = r.response.docs;
  var p = $("sr-people-content");
  
  p.innerHTML = '';
  
  if (docs.length != 0) {
    for(var i=0; i < docs.length; i++) {
	
		if (docs[i]) {
			if((docs[i].defaultPhotoSet)) {
				p.innerHTML += '<a href="http://alpha.search.wikia.com/profile/profile.html?vuid='+docs[i].userId+'"><img src="http://alpha.search.wikia.com/photos/users/'+docs[i].userId+'-'+docs[i].defaultPhotoSet+'m.jpg" alt="'+docs[i].name+'" title="'+docs[i].name+'" border="0"></a>';
			} else {
				p.innerHTML += '<a href="http://alpha.search.wikia.com/profile/profile.html?vuid='+docs[i].userId+'"><img src="http://alpha.search.wikia.com/images/nophotom.png" border="0" alt="'+docs[i].name+'" title="'+docs[i].name+'" /></a>';
			}
			
			if (((i+1)%4==0) || (i==docs.length-1)) {
				p.innerHTML += '<div class="cleared"></div>';
			}
			
		}
		
    }

  } else {
    p.innerHTML += '<div id="sr-people-none"><a href="http://alpha.search.wikia.com/profile/choosepersonal.html">List yourself here</a></div>';
  }

  
  
}

// star rating stuffs
function iStar(n)
{
	return '<img onclick="clickVoteStars('+rid+','+n+');" onmouseover="updateRating('+rid+','+n+',5);" onmouseout="startClearRating('+rid+',5,0);" id="rating_'+rid+'_'+n+'" src="http://images.wikia.com/openserving/sports/images/star_on.gif" alt="" height="12" width="12" />';
}

var MaxRating = 5;
var clearRatingTimer = "";
var voted_new = new Array();
	
var id=0;
var last_id = 0;

function startClearRating(id,rating,voted)
{
	clearRatingTimer = setTimeout("clearRating('" + id + "',0," + rating + "," + voted + ")",200);
}

function clearRating(id,num,prev_rating,voted)
{
	if(voted_new[id])voted=voted_new[id];
		
	for (var x=1;x<=MaxRating;x++) {
		if(voted){
			star_on = "voted";
			old_rating = voted;
		}else{	
			star_on = "on";
			old_rating = prev_rating;
		}
		if(!num && old_rating >= x){
			$("rating_" + id + "_" + x).src = "http://images.wikia.com/openserving/sports/images/star_" + star_on + ".gif";
		}else{
			$("rating_" + id + "_" + x).src = "http://images.wikia.com/openserving/sports/images/star_off.gif";
		}
	}
}
	
function updateRating(id,num,prev_rating)
{
	if(clearRatingTimer && last_id==id)clearTimeout(clearRatingTimer);
	clearRating(id,num,prev_rating);
	for (var x=1;x<=num;x++) {
		$("rating_" + id + "_" + x).src = "http://images.wikia.com/openserving/sports/images/star_voted.gif";
	}
	last_id = id;
}

var showWarn = true;
function clickVoteStars(id,rating)
{
	if(showWarn) alert("Sorry, these don't actually do anything yet :(");
	showWarn = false;
	voted_new[id] = rating;
}

function voteShow(id)
{
	$('stars_'+id).style.display='inline';
}

function voteHide(id)
{
	if(voted_new[id]) return;
	$('stars_'+id).style.display='none';
}

var rid=0;
function processResult(search)
{
  var sr = $("sr-results");
  if (sr.innerHTML.substr(0,7) == "Loading") {
	  sr.innerHTML = "";
  }
  $("q").value = search.query.unescapeHTML().replace(/&quot;/g, '"');
  $("query").innerHTML = search.query;
  $("result-q").innerHTML = search.query;
  $("discuss-result-link").innerHTML = '(<a href="http://search.wikia.com/wiki/Mini_talk:'+search.query+'">discuss these results</a>)';
  $("mini-title").innerHTML = search.query;
  
  if(search.numberOfHits == 0)
  {
    $("count").innerHTML = ":(";
    $("span").innerHTML = "0";
    $("sr-bottom").hide();
    return;
  }
  
  // update/show fields on results screen
  $("sr-bottom").show();
  $("mq").value = search.query.unescapeHTML().replace(/&quot;/g, '"');
  $("count").innerHTML = comma(search.numberOfHits);
  $("span").innerHTML = (search.numberOfHits > 9)?"1-10":"1-"+search.numberOfHits+" ";
  var last1="";
  var rehost = /http:\/\/([^\/]+)/i;
  var rekw = new RegExp("("+search.query+")","ig");
  for (var i=0;i < search.documents.length; i++)
  {
    rid++; // unique result id
    var doc = search.documents[i];
    var url = new String(doc.fields.url);
    var host = url.match(rehost);
    // testing magic summary splitter
    doc.summary = '<span>'+doc.summary.replace(rekw,"</span><b onmouseover='context(this)'>$1</b><span>")+'</span>';
    if(doc.summary == "") { doc.summary = "..."; }
    if(doc.fields.title == "") {doc.fields.title = search.query;}
    var htm = "";
    if(host[0] == last1) { htm += '<blockquote>'; }
    htm += '<div class="result-item" onmouseover="voteShow('+rid+');" onmouseout="voteHide('+rid+');">';
    htm += '<div class="result-item-title"><a href="'+doc.fields.url+'">'+doc.fields.title+'</a></div>';
    htm += '<div class="result-item-blurb">'+doc.summary+'</div>';
    htm += '<div class="result-item-full-url"> ';
    if(url.length > 52){
	    htm += url.substr(0,52)+'... - ';
    }else{
	    htm += doc.fields.url+' - ';
    }
    htm += '<span class="result-item-sub-links"><a href="http://search.isc.swlabs.org/cached.jsp?idx='+doc.indexNo+'&id='+doc.indexDocumentNo+'&query='+search.query+'">Cached</a> - ';
    htm += '<a href="http://search.isc.swlabs.org/explain.jsp?idx='+doc.indexNo+'&id='+doc.indexDocumentNo+'&query='+search.query+'">'+boost(doc.fields.boost)+'</a></span> ';
    htm += '<span id="stars_'+rid+'" style="display:none"><nobr>'+iStar(1)+iStar(2)+iStar(3)+iStar(4)+iStar(5)+'</nobr></span>';
    htm += '</div>';
    htm += '</div>';
    if(host[0] == last1) { htm += '</blockquote>'; }
    sr.innerHTML += htm;
    last1 = host[0]; // for next result indentation
  }
  if(search.end < search.numberOfHits)
  {
    sr.innerHTML += '<div class="sr-more-results"><div class="sr-more-results-top"><div class="sr-more-results-tl"></div><div class="sr-more-results-tr"></div></div><div class="sr-more-results-content"><a href="javascript:srfetch(q,'+(search.end)+');void(0)">Results '+(search.end+1)+' to '+(search.end+10)+'</a></div><div class="sr-more-results-bottom"><div class="sr-more-results-bl"></div><div class="sr-more-results-br"></div></div></div>';
  }
}

function processMini(j)
{
  $("sr-mini-content").innerHTML = j.html;
  $("sr-mini-controls").innerHTML = "";
  
  $("sr-mini-tr").innerHTML = '<img src="http://re.search.wikia.com/images/edit.gif" alt="" border="0"/> <a href="http://search.wikia.com/index.php?title=Mini:'+q+'&action=edit">Edit</a>';
  
  if (j.html!="") {
    $("sr-mini-controls").innerHTML = '';
    $("sr-mini-controls").innerHTML += '<span id="sr-mini-toggle-button"><a href="javascript:toggleMini()">Expand</a></span> - ';
    $("sr-mini-controls").innerHTML += '<a href="http://search.wikia.com/index.php?title=Mini:'+q+'&action=history">History</a> - <a href="http://search.wikia.com/index.php?title=Mini:'+q+'">Full Article</a>';
  }
  
}

function context(e)
{
  var kw = $(e);
  var more;
  if(kw.next() && kw.next().tagName == "SPAN")
  {
    var stub = kw.next().innerHTML.match(/(.+?)\W{2,}/);
    more = (stub)?stub[0]:kw.next().innerHTML;
    if(more.length > 3)
    {
      kw.style.textDecoration = "underline";
      kw.next().style.textDecoration = "underline";
      kw.onmouseout = function (){ this.style.textDecoration = ""; this.next().style.textDecoration = ""; }
      kw.onclick = function () {
        $("sr-results").innerHTML = "";
        srfetch(kw.innerHTML + more.unescapeHTML(),0);
      }
    }
  }
  return false;
}

function boost(amount)
{
    amount -= 0;
    amount = (Math.round(amount*100))/100;
    return (amount == Math.floor(amount)) ? amount + '.00' : (  (amount*10 == Math.floor(amount*10)) ? amount + '0' : amount);
}

function toggleMini() {  
  
  
  if ($("sr-mini-content").hasClassName("collapsed")) {
  
    $("sr-mini-content").removeClassName("collapsed");
    $("sr-mini-content").addClassName("expanded");
    $("sr-mini-toggle-button").innerHTML = '<a href="javascript:toggleMini()">Collapse</a>';
  
  } else {
  
    $("sr-mini-content").removeClassName("expanded");
    $("sr-mini-content").addClassName("collapsed");
    $("sr-mini-toggle-button").innerHTML = '<a href="javascript:toggleMini()">Expand</a>';
    
  }
  
}