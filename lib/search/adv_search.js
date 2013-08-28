/*
 * Copyright 2006 Wikia, Inc.  All rights reserved.
 * Use is subject to license terms.
 */

function advsearchWikiChanged() {
	var wikibox = document.getElementById('wikiasearchotherwikibox');
	var wikiopt = document.getElementById('local');

	if (wikiopt.value == 2)
		wikibox.className = 'shown';
	else
		wikibox.className = 'hidden';
}

function advsearchInit() {
	var wikiopt = document.getElementById('local');
	wikiopt.addEventListener("change", advsearchWikiChanged, false);
	advsearchWikiChanged()
}

window.addEventListener("load", advsearchInit, false);
