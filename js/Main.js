/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

"use strict";

/**
 * Массив загруженых данных
 * @type {Array}
 */
var total_data_gallery = [];

/**
 * Основной модуль приложения
 * @type {{loadList: Function, SubmitForm: Function, handlerAjax: Function, appendToArray: Function}}
 */
var Main = {
	/**
	 * Загружает список во время старта
	 */
	loadList: function(){
		$.ajax({
			url: "/backend.php", type: "GET",
			dataType: "json", processData: false,
			contentType: false
		}).done(function( data ) {
			Main.handlerAjax(data);
		});
	},

	/**
	 * Функция отправки файла
	 * @returns {boolean}
	 */
	SubmitForm: function() {
		var data = new FormData(document.getElementById("uploadform"));
		$.ajax({
			url: "/backend.php",
			type: "POST",
			data: data,
			enctype: 'multipart/form-data',
			dataType: "json",
			processData: false,
			contentType: false
		}).done(function( data ) {
			Main.handlerAjax(data);
		});
		return false;
	},

	/**
	 * Обработчик ajax запросов
	 * @param result
	 * @returns {boolean}
	 */
	handlerAjax: function(result){
		if(result.status){
			Messages.message("Идёт загрузка данных...", "text-info", result.data);
		} else {
			Messages.message(result.message, "text-danger");
		}
		return false;
	},

	/**
	 * Дополняет массив загруженых данных
	 * @param data
	 */
	appendToArray: function(data){
		for(var i in data){
			total_data_gallery.push(data[i]);
		}

		// Инициализаруем галерею
		Gallery.init({
			container: "#gallery",
			margin: 5
		});

		// Запускаем галлерею
		Gallery.add(total_data_gallery);
	}
};
