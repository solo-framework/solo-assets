<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Web\Assets\Smarty;

use App\Application;
use Solo\Core\UI\Smarty\Plugins\Base;

class Assets extends Base
{

	/**
	 * Тип плагина (function, block, modifier, etc.)
	 *
	 * @return string
	 */
	function getType()
	{
		return "function";
	}

	/**
	 * Название плагина
	 *
	 * @return string
	 */
	function getTag()
	{
		return "assets";
	}

	/**
	 * @param array $params Параметры
	 *
	 * @return string
	 */
	public function execute($params)
	{
		$assets = Application::getInstance()->getComponent("solo_assets");
		$files = array();

		if (isset($params["outdir"]))
			$assets->outdir = $params["outdir"];

		if (isset($params["files"]))
			$files = $params["files"];

		if (isset($params["ttl"]))
			$assets->ttl = $params["ttl"];

		return $assets->bind($files);

//		$assetDir = isset($params["outdir"]) ? $params["outdir"] : "/assets";
//		$files = explode(",", trim($params["files"], ","));
//		$ttl = isset($params["ttl"]) ? intval($params["ttl"]) : 300;
	}
}

