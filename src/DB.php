<?php

namespace MuscaKit;

class DB 
{
	public $link = false;
	public $dbname = false;
	public $connected = false;
	
	public $die_on_errors = false;
	private $cache_fields = array();
	
	public function __construct($server, $user, $password='', $dbname='', $encoding='utf8') 
	{
		$this->link = mysqli_connect($server, $user, $password);
		if (!$this->link) return $this->error('Error connecting to the server');
		if ($dbname) $this->setDB($dbname);
		if ($encoding) $this->setEncoding($encoding);
	}

	public function setDB($dbname) 
	{
		if (!mysqli_select_db($this->link, $dbname)) return $this->error('Error setting the database');
		$this->dbname = $dbname;
		$this->connected = true;
	}

	public function setEncoding($encoding) 
	{
		$this->query('SET NAMES '.$encoding);
	}
	
	public function query ($query, $params=false) 
	{
		if ($params) {
			$sth = $this->prepare($query);
			$res = $sth->execute($params);
		} else {
			$res = mysqli_query($this->link, $query);
		}
		if (!$res) return $this->error('Error executing sql query', $query);
		return $res;
	}

	public function getOne($query, $params=false, $idx=0) 
	{
		if (!($res = $this->query($query, $params))) return false;
		$data = mysqli_fetch_row($res);
		mysqli_free_result($res);
		return ($data && isset($data[$idx]))? $data[$idx] : false;
	}

	public function getRow($query, $params=false) 
	{
		if (!($res = $this->query($query, $params))) return false;
		$data = mysqli_fetch_assoc($res);
		mysqli_free_result($res);
		return $data;
	}	
	public function getAssoc($query, $params=false, $field=0) 
	{
		if (!($res = $this->query($query, $params))) return false;
		$finfo = $res->fetch_field_direct($field);	
		$rows = array();
		if (mysqli_num_fields($res)==2) {
			while($row=mysqli_fetch_row($res)) $rows[$row[0]]=$row[1];
		} else {
			while($row=mysqli_fetch_assoc($res)) $rows[$row[$finfo->name]]=$row;
		}
		mysqli_free_result($res);
		return $rows;
	}

	public function &getCol($query, $params=false, $field=0) 
	{
		if (!($res = $this->query($query, $params))) return false;
		$rows = array();
		while($row=mysqli_fetch_assoc($res)) $rows[]=$row[$field];
		mysqli_free_result($res);
		return $rows;
	}	

	public function &getAll($query, $params=false) 
	{
		if (!($res = $this->query($query, $params))) return false;
		$rows = array();
		while($row=mysqli_fetch_assoc($res)) $rows[]=$row;
		mysqli_free_result($res);
		return $rows;
	}	
	
	public function getTables($database=false) 
	{
		if (!($res = mysqli_list_tables($database? $database : $this->dbname)))return false;
		$tables = array();
		while ($row = mysqli_fetch_row($res)) $tables[] = $row[0];
		return $tables;
	}

	public function getFields($query, $group=false) 
	{
		$res = $this->query("$query LIMIT 1");
		$fields = array();
		$n_fields = mysqli_num_fields($res);
		for ($i=0; $i < $n_fields; $i++) {
			// $name = mysqli_field_name($res, $i);
			$finfo = $res->fetch_field_direct($i);	
			$field = array(
				'table' => $finfo->table,
			    'type'  => $finfo->type,
			    'name'  => $finfo->name,
			    'len'   => $finfo->max_length,
			    'flags' => $finfo->flags,
			    'subtype' => $finfo->type
			);
			$field['subtype'] = $this->getFieldSubtype($field);
			if ($group) {
				if (!isset($fields[$finfo->table])) $fields[$finfo->table] = array();
				$fields[$finfo->table][$finfo->name] = $field;
			} else {
				$fields[$finfo->name] = $field;
			}
		}
		mysqli_free_result($res);
		return $fields;
	}

	public function describe($table) 
	{
		$fields = $this->getAssoc('DESCRIBE '.$table);
		foreach($fields as $f => $v) {
			$fields[$f]['name'] = $f;
			$fields[$f]['flags'] = array();
			if (preg_match('/^(\w+)\(?(\d+)?\)?\s?(.+)?/', $v['Type'], $match)) {
				$fields[$f]['type'] = $match[1];
				if (isset($match[2])) $fields[$f]['len'] = $match[2];
				if (isset($match[3])) $fields[$f]['flags'] = explode(' ', $match[3]);
				$fields[$f]['subtype'] = $this->getFieldSubtype($fields[$f]);
			} else {
				$fields[$f]['type'] = $fields[$f]['Type'];
				$fields[$f]['len'] = 0;
			}
			$fields[$f]['flags']['unique'] = !($v['Key']=='UNI');
			$fields[$f]['flags']['primary'] = !($v['Key']=='PRI');
			$fields[$f]['flags']['not_null'] = !($v['Null']=='YES');
		}
		return $fields;
	}

	private function getFieldSubtype($field) 
	{
		if (!isset($field['len'])) return $field['type'];
		if ($field['len']==1 && substr($field['type'],-3)=='int') return 'bool';
		return $field['type'];
	}
	
	public function lock($table, $type='write') 
	{
		switch($type) {
			case 'read': $type = 'READ'; break;
			case 'write': $type = 'WRITE'; break;
		}
		$this->query('LOCK TABLES '.$table.' '.$type);
	}

	public function unlock() 
	{
		$this->query('UNLOCK TABLES');
	}
	
	public function begin() 
	{
		$this->query('begin');
	}

	public function commit() 
	{
		$this->query('commit');
	}

	public function rollback() 
	{
		$this->query('rollback');
	}

	public function insert($table, $data) 
	{
		if (!is_array($data)) return false;
		$_data = $this->escapeFields($table, $data);
		if (!count($_data)) return false;
		$fields = array_keys($_data);
		$values = array_values($_data); 
		
		$values = array();
		foreach($_data as $k=>$v) {
			if (is_null($v)) {
				$values[] = 'NULL';
			} elseif ($v === 'CURRENT_TIMESTAMP') {
				$values[] = 'CURRENT_TIMESTAMP';
			} else $values[] = "'".$v."'";
		}
		$query = "INSERT IGNORE INTO $table (`".join('`,`', $fields)."`) VALUES (".join(',', $values).")";
		return $this->query($query);
	}

	public function update($table, $data, $where) 
	{
		$_data = $this->escapeFields($table, $data);
		// print_r($_data); exit;
		if (!count($_data)) return false;
		$changes = array();
		foreach($_data as $k=>$v) {
//				$v = str_replace("'", 'ʹ', $v);
			if (is_null($v)) {
				$changes[] = "`$k`=NULL";
			} elseif ($v === 'CURRENT_TIMESTAMP') {
				$changes[] = "`$k`=$v";
			} else {
				$changes[] = "`$k`='$v'";
			}
		}
		$query = "UPDATE $table SET ".join(',', $changes)." WHERE $where";
		return $this->query($query);
	}

	public function delete($table, $where) 
	{
		return $this->query("DELETE FROM $table WHERE $where");
	}

	public function escapeFields($table, $data) 
	{
		if (isset($this->cache_fields[$table])) {
			$fields = $this->cache_fields[$table];
		} else {
			$fields = $this->getFields("SELECT * FROM $table");
			$this->cache_fields[$table] = $fields;
		}
		$_data = array();
		$keys = array_keys($data);
		foreach($keys as $k) {
			if (!isset($fields[$k]) || is_null($data[$k])) continue;
			switch($fields[$k]['type']) {
				case 'tinyint':
				case 'bigint':
				case 'mediumint':
				case 'smallint':
				case 'int':
					if ($fields[$k]['len']==1) $_data[$k] = $data[$k]? 1:0;
					else $_data[$k]=intval($data[$k]);
					break;
				case 'float':
					$_data[$k]=floatval($data[$k]);
					break;
				case 'string':
				default:
					/*
						escaping strings for database
						
						1. escape string with: mysql_real_escape_string($string)
						2. unescape string with: stripslashes($string)
					*/
					$_data[$k] = $this->link->real_escape_string($data[$k]);
			}
		}
		return $_data;
	}
	
	private function error($msg, $query='') 
	{
		$msg = "$msg<br>\n".$this->link->error;
		if (DEBUG_MODE) {
			$msg.="<br>\n$query";
			$msg.="<br>\n".$this->link->info;
		}
		throw new \Exception($msg);
		// if ($this->die_on_errors) die($msg); 
		// else trigger_error($msg);
	}





	/* get PRIMARY KEY column */
	function getPKeyColumn($table)
	{
		$sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS
				WHERE `TABLE_SCHEMA` = '".DB_NAME."'
				AND (TABLE_NAME = '$table')
				AND (COLUMN_KEY = 'PRI')";
		return $this->getOne($sql);
	}
}
