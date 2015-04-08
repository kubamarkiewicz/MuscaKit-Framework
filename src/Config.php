<?php
namespace MuscaKit;
	
/**
 *	Get & set dynamic configuration stored in DB
 */

class Config
{

	/* DB connection */
	protected $db;

	public function __construct($db=null)
	{
		$this->db = $db;
	}

	public function get($section=false, $name=false)
	{
		$sql = "SELECT value FROM ".PRE."musca_config WHERE section='$section' AND name='$name'";
		$value = $this->db->getOne($sql);
	}
	
	public function set($section=false, $name=false, $value)
	{
		$sql = "UPDATE ".PRE."musca_config SET value='".mysql_real_escape_string($value)."' 
				WHERE section='$section' AND name='$name'";
		return $this->db->query($sql);
	}

}