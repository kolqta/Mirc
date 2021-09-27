"use strict";

const mirc = {
	json: {},
	/*
	* Задавам в коя стая да се пише
	* @params elem object - алемента на стаята която да е активна
	* @return bool
	*/
	setActive: function( elem ) {
		room = $(elem).text();
		$('.rooms div').removeClass('active');
		$(elem).addClass('active');
		return false;
	},
	/*
	* проверявам за ново съобщение в обчия активен чат
	*/
	checkNew: function() {
		$.ajax({
			method: "POST",
			dataType: "json",
			url: url,
			data: {"room": room, "get_all": 1},
			success: function (data) {
				for (let i in data) {
					if (data[i]['msg'] && last_msg_time != data[i]['time']) {
						last_msg_time = data[i]['time'];
						var name = Object.keys(data);
						$(".send_box_all").before('<div class="bubble sender middle"><b>'+name+'</b> - '+data[i]['msg']+'</div>');
					}
				}
				$('.discussion_all').scrollTop($('.discussion_all')[0].scrollHeight);
			}
		});
	},	
	/*
	* Пращам текстовото съобщение към общия чат и избраната стая
	*/
	sendMsg: function() {
		let message = $('.message_box_all').val();
		if (message) {
			var dateClass = new Date();
			last_msg_time = dateClass.getTime();
			$(".send_box_all").before('<div class="bubble sender middle"><b>'+recipient+'</b> - '+message+'</div>');
			$('.message_box_all').val('');
			$.ajax({
				method: "POST",
				dataType: "json",
				url: url,
				data: {"room": room, "last_msg_time": last_msg_time, "msg": message, "set_msg_all": 1}
			});
		}
		$('.discussion_all').scrollTop($('.discussion_all')[0].scrollHeight);
	},
	/*
	* добавям активните и премахвам неактивните регистрирани потребители
	*/
	checkUsers: function() {
		$.ajax({
			method: "POST",
			dataType: "json",
			url: url,
			data: {"checkUsers": 1},
			success: function (data) {
				var length = Object.keys(data).length;
				if (length) {
					$(".users").empty();
					for (var i in data) {
						$(".users").append("<div class=\"user\" onClick=\"chat.getChronology(this)\">"+data[i]+"</div>");
					}
				}
			}
		});
	},	
	/*
	* Функциите които ще се изпълняват през определения интервал от време
	*/
	timeout: function() {
		this.checkUsers(); // проверка за нови съобщения в активната стая
		this.checkNew(); // проверка за нови съобщения в активната стая
	}
};