<?php
/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

namespace Lang;

/**
 * Class Langs
 *
 * @package Lang
 */
class Langs
{

	/**
	 * Массив загруженых языков
	 * @var array
	 */
	private $lang = [];

	/**
	 * Конфиг локалей
	 * Вписывать языки по кодам https://ru.wiktionary.org/wiki/Викисловарь:ISO_639
	 * @var array
	 */
	protected $configLocales = [
		// Русскую локаль
		'ru' => [
			'ru', // Россиянам
			'be', // Беларусам
			'uk', // Украинцам
			'ky', // Киргизам
			'ab', // Абхазам
			'mo', // Молдаваням
			'et', // Эстонцам
			'lv' // Латышам
		],

		// Английскую локаль
		'en' => 'en'
	];

	/**
	 * Язык по умолчанию
	 * @var string
	 */
	protected $defaultLocale = 'en';

	/**
	 * Текущяя локаль
	 * @var string
	 */
	private $currentLocale = 'en';

	/**
	 * Объект детектора языка пользователя
	 * @var object
	 */
	private $Detector;

	/**
	 * Langs constructor.
	 */
	public function __construct()
	{
		// Load browser detector
		$this->Detector = new Detector();

		// set current locale
		$this->currentLocale = $this->Detector
			->getBrowserPriorityLang(
				$this->defaultLocale,
				$this->configLocales
			);
	}

	/**
	 * Возвращает языки
	 * @return array
	 */
	public function getLang($section, $key = null, $lang = null){

		// Set locale, if not set user
		$locale = ($lang === null) ? $this->currentLocale : $lang;

		// Load lang section
		$this->lang = $this->load($section, $locale);

		// If set key return key
		if(is_string($key)){

			// Clean
			$key = trim($key);

			return $this->lang[$section][$key];
		}

		return $this->lang;
	}

	/**
	 * Загружает файл языка
	 * @param $section
	 * @return array
	 * @throws Exception
	 */
	private function load($section, $lang)
	{

		if($section !== '' && strlen($section) > 0){

			// Проверяем сущестрование файла
			if(file_exists($path = \Main::getLangPath()."/{$lang}/{$section}.php")){

				// Возвращаем результат
				return [
					$section => include($path)
				];

			} else {
				throw new Exception("{$path} file not found!");
			}

		} else {

			throw new Exception("Lang not set.");
		}
	}

}