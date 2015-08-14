<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 14.08.15
 * Time: 16:17
 */

// Путь сохранения
$savePath = "./images/";

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

	// Парсим Урл и делаем красивый
	$url = parse_url(urldecode($string));

	// Убираем лишние символы
	$url['path'] = str_replace(".jpg__", ".jpg", $url['path']);

	// Разбираем по косточкам путь к файлу
	foreach(explode("/", $url['path']) as $img){

		// Ищем файл
		if(preg_match("/jpg/i", $img)){
			echo "Wrote to file: ".$img;
			// Сохраняем файлы
			$fp = fopen($savePath.$img, "w");
			fwrite($fp, file_get_contents($string, false, $context));
			fclose($fp);

		}
	}
}
