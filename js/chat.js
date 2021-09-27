"use strict";

let scrollHeight = Math.max(
  document.body.scrollHeight, document.documentElement.scrollHeight,
  document.body.offsetHeight, document.documentElement.offsetHeight,
  document.body.clientHeight, document.documentElement.clientHeight
);

const chat = {
	lock_chat: 0,
	/*
	* Взимам хронологията за активния чат
	*/
	getChronology: function ( elem ) {
		sender = $(elem).text();
		$(".discussion").show();
		$(".discussion").empty().append('<div class="send_box"><input type="text" name="message_box" class="message_box" onfocus="chat.checkLock();" onkeypress="chat.checkLock();" /><button type="submit" class="Send" onfocus="chat.send();" disabled>Send</button></div>');
		$(".discussion").prop('id', sender);
		window.scrollTo(0, scrollHeight);
		$.ajax({
			method: "POST",
			dataType: "json",
			// contentType: "application/json",
			url: url,
			data: {"recipient": sender},
			success: function (data) {
				if (data.length) {
					for (let i in data) {
						if (data[i][sender]) {
							$(".send_box").before('<div class="bubble sender first">'+data[i][sender]['msg']+'</div>');
						}
						if (data[i][recipient]) {
							$(".send_box").before('<div class="bubble recipient first">'+data[i][recipient]['msg']+'</div>');
						}
						$(".send_box").before('<br /><div class="clearfix"></div>');
					}
				}
				$('.discussion').scrollTop($('.discussion')[0].scrollHeight);
			}
		});
	},
	/*
	* Заключвам бутона изпрати на човека на който пиша както извеждам икона че в момента се въвежда
	*/
	checkLock: function ( ) {
		this.isWrite();
		var id = $(".discussion").prop('id');
		if (id && !lock_chat) {
			$.post(url, {"id": id, "lock_chat": 0});
			this.lock_chat = 1;
		}
	},
	/*
	* Проверявам дали не ми пишат и дали ми е позволено да пиша
	*/
	isWrite: function () {
		// lock_chat
		// 1 - пишат ми
		// 0 - мога да пиша
		if (sender) {
			$.post(url, {"check_lock": sender}).done(function(data) {
				if (data.length > 0) {
					// няма създаден файл
					if (parseInt(data) === 0) {
						$('.write').remove();
						$('.Send').prop("disabled", false);
						this.lock_chat = 0;
						
					// има данни за извеждане
					} else {
						$('.write').remove();
						this.lock_chat = 0;
						var obj = $.parseJSON(data);
						for (let i in obj) {
							if (obj[i]['msg']) {
								$(".send_box").before('<div class="bubble sender last">'+obj[i]['msg']+'</div>');
								$(".user").removeClass('blinking');
							}
						}
					}
				} else {
					if (!$('.write').length) {
						$(".send_box").before('<div class="bubble sender last write"><img src="img/write.gif" width="50" /></div>');					
						$('.Send').prop("disabled", true);
						this.lock_chat = 1;
					}
				}
			});
		}
	},
	/*
	* Пращам текстовото съобщение
	*/
	send: function ( ) {
		let message = $('.message_box').val();
		sender = $(".discussion").prop('id');
		if (message) {
			$(".send_box").before('<div class="bubble recipient first">'+message+'</div>');
			$('.message_box').val('');
			$.ajax({
				method: "POST",
				dataType: "json",
				url: url,
				data: {"message": message, "sender": sender}
			});
			$('.discussion').scrollTop($('.discussion')[0].scrollHeight);
		}
	},
	timeout: function ( ) {
		this.checkLock();
	}
};

 


