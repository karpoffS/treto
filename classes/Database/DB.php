<?php
/**
 * Created by PhpStorm.
 * User: Karpov Sergey
 */

namespace Database;


class MySQL_Client
{
	/**
	 * Настройки подключения
	 */
	const SERVER = 'localhost';
	const USERNAME = 'treto_test';
	const PASSWORD = 'bbR3L8bHEBBB2K7P';
	const DATABASE = 'treto_test';

	/**
	 * Объект подключения БД MySql
	 * @var null
	 */
	private $mysqli = null;

	/**
	 * Данные как есть
	 * @var array
	 */
	private $unquote = ['NULL', 'NOW()'];

	/**
	 * DB constructor.
	 * @throws Exception
	 */
	public function __constuct()
	{
		$this->mysqli = new mysqli(
			self::SERVER, self::USERNAME,
			self::PASSWORD, self::DATABASE
		);

		if (mysqli_connect_errno()) {
			trigger_error ('DB Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
			throw new Exception('Sorry!  We were unable to connect to the database.  Please try again.');
		}
	}


	/**
	 * Экранирование чего либо
	 * @param $str
	 * @return string
	 */
	function escape($str){
		$str = get_magic_quotes_gpc() ? stripslashes($str) : $str;
		$str = function_exists("mysqli_real_escape_string") ?
			mysqli_real_escape_string($this->mysqli,$str) : mysqli_escape_string($str);
		return (string)$str;
	}

	/**
	 * Пропускает только безопасные значения
	 * @param mixed $var the value to secure
	 * @return string Secure value
	 */
	function secure($var){
		if(is_object($var) && isset($var->scalar) && count((array)$var)==1){
			$var = (string)$var->scalar;
		}elseif(is_string($var)){
			$var = trim($var);
			$var = "'".$this->escape($var)."'";
		}elseif(is_int($var)){
			$var = intval((int)$var);
		}elseif(is_float($var)){
			$var = "'".round(floatval(str_replace(",",".",$var)),6)."'";
		}elseif(is_bool($var)){
			$var = (int)$var;
		}elseif(is_array($var)){
			$var = NULL;
		}
		$var = iconv("UTF-8", "UTF-8", $var);
		return ($var != "") ? $var  : "NULL";
	}

	/**
	 * Выполняет запрос
	 * @param $query
	 * @return mixed
	 */
	public function query($query) {
		if (!$result = $this->mysqli->query($query))
			/** @var string $this->mysqli->error */
			trigger_error("Query: {$query}<br />Error: {$this->mysqli->error}");
		return $result;
	}

	/**
	 * Вставляет данные
	 * @param $table
	 * @param $array
	 * @return mixed
	 * @throws Exception
	 */
	public function insert ($table, $array) {

		if(strlen($table)==0){
			throw new Exception("invalid table name");
		}
		if(count($array)==0){
			throw new Exception("empty data to INSERT");
		}

		$data = [];
		foreach ($array as $key => $value) {
			$columns[] = $key;
			if (is_numeric($value) || in_array($value, $this->unquote)) {
				$data[] = $value;
			} else {
				$data[] = "'{$this->secure($value)}'";
			}
		}

		/** @var array $columns */
		$col_string = implode('`, `', $columns);
		$values = implode(', ', $data);

		$sql = 'INSERT INTO `'.$table.'` (`'.$col_string.'`) VALUES ('.$values.')';
		$this->query($sql);
		return $this->mysqli->insert_id;
	}

	/**
	 * Обновляет данные
	 * @param $table
	 * @param $array
	 * @param $where
	 * @param string $limit
	 * @return mixed
	 * @throws Exception
	 * @internal param $column
	 * @internal param $id
	 * @internal param string $add
	 */
	public function update($table, $array, $where, $limit='') {

		if(strlen($table)==0){
			throw new Exception("invalid table name");
		}
		if(count($array)==0){
			throw new Exception("empty data to UPDATE");
		}

		foreach ($array as $key => $value) {
			if (is_numeric($value) || in_array($value, $this->unquote)) {
				$data[] = "`" . $key . "`=" . $value;
			} else {
				$data[] = "`" . $key . "`='{$this->secure($value)}'";
			}
		}

		$sql = "UPDATE `{$table}` SET " . implode(', ', $data) . " WHERE {$where} {$limit}";

		$this->query($sql);

		return $this->mysqli->affected_rows;
	}

	/**
	 * Удаляет данные
	 * @param $table
	 * @param $array
	 * @return mixed
	 * @throws Exception
	 */
	public function delete($table, $array){

		if(strlen($table)==0){
			throw new Exception("invalid table name");
		}
		if(count($array)==0){
			throw new Exception("empty data to DELETE");
		}

		foreach ($array as $key => $value) {
			$data[] = "`" . $key . "`='{$this->secure($value)}'";
		}

		$sql = "DELETE FROM $table WHERE (".implode('AND ', $data).")";
		return $this->query($sql);
	}

	/**
	 * Делает выборку данных
	 * @param $table
	 * @param array $row
	 * @param $array
	 * @return mixed
	 * @throws Exception
	 */
	public function select($table, $row = ['*'] , $array){

		if(strlen($table)==0){
			throw new Exception("invalid table name");
		}
		if(count($array)==0){
			throw new Exception("empty data to SELECT");
		}

		foreach ($row as $key) {
			$data[] = "`".$key."`";
		}

		foreach ($array as $key => $value) {
			if (is_numeric($value) || in_array($value, $this->unquote)) {
				$data[] = "`" . $key . "`=" . $value;
			} else {
				$data[] = "`" . $key . "`='{$this->secure($value)}'";
			}
		}

		$sql = "SELECT FROM $table WHERE (".implode('AND ', $data).")";
		return $this->query($sql);
	}
}