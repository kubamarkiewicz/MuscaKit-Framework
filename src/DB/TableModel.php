<?php
namespace MuscaKit\DB;

class TableModel
{
	public $table;			// table name without prefix
	public $PKey = 'id'; 	// primary key

	protected $db;
	protected $lang;


	
	function __construct($db, $lang = null)
	{
		if (!$this->table) {
			exit('No table name defined in model');
		}
		$this->table = PRE.$this->table;
		$this->db = $db;
		$this->lang = $lang;
	}
	
	
	function get($id)
	{
		$sql = "SELECT * FROM $this->table WHERE ".$this->PKey."='".$this->db->link->real_escape_string($id)."'";
		$result = $this->db->getRow($sql);
		if ($this->lang) $result = $this->getI18n($result);
		return $result;
	}
	
	
	function getAll()
	{
		$sql = "SELECT * FROM $this->table";
		$result = $this->db->getAll($sql);
		if ($this->lang) foreach ($result as $key => $elem) $result[$key] = $this->getI18n($elem);
		return $result;
	}
	
	
	function getAssoc()
	{
		$sql = "SELECT * FROM $this->table";
		$result = $this->db->getAssoc($sql);
		if ($this->lang) foreach ($result as $key => $elem) $result[$key] = $this->getI18n($elem);
		return $result;
	}

	
	function insert($data)
	{
		return $this->db->insert($this->table, $data);
	}
	

	function update($data, $id)
	{
		return $this->db->update($this->table, $data, $this->PKey."='".$this->db->link->real_escape_string($id)."'");
	}


	function last($table = null) 
	{
		return $this->db->getOne("SELECT MAX(".$this->PKey.") FROM ".($table ? $table : $this->table)); 
	}




	protected function getI18n($data, $table = null)
	{
		if (!$data) return;
		if (!$table) $table = $this->table;
		$id = $data[$this->PKey];
		$sql = "SELECT field, value FROM ".$table."_i18n WHERE lang='".$this->lang."' AND id='".$this->db->link->real_escape_string($id)."'";
		$result = $this->db->getAssoc($sql);
	    $i18n =  array();
		foreach ($result as $field => $value) $i18n[$field] = $value;
		return array_merge($data, $i18n);
	}	



	protected function getFiles($result)
	{
		if (!$result) return;
		$id = $result[$this->PKey];
		$sql = "SELECT * FROM {$this->table}_files WHERE ".substr($this->table, strlen(PRE))."_id='".$this->db->link->real_escape_string($id)."' ORDER BY pos";
		$result['files'] = $this->db->getAll($sql);
		return $result;
	}


	protected function getGallery($result)
	{
		if (!$result) return;
		$id = $result[$this->PKey];
		$sql = "SELECT * FROM {$this->table}_gallery WHERE ".substr($this->table, strlen(PRE))."_id='".$this->db->link->real_escape_string($id)."' ORDER BY pos";
		$result['gallery'] = $this->db->getAll($sql);
		return $result;
	}

}
