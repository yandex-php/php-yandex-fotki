<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Tests\Unit\Api;


use Yandex\Fotki\Tests\Unit\BaseTestCase;

class AlbumTest extends BaseTestCase {

	public function testAlbumGetParent() {
		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumGetParent Parent' ) )->load();
		$child  = $this->api->createAlbum( array( 'title' => 'testAlbumGetParent Child' ), $parent->getId() )->load();

		$this->assertEquals( null, $parent->getParent() );
		$this->assertEquals( null, $parent->getParentId() );
		$this->assertEquals( null, $parent->getApiUrlParent() );
		$this->assertEquals( $parent->getId(), $child->getParent()->getId() );
		$this->assertEquals( $parent->getId(), $child->getParentId() );
		$this->assertEquals( parse_url( $parent->getApiUrl(), PHP_URL_PATH ), parse_url( $child->getApiUrlParent(), PHP_URL_PATH ) );
	}

	public function testAlbumSetParent() {
		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumSetParent Parent' ) )->load();
		$child  = $this->api->createAlbum( array( 'title' => 'testAlbumSetParent Child' ) )->load();

		$child->setParent( $parent );
		$this->assertEquals( 'testAlbumSetParent Parent', $child->getParent()->getTitle() );
		$this->assertEquals( $parent->getId(), $child->getParentId() );
		$this->assertEquals( parse_url( $parent->getApiUrl(), PHP_URL_PATH ), parse_url( $child->getParent()->getApiUrl(), PHP_URL_PATH ) );
		$this->assertEquals( parse_url( $parent->getApiUrl(), PHP_URL_PATH ), parse_url( $child->getApiUrlParent(), PHP_URL_PATH ) );

		$child->setParent( null );
		$this->assertEquals( null, $child->getParent() );
		$this->assertEquals( null, $child->getParentId() );
		$this->assertEquals( null, $child->getApiUrlParent() );

		$child->setParent( $parent->getId() );
		$this->assertEquals( 'testAlbumSetParent Parent', $child->getParent()->getTitle() );
		$this->assertEquals( $parent->getId(), $child->getParentId() );
		$this->assertEquals( parse_url( $parent->getApiUrl(), PHP_URL_PATH ), parse_url( $child->getParent()->getApiUrl(), PHP_URL_PATH ) );
		$this->assertEquals( parse_url( $parent->getApiUrl(), PHP_URL_PATH ), parse_url( $child->getApiUrlParent(), PHP_URL_PATH ) );
	}

	/**
	 * Так как возможность получить дочерние альбомы есть только на
	 * верхнем уровне Api, то метод \Yandex\Fotki\Api\Album::getChildren
	 * возвращает текущее состояние объекта, а не загружает его
	 * детей с сервера Api.
	 *
	 * Может в будущем это будет исправлено. Но пока тестировать тут нечего
	 *
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 */
	public function testAlbumGetChildren() {
//		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumGetChildren Parent' ) )->load();
//		$child1 = $this->api->createAlbum( array( 'title' => 'testAlbumGetChildren Child1' ), $parent->getId() )->load();
//		$child2 = $this->api->createAlbum( array( 'title' => 'testAlbumGetChildren Child2' ), $parent->getId() )->load();
//
//		$children = $parent->getChildren();
//		$this->assertEquals('testAlbumGetChildren Child1', $children[$child1->getId()]->getTitle());
//		$this->assertEquals('testAlbumGetChildren Child2', $children[$child2->getId()]->getTitle());
	}

	public function testAlbumSetChildren() {
		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumSetChildren Parent' ) )->load();
		$child1 = $this->api->createAlbum( array( 'title' => 'testAlbumSetChildren Child1' ), $parent->getId() )->load();
		$child2 = $this->api->createAlbum( array( 'title' => 'testAlbumSetChildren Child2' ), $parent->getId() )->load();

		$parent->setChildren( array( $child1, $child2 ) );
		$children = $parent->getChildren();
		$this->assertEquals( 2, count( $children ) );
		$this->assertEquals( 'testAlbumSetChildren Child1', $children[ $child1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumSetChildren Child2', $children[ $child2->getId() ]->getTitle() );

		$parent->setChildren( array( intval( $child1->getId() ), intval( $child2->getId() ) ) );
		$children = $parent->getChildren();
		$this->assertEquals( 2, count( $children ) );
		$this->assertEquals( 'testAlbumSetChildren Child1', $children[ $child1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumSetChildren Child2', $children[ $child2->getId() ]->getTitle() );

		$parent->setChildren( array( strval( $child1->getId() ), strval( $child2->getId() ) ) );
		$children = $parent->getChildren();
		$this->assertEquals( 2, count( $children ) );
		$this->assertEquals( 'testAlbumSetChildren Child1', $children[ $child1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumSetChildren Child2', $children[ $child2->getId() ]->getTitle() );
	}

	public function testAlbumAddChild() {
		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumAddChild Parent' ) )->load();
		$child1 = $this->api->createAlbum( array( 'title' => 'testAlbumAddChild Child1' ), $parent->getId() )->load();
		$child2 = $this->api->createAlbum( array( 'title' => 'testAlbumAddChild Child2' ), $parent->getId() )->load();
		$child3 = $this->api->createAlbum( array( 'title' => 'testAlbumAddChild Child3' ), $parent->getId() )->load();

		$this->assertEquals( 0, count( $parent->getChildren() ) );

		$parent->addChild( $child1 );
		$children = $parent->getChildren();
		$this->assertEquals( 1, count( $children ) );
		$this->assertEquals( 'testAlbumAddChild Child1', $children[ $child1->getId() ]->getTitle() );

		$parent->addChild( intval( $child2->getId() ) );
		$children = $parent->getChildren();
		$this->assertEquals( 2, count( $children ) );
		$this->assertEquals( 'testAlbumAddChild Child1', $children[ $child1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumAddChild Child2', $children[ $child2->getId() ]->getTitle() );

		$parent->addChild( strval( $child3->getId() ) );
		$children = $parent->getChildren();
		$this->assertEquals( 3, count( $children ) );
		$this->assertEquals( 'testAlbumAddChild Child1', $children[ $child1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumAddChild Child2', $children[ $child2->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumAddChild Child3', $children[ $child3->getId() ]->getTitle() );

		$parent->addChild( null );
		$children = $parent->getChildren();
		$this->assertEquals( 3, count( $children ) );
		$this->assertEquals( 'testAlbumAddChild Child1', $children[ $child1->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumAddChild Child2', $children[ $child2->getId() ]->getTitle() );
		$this->assertEquals( 'testAlbumAddChild Child3', $children[ $child3->getId() ]->getTitle() );
	}

	public function testAlbumRemoveChild() {
		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveChild Parent' ) )->load();
		$child1 = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveChild Child1' ), $parent->getId() )->load();
		$child2 = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveChild Child2' ), $parent->getId() )->load();
		$child3 = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveChild Child3' ), $parent->getId() )->load();

		$parent->setChildren( array(
			$child1,
			$child2,
			$child3,
		) );
		$children = $parent->getChildren();
		$this->assertEquals( 3, count( $parent->getChildren() ) );
		$this->assertEquals( true, isset( $children[ $child1->getId() ] ) );
		$this->assertEquals( true, isset( $children[ $child2->getId() ] ) );
		$this->assertEquals( true, isset( $children[ $child3->getId() ] ) );

		$parent->removeChild( null );
		$children = $parent->getChildren();
		$this->assertEquals( 3, count( $parent->getChildren() ) );
		$this->assertEquals( true, isset( $children[ $child1->getId() ] ) );
		$this->assertEquals( true, isset( $children[ $child2->getId() ] ) );
		$this->assertEquals( true, isset( $children[ $child3->getId() ] ) );

		$parent->removeChild( $child1 );
		$children = $parent->getChildren();
		$this->assertEquals( 2, count( $children ) );
		$this->assertEquals( false, isset( $children[ $child1->getId() ] ) );
		$this->assertEquals( true, isset( $children[ $child2->getId() ] ) );
		$this->assertEquals( true, isset( $children[ $child3->getId() ] ) );

		$parent->removeChild( intval( $child2->getId() ) );
		$children = $parent->getChildren();
		$this->assertEquals( 1, count( $children ) );
		$this->assertEquals( false, isset( $children[ $child1->getId() ] ) );
		$this->assertEquals( false, isset( $children[ $child2->getId() ] ) );
		$this->assertEquals( true, isset( $children[ $child3->getId() ] ) );

		$parent->removeChild( strval( $child3->getId() ) );
		$children = $parent->getChildren();
		$this->assertEquals( 0, count( $children ) );
		$this->assertEquals( false, isset( $children[ $child1->getId() ] ) );
		$this->assertEquals( false, isset( $children[ $child2->getId() ] ) );
		$this->assertEquals( false, isset( $children[ $child3->getId() ] ) );
	}

	public function testAlbumRemoveAllChildren() {
		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveAllChildren Parent' ) )->load();
		$child1 = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveAllChildren Child1' ), $parent->getId() )->load();
		$child2 = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveAllChildren Child2' ), $parent->getId() )->load();
		$child3 = $this->api->createAlbum( array( 'title' => 'testAlbumRemoveAllChildren Child3' ), $parent->getId() )->load();

		$parent->setChildren( array(
			$child1,
			$child2,
			$child3,
		) );

		$this->assertEquals( 3, count( $parent->getChildren() ) );

		$parent->removeAllChildren();

		$this->assertEquals( 0, count( $parent->getChildren() ) );
	}

	public function testAlbumHasChildrenWithId() {
		$parent = $this->api->createAlbum( array( 'title' => 'testAlbumHasChildrenWithId Parent' ) )->load();
		$child1 = $this->api->createAlbum( array( 'title' => 'testAlbumHasChildrenWithId Parent.Child1' ), $parent->getId() )->load();
		$child2 = $this->api->createAlbum( array( 'title' => 'testAlbumHasChildrenWithId Parent.Child2' ), $parent->getId() )->load();
		$child3 = $this->api->createAlbum( array( 'title' => 'testAlbumHasChildrenWithId Parent.Child2.Child3' ), $child2->getId() )->load();

		$parent->setChildren( $this->api->getAlbumsTree( $parent ) );

		$this->assertEquals( false, $parent->contains( null ) );
		$this->assertEquals( false, $parent->contains( $parent ) );
		$this->assertEquals( true, $parent->contains( $child1 ) );
		$this->assertEquals( true, $parent->contains( intval( $child2->getId() ) ) );
		$this->assertEquals( true, $parent->contains( strval( $child3->getId() ) ) );

		$children       = $parent->getChildren();
		$child2Children = $children[ $child2->getId() ]->getChildren();

		$this->assertEquals( false, $children[ $child1->getId() ]->contains( $parent ) );
		$this->assertEquals( false, $children[ $child2->getId() ]->contains( $child1 ) );
		$this->assertEquals( false, $child2Children[ $child3->getId() ]->contains( $child2 ) );

	}
}
