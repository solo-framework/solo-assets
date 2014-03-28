<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Web\Assets;

use Solo\Core\IApplicationComponent;

class SoloAdapter extends Assets implements IApplicationComponent
{
	/**
	 * Инициализация компонента
	 *
	 * @see IApplicationComponent::initComponent()
	 *
	 * @return Assets
	 **/
	public function initComponent()
	{
		return true;
	}
}

