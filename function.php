<?php
/// GET SIZE
function remotefileSize($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_exec($ch);
	$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
	curl_close($ch);
	if ($filesize) return $filesize;
}
/// FORMAT BYTES
function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', ' KB', ' MB', ' GB', ' TB');   
    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}
            
///$cont = remotefileSize('.$url');///
///echo formatBytes($cont);///

// PHP4 workaround
if (!function_exists('file_put_contents')) {
  function file_put_contents($filename, $data, $respect_lock = true) {
    // Open the file for writing
    $fh = @fopen($filename, 'w');
    if ($fh === false) {
      return false;
    }

    // Check to see if we want to make sure the file is locked before we write to it
    if ($respect_lock === true && !flock($fh, LOCK_EX)) {
      fclose($fh);
      return false;
    }

    // Convert the data to an acceptable string format
    if (is_array($data)) {
      $data = implode('', $data);
    } else {
       $data = (string) $data;
    }

    // Write the data to the file and close it
    $bytes = fwrite($fh, $data);

    // This will implicitly unlock the file if it's locked
    fclose($fh);

    return $bytes;
  }
}

////////////////////

function curPageURL() {
  $dirname = dirname($_SERVER["PHP_SELF"]);
  if(substr($dirname, -1)!=="/")$dirname .= "/";
  $o = 'http://'.$_SERVER["SERVER_NAME"].$dirname;
  return $o;
}

////////////////////

function Random_Password($length) {
  $possible_characters = "abcdefghijklmnopqrstuvwxyz1234567890";
  $string = "";
  while(strlen($string)<$length) {
    $string .= substr($possible_characters, rand()%(strlen($possible_characters)),1);
  }
  return($string);
}

/////////////////////

function getData($file) {
  $targetz = "content/$file";
  if(file_exists($targetz) && is_writable($targetz)) {
    $data = trim(file_get_contents($targetz));
    @list($array['url'], $array['date'], $array['count'], $array['last']) = explode(";", $data);
    if(!isset($array['date']))$array['date'] = "-";
    if(!isset($array['count']))$array['count'] = "-";
    if(!isset($array['last']))$array['last'] = "-";
    return $array;
  } else {
    return false;
  }
}

/////////////////////

function checkurl($linkurl) {
  $urlregex = "^(https?)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
  $return = eregi($urlregex, $linkurl);
  return $return;
}

/////////////////////

function addlinkmain($linkurl, $tag='0', $date='0', $count='0', $last='0') {
  if($date=='0')$date = date("Y.m.d");
  $last = ($count=='0' || $count =='-') ? "-" : date("Y.m.d");
  $contents = "$linkurl;$date;$count;$last";
  if ($tag=='0') {
    $shortcut = Random_Password(50);
    while(file_exists("content/$shortcut"))
    {
      $shortcut = Random_Password(50);
    }
    $short_url = curPageURL().$shortcut;
    $sysinfo = (file_put_contents("content/$shortcut",$contents))
    ? "Created new url: <a href=\"$short_url\" target=\"_blank\">$short_url</a>"
    : "Can't write your URL, please check CHMOD!";
  } else {
    if (preg_match("/^[a-zA-Z0-9]+$/", $tag)) {
      if(file_exists("content/$tag"))
      {
        $sysinfo = "Alias already taken!";
      } else {
        $short_url = curPageURL().$tag;
        $sysinfo = (file_put_contents("content/$tag",$contents))
        ? "Created new url: <br /><a href=\"$short_url\">$short_url</a>"
        : "Can't write your URL, please check CHMOD!";
      }
    } else {
      $sysinfo = "Illegal chars in shortcut name!";
    }
  }
  return $sysinfo;
}

/////////////////////

function clickCount($shortcut, $array) {
  $counter = $array['count'];
  $counter = ($counter == "-" || $counter == "0") ? 1 : ++$counter;
  unlink("content/$shortcut");
  addlinkmain($array['url'], $shortcut, $array['date'], $counter, $array['last']);
}

/////////////////////

function redirect($shortcut) {
  $array = getData($shortcut);
  if($array) {
    $destination = trim($array['url']);
    clickCount($shortcut, $array);
  } else {
    include("config/user.php");
    $error = (empty($conf['redirect'])) ? curPageURL() : $conf['redirect'];
    header('Location: '.$error.'');
    exit;
  }
  

  
  $xml = simplexml_load_file($destination, 'SimpleXMLElement', LIBXML_NOCDATA);
  foreach ($xml->channel->item as $item) {
    // get children for namespace prefixed 'media'
    $media = $item->children('media',true);
    $preview = $media->group->content[0]->attributes();
    $video1 = $media->group->content[1]->attributes();
   // $video2 = $media->group->content[2]->attributes();
    $cont = remotefileSize(''.$video1['url'].'');
   // $cont2 = remotefileSize(''.$video2['url'].'');
    }
    
    
 if(!empty($video1))
    {
    echo '
    <br/>
    <center>
    <img src="'.$preview['url'].'"/>
    </center>
    <table class="u-full-width">
    <thead>
    <tr>
    <th>Title</th>
    <th>File</th>
    <th>Size</th>
    <th>Addon</th>
    </tr>
    </thead>
    <tbody>
    <tr>
    <td>'.$item->title.'</td>
    <td>MP4/Video</td>
    <td>'.formatBytes($cont).'</td>     <td>'.$item->pubDate.'</td>
    </tr>
    </tbody>
    </table>
    <center>
    <a class="button button-primary" href="'.$video1['url'].'">Download</a>
    </center>
    <br/>
';
include 'data/download';
    }
    else
    {
    echo "Video Sedang Di Proses";
    }   


}

/////////////////////

function logout($id_type) {
  unset($_SESSION[$id_type]);
  $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
  header("Location: $url");
}

//////////////////////

function authorize($id_type) {
  if (!isset($_SESSION[$id_type])) {
    if (!isset($_POST['login'])) {
      include('login.php');
      exit();
    } else {
      include('config/user.php');
      if (($conf['adm_password'] !== $_POST['password']) or ($conf['adm_user'] !== $_POST['login'])) {
        $loginerror = "Wrong login or password!";
        include('login.php');
        exit();
      } else {
        $_SESSION['loginidadmin2121'] = $conf['adm_user'];
      }
    }
  }
}

//////////////////////

function dirList($directory, $sortOrder, $startFrom='0'){
  $results = array();
  $handler = opendir($directory);
  while ($file = readdir($handler)) {
    if ($file != '.' && $file != '..' && $file != "robots.txt" && $file != ".htaccess"){
      $currentModified = filectime($directory."/".$file);
      $file_names[] = $file;
      $file_dates[] = $currentModified;
    }
  }
  closedir($handler);

  if (isset($file_dates)) {
    if ($sortOrder == "newestFirst") {
        arsort($file_dates);
    } else {
        asort($file_dates);
    }
 
    $file_names_Array = array_keys($file_dates);
    foreach ($file_names_Array as $idx => $name) $name=$file_names[$name];
    $file_dates = array_merge($file_dates);

    $i = 0;
 
    foreach ($file_dates as $file_dates) {
      $date = $file_dates;
      $j = $file_names_Array[$i];
      $file = $file_names[$j];
      $i++;
      $shortcuts[$i] = $file;
    }
  } else {
    $shortcuts = 0;
  }
  return $shortcuts;
}

//////////////////////

function listing($dir, $pointer='1') {
  $pliki = dirList('content/','newestFirst');

  if ($pliki!==0) {
    for ($i=1; $i <= 10; $i++) {
      if (isset($pliki[$pointer]))$setList[$i] = $pliki[$pointer];
      $pointer++;
    } 

    $ile = count($setList);
    $sumaPlikow = count($pliki);
    
    $mid_table = '';

    $pre_table = '
      <table border="0" cellpadding="5" width="100%">
        <tr class="head">
          <td><img src="yellow.gif" width="8" height="8"> ID</td>
          <td>Url</td>
          <td style="text-align: center;">Created</td>
          <td style="text-align: center;">Click count</td>
          <td style="text-align: center;">Last click</td>
          <td colspan="2" class="head">&nbsp;</td>
        </tr>
        ';

    for ($i=1; $i<=$ile; $i++) {
      $array = getData($setList[$i]);
      if($array) {
        $urllong = trim($array['url']);
        if (strlen($urllong)>55) {
          $urllong = substr($urllong, 0, 52) . "...";
        }
      } else {
        $urllong = "Can not access data file!";
      }
      $mid_table = $mid_table . '
        <tr>
          <td bgcolor="#FFFFA4"><a href="'.curPageURL().$setList[$i].'" target="_blank">' . $setList[$i] . '</a></td>
          <td bgcolor="#FFFFA4"><a href="' . $array['url'] . '">' . $urllong . '</a></td>
          <td bgcolor="#FFFFA4" style="text-align: center;">' . $array['date'] . '</td>
          <td bgcolor="#FFFFA4" style="text-align: center;">' . $array['count'] . '</td>
          <td bgcolor="#FFFFA4" style="text-align: center;">' . $array['last'] . '</td>
          <form method="post">
          <td align="center" width="5%">
            <div class="submit">
              <input type="hidden" name="id" value="' . $setList[$i] . '" />
              <input type="submit" name="edit" value="Edit" />
            </div>
          </td>
          </form>
          <form method="post">
          <td align="center" width="5%">
            <div class="submit">
              <input type="hidden" name="id" value="' . $setList[$i] . '" />
              <input type="submit" name="delete" value="Delete" />
            </div>
          </td>
          </form>
        </tr>
        ';
    }
    $post_table = '</table>';
    
    if ($sumaPlikow > '10') {
      $subPagesCount = ($sumaPlikow / 10) + 1;
      $subPages = '<div style="font-size: 18px; width: 750px; margin-top: 4px;">Subpage: ';
      $lamanie = 1;
      $page = (isset($_GET['start'])) ? ($_GET['start']+9)/10 : 1;
      for ($i=1; $i < $subPagesCount; $i++) {
        if($i==$page)$subPages .= "<span style=\"font-size: 20px; font-weight: bold;\"";
        $subPages .= "<a href=\"./admin.php?start=$lamanie\">$i</a> ";
        if($i==$page)$subPages .= "</span>";
        $lamanie = $lamanie + 10;
      }
      $subPages .= '</div>';  
    } else {
      $subPages = '<P>';
    }
    $table = $pre_table . $mid_table . $post_table . $subPages;
  } else {
    $table = "<center>No shortcuts set!</center>";
  }
  return $table;
}

//////////////////////

function delete($file) {
  $target = "content/$file";
  (file_exists($target) && @unlink($target)) ? $sysinfo = "Shortcut \"" . $file . "\" deleted succesfully!"
                                             : $sysinfo = "Error: Data file \"" . $file . "\" can not be deleted!";
  return $sysinfo;
}

//////////////////////

function edit($file) {
  $array = getData($file);
  ($array) ? $url = trim($array['url'])
           : $url = "Can not access data file \"" . $file . "\"!";

  $out = '
  <table border="0" cellpadding="5">
    <tr class="head">
        <td colspan="2"><img src="yellow.gif" width="8" height="8"> Edit link "' . $file . '"</td>
    </tr>
    <form method="post">
    <tr>
        <td>URL:</td>
        <td>
        <input type="text" id="urlf" size="60" name="urlf" value="' . $url . '">
        </td>
    </tr>
    <tr>
        <td>
        Created:
        </td>
        <td>
        <input type="text" id="date" size="10" name="date" value="' . $array['date'] . '" READONLY>
        Clicks:
        <input type="text" id="clicks" size="10" name="clicks" value="' . $array['count'] . '" READONLY>
        </td>
    </tr>
    <tr>
    </tr>
    <tr>
        <td colspan="2">
        <p align="center" class="submit">
        <input type="hidden" name="id" value="' . $file . '">
        <input type="submit" name="back" value="<< back" />
        <input type="submit" name="save" value="Save" />
        </p>
        </td>
    </tr>
    </form>
  </table>
  ';
  return $out;
}

//////////////////////

function save($file,$url) {
  $array = getData($file);
  if($array) {
    unlink("content/$file");
    addlinkmain($url, $file, $array['date'], $array['count']);
    $sysinfo = "Shortcut \"" . $file . "\" succesfully edited!";
  } else {
    $sysinfo = "Error: Data file \"" . $file . "\" could not be edited!";
  }
  $targetz='';
  return $sysinfo;
}

//////////////////////

function update_config($post, $config) {
  $config_str = "<?php\r\n\$".$config['type']." = array();\r\n";

  foreach($config as $key=>$val) {
    $new_val = false;

    (isset($post[$key]))
      ? $new_val = $post[$key]
      : $new_val = $config[$key];

    $config_str .= "\$".$config['type']."['{$key}'] = '{$new_val}'; \r\n";
  }

  $config_str .= "?>";
  $fp = @fopen($config['type'].".php", 'w');
  if ($fp) {
    fwrite($fp, $config_str, strlen($config_str));
    return true;
  } else {
    return false;
  }
}

//////////////////////

function getFeed($feed_url) {
  $content = file_get_contents($feed_url);
  if ($content) {
    $x = new SimpleXmlElement($content);
    echo "<ul id=\"feedlist\">";
    echo "<li id='head'><a href='http://blog.kreci.net' target='_blank'>RSS FEED: Latest@Blog.KreCi.net</a></li>";
    foreach($x->channel->item as $entry) {
      echo "<li><a href='$entry->link' title='$entry->title' target='_blank'>$entry->title</a></li>";
    }
    echo "</ul>";
  }
}

?>
