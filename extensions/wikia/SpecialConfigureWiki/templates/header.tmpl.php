<!-- s:<?= __FILE__ ?> -->
<link type="text/css" rel="stylesheet" href="/skins/wikia/css/qform.css" />
<style type="text/css">
/*<![CDATA[*/
#cityselect {
    z-index:9000
} /* for IE z-index of absolute divs inside relative divs issue */
#citydomain  {
    position:absolute;width:100%;height:1.4em;z-index:0;
} /* abs for ie quirks */
#var-autocomplete {position:absolute;top:10em;width:20em;}
#var-autocomplete .yui-ac-content {position:absolute;width:100%;border:1px solid #404040;background:#fff;overflow:hidden;z-index:9050;}
#var-autocomplete .yui-ac-shadow {position:absolute;margin:.3em;width:100%;background:#a0a0a0;z-index:9049;}
#var-autocomplete ul {padding:5px 0;width:100%;}
#var-autocomplete li {padding:0 5px;cursor:default;white-space:nowrap;}
#var-autocomplete li.yui-ac-highlight {background:#ff0;}
#var-autocomplete li.yui-ac-prehighlight {background:#FFFFCC;}

#wikia-variable-editor {
    margin: 0;
    border: 1px solid lightgray;
    padding: 1em;
}
/*]]>*/
</style>

<script type="text/javascript" src="/skins/yui/js/yahoo-dom-event.js"></script>
<script type="text/javascript" src="/skins/yui/js/connection-min.js"></script>
<script type="text/javascript" src="/skins/yui/js/animation-min.js"></script>
<script type="text/javascript" src="/skins/yui/js/autocomplete-min.js"></script>
<script type="text/javascript" src="/skins/wikia/js/wikia_variable_editor.js"></script>

<?php
/**
 * get html form for changing variable parameteres
 *
 * id = variable id
 *
 * form = form identifier:
 * 1 = type
 * 2 = group
 * 3 = access level
 *
 * baseurl = base url for ajax request
 */

/**
 * callback used for replacing div.innerHTML with info taken from ajax
 * response, it uses json format and expects that format is:
 * {"div-name": name-of-div-being-replaced, "div-body": "new-body-for-div"}
 *
 */
?>
<script type="text/javascript">
/*<![CDATA[*/
var oSimpleReplaceCallback = {
    success: function( oResponse ) {
        var divData = eval( '(' + oResponse.responseText + ')' );
        var div = document.getElementById( divData["div-name"] );
        div.innerHTML = divData["div-body"];
        YAHOO.log( "simple replace success:" + divData["div-body"] );
    },
    failure: function( oResponse ) {
        YAHOO.log( "simple replace failure " + oResponse.responseText );
    }
};

var makeRequest = function( form, baseurl ) {
    var oForm = document.getElementById( form );
    YAHOO.util.Connect.setForm( oForm );
    var request = YAHOO.util.Connect.asyncRequest( 'GET', baseurl, oSimpleReplaceCallback );
};


/*]]>*/
</script>
<!-- e:<?= __FILE__ ?> -->
