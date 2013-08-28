<!-- s:<?= __FILE__ ?> -->
<?php
/**
 * handle enabling and disabling of wikia
 * possible states
 * 0 - confirm enable
 * 1 - confirm disable
 * 2 - really disable
 * 3 - really enable
 * 4 - cancel disable
 * 5 - cancel enable
 */
?>
<script type="text/javascript">
/*<![CDATA[*/
function ToggleWikia( state ) {
    var eDiv = document.getElementById("wk-wikia-toggle");
    var id = <?= $city->mParams->city_id ?>;
    var url = '<?= $ajaxurl ?>';
    switch ( state ) {
        case 0: // confirm
            eDiv.innerHTML = "Are you sure? Click <input type=\"button\" onclick=\"ToggleWikia(3)\" value=\"Enable\" /> " +
            " or click <input type=\"button\" onclick=\"ToggleWikia(5)\" value=\"Cancel\" /> to keep state";
        break;

        case 1: // confirm
            eDiv.innerHTML =
            "Are you sure? Click <input type=\"button\" onclick=\"ToggleWikia(2)\" value=\"Disable\" /> " +
            " or click <input type=\"button\" onclick=\"ToggleWikia(4)\" value=\"Cancel\" /> to keep state";
        break;

        case 2: // really disable
            eDiv.innerHTML = '<img src="/skins/wikia/images/progress-wheel.gif" width="16" height="16" alt="wait" border="0" />';
            YAHOO.util.Connect.asyncRequest("GET", url + "&cityid="+id+"&state=0", oSimpleReplaceCallback );
        break;

        case 3: // really enable
            eDiv.innerHTML = '<img src="/skins/wikia/images/progress-wheel.gif" width="16" height="16" alt="wait" border="0" />';
            YAHOO.util.Connect.asyncRequest("GET", url + "&cityid="+id+"&state=1", oSimpleReplaceCallback );
        break;

        case 4: // cancel disable
            eDiv.innerHTML = 'This wikia is <strong>enabled</strong>'
                + "<input type=\"button\" onclick=\"ToggleWikia(1)\" value=\"Disable this wikia\" />";
        break;

        case 5: // cancel enable
            eDiv.innerHTML = 'This wikia is <strong>diabled</strong>'
                + "<input type=\"button\" onclick=\"ToggleWikia(0)\" value=\"Enable this wikia\" />";
        break;
    }
}
/*]]>*/
</script>

<ul>
 <li>Wikia id is: <strong><?= $city->mParams->city_id ?></strong></li>
 <li>Wikia was created: <strong><?= $city->mParams->city_created ?></strong></li>
 <li>Wikia uses database: <strong><?= $city->mParams->city_dbname ?></strong></li>
 <li>Wikia's main url: <strong><?= $city->mParams->city_url ?></strong></li>
 <li>Wikia is configured for handling domains:
<? foreach ($city->mDomains as $domain): ?>
  <strong><?= $domain ?></strong>
<? endforeach ?>
 </li>
 <li>
  <div id="wk-wikia-toggle" style="display: inline;">
   This wikia is <strong><?= ($city->mParams->city_public == 0) ? "disabled" : "enabled" ?></strong>
   <input
    type="button"
    onclick="ToggleWikia(<?= $city->mParams->city_public ?>)"
    value="<?= ($city->mParams->city_public == 0) ? "Enable this wikia" : "Disable this wikia" ?>"
   />
  </div>
 </li>
</ul>
<!-- e:<?= __FILE__ ?> -->