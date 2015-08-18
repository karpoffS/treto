<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 17.08.15
 * Time: 12:56
 */

require_once("inc/Main.php");
//require_once("inc/DB.php");

// Устанавливаем настройки путей
Main::setSavePath(__DIR__."/images/");
Main::setUploadPath(__DIR__."/uploads/");

try {

	// Отправляем ответ
	Main::JsonMessage([
		'status' => true,
		'data' => Main::loadList()
	]);

} catch (Exception $e) {

	// Отправляем ответ
	Main::JsonMessage ([
		'status' => false,
		'message' => $e->getMessage()
	]);
}
