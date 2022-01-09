[root@zabbix-cloud /]# cat /usr/share/zabbix/addition-data-log.php
<?php

# Точка входа направляющих на сервер журналы:

$reguest_metod = $_SERVER['REQUEST_METHOD'];

$realm = 'Authenticate';
$users = array( 'account' => '8Dvqg' );

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
  header('HTTP/1.1 401 Unauthorized');
  header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
  exit;
  }

// анализируем переменную PHP_AUTH_DIGEST
if ( !($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']) ) || !isset($users[$data['username']]) )
  die('login or password is not correct');

// генерируем корректный ответ
$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

if ( $data['response'] != $valid_response)
  die('login or password is not correct');

// логин и пароль верны
if ( $reguest_metod == 'GET' && $data['username'] == 'account' )  {
  echo "Welcome account<p>";
  }

$uploaddir   = '/tmp/addition-data-log';
if ( !file_exists( $uploaddir ) ) {
  mkdir($uploaddir, 0777, true);
  }

if ( ! file_exists( $uploaddir ) ) {
  echo "folder-error(error create folder " . $uploaddir . ")<p>";
  exit;
  }

echo "folder-ok<p>";

if ( $reguest_metod != 'POST' ) {
  echo 'error,allowed only metod POST<p>';
  exit;
  }

echo "metod-post-ok<p>";

if ( $_FILES['file']['name'] == '' ) {
  echo "filename-null-error<p>";
  exit;
  }

if ( $data['username'] == 'account' ) {
  $file_patch = $uploaddir . '/account_locked.txt';
  if ( file_exists ( $file_patch ) ) {
    unlink( $file_patch );
    if ( file_exists ( $file_patch ) ) {
      echo "filename-json-old-not-unlink-error(файл " . $file_patch ." нужно удалить с сервера руками<p>";
      exit;
      }
    }
  if ( copy( $_FILES['file']['tmp_name'], $file_patch ) ) {
    if ( file_exists ( $file_patch ) ) {
      echo "filename-json-new-ok<p>";
      }
    else {
      echo "filename-json-new-error<p>";
      exit;
      }
    echo shell_exec( "sudo /usr/user_account_locked/addition-data-log.pl addition-data-log" ); // Объеденим с журналом, скажем вывод на контроллер
    exit;
    }
  }

if ( $data['username'] == 'security_auditing' ) {
  $file_patch = $uploaddir .'/security_auditing_'. uniqid();
  if ( file_exists ( $file_patch ) ) {
    unlink( $file_patch );
    if ( file_exists ( $file_patch ) ) {
      echo "filename-json-old-not-unlink-error(файл " . $file_patch ." нужно удалить с сервера руками<p>";
      exit;
      }
    }
  if ( copy( $_FILES['file']['tmp_name'], $file_patch ) ) {
    if ( file_exists ( $file_patch ) ) {
      echo "filename-json-new-ok<p>";
      }
    else {
      echo "filename-json-new-error<p>";
      exit;
      }
    echo shell_exec( "sudo /usr/user_security_auditing/security_auditing.pl add $file_patch" );
    exit;
    }
  }

// функция разбора заголовка http auth
function http_digest_parse($txt) {
// защита от отсутствующих данных
$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
$data = array();
$keys = implode('|', array_keys($needed_parts));
preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
foreach ($matches as $m) {
  $data[$m[1]] = $m[3] ? $m[3] : $m[4];
  unset($needed_parts[$m[1]]);
  }
return $needed_parts ? false : $data;
}
?>
