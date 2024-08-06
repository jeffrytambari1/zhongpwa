importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.2/workbox-sw.js');

// function getYear_Week(dt) {
//   dt = new Date(Date.UTC(dt.getFullYear(), dt.getMonth(), dt.getDate()));
//   dt.setUTCDate(dt.getUTCDate() + 4 - (dt.getUTCDay()||7));
//   let yearStart = new Date(Date.UTC(dt.getUTCFullYear(),0,1));
//   let weekNo = Math.ceil(( ( (dt - yearStart) / 86400000) + 1)/7);
//   let yy = dt.getFullYear().toString().substr(-2);
//   return `${yy}_${weekNo}`; // return [dt.getFullYear(), weekNo];
// }
// var yy_ww = getYear_Week(new Date());

workbox.setConfig({
  debug: false,
});

workbox.loadModule('workbox-core');

const cacheName = 'ZhongPWA_v3';
// const lastUpdateCache = 'last_update';

const precachedAssets = [
  'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
  'https://code.jquery.com/jquery-3.6.0.js',
  'https://code.jquery.com/ui/1.13.2/jquery-ui.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
  // 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.woff2',
  'favicon.ico',
  'images/256x256.ico',
  'images/16x16.png',
  'images/32x32.png',
  'images/40x40.png',
  'images/48x48.png',
  'images/64x64.png',
  'images/72x72.png',
  'images/96x96.png',
  'images/128x128.png',
  'images/192x192.png',
  'images/256x256.png',
  'images/384x384.png',
  'images/512x512.png',
  'images/1024x1024.png',
  'images/1024x1024_maskable.png',
  'images/apple-touch.png',
  'images/screenshot.png',
  'images/loading.gif',
  'images/icons/arrow-right.png',
  'images/icons/home.png',
  'images/icons/reload.png',
  'images/icons/edit.png',
  'images/icons/delete.png',
  'images/icons/wifi_off.png',
  // 'plugins/jquery-ui/jquery-ui.min.css',
  // 'plugins/bootstrap/css/bootstrap.min.css',
  // 'plugins/jquery/js/jquery-3.6.0.min.js',
  // 'plugins/jquery-ui/jquery-ui.min.js',
  'plugins/popper.min.js',
  // 'plugins/bootstrap/js/bootstrap.min.js',
  'zhongwen/background.js',
  'zhongwen/content.js',
  'zhongwen/dict.js',
  'zhongwen/wordlist.html',
  'zhongwen/css/bootstrap.min.css',
  'zhongwen/css/content.css',
  'zhongwen/css/dataTables.bootstrap4.min.css',
  'zhongwen/css/jquery.dataTables.min.css',
  'zhongwen/css/wordlist.css',
  // 'zhongwen/data/cedict.idx',
  // 'zhongwen/data/cedict_ts.u8',
  'zhongwen/data/grammarKeywordsMin.json',
  'zhongwen/data/vocabularyKeywordsMin.json',
  'zhongwen/images/zhongwen.png',
  'zhongwen/images/zhongwen16.png',
  'zhongwen/images/zhongwen48.png',
  // 'zhongwen/js/bootstrap.min.js',
  // 'zhongwen/js/dataTables.bootstrap4.min.js',
  // 'zhongwen/js/jquery-3.3.1.min.js',
  // 'zhongwen/js/jquery.dataTables.min.js',
  'zhongwen/js/options.js',
  'zhongwen/js/wordlist.js',
  'zhongwen/js/zhuyin.js',
  // 'index.php',
];



// // Function to get the current timestamp
// function getCurrentTimestamp() {
//   return new Date().getTime();
// }

// // Function to get the last update timestamp from the cache
// async function getLastUpdateTimestamp() {
//   const cache = await caches.open(lastUpdateCache);
//   const response = await cache.match('timestamp');
//   if (response) {
//     const lastUpdate = await response.text();
//     return parseInt(lastUpdate, 10);
//   }
//   return 0;
// }

// // Function to set the last update timestamp in the cache
// async function setLastUpdateTimestamp(timestamp) {
//   const cache = await caches.open(lastUpdateCache);
//   const response = new Response(timestamp.toString());
//   await cache.put('timestamp', response);
// }

// // Function to check if a month has passed since the last update
// async function shouldUpdateCache() {
//   const lastUpdate = await getLastUpdateTimestamp();
//   const currentTime = getCurrentTimestamp();
//   const oneMonth = 30 * 24 * 60 * 60 * 1000; // One month in milliseconds
//   return currentTime - lastUpdate > oneMonth;
// }

// Install event - cache assets and set last update timestamp if a month has passed
self.addEventListener('install', async (event) => {
  // const updateCache = await shouldUpdateCache();
  // if (updateCache) {
  event.waitUntil(
    caches.open(cacheName).then((cache) => {
      // return cache.addAll(precachedAssets).then(() => {
      //   return setLastUpdateTimestamp(getCurrentTimestamp());
      // });
      return cache.addAll(precachedAssets);
    }).catch((error) => {
      console.error('Failed to pre-cache assets:', error);
    })
  );
  // }
  self.skipWaiting();
});

// // Activate event - clean up old caches
// self.addEventListener('activate', (event) => {
//   event.waitUntil(
//     caches.keys().then((cacheNames) => {
//       return Promise.all(
//         cacheNames.map((cache) => {
//           if (cache !== cacheName && cache !== lastUpdateCache) {
//             return caches.delete(cache);
//           }
//         })
//       );
//     }).catch((error) => {
//       console.error('Failed to activate new service worker:', error);
//     })
//   );
//   self.clients.claim();
// });

// // Fetch event - serve from cache or fetch from network
// self.addEventListener('fetch', (event) => {
//   const url = new URL(event.request.url);
//   const isPrecachedRequest = precachedAssets.includes(url.href);

//   if (isPrecachedRequest) {
//     event.respondWith(
//       caches.open(cacheName).then((cache) => {
//         return cache.match(event.request).then((response) => {
//           return response || fetch(event.request).then((networkResponse) => {
//             cache.put(event.request, networkResponse.clone());
//             return networkResponse;
//           }).catch((error) => {
//             console.error('Network request failed:', error);
//             throw error;  // Ensure that the error is handled correctly
//           });
//         }).catch((error) => {
//           console.error('Cache match failed:', error);
//           throw error;  // Ensure that the error is handled correctly
//         });
//       }).catch((error) => {
//         console.error('Failed to open cache:', error);
//         throw error;  // Ensure that the error is handled correctly
//       })
//     );
//   } else {
//     event.respondWith(
//       fetch(event.request).then((networkResponse) => {
//         return caches.open(cacheName).then((cache) => {
//           cache.put(event.request, networkResponse.clone());
//           return networkResponse;
//         }).catch((error) => {
//           console.error('Failed to open cache:', error);
//           return networkResponse;  // Return the network response even if caching fails
//         });
//       }).catch((error) => {
//         console.error('Network request failed:', error);
//         return caches.match(event.request).then((cacheResponse) => {
//           return cacheResponse || new Response('Failed to fetch', {
//             status: 408,
//             statusText: 'Request Timeout'
//           });
//         });
//       })
//     );
//   }
// });

self.addEventListener('fetch', function(event) {
  // console.log ( "sw: Fetch" );
  event.respondWith(
    caches.match(event.request)
    .then(
      function(response) {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});

