<?php 
ini_set('display_errors', 0);
ini_set('memory_limit', '100M');
ini_set('max_execution_time', 30);


header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=utf-8");
header("Content-Security-Policy: default-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'none'; script-src 'none'; connect-src 'none'; img-src https: data:; style-src 'self' 'unsafe-inline'; object-src 'none'");

date_default_timezone_set('Asia/Jakarta');

// --------------------- SECURED CURL

/* =======================
   HELPERS
   ======================= */

function normalize_url_for_http(string $url): string {
  $url = trim($url);
  $p = @parse_url($url);
  if ($p === false || empty($p['scheme']) || empty($p['host'])) return $url;

  // Block URLs containing userinfo (user:pass@)
  if (!empty($p['user']) || !empty($p['pass'])) {
    return ''; // will fail validation later
  }

  // IDN host → ASCII punycode
  if (function_exists('idn_to_ascii')) {
    $ascii = @idn_to_ascii($p['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    if ($ascii) $p['host'] = strtolower($ascii);
  } else {
    $p['host'] = strtolower($p['host']);
  }

  // Encode each path segment safely (avoid double-encoding)
  $path = $p['path'] ?? '/';
  $segs = explode('/', $path);
  foreach ($segs as &$s) {
    if ($s === '') continue;
    $s = rawurlencode(urldecode($s));
  }
  $p['path'] = implode('/', $segs) ?: '/';

  $frag  = isset($p['fragment']) && $p['fragment'] !== '' ? '#' . rawurlencode(urldecode($p['fragment'])) : '';
  $query = isset($p['query'])    && $p['query']    !== '' ? '?' . $p['query'] : '';

  $port  = isset($p['port']) ? ':' . $p['port'] : '';
  return strtolower($p['scheme']).'://'.$p['host'].$port.$p['path'].$query.$frag;
}

function host_matches_allowlist(string $host, array $patterns): bool {
  foreach ($patterns as $pat) {
    $pat = strtolower($pat);
    if ($pat === $host) return true;
    if (str_starts_with($pat, '*.')) {
      $suffix = substr($pat, 1); // ".example.cn"
      if (str_ends_with($host, $suffix) && substr_count($host, '.') >= substr_count($suffix, '.')) {
        return true;
      }
    }
  }
  return false;
}

function url_is_allowlisted(string $url, array $allowUrls, array $allowHosts): bool {
  // Exact URL allow
  if (in_array($url, $allowUrls, true)) return true;

  $p = @parse_url($url);
  if ($p === false || !isset($p['scheme'],$p['host'])) return false;

  return host_matches_allowlist(strtolower($p['host']), $allowHosts);
}

function dns_resolves_to_public(string $host): bool {
  $v4 = @gethostbynamel($host) ?: [];
  foreach ($v4 as $ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
  }
  $v6recs = @dns_get_record($host, DNS_AAAA) ?: [];
  foreach ($v6recs as $r) {
    $ip = $r['ipv6'] ?? '';
    if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
  }
  return true;
}

function is_allowed_port(?int $port, string $scheme): bool {
  if ($port === null) return true;
  if ($scheme === 'http')  return $port === 80;
  if ($scheme === 'https') return $port === 443;
  return false;
}

function is_text_like(string $ct): bool {
  $ct = strtolower($ct);
  return $ct === '' ||
         str_starts_with($ct, 'text/') ||
         str_contains($ct, 'html') ||
         str_contains($ct, 'xml') ||
         str_contains($ct, 'json') ||
         str_contains($ct, 'javascript') ||
         str_contains($ct, 'svg');
}

// Tiny relative-URL resolver (handles absolute, root-abs, and simple relatives)
function resolve_url(string $base, string $rel): string {
  if (preg_match('#^https?://#i', $rel)) return $rel;
  $bp = parse_url($base);
  if ($bp === false || !isset($bp['scheme'],$bp['host'])) return $rel;

  $scheme = $bp['scheme'];
  $host   = $bp['host'];
  $port   = isset($bp['port']) ? ':' . $bp['port'] : '';
  $basePath = $bp['path'] ?? '/';
  if (str_starts_with($rel, '/')) {
    $path = $rel;
  } else {
    // join with base directory
    $dir = preg_replace('#/[^/]*$#', '/', $basePath);
    $path = $dir . $rel;
    // normalize ./ and ../
    $parts = [];
    foreach (explode('/', $path) as $seg) {
      if ($seg === '' || $seg === '.') continue;
      if ($seg === '..') { array_pop($parts); continue; }
      $parts[] = $seg;
    }
    $path = '/' . implode('/', $parts);
  }
  return "$scheme://$host$port$path";
}

/* -------- Simple per-IP daily rate limiter (file-based) -------- */
function rate_limit_check(string $storeDir, int $limitPerDay, string $id): array {
  if (!is_dir($storeDir)) @mkdir($storeDir, 0775, true);
  $key = $storeDir . '/' . date('Ymd') . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $id) . '.cnt';
  $count = 0;

  $f = @fopen($key, 'c+');
  if ($f) {
    if (flock($f, LOCK_EX)) {
      $size = filesize($key);
      if ($size > 0) $count = (int)trim(fread($f, $size));
      $count++;
      ftruncate($f, 0);
      rewind($f);
      fwrite($f, (string)$count);
      fflush($f);
      flock($f, LOCK_UN);
    }
    fclose($f);
  } else {
    // best effort if file cannot be opened
    $count = $limitPerDay + 1; // force block to be safe
  }

  return ['allowed' => $count <= $limitPerDay, 'count' => $count, 'limit' => $limitPerDay];
}

/* =======================
   MAIN SECURE FETCH (cURL)
   ======================= */

function secure_fetch_whitelisted(string $inputUrl, array $opt = []): array {
  global $ALLOW_URLS, $ALLOW_HOSTS;

  $timeout      = $opt['timeout']       ?? 15;
  $maxRedirects = $opt['max_redirects'] ?? 3;
  $maxBytes     = $opt['max_bytes']     ?? (5 * 1024 * 1024); // 5 MB
  $ua           = $opt['user_agent']    ?? 'ZhongPWA/1.0';
  $acceptLang   = $opt['accept_lang']   ?? 'zh-CN,zh;q=0.9,en;q=0.5';

  $url = normalize_url_for_http($inputUrl);

  // Basic structure & scheme check
  $p = @parse_url($url);
  if ($p === false || !isset($p['scheme'],$p['host'])) {
    return ['ok'=>false,'status'=>0,'final_url'=>null,'ip'=>null,'headers'=>[],'body'=>'','error'=>'Invalid URL'];
  }
  $scheme = strtolower($p['scheme']);
  $host   = strtolower($p['host']);
  $port   = $p['port'] ?? null;

  if (!in_array($scheme, ['http','https'], true)) {
    return ['ok'=>false,'status'=>0,'final_url'=>null,'ip'=>null,'headers'=>[],'body'=>'','error'=>'Unsupported scheme'];
  }
  if (!is_allowed_port($port, $scheme)) {
    return ['ok'=>false,'status'=>0,'final_url'=>null,'ip'=>null,'headers'=>[],'body'=>'','error'=>'Port not allowed'];
  }
  // if (!url_is_allowlisted($url, $ALLOW_URLS, $ALLOW_HOSTS)) {
  //   return ['ok'=>false,'status'=>0,'final_url'=>null,'ip'=>null,'headers'=>[],'body'=>'','error'=>'URL/host not allow-listed'];
  // }
  if (!dns_resolves_to_public($host)) {
    return ['ok'=>false,'status'=>0,'final_url'=>null,'ip'=>null,'headers'=>[],'body'=>'','error'=>'Host resolves to private/reserved IP'];
  }

  $hops = 0;
  $finalHeaders = [];
  $finalIp = null;
  $status = 0;
  $body = '';

  while (true) {
    $headersOut = [];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER   => true,
      CURLOPT_FOLLOWLOCATION   => false, // manual redirects to re-validate
      CURLOPT_PROTOCOLS        => CURLPROTO_HTTP | CURLPROTO_HTTPS,
      CURLOPT_REDIR_PROTOCOLS  => CURLPROTO_HTTP | CURLPROTO_HTTPS,
      CURLOPT_USERAGENT        => $ua,
      CURLOPT_ACCEPT_ENCODING  => '',    // enable gzip/deflate
      CURLOPT_HTTPHEADER       => [
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Language: $acceptLang",
        "Connection: close"
      ],
      CURLOPT_CONNECTTIMEOUT   => $timeout,
      CURLOPT_TIMEOUT          => $timeout,
      CURLOPT_MAXREDIRS        => 0,
      CURLOPT_NOBODY           => false,
      CURLOPT_HEADERFUNCTION => function($ch, $line) use (&$headersOut) {
        $headersOut[] = rtrim($line, "\r\n");
        return strlen($line);
      },
      CURLOPT_SSL_VERIFYPEER   => true,
      CURLOPT_SSL_VERIFYHOST   => 2,
      CURLOPT_COOKIEFILE       => '',  // disable cookie in/out
      CURLOPT_COOKIEJAR        => '',
      CURLOPT_IPRESOLVE        => CURL_IPRESOLVE_WHATEVER,
      CURLOPT_NOPROGRESS       => false,
      // CURLOPT_XFERINFOFUNCTION => function($ch, $dlTotal, $dlNow) use ($maxBytes) {
      //   return ($dlNow > $maxBytes) ? 1 : 0; // abort if too large
      // },
      // CURLOPT_XFERINFOFUNCTION => function ($ch, $dlTotal, $dlNow, $ulTotal, $ulNow) use ($maxBytes) {
      //   return ($dlNow > $maxBytes) ? 1 : 0; // non-zero aborts
      // },
      CURLOPT_PROGRESSFUNCTION => function ($ch, $dlTotal, $dlNow, $ulTotal, $ulNow) use ($maxBytes) {
        return ($dlNow > $maxBytes) ? 1 : 0;
      },
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $ip   = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
    $eff  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    // IP must be public (defense vs SSRF tricks)
    if ($ip && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
      return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$headersOut,'body'=>'','error'=>'Connected IP is private/reserved'];
    }

    // Parse headers
    $hdrAssoc = [];
    foreach ($headersOut as $hline) {
      if (stripos($hline, 'HTTP/') === 0) continue;
      $pos = strpos($hline, ':');
      if ($pos === false) continue;
      $k = strtolower(trim(substr($hline, 0, $pos)));
      $v = trim(substr($hline, $pos + 1));
      if (isset($hdrAssoc[$k])) $hdrAssoc[$k] .= ', ' . $v; else $hdrAssoc[$k] = $v;
    }

    // Redirect?
    if ($code >= 300 && $code < 400) {
      if ($hops >= $maxRedirects) {
        return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$hdrAssoc,'body'=>'','error'=>'Too many redirects'];
      }
      $loc = $hdrAssoc['location'] ?? '';
      if ($loc === '') {
        return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$hdrAssoc,'body'=>'','error'=>'Redirect without Location'];
      }
      $next = normalize_url_for_http(resolve_url($url, $loc));

      // Re-validate next hop
      $np = @parse_url($next);
      if ($np === false || !isset($np['scheme'],$np['host'])) {
        return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$hdrAssoc,'body'=>'','error'=>'Bad redirect URL'];
      }
      $ns = strtolower($np['scheme']);
      $nh = strtolower($np['host']);
      $nport = $np['port'] ?? null;

      if (!in_array($ns, ['http','https'], true)) {
        return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$hdrAssoc,'body'=>'','error'=>'Redirect to unsupported scheme'];
      }
      if (!is_allowed_port($nport, $ns)) {
        return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$hdrAssoc,'body'=>'','error'=>'Redirect to disallowed port'];
      }
      // if (!url_is_allowlisted($next, $GLOBALS['ALLOW_URLS'], $GLOBALS['ALLOW_HOSTS'])) {
      //   return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$hdrAssoc,'body'=>'','error'=>'Redirect target not allow-listed'];
      // }
      if (!dns_resolves_to_public($nh)) {
        return ['ok'=>false,'status'=>$code,'final_url'=>$eff,'ip'=>$ip,'headers'=>$hdrAssoc,'body'=>'','error'=>'Redirect host resolves to private/reserved IP'];
      }

      $url = $next;
      $hops++;
      continue;
    }

    // Final
    $status = $code;
    $finalHeaders = $hdrAssoc;
    $finalIp = $ip ?: null;
    $body = is_string($resp) ? $resp : '';
    break;
  }

  // Content-type filter
  $ct = $finalHeaders['content-type'] ?? '';
  if (!is_text_like($ct)) {
    return ['ok'=>false,'status'=>$status,'final_url'=>$url,'ip'=>$finalIp,'headers'=>$finalHeaders,'body'=>'','error'=>'Unsupported content type'];
  }

  // Charset → UTF-8 (best effort)
  if ($body !== '' && preg_match('/;\s*charset=([^\s;]+)/i', $ct, $m)) {
    $charset = trim($m[1], "\"'");
    if ($charset && strcasecmp($charset, 'utf-8') !== 0) {
      $c = @mb_convert_encoding($body, 'UTF-8', $charset);
      if ($c !== false) $body = $c;
    }
  }

  return [
    'ok'        => ($status >= 200 && $status < 400),
    'status'    => $status,
    'final_url' => $url,
    'ip'        => $finalIp,
    'headers'   => $finalHeaders,
    'body'      => $body,
    'error'     => null
  ];
}

// --------------------- END OF SECURED CURL


function sanitize_links(string $html, string $mode = 'span'): string {
  // $mode: 'span' or 'disable'
  libxml_use_internal_errors(true);
  $dom = new DOMDocument();
  $dom->loadHTML(
    '<meta charset="utf-8">'.$html,
    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
  );
  $xp = new DOMXPath($dom);

  foreach ($xp->query('//a[@href]') as $a) {
    /** @var DOMElement $a */
    // Skip named anchors without href (rare), we target only real links
    $href = $a->getAttribute('href');

    if ($mode === 'span') {
      // Replace <a> with <span> while preserving classes/styles/ids
      $span = $dom->createElement('span');
      // copy some safe presentation attrs
      foreach (['class','id','style','title'] as $att) {
        if ($a->hasAttribute($att)) $span->setAttribute($att, $a->getAttribute($att));
      }
      // keep URL for reference/tooltip/etc., but don't keep javascript:*
      if ($href !== '' && !preg_match('#^\s*javascript:#i', $href)) {
        $span->setAttribute('data-href', $href);
      }
      // move children
      while ($a->firstChild) {
        $span->appendChild($a->firstChild);
      }
      $a->parentNode->replaceChild($span, $a);

    } else { // 'disable' mode
      // Remove href and interaction/event-related attributes
      $a->removeAttribute('href');
      foreach (['target','rel','ping','download'] as $rm) {
        if ($a->hasAttribute($rm)) $a->removeAttribute($rm);
      }
      // Remove inline event handlers like onclick/onmousedown/...
      $attrs = iterator_to_array($a->attributes ?? []);
      foreach ($attrs as $attr) {
        if (preg_match('/^on/i', $attr->name)) $a->removeAttribute($attr->name);
      }
      // Make it visually link-like but inert
      $style = $a->getAttribute('style');
      $extra = 'pointer-events:none;cursor:default;';
      // keep underline if you want the look; omit if not desired:
      // $extra .= 'text-decoration:underline;';
      $a->setAttribute('style', trim(rtrim($style, ';').';'.$extra, ';'));
      $a->setAttribute('aria-disabled', 'true');
    }
  }

  $out = $dom->saveHTML();
  libxml_clear_errors();
  return $out;
}

function logUserActivity() {
  global $now1, $today2, $logFolderPath;
  $ipAddress = $_SERVER['REMOTE_ADDR'];
  $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A';
  $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : 'N/A';
  $requestParameters = $_REQUEST;
  $sessionData = isset($_SESSION) ? $_SESSION : array();
  $cookiesData = isset($_COOKIE) ? $_COOKIE : array();
  $logEntry = date('Y-m-d H:i:s') . " - IP: $ipAddress, Referrer: $referrer, Query String: $queryString, Parameters: " . json_encode($requestParameters) . ", Session: " . json_encode($sessionData) . ", Cookies: " . json_encode($cookiesData) . PHP_EOL;
  $logFilePath = $logFolderPath . DIRECTORY_SEPARATOR . "log-{$today2}.txt";
  file_put_contents($logFilePath, $logEntry, FILE_APPEND);
}

function isLimitUserActivity() {
  global $RATE_LIMIT_STORE_DIR, $RATE_LIMIT_MAX_PER_DAY;
  $clientId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $rl = rate_limit_check($RATE_LIMIT_STORE_DIR, $RATE_LIMIT_MAX_PER_DAY, $clientId);
  return !$rl['allowed'];
}

function fetch1($url) {
  // $response = @file_get_contents($url, false);
  $res = secure_fetch_whitelisted($url, [
    'timeout'       => 30,
    'max_redirects' => 3,
    'max_bytes'     => 5 * 1024 * 1024, // 5 MB
  ]);

  if (!$res['ok']) {
    http_response_code(400);
    $msg = htmlspecialchars($res['error'] ?: ('HTTP '.$res['status']), ENT_QUOTES, 'UTF-8');
    echo "Blocked or failed: $msg";
    exit;
  }

  $response = $res['body'];
  $safe = sanitize_links($response);
  return $safe;
}


// --------------------- MAIN CONTROLLER

$now1 = date('Y-m-d H:i:s');
$today1 = date('Y-m-d');
$today2 = date('Y_m_d');
$logFolderPath = "log";
$limitFolderPath = "limit";

$ALLOW_URLS = [
  // 'https://zh.wikipedia.org/wiki/%E5%8D%97%E6%9E%81%E6%B4%B2',
];

// Host allow-list. Prefer exact hosts; use a single leading "*." only if needed.
$ALLOW_HOSTS = [
  // 'zh.wikipedia.org',
  // 'zh.m.wikipedia.org',
  // // 'baike.baidu.com',
  // // '*.example.cn',
];

// Per-IP rate limit
$RATE_LIMIT_MAX_PER_DAY = 20;
// $RATE_LIMIT_STORE_DIR   = sys_get_temp_dir() . '/fetch_rate_limit'; // make sure PHP can write
$RATE_LIMIT_STORE_DIR   = $limitFolderPath;


logUserActivity();


$url = @$_GET['url'];
$url = trim($url);
if (strlen($url) > 2048) {
  http_response_code(400);
  echo "Bad request.";
  exit;
}

$content = '';
if($url == '') {
  $file1 = fopen("empty.html", "r") or die("Unable to open file!");
  $content = fread($file1, filesize("empty.html"));
  fclose($file1);
} else {

  $url2 = parse_url($url);
  $path = trim( $url2['path'], "/" );
  $file_path = $path;
  $is_local_file = file_exists($file_path);

  if($is_local_file) {
    $file1 = fopen($file_path, "r");
    $content = fread($file1, filesize($file_path));
    fclose($file1);
  } else if( isLimitUserActivity() ) {
    http_response_code(429);
    $file1 = fopen("limit.html", "r") or die("Unable to open file!");
    $content = fread($file1, filesize("limit.html"));
    fclose($file1);
  } else {
    $content = fetch1($url);
  }
}

echo $content;
