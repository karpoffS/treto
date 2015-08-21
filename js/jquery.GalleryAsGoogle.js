/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

/**
 * Галлерея Стена как у Гуугла и Яндекса
 * @type {{run, resize}}
 */
var Gallery = (function(){

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
	function setConfig(opt){

		opt.margin = opt.margin || 0;


		opt.debug = opt.debug || false;

		opt.container = opt.container || "#container-gallery";

		options = opt;

		return;
	}

	/**
	 * Метод проверки на обновления в основном массиве
	 * @param data
	 * @returns {boolean}
	 */
	function checkUpdate(data){
		if(data.length != lastArrayCount){
			lastArrayCount = data.length;
			return true;
		}

		return false;
	}

	/**
	 * Основной метод запуска галлереи
	 */
	function startGallery() {

		elements = [];
		nowLineWidth = 0;

		// Узнаём ширину контейнера
		containerWidth = $(options.container).width();

		// Перебираем массив
		for (var i in imgSrc) {

			// Набиваем массив кол-вом елементов в строке
			elements.push(id_item+i);

			nowLineWidth += (imgSrc[i]['width'] + options.margin * 2);

			if (nowLineWidth >= containerWidth) {

				// Узнаём разницу ширины строки
				var diffWidthRow = (nowLineWidth - containerWidth);

				$(id_item+i).css({
					width: imgSrc[i]['width']-diffWidthRow+"px"
				});

				// Устанавливаем ширину строки
				setWidthElementsInRows(diffWidthRow);

				// Сбрасываем текущюю ширину
				nowLineWidth = 0;

				// Сбразываем текущее кол-во елементов в строке
				elements = [];
			}
		}
	}

	/**
	 * Установка ширины элементов в строке
	 * @param diffWidthRow
	 */
	function setWidthElementsInRows(diffWidthRow) {

		//Количество картинок
		var countImages = elements.length;

		// По сколько отнимать ширину от каждой картинки
		var diffToEveryImg = Math.ceil(diffWidthRow / countImages);

		// Получаем расчитаный массив ширин
		var newElements = calculatingWidthInRow(countImages, diffWidthRow, diffToEveryImg);

		// Устанвливаем ширины
		for (var i in newElements) {
			var elem = $('#' + newElements[i]['id']);
			var id = newElements[i]['id'].replace(id_item, '');
			var orginalWidth = imgSrc[id]['width'];
			var calcWidth = newElements[i]['width'];
			var newWidth = (orginalWidth - calcWidth);

			// Устанвливаем ширину
			elem.css({
				'width': newWidth + 'px'
			});

			// Отодвигаем влево если это небходимо
			elem.children('img').css({
				marginLeft: -Math.abs(Math.ceil( calcWidth ))+"px"
			});
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
		for (var i in elements) {

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
		 * Инициализация
		 * @param c
		 */
		init: function (c) {
			// Утанавливаем настройки
			setConfig(c);

			return;
		},

		/**
		 * Метод запуска
		 * @param array
		 */
		add: function (array) {

			// Сохраняем массив
			imgSrc = array ? array : [];

			// Проверяем на существоквание данных
			if (
				(imgSrc.length > 0 &&
				$(options.container+' > div').length === 0)
				|| checkUpdate(imgSrc)
			) {

				$(options.container).html('');
				var str = '';
				var strLoad = 'onLoad="Gallery.start()"';

				for (var i in imgSrc) {

					str += '<div id="'+id_item+i + '" class="item" ' +
						'style="margin:'+options.margin+'px; width: ' + imgSrc[i]['width'] +
						'px; height: ' + imgSrc[i]['height'] + 'px;">' +
						'<img src="images/' + imgSrc[i]['name'] + '" ' +
						'style="width: ' + imgSrc[i]['width'] + 'px; ' +
						'height: ' + imgSrc[i]['height'] + 'px;" '+
						((imgSrc.length-1) == i ? strLoad : '')+'></div>';
				}
				$(options.container).append(str);
			}

			if(options.debug){
				console.log("Waiting loading "+imgSrc.length+" items in gallery...");
			}

			return;
		},

		/**
		 * Метод получения кол-ва эллементов
		 * @returns {Number}
		 */
		getTotalElements: function () {
			return imgSrc.length ;
		},

		/**
		 * Метод обёртка для запуска галлереи
		 */
		start: function () {
			startGallery();

			if(options.debug) {
				console.log("Gallery starting...");
			}
			return true;
		},

		/**
		 * Пересчёт при изменении размеров контейнера
		 */
		resize: function () {
			// Если ширина изменилась то запускаем перерасчёт
			if ($(options.container).width() !== containerWidth) {
				startGallery();
			}

			return;
		}
	};
}());