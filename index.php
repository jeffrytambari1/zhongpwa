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
  if (!file_exists($filePath)) {
    return null;
  }
  $jsonContent = file_get_contents($filePath);
  if ($jsonContent === false) {
    return null;
  }
  return json_decode($jsonContent, true); // Decode JSON as an associative array
}
$dialogues_file = getRandomFileFromDirectory($dialogues_directory);
$dialogues_file_path = "{$dialogues_directory}/{$dialogues_file}";
$dialogues_content = readJsonFile($dialogues_file_path) ?? '{}';
// $dialogues_content2 = [];
// foreach ($dialogues_content as $jc_ky => &$jc_dt) {
//   foreach ($jc_dt['sentences'] as $sn_ky => &$sn_dt) {
//     unset($sn_dt['speaker']);
//   }
// }

// $dialogues_content2 = array_map(function($item) {
//     return [
//         'sentences' => [
//             'speaker' => $item['sentences']['sentence']['speaker'],
//             'orgnc_sentence' => $item['sentences']['sentence']['orgnc_sentence'],
//             'trsl_orgnc_sentence' => $item['sentences']['sentence']['trsl_orgnc_sentence'],
//         ]
//     ];
// }, $dialogues_content);

function extractSentences($data) {
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

// function getFileListFromDirectory($directory) {
//   $excludedFiles = ['.', '..', '.git', '.gitignore', '.DS_Store', 'Thumbs.db'];

//   $filesAndDirs = scandir($directory);
//   $files = array_filter($filesAndDirs, function($item) use ($directory, $excludedFiles) {
//     $filePath = $directory . DIRECTORY_SEPARATOR . $item;
//     // return is_file($filePath) && !in_array($item, $excludedFiles) && pathinfo($filePath, PATHINFO_EXTENSION) === 'txt';
//     return is_file($filePath) && !in_array($item, $excludedFiles) && in_array(pathinfo($filePath, PATHINFO_EXTENSION), ['txt', 'csv']);
//   });
//   if (empty($files)) { return []; }
//   natsort($files);
//   // return $files;
//   return array_values($files);
// }
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

// $directory_file_list = getFileListFromDirectory($directory_directory);
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
    <base href="<?php echo $base_url; ?>" />
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link id="favicon" rel="icon" href="favicon.ico" type="image/x-icon">
  	<link rel="apple-touch-icon" href="images/apple-touch.png" />

  	<link rel="manifest" href="manifest.json?v2" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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

    <!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" /> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" integrity="sha512-ELV+xyi8IhEApPS/pSj66+Jiw+sOT1Mqkzlh8ExXihe4zfqbWkxPRi8wptXIO9g73FSlhmquFlUOuMSoXz5IRw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
    <!-- <link rel="stylesheet" href="plugins/jquery-ui/jquery-ui.min.css" /> -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.min.css" />

    <!-- <link rel="stylesheet" href="plugins/bootstrap/css/bootstrap.min.css" /> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/todo_style.css?<?php echo $week2; ?>" />


    <!-- zhongwen-extension -->
    <link rel="stylesheet" type="text/css" href="zhongwen/css/content.css" />
    <link rel="stylesheet" type="text/css" href="css/content_example.css?<?php echo $week2; ?>" />
    <link rel="stylesheet" href="css/style.css?<?php echo $week2; ?>" />

    <style>
      /*#cursor1 {
        width: 20px;
        height: 20px;
        background-color: black;
        border-radius: 50%;
        position: absolute;
        pointer-events: none;
        display: none;
        z-index: 1000;
      }*/
    </style>

  </head>
  <body>


    <div aria-live="polite" aria-atomic="true" class="bg-dark position-relative bd-example-toasts">
      <!-- <div class="toast-container position-absolute p-3 top-0 end-0" id="toastPlacement"> -->
      <div class="toast-container position-fixed p-3 top-0 end-0" id="toastPlacement">
        <!-- <div class="toast" id="divToast">
          <div class="toast-header">
            <img src="images/72x72.png" class="rounded me-2" alt="icon">
            <strong class="me-auto">Bootstrap</strong>
            <small>11 mins ago</small>
          </div>
          <div class="toast-body">
            Hello, world! This is a toast message.
          </div>
        </div> -->

        <div id="divToast" class="toast align-items-center bg-success text-white border-0 toast1" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body toast_body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>

      </div>
    </div>
    <div id="offlineInfo" style="display: none;">
      <!-- <i class="fa-solid fa-wifi fa-beat"></i> -->
      <!-- <i class="icon1 icon_wifi_off fa-beat2"></i> -->
      <img scr="images/icons/wifi_off.png" class="wifi_off2" id="wifi_off2" />
      <!-- fa-beat2 -->
       You're Offline
    </div>


    <header class="navbar navbar-expand navbar-dark flex-column flex-md-row bd-navbar header0 header1" id="header1">
      <div class="col-12 padding0">
        <div class="input-group">
            <input value="https://zh.wikipedia.org/wiki/Wikipedia:首页" class="form-control txt_url" type="text" id="txt_url">
            <!-- https://zh.wikipedia.org/wiki/Wikipedia:首页 -->
            <span class="input-group-btn">
               <button class="btn btn-default button2 btn_go" type="submit" id="btn_go">
                  <i class="icon1 icon_right"></i>
               </button>
               <!-- <button class="btn btn-default button2 btn_home" type="submit" id="btn_home">
                  <i class="icon1 icon_home"></i>
               </button> -->
               <button class="btn btn-default button2 btn_reload" type="button" id="btn_reload">
                  <i class="icon1 icon_reload"></i>
               </button>
               <!-- <button class="btn btn-default button2 btn_randomY" type="button" id="btn_randomY">
                  <i class="icon1 icon_reload"></i>
               </button> -->
            </span>
        </div>
      </div>

    </header>


    <main class="bd-masthead main0 main1" id="content" role="main" style="height: auto;">
      <div class="row h-100 padding0">
        <div class="col-12 h-100 padding0 frm1_container">
          <div id="div1" class="col-12 padding0">
            <div id="message_container" class="message_container" style="display: none;">
              <div id="message1" class="message1">
              </div>
            </div>
          </div>
          <div id="div2" class="col-12 padding0" style="visibility: hidden;">

            <h3 class="heading1">Nǐ hǎo - 你好</h3>
            <div class="prewords" style="padding: 0; padding-top: 7px;">
              <div class="welcome1">
                欢迎用户。 &nbsp; Huānyíng yònghù. &nbsp; Welcome User. &nbsp; Selamat Datang Pengguna.
              </div>
              <div class="description1">
                This is Zhong PWA (Progressive Web Application) for learning Chinese. <br>
                <b>How to use:</b>
                <ol>
                  <li>
                    Fill textbox with any site url. Example of recommended Mandarin website:
                    <ul id="ul_url_list1" class="ul_url_list">
                      <li>https://www.bbc.com/zhongwen/simp/chinese-news-66449834</li>
                      <li>https://www.bbc.com/zhongwen/simp/world-66141140</li>
                      <li>https://zh.wikipedia.org/wiki/Wikipedia:首页</li>
                      <li>https://www.rfa.org/mandarin</li>
                      <li>https://www.chinatimes.com/?chdtv</li>
                      <li>https://scdaily.com/</li>
                    </ul>
                  </li>
                  <li>If you hover every mandarin text with mouse, it will showing English translation in pop-up window.</li>
                  <li>The source is similar to (with adaptation): Chrome Extension "Zhongwen: Chinese-English Dictionary", only this works on Android/iOS phone where web extension only available in desktop.</li>
                  <li>Here is the <a href="https://github.com/jeffrytambari1/zhongpwa">source</a> url</li>
                  <li>Please use this tool wisely.</li>
                </ol>
              </div>
            </div>


            <h3 class="heading1">Files</h3>
            <div class="files">
              <ul id="ul_url_list2" class="ul_url_list" style="height: 250px;">
                <!-- <li>https://zhongpwa.com/hsk/hsk_1_vocabulary_list.txt</li>
                <li>https://zhongpwa.com/hsk/hsk_2_vocabulary_list.txt</li>
                <li>https://zhongpwa.com/hsk/hsk_3_vocabulary_list.txt</li>
                <li>https://zhongpwa.com/hsk/hsk_4_vocabulary_list.txt</li>
                <li>https://zhongpwa.com/hsk/hsk_5_vocabulary_list.txt</li>
                <li>https://zhongpwa.com/hsk/hsk_6_vocabulary_list.txt</li>
                <li>https://zhongpwa.com/hsk/hsk_6_vocabulary_list.txt</li> -->
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



            <h3 class="heading1">Directory</h3>
            <div class="directory">
              <ul id="ul_url_list3" class="ul_url_list" style="height: 250px;">
                <?php 
                foreach ($directory_file_list as $df_ky => $df_dt) {
                  echo "<li>{$df_dt}</li>";
                }
                ?>
              </ul>
            </div>


            <h3 class="heading1">Saved URLs</h3>
            <div class="saved_urls">
              <!-- <form class="form1"> -->
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
                <!-- <div class="to-do-list"></div> -->
                <ul id="todo_list" class="to-do-list ul_url_list" style="height: 250px;">
                </ul>
              </main>
              <!-- </form> -->

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
              <div class="row row0">
                <div class="col-12 col-md-4 col0">
                  <div class="row row0">
                    <div class="col-12 col0">
                      Font Size
                    </div>
                    <div class="col-12 col0">
                      <!-- <div class="d-flex flex-row">
                        <div class="p-1">
                          <button id="increase_font_size" type="button" class="btn btn-success button3">+</button>
                        </div>
                        <div class="p-1">
                          <button id="decrease_font_size" type="button" class="btn btn-success button3">-</button>
                        </div>
                        <div class="p-1">
                          <button id="reset_font_size" type="button" class="btn btn-success button3">Reset</button>
                        </div>
                      </div> -->
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
                        <!-- <div class="col-12">
                          <div class="float-left squared button_background_color" data-color_value="#ffffbf" style="background-color: #ffffbf;" title="Default Color"></div>
                          <div class="float-left squared button_background_color" data-color_value="yellow" style="background-color: yellow;"></div>
                          <div class="float-left squared button_background_color" data-color_value="cyan" style="background-color: cyan;"></div>
                          <div class="float-left squared button_background_color" data-color_value="lime" style="background-color: lime;"></div>
                        </div> -->

                        <div class="col-12">
                          <div class="row row0">
                            <?php 
                            foreach ($popup_background_colors as $pbc_ky => $pbc_dt) {
                              $color = $pbc_dt['color'];
                              $title = $pbc_dt['title'] ?? '';
                              // echo "<div class='float-left squared button_background_color' data-color_value='{$color}' style='background-color: {$color};' title='{$title}'></div>";

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

      <!-- <div id="mouse1">
        <svg viewBox="11.8 9 16 22" class="mouse1"><path d="M20,21l4.5,8l-3.4,2l-4.6-8.1L12,29V9l16,12H20z"></path></svg>
      </div> -->
      <!-- <div id="cursor1" class="cursor1"></div> -->



      <div class="floating_action_button share" id="fab1" style="display: none;">
        <div class="main">
          <!-- <i class="fa-solid fa-headset"></i> -->
          <i class="fa fa-align-justify"></i>
        </div>
        <!-- <div class="secondary phone">
          <i class="fa fa-phone"></i>
        </div>
        <div class="secondary sms">
          <i class="fa fa-envelope"></i>
        </div>
        <div class="secondary whatsapp">
          <i class="fa-brands fa-whatsapp"></i>
        </div>
        <div class="secondary telegram">
          <i class="fa-brands fa-telegram"></i>
        </div>
        <div class="secondary facebook">
          <i class="fa-brands fa-facebook-f"></i>
        </div>
        <div class="secondary email">
          <i class="fa fa-envelope"></i>
        </div> -->

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

        <!-- <div class="secondary reload">
          <i class="fa fa-arrows-rotate"></i>
        </div> -->

        <!-- <div class="secondary go_url">
          <i class="fa fa-play"></i>
        </div> -->
        

        <div class="secondary go_to_top">
          <i class="fa fa-arrow-up"></i>
        </div>
        
      </div>


      <!-- <div role="alert" aria-live="assertive" aria-atomic="true" class="toast" data-autohide="false" id="toast1">
        <div class="toast-header">
          <img src="images/72x72.png" class="rounded mr-2" alt="icon">
          <strong class="mr-auto">Bootstrap</strong>
          <small>11 mins ago</small>
          <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="toast-body">
          Hello, world! This is a toast message.
        </div>
      </div> -->
      <!-- <button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button> -->

      <!-- <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="divToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-header">
            <img src="images/72x72.png" class="rounded me-2" alt="icon">
            <strong class="me-auto">Bootstrap</strong>
            <small>11 mins ago</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            Hello, world! This is a toast message.
          </div>
        </div>
      </div> -->

      <!-- <div id="divToast" class="toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            Hello, world! This is a toast message.
          </div>
          <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div> -->


    </main>

    <footer class="bd-footer text-muted" style="height: 2px;">
      <div style="font-size: 12px; padding-left: 5px;">
        good words. 
        <a href="https://jeffrytambari.info/using-zhong-pwa/">help</a>
        <i class="bi bi-0-circle-fill"></i>
      </div>
    </footer>


    <!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
    <!-- <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js" integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script> // 231125_222526 -->

    <!-- <script src="plugins/jquery/js/jquery-3.6.0.min.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <!-- <script src="plugins/jquery-ui/jquery-ui.min.js"></script> -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <!-- <script src="plugins/bootstrap/js/bootstrap.min.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="https://rawgit.com/pisi/Longclick/master/jquery.longclick-min.js"></script> -->

    <script src="plugins/popper.min.js"></script>


    <script type="text/javascript">
      // console.log("Installing service worker");
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          // navigator.serviceWorker.register('sw.js?v2')
          //   .then((reg) => {
          //     // console.log ( 'after register' );
          //     setInterval( () => reg.update(), 86400 );
          //   });
          navigator.serviceWorker.register('sw.js?v3')
            .then((registration) => {
              console.log('ServiceWorker registration successful with scope: ', registration.scope);
              // setInterval( () => reg.update(), 86400 );
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
      // window.onbeforeunload = null;
      // window.onbeforeunload = function () {
      //   // return "";
      //   return "Press OK to refresh or quit.";
      // };
      // let isFormDirty = false;
      window.addEventListener('beforeunload', function (event) {
        // if (state.formDirty) {
        const confirmationMessage = "You have unsaved changes. Are you sure you want to leave?";
        event.returnValue = confirmationMessage;
        event.preventDefault();
        return confirmationMessage;
        // return false;
        // }
      });
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
      // var btn_randomY = $("#btn_randomY");
      var txt_url = $("#txt_url");
      var frm1 = $("#frm1");
      var header1 = $("#header1");
      var loading1 = $("#loading1");
      var div2 = $("#div2");
      var form1 = $(".form1");
      // var increase_font_size = $("#increase_font_size");
      // var decrease_font_size = $("#decrease_font_size");
      // var reset_font_size = $("#reset_font_size");
      // var ul_url_list = $("#ul_url_list");
      // var ul_url_list = $(".ul_url_list");
      const hh1 = $(window).height();
      const ww1 = $(window).width();
      let header1_hh = 0;
      var data1 = {
        domain_name: "",
        protocol: "",
      };
      var data_settings = {};
      // var default_data_settings = {
      //   'w-hanzi' : {
      //     'font-size' : '18px',
      //   },
      //   'w-pinyin' : {
      //     'font-size' : '16px',
      //   },
      //   'w-def' : {
      //     'font-size' : '12px',
      //   },

      //   'w-hanzi-small' : {
      //     'font-size' : '18px',
      //   },
      //   'w-pinyin-small' : {
      //     'font-size' : '16px',
      //   },
      //   'w-def-small' : {
      //     'font-size' : '12px',
      //   },
      // };
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

      // function getProtocolDomainFromUrl(url) { // https://domainname.com
      //   let domain = (new URL(url));
      //   const domain_name = domain.hostname; 
      //   const protocol = domain.protocol; 
      //   return protocol + "//" + domain_name; // without :
      // }

      function setProtocolDomainFromUrl(url) {
        let domain = (new URL(url));
        data1.domain_name = domain.hostname;
        data1.protocol = domain.protocol;
        // console.log ( data1 );
      }

      function getProtocolDomain() {
        // var base_url = window.location.origin;
        // var host = window.location.host;
        return window.location.origin;
      }

      function getFullUrl(path) {
        if(isValidUrl(path)) { return path; }
        if(path.substring(0, 2) === "//") { return data1.protocol + path; } // url without https:
        path = "/" + ( path.startsWith("/") ? path.substring(1) : path );
        return data1.protocol + "//" +  data1.domain_name + path;
      }

      function set_saved_urls_list_item_click() {
        // // // ul_url_list.find('li').on('click', function(ee, cc) {
        // console.log ('set_saved_urls_list_item_click'  );
        // $("#div_todo_app .ul_url_list").find('li input[type="text"]').on('click', function(ee, cc) {
        $("#div_todo_app .ul_url_list").find('li input').on('click', function(ee, cc) {
          // let li = $(ee.target);
          // let li = $(this);
          let li_input = $(this);
          // // txt_url.val(li_input.text());
          // console.log ( li_input.val() );
          // let str = li_input.val().replaceAll(' ', '');
          // alert(li_input.val());
          // let str = li_input.val().replace(/\s/g, '');
          let str = li_input.val();
          // alert ( li_input.parent().find(`input[type="hidden"]`).val() );
          txt_url.val(str);
          // txt_url.val(str);
          // txt_url.val(1234567);
          // $("#txt_url").val(555);
          // // txt_url.val(li_input.val());
          // alert($("#txt_url").val());
        });
      }

      function fetchSettings() {
        // console.log ( localStorage.getItem('data_settings')  );
        // let settings = localStorage.getItem('data_settings') ? JSON.parse(localStorage.getItem('data_settings')) : data_settings;
        // data_settings = localStorage.getItem('data_settings') ? JSON.parse(localStorage.getItem('data_settings')) : data_settings;
        data_settings = localStorage.getItem('data_settings') ? JSON.parse(localStorage.getItem('data_settings')) : clone1(default_data_settings);
        // console.log ( 'settings' , settings );
      }

      function saveSettings() {
        localStorage.setItem('data_settings', JSON.stringify(data_settings));
      }
      function applyCssSettings() {
        // console.log ( 'applyCssSettings' );
        $.each(data_settings, function(ii, setting) {
          // // console.log ( ii, dt );
          // // // console.log ( "#zhongwen-window ." + ii, dt );
          // // console.log ( "#zhongwen-window ." + ii, );
          // $("#zhongwen-window ." + ii).css(dt); // { "backgroundColor": "black", "color": "white" }
          // // $("#zhongwen-window ." + ii).css({'font-size' : '8px !important'});
          // $("#zhongwen_window_example ." + ii).css(dt); // { "backgroundColor": "black", "color": "white" }

          $.each(setting, function(jj, element) {
            // console.log ( element );
            $("#zhongwen-window" + element.selector).css(element.css);
            $("#zhongwen_window_example" + element.selector).css(element.css);
          });
        });
      }

      // cssIncreaseFontSize(el) {
      //   // let w_def_set = data_settings['w-def'];
      //   // let w_def_fz = w_def_set['font-size'];
      //   let old_fz = el['font-size'];
      //   let new_fz = parseInt(old_fz) + 1;
      //   // data_settings['w-def'] = {'font-size' : new_fz + 'px'};;
      //   return {'font-size' : new_fz + 'px'};;
      //   // console.log ( 'before store ' , data_settings );
      //   // localStorage.setItem('data_settings', JSON.stringify(data_settings));
      // }

      function changeFontSizePopUp(addition) {
        // console.log ( 'increase_font_size click' );
        // // data_settings['w-def-small'] = {'font-size' : '8px'};
        // // data_settings['w-def-small'] = {'font-size' : '8px !important'};

        $.each(data_settings.font_size, function (ii, element) {
          // console.log ( ii, element );
          // // let w_def_set = element;
          // console.log ( 1111 , element.css);
          let old_fz = element.css['font-size'];
          // console.log ( 2222 );
          // // let new_fz = parseInt(old_fz) + 1;
          let new_fz = parseInt(old_fz) + addition;
          // data_settings.font_size[ii] = {'font-size' : new_fz + 'px'};;
          // // console.log ( 'before store ' , data_settings );
          data_settings.font_size[ii].css = {'font-size' : new_fz + 'px'};;
        }); 

        saveSettings();
        // localStorage.setItem('data_settings', JSON.stringify(data_settings));
        // cssIncreaseFontSize()
      }
      function changeBackgroundColor(color) {
        data_settings['background_color'] = [
          { selector: '', css: {'background-color' : color}, },
          { selector: ' span', css: {'background-color' : color}, },
        ];
        saveSettings();
        // localStorage.setItem('data_settings', JSON.stringify(data_settings));
        // cssIncreaseFontSize()
      }
      function get_url_extension( url ) {
        return url.split(/[#?]/)[0].split('.').pop().trim();
      }

      $(document).ready(function() {
        frm1.parent().parent().parent().css("height", hh1 - header1.height() - 30 + 'px');

        btn_go.on('click', function() {
          loading1.show();
          fetch(base_url + "/main.php?url=" + txt_url.val())
            // .then(response => response.json())
            .then(response => response.text())
            .then(data => {
              let html1 = data;

              let is_file_txt = get_url_extension(txt_url.val()) == 'txt';
              // let is_html = $('<div>').html(html1).children().length;
              let is_html = !is_file_txt && ( $('<div>').html(html1).children().length > 0 );
              let is_show_fab1 = !is_html;
              let url2 = '';
              if(is_html){
                html1 = html1.replace(/<\!DOCTYPE[^>]*>/gi,"<!-- doctype1 -->")
                  .replace(/<html[^>]*>/gi,"<div class=\"html1\">").replace(/<\/html[^>]*>/gi,"<\/div>")
                  .replace(/<head[^>]*>/gi,"<div class=\"head1\" hidden>").replace(/<\/head[^>]*>/gi,"<\/div>")
                  .replace(/<script[^>]*>/gi,"<div class=\"script1\" hidden>").replace(/<\/script[^>]*>/gi,"<\/div>")
                  .replace(/<body[^>]*>/gi,"<div class=\"body1\">").replace(/<\/body[^>]*>/gi,"<\/div>");
                div2.html(html1);
                url2 = txt_url.val();
              } else {
                div2.multiline(html1);
                div2.addClass('text_result');
                url2 = getProtocolDomain();
              }

              // console.log ( url2 );

              // setProtocolDomainFromUrl(txt_url.val());
              setProtocolDomainFromUrl(url2);


              $('a').on('click', function(ee, bb) {
                let aa = $(this);
                let href = aa.attr('href');
                txt_url.val(getFullUrl(href));
                ee.preventDefault();
                btn_go.click();
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

              // if(!is_show_fab1) { fab1.obj.hide(); }
              if(is_show_fab1) { fab1.obj.show(); }
              loading1.hide();
            })
            .catch(error => console.error(error));
        });

        btn_reload.on('click', function() {
          window.location.reload();
        });
        // btn_randomY.on('click', function() {
        //   randomScroll()
        // });

        txt_url.keypress(function(ee){
          if(ee.keyCode == 13){
            btn_go.click();
          }
        });

        // // $(".ul_url_list").find('li').on('click', function(ee, cc) {
        // $("#ul_url_list1").find('li').on('click', function(ee, cc) {
        //   // let li = $(ee.target);
        //   let li = $(this);
        //   txt_url.val(li.text());
        // });
        // $("#ul_url_list2").find('li').on('click', function(ee, cc) {
        //   // let li = $(ee.target);
        //   let li = $(this);
        //   txt_url.val(li.text());
        // });

        $(".ul_url_list").find('li').on('click', function(ee, cc) {
          // let li = $(ee.target);
          let li = $(this);
          txt_url.val(li.text());
        });


        $("#increase_font_size").on('click', function() {
          // console.log ( 'increase_font_size click' );
          // // data_settings['w-def-small'] = {'font-size' : '8px'};
          // // data_settings['w-def-small'] = {'font-size' : '8px !important'};

          // let w_def_set = data_settings['w-def'];
          // let w_def_fz = w_def_set['font-size'];
          // let w_def_new_fz = parseInt(w_def_fz) + 1;
          // data_settings['w-def'] = {'font-size' : w_def_new_fz + 'px'};;
          // console.log ( 'before store ' , data_settings );
          // localStorage.setItem('data_settings', JSON.stringify(data_settings));

          // cssIncreaseFontSize();
          // increaseFontSize();
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
          // data_settings = default_data_settings;
          // saveSettings();
          // applyCssSettings();
          let btn = $(this);
          let color = btn.attr("data-color_value");
          changeBackgroundColor(color);
          // saveSettings();
          applyCssSettings();
        });



        div2.accordion({
          collapsible: true,
          autoHeight: false,
          navigation: true,
          heightStyle: "content",
        });
        // div2.fadeIn();
        // div2.show();
        div2.css('visibility', 'visible');


        // div2.find('.heading1').on('click', function(ee){
        //   ee.preventDefault();
        //   var accordion = $(this);
        //   var accordionContent = accordion.next('.accordion-content');
        //   accordion.toggleClass("open");
        //   accordionContent.slideToggle(250);
        // });


        fetchSettings();
        applyCssSettings();

        form1.on('submit', function(ee) {
          ee.preventDefault();
        });

        txt_url.focus();


      });
    </script>

    <script type="text/javascript">
      // console.log ( 'emulate mouse input' );
      // const el = document.querySelector('.mouse1');
      // let lastMove = 0;

      // function onMouseMove (e) {
      //   x = e.clientX;
      //   y = e.clientY;
      //   updateMouse(x, y);
      //   lastMove = Date.now();
      // }

      // function updateMouse (x, y) {
      //     el.style.transform = `translate(${x}px, ${y}px)`;
      // }

      // function render (a) {
      //   if (Date.now() - lastMove > 500) {
      //     const noiseX = (noise.simplex3(2, 0, a*0.0004) + 1) / 2;
      //     const noiseY = (noise.simplex3(10, 0, a*0.0004) + 1) / 2;
      //     const x = noiseX * innerWidth;
      //     const y = noiseY * innerHeight;
      //     updateMouse(x, y);
      //   }
        
      //   requestAnimationFrame(render);
      // }

      // window.addEventListener('mousemove', onMouseMove);
      // requestAnimationFrame(render);
    </script>

    <script type="text/javascript">
      // document.addEventListener('DOMContentLoaded', () => {
      //   const cursor1 = document.getElementById('cursor1');
      //   const offset = -150; // Offset in pixels between the touch point and the cursor
      //   let lastElement = null;

      //   // Show cursor and move it based on touch position
      //   function moveCursor(event) {
      //     const touch = event.touches[0];
      //     const cursorX = touch.clientX; // + offset;
      //     const cursorY = touch.clientY + offset;
      //     // cursor1.style.left = `${touch.clientX + offset}px`;
      //     // cursor1.style.left = `${touch.clientX}px`;
      //     // cursor1.style.top = `${touch.clientY + offset}px`;
      //     cursor1.style.left = `${cursorX}px`;
      //     cursor1.style.top = `${cursorY}px`;
      //     cursor1.style.display = 'block';

      //     // Handle hover
      //     const element = document.elementFromPoint(cursorX, cursorY);
      //     if (element !== lastElement) {
      //       if (lastElement) {
      //         // Trigger mouseout on the last element
      //         const mouseOutEvent = new MouseEvent('mouseout', {
      //           view: window,
      //           bubbles: true,
      //           cancelable: true,
      //           clientX: cursorX,
      //           clientY: cursorY
      //         });
      //         lastElement.dispatchEvent(mouseOutEvent);
      //       }
      //       if (element) {
      //         // Trigger mouseover on the new element
      //         const mouseOverEvent = new MouseEvent('mouseover', {
      //           view: window,
      //           bubbles: true,
      //           cancelable: true,
      //           clientX: cursorX,
      //           clientY: cursorY
      //         });
      //         element.dispatchEvent(mouseOverEvent);
      //       }
      //       lastElement = element;
      //     }


      //     // Simulate mousemove for text selection
      //     const mouseMoveEvent = new MouseEvent('mousemove', {
      //       view: window,
      //       bubbles: true,
      //       cancelable: true,
      //       clientX: cursorX,
      //       clientY: cursorY
      //     });
      //     document.dispatchEvent(mouseMoveEvent);

      //   }

      //   // Hide cursor when touch ends
      //   // function hideCursor() {
      //   //   cursor1.style.display = 'none';
      //   // }
      //   function hideCursor() {
      //     cursor1.style.display = 'none';
      //     if (lastElement) {
      //       // Trigger mouseout on the last element
      //       const mouseOutEvent = new MouseEvent('mouseout', {
      //         view: window,
      //         bubbles: true,
      //         cancelable: true
      //       });
      //       lastElement.dispatchEvent(mouseOutEvent);
      //       lastElement = null;
      //     }
      //   }

      //   // Emulate mouse click at the offset position
      //   function emulateMouseClick(event) {
      //     const touch = event.changedTouches[0];
      //     const clickX = touch.clientX + offset;
      //     const clickY = touch.clientY + offset;

      //     const element = document.elementFromPoint(clickX, clickY);
      //     if (element) {
      //       const mouseEvent = new MouseEvent('click', {
      //         view: window,
      //         bubbles: true,
      //         cancelable: true,
      //         clientX: clickX,
      //         clientY: clickY
      //       });
      //       element.dispatchEvent(mouseEvent);
      //     }
      //   }


      //   // Simulate mousedown event at the offset position
      //   function emulateMouseDown(event) {
      //     const touch = event.touches[0];
      //     const downX = touch.clientX + offset;
      //     const downY = touch.clientY + offset;

      //     const mouseDownEvent = new MouseEvent('mousedown', {
      //       view: window,
      //       bubbles: true,
      //       cancelable: true,
      //       clientX: downX,
      //       clientY: downY
      //     });
      //     document.dispatchEvent(mouseDownEvent);
      //   }

      //   // Simulate mouseup event at the offset position
      //   function emulateMouseUp(event) {
      //     const touch = event.changedTouches[0];
      //     const upX = touch.clientX + offset;
      //     const upY = touch.clientY + offset;

      //     const mouseUpEvent = new MouseEvent('mouseup', {
      //       view: window,
      //       bubbles: true,
      //       cancelable: true,
      //       clientX: upX,
      //       clientY: upY
      //     });
      //     document.dispatchEvent(mouseUpEvent);
      //   }


      //   // Event listeners for touch events
      //   // document.addEventListener('touchstart', moveCursor);
      //   document.addEventListener('touchstart', (event) => {
      //     emulateMouseDown(event);
      //     moveCursor(event);
      //   });
      //   document.addEventListener('touchmove', moveCursor);
      //   // document.addEventListener('touchend', hideCursor);
      //   document.addEventListener('touchend', (event) => {
      //     emulateMouseUp(event);
      //     hideCursor();
      //     emulateMouseClick(event);
      //   });
      // });
    </script>


    <script type="text/javascript">
      // console.log ( 'dialogues1' );
      // const json_url =  "< ? php echo $dialogues_file ? "/json/{$dialogues_file}" : ''; ? >";
      const dialogues_content = <?php echo json_encode($dialogues_content2, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> ;

      function init_dialogues1() {
        // const url = "https://lh.com/mandarin/20150201.json";
        // const url = "/json/20150201.json";
        // displayDialogues(jsonData);

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
          // console.log(data); // Process your JSON data further here
          displayDialogues(data);
        } catch (error) {
          console.error('Error fetching data: ', error);
        }
      }
      function displayDialogues(data) {
        // console.log ( 'displayDialogues', data );
        let str = '';
        const date1 = "20150118";
        // Object.keys(data).forEach(key => {
        Object.keys(data).sort().forEach(key => {
          if (key.startsWith("todayExpr_")) {
            const dialogues = data[key];

            str += `<div class="dialogue">`;

            // console.log ( dialogues );
            // console.log(`Date: ${key.replace("todayExpr_", "")}`);
            // console.log ( dialogues.sentences );
            // console.log ( dialogues.sentences );

            const date2 = key.replace("todayExpr_", "");
            const dayDifference = getDayDifference(date1, date2);

            str += `<div class="dialogue_title">
              <span class="dialogue_number">${dayDifference}</span> &bull; 
              <span class="mandarin_title">${dialogues.title_translation}</span> &bull; 
              <span class="english_title">${dialogues.title}</span>
            </div>`;


            str += `<div class="dialogue_sentences">`;
            dialogues.sentences.forEach(dialogue => {
              // // console.log ( "English: " + dialogue.orgnc_sentence );
              // // console.log ( "Mandarin: " + dialogue.trsl_orgnc_sentence );
              // // console.log ( "Mandarin2: " + dialogue.trsl_sentence );
              // // // console.log ( trsl_orgnc_sentence );
              // // str += `<div class="mandarin">${dialogue.trsl_orgnc_sentence}</div>`;
              // str += `<div class="mandarin">${dialogue.trsl_sentence}</div>`;
              // // str += `<div class="english">${dialogue.orgnc_sentence}</div>`;
              // str += `<div class="english">${dialogue.sentence}</div>`;
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
        // obj: undefined,
        main_button: { cls: 'main', isClicked: false },
        secondary_buttons: {
          // phone: { cls: 'phone' },
          // sms: { cls: 'sms' },
          // whatsapp: { cls: 'whatsapp' },
          // telegram: { cls: 'telegram' },
          // facebook: { cls: 'facebook' },
          // // twitter: { cls: 'twitter' },
          // // instagram: { cls: 'instagram' },
          // email: { cls: 'email' },
          random: { cls: 'random', is_show_notif: true },
          tab_space: { cls: 'tab_space', is_show_notif: true },
          plus_font_size: { cls: 'plus_font_size' },
          minus_font_size: { cls: 'minus_font_size' },
          enable_counting: { cls: 'enable_counting', is_enable_counting: false },
          // go_url: { cls: 'go_url' },
          // reload: { cls: 'reload' },
          go_to_top: { cls: 'go_to_top' },

        }
      };

      function openContact(contact_type = 'phone', destination = '', title = '', text = '') {
        let url = getContactUrl(contact_type, destination, title, text);
        if(url.length > 0) {
          loadUrlBlankPage(url);
        }
      }
      function getElementPositionValue(obj, type = 'top', isRelative = true) { // type = top/bottom/left/right/all, relative/absolute
        let ret;
        if(isRelative) {
          let rel_height = $(document).height(); // returns height of HTML document
          let rel_top_value = obj.position().top + obj.offset().top + obj.outerHeight(true); // TODO: not tested yet
          let rel_bottom_value = rel_height - rel_top_value; // TODO: not tested yet
          let rel_width = $(document).width(); // returns width of HTML document
          let rel_left_value = obj.position().left + obj.offset().left + obj.outerWidth(true); // TODO: not tested yet
          let rel_right_value = rel_width - rel_left_value; // TODO: not tested yet
          if(type == 'top') { ret = rel_top_value; }
          else if(type == 'bottom') { ret = rel_bottom_value; }
          else if(type == 'left') { ret = rel_left_value; }
          else if(type == 'right') { ret = rel_right_value; }
          else { ret = { top: rel_top_value, bottom: rel_bottom_value, left: rel_left_value, right: rel_right_value }; }
        } else {
          // // using calculation
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
        // x = Math.abs(x);
        // y = Math.abs(y);
        return { x: x, y: y };
      }
      function setFloatingActionButtonShare() {
        var flag = 0;
        let delay = 100;
        let distance = 170;
        let degree = 90 / ( Object.keys(fab1.secondary_buttons).length - 1 );
        // console.log ( 'degree', degree );
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

        let phone_number = "08983489990";

        // fab1.secondary_buttons.phone.obj.on('click', function() {
        //   console.log ( 'clck' );
        //   // openContact("phone", phone_number);
        // });
        // fab1.secondary_buttons.sms.obj.on('click', function() {
        //   openContact("sms", phone_number);
        // });
        // fab1.secondary_buttons.whatsapp.obj.on('click', function() {
        //   openContact("whatsapp", phone_number);
        // });
        // fab1.secondary_buttons.telegram.obj.on('click', function() {
        //   openContact("telegram", "jeffrytambari");
        // });
        // fab1.secondary_buttons.facebook.obj.on('click', function() {
        //   openContact("facebook_messenger", "gws");
        // });
        // fab1.secondary_buttons.email.obj.on('click', function() {
        //   openContact("email", "namasayajev@gmail.com");
        // });

        fab1.secondary_buttons.random.obj.on('click', function() {
          randomScroll();
          showNotif(fab1.secondary_buttons.random.obj);
        });
        fab1.secondary_buttons.tab_space.obj.on('click', function() {
          convertTabSpace(div2);
          showNotif(fab1.secondary_buttons.tab_space.obj);
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
        // fab1.secondary_buttons.reload.obj.on('click', function() {
        //   btn_reload.click();
        // });
        // fab1.secondary_buttons.go_url.obj.on('click', function() {
        //   btn_go.click();
        // });
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
        // const documentHeight = document.documentElement.scrollHeight;
        // const windowHeight = window.innerHeight;
        // const randomY = Math.floor(Math.random() * (documentHeight - windowHeight));
        window.scrollTo({
          top: 0,
          // behavior: 'smooth',
        });
      }
      function convertTabSpace(obj) {
        let html1 = obj.html();
        // div2.html(html1.replaceAll("\t", "&emsp;&emsp;&emsp;&emsp;"));
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
      const toast1 = $('#divToast');
      // const toastTrigger = document.getElementById('liveToastBtn')
      // const toastLiveExample = document.getElementById('divToast')

      // if (toastTrigger) {
      //   const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
      //   toastTrigger.addEventListener('click', () => {
      //     toastBootstrap.show()
      //   })
      // }
      // $('#liveToastBtn').on('click', function() {
      //   $('#divToast').toast('show');
      //   // // $('#toastPlacement').toast('show');
      // });
      function showNotif(obj) {
        // obj.is_show_notif = obj.hasOwnProperty('is_show_notif') ? obj.is_show_notif : false;
        if(obj.is_show_notif) {
          let btn = $(this);
          let title = btn.attr('title');
          toast1.find('.toast_body').text(title);
          toast1.toast('show');
          fab1.secondary_buttons.random.is_show_notif = false;
        }
      }
    </script>
  </body>
</html>
