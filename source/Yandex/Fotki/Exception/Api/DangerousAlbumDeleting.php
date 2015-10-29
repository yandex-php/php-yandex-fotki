<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Exception\Api;


use Exception;
use Yandex\Fotki\Api\Album;
use Yandex\Fotki\Api\Photo;

/**
 * Class DangerousAlbumDeleting
 * @package Yandex\Fotki\Exception\Api
 */
class DangerousAlbumDeleting extends \Yandex\Fotki\Exception\Api {

	/**
	 * @var \Yandex\Fotki\Api\Album[]
	 */
	protected $_albums;
	/**
	 * @var \Yandex\Fotki\Api\Photo[]
	 */
	protected $_photos;

	/**
	 * DangerousAlbumDeleting constructor.
	 *
	 * @param Album[]         $albums
	 * @param Photo[]         $photos
	 * @param string          $message
	 * @param int             $code
	 * @param \Exception|null $previous
	 */
	public function __construct( array $albums, array $photos, $message = "", $code = 0, \Exception $previous = null ) {
		$this->_albums = $albums;
		$this->_photos = $photos;
		$message       = $message ?: $this->getDefaultMessage();
		parent::__construct( $message, $code, $previous );
	}

	/**
	 * @return string
	 */
	public function getDefaultMessage() {
		$totalAlbums = count( $this->_albums );
		$totalPhotos = count( $this->_photos );

		return "Dangerous album deleting - it is parent of {$totalAlbums} albums and {$totalPhotos} photos.";
	}

	/**
	 * @return \Yandex\Fotki\Api\Album[]
	 */
	public function getAlbums() {
		return $this->_albums;
	}

	/**
	 * @return \Yandex\Fotki\Api\Photo[]
	 */
	public function getPhotos() {
		return $this->_photos;
	}

}