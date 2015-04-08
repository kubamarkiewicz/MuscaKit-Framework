<?php
namespace MuscaKit\Utils;

class DB
{
	private $db;
	
	function __construct($db)
	{
		$this->db = $db;
	}
	
	static function strtominus(&$string)
	{
		$string = preg_replace("(À|Á|Â|Ã|Ä|Å|à|á|â|ã|ä|å)","a",$string);
		$string = preg_replace("(È|É|Ê|Ë|è|é|ê|ë)","e",$string);
		$string = preg_replace("(Ì|Í|Î|Ï|ì|í|î|ï)","i",$string);
		$string = preg_replace("(Ò|Ó|Ô|Õ|Ö|Ø|ò|ó|ô|õ|ö|ø)","o",$string);
		$string = preg_replace("(Ù|Ú|Û|Ü|ù|ú|û|ü)","u",$string);
		$string = preg_replace("(Ñ|ñ)","n",$string);
		$string = preg_replace("(Ç|ç)","c",$string);
		$string = preg_replace("(ÿ)","y",$string);
		
		$string = strtolower(trim($string));
		$string = preg_replace('/[^a-z0-9-]/', '-', $string);
		$string = preg_replace('/-+/', "-", $string);
	}
	
	function slug($slug, $table, $exclude=false, $field='slug')
	{
    	self::strtominus($slug);
    	
		$i = '';
		while ($this->slug_exists($slug.$i, $table, $exclude)) { $i++; }
		$slug .= $i;
		return $slug;
	}
	
	function slug_exists($slug, $table, $exclude=false, $field='slug')
	{
		$exclude = $exclude ? ' AND '.$exclude : '';
		$r = $this->db->getOne("SELECT $field FROM $table WHERE $field='$slug' $exclude");
		if ($r) return true;
		else return false;
	}
	
	function slug_i18n($slug, $table)
	{
    	self::strtominus($slug);

		$i = '';
		while ($this->slug_i18n_exists($slug.$i, $table)) { $i++; }
		$slug .= $i;
		return $slug;
	}

	function slug_i18n_exists($slug, $table)
	{
		$r = $this->db->getOne("SELECT count(*) FROM $table WHERE field='slug' and value='$slug'");
		if ($r<=1) return false;
		else return true;
	}
	
	function clean_post($table, &$data)
	{
		$aKeys = array_keys($this->db->describe($table));
		foreach ($data as $field => $value)
		{
			if (!in_array($field, $aKeys)) unset($data[$field]);
		}
	}
	
	function last_id($table, $field)
	{
		return $this->db->getOne("SELECT MAX($field) FROM $table");
	}

	function clean_user_input($dirty)
	{
		if (get_magic_quotes_gpc()) $clean = $this->db->link->real_escape_string(stripslashes($dirty));
		else $clean = $this->db->link->real_escape_string($dirty);

		return $clean;
	}

	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
	{
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
		$theValue = $this->db->link->real_escape_string($theValue);

		switch ($theType)
		{
			case "text":
				$theValue = ($theValue != "") ? $theValue : NULL;
				break;
			case "long":
			case "int":
				$theValue = ($theValue != "") ? intval($theValue) : NULL;
				break;
			case "double":
				$theValue = ($theValue != "") ? doubleval($theValue) : NULL;
				break;
			case "date":
				$theValue = ($theValue != "") ? $theValue : NULL;
				break;
			case "defined":
				$theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
				break;
		}
		return $theValue;
	}
}
