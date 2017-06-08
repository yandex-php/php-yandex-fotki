<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Tests\Unit;


use Yandex\Fotki\Api;
use Yandex\Fotki\Exception\Api\Album;
use Yandex\Fotki\Exception\Api\Photo;

class BaseTestCase extends \PHPUnit_Framework_TestCase {

	/** @var \Yandex\Fotki\Api */
	public $api;

	/**
	 * @inheritdoc
	 */
	protected function setUp() {
		parent::setUp();

		$this->api = new Api( FOTKI_API_LOGIN );
		$this->api->oauth( FOTKI_API_OAUTH_TOKEN );
	}

	/**
	 * @inheritdoc
	 * @throws \Yandex\Fotki\Exception\Api\DangerousAlbumDeleting
	 */
	protected function tearDown() {
		if ( FOTKI_API_DELETE_STUFF_AFTER_TESTS === false ) {
			parent::tearDown();

			return;
		}

		$api = $this->api;
		/** @var \Yandex\Fotki\Api\Album[] $albums */
		$albums = $api->getAlbumsCollection()->loadAll()->getList();
		foreach ( $albums as $album ) {
			if ( stripos( $album->getTitle(), 'test' ) === false ) {
				continue;
			}

			try {
				$api->deleteAlbum( $album->getId(), $api::DELETE_ALBUM_WITH_PHOTOS_YES, $api::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_YES );
			} catch ( Album $e ) {
				if ( $e->getCode() == 404 ) {
					continue;
				} else {
					throw new \RuntimeException( "Ошибка во время удаления альбома после теста", $e->getCode(), $e );
				}
			}
		}

		/** @var \Yandex\Fotki\Api\Photo[] $photos */
		$photos = $api->getPhotosCollection()->loadAll()->getList();
		foreach ( $photos as $photo ) {
			if ( stripos( $photo->getTitle(), 'test' ) === false ) {
				continue;
			}

			try {
				$api->deletePhoto( $photo->getId() );
			} catch ( Photo $e ) {
				if ( $e->getCode() == 404 ) {
					continue;
				} else {
					throw new \RuntimeException( "", 0, $e );
				}
			}
		}

		parent::tearDown();
	}
}