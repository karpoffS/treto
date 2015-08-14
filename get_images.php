<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 14.08.15
 * Time: 16:17
 */

// Путь сохранения
$savePath = "images/";

// Настройки потока
$opts = array(
	'http'=>array(
		'method'=>"GET",
		'header'=>"Accept-language: en\r\n"
	)
);

// Создаём поток
$context = stream_context_create($opts);

// Создаём объект файла
$file = new SplFileObject("test.txt");

// Читаем файл до конца
while (!$file->eof()) {
	$string = $file->fgets();

	// обрезаем лишнее
	$string = trim($string);

	// Парсим Урл и делаем красивый
	$url = parse_url(urldecode($string));

	// Разбираем по косточкам путь к файлу
	foreach(explode("/", $url['path']) as $img){

		// Ищем файл
		if(preg_match("/jpg/i", $img)){

			// Сохраняем файлы
			file_put_contents(
				$savePath.$img, // куда сохранить
				file_get_contents($string, false, $context) // Получаем файл
			);
		}
	}
}
