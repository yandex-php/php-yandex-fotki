<?php
namespace Yandex\Fotki;

use Unirest\Request;
use Yandex\Fotki\Api\Album;
use Yandex\Fotki\Api\Photo;
use Yandex\Fotki\Exception\Api\DangerousAlbumDeleting;

/**
 * Class Api
 * @package Yandex\Fotki
 * @author  Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
class Api {

	const DELETE_ALBUM_WITH_PHOTOS_YES = 'DELETE_ALBUM_WITH_PHOTOS_YES';
	const DELETE_ALBUM_WITH_PHOTOS_NO  = 'DELETE_ALBUM_WITH_PHOTOS_NO';

	const DELETE_ALBUM_WITH_CHILDREN_ALBUMS_YES = 'DELETE_ALBUM_WITH_CHILDREN_ALBUMS_YES';
	const DELETE_ALBUM_WITH_CHILDREN_ALBUMS_NO  = 'DELETE_ALBUM_WITH_CHILDREN_ALBUMS_NO';

	/**
	 * @var \Yandex\Fotki\Transport
	 */
	protected $_transport;
	/**
	 * @var \Yandex\Fotki\Api\ServiceDocument
	 */
	protected $_serviceDocument;
	/**
	 * @var string
	 */
	protected $_login;

	/**
	 * @param string $login
	 */
	public function __construct( $login ) {
		$this->_login           = (string) $login;
		$this->_transport       = new \Yandex\Fotki\Transport();
		$this->_serviceDocument = new \Yandex\Fotki\Api\ServiceDocument( $this->_transport, $this->_login );
	}

	/**
	 * @deprecated
	 *
	 * @param $str
	 *
	 * @return self
	 */
	public function auth( $str ) {
		$token    = null;
		$password = null;
		// пароль на Яндексе не может быть более 20 символов
		if ( strlen( $str ) <= 20 ) {
			/** @noinspection PhpDeprecationInspection */
			$this->password( $str );
		} else {
			/** @noinspection PhpDeprecationInspection */
			$this->fimp( $str );
		}

		return $this;
	}

	/**
	 * Авторизация по паролю
	 * @deprecated
	 *
	 * @param string $password
	 *
	 * @return self
	 */
	public function password( $password ) {
		trigger_error( '\\Yandex\\Fotki\\Api::password() is deprecated! Use \\Yandex\\Fotki\\Api::oauth()', E_USER_DEPRECATED );
		$auth = new \Yandex\Fotki\Api\FimpAuth( $this->_transport, $this->_login, $password, null );
		$this->_transport->setFimpToken( $auth->getToken() );

		return $this;
	}

	/**
	 * Авторизация по fimp-токену
	 * @deprecated
	 *
	 * @param string $token Fimp токен
	 *
	 * @return self
	 */
	public function fimp( $token ) {
		trigger_error( '\\Yandex\\Fotki\\Api::fimp() is deprecated! Use \\Yandex\\Fotki\\Api::oauth()', E_USER_DEPRECATED );
		$this->_transport->setFimpToken( $token );

		return $this;
	}

	/**
	 * Авторизацию по oauth-токену
	 *
	 * @param string $token OAuth токен
	 *
	 * @return self
	 */
	public function oauth( $token ) {
		$this->_transport->setOAuthToken( $token );

		return $this;
	}

	/**
	 * Загрузка сервисного документа
	 * @return self
	 */
	public function loadServiceDocument() {
		$this->_serviceDocument->load();

		return $this;
	}

	/**
	 * @return \Yandex\Fotki\Api\ServiceDocument
	 */
	public function getServiceDocument() {
		return $this->_serviceDocument;
	}

	/**
	 * @return \Yandex\Fotki\Api\PhotosCollection
	 */
	public function getPhotosCollection() {
		$apiUrl           = $this->_serviceDocument->getUrlPhotosCollection();
		$photosCollection = new \Yandex\Fotki\Api\PhotosCollection( $this->_transport, $apiUrl );

		return $photosCollection;
	}

	/**
	 * @return \Yandex\Fotki\Api\TagsCollection
	 */
	public function getTagsCollection() {
		$apiUrl         = $this->_serviceDocument->getUrlTagsCollection();
		$tagsCollection = new \Yandex\Fotki\Api\TagsCollection( $this->_transport, $apiUrl );

		return $tagsCollection;
	}

	/**
	 * @param string $title
	 *
	 * @return \Yandex\Fotki\Api\Tag
	 */
	public function getTag( $title ) {
		$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/tag/%s/?format=json", $this->_login, trim( $title ) );
		$tag    = new \Yandex\Fotki\Api\Tag( $this->_transport, $apiUrl );

		return $tag;
	}

	/**
	 * @param array $data
	 * @param int   $albumId
	 *
	 * @return \Yandex\Fotki\Api\Album
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 * @throws \Yandex\Fotki\Exception\InvalidCall
	 */
	public function createAlbum( $data, $albumId ) {

		$url = $albumId
			? sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/", $this->_login, intval( $albumId ) )
			: sprintf( "http://api-fotki.yandex.ru/api/users/%s/albums/", $this->_login );

		$album = new Album( $this->_transport, "{$url}?format=json" );
		$album->setAuthor( $this->_login );
		$album->setTitle( isset( $data['title'] ) ? $data['title'] : null );
		$album->setSummary( isset( $data['summary'] ) ? $data['summary'] : null );
		$album->setPassword( isset( $data['password'] ) ? $data['password'] : null );
		$album->setParentId( isset( $data['parentId'] ) ? $data['parentId'] : null );

		$oAuthToken = $this->_transport->getOAuthToken();
		$fimpToken  = $this->_transport->getFimpToken();

		$headers = array(
			'Authorization' => $oAuthToken ? "OAuth {$oAuthToken}" : "FimpToken realm=\"fotki.yandex.ru\", token=\"{$fimpToken}\"",
			'Content-Type'  => 'application/atom+xml; type=entry'
		);
		$body    = $album->getAtomEntryForSave()->asXML();

		$response = Request::post( $url, $headers, $body );
		if ( $response->code === 201 ) {
			$responseXml = new \SimpleXMLElement( $response->body );
			/** @noinspection PhpUndefinedFieldInspection */
			$createdId = strval( $responseXml->id );
			$createdId = substr( $createdId, strrpos( $createdId, ':' ) + 1 );
			$apiUrl    = sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/", $this->_login, $createdId );

			return new Album( $this->_transport, $apiUrl );
		} else {
			throw new \Yandex\Fotki\Exception\Api\Album( $response->body, $response->code );
		}
	}

	/**
	 * @param string|int $id
	 *
	 * @return \Yandex\Fotki\Api\Album
	 */
	public function getAlbum( $id ) {
		$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/?format=json", $this->_login, trim( $id ) );
		$album  = new \Yandex\Fotki\Api\Album( $this->_transport, $apiUrl );

		return $album;
	}

	/**
	 * @param \Yandex\Fotki\Api\Album $album
	 *
	 * @return \Yandex\Fotki\Api\Album
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 * @throws \Yandex\Fotki\Exception\InvalidCall
	 */
	public function updateAlbum( Album $album ) {
		$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/", $this->_login, intval( $album->getId() ) );

		$oAuthToken = $this->_transport->getOAuthToken();
		$fimpToken  = $this->_transport->getFimpToken();

		$headers = array(
			'Authorization' => $oAuthToken ? "OAuth {$oAuthToken}" : "FimpToken realm=\"fotki.yandex.ru\", token=\"{$fimpToken}\"",
			'Content-Type'  => 'application/atom+xml; type=entry'
		);
		$body    = $album->getAtomEntryForSave()->asXML();

		$response = Request::put( $apiUrl, $headers, $body );

		if ( $response->code === 200 ) {
			$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/", $this->_login, intval( $album->getId() ) );

			return new Album( $this->_transport, $apiUrl );
		} else {
			throw new \Yandex\Fotki\Exception\Api\Album( $response->body, $response->code );
		}
	}

	/**
	 * @param int    $albumId
	 * @param string $withPhotos
	 * @param string $withChildrenAlbums
	 *
	 * @return $this
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 * @throws \Yandex\Fotki\Exception\Api\DangerousAlbumDeleting
	 */
	public function deleteAlbum(
		$albumId,
		$withPhotos = self::DELETE_ALBUM_WITH_PHOTOS_NO,
		$withChildrenAlbums = self::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_NO
	) {
		$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/", $this->_login, intval( $albumId ) );
		$album  = new Album( $this->_transport, $apiUrl );

		/** @var Album[] $relatedAlbums */
		$relatedAlbums = array();
		/** @var Photo[] $relatedPhotos */
		$relatedPhotos = array();

		if ( $withPhotos !== self::DELETE_ALBUM_WITH_PHOTOS_YES ) {
			$photos = $album->load()->getList();
			foreach ( $photos as $photo ) {
				$relatedPhotos[ $photo->getId() ] = $photo;
			}
		}

		if ( $withChildrenAlbums !== self::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_YES ) {
			/** @var Album[] $albums */
			$albums = $this->getAlbumsCollection()->loadAll()->getList();
			foreach ( $albums as $album ) {
				if ( $album->getParentId() == $albumId ) {
					$relatedAlbums[ $album->getId() ] = $album;
				}
			}
		}

		if ( count( $relatedAlbums ) || count( $relatedPhotos ) ) {
			throw new DangerousAlbumDeleting( $relatedAlbums, $relatedPhotos );
		}

		$oAuthToken = $this->_transport->getOAuthToken();
		$fimpToken  = $this->_transport->getFimpToken();

		$response = Request::delete( $apiUrl, array(
			'Authorization' => $oAuthToken ? "OAuth {$oAuthToken}" : "FimpToken realm=\"fotki.yandex.ru\", token=\"{$fimpToken}\""
		) );

		if ( $response->code === 204 ) {
			return $this;
		} else {
			throw new \Yandex\Fotki\Exception\Api\Album( $response->body, $response->code );
		}
	}

	/**
	 * @return \Yandex\Fotki\Api\AlbumsCollection
	 */
	public function getAlbumsCollection() {
		$apiUrl           = $this->_serviceDocument->getUrlAlbumsCollection();
		$albumsCollection = new \Yandex\Fotki\Api\AlbumsCollection( $this->_transport, $apiUrl );

		return $albumsCollection;
	}

	/**
	 * @param $data
	 * @param $albumId
	 *
	 * @return $this
	 * @throws \Yandex\Fotki\Exception\Api\Photo
	 */
	public function sendPhoto( $data, $albumId ) {
		if ( $albumId ) {
			$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/photos/?format=json", $this->_login, trim( $albumId ) );
		} else {
			$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/photos/?format=json", $this->_login );
		}
		$photo = new \Yandex\Fotki\Api\Photo( $this->_transport, $apiUrl );

		return $photo->upload( $data );
	}
}