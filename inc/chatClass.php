<?php

/**
 * chatClass - Този клас е настроен за сега да работи със сесийни променливи и файлове за съхраняване на данните
 * на присъстващите потребители както и хронологията между тях
 * 
 * @author Nikolai D. Chichev <kolqta@abv.bg>
 * @version 1.0
 */
class chatClass
{
	private $sess = array();
	private $allUsers = array();
	public $newMsg = array();
	private $users = array();
	private $rooms = array();
	public $usersPath = 'sessions/users/';
	public $chat_path = 'sessions/chronology/';
	public $chat_rooms_path = 'sessions/rooms/';
	private $userFileLife = '600'; //s Колко време да стои файла като сесия ако не се ползва от активния потребител
	
	public function __construct() {
		if (isset($_COOKIE['kolqta_chat'])) {
			$userName = $_COOKIE['kolqta_chat'];
			// Проверявам ако има създадени бисквитки, но потребителя е станал неактивен да се логне наново
			if (!$this->AjaxGetUserSessFile( $userName )) { setcookie("kolqta_chat", "", time()-3600); }
			else { $this->sess['user'] = $_COOKIE['kolqta_chat']; }
			// обновявам времето на активния потребител
			if (isset($this->sess['user']) and file_exists($this->usersPath.$this->sess['user'])) { touch($this->usersPath.$this->sess['user']); }
		}
	}
	
	/*
	 * @return string - Връщам сесията на потребителя
	 */
	public function getUser() {
		return (@$this->sess['user'] ? $this->sess['user'] : false);
	}
	
	/*
	 * @params string $userName - Името на потребителя
	 * @params array $users - Всичките потребители
	 */
	public function setUser($userName = '') {
		$this->setFile($this->usersPath.$userName);
		setcookie("kolqta_chat", $userName);
		$this->sess['user'] = $userName;
	}
	
	/*
	 * @params string $userName - Името на потребителя
	 */
	public function removeUser($userName = '') {
		setcookie("kolqta_chat", '', '/', null);
		$this->sess['user'] = '';
		unset($this->sess['user']);
		if (file_exists($this->usersPath.$userName)) { unlink($this->usersPath.$userName); }
	}
	
	/*
	 * @return string - всичките потребители
	 */
	public function getUsers() {
		$this->users = array();
		$users = scandir($this->usersPath);
		foreach ($users as $user) {
			if ( (strlen($user) > 2) ) {
				$this->allUsers[] = $user;
				if ($user != @$this->sess['user']) {
					$this->users[] = $user;
				}
			}
		}
		$this->scanNewMsg();
		return $this->users;
	}
	
	/*
	 * Проверка за потребители коите не са налиния от определен интервал от време
	 * @return array - Активните потребители
	 */
	public function checkActiveUsers() {
		foreach ($this->getUsers(1) as $file) {
			$filemtime = @filemtime($this->usersPath.$file);
			// echo $this->usersPath.$file.' - '.date('H:i', $filemtime).' - '.date('H:i', (time()-$filemtime)).'<br>';
			if (!$filemtime or (time() - $filemtime >= $this->userFileLife)) {
				unlink($this->usersPath.$file);
			}
		}		
		return $this->getUsers(1);
	}
	
	/*
	 * @params string $userName - Проверка дали потребителя е създаден
	 * @return bool
	 */
	public function AjaxGetUserSessFile( $userName = '') {
		if (file_exists($this->usersPath.$userName)) { return true; }
		return false;
	}
	
	/*
	 * @params string $toUser - който ще получи
	 * @return string $chat_file - Пътя до хронологията
	 */
	public function setGetChronologyFile($toUser = '') {
		$chat_file = $this->chat_path.$toUser.'-'.$this->sess['user'];
		if (!file_exists($chat_file)) { $chat_file = $this->chat_path.$this->sess['user'].'-'.$toUser; }
		if (!file_exists($chat_file)) { $this->setFile($chat_file); } // само когато се създава за пръв път чат
		return $chat_file;
	}
	
	/*
	 * @params string $toUser - който ще получи
	 * @return string $chat_file - Пътя до хронологията
	 */
	public function lockChronology($toUser = '') {
		if ($toUser and $this->sess['user']) {
			$file = $this->chat_path.$toUser.'-'.$this->sess['user'].'.lock';
			$this->setFile($file);
			return $file;
		}
	}
	
	/*
	 * Проверявам дали в момента някой не пише или има празно съобщение
	 * @params string $fromUser - в активни чат човека с който си пиша
	 * @return string $chat_file - Пътя до хронологията
	 */
	public function checkLock($fromUser = '') {
		$file = $this->chat_path.$this->sess['user'].'-'.$fromUser.'.lock';
		return (file_exists($file) ? $file : false);
	}
	
	/*
	 * Проверявам дали за ново съобщение и от кой е
	 * трия файловете които са от неактивните потребители
	 * @return string $chat_file - Пътя до хронологията
	 */
	public function scanNewMsg() {
		$files = scandir($this->chat_path);
		foreach ($files as $file) {
			if (strstr($file, '.lock')) {
				$check_file = explode('-', str_replace(array('.lock'), '', $file));
				foreach ($check_file as $user) {
					// трия файла ако е от неактивен
					if (!in_array($user, $this->allUsers)) { unlink($this->chat_path.$file); }
				}
				// когато е ново съобщението за моментния потребител
				if (!empty($check_file) and $check_file[0] == @$this->sess['user'] ) { $this->newMsg[$check_file[1]] = 'new'; }
			}
		}
	}
	
	/*
	 * @params string $fileName
	 */
	public function setFile($fileName = '') {
		$hp = fopen($fileName, "w");
		fclose($hp);
	}
	
	/*
	 * @return string - Всички чат стаи
	 */
	public function getRooms() {
		$roomsFiles = scandir($this->chat_rooms_path);
		foreach ($roomsFiles as $room) {
			if ( (strlen($room) > 2) and !stristr($room, '.') ) {
				$this->rooms[] = $room;
			}
		}
		return $this->rooms;
	}
	
	/*
	 * @return string - хронологията в стаята
	 */
	public function readRoom( $room = '' ) {
		$file = $this->chat_rooms_path.$room;
		return $this->decode(file_get_contents($this->chat_rooms_path.$room));
	}
	
	/*
	 * @params array $array
	 * @return string - Връщам json_encode
	 */
	public function encode($array = array()) { return json_encode($array); }
	
	/*
	 * @params string $str
	 * @return array - Връщам json_encode
	 */
	public function decode($str = '') { return json_decode($str, 1); }
	
}