<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Tests\Unit\Api;


use Yandex\Fotki\Tests\Unit\BaseTestCase;

class PhotoTest extends BaseTestCase {

	public function testPhotoSetTags() {
		$login = FOTKI_API_LOGIN;

		$reflectionCollectionClass        = new \ReflectionClass( '\Yandex\Fotki\Api\TagsCollection' );
		$reflectionCollectionDataProperty = $reflectionCollectionClass->getProperty( '_data' );
		$reflectionCollectionDataProperty->setAccessible( true );

		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testPhotoSetTags',
			'tags'  => array( 'test-photo-set-tags-1', 'test-photo-set-tags-2', 'кириллический-тег-3' ),
		) )->load();

		$tags = $photo->getTags();
		$this->assertEquals( 'test-photo-set-tags-1', $tags[0]->getTitle() );
		$this->assertEquals( 'test-photo-set-tags-2', $tags[1]->getTitle() );
		$this->assertEquals( 'кириллический-тег-3', $tags[2]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			$tags[0]->getApiUrl() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json",
			$tags[1]->getApiUrl() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "кириллический-тег-3" ) . "/?format=json",
			$tags[2]->getApiUrl() );

		/**
		 * Задание единичного тега обычной строкой
		 */
		$photo->setTags( 'тег в строковом формате' );
		$tags = $photo->getTags();
		$this->assertEquals( 'тег в строковом формате', $tags[0]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "тег в строковом формате" ) . "/?format=json",
			$tags[0]->getApiUrl() );

		/**
		 * Задание единичного тега неэкранированным Url
		 */
		$photo->setTags( "http://api-fotki.yandex.ru/api/users/{$login}/tag/кириллический-тег-3/" );
		$tags = $photo->getTags();
		$this->assertEquals( 'кириллический-тег-3', $tags[0]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "кириллический-тег-3" ) . "/?format=json",
			$tags[0]->getApiUrl() );

		/**
		 * Задание единичного тега экранированным Url
		 */
		$photo->setTags( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "кириллический-тег-3" ) . "/?format=xml&true=false" );
		$tags = $photo->getTags();
		$this->assertEquals( 'кириллический-тег-3', $tags[0]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "кириллический-тег-3" ) . "/?format=json",
			$tags[0]->getApiUrl() );

		/**
		 * Задание единичного тега обычной строкой
		 */
		$photo->setTags( "тег 1 \t \n \r \0 \x0B  ,     \t   \n \r \0   \x0B   тег 2" );
		$tags = $photo->getTags();
		$this->assertEquals( 'тег 1', $tags[0]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "тег 1" ) . "/?format=json", $tags[0]->getApiUrl() );
		$this->assertEquals( 'тег 2', $tags[1]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "тег 2" ) . "/?format=json", $tags[1]->getApiUrl() );

		$tag = $this->api->getTag( 'test-photo-set-tags-1' )->load();
		$photo->setTags( $tag );
		$tags = $photo->getTags();
		$this->assertEquals( 'test-photo-set-tags-1', $tags[0]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			$tags[0]->getApiUrl() );

		$tag1           = $this->api->getTag( 'test-photo-set-tags-1' )->load();
		$tag2           = $this->api->getTag( 'test-photo-set-tags-2' )->load();
		$tagsCollection = $this->api->getTagsCollection();
		$reflectionCollectionDataProperty->setValue( $tagsCollection, array(
			$tag1->getId() => $tag1,
			$tag2->getId() => $tag2,
		) );
		$photo->setTags( $tagsCollection );
		$tags = $photo->getTags();
		$this->assertEquals( $tag1->getTitle(), $tags[0]->getTitle() );
		$this->assertEquals( $tag1->getApiUrl(), $tags[0]->getApiUrl() );
		$this->assertEquals( $tag2->getTitle(), $tags[1]->getTitle() );
		$this->assertEquals( $tag2->getApiUrl(), $tags[1]->getApiUrl() );

		$photo->setTags( array( 'test-photo-set-tags-1', 'test-photo-set-tags-2' ) );
		$tags = $photo->getTags();
		$this->assertEquals( 'test-photo-set-tags-1', $tags[0]->getTitle() );
		$this->assertEquals( 'test-photo-set-tags-2', $tags[1]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			$tags[0]->getApiUrl() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json",
			$tags[1]->getApiUrl() );

		$photo->setTags( array( 'test-photo-set-tags-1, test-photo-set-tags-2' ) );
		$tags = $photo->getTags();
		$this->assertEquals( 'test-photo-set-tags-1', $tags[0]->getTitle() );
		$this->assertEquals( 'test-photo-set-tags-2', $tags[1]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			$tags[0]->getApiUrl() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json",
			$tags[1]->getApiUrl() );

		$photo->setTags( array(
			"http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			"http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json"
		) );
		$tags = $photo->getTags();
		$this->assertEquals( 'test-photo-set-tags-1', $tags[0]->getTitle() );
		$this->assertEquals( 'test-photo-set-tags-2', $tags[1]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			$tags[0]->getApiUrl() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json",
			$tags[1]->getApiUrl() );

		$photo->setTags( array(
			"http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			"http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json"
		) );
		$tags = $photo->getTags();
		$this->assertEquals( 'test-photo-set-tags-1', $tags[0]->getTitle() );
		$this->assertEquals( 'test-photo-set-tags-2', $tags[1]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			$tags[0]->getApiUrl() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json",
			$tags[1]->getApiUrl() );

		$tag1 = $this->api->getTag( 'test-photo-set-tags-1' )->load();
		$tag2 = $this->api->getTag( 'test-photo-set-tags-1' )->load();
		$photo->setTags( array(
			$tag1,
			$tag2
		) );
		$this->assertEquals( 'test-photo-set-tags-1', $tags[0]->getTitle() );
		$this->assertEquals( 'test-photo-set-tags-2', $tags[1]->getTitle() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-1" ) . "/?format=json",
			$tags[0]->getApiUrl() );
		$this->assertEquals( "http://api-fotki.yandex.ru/api/users/{$login}/tag/" . urlencode( "test-photo-set-tags-2" ) . "/?format=json",
			$tags[1]->getApiUrl() );


		$tag1            = $this->api->getTag( 'test-photo-set-tags-1' )->load();
		$tag2            = $this->api->getTag( 'test-photo-set-tags-2' )->load();
		$tagsCollection1 = $this->api->getTagsCollection();
		$tagsCollection2 = $this->api->getTagsCollection();
		$reflectionCollectionDataProperty->setValue( $tagsCollection1, array( $tag1->getId() => $tag1 ) );
		$reflectionCollectionDataProperty->setValue( $tagsCollection2, array( $tag2->getId() => $tag2 ) );
		$photo->setTags( array(
			$tagsCollection1,
			$tagsCollection2
		) );
		$this->assertEquals( $tag1->getTitle(), $tags[0]->getTitle() );
		$this->assertEquals( $tag1->getApiUrl(), $tags[0]->getApiUrl() );
		$this->assertEquals( $tag2->getTitle(), $tags[1]->getTitle() );
		$this->assertEquals( $tag2->getApiUrl(), $tags[1]->getApiUrl() );

	}

	public function testPhotoGetMaxAvailableImg() {
		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testPhotoGetMaxAvailableImg',
		) )->load();

		$img = $photo->getMaxAvailableImg();
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_L ), $img['href'] );
		$this->assertEquals( 350, $img['width'] );
		$this->assertEquals( 150, $img['height'] );

		$img = $photo->getMaxAvailableImg( true );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_ORIGINAL ), $img['href'] );
		$this->assertEquals( 350, $img['width'] );
		$this->assertEquals( 150, $img['height'] );
	}

	public function testPhotoGetMaxAvailableImgHref() {
		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testPhotoGetMaxAvailableImgHref',
		) )->load();

		$this->assertEquals( $photo->getImgHref( $photo::SIZE_L ), $photo->getMaxAvailableImgHref() );
		$this->assertEquals( $photo->getImgHref( $photo::SIZE_ORIGINAL ), $photo->getMaxAvailableImgHref( true ) );
	}

	public function testPhotoGetMaxAvailableImgWidth() {
		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testPhotoGetMaxAvailableImgHref',
		) )->load();

		$this->assertEquals( 350, $photo->getMaxAvailableImgWidth() );
		$this->assertEquals( 350, $photo->getMaxAvailableImgWidth( true ) );
	}

	public function testPhotoGetMaxAvailableImgHeight() {
		$photo = $this->api->createPhoto( array(
			'image' => FOTKI_API_ASSETS . '/test.png',
			'title' => 'testPhotoGetMaxAvailableImgHref',
		) )->load();

		$this->assertEquals( 150, $photo->getMaxAvailableImgHeight() );
		$this->assertEquals( 150, $photo->getMaxAvailableImgHeight( true ) );
	}

}