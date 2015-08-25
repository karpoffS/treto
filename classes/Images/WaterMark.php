<?php
/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

namespace Images;

/**
 * Class WaterMark
 * @author Karpov Sergey
 */
class WaterMark
{
	/**
	 * Целевая картинка
	 * @var resource
	 */
	private $im = null;

	/**
	 * Тип целевой картинки
	 * @var int
	 */
	private $type_im = 0;

	/**
	 * Водяной знак
	 * @var resource
	 */
	private $st = null;

	/**
	 * Тип водяного знака
	 * @var int
	 */
	private $type_st = 0;

	/**
	 * Альфа-канал
	 * @var int
	 */
	private $alpha = 75;

	/**
	 * Права доступа на файл
	 * @var null
	 */
	private $permissions = null;

	/**
	 * Компресия файл по умолчанию
	 * @var int
	 */
	private $compression = 75;

	/**
	 * Разрешонные форматы файлов
	 * @var array
	 */
	private $allow_types = [
		'jpg' => IMAGETYPE_JPEG,
		'png' => IMAGETYPE_PNG,
		'gif' => IMAGETYPE_GIF,
	];

	/**
	 * Проверяет наличие расшрения GD и устанашливает окружение для GD
	 * @throws Exception
	 */
	public function __construct() {
		// Проверяем установку библиотеки GD
		if(!extension_loaded('gd')) {
			throw new Exception('GD не установлено. Обратитесь к администратору вашего сайта!');
		}
    }

	/**
	 * Очистка помяти во время удаления объекта
	 */
	public function __destruct() {
		$this->destroy('im');
		$this->destroy('st');
	}

	/**
	 * Устанавливает уровень альфа-канала
	 * @param int $alpha
	 */
	public function setAlpha($alpha) {
		$this->alpha = $alpha;
	}

	/**
	 * Получение уровня альфа-канала
	 * @return int
	 */
	public function getAlpha() {
		return $this->alpha;
	}

	/**
	 * Задаёт *nix права на файл
	 * @param int $permissions
	 * @throws Exception
	 */
	public function setPermissions($permissions = 0655) {
		if(is_int($permissions)){
			$this->permissions = $permissions;
		} else{
			throw new Exception("Wrong param: is not integer!");
		}
	}

	/**
	 * Устанавливает уровень компрессии
	 * @param int $compression
	 */
	public function setCompression($compression = 100) {
		if(is_int($compression)){
			$this->compression = $compression;
		} else{

		}
	}

	/**
	 * Возвращает уровень компресии
	 * @return int
	 */
	public function getCompression() {
		return $this->compression;
	}

	/**
	 * Устанавливает тип картинки в объект
	 * @param $file
	 * @param string $type
	 */
	private function setTypes($file, $type = 'im') {
		$this->type_{$type} = getimagesize($file)[2];
	}

	/**
	 * Загружает файл в память
	 * @param $file
	 * @param string $type
	 * @throws Exception
	 */
	private function load($file, $type = 'im'){

		if(file_exists($file)){

			/** @var integer $image_info */
			$this->setTypes($file, $type);

			if( $this->type_{$type} == IMAGETYPE_JPEG ) {
				$this->{$type} = imagecreatefromjpeg($file);

			} elseif( $this->type_{$type} == IMAGETYPE_GIF ) {
				$this->{$type} = imagecreatefromgif($file);

			} elseif( $this->type_{$type} == IMAGETYPE_PNG ) {
				$this->{$type}  = imagecreatefrompng($file);
			}
		} else {
			throw new Exception("File not found!");
		}
	}

	/**
	 * Загружает целевую картинку
	 * @param string $file
	 * @throws Exception
	 */
	public function loadImage($file){
		$this->load($file, 'im');
	}

	/**
	 * Загружает файл водяного знака
	 * @param string $file
	 * @throws Exception
	 */
	public function loadStamp($file) {
		$this->load($file, 'st');
	}

	/**
	 * Получаем ширину картинки
	 * @param string $res
	 * @return int
	 */
	public function getWidth($res = 'im') {
		return imagesx($this->{$res});
	}

	/**
	 * Получаем высоту картинки
	 * @param string $res
	 * @return int
	 */
	public function getHeight($res = 'im') {
		return imagesy($this->{$res});
	}

	/**
	 * Изменяем пропорционально размер по высоте
	 * @param int $height
	 * @param string $res
	 */
	public function resizeToHeight($height, $res = 'im') {
		$ratio = $height / $this->getHeight($res);
		$width = $this->getWidth($res) * $ratio;
		$this->resize($width, $height, $res);
	}

	/**
	 * Изменяем пропорционально по ширине
	 * @param int $width
	 * @param string $res
	 */
	public function resizeToWidth($width, $res = 'im') {
		$ratio = $width / $this->getWidth($res);
		$height = $this->getHeight($res) * $ratio;
		$this->resize($width, $height, $res);
	}

	/**
	 * Изменение размер в процентном соотношении от оригинала
	 * @param int $scale
	 * @param string $res
	 */
	public function scale($scale, $res = 'im') {
		$width = $this->getWidth($res) * $scale/100;
		$height = $this->getHeight($res) * $scale/100;
		$this->resize($width, $height, $res);
	}

	/**
	 * Изменет картинку в заданых диапазонах
	 * @param int $width
	 * @param int $height
	 * @param string $res
	 */
	public function resize($width, $height, $res = 'im') {
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled(
			$new_image, $this->{$res},
			0, 0, 0, 0,
			$width, $height,
			$this->getWidth($res), $this->getHeight($res)
		);
		$this->{$res} = $new_image;
	}

	/**
	 * Сохраняет в файл
	 * @param string $file
	 * @param string $type
	 * @throws Exception
	 */
	public function save($file, $type = 'jpg') {

		if(isset($this->allow_types[$type])){

			if( $this->allow_types[$type] == IMAGETYPE_JPEG ) {
				imagejpeg($this->im,$file,$this->getCompression());
			} elseif( $this->allow_types[$type] == IMAGETYPE_GIF ) {
				imagegif($this->im,$file);
			} elseif( $this->allow_types[$type] == IMAGETYPE_PNG ) {
				imagepng($this->im,$file);
			}

			if( $this->permissions != null) {
				chmod($file,$this->permissions);
			}
		} else {
			throw new Exception("Wrong image format!");
		}
	}

	/**
	 * Выводит напрямую в поток
	 * @param string $type
	 * @throws Exception
	 */
	public function output($type = 'jpg') {

		if(in_array($type, $this->allow_types)){

			if( $this->allow_types[$type] == IMAGETYPE_JPEG ) {
				imagejpeg($this->im);
			} elseif( $this->allow_types[$type] == IMAGETYPE_GIF ) {
				imagegif($this->im);
			} elseif( $this->allow_types[$type] == IMAGETYPE_PNG ) {
				imagepng($this->im);
			}

		} else {
			throw new Exception("Wrong image format!");
		}
	}

	/**
	 * Функция для сохранения результата в переменную
	 * @param string $type
	 * @return string
	 */
	public function toVar($type='jpg') {
		ob_start();
		$this->output($type);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	/**
	 * Добавление водяного знака текстом
	 * @throws Exception
	 */
	public function addText() {

		// First we create our stamp image manually from GD
		$stamp = imagecreatetruecolor(100, 70);
		imagefilledrectangle($stamp, 0, 0, 99, 69, 0x0000FF);
		imagefilledrectangle($stamp, 9, 9, 90, 60, 0xFFFFFF);

		imagestring($stamp, 3, 12, 20, '(c) Karpoff', 0x0000FF);
		imagestring($stamp, 3, 15, 40, date("Y-m-d"), 0x0000FF);

		// Set the margins for the stamp and get the height/width of the stamp image
		$marge_right = 10;
		$marge_bottom = 10;
		$sx = imagesx($stamp);
		$sy = imagesy($stamp);

		// Соединяем картинку со штампом
		imagecopymerge($this->im, $stamp, $this->getWidth() - $sx - $marge_right, $this->getHeight() - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp), $this->getAlpha());

	}

	/**
	 * Добавление водяного знака из изображения
	 */
	public function addStamp() {

		if($this->type_im !== $this->type_st){
			$this->im = $this->toVar($this->getType($this->type_st));
		}

		// Если водяной знак не вмещяется в картинку, то уменьшаем его
		if($this->getWidth('st') > $this->getWidth())
			$this->resizeToWidth(ceil($this->getWidth('st')/2.67), 'st');

		elseif($this->getHeight('st') > $this->getHeight())
			$this->resizeToHeight(ceil($this->getHeight('st')/2.67), 'st');

		// Расчёт позиции
		$dest_x = $this->getWidth() - $this->getWidth('st') - 5;
		$dest_y = $this->getHeight() - $this->getHeight('st') - 5;

		imagecopy(
			$this->im, $this->st,
			$dest_x, $dest_y,
			0, 0,
			$this->getWidth('st'), $this->getHeight('st')
		);
	}

	/**
	 * Возвращает тип искомого элемента
	 * @param $type
	 * @return int|string
	 */
	private function getType($type) {
		foreach ($this->allow_types as $key => $value) {
			if($value === $type)
				return $key;
		}
	}

	/**
	 * Освобождение памяти
	 */
	public function destroy($res = 'im') {
		if($this->{$res} !== null ){
			$this->type_{$res} = 0;
//			imagedestroy($this->{$res});
			$this->{$res} = null;
		}
	}

}