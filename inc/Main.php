<?php
/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

/**
 * Class Main
 */
class Main
{
	/**
	 * Путь сохранения готовой продукции
	 * @var string
	 */
	private static $savePath = "";

	/**
	 * Путь загруженых оригиналов
	 * @var string
	 */
	private static $uploadPath = "";


	/**
	 * @return string
	 */
	public static function getSavePath()
	{
		return self::$savePath;
	}

	/**
	 * @param string $savePath
	 */
	public static function setSavePath($savePath)
	{
		self::$savePath = $savePath;
	}

	/**
	 * @return string
	 */
	public static function getUploadPath()
	{
		return self::$uploadPath;
	}

	/**
	 * @param string $uploadPath
	 */
	public static function setUploadPath($uploadPath)
	{
		self::$uploadPath = $uploadPath;
	}

	/**
	 * Парсит приходящий файл и передаёт его на загрузку
	 * @param $input string
	 * @return array
	 * @throws Exception
	 */
	public static function parseFile($input){

		if(!is_string($input) && !file_exists($input))
			throw new Exception("Упс файл куда-то пропал!");

		$result = [];

		// Создаём объект файла
		$file = new SplFileObject($input);

		// Читаем файл до конца
		while (!$file->eof()) {
			$string = $file->fgets();

			// обрезаем лишнее
			$string = trim($string);

			$url = urldecode($string);

			// Парсим Урл и делаем красивый
			$array_url = parse_url($url);

			// Разбираем по косточкам путь к файлу
			foreach(explode("/", $array_url['path']) as $file_name){

				// Ищем файл
				if(preg_match("/jpg/i", $file_name)){

					// Если уже имеется такой же обработаный, пропускаем
					if(file_exists(self::getSavePath().$file_name))
						continue;

					// Формируем массив
					$result[] = $file_name;

					// Сохраняем файл
					self::saveFile([
						'url' => $url,
						'name' => $file_name
					]);
				}
			}
		}

		return $result;
	}

	/**
	 * Загрузка файла по url
	 * @param $file
	 */
	public static function saveFile($file){

		// Создаём поток
		$ctx = stream_context_create(
			[ // Настройки потока
				'http' => [
					'method'=>"GET",
					'header'=>"Accept-language: en\r\n"
				]
			]
		);

		// Сохраняем файл если файл не существует
		if(!file_exists(self::getUploadPath().$file['name'])){
			file_put_contents(
				self::getUploadPath().$file['name'], // куда сохранить
				file_get_contents($file['url'], false, $ctx) // Получаем файл
			);
		}

	}

	/**
	 * Метод для обработки наложения водяных знаков
	 * @param array $array
	 * @return array
	 * @throws Exception
	 */
	public static function buildImages($array = []){
		if(!is_array($array) && count($array) == 0)
			throw new Exception("Нет данных для обработки");

		$result = [];
		$resize = [
			'w' => null, // auto
			'h'=> 200 // manual size px
		];

		$wm = new WaterMark();
		$wm->loadStamp('watermark/watermark.png');

		$cnt = 1;
		foreach($array as $name){
			$hash = md5($name);
			$wm->loadImage(self::getUploadPath().$name);
			$wm->resizeToHeight($resize['h']);

			if($cnt % 2){
				$wm->addStamp();
			} else {
				$wm->addText();
			}

			// Формируем массив для сохранения в бд
			$result[$cnt] = [
				'name' => $name,
				'width' => $wm->getWidth(),
				'height' => $wm->getHeight(),
				'hash' => $hash,
			];
			$cnt++;
			$wm->save(self::getSavePath().$name);
			$wm->destroy();
			//unlink(self::getUploadPath().$name);
			usleep(2000);
		}

		return $result;
	}


	public static function saveToDB($array = []){
		if(!is_array($array) && count($array) == 0)
			throw new Exception("Нет данных для обработки");

	}

	/**
	 * Загружает список из директории
	 * @return array
	 */
	public static function loadList(){
		$result = [];
		foreach (glob(self::getSavePath()."/*.{jpg,gif,png}", GLOB_BRACE) as $filename) {
			list($width, $height, $type, $attr) = getimagesize($filename);
			$result[] = [
				'name' => basename($filename),
				'width' => $width,
				'height' => $height,
				'attr' => $attr,
			];
		}
		return $result;
	}

	/**
	 * Функция отправляет json ответ
	 * @param array $array
	 * @throws Exception
	 */
	public static function jsonMessage($array = []){

		if(!is_array($array) && count($array) == 0)
			throw new Exception("Нет данных для отправки");

		header('Content-Type: application/json');
		die(json_encode($array));
	}

}