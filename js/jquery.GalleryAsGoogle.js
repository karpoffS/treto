/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

/**
 * Галлерея Стена как у Гуугла и Яндекса
 * @type {{run, resize}}
 */
var Gallery;
Gallery = function () {

	"use strict";

	/**
	 * Конфигурация модуля
	 * @type {{}}
	 */
	var options = {};

	/**
	 * Id galery items
	 * @type {string}
	 */
	var id_item = "gallery_item_";

	/**
	 * Шаблон для вставки
	 * @type {string}
	 */
	var tpl = '<div id="{id}" class="item" style="margin: {margin}px; width: {width}px; height: {height}px;"> <img src="{url}" style=" width: {width}px; height: {height}px;" {onLoad}></div>';

	/**
	 * Масив с данными
	 * @type {Array}
	 */
	var imgSrc = [];

	/**
	 * Счётчик для отслеживания измененй
	 * @type {number}
	 */
	var lastArrayCount = 0;

	/**
	 * Текущая ширина контейнера
	 * @type {number}
	 */
	var containerWidth = 0;

	/**
	 * Ширина текущей линии
	 * @type {number}
	 */
	var nowLineWidth = 0;

	/**
	 * Массив для наполнения id для последующего изменения ширин этих эл-тов
	 * @type {Array}
	 */
	var elements = [];

	/**
	 * Метод проверки и установки конфигурации
	 * @param opt
	 */
	function setOptions(opt) {

		opt.margin = opt.margin || 0;


		opt.debug = opt.debug || false;

		opt.container = opt.container || "#container-gallery";

		options = opt;

		return true;
	}

	/**
	 * Метод проверки на обновления в основном массиве
	 * @param data
	 * @returns {boolean}
	 */
	function checkUpdate(data) {
		if (data.length != lastArrayCount) {
			lastArrayCount = data.length;
			return true;
		}

		return false;
	}

	/**
	 * Возвращает имя контейнера
	 * @returns {options.container}
	 */
	function getContainer() {

		/** @namespace options.container */
		return options.container;
	}

	/**
	 * Шаблонизатор
	 * @param template
	 * @param data
	 * @returns {void|XML|string}
	 */
	function templateParser(template, data) {
		return template.replace(/\{(\w*)\}/g, function (m, key) {
			return data.hasOwnProperty(key) ? data[key] : "";
		});
	}

	/**
	 * Основной метод запуска галлереи
	 */
	function startGallery() {

		elements = [];
		nowLineWidth = 0;

		// Узнаём ширину контейнера
		containerWidth = $(getContainer()).width();

		// Перебираем массив
		var i, len = imgSrc.length;
		for (i = 0; i < len; i++) {

			// Набиваем массив кол-вом елементов в строке
			elements.push(id_item + i);

			nowLineWidth += (imgSrc[i]['width'] + options.margin * 2);

			if (nowLineWidth >= containerWidth) {

				// Узнаём разницу ширины строки
				var diffWidthRow = (nowLineWidth - containerWidth);

				$(id_item + i).css({
					width: imgSrc[i]['width'] - diffWidthRow + "px"
				});

				// Устанавливаем ширину строки
				setWidthElementsInRows(diffWidthRow);

				// Сбрасываем текущюю ширину
				nowLineWidth = 0;

				// Сбразываем текущее кол-во елементов в строке
				elements = [];
			} else {

				if ((imgSrc.length - 1) == i && containerWidth > (containerWidth - nowLineWidth)) {
					// Устанавливаем ширину строки
					setWidthElementsInRows((containerWidth - nowLineWidth), true);

				}
			}
		}
	}

	/**
	 * Установка ширины элементов в строке
	 * @param diffWidthRow
	 */
	function setWidthElementsInRows(diffWidthRow, direction) {

		// Задаём направление расчётов
		direction = direction || false;

		//Количество картинок
		var countImages = elements.length;

		// По сколько отнимать ширину от каждой картинки
		var diffToEveryImg = Math.ceil(diffWidthRow / countImages);

		console.log(elements, diffWidthRow, diffToEveryImg);

		// Получаем расчитаный массив ширин
		var newElements = calculatingWidthInRow(countImages, diffWidthRow, diffToEveryImg);

		// Устанвливаем ширины
		var i, len = newElements.length;
		if(len > 1)
		for (i = 0; i < len; i++) {

			var elem = $('#' + newElements[i]['id']);
			var child = elem.children('img');

			var id = newElements[i]['id'].replace(id_item, '');

			var orginalWidth = imgSrc[id]['width'];
			var calcWidth = newElements[i]['width'];

			// Изменяем расчёты
			if(direction){
				var newWidth = (orginalWidth + calcWidth);
				var elemObj = { 'width': newWidth + 'px'};
				var childObj = { marginLeft: "0px", 'width': newWidth + 'px'};
			}else{
				var newWidth = (orginalWidth - calcWidth);
				var elemObj = { 'width': newWidth + 'px'};
				var childObj = { marginLeft: -Math.abs(Math.ceil(calcWidth)) + "px"};
			}

			// Устанвливаем ширину
			elem.css(elemObj);

			// Отодвигаем влево если это небходимо
			child.css(childObj);
		}
	}

	/**
	 * Метод расчёта ширины елеменов в строк
	 * @param countImg
	 * @param residual
	 * @param diffToEveryImg
	 * @returns {Array}
	 */
	function calculatingWidthInRow(countImg, residual, diffToEveryImg) {

		// Сумма разниц на картинку
		var localDiff = (diffToEveryImg * countImg) - residual;

		var newElements = [];

		// Расчитываем строку
		var i, len = elements.length;
		for (i = 0; i < len; i++) {
			// Набиваем массив
			newElements.push({
				'id': elements[i],
				'width': (
					i == (countImg - 1) ?
						(diffToEveryImg - localDiff)
						: diffToEveryImg
				)
			});
		}

		return newElements;
	}

	return {

		/**
		 * Инициализация настроек
		 * @param opt
		 */
		init: function (opt) {
			// Устанавливаем настройки
			return setOptions(opt);
		},

		/**
		 * Метод запуска галереи
		 * @param array
		 */
		add: function (array) {

			// Сохраняем массив
			imgSrc = array ? array : [];

			// Проверяем на существоквание данных
			if (imgSrc.length > 0 && $(options.container + ' > div').length === 0) {

				if (!checkUpdate(imgSrc))
					$(getContainer()).html('');

				var str = '';

				var i, len = imgSrc.length;
				for (i = 0; i < len; i++) {
					str += templateParser(tpl, {
						id: id_item + i,
						margin: options.margin,
						width: imgSrc[i]['width'],
						height: imgSrc[i]['height'],
						url: 'images/' + imgSrc[i]['name'],
						onLoad: ((imgSrc.length - 1) == i ? 'onLoad="return Gallery.start();"' : '')
					});
				}

				$(getContainer()).append(str);
			}

			/** @namespace options.debug */
			if (options.debug) {
				console.log("Waiting loading " + this.getTotalElements() + " items in gallery...");
			}

			return;
		},

		/**
		 * Метод получения кол-ва эллементов
		 * @returns {Number}
		 */
		getTotalElements: function () {
			return imgSrc.length;
		},

		/**
		 * Метод обёртка для запуска галлереи
		 */
		start: function () {
			startGallery();

			/** @namespace options.debug */
			if (options.debug) {
				console.log("Gallery starting...");
			}
			return true;
		},

		/**
		 * Пересчёт при изменении размеров контейнера
		 */
		resize: function () {
			// Если ширина изменилась то запускаем перерасчёт
			if ($(getContainer()).width() !== containerWidth) {
				startGallery();
			}

			return;
		}
	};
}();