<?php
/**
 * Created by PhpStorm.
 * User: Karpov Sergey
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

					// Отправляем ответ
					Main::jsonMessage([
						'status' => true,
						'data' => count($build) > 0 ? $build : Main::loadList()
					]);

				} else {
					throw new Exception("Файл имеет длину 0 байт!");
				}
			} else {
				throw new Exception("Вы ничего не загрузили!");
			}
		}
	} else {

		$result = Main::loadList();
		if(count($result) > 0){
			// Отправляем ответ
			Main::JsonMessage([
				'status' => true,
				'data' => Main::loadList()
			]);
		}

		throw new Exception("Галерея пуста, пожайлуста загрузите файл загрузок!");

	}
} catch (Exception $e) {

	// Отправляем ответ
	Main::jsonMessage([
		'status' => false,
		'message' => $e->getMessage()
	]);
}


