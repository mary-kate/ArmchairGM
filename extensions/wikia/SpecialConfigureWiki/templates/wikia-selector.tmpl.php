<!-- s:<?= __FILE__ ?> -->
<?= $qform->toHTML() ?>

<script type="text/javascript">
/*<![CDATA[*/
var domainsArray = [
<?php foreach ($domains as $name => $id): ?>
    [ "<?= $name ?>", "<?= $id ?>"  ],
<?php endforeach ?>
];

// handle autocompletion
var wkDataSource = new YAHOO.widget.DS_JSArray( domainsArray );
var wkAutoComp = new YAHOO.widget.AutoComplete("citydomain","var-autocomplete", wkDataSource);
wkAutoComp.maxCacheEntries = 60;
wkAutoComp.queryMatchContains = true;
wkAutoComp.queryDelay = 0;
wkAutoComp.typeAhead = true;
wkAutoComp.queryMatchSubset = true;

/*]]>*/
</script>

<!-- e:<?= __FILE__ ?> -->