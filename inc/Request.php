<?php
// if($_POST) pr($_POST);
// $_POST['setName'] = 'kolqta';
// $chat->removeUser('kolqta');
if (!empty($_POST)) {
	foreach ($_POST as $key => $value) {
		$_POST[$key] = clearStr($value);
	}
}

/* MIRC */
// записвам съощението в общия чат
// дефекта ще е че позволявам повече от едним чевек да работят едновременно
if (isset($_POST['set_msg_all'])) {
	if (!trim($_POST['msg'])) exit;
	$user = $chat->getUser();
	$user = ($return = strlen($user) > 2 ? $user : 'anonymous');
	$msg[$user] = array('time' => $_POST['last_msg_time'], 'msg' => $_POST['msg']);
	$file = $chat->chat_rooms_path.$_POST['room'];
	if (!file_exists($file)) { exit; $chat->setFile($file); } // ако няма такава стая
	$chat_all = $chat->decode(file_get_contents($file));
	$chat_all[] = $msg;
	file_put_contents($file, $chat->encode($chat_all));
	$lock = $file.'.msg';
	file_put_contents($lock, $chat->encode($msg));
	exit;
}

// проверявам дали има ново съобщение
if (isset($_POST['get_all'])) {
	$file = $chat->chat_rooms_path.$_POST['room'].'.msg';
	sendHeader();
	header('Content-Type: application/json; charset=utf-8');
	echo file_get_contents($file);
	exit;
}
/* END MIRC */

/* CHAT */

// добавя потребител за сесията
if (isset($_POST['checkUsers'])) {
	sendHeader();
	header('Content-Type: application/json; charset=utf-8');
	$users = (empty($users) ? $chat->getUsers(0) : $users);
	foreach ($users as $key => $user) {
		if ($user == $chat->getUser()) { unset($users[$key]); }
	}
	die($chat->encode($users));
}

// добавя потребител за сесията
if (isset($_POST['setName'])) {
	//проверка дали вече не присътства	
	if ($chat->AjaxGetUserSessFile($_POST['setName'])) { die('1'); }
	$chat->setUser($_POST['setName']);
	sendJson($chat->encode($chat->checkActiveUsers())); // return JSON
}

// взимам хронологията
if (isset($_POST['recipient'])) {
	$chat_file = $chat->setGetChronologyFile($_POST['recipient']);
	sendHeader();
	header('Content-Type: application/json; charset=utf-8');
	die(file_get_contents($chat_file));
}

// заключвам хронологията
if (isset($_POST['id'])) {
	$sender = $_POST['id'];
	if ($_POST['check_lock'] == 0 and !$chat->checkLock($sender)){ $chat->lockChronology($sender); 
	}
	exit;
}

// Проверява дали в момента  се пише в активния чат
if (isset($_POST['check_lock'])) {
	$file = $chat->checkLock($_POST['check_lock']);
	if ($file) {
		$content = file_get_contents($file);
		if (strlen($content) > 0) { echo $content; unlink($file); }
		// else {  }
	} else { echo 0; }
	exit;
}

// записвам съощението
if (isset($_POST['message'])) {
	$sender = $_POST['sender'];	
	$user = $chat->getUser();
	$msg[$user] = array('time' => date('Y-m-d H:i:s'), 'msg' => $_POST['message']);

	// взимам общата хронология и добавям новото съобщение към общия
	$file = $chat->setGetChronologyFile($sender);
	$chat_all = $chat->decode(file_get_contents($file));
	$chat_all[] = $msg;
	$newMsg = $chat->encode($chat_all);
	file_put_contents($file, $newMsg);

	// добавям непрочетеното (ново) съобщение към заключения файл
	$lock_file = $chat->lockChronology($sender);
	$lock_msg = $chat->encode($msg);
	file_put_contents($lock_file, $chat->encode($msg));
	exit;
}