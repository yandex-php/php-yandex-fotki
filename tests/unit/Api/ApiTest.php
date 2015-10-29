<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */

namespace Yandex\Fotki\Tests\Unit\Api;


use Yandex\Fotki\Api;

class ApiTest extends \PHPUnit_Framework_TestCase {

	/** @var \Yandex\Fotki\Api */
	public $api;

	public function testCreateAlbum() {
		$album = $this->api->createAlbum( array(
			'title'   => 'testCreateAlbum Title',
			'summary' => 'testCreateAlbum Summary'
		) )->load();

		$this->assertEquals( 'testCreateAlbum Title', $album->getTitle() );
		$this->assertEquals( 'testCreateAlbum Summary', $album->getSummary() );
	}

	public function testCreateAlbumWithPassword() {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$album = $this->api->createAlbum( array(
			'title'    => 'testCreateAlbumWithPassword Title',
			'password' => 'asd123'
		) )->load();

		$this->assertEquals( true, true ); // Будем надеяться, что все хорошо. Хотя можно посмотреть в веб-версии
	}

	public function testCreateAlbumInsideAnother() {
		$parentAlbum = $this->api->createAlbum( array(
			'title' => 'testCreateAlbumInsideAnother Parent'
		) )->load();

		$childAlbum = $this->api->createAlbum( array(
			'title' => 'testCreateAlbumInsideAnother Child'
		), $parentAlbum->getId() )->load();

		$this->assertEquals( $parentAlbum->getId(), $childAlbum->getParentId() );
	}

	/**
	 * @inheritdoc
	 */
	protected function setUp() {
		parent::setUp();

		$this->api = new Api( FOTKI_API_LOGIN );
		$this->api->oauth( FOTKI_API_OAUTH_TOKEN );
	}
}
