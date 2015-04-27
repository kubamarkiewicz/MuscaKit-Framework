<?php

	/**
	 * params:
	 * @param string $section
	 * @param string $label 		- label that will be displayed in translation panel
	 * @param bool $escape = true 	- escape special characters
	 * @param bool $html = false 	- use TinyMCE editor
	 * @param bool $file 			- file upload (URL)
	 * @param bool $image 			- image upload (URL)
	 * @param int $width 			- scale uploaded image to width
	 * @param int $height 			- scale uploaded image to height
	 * @param bool $crop 			- crop uploaded image
	 * @param bool $admin 			- is it admin panel traslation
	 *
	 */

	function smarty_block_t($params, $tag, &$smarty)
	{
		if (!$smarty->i18n) return $tag;

		$return = $smarty->i18n->t($tag, 
									$params['section'], 
									$params['label'], 
									(bool)$params['html'], 
									(bool)$params['file'], 
									(bool)$params['image'], 
									(int)$params['width'], 
									(int)$params['height'], 
									(bool)$params['crop'], 
									isset($params['admin']) ? (bool)$params['admin'] : null
								);
		if ($params['html'] || (isset($params['escape']) && !$params['escape'])) return $return;
		else return htmlspecialchars($return);
	}

