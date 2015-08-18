<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 14.08.15
 * Time: 16:17
 */

require_once("inc/common.php");

// Устанавливаем настройки путей
Main::setSavePath(__DIR__."/images/");
Main::setUploadPath(__DIR__."/uploads/");

try {
	if($_SERVER['REQUEST_METHOD'] === 'POST'){

		// Обработчик загрузки файла
		if(isset($_POST["upload"])){

			if($_FILES["fileToUpload"]["type"] === "text/plain"){

				if ($_FILES["fileToUpload"]["size"] > 0) {

					/** @var array $data */
					$data = Main::parseFile($_FILES["fileToUpload"]["tmp_name"]);

					$build = Main::buildImages($data);

					//usleep((2*1000*1000)); // 5 sec

					// Сохраняем результат в базу данных
					//$result = Main::saveToDB($build);

					// Отправляем ответ
					Main::jsonMessage([
						'status' => true,
						'data' => $build
					]);

				} else {
					throw new Exception("Файл имеет 0 байт длину!");
				}
			} else {
				throw new Exception("Вы ничего не загрузили!");
			}
		}
	} else {
		throw new Exception("Не верный формат запроса!");
	}
} catch (Exception $e) {

	// Отправляем ответ
	Main::jsonMessage([
		'status' => false,
		'message' => $e->getMessage()
	]);
}


