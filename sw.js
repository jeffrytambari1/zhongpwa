importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.2/workbox-sw.js');

workbox.setConfig({
  debug: false,
});

workbox.loadModule('workbox-core');

const cacheName = 'ZhongPWA_v1';

const precachedAssets = [
  'images/arrow-right.png',
  'images/home.png',
  'images/reload.png',
  'images/loading.gif',
  'images/icons/1024x1024.png',
  'images/icons/1024x1024_maskable.png',
  'images/icons/128x128.png',
  'images/icons/16x16.png',
  'images/icons/192x192.png',
  'images/icons/256x256.ico',
  'images/icons/256x256.png',
  'images/icons/32x32.png',
  'images/icons/384x384.png',
  'images/icons/40x40.png',
  'images/icons/48x48.png',
  'images/icons/512x512.png',
  'images/icons/64x64.png',
  'images/icons/72x72.png',
  'images/icons/96x96.png',
  'images/icons/apple-touch.png',
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


