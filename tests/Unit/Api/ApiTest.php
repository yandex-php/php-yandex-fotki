<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Tests\Unit\Api;


use Yandex\Fotki\Api;
use Yandex\Fotki\Exception\Api\Album;
use Yandex\Fotki\Exception\Api\Photo;

class ApiTest extends \PHPUnit_Framework_TestCase {

	/** @var \Yandex\Fotki\Api */
	public $api;

	public function testAlbumCreate() {
		$album = $this->api->createAlbum( array(
			'title'   => 'testCreateAlbum Title',
			'summary' => 'testCreateAlbum Summary'
		) )->load();

		$this->assertEquals( 'testCreateAlbum Title', $album->getTitle() );
		$this->assertEquals( 'testCreateAlbum Summary', $album->getSummary() );
	}

	public function testAlbumCreateWithPassword() {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$album = $this->api->createAlbum( array(
			'title'    => 'testCreateAlbumWithPassword Title',
			'password' => 'asd123'
		) )->load();

		$this->assertEquals( true, $album->isProtected() );
	}

	public function testAlbumCreateInsideAnother() {
		$parentAlbum = $this->api->createAlbum( array(
			'title' => 'testCreateAlbumInsideAnother Parent'
		) )->load();

		$childAlbum = $this->api->createAlbum( array(
			'title' => 'testCreateAlbumInsideAnother Child'
		), $parentAlbum->getId() )->load();

		$this->assertEquals( $parentAlbum->getId(), $childAlbum->getParentId() );
	}

	public function testAlbumUpdate() {
		$album = $this->api->createAlbum( array(
			'title' => 'testAlbumUpdate Title',
		) )->load();

		$this->assertEquals( 'testAlbumUpdate Title', $album->getTitle() );

		$album->setTitle( 'testAlbumUpdate Title Changed' );
		$updatedAlbum = $this->api->updateAlbum( $album )->load();

		$this->assertEquals( 'testAlbumUpdate Title Changed', $updatedAlbum->getTitle() );
	}

	public function testAlbumDelete() {
		$album = $this->api->createAlbum( array(
			'title' => 'testAlbumDelete Title',
		) )->load();

		$albumId = $album->getId();
		$this->api->deleteAlbum( $album->getId() );

		try {
			$this->api->getAlbum( $albumId )->load();
			$this->assertEquals( true, false );
		} catch ( Album $e ) {
			$this->assertEquals( 404, $e->getCode() );
		}
	}

	public function testNestedAlbumDeletePrevented() {
		$parentAlbum = $this->api->createAlbum( array(
			'title' => 'testNestedAlbumDeletePrevented Parent'
		) )->load();

		$childAlbum = $this->api->createAlbum( array(
			'title' => 'testNestedAlbumDeletePrevented Child'
		), $parentAlbum->getId() )->load();

		try {
			$this->api->deleteAlbum( $parentAlbum->getId() );
			$this->assertEquals( true, false );
		} catch ( \Yandex\Fotki\Exception\Api\DangerousAlbumDeleting $e ) {
			$albums = $e->getAlbums();
			$ids    = array_keys( $albums );

			$this->assertEquals( $ids[0], $childAlbum->getId() );
		}
	}

	public function testNestedAlbumDeleteSuccessed() {
		$api         = $this->api;
		$parentAlbum = $api->createAlbum( array(
			'title' => 'testNestedAlbumDeleteSuccessed Parent'
		) )->load();

		$childAlbum = $api->createAlbum( array(
			'title' => 'testNestedAlbumDeleteSuccessed Child'
		), $parentAlbum->getId() )->load();

		$api->deleteAlbum( $parentAlbum->getId(), $api::DELETE_ALBUM_WITH_PHOTOS_YES, $api::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_YES );

		try {
			$this->api->getAlbum( $parentAlbum->getId() )->load();
			$this->assertEquals( true, false );
		} catch ( Album $e ) {
			$this->assertEquals( 404, $e->getCode() );
		}

		try {
			$this->api->getAlbum( $childAlbum->getId() )->load();
			$this->assertEquals( true, false );
		} catch ( Album $e ) {
			$this->assertEquals( 404, $e->getCode() );
		}
	}

	public function testCreatePhoto() {
		$login = FOTKI_API_LOGIN;
		$photo = $this->api->createPhoto( array(
			'image'             => FOTKI_API_ASSETS . '/test.png',
			'title'             => 'testCreatePhoto Title',
			'summary'           => 'testCreatePhoto Summary',
			'isAdult'           => true,
			'isDisableComments' => true,
			'isHideOriginal'    => true,
			'access'            => 'friends',
			'geo'               => array( 55.12, 38.24 ),
			'tags'              => array( 'tag-1', 'tag-2' ),
		) )->load();

		$this->assertEquals( 'testCreatePhoto Title', $photo->getTitle() );
		$this->assertEquals( 'testCreatePhoto Summary', $photo->getSummary() );
//		$this->assertEquals( true, $photo->isAdult() ); // todo проверить, почему флаг не устанавливается
		$this->assertEquals( true, $photo->isDisableComments() );
		$this->assertEquals( true, $photo->isHideOriginal() );
		$this->assertEquals( 'friends', $photo->getAccess() );
		$this->assertEquals( array_map( 'intval', array( 55.12, 38.24 ) ), array_map( 'intval', $photo->getGeo() ) );
		$this->assertEquals(
			implode( ', ', array(
				"http://api-fotki.yandex.ru/api/users/{$login}/tag/tag-2/",
				"http://api-fotki.yandex.ru/api/users/{$login}/tag/tag-1/"
			) )
			, $photo->getTags()
		);
	}

	public function testCreatePhotoInAlbum() {
		$album = $this->api->createAlbum( array(
			'title' => 'testCreatePhotoInAlbum Title',
		) )->load();

		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testCreatePhotoInAlbum Title'
		), $album->getId() )->load();

		$this->assertEquals( $album->getId(), $photo->getAlbumId() );
	}

	public function testUpdatePhoto() {
		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testUpdatePhoto Title'
		) )->load();

		$photo->setTitle( 'testUpdatePhoto Title Changed' );

		$updatedPhoto = $this->api->updatePhoto( $photo )->load();

		$this->assertEquals( 'testUpdatePhoto Title Changed', $updatedPhoto->getTitle() );
	}

	public function testPhotoDelete() {
		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testPhotoDelete Title'
		) )->load();

		$this->api->deletePhoto( $photo->getId() );

		try {
			$this->api->getPhoto( $photo->getId() );
		} catch ( Photo $e ) {
			$this->assertEquals( 404, $e->getCode() );
		}
	}

	public function testDeleteAlbumWithPhotos() {
		$api   = $this->api;
		$album = $api->createAlbum( array(
			'title' => 'testDeleteAlbumWithPhotos Title',
		) )->load();

		$photo = $api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testPhotoDelete Title'
		), $album->getId() )->load();

		$api->deleteAlbum( $album->getId(), $api::DELETE_ALBUM_WITH_PHOTOS_YES );

		try {
			$api->getAlbum( $album->getId() );
		} catch ( Album $e ) {
			$this->assertEquals( 404, $e->getCode() );
		}

		try {
			$api->getPhoto( $photo->getId() );
		} catch ( Photo $e ) {
			$this->assertEquals( 404, $e->getCode() );
		}

	}


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
					throw new \RuntimeException( "", 0, $e );
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
