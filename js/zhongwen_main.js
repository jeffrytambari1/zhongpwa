'use strict';

if(typeof chrome == 'undefined') {
  var chrome = {};
}

// var data1 = {
//   response: {},
// };

function callback1() {
  // console.log( 'callback1' );
}

function showMessage(text = "", isShow = true) {
  // console.log ( 'showMessage 1' );
  let message_container = document.getElementById("message_container");
  let message1 = document.getElementById("message1");
  message1.textContent = text;
  if(isShow) {
    message_container.style.display = "block";
  } else {
    message_container.style.display = "none";
  }
}

let timeout1; //  = setTimeout(myGreeting, 5000);

function startAutoHideMessage(second = 2) {
  if(timeout1){ clearTimeout(timeout1); }
  timeout1 = setTimeout(autoHideMessage, second * 1000);
}

function autoHideMessage() {
  // showMessage("", false);
  if(timeout1){ clearTimeout(timeout1); }
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

    applyCssSettings();
  },
  messageListeners: [],
  onMessage: {
    addListener: function(callback) {
      customRuntime.messageListeners.push(callback);
    }
  },
  triggerMessageListeners: function(message) {
    customRuntime.messageListeners.forEach(listener => {
      try {
        listener(message, 'sender', customRuntime.currentCallback);
        showMessage("", false);
      } catch (ee) {
        showMessage("Please wait, still loading...");
        console.log ( ee ); // 240616_000401 - debug
        startAutoHideMessage();
      }
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

