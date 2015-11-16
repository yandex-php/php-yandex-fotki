<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Tests\Unit\Helpers;


use Yandex\Fotki\Api\Photo;

class PhotoHelper {

	public static $filterTitleStatic = 'filter-me';

	public $filterTitle = 'filter-me';

	/**
	 * @param \Yandex\Fotki\Api\Photo $photo
	 *
	 * @return bool
	 */
	public static function filterStatic( Photo $photo ) {
		return $photo->getTitle() == self::$filterTitleStatic;
	}

	/**
	 * @param \Yandex\Fotki\Api\Photo $photo
	 *
	 * @return bool
	 */
	public function filter( Photo $photo ) {
		return $photo->getTitle() == $this->filterTitle;
	}


}