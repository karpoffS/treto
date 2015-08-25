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
 * Обект хранящий языковые ключи
 * @type {Object}
 */
var Languages = {};

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
			url: "/backend.php",
			type: "GET",
			dataType: "json",
			data: {list:1}
		}).done(function(data){
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

		// Инициализируем модуль сообщений
		Messages.init({
			container: "#message",
			delay: 2000,
			fadeOut: 1000,
			defaultString: Main.getLang("system", "LoadFileLinks" ),
			callBackArgs: true,
			callback: function(array){
				Main.appendToArray(array);
				return true;
			}
		});

		if(result.status){
			Messages.message(this.getLang("system", "LoadMessage"), "text-info", result.data);
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
	},

	/**
	 * Метод возвращает строку
	 * @param obj
	 * @returns {string}
	 */
	getLang: function (section, key) {

		if (arguments.length < 2) {
			throw new Error("This method call is " + arguments.length
				+ " arg, needed 2 args. section and key");
		}

		if(Languages.hasOwnProperty(section)){
			if(Languages[section].hasOwnProperty(key)){
				return Languages[section][key];
			}else {
				throw new Error("Key not found!");
			}
		} else {
			throw new Error("Section not found!");
		}
	},

	/**
	 * Загружает язык с сервера
	 * @param item
	 */
	loadLang: function(section){

		$.ajax({
			url: "/backend.php",
			type: "GET",
			dataType: "json",
			async: false,
			data: {lang: section}
		}).done(function(result){

			// Добавляем ленги
			for(var item in result.data){
				Languages[item] = result.data[item];
			}
		});

		return;
	}
};

