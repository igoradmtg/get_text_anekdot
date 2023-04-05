<?php
require_once('inc/simple_html_dom.php');
require_once('inc/str.php');
function br2nl($string)
{
  return preg_replace('/\<br(\s*)?\/?\>/i', "\r\n", $string);
}

function get_web_page( $url )
{
  $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
  $options = array(
      CURLOPT_CUSTOMREQUEST  =>'GET',        //set request type post or get
      CURLOPT_POST           =>false,        //set to GET
      CURLOPT_USERAGENT      => $user_agent, //set user agent
      CURLOPT_COOKIEFILE     =>'cookie.txt', //set cookie file
      CURLOPT_COOKIEJAR      =>'cookie.txt', //set cookie jar
      CURLOPT_RETURNTRANSFER => true,     // return web page
      CURLOPT_HEADER         => false,    // don't return headers
      CURLOPT_FOLLOWLOCATION => true,     // follow redirects
      CURLOPT_ENCODING       => "",       // handle all encodings
      CURLOPT_AUTOREFERER    => true,     // set referer on redirect
      CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
      CURLOPT_TIMEOUT        => 120,      // timeout on response
      CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
      CURLOPT_PROXY          => '127.0.0.1:9050',
      CURLOPT_PROXYTYPE      => CURLPROXY_SOCKS5
  );

  $ch      = curl_init( $url );
  curl_setopt_array( $ch, $options );
  $content = curl_exec( $ch );
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );

  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $content;
  return $header;
}
$a_start = 219579;
$a_finish = 999500;
$dir_text = 'd:/vid/text';
if (make_dir_if_not_exists($dir_text)==false) {
  echo "Error make dir $dir_text <br>\r\n";
  exit;
}

for($a = $a_start; $a <= $a_finish; $a++) {
  $url = 'https://www.anekdot.ru/id/'.$a.'/';
  echo "$a $url <br>\r\n";
  $head = get_web_page($url);
  $string_url = $head['content'];
  
  if ($string_url==false) {
    echo "Error get url $url <br>\r\n".
    $head['errno']."<br>\r\n".
    $head['errmsg']."<br>\r\n".
    "<br>\r\n";
    continue;
  }
  $data = str_get_html($string_url);
  $text = '';
  if ($data==false) continue;
  if ($data->innertext!='') {
    if (count($data->find('div.text'))>0) {
      foreach($data->find('div.text') as $div_text){
        $text .= $div_text->innertext;
      }
    $data->clear();
    }
  }
  unset($data);
  if (!empty($text)) {
    $text = str_replace('&quot;','"',$text);
    $fname = get_fname($a,$dir_text,'.txt');
    if ($fname==false) {
      echo "Error create file name $a <br>\r\n";
      exit;
    }
    file_put_contents($fname,br2nl($text));
  } else echo "Error get text <br>\r\n";
}