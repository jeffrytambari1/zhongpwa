

$(document).ready(function(ev) {
  console.log ( 'zhongwen_second.js jquery ready' );

  setTimeout(function() {
    let tabId = 1;
    
    chrome.browserAction.clickedListener(tabId);

    chrome.tabs.activatedListener( {tabId: tabId} );

    let config = {};
    config.css = 'yellow';
    config.toneColorScheme = 'standard';
    config.originalText = [];
    chrome.runtime.sendMessage({ type: 'enable', config: config, originalText: [] }, callback1);
  }, 500);

});



