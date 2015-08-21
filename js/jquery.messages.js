/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

/**
 * Модуль сообщений
 * @type {{init, message}}
 */
var Messages = (function(){

	"use strict";

	/**
	 * Конфигурация модуля
	 * @type {{}}
	 */
	var options = {};

	/**
	 * Метод проверки и установки конфигурации
	 * @param opt
	 */
	function setConfig(opt){

		opt.delay = opt.delay || 1000;

		opt.fadeOut = opt.fadeOut || 1000;

		opt.container = opt.container || "#messages";

		opt.callBackArgs = opt.callBackArgs || false;

		opt.callBack = opt.callback || function(){return;};

		opt.defaultString = opt.defaultString || "Default message string";

		options = opt;
		return true;
	}

	return {

		/**
		 * Метод инициализации модуля
		 * @param opt
		 * @param callback
		 */
		init: function(opt){
			if(typeof opt !== "object")
				throw new Error("Is not object!");

			setConfig(opt);

			return;
		},

		/**
		 * Метод выводит сообщения
		 * @param text
		 * @param class_name
		 * @param data
		 */
		message: function(text, class_name, args){

			args = args || [];

			if (arguments.length < 2) {
				throw new Error("This method call is " + arguments.length
					+ " arg, needed 2 args. text, class_name");
			}

			if(typeof text !== "string" || text === "")
				throw new Error("text is empty!");

			if(typeof class_name !== "string" || class_name === "")
				throw new Error("class_name is empty!");

			// Вставляем текст
			$(options.container).html("<div class=\""+class_name+"\">"+text+"</div>");

			// Вызываем callback
			if(options.callBackArgs && args.length > 0){
				options.callback(args);
			} else {
				options.callback();
			}

			// Скрываем сообщение
			$(options.container).children().delay(options.delay).fadeOut(options.fadeout, function() {
				$(this).remove();

				// Возвращаем строку по умолчанию
				$(options.container).html(options.defaultString);
			});

			return;
		}
	};
}());
