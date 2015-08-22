<?php
/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 * @todo Причесать данный класс
 */

namespace Lang;

/**
 * Class Detector
 *
 * @package Lang
 */
class Detector
{
	var $language = null;


	public function __construct()
	{
		if ($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
				$this->language = array_combine($list[1], $list[2]);
				foreach ($this->language as $n => $v)
					$this->language[$n] = $v ? $v : 1;
				arsort($this->language, SORT_NUMERIC);
			}
		} else $this->language = array();
	}

	public function getBrowserPriorityLang($default, $langs)
	{
		$languages=array();
		foreach ($langs as $lang => $alias) {
			if (is_array($alias)) {
				foreach ($alias as $alias_lang) {
					$languages[strtolower($alias_lang)] = strtolower($lang);
				}
			}else $languages[strtolower($alias)]=strtolower($lang);
		}
		foreach ($this->language as $l => $v) {
			$s = strtok($l, '-'); // убираем то что идет после тире в языках вида "en-us, ru-ru"
			if (isset($languages[$s]))
				return $languages[$s];
		}
		return $default;
	}

}