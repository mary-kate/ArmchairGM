<!-- s:<?= __FILE__ ?> -->
<style type="text/css">
.wk-form-row {
    list-style-type: none;
    display: inline;
    margin:0;
}
.wk-form-row li {
    display: inline;
}

.wk-form-row label {
    width: 22em;
    float: left;
    text-align: left;
    vertical-align: middle;
    margin: 5px 0 0 0;
}
</style>
<div id="wikia-variable-toggle">
<h2><?= $variable->cv_name ?></h2>
<h3><?= $variable->cv_description ?></h3>
<ul>
<li>
 <div id="wikia-variable-change1" style="display:inline">
 <form id="wikia-variable-form1">
  <input type="hidden" name="variable_id" value="<?= $variable->cv_id ?>" />
  <input type="hidden" name="variable_type" value="<?= $variable->cv_variable_type ?>" />
  <input type="hidden" name="variable_div" value="wikia-variable-change1" />
  <input type="hidden" name="variable_form" value="1" />
  <ul class="wk-form-row">
   <li>
    <label>Type: <strong><?= $variable->cv_variable_type ?></strong></label>
   </li>
   <li>
    <input type="button" onclick="makeRequest('wikia-variable-form1', '<?= $baseurl ?>');" value="Change type of variable" />
   </li>
  </ul>
 </form>
 </div>
</li>
<li>
 <div id="wikia-variable-change2" style="display:inline">
 <form id="wikia-variable-form2">
  <input type="hidden" name="variable_id" value="<?= $variable->cv_id ?>" />
  <input type="hidden" name="variable_group" value="<?= $variable->cv_variable_group ?>" />
  <input type="hidden" name="variable_div" value="wikia-variable-change2" />
  <input type="hidden" name="variable_form" value="2" />
  <ul class="wk-form-row">
   <li>
    <label>Group: <strong><?= $groups[$variable->cv_variable_group] ?></strong></label>
   </li>
   <li>
    <input type="button" onclick="makeRequest('wikia-variable-form2', '<?= $baseurl ?>');" value="Change group for variable" />
   </li>
 </form>
 </div>
</li>
<li>
 <div id="wikia-variable-change3" style="display:inline">
 <form id="wikia-variable-form3">
  <input type="hidden" name="variable_id" value="<?= $variable->cv_id ?>" />
  <input type="hidden" name="variable_level" value="<?= $variable->cv_access_level ?>" />
  <input type="hidden" name="variable_div" value="wikia-variable-change3" />
  <input type="hidden" name="variable_form" value="3" />
  <ul class="wk-form-row">
   <li>
    <label>Access level: <strong><?= $variable->cv_access_level ?> (<?= $accesslevels[$variable->cv_access_level] ?>)</strong></label>
   </li>
   <li>
    <input type="button" onclick="makeRequest('wikia-variable-form3', '<?= $baseurl ?>');" value="Change access level for variable" />
   </li>
  </ul>
 </form>
 </div>
</li>
</ul>
</div>
<!-- e:<?= __FILE__ ?> -->
