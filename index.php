<?php
 // chat time - 'Y-m-d H:i:s'
//проверка дали потребителя си е създал сесия
$setUser = false;
// всичките потребители създали сесия
$users = $rooms = array();
$roomChronology = '';
$room = 'common';

include('inc/functions.php');

include('inc/chatClass.php');
$chat = new chatClass();
$users = $chat->checkActiveUsers(); // Чистя неактивните потребители и връщам всички активните
$rooms = $chat->getRooms($room); // всичките стаи
$roomChronology = $chat->readRoom($room); // тегля общата хронология за дадения общ чат

// всичките обръщения към сървъра
include('inc/Request.php');

// ако няма потребител за тази сесия
if (!$chat->getUser()) {
	$setUser = true;
	// проверка ако потребителя не присътства във файла
}

$last_user = $last_msg = $last_msg_time = '';
//взимам времето на последното съобщение от обчия чат
if (!empty($roomChronology) and $user = @key(end($roomChronology))) { 
	$last_user = $user; 
	$last_msg = end($roomChronology)[$user]['msg']; 
	$last_msg_time = end($roomChronology)[$user]['time']; 
}
?>
<!DOCTYPE html>
<html>
	<head>

		<meta charset="utf-8">
		<title></title>
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-store" />
		<meta http-equiv="expires" content="-1" />
		<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
		<meta http-equiv="pragma" content="no-cache" />
		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<link href="css/style.css?v=<?php echo date('H:i:s'); ?>" rel="stylesheet" />
		
		<style>
		</style>
		<script type="text/javascript">
const url = "index.php";
var lock_chat = 0;
var interval;
var time_interval = 10000;
var sender = "";
var recipient = '<?php echo $chat->getUser(); ?>';
recipient = (recipient ? recipient : 'anonymous');
// последните данни за обчия чат
var room = '<?php echo $room; ?>';
var last_user = '<?php echo $last_user; ?>';
var last_msg = '<?php echo $last_msg; ?>';
var last_msg_time = '<?php echo $last_msg_time; ?>';

		</script>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-6">
					<section class="discussion_all">
<?php if (!empty($roomChronology)): ?>
	<?php foreach($roomChronology as $key => $user_msg): ?>
		<?php $user_key = key($user_msg); ?>
						<div class="bubble sender middle"><b><?php echo $user_key.'</b> - '.$user_msg[$user_key]['msg']; ?></div>
	<?php endforeach; ?>
	<?php endif; ?>
						<div class="send_box_all">
							<input type="text" name="message_box_all" class="message_box_all" />
							<button type="submit" class="send_all" onClick="mirc.sendMsg();">Send</button>
						</div>
					</section>
				</div>
				<div class="col-md-2">
					<section class="users">
<?php if (!$setUser): ?>
	<?php foreach($users as $user): ?>
						<div class="user<?php if (isset($chat->newMsg[$user])) echo ' blinking'; ?>" onClick="chat.getChronology(this)"><?php echo $user; ?></div>
	<?php endforeach; ?>
	<?php endif; ?>
					</section>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<section class="rooms">
<?php if (!empty($rooms)): ?>
	<?php foreach($rooms as $key => $room): ?>
						<div class="room<?php echo ($room == 'common' ? ' active' : ''); ?>" onClick="return mirc.setActive(this); "><?php echo $room; ?></div>
	<?php endforeach; ?>
	<?php endif; ?>
					</section>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<section class="discussion" style="display: none;">
						<div class="send_box">
							<input type="text" name="message_box" class="message_box" onfocus="chat.checkLock();" onkeypress="chat.checkLock();" />
							<button type="submit" class="Send" onfocus="chat.send();" disabled>Send</button>
							<!--
							<br /><div class="clearfix"></div>
							<button type="submit" class="audio" disabled>audio</button>
							<button type="submit" class="video" disabled>video</button>
							-->
						</div>
					</section>
				</div>
			</div>
		</div>

		<!-- jQuery Core jQuery 3.x -->
		<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
		<!-- Bootstrap -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
		
		<script src="js/chat.js?v=<?php echo date('H:i:s'); ?>"></script>
		<script src="js/mirc.js?v=<?php echo date('H:i:s'); ?>"></script>
		
		<script>
<?php
if ($setUser) {
	echo '
var username = prompt("Въведи потребител", "");
if (username != null) {
	var data = {"setName": username};
	$.ajax({
		method: "POST",
		dataType: "json",
		url: url,
		data: data,
		success: function (data) {
			var length = Object.keys(data).length;
			if (data == 1) {
				alert("Името присътсва изберете друго");
				window.location.href = url;
			}
			if (length) {
				recipient = username;
				$(".users").empty();
				<!-- for (var i in data) { -->
				for (var i = 0; i < data.length; i++) {
					$(".users").append("<div class=\"user\" onClick=\"chat.getChronology(this);\">"+data[i]+"</div>");
				}
			}
		}
	});
}
	';
}
?>
function timeout() {
	mirc.timeout();
	chat.timeout();
	interval = setTimeout(function () {	timeout(); }, time_interval);
}
timeout();
		</script>
		
	</body>
</html>
