<?php
namespace MuscaKit;

	
class Musca_Utils
{


	/**
	 * Get list of files from directory
	 *
	 * @param string $localPath
	 * @return array
	 */
	static function filesFromDir($localPath) 
	{
		$list = array();
		$diterator = new DirectoryIterator($localPath);
		foreach ($diterator as $item) 
		{
			if (($item->getFileName()<>'.') 
					&& ($item->getFileName()<>'..')
					&& (!$item->isDir()))
				$list[] = $item->getFileName();
		}
		//todo: sort
		return $list;
	}



	/**
	 * force download
	 *
	 * @param unknown_type $file
	 */
	static function downloadFile($file, $newName = '') {

	   //First, see if the file exists
	   if (!is_file($file)) { return false; }
	
	   //Gather relevent info about file
	   $len = filesize($file);
	   if ($newName) $filename = $newName;
	   else $filename = basename($file);
	   $file_extension = strtolower(substr(strrchr($filename,"."),1));
	
	   //This will set the Content-Type to the appropriate setting for the file
	   switch( $file_extension ) {
	
	   	case "pdf": $ctype="application/pdf"; break;
	     case "exe": $ctype="application/octet-stream"; break;
	     case "zip": $ctype="application/zip"; break;
	     case "doc": $ctype="application/msword"; break;
	     case "xls": $ctype="application/vnd.ms-excel"; break;
	     case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
	     case "gif": $ctype="image/gif"; break;
	     case "png": $ctype="image/png"; break;
	     case "jpeg":
	     case "jpg": $ctype="image/jpg"; break;
	     case "mp3": $ctype="audio/mpeg"; break;
	     case "wav": $ctype="audio/x-wav"; break;
	     case "mpeg":
	     case "mpg":
	     case "mpe": $ctype="video/mpeg"; break;
	     case "mov": $ctype="video/quicktime"; break;
	     case "avi": $ctype="video/x-msvideo"; break;
	     case "txt": $ctype="text/text; charset=utf-8"; break;
	
	     //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
	     case "php":
	     case "tpl":
	     case "htm":
	     case "html":  die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;
	
	     default: $ctype="application/force-download";
	   }
	
	   //Begin writing headers
	   header("Pragma: public");
	   header("Expires: 0");
	   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	   header("Cache-Control: public"); 
	   header("Content-Description: File Transfer");
	   
	   //Use the switch-generated Content-Type
	   header("Content-Type: $ctype");
	
	   //Force the download
	   $header="Content-Disposition: attachment; filename=".$filename.";";
	   header($header );
	   header("Content-Transfer-Encoding: binary");
	   header("Content-Length: ".$len);
	   @readfile($file);
	   exit;
	}	



	/**
	 * Translates english names to other language
	 * eg. date("l, j F")
	 */
	static function dateI18n($string, $lang)
	{
		$search = array('January','February','March','April','May','June','July','August','September','October','November','December', 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
		$replace['es'] = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre', 'lunes','martes','miércoles','jueves','viernes','sábado','domingo');

		if (isset($replace[$lang])) return str_ireplace($search, $replace[$lang], $string);
		else return $string;
	}




	static function slug($string, $separator = '-')
	{
		$string = strtolower($string);
		$string = self::remove_accents($string);

		$string_tmp = '';
		for ($i=0; $i<strlen($string); $i++)
		{
			$char = ord(substr($string,$i,1));
			if (($char>=48 && $char<=57) || ($char>=97 && $char<=122)) $string_tmp.=chr($char);
			else $string_tmp .= $separator;
		}

		$string_tmp = str_replace($separator.$separator, $separator, $string_tmp);
		$string_tmp = str_replace($separator.$separator, $separator, $string_tmp);
		$string_tmp = trim($string_tmp, $separator);

		return $string_tmp;
	}



	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * If there are no accent characters, then the string given is just returned.
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */
	static function remove_accents($string) {
	    if ( !preg_match('/[\x80-\xff]/', $string) )
	        return $string;

        $chars = array(
        // Decompositions for Latin-1 Supplement
        chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
        chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
        chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
        chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
        chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
        chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
        chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
        chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
        chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
        chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
        chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
        chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
        chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
        chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
        chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
        chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
        chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
        chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
        chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
        chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
        chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
        chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
        chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
        chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
        chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
        chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
        chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
        chr(195).chr(191) => 'y',
        // Decompositions for Latin Extended-A
        chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
        chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
        chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
        chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
        chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
        chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
        chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
        chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
        chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
        chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
        chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
        chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
        chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
        chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
        chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
        chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
        chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
        chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
        chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
        chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
        chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
        chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
        chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
        chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
        chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
        chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
        chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
        chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
        chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
        chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
        chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
        chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
        chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
        chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
        chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
        chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
        chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
        chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
        chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
        chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
        chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
        chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
        chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
        chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
        chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
        chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
        chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
        chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
        chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
        chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
        chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
        chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
        chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
        chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
        chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
        chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
        chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
        chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
        chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
        chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
        chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
        chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
        chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
        // Euro Sign
        chr(226).chr(130).chr(172) => 'E',
        // GBP (Pound) Sign
        chr(194).chr(163) => '');

        $string = strtr($string, $chars);
	    

	    return $string;
	}



	/**
	 * modified 2013.09.29
	 * resize image keeping it's proportions and (optionaly) crop it.
	 * requires PHP 5.2.0
	 * (C) Kuba Markiewicz 2011
	 * 
	 * @param string $in_path - path to source file
	 * @param integer $width - output image max width
	 * @param integer $height - output image max height. if empty equals to width.
	 * @param bool $crop - specifies if image will be cropped. default is false.
	 * @param string $out_path - output directory or file. if empty, image will be saved to source file. 
	 * @param integer $quality - output image quality (if JPG). default is 85.
	 * 
	 * @return string - path to output file
	 */
	static function imageResize($in_path, $width=null, $height=null, $out_path=null, $crop=false, $quality=85, $prefix=null, $suffix=null) 
	{
		if (!$width) $width = 10000;
		if (!$height) $height = 10000;

		$imginfo = getimagesize($in_path);
	
		// loading source image
		switch ($imginfo[2]) {
			case 2://jpg
				$img = imagecreatefromjpeg($in_path);
				break;
			case 1://gif 
				$img = imagecreatefromgif($in_path);
				break;
			case 3://png
				$img = imagecreatefrompng($in_path);
				break;
			default:
				return false;
		}
			
		// output image dimesnions
		$im_h = $imginfo[1];
		$im_w = $imginfo[0];
		
		if ($crop)
		{
			// calculating area to crop
			if (($im_w > $width) || ($im_h > $height)) {
				$resize_needed = true;
				
				// cropping horizontally
				$h = $im_h;
				$w = round(($im_h / $height) * $width);
				$x = floor (($im_w - $w) / 2);
				$y = 0;
				
				if ($w > $im_w) {
					// cropping vertically
					$w = $im_w;
					$h = round(($im_w / $width) * $height);
					$x = 0;
					$y = floor (($im_h - $h) / 2);
				}
			}
			else $resize_needed = false;
						
			// scaling
			if ($resize_needed) {
				$img_out = imagecreatetruecolor($width, $height);
				if($imginfo[2] == 3) // if png, preserve transparency
				{
				    imagealphablending($img_out, false);
				    imagesavealpha($img_out, true);
				    $trans_layer_overlay = imagecolorallocatealpha($img_out, 220, 220, 220, 127);
					imagefill($img_out, 0, 0, $trans_layer_overlay);
				}
				if (!imagecopyresampled($img_out, $img, 0, 0, $x, $y, $width, $height, $w, $h)) return false;
			} else {
				$img_out = $img;
			}
		}
		else
		{
			// calculatin output dimensions
			$resize_needed = true;
			if (!empty($height) && empty($width) && $im_h > $height) { // scaling vertically
				$h = $height;
				$w = ($im_w * $height)/$im_h;		
		
			} else if (empty($height) && !empty($width) && $im_w > $width) { // scaling horizontally
				$w = $width;
				$h = ($im_h * $width)/$im_w;
		
			} else if (!empty($height) && !empty($width) && ($im_h > $height || $im_w > $width)){
				if ($im_h/$height > $im_w/$width) { // scaling vertically
					$h = $height;
					$w = ($im_w * $height)/$im_h;
					
				} else { // scaling horizontally
					$w = $width;
					$h = ($im_h * $width)/$im_w;
					
				}
			} else {
				$resize_needed = false;
			}
			
			// scaling
			if ($resize_needed) {
				$img_out = imagecreatetruecolor($w, $h);
				if($imginfo[2] == 3) // if png, preserve transparency
				{
				    imagealphablending($img_out, false);
				    imagesavealpha($img_out, true);
				    $trans_layer_overlay = imagecolorallocatealpha($img_out, 220, 220, 220, 127);
					imagefill($img_out, 0, 0, $trans_layer_overlay);
				}
				if (!imagecopyresampled($img_out, $img, 0, 0, 0, 0, $w, $h, $im_w, $im_h)) return false;
			} else {
				$img_out = $img;
			}
		}
		
		
		// finding filemane
		if (!$out_path) $out_file = $in_path;
		elseif (is_file($out_path)) $out_file = $out_path; // is file and exists
		elseif (is_dir($out_path)) // is directory
		{
			$out_file = rtrim($out_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($in_path);
		}
		else // file doesn't exist
		{
			$path_parts = pathinfo($out_path);
			$valid_extensions = array('jpg', 'gif', 'png');
			if (is_dir($path_parts['dirname']) && $path_parts['filename'] && in_array(strtolower($path_parts['extension']), $valid_extensions))
				$out_file = $out_path;
		}
		
		if ($prefix)
		{
			$path_parts = pathinfo($out_file);
			$out_file = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $prefix . $path_parts['basename'];
		}
		if ($suffix)
		{
			$path_parts = pathinfo($out_file);
			$out_file = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $path_parts['filename'] . $suffix . '.' . $path_parts['extension'];
		}

		if (!is_dir(dirname($out_file)))
		{
			throw new Exception("Directory: ".dirname($out_file)." does not exist.");
		}
		
		// saving image
		switch ($imginfo[2]) {
			case 2: // jpg
				imagejpeg($img_out, $out_file, $quality);
				break;
			case 1: // gif
				imagegif($img_out, $out_file);
				break;
			case 3: // png
				imagepng($img_out, $out_file);
				break;
			default:
				return false;
		}	
	
		return $out_file;
	}

}