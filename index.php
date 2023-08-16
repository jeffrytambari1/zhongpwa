<?php 
$env = parse_ini_file('.env');
$base_url = $env["BASE_URL"];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <script type="text/javascript">
      const base_url = "<?php echo $base_url; ?>";
    </script>
    <meta charset="UTF-8" />
    <link id="favicon" rel="icon" href="favicon.ico" type="image/x-icon">
  	<link rel="apple-touch-icon" href="images/icons/apple-touch.png" />

  	<link rel="manifest" href="manifest.json" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Zhong PWA</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- zhongwen-extension -->
    <link rel="stylesheet" type="text/css" href="zhongwen/css/content.css" />
    
    <link rel="stylesheet" href="css/style.css" />

  </head>
  <body>

    <header class="navbar navbar-expand navbar-dark flex-column flex-md-row bd-navbar header0 header1" id="header1">
      <div class="col-12 padding0">
        <div class="input-group">
            <input value="https://zh.wikipedia.org/wiki/Wikipedia:首页" class="form-control txt_url" type="text" id="txt_url">
            <!-- https://zh.wikipedia.org/wiki/Wikipedia:首页 -->
            <!-- https://dict.naver.com/linedict/enzhdict/#/encn/todayexpr?data=20230808 -->
            <span class="input-group-btn">
               <button class="btn btn-default btn_go" type="submit" id="btn_go">
                  <i class="icon1 icon_right"></i>
               </button>
               <!-- <button class="btn btn-default btn_home" type="submit" id="btn_home">
                  <i class="icon1 icon_home"></i>
               </button> -->
               <button class="btn btn-default btn_reload" type="submit" id="btn_reload">
                  <i class="icon1 icon_reload"></i>
               </button>
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
          <div id="div2" class="col-12 padding0">
            <div class="prewords">
              <div class="welcome1">
                欢迎用户。 &nbsp; Huānyíng yònghù. &nbsp; Welcome User. &nbsp; Selamat Datang Pengguna.
              </div>
              <div class="description1">
                This is Zhong PWA (Progressive Web Application) for learning Chinese. <br>
                <b>How to use:</b>
                <ol>
                  <li>
                    Fill textbox with any site url. Example of recommended Mandarin website:
                    <ul id="ul_url_list" class="ul_url_list">
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

          </div>
        </div>
      </div>

      <div id="loading1" class="loading1" style="display: none;">
        <img id="loading-image" class="loading-image" src="images/loading.gif" alt="Loading..." />
      </div>
    </main>

    <footer class="bd-footer text-muted" style="height: 2px;">
      <div style="font-size: 12px; padding-left: 5px;">
        good words. 
        <a href="https://jeffrytambari.info/using-zhong-pwa/">help</a>
      </div>
    </footer>


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
      // console.log("Installing service worker");
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
          .then((reg) => {
            // console.log ( 'after register' );
          });
      }
      const module = {};
    </script>

    <script type="text/javascript" src="js/zhongwen_main.js"></script>
    <script type="module" src="zhongwen/js/zhuyin.js"></script>
    <script type="module" src="zhongwen/dict.js"></script>
    <script type="module" src="zhongwen/background.js"></script>
    <script type="text/javascript" src="zhongwen/content.js"></script>
    <script type="text/javascript" src="js/zhongwen_ready.js"></script>

    <script type="text/javascript">
      
      var btn_go = $("#btn_go");
      var btn_reload = $("#btn_reload");
      var txt_url = $("#txt_url");
      var frm1 = $("#frm1");
      var header1 = $("#header1");
      var loading1 = $("#loading1");
      var div2 = $("#div2");
      var ul_url_list = $("#ul_url_list");
      const hh1 = $(window).height();
      const ww1 = $(window).width();
      let header1_hh = 0;
      var data1 = {
        domain_name: "",
        protocol: "",
      };


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
      }

      function getFullUrl(path) {
        if(isValidUrl(path)) { return path; }
        if(path.substring(0, 2) === "//") { return data1.protocol + path; } // url without https:
        path = "/" + ( path.startsWith("/") ? path.substring(1) : path );
        return data1.protocol + "//" +  data1.domain_name + path;
      }

      $(document).ready(function() {
        frm1.parent().parent().parent().css("height", hh1 - header1.height() - 30 + 'px');

        btn_go.on('click', function() {
          loading1.show();
          fetch(base_url + "main.php?url=" + txt_url.val())
            // .then(response => response.json())
            .then(response => response.text())
            .then(data => {
              let html1 = data;
              html1 = html1.replace(/<\!DOCTYPE[^>]*>/gi,"<!-- doctype1 -->")
                .replace(/<html[^>]*>/gi,"<div class=\"html1\">").replace(/<\/html[^>]*>/gi,"<\/div>")
                .replace(/<head[^>]*>/gi,"<div class=\"head1\" hidden>").replace(/<\/head[^>]*>/gi,"<\/div>")
                .replace(/<script[^>]*>/gi,"<div class=\"script1\" hidden>").replace(/<\/script[^>]*>/gi,"<\/div>")
                .replace(/<body[^>]*>/gi,"<div class=\"body1\">").replace(/<\/body[^>]*>/gi,"<\/div>");
              div2.html(html1);

              setProtocolDomainFromUrl(txt_url.val());

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

              loading1.hide();
            })
            .catch(error => console.error(error));
        });

        btn_reload.on('click', function() {
          window.location.reload();;
        });

        txt_url.keypress(function(ee){
          if(ee.keyCode == 13){
            btn_go.click();
          }
        });

        ul_url_list.find('li').on('click', function(ee, cc) {
          // let li = $(ee.target);
          let li = $(this);
          txt_url.val(li.text());
        });

        txt_url.focus();
      });
    </script>

  </body>
</html>
