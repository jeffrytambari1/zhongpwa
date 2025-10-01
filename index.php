<?php 
$env = parse_ini_file('.env');
$base_url = $env["BASE_URL"];

$week2 = Date('y_W');

$dialogues_directory ="dialogues";
function getRandomFileFromDirectory($directory) {
  $filesAndDirs = scandir($directory);
  $files = array_filter($filesAndDirs, function($item) use ($directory) {
    return is_file($directory . DIRECTORY_SEPARATOR . $item);
  });
  if (empty($files)) { return ''; }
  $randomFileKey = array_rand($files);
  $randomFile = $files[$randomFileKey];
  return $randomFile;
}
function readJsonFile($filePath) {
  if (!file_exists($filePath)) { return null; }
  $jsonContent = file_get_contents($filePath);
  if ($jsonContent === false) { return null; }
  return json_decode($jsonContent, true);
}
$dialogues_file = getRandomFileFromDirectory($dialogues_directory);
$dialogues_file_path = "{$dialogues_directory}/{$dialogues_file}";
$dialogues_content = readJsonFile($dialogues_file_path) ?? '{}';

function extractSentences($data) {
  if(!is_array($data)) { return []; }
  return array_map(function($item) {
    return [
      'sentences' => array_map(function($sentenceItem) {
        return [
          // 'sentence' => $sentenceItem['sentence'],
          // 'trsl_sentence' => $sentenceItem['trsl_sentence'],
          'orgnc_sentence' => $sentenceItem['orgnc_sentence'],
          'trsl_orgnc_sentence' => $sentenceItem['trsl_orgnc_sentence'],
          'speaker' => $sentenceItem['speaker'],
        ];
      }, $item['sentences']),
      'title' => $item['title'],
      'title_translation' => $item['title_translation'],
    ];
  }, $data);
}

$dialogues_content2 = extractSentences($dialogues_content);
$directory_directory ="directory";

function getFileListFromDirectoryRecursive($directory) {
    $excludedFiles = ['.', '..', '.git', '.gitignore', '.DS_Store', 'Thumbs.db'];
    $includedFileExtensions = ['txt', 'csv'];
    $allFiles = [];
    $scanDirectory = function($dir) use (&$scanDirectory, &$allFiles, $excludedFiles, $includedFileExtensions) {
      $filesAndDirs = scandir($dir);
      foreach ($filesAndDirs as $item) {
        if (in_array($item, $excludedFiles)) { continue; }
        $filePath = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_file($filePath) && in_array(pathinfo($filePath, PATHINFO_EXTENSION), $includedFileExtensions)) {
          $allFiles[] = $filePath;
        } elseif (is_dir($filePath)) {
          $scanDirectory($filePath);
        }
      }
    };
    $scanDirectory($directory);
    if (empty($allFiles)) { return []; }
    natsort($allFiles);
    return array_values($allFiles);
}

$directory_file_list = getFileListFromDirectoryRecursive($directory_directory);

$popup_background_colors = [
  [ 'color' =>"#ffffbf", 'title' => "Default Color" ],
  [ 'color' =>"yellow" ],
  [ 'color' =>"cyan" ],
  [ 'color' =>"lime" ],
  [ 'color' =>"rgb(238 176 176)" ],
  [ 'color' =>"rgb(239 130 249)" ],
];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <script type="text/javascript">
      const base_url = "<?php echo $base_url; ?>";
    </script>
    <!-- <base href="<?php /* echo $base_url; */ ?>" /> -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link id="favicon" rel="icon" href="favicon.ico" type="image/x-icon">
  	<link rel="apple-touch-icon" href="images/apple-touch.png" />

  	<link rel="manifest" href="manifest.json?v3" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, shrink-to-fit=no" /> -->
    <link rel="apple-touch-icon" href="images/icon-192x192.png" />
    <meta name="theme-color" content="#ABCDEF" />
    <meta name="msapplication-TileColor" content="#ABCDEF"/>
    <meta name="msapplication-TileImage" content="images/ms-icon-144x144.png"/>
    <meta name="application-name" content="ZhongPWA" />
    <meta name="description" content="Zhong PWA for zhongwen extension." />
    <meta name="author" content="Jeffry Tambari" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

    <title>Zhong PWA</title>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/todo_style.css?<?php echo $week2; ?>" />

    <!-- zhongwen-extension -->
    <link rel="stylesheet" type="text/css" href="zhongwen/css/content.css" />
    <link rel="stylesheet" type="text/css" href="css/content_example.css?<?php echo $week2; ?>" />
    <link rel="stylesheet" href="css/zhongpwa_style.css?<?php echo $week2; ?>" />
  </head>
  <body style="
    background-color: #212121 !important;
  ">
    <div aria-live="polite" aria-atomic="true" class="bg-dark position-relative bd-example-toasts">
      <div class="toast-container position-fixed p-3 top-0 end-0" id="toastPlacement">
        <div id="divToast" class="toast align-items-center bg-success text-white border-0 toast1" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="1000000">
          <div class="d-flex">
            <div class="toast-body toast_body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"
              style="
                margin: auto !important;
                margin-right: .5rem !important;
              " 
            ></button>
          </div>
        </div>
      </div>
    </div>
    <div id="offlineInfo" style="display: none;">
      <img scr="images/icons/wifi_off.png" class="wifi_off2" id="wifi_off2" /> You're Offline
    </div>

    <header class="navbar navbar-expand navbar-dark flex-column flex-md-row bd-navbar header0 header1" id="header1">
      <div class="col-12 padding0">
        <div class="input-group">
          <input value="" class="form-control txt_url" type="text" id="txt_url" placeholder="eg. https://zh.wikipedia.org/wiki/Wikipedia:首页" style="
              height: 38px !important;
              padding: 2px 16px !important;
              box-sizing: content-box !important;
              border-top-left-radius: 50px !important;
              border-bottom-left-radius: 50px !important;
              background-color: #303030 !important;
              color: white !important;
              font-family: var(--bs-body-font-family) !important;
            " />

          <span class="input-group-btn" style="height: 38px !important;">
            <button class="btn btn-default button2 btn_go" type="submit" id="btn_go" style="
                height: 38px !important;
                width: 40px !important;
                padding: 2px 4px !important;
                background-color: white !important;
                border-radius: 5px !important;
                box-sizing: content-box !important;
              " >
              <i class="icon1 icon_right" style="
                  height: 15px !important;
                  width: 15px !important;
                " ></i>
            </button>
            <button class="btn btn-default button2 btn_reload" type="button" id="btn_reload" style="
                height: 38px !important;
                width: 40px !important;
                padding: 2px 4px !important;
                background-color: white !important;
                border-radius: 5px !important;
                box-sizing: content-box !important;
              " >
              <i class="icon1 icon_reload" style="
                  height: 15px !important;
                  width: 15px !important;
                " ></i>
            </button>
          </span>
        </div>
      </div>
    </header>

    <main class="bd-masthead main0 main1" id="content" role="main" style="height: auto; width: 100vw;">
      <div class="row h-100 padding0">
        <div class="col-12 h-100 padding0 frm1_container">
          <div id="div1" class="col-12 padding0">
            <div id="message_container" class="message_container" style="display: none;">
              <div id="message1" class="message1">
              </div>
            </div>
          </div>
          <div id="div2" class="col-12 padding0" style="visibility: hidden; background-color: white; width: 100vw;">

            <h3 class="heading1">Nǐ hǎo - 你好</h3>
            <div class="prewords" style="padding: 0; padding-top: 7px;">
              <div class="welcome1">
                欢迎用户。 &nbsp; Huānyíng yònghù. &nbsp; Welcome User. &nbsp; Selamat Datang Pengguna.
              </div>
              <div class="description1">
                This is Zhong PWA (Progressive Web Application) for learning Chinese. <br/>
                <b>How to use:</b>
                <ol style="padding-left: 18px;">
                  <li>
                    Fill textbox with Mandarin site url <br/>
                    <div class="container2">
                      <b class="">
                        <a href="#" id="toggleList" class="text-decoration-none">
                          Recommended Mandarin sites: <span class="ms-2" id="toggleIcon">[&nbsp;➕&nbsp;]</span>
                        </a>
                      </b>
                      <div id="websiteList" class="border rounded bg-light ps-2" style="
                          display: none;
                          " >
                        <ul id="ul_url_list1" class="ul_url_list">
                          <li>https://zh.m.wikipedia.org/wiki/印度尼西亚</li>
                          <li>https://zh.wikipedia.org/wiki/雅加达</li>
                          <li>https://www.bbc.com/zhongwen/articles/c9wdnd1ynd8o/simp</li>
                          <li>https://www.bbc.com/zhongwen/simp/world-66141140</li>
                          <li>https://www.rfa.org/mandarin</li>
                          <li>https://scdaily.com/</li>
                          <li>https://www.douban.com/</li>
                          <li>https://www.setn.com</li>
                          <li>https://www.hk01.com/%E5%8D%B3%E6%99%82%E5%9C%8B%E9%9A%9B/1085780/%E5%8D%B0%E5%B0%BC%E5%A4%AE%E8%A1%8C%E7%96%91%E6%B6%89%E6%8C%AA%E7%94%A8%E5%85%AC%E6%AC%BE-%E8%A1%8C%E9%95%B7%E8%BE%A6%E5%85%AC%E5%AE%A4%E9%81%AD%E5%8F%8D%E8%B2%AA%E6%A9%9F%E6%A7%8B%E6%90%9C%E6%9F%A5?itm_source=universal_search&itm_campaign=hk01&itm_content=all&itm_medium=web</li>
                          <li>https://news.mingpao.com/pns/%e8%a7%80%e9%bb%9e/article/20250929/s00012/1759075905107/%e7%ad%86%e9%99%a3-%e6%90%b6%e5%a5%aa%e5%b0%8d%e6%97%a5%e9%97%9c%e4%bf%82%e4%b8%bb%e5%b0%8e%e6%ac%8a-%e5%9c%8b%e6%b0%91%e9%bb%a8%e4%b8%89%e5%9c%98%e5%a4%a7%e9%99%a3%e4%bb%97%e8%a8%aa%e6%97%a5%e7%9a%84%e6%b7%b1%e5%b1%a4%e6%84%8f%e7%be%a9-%e6%96%87-%e6%9e%97%e6%b3%89%e5%bf%a0</li>
                          <li>https://www.stheadline.com/breaking-news/3503909/%E5%B1%B1%E9%A0%82%E7%92%B0%E7%BF%A0%E5%9C%92%E5%96%AE%E4%BD%8D%E9%81%AD%E7%88%86%E7%AB%8A-%E4%BC%B0%E8%A8%88%E6%90%8D%E5%A4%B1500%E8%90%AC%E7%8F%A0%E5%AF%B6</li>
                          <li>https://www.rthk.hk</li>
                        </ul>
                      </div>
                    </div>
                  </li>
                  <li>
                    Press <i class="icon1 icon_right" style="height: 15px !important; width: 15px !important;"></i> to load
                  </li>
                  <li>
                    Press <i class="icon1 icon_reload" style="height: 15px !important; width: 15px !important;"></i> to reload this page
                  </li>
                  <li>Hover/touch each mandarin text (eg. 中文) with mouse/finger, will showing English translation in pop-up window</li>
                </ol>

                <b>Note:</b>
                <ol style="padding-left: 18px;">
                  <li>This app use desktop Chrome Extension "Zhongwen: Chinese-English Dictionary", which only works in desktop.</li>
                  <li>This app works on Android/iOS mobile phone.</li>
                  <li>Learn more from HSK Files & Dialogues</li>
                </ol>

                <b>Contact:</b>
                <ol style="padding-left: 18px;">
                  <li>Github repo <a href="https://github.com/jeffrytambari1/zhongpwa" target="_blank" rel="noopener">source</a></li>
                  <li>📧 <a href="https://vrhythm.net/#contact" target="_blank" rel="noopener">Contact me</a> if you have question or business inquiry.</li>
                  <li>💖 <a href="https://vrhythm.net/zhong_pwa/" target="_blank" rel="noopener">Support My Work</a> - If you find this app useful 🙏</li>
                </ol>

              </div>
            </div>

            <h3 class="heading1">Files</h3>
            <div class="files">
              Learn Mandarin from HSK Vocabulary list. <br>
              Press <i class="icon1 icon_right" style="height: 15px !important; width: 15px !important;"></i> to view<br>
              Press <i class="icon1 icon_reload" style="height: 15px !important; width: 15px !important;"></i> to reload this page
              <ul id="ul_url_list2" class="ul_url_list" style="height: 250px;">
                <li>hsk/hsk_1_vocabulary_list.txt</li>
                <li>hsk/hsk_2_vocabulary_list.txt</li>
                <li>hsk/hsk_3_vocabulary_list.txt</li>
                <li>hsk/hsk_4_vocabulary_list.txt</li>
                <li>hsk/hsk_5_vocabulary_list.txt</li>
                <li>hsk/hsk_6_vocabulary_list.txt</li>
                <li>hsk/hsk_official_with_definitions_2012_L6.txt</li>
                <li>lyrics/jay_chou__ting_ma_ma_de_hua.txt</li>
                <li>lyrics/Xie_Xie_Ni_Di_Ai_-_Andy_Lau.txt</li>
              </ul>
            </div>

            <!-- 251001_143155 - disabled -->
            <!-- <h3 class="heading1">Directory</h3>
            <div class="directory">
              <ul id="ul_url_list3" class="ul_url_list" style="height: 250px;">
                <?php 
                // foreach ($directory_file_list as $df_ky => $df_dt) {
                //   echo "<li>{$df_dt}</li>";
                // }
                ?>
              </ul>
            </div> -->

            <h3 class="heading1">Saved URL</h3>
            <div class="saved_urls">
              <main class="todo_app" id="div_todo_app">
                <div style="display: none;">
                  <span id="todo_date"></span>
                </div>
                <div class="input-header">
                  <div class="input-group to-do-input">
                    <input type="text" id="todo_item" placeholder="Enter an URL..." style="">
                    <span class="input-group-btn" style="width: 40px;">
                      <button id="todo_enter" type="button" style="width: 40px;">Enter</button>
                    </span>
                  </div>
                </div>
                <ul id="todo_list" class="to-do-list ul_url_list" style="height: 250px;">
                </ul>
              </main>
            </div>

            <h3 class="heading1">Dialogues</h3>
            <div class="dialogues1">
              <main class="dialogues_main" id="div_dialogues">
                <!-- divLine = div_dialogues
                line = dialogues_main -->
                <div class='dialogues'></div>
              </main>
            </div>

            <h3 class="heading1">Settings</h3>
            <div class="settings">
              <div class="row row0 mb-4">
                <div class="col-12 col-md-4 col0">
                  <div class="row row0">
                    <div class="col-12 col0">
                      Font Size
                    </div>
                    <div class="col-12 col0">
                      <div class="row row0">
                        <div class="col-4">
                          <div class="d-grid div_button3">
                            <button id="increase_font_size" type="button" class="btn btn-warning button3">+</button>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="d-grid div_button3">
                            <button id="decrease_font_size" type="button" class="btn btn-warning button3">-</button>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="d-grid div_button3">
                            <button id="reset_font_size" type="button" class="btn btn-warning button3">Reset</button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col0" style="margin-top: 20px;">
                      Background Color
                    </div>
                    <div class="col-12 col0">
                      <div class="row row0">
                        <div class="col-12">
                          <div class="row row0">
                            <?php 
                            foreach ($popup_background_colors as $pbc_ky => $pbc_dt) {
                              $color = $pbc_dt['color'];
                              $title = $pbc_dt['title'] ?? '';

                              echo "<div class='col-2'>
                                <div class='d-grid div_button3'>
                                  <button id='' type='button' class='btn button_background_color' data-color_value='{$color}' style='background-color: {$color};' title='{$title}'></button>
                                </div>
                              </div>";
                            }
                            ?>
                          </div>
                        </div>

                      </div>
                    </div>

                  </div>
                  
                </div>
                <div class="col-12 col-md-8 col0">

                  <div id="zhongwen_window_example" class="background-yellow tonecolor-standard"><span class="w-hanzi" style="font-size: 8px;">你好</span>&nbsp;<span class="w-pinyin tone3" style="font-size: 8px;">nǐ</span>&nbsp;<span class="w-pinyin tone3" style="font-size: 8px;">hǎo</span><br><span class="w-def" style="font-size: 8px;">hello ◆ hi</span><br><span class="w-hanzi" style="font-size: 8px;">你</span>&nbsp;<span class="w-pinyin tone3" style="font-size: 8px;">nǐ</span><br><span class="w-def" style="font-size: 8px;">you (informal, as opposed to courteous 您[nin2])</span><br></div>

                </div>
              </div>
            </div>



          </div>

        </div>
      </div>

      <div id="loading1" class="loading1" style="display: none;">
        <img id="loading-image" class="loading-image" src="images/loading.gif" alt="Loading..." />
      </div>

      <div class="floating_action_button share" id="fab1" style="display: none;">
        <div class="main">
          <!-- <i class="fa-solid fa-headset"></i> -->
          <i class="fa fa-align-justify"></i>
        </div>

        <div class="secondary random" title="Random Scroll/Line for Learning/Memorizing HSK Files">
          <i class="fa fa-random"></i>
        </div>

        <div class="secondary tab_space" title="Convert Tab To More Spaces">
          <i class="fa fa-square-full"></i>
        </div>

        <div class="secondary plus_font_size">
          <i class="fa fa-add"></i>
        </div>

        <div class="secondary minus_font_size">
          <i class="fa fa-minus"></i>
        </div>

        <div class="secondary enable_counting">
          <i class="fa fa-3"></i>
        </div>

        <div class="secondary go_to_top">
          <i class="fa fa-arrow-up"></i>
        </div>
        
      </div>


    </main>

    <footer class="bd-footer text-muted" style="height: 2px;">
      <div style="font-size: 12px !important; padding-left: 5px !important; color: white !important;">
        good words. 
        <a href="https://vrhythm.net/using-zhong-pwa/" style="
            color: rgba(var(--bs-link-color-rgb), var(--bs-link-opacity, 1));
            text-decoration: underline;
            " >help</a>
        <i class="bi bi-0-circle-fill"></i>
      </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="plugins/popper.min.js"></script>

    <script type="text/javascript">
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('sw.js?v3')
            .then((registration) => {
              console.log('ServiceWorker registration successful with scope: ', registration.scope);
              registration.onupdatefound = () => {
                const installingWorker = registration.installing;
                installingWorker.onstatechange = () => {
                  switch (installingWorker.state) {
                    case 'installed':
                      if (navigator.serviceWorker.controller) {
                        console.log('New content is available; please refresh.');
                      } else {
                        console.log('Content is cached for offline use.');
                      }
                      break;
                    case 'redundant':
                      console.error('The installing service worker became redundant.');
                      break;
                    default:
                      // Ignore
                  }
                };
              };
            })
            .catch((error) => {
                console.error('ServiceWorker registration failed: ', error);
            });

          navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('Controller has changed');
          });

        });


      }
      const module = {};
    </script>

    <script type="text/javascript">
      let offlineInfo = document.getElementById("offlineInfo");
      function toggleOfflineInfo(isHidden = true) {
        if(isHidden) {
          offlineInfo.style.display = 'none';
        } else {
          offlineInfo.style.display = 'block';
        }
      }
      function init_handle_network_change() {
        window.addEventListener("load", () => {
          function handleNetworkChange(event) {
            toggleOfflineInfo(navigator.onLine);
          }
          window.addEventListener("online", handleNetworkChange);
          window.addEventListener("offline", handleNetworkChange);
          // handleNetworkChange();
        });
      }
      init_handle_network_change();
    </script>

    <script type="text/javascript" src="js/zhongwen_main.js?<?php echo $week2; ?>"></script>
    <script type="module" src="zhongwen/js/zhuyin.js"></script>
    <script type="module" src="zhongwen/dict.js"></script>
    <script type="module" src="zhongwen/background.js"></script>
    <script type="text/javascript" src="zhongwen/content.js"></script>
    <script type="text/javascript" src="js/zhongwen_ready.js?<?php echo $week2; ?>"></script>
    
    <script type="text/javascript" src="js/todo_list.js?<?php echo $week2; ?>"></script>

    <script type="text/javascript">
      var btn_go = $("#btn_go");
      var btn_reload = $("#btn_reload");
      var txt_url = $("#txt_url");
      var frm1 = $("#frm1");
      var header1 = $("#header1");
      var loading1 = $("#loading1");
      var div2 = $("#div2");
      var form1 = $(".form1");
      const hh1 = $(window).height();
      const ww1 = $(window).width();
      let header1_hh = 0;
      var data1 = {
        domain_name: "",
        protocol: "",
      };
      var data_settings = {};
      var default_data_settings = {
        'font_size' : [
          { selector: ' .w-hanzi', css: {'font-size' : '18px'}, },
          { selector: ' .w-pinyin', css: {'font-size' : '16px'}, },
          { selector: ' .w-def', css: {'font-size' : '12px'}, },
          { selector: ' .w-hanzi-small', css: {'font-size' : '18px'}, },
          { selector: ' .w-pinyin-small', css: {'font-size' : '16px'}, },
          { selector: ' .w-def-small', css: {'font-size' : '12px'}, },
        ],
        'background_color' : [
          { selector: '', css: {'background-color' : '#ffffbf'}, },
          { selector: ' span', css: {'background-color' : '#ffffbf'}, },
        ],
      };


      $.fn.multiline = function(text){
        this.text(text);
        // this.html(this.html().replace(/\n/g,'<br/>'));
        this.html("<p>" + this.html().replace(/\n/g,'</p><p>') + "</p>");
        return this;
      }

      function clone1(obj) {
        return $.extend(true, {}, obj);
      }

      function isValidUrl(string) {
        try {
          new URL(string);
          return true;
        } catch (err) {
          return false;
        }
      }

      function setProtocolDomainFromUrl(url) {
        let domain = (new URL(url));
        data1.domain_name = domain.hostname;
        data1.protocol = domain.protocol;
      }

      function getProtocolDomain() {
        return window.location.origin;
      }

      function getFullUrl(path) {
        if(isValidUrl(path)) { return path; }
        if(path.substring(0, 2) === "//") { return data1.protocol + path; } // url without https:
        path = "/" + ( path.startsWith("/") ? path.substring(1) : path );
        return data1.protocol + "//" +  data1.domain_name + path;
      }

      function set_saved_urls_list_item_click() {
        $("#div_todo_app .ul_url_list").find('li input').on('click', function(ee, cc) {
          let li_input = $(this);
          let str = li_input.val();
          txt_url.val(str);
        });
      }

      function fetchSettings() {
        data_settings = localStorage.getItem('data_settings') ? JSON.parse(localStorage.getItem('data_settings')) : clone1(default_data_settings);
      }

      function saveSettings() {
        localStorage.setItem('data_settings', JSON.stringify(data_settings));
      }
      function applyCssSettings() {
        $.each(data_settings, function(ii, setting) {
          $.each(setting, function(jj, element) {
            // console.log ( element );
            $("#zhongwen-window" + element.selector).css(element.css);
            $("#zhongwen_window_example" + element.selector).css(element.css);
          });
        });
      }

      function changeFontSizePopUp(addition) {
        $.each(data_settings.font_size, function (ii, element) {
          let old_fz = element.css['font-size'];
          let new_fz = parseInt(old_fz) + addition;
          data_settings.font_size[ii].css = {'font-size' : new_fz + 'px'};;
        }); 
        saveSettings();
      }
      function changeBackgroundColor(color) {
        data_settings['background_color'] = [
          { selector: '', css: {'background-color' : color}, },
          { selector: ' span', css: {'background-color' : color}, },
        ];
        saveSettings();
      }
      function get_url_extension( url ) {
        return url.split(/[#?]/)[0].split('.').pop().trim();
      }

      $(document).ready(function() {
        frm1.parent().parent().parent().css("height", hh1 - header1.height() - 30 + 'px');

        btn_go.on('click', function() {
          loading1.show();

          let is_success = true;
          fetch(base_url + "/main.php?url=" + txt_url.val())
            .then(response => {
                is_success = response.status == 200;
                return response.text();
              })
            .then(data => {
              let html1 = data;

              let is_file_txt = get_url_extension(txt_url.val()) == 'txt';
              let is_html = !is_file_txt && ( $('<div>').html(html1).children().length > 0 );
              let is_show_fab1 = !is_html;
              let url2 = '';
              if(is_html){
                html1 = html1.replace(/<\!DOCTYPE[^>]*>/gi,"<!-- doctype1 -->")
                  .replace(/<html[^>]*>/gi,"<div class=\"html1\">").replace(/<\/html[^>]*>/gi,"<\/div>")
                  .replace(/<head[^>]*>/gi,"<div class=\"head1\" no_hidden>").replace(/<\/head[^>]*>/gi,"<\/div>")
                  // .replace(/<script[^>]*>/gi,"<div class=\"script1\" hidden>").replace(/<\/script[^>]*>/gi,"<\/div>")
                  //   .replace(/<body[^>]*>/gi,"<div class=\"body1\">").replace(/<\/body[^>]*>/gi,"<\/div>");
                  // // .replace(/<a[^>]*>/gi,"<aa class=\"a1\">").replace(/<\/a[^>]*>/gi,"<\/aa>")
                  .replace(/<a\b([^>]*)>/gi,'<aa$1>').replace(/<\/a\b\s*>/gi, '</aa>')
                  ;

                div2.html(html1);

                url2 = txt_url.val();
              } else {
                div2.multiline(html1);
                div2.addClass('text_result');
                url2 = getProtocolDomain();
              }

              if(url2 == '') { url2 = window.location.href; }
              if(!is_success) { loading1.hide(); return; }

              setProtocolDomainFromUrl(url2);

              $('aa').off('click').on('click', function(ee, bb) {
                let aa = $(this);
                let href = aa.attr('href');
                txt_url.val("..."); // to show url has changed
                setTimeout(function() {
                  txt_url.val(getFullUrl(href));
                }, 500);
                ee.preventDefault();
                // btn_go.click();
              });

              let srcNodeList = div2.get(0).querySelectorAll('[src],[href]');
              for (let ii = 0; ii < srcNodeList.length; ++ii) {
                let item = srcNodeList[ii];
                if(item.getAttribute('src') !== null) {
                  let url = getFullUrl(item.getAttribute('src'));
                  item.setAttribute('src', url)
                } 
                if(item.getAttribute('href') !== null) {
                  let url = getFullUrl(item.getAttribute('href'));
                  item.setAttribute('href', url)
                }
              }

              if(is_show_fab1) { fab1.obj.show(); }
              loading1.hide();
            })
            .catch(error => console.error(error));
        });

        btn_reload.on('click', function() {
          location.href = "/";
          // window.location.reload();
        });

        txt_url.keypress(function(ee){
          if(ee.keyCode == 13){
            btn_go.click();
          }
        });

        $(".ul_url_list").find('li').on('click', function(ee, cc) {
          let li = $(this);
          txt_url.val(li.text());
        });


        $("#increase_font_size").on('click', function() {
          changeFontSizePopUp(1);
          applyCssSettings();
        });

        $("#decrease_font_size").on('click', function() {
          changeFontSizePopUp(-1);
          applyCssSettings();
        });

        $("#reset_font_size").on('click', function() {
          data_settings = clone1(default_data_settings);
          saveSettings();
          applyCssSettings();
        });

        $(".button_background_color").on('click', function() {
          let btn = $(this);
          let color = btn.attr("data-color_value");
          changeBackgroundColor(color);
          applyCssSettings();
        });



        div2.accordion({
          collapsible: true,
          autoHeight: false,
          navigation: true,
          heightStyle: "content",
        });
        div2.css('visibility', 'visible');

        fetchSettings();
        applyCssSettings();

        form1.on('submit', function(ee) {
          ee.preventDefault();
        });

        txt_url.focus();


      });
    </script>

    <script type="text/javascript">
      const dialogues_content = <?php echo json_encode($dialogues_content2, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> ;

      function init_dialogues1() {
        if(json_url) {
          fetchJSONDataDialogues(json_url);
        } else {
          $(`#div_dialogues .dialogues`).html("No Dialogue Found");
        }
      }
      async function fetchJSONDataDialogues(url) {
        try {
          const response = await fetch(url);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          const data = await response.json();
          displayDialogues(data);
        } catch (error) {
          console.error('Error fetching data: ', error);
        }
      }
      function displayDialogues(data) {
        let str = '';
        const date1 = "20150118";
        Object.keys(data).sort().forEach(key => {
          if (key.startsWith("todayExpr_")) {
            const dialogues = data[key];

            str += `<div class="dialogue">`;

            const date2 = key.replace("todayExpr_", "");
            const dayDifference = getDayDifference(date1, date2);

            str += `<div class="dialogue_title">
              <span class="dialogue_number">${dayDifference}</span> &bull; 
              <span class="mandarin_title">${dialogues.title_translation}</span> &bull; 
              <span class="english_title">${dialogues.title}</span>
            </div>`;


            str += `<div class="dialogue_sentences">`;
            dialogues.sentences.forEach(dialogue => {
              str += `
                <div class="line">
                  <span class="speaker speaker_${dialogue.speaker}">${dialogue.speaker}</span> :  
                  <span class="mandarin">${dialogue.trsl_orgnc_sentence}</span> &bull; 
                  <span class="english">${dialogue.orgnc_sentence}</span>
                </div>
                <br />`;
            });
            str += `</div>`; // dialogue_sentences

            str += `</div>`;
          }
        });
        $(`#div_dialogues .dialogues`).html(str);
      }
      // Function to convert date string (YYYYMMDD) to Date object
      function parseDate(dateString) {
        const year = parseInt(dateString.slice(0, 4), 10);
        const month = parseInt(dateString.slice(4, 6), 10) - 1; // Months are 0-based in JavaScript
        const day = parseInt(dateString.slice(6, 8), 10);
        return new Date(year, month, day);
      }
      // Calculate day difference between two dates
      function getDayDifference(date1String, date2String) {
        const date1 = parseDate(date1String);
        const date2 = parseDate(date2String);
        const timeDifference = date2 - date1; // Difference in milliseconds
        const dayDifference = timeDifference / (1000 * 60 * 60 * 24); // Convert milliseconds to days
        return dayDifference;
      }

      $(document).ready(function() {
        // init_dialogues1();
        displayDialogues(dialogues_content);
      });

    </script>

    <script type="text/javascript">
      var fab1 = { // floating_action_button
        id: 'fab1',
        cls: 'share',
        main_button: { cls: 'main', isClicked: false },
        secondary_buttons: {
          random: { cls: 'random', is_show_notif: true },
          tab_space: { cls: 'tab_space', is_show_notif: true },
          plus_font_size: { cls: 'plus_font_size' },
          minus_font_size: { cls: 'minus_font_size' },
          enable_counting: { cls: 'enable_counting', is_enable_counting: false },
          go_to_top: { cls: 'go_to_top' },

        }
      };

      function getElementPositionValue(obj, type = 'top', isRelative = true) { // type = top/bottom/left/right/all, relative/absolute
        let ret;
        if(isRelative) {
          let rel_height = $(document).height(); // returns height of HTML document
          let rel_top_value = obj.position().top + obj.offset().top + obj.outerHeight(true);
          let rel_bottom_value = rel_height - rel_top_value;
          let rel_width = $(document).width(); // returns width of HTML document
          let rel_left_value = obj.position().left + obj.offset().left + obj.outerWidth(true);
          let rel_right_value = rel_width - rel_left_value;
          if(type == 'top') { ret = rel_top_value; }
          else if(type == 'bottom') { ret = rel_bottom_value; }
          else if(type == 'left') { ret = rel_left_value; }
          else if(type == 'right') { ret = rel_right_value; }
          else { ret = { top: rel_top_value, bottom: rel_bottom_value, left: rel_left_value, right: rel_right_value }; }
        } else {
          let abs_top_value = parseInt(obj.css('top'), 10); // specify radix to prevent unpredictable behavior
          let abs_bottom_value = parseInt(obj.css('bottom'), 10);
          let abs_left_value = parseInt(obj.css('left'), 10);
          let abs_right_value = parseInt(obj.css('right'), 10);
          if(type == 'top') { ret = abs_top_value; }
          else if(type == 'bottom') { ret = abs_bottom_value; }
          else if(type == 'left') { ret = abs_left_value; }
          else if(type == 'right') { ret = abs_right_value; }
          else { ret = { top: abs_top_value, bottom: abs_bottom_value, left: abs_left_value, right: abs_right_value }; }
        }
        return ret;
      }
      function calculateCoordinatesFromInitialPoint(initialX = 0, initialY = 0, distance, angle) {
        var radians = angle * (Math.PI / 180);
        var x = initialX + distance * Math.cos(radians);
        var y = initialY + distance * Math.sin(radians);
        return { x: x, y: y };
      }
      function setFloatingActionButtonShare() {
        var flag = 0;
        let delay = 100;
        let distance = 170;
        let degree = 90 / ( Object.keys(fab1.secondary_buttons).length - 1 );
        let ii = 0;
        fab1.obj = $(`#${fab1.id}`);
        fab1.main_button.obj = fab1.obj.find(`.${fab1.main_button.cls}`);
        fab1.main_button.bottom = getElementPositionValue(fab1.main_button.obj, 'bottom', false);
        $.each(fab1.secondary_buttons, function(key, btn) {
          btn.obj = fab1.obj.find(`.secondary.${btn.cls}`);
          let degree2 = degree * ii;
          btn.bottom = getElementPositionValue(btn.obj, 'bottom', false);
          btn.right = getElementPositionValue(btn.obj, 'right', false);
          let coordinate2 = calculateCoordinatesFromInitialPoint(btn.right, btn.bottom, distance, degree2);
          btn.bottom2 = coordinate2.y;
          btn.right2 = coordinate2.x;
          ii++;
        });

        fab1.secondary_buttons.random.obj.on('click', function() {
          randomScroll();
        });
        fab1.secondary_buttons.tab_space.obj.on('click', function() {
          convertTabSpace(div2);
        });
        fab1.secondary_buttons.plus_font_size.obj.on('click', function() {
          changeFontSizeTextFiles(div2, +1);
        });
        fab1.secondary_buttons.minus_font_size.obj.on('click', function() {
          changeFontSizeTextFiles(div2, -1);
        });
        fab1.secondary_buttons.enable_counting.obj.on('click', function() {
          fab1.secondary_buttons.enable_counting.obj.is_enable_counting = !fab1.secondary_buttons.enable_counting.obj.is_enable_counting;
          if(fab1.secondary_buttons.enable_counting.obj.is_enable_counting) {
            div2.addClass('enable_counting');
          } else {
            div2.removeClass('enable_counting');
          }
        });
        fab1.secondary_buttons.go_to_top.obj.on('click', function() {
          scrollToTop();
        });

        fab1.obj.find('.main').on('click', function() {
          if(flag == 0) {
            $.each(fab1.secondary_buttons, function(key, btn) {
              btn.obj.animate({
                bottom: btn.bottom2 + 'px',
                right: btn.right2 + 'px',
                opacity: 0.5,
              }, delay);
            });
            fab1.obj.find('.secondary i').delay(delay).fadeIn(3*delay);  
            flag = 1;
            fab1.main_button.isOpen = true;
          } else {
            $.each(fab1.secondary_buttons, function(key, btn) {
              btn.obj.animate({
                bottom: btn.bottom + 'px',
                // left:'50%'
                right: btn.right + 'px',
                opacity: 0.01,
              } , delay);
            });
            fab1.obj.find('.secondary i').delay(delay).fadeOut(delay);
            flag = 0;
            fab1.main_button.isOpen = false;
          }
        });
      };

      function randomScroll() {
        const documentHeight = document.documentElement.scrollHeight;
        const windowHeight = window.innerHeight;
        const randomY = Math.floor(Math.random() * (documentHeight - windowHeight));
        window.scrollTo({
          top: randomY,
          behavior: 'smooth',
        });
      }
      function scrollToTop() {
        window.scrollTo({
          top: 0,
          // behavior: 'smooth',
        });
      }
      function convertTabSpace(obj) {
        let html1 = obj.html();
        obj.html(html1.replaceAll("\t", "&emsp;&emsp;"));
      }
      function changeFontSizeTextFiles(obj, size) {
        var fontSize = parseInt(obj.css("font-size"));
        fontSize = fontSize + size + "px";
        obj.css({'font-size' : fontSize});
      }
      $(document).ready(function() {
        setFloatingActionButtonShare();
      });
    </script>

    <script type="text/javascript">
      (function () {
        try {
          const u = new URL(location.href);
          const title = u.searchParams.get('title') || '';
          const text  = u.searchParams.get('text')  || '';
          const url   = u.searchParams.get('url')   || '';
          const payload = url || text || title; // Android often puts the link in "text"
          if (payload) {
            sessionStorage.setItem('shared_payload', payload);
          }
        } catch (e) {}
      })();
    </script>
    <script type="text/javascript">
      function page_after_load() {
        const payload = sessionStorage.getItem('shared_payload');
        if (!payload) { return; }
        sessionStorage.removeItem('shared_payload');
        txt_url.val(payload);
        btn_go.trigger('click');
      }
      $(document).ready(function() {
        setTimeout(function() {
          page_after_load();
        }, 100);
      });
    </script>

    <script type="text/javascript">
      $(document).ready(function() {
        $("#toggleList").click(function(e){
          e.preventDefault();
          $("#websiteList").slideToggle();
          setTimeout(function() {
            $("#toggleIcon").text(
              $("#websiteList").is(":visible") ? "[ ➖ ]" : "[ ➕ ]"
            );
          }, 500);
        });
      });
    </script>



  </body>
</html>
