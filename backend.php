<?php
/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

// Запускаем Autoloader
require_once("bootstrap.php");

// Устанавливаем настройки путей
\Main::setSavePath(__DIR__."/images/");
\Main::setUploadPath(__DIR__."/uploads/");
\Main::setLangPath(__DIR__."/languages/");

$Request = $_SERVER['REQUEST_METHOD'];

try {
	if($Request === 'POST'){

		// Обработчик загрузки файла
		if(isset($_POST["upload"])){

			if($_FILES["fileToUpload"]["type"] === "text/plain"){

				if ($_FILES["fileToUpload"]["size"] > 0) {

					/** @var array $data */
					$data = \Main::parseFile($_FILES["fileToUpload"]["tmp_name"]);

					$build = \Main::buildImages($data);

					// Отправляем ответ
					\Main::jsonMessage([
						'status' => true,
//						'data' => count($build) > 0 ? $build : \Main::loadList()
						'data' => $build
					]);

				} else {
					throw new Exception("Файл имеет длину 0 байт!");
				}
			} else {
				throw new Exception("Вы ничего не загрузили!");
			}
		}

	} else if($Request === 'GET'){

		if(isset($_GET["list"]) && intval($_GET["list"]) == 1){

			$result = \Main::loadList();

			if(count($result) > 0){

				// Отправляем ответ
				\Main::JsonMessage([
					'status' => true,
					'data' => $result
				]);

			} else {
				throw new Exception("Галерея пуста, пожайлуста загрузите файл загрузок!");
			}
		}

		if(isset($_GET["lang"]) && is_string($_GET["lang"])){
			// Отправляем ответ
			\Main::JsonMessage([
				'status' => true,
//				'data' => ((new \Lang\Langs())->getLang(trim($_GET["lang"])))
				'data' =>\Main::getLangs(trim($_GET["lang"]))
			], true);
		}

		throw new Exception("error");

	}
} catch (Exception $e) {

	// Отправляем ответ
	\Main::jsonMessage([
		'status' => false,
		'message' => $e->getMessage()
	]);
}


