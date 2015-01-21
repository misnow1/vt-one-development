
function makeTabActive (tgidx, tidx) {
	// activate the requested tab
	tabid = "#tabs-" + tgidx;
	jQuery(tabid).tabs("option", "active", tidx );
	
	// override the default
	return false;
}

function vtoneTabActivateHandler(event, ui) {
	// Shuffle classes for the sidebar menu
	oldPanelSelector = ui.oldPanel.selector;
	newPanelSelector = ui.newPanel.selector;
	
	oldLinkSelector = oldPanelSelector.replace("#tabs", "a#vtone_tabgroup").replace(/-/g, '_');
	newLinkSelector = newPanelSelector.replace("#tabs", "a#vtone_tabgroup").replace(/-/g, '_');
	
	jQuery(oldLinkSelector).removeClass("active");
	jQuery(newLinkSelector).addClass("active");
	
	// and update the window URL
	var s = History.getState();

	var newHash = newPanelSelector;
	var newURL = s.url.split('#')[0] + newPanelSelector;
	
	History.replaceState(s.data, s.title, newURL)
}

function vtoneTabCreateHandler (event, ui) {
	newPanelSelector = ui.panel.selector;
	newLinkSelector = newPanelSelector.replace("#tabs", "a#vtone_tabgroup").replace(/-/g, '_');
	jQuery(newLinkSelector).addClass("active");
}