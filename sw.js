importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.2/workbox-sw.js');

workbox.setConfig({
  debug: false,
});

workbox.loadModule('workbox-core');

const cacheName = 'ZhongPWA_v2';

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
  'zhongwen/data/cedict.idx',
  'zhongwen/data/cedict_ts.u8',
  'zhongwen/data/grammarKeywordsMin.json',
  'zhongwen/data/vocabularyKeywordsMin.json',
  'zhongwen/images/zhongwen.png',
  'zhongwen/images/zhongwen16.png',
  'zhongwen/images/zhongwen48.png',
  'zhongwen/js/bootstrap.min.js',
  'zhongwen/js/dataTables.bootstrap4.min.js',
  'zhongwen/js/jquery-3.3.1.min.js',
  'zhongwen/js/jquery.dataTables.min.js',
  'zhongwen/js/options.js',
  'zhongwen/js/wordlist.js',
  'zhongwen/js/zhuyin.js',
  // 'index.php',
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(cacheName).then((cache) => {
    return cache.addAll(precachedAssets);
  }));
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  const isPrecachedRequest = precachedAssets.includes(url.pathname);

  if (isPrecachedRequest) {
    event.respondWith(caches.open(cacheName).then((cache) => {
      return cache.match(event.request.url);
    }));
  } else {
    return;
  }
});


