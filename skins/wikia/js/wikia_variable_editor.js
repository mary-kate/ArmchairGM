// handle ajax requests for editing wikia parameters
//
// used divs: wikia-variable-editor

// for devel version only, should be removed or commented on live server

var oGroupCallback = {
    success: function( oResponse ) {
        var selector = document.getElementById( 'var_id' );
        var div = document.getElementById( 'wikia-variable-editor' );
        selector.innerHTML = oResponse.responseText;
        div.innerHTML = '<br />';
    },
    failure: function( oResponse ) {
        YAHOO.log( "group failure " + oResponse.argument );
        var div = document.getElementById( 'wikia-variable-editor' );
        div.innerHTML = oResponse.responseText;
    }
};

var oVariableCallback = {
    success: function( oResponse ) {
        YAHOO.log( "variable success " + oResponse.argument );
        var div = document.getElementById( 'wikia-variable-editor' );
        div.innerHTML = oResponse.responseText;
    },
    failure: function( oResponse ) {
        YAHOO.log( "variable failure "  + oResponse.argument );
        var div = document.getElementById( 'wikia-variable-editor' );
        div.innerHTML = oResponse.responseText;
    }
};

var oVariableSaveCallback = {
    success: function( oResponse ) {
        YAHOO.log( "variable save success " + oResponse.argument );
        var div = document.getElementById( 'wikia-variable-editor' );
        div.innerHTML = oResponse.responseText;
    },
    failure: function( oResponse ) {
        YAHOO.log( "variable save failure "  + oResponse.argument );
        var div = document.getElementById( 'wikia-variable-editor' );
        div.innerHTML = oResponse.responseText;
    }
};

/**
 * handle group & variable selectors
 */
function wikiaSelectVariable( e, baseurl ) {
    var obj = document.getElementById( e );
    var div = document.getElementById( 'wikia-variable-editor' );
    YAHOO.log( e );
    YAHOO.log( baseurl );
    if ( e == 'group_id' ) {
        // we just replacing values in second window
        baseurl += "&rsargs[1]=" + obj.value
        YAHOO.util.Connect.asyncRequest("GET", baseurl, oGroupCallback);
    }
    else if (e == 'var_id') {
        // we change wikia-variable-editor
        baseurl += "&rsargs[1]=" + obj.value
        if ( obj.value == 0) {
            div.innerHTML = "Select group first";
            return;
        }
        YAHOO.util.Connect.asyncRequest("GET", baseurl, oVariableCallback);
    }
    div.innerHTML = '<img src="/skins/wikia/images/progress-wheel.gif" width="16" height="16" alt="wait" border="0" />';
}

/**
 * handle group & variable saving
 */
function wikiaSaveVariable( e, baseurl ) {
    var oForm = document.getElementById("wikia-variable-form");
    YAHOO.util.Connect.setForm(oForm);
    YAHOO.util.Connect.asyncRequest("POST", baseurl, oVariableSaveCallback);

    var div = document.getElementById( 'wikia-variable-editor' );
    div.innerHTML = '<img src="/skins/wikia/images/progress-wheel.gif" width="16" height="16" alt="wait" border="0" />';
}
