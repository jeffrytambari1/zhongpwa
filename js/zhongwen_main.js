'use strict';

if(typeof chrome == 'undefined') {
  var chrome = {};
}

var data1 = {
  response: {},
};

function callback1() {
  // console.log( 'callback1' );
}

// to emulate chrome object
const customRuntime = {
  currentCallback: null,
  sendMessage: function(message, callback) {
    // console.log("Custom sendMessage:", message);
    customRuntime.currentCallback = callback;
    customRuntime.triggerMessageListeners(message);

    // if (callback && typeof callback === 'function') {
    //     callback({ response: "Message received!" });
    // }
  },
  messageListeners: [],
  onMessage: {
    addListener: function(callback) {
      customRuntime.messageListeners.push(callback);
    }
  },
  triggerMessageListeners: function(message) {
    customRuntime.messageListeners.forEach(listener => {
      listener(message, 'sender', customRuntime.currentCallback);
    });
  },
  getURL: function(url) {
    // console.log ( 'getURL', url );
    return `zhongwen/${url}`;
  },
};

chrome.runtime = customRuntime;


chrome.browserAction = {
  onClicked: {
    addListener: function(callback) {
      chrome.browserAction.clickedListener = callback;
    }
  },
  clickedListener: null,
  setBadgeBackgroundColor: function(options) {
    // console.log("Custom setBadgeBackgroundColor:", options);
  },

  setBadgeText: function(options) {
    // console.log("Custom setBadgeText:", options);
  },
};


const customTabs = {
  sendMessage: function(message, callback) {
    // console.log("Custom Tabs sendMessage:", message);
    customTabs.triggerMessageListeners(message);
    // if (callback && typeof callback === 'function') {
    //   callback(ret);
    // }
  },
  messageListeners: [],
  onMessage: {
    addListener: function(callback) {
      customTabs.messageListeners.push(callback);
    }
  },
  triggerMessageListeners: function(message) {
    customTabs.messageListeners.forEach(listener => {
      listener(message);
    });
  },
  onActivated: {
    addListener: function(callback) {
      customTabs.activatedListener = callback;
    }
  },
  activatedListener: null,
  onUpdated: {
    addListener: function(callback) {
      customTabs.updatedListener = callback;
    }
  },
  updatedListener: null,
  reload: function(tabId) {
    // console.log(`Custom tabs.reload called for tab ${tabId}`);
    console.log('Custom tabs.reload');
  },
};

chrome.tabs = customTabs;


chrome.contextMenus = {
  create: function(options) {
    // console.log("Custom create:", options);
  },
};


// $(document).ready(function(ev) {
//   // console.log ( 'zhongwen_main.js jquery ready' );
// });

