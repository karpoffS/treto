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
		img_total_data.push(data[i]);

		str += "<div><img src=\"/images/"+data[i].name+"\" style=\"width: "+data[i].width+"px; height: "+data[i].height+"px;\" /></div>";
		//str += "<div id=\"wrapper"+i+"\"><img id=\"img"+i+"\" src=\"/images/"+data[i].name+"\" style=\"width: "+data[i].width+"px; height: "+data[i].height+"px;\" /></div>";
	}
	$( "#container" ).append(str);
	grid_maker();
}

/**
 * Запускает расположение картинок
 */
function grid_maker(){
	var $container 	= $('#container'),
		$imgs = $container.find('img'),
		img_total_width = 0,
		container_width = $container.innerWidth(),
		total_this_row = 0,
		margin = 5;

	// Делаем расчёты по картинкам
	$imgs.each(function(i) {
		total_this_row += $(this).width() + (margin*2);
		//$(this).css({marginLeft: "-"+margin+"px"});
		$(this).parent().css({ margin: margin+"px"});
		$(this).parent().css({
			width: $(this).width()+"px",
			height: $(this).height()+"px"
		});
		// Расчитываем строки
		if(total_this_row > container_width){
			total_this_row = 0;
		}
	});

	// debug
	console.log(container_width, img_total_width);
	console.log("img_total_data: "+img_total_data.length);
}


