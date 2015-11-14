<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Tests\Unit\Api;


use Yandex\Fotki\Api;
use Yandex\Fotki\Exception\Api\Album;
use Yandex\Fotki\Exception\Api\Photo;
use Yandex\Fotki\Tests\Unit\BaseTestCase;

class ApiTest extends BaseTestCase {

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

	public function testImgHrefMap() {
		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testImgHrefMap Title'
		) )->load();

		$map = $photo->getImgHrefMap();

		//@formatter:off
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_XXXS ),     $map[ $photo::SIZE_XXXS ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_XXS ),      $map[ $photo::SIZE_XXS ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_XS ),       $map[ $photo::SIZE_XS ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_S ),        $map[ $photo::SIZE_S ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_M ),        $map[ $photo::SIZE_M ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_L ),        $map[ $photo::SIZE_L ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_XL ),       $map[ $photo::SIZE_XL ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_XXL ),      $map[ $photo::SIZE_XXL ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_XXXL ),     $map[ $photo::SIZE_XXXL ] );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_ORIGINAL ), $map[ $photo::SIZE_ORIGINAL ] );
		//@formatter:on
	}

	/**
	 * Будет воссоздана следующая структура:
	 *
	 * * testAlbumsBaseTree Root
	 *   |- testAlbumsBaseTree root.category1
	 *   |  |- testAlbumsBaseTree root.category1.product1
	 *   |  |- testAlbumsBaseTree root.category1.product2
	 *   |  |- testAlbumsBaseTree root.category1.product3
	 *   |
	 *   |- testAlbumsBaseTree root.category2
	 *      |- testAlbumsBaseTree root.category2.product4
	 *      |- testAlbumsBaseTree root.category2.product5
	 *      |- testAlbumsBaseTree root.category2.product6
	 *      |
	 *      |- testAlbumsBaseTree root.category2.category3
	 *         |- testAlbumsBaseTree root.category2.category3.product7
	 *         |- testAlbumsBaseTree root.category2.category3.product8
	 *         |- testAlbumsBaseTree root.category2.category3.product9
	 *
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 */
	public function testAlbumsBaseTree() {
		$rootAlbum = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree Root' ) )->load();

		$category1 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category1' ), $rootAlbum->getId() )->load();
		$category2 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2' ), $rootAlbum->getId() )->load();
		$category3 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2.category3' ), $category2->getId() )->load();

		$product1 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category1.product1' ), $category1->getId() )->load();
		$product2 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category1.product2' ), $category1->getId() )->load();
		$product3 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category1.product3' ), $category1->getId() )->load();

		$product4 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2.product4' ), $category2->getId() )->load();
		$product5 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2.product5' ), $category2->getId() )->load();
		$product6 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2.product6' ), $category2->getId() )->load();

		$product7 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2.category3.product7' ), $category3->getId() )->load();
		$product8 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2.category3.product8' ), $category3->getId() )->load();
		$product9 = $this->api->createAlbum( array( 'title' => 'testAlbumsBaseTree root.category2.category3.product9' ), $category3->getId() )->load();


		$tree = $this->api->getAlbumsTree();

		//@formatter:off
		$rootChildren      =              $tree[ $rootAlbum->getId() ]->getChildren();
		$category1Children =      $rootChildren[ $category1->getId() ]->getChildren();
		$category2Children =      $rootChildren[ $category2->getId() ]->getChildren();
		$category3Children = $category2Children[ $category3->getId() ]->getChildren();
		//@formatter:on


		//@formatter:off
		$this->assertEquals( 'testAlbumsBaseTree Root', $tree[ $rootAlbum->getId() ]->getTitle() );

		$this->assertEquals( 'testAlbumsBaseTree root.category1',           $rootChildren[ $category1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category1.product1',       $category1Children[ $product1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category1.product2',       $category1Children[ $product2->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category1.product3',       $category1Children[ $product3->getId() ]->getTitle() );

		$this->assertEquals( 'testAlbumsBaseTree root.category2',           $rootChildren[ $category2->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category2.product4',       $category2Children[ $product4->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category2.product5',       $category2Children[ $product5->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category2.product6',       $category2Children[ $product6->getId() ]->getTitle() );

		$this->assertEquals( 'testAlbumsBaseTree root.category2.category3',           $category2Children[ $category3->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category2.category3.product7',       $category3Children[ $product7->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category2.category3.product8',       $category3Children[ $product8->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsBaseTree root.category2.category3.product9',       $category3Children[ $product9->getId() ]->getTitle() );
		//@formatter:on
	}

	/**
	 * Будет воссоздана следующая структура:
	 *
	 * * testAlbumsDirectTree Root
	 *   |- testAlbumsDirectTree root.category1
	 *      |- testAlbumsDirectTree root.category1.product1
	 *      |- testAlbumsDirectTree root.category1.product2
	 *      |- testAlbumsDirectTree root.category1.product3
	 *
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 */
	public function testAlbumsDirectTree() {
		$rootAlbum = $this->api->createAlbum( array( 'title' => 'testAlbumsDirectTree Root' ) )->load();

		$category1 = $this->api->createAlbum( array( 'title' => 'testAlbumsDirectTree root.category1' ), $rootAlbum->getId() )->load();

		$product1 = $this->api->createAlbum( array( 'title' => 'testAlbumsDirectTree root.category1.product1' ), $category1->getId() )->load();
		$product2 = $this->api->createAlbum( array( 'title' => 'testAlbumsDirectTree root.category1.product2' ), $category1->getId() )->load();
		$product3 = $this->api->createAlbum( array( 'title' => 'testAlbumsDirectTree root.category1.product3' ), $category1->getId() )->load();

		$tree = $this->api->getAlbumsTree( $rootAlbum );
		$this->assertEquals( 1, count( $tree ) );
		$this->assertEquals( 'testAlbumsDirectTree root.category1', $tree[ $category1->getId() ]->getTitle() );

		$tree = $this->api->getAlbumsTree( $category1 );
		$this->assertEquals( 3, count( $tree ) );
		$this->assertEquals( 'testAlbumsDirectTree root.category1.product1', $tree[ $product1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsDirectTree root.category1.product2', $tree[ $product2->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumsDirectTree root.category1.product3', $tree[ $product3->getId() ]->getTitle() );
	}
}
