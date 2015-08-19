/**
 * Created by sergey on 15.08.15.
 */

// Сумарная информация
var img_total_data = [];
var message = $('#message');
var default_text = "Загрузите файл содержащий ссылки";

function loadList(){
	$.ajax({
		url: "/loadlist.php", type: "GET",
		dataType: "json", processData: false,
		contentType: false
	}).done(function( data ) {
		ajax_runner(data);
	});
}

/**
 * Обработчик ajax запросов
 * @param data
 * @returns {boolean}
 */
function ajax_runner(data){
	if(data.status){
		message.html("<div class=\"text-info\">Идёт загрузка данных...</div>");
		parse_data(data.data);
		message.find(".text-info").delay(2000).fadeOut(1000, function() {
			$(this).remove();
			message.html(default_text);
		});

	} else {

		message.html("<div class=\"text-danger\">"+data.message+"</div>");
		message.find(".text-danger").delay(2000).fadeOut(1000, function() {
			$(this).remove();
			message.html(default_text);
		});
	}
	return false;
}

/**
 * Функция отправки файла
 * @returns {boolean}
 */
function uploadfile() {
	var data = new FormData(document.getElementById("uploadform"));
	$.ajax({
		url: "/upload.php",
		type: "POST",
		data: data,
		enctype: 'multipart/form-data',
		dataType: "json",
		processData: false,
		contentType: false
	}).done(function( data ) {
		ajax_runner(data);
	});
	return false;
}

/**
 * Вставляет картинки в контейнер и глобальную перменную
 * @param data
 */
function parse_data(data){
	var str = '';
	for(var i in data){
		data[i].id = "wrpimg"+i;
		img_total_data.push(data[i]);
		str += "<div id=\"wrpimg"+i+"\"><img src=\"/images/"+data[i].name+"\" "+data[i].attr+" /></div>";
	}
	$( "#container" ).append(str);
	grid_maker();
}

/**
 * Функция раскитывает строки и возвращает результат в виде масива
 * @returns {Array}
 */
function calc_rows(){
	var $container 	= $('#container'),
		$wrps = $container.find('div'),
		container_width = $container.width(),
		total_row_witdh = 0,
		col_rows = 0,
		margin = 5,
		array = [];

	// Считаем общюю длину с отступами(l+r)
	$.each(img_total_data, function(i, v){
		total_row_witdh += v.width + (margin*2);
	});

	// Считаем кол-во строк
	col_rows = Math.ceil(total_row_witdh / container_width);

	console.log(
		"container_width: "+container_width,
		"total_row_witdh: "+total_row_witdh,
		"col_rows: "+col_rows
	);
	//return array;
}

/**
 * Запускает расположение картинок
 */
function grid_maker(){
	var $container 	= $('#container'),
		$wrps = $container.find('div'),
		//img_total_width = 0,
		//container_width = $container.innerWidth(),
		container_width = $container.width(),
		percent_container = 75,
		total_this_row = 0,
		margin = 5;

		console.log("container_width: "+container_width);

	// Делаем расчёты по картинкам
	$wrps.each(function() {

		var $img = $(this).children();
		var width = $img.width();
		var freeSpace = 0;

		// Общяя ширина картинок
		total_this_row += width + (margin*2);

		//$(this).css({ margin: margin+"px"});

		// Расчитываем строки
		if(total_this_row > container_width){

			// Находим пустое место в конце строки
			freeSpace = Math.abs((container_width - total_this_row));

			if(!freeSpace < 16)
			width -= freeSpace;
			console.log("id: "+$(this).attr('id'),"freeSpace: "+freeSpace, "width: "+width);
			total_this_row = 0;

		}

		// Задаём размеры контейнеру картинки
		$(this).css({
			width: width+"px",
			height: $img.height()+"px"
		});
	});

	// debug
	//console.log(container_width, img_total_width);
	//console.log("img_total_data: "+img_total_data.length);
}

$(window).resize(function () {
	grid_maker();
});


