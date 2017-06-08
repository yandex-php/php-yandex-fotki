<?php
namespace Yandex\Fotki;

use Unirest\File;
use Unirest\Request;
use Yandex\Fotki\Api\Album;
use Yandex\Fotki\Api\AlbumsCollection;
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
	 * @return Transport
	 */
	public function getTransport() {
		return $this->_transport;
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
	 * Добавление нового альбома.
	 *
	 * <h1>Примеры</h1>
	 *
	 * <h2>Простой пример</h2>
	 * <code>
	 * <?php
	 * $album = $api->createAlbum( array(
	 *     'title'    => 'Мой альбом',
	 *     'summary'  => 'Описание моего альбома'
	 * ) );
	 * ?>
	 * </code>
	 *
	 * <h2>Альбом с паролем</h2>
	 * <code>
	 * <?php
	 * $album = $api->createAlbum( array(
	 *     'title'    => 'Мой день рождения',
	 *     'password' => 'asd123'
	 * ) );
	 * ?>
	 * </code>
	 *
	 * <h2>Вложенный альбом</h2>
	 * <code>
	 * <?php
	 * $album = $api->createAlbum( array(
	 *     'title' => 'Дочерний альбом'
	 * ), 456789 );
	 * ?>
	 * </code>
	 *
	 * @param array    $data         Данные альбома
	 *                               <ul>
	 *                               <li> ['title']    <i><u>string</u></i> Название <b>(Обязательный)</b></li>
	 *                               <li> ['summary']  <i><u>string</u></i> Описание</li>
	 *                               <li> ['password'] <i><u>string</u></i> Пароль</li>
	 *                               </ul>
	 *
	 * @param int|null $parentId     Id родительского альбома. Если null, то альбом будет корневым.
	 *
	 * @return Album Незагруженный альбом. Чтобы продолжить с ним работать,
	 * не забудьте вызвать метод \Yandex\Fotki\Api\Album::load
	 *
	 * @throws \Yandex\Fotki\Exception\Api\Album Если не удалось добавить альбом
	 * @throws \Yandex\Fotki\Exception\InvalidCall Если произошла ошибка при генерации XML
	 */
	public function createAlbum( $data, $parentId = null ) {

		$url = sprintf( "http://api-fotki.yandex.ru/api/users/%s/albums/", $this->_login );

		$album = new Album( $this->_transport, "{$url}?format=json" );
		$album->setAuthor( $this->_login );
		$album->setTitle( isset( $data['title'] ) ? $data['title'] : null );
		$album->setSummary( isset( $data['summary'] ) ? $data['summary'] : null );
		$album->setPassword( isset( $data['password'] ) ? $data['password'] : null );
		$album->setParentId( $parentId );

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
	 * @return Album
	 */
	public function getAlbum( $id ) {
		$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/?format=json", $this->_login, trim( $id ) );
		$album  = new Album( $this->_transport, $apiUrl );

		return $album;
	}

	/**
	 * Редактирование альбома
	 *
	 * <h1>Примеры</h1>
	 *
	 * <h2>Изменение заголовка</h2>
	 * <code>
	 * <?php
	 * $album = $api->getAlbum(123456)->load();
	 * $album->setTitle('Новое название');
	 *
	 * $updatedAlbum = $api->updateAlbum($album)->load();
	 * echo $updatedAlbum->getTitle();
	 * ?>
	 * </code>
	 *
	 * <h2>Изменение ссылки на родительский альбом</h2>
	 * <code>
	 * <?php
	 * $album = $api->getAlbum(123456)->load();
	 * $album->setParentId(654321);
	 *
	 * $updatedAlbum = $api->updateAlbum($album)->load();
	 * echo $updatedAlbum->getParentId();
	 * ?>
	 * </code>
	 *
	 * @param Album $album Альбом, который нужно обновить
	 *
	 * @return Album Обновленный альбом. Чтобы продолжить с ним работать,
	 * не забудьте вызвать метод \Yandex\Fotki\Api\Album::load
	 *
	 * @throws \Yandex\Fotki\Exception\Api\Album Если не удалось обновить альбом
	 * @throws \Yandex\Fotki\Exception\InvalidCall Если произошла ошибка при генерации XML
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
	 * Удаление альбома. ИСПОЛЬЗОВАТЬ С ОСТОРОЖНОСТЬЮ.
	 *
	 * Пожалуйста, БУДЬТЕ КРАЙНЕ ВНИМАТЕЛЬНЫ, так как одним запросом можно удалить
	 * ВСЕ дочерние альбомы и фотографии и загубить свою судьбу.
	 *
	 * При вызове без явного указания на удаление дочерних фото и альбомов
	 * будет произведена проверка на наличие таковых. И если они будут найдены,
	 * то будет выброшено исключение.
	 *
	 * <h1>Примеры</h1>
	 *
	 * <h2>Удаление альбома</h2>
	 * <code>
	 * <?php
	 * $api->deleteAlbum(123456);
	 * ?>
	 * </code>
	 *
	 * <h2>Удаление альбома вместе с дочерними фотографиями</h2>
	 * Обратите внимание на то, что поиск фотографий будет производиться
	 * ТОЛЬКО в указанном альбоме, но не в его дочерних альбомах.
	 * Если внутри будет альбом "Неразобранное", то фотографии не будут найдены.
	 *
	 * <code>
	 * <?php
	 * $api->deleteAlbum(123456, $api::DELETE_ALBUM_WITH_PHOTOS_YES);
	 * ?>
	 * </code>
	 *
	 * <h2>Удаление альбома вместе с дочерними фотографиями и дочерними альбомами</h2>
	 * Самый опасный вариант вызова - можно удалить ВСЕ данные.
	 * Дважды подумайте перед вызовом - если вы передатите не тот ID,
	 * может удалиться вся ваша коллекция, а вас будут презирать и порицать.
	 *
	 * <code>
	 * <?php
	 * $api->deleteAlbum(123456, $api::DELETE_ALBUM_WITH_PHOTOS_YES, $api::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_YES);
	 * ?>
	 * </code>
	 *
	 * @param int    $albumId            Id альбома, который нужно удалить
	 * @param string $withPhotos         Удалить вместе с дочерними фотографиями.
	 *                                   Не является bool-значением во избежание
	 *                                   неправильных неумышленных вызовов.
	 * @param string $withChildrenAlbums Удалить вместе с дочерними альбомами.
	 *                                   Не является bool-значением во избежание
	 *                                   неправильных неумышленных вызовов.
	 *
	 * @see \Yandex\Fotki\Api::DELETE_ALBUM_WITH_PHOTOS_NO
	 * @see \Yandex\Fotki\Api::DELETE_ALBUM_WITH_PHOTOS_YES
	 * @see \Yandex\Fotki\Api::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_NO
	 * @see \Yandex\Fotki\Api::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_YES
	 *
	 * @see \Yandex\Fotki\Exception\Api\DangerousAlbumDeleting::getAlbums
	 * @see \Yandex\Fotki\Exception\Api\DangerousAlbumDeleting::getPhotos
	 *
	 * @return $this
	 * @throws \Yandex\Fotki\Exception\Api\Album Если произошла ошибка во время запроса на удаление
	 * @throws \Yandex\Fotki\Exception\Api\DangerousAlbumDeleting Если был вызов на удаление без
	 *                                                            явного указания, что нужно удалять дочерние альбомы или фото.
	 *                                                            Из исключения можно получить список дочерних альбомов и фото.
	 */
	public function deleteAlbum( $albumId, $withPhotos = self::DELETE_ALBUM_WITH_PHOTOS_NO, $withChildrenAlbums = self::DELETE_ALBUM_WITH_CHILDREN_ALBUMS_NO ) {
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
	 * Получение древовидной структуры альбомов.
	 *
	 * Если указать, структуру какого альбома отобразить, то
	 * будут возвращены только его дочерние альбомы.
	 *
	 * <h1>ВАЖНО</h1>
	 * Пожалуйста, не используйте метод \Yandex\Fotki\Api\Album::getChildren,
	 * так как он отдаст только то, что было программно записано в \Yandex\Fotki\Api\Album::$_children.
	 * Загрузки данных с сервера этот метод не производит.
	 * Пока поддержка загрузки прямо из альбома не реализована из-за архитектурных сложностей.
	 * Используйте этот метод - например:
	 *
	 * <code>
	 * <?php
	 * $album = $api->getAlbum(123456);
	 *
	 * // Вместо вызова этого кода:
	 * // $children = $album->getChildren()
	 *
	 * // Используйте этот
	 * $children = $api->getAlbumsTree($album)
	 * </code>
	 *
	 * <h1>Примеры</h1>
	 *
	 * <h2>Получение полной иерархии</h2>
	 * <code>
	 * <?php
	 * $rootAlbum1 = $api->getAlbum(123456);
	 * $rootAlbum2 = $api->getAlbum(123457);
	 *
	 * // Иерерахия представляет из себя массив корневых альбомов.
	 * $tree = $api->getAlbumsTree();
	 *
	 * echo $tree[$rootAlbum1->getId()]->getTitle();
	 * echo $tree[$rootAlbum2->getId()]->getTitle();
	 *
	 * // Для каждого дочернего альбома можно получить список его дочерних альбомов.
	 * $children = $tree[$rootAlbum1->getId()]->getChildren();
	 * foreach($children as $id => $childAlbum){
	 *     echo $childAlbum->getTitle();
	 * }
	 * ?>
	 * </code>
	 *
	 *
	 * <h2>Получение иерархии конкретного альбома</h2>
	 * <code>
	 * <?php
	 * $parentAlbum = $api->getAlbum(123455);
	 *
	 * // Таким образом можно загрузить все дочерние альбомы указанного альбома
	 * $children = $api->getAlbumsTree($parentAlbum);
	 * // $children = $api->getAlbumsTree(123455); // Можно указывать и просто Id альбома
	 *
	 * // Для каждого дочернего альбома можно получить список его дочерних альбомов.
	 * foreach($children as $id => $childAlbum){
	 *     $totalChildren = count($childAlbum->getChildren();
	 *     if($totalChildren){
	 *         echo "{$childAlbum->getTitle()} имеет {$totalChildren} дочерних альбомов."
	 *     }else{
	 *         echo "{$childAlbum->getTitle()} не имеет дочерних альбомов."
	 *     }
	 * }
	 * ?>
	 * </code>
	 *
	 * @param Album|int|string|null $album Альбом, либо его ID.
	 *                                     Если аргумент указан, то будет возвращен массив
	 *                                     дочерних альбомов.
	 *                                     Если передан null, то будет возвращено полное дерево
	 *                                     коллекции альбомов
	 *
	 * @return Album[]
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 */
	public function getAlbumsTree( $album = null ) {
		$albumId = null;
		if ( is_numeric( $album ) ) {
			$albumId = intval( $album );
		} elseif ( $album instanceof Album ) {
			if ( ! $album->getId() ) {
				$album->load();
			}

			$albumId = $album->getId();
		} elseif ( ! is_null( $album ) ) {
			$instance = '\Yandex\Fotki\Api\Album';
			$type     = gettype( $album );
			throw new \Yandex\Fotki\Exception\Api\Album( "\$album must be an instance of {$instance} or numeric. {$type} given" );
		}

		/** @var Album[] $albumsArray */
		$albumsArray = $this->getAlbumsCollection()->setLimit(AlbumsCollection::MAX_LIMIT)->loadAll(PHP_INT_MAX)->getList();
		$tree        = array();
		$rootIds     = array();

		foreach ( $albumsArray as $id => $albumItem ) {
			if ( $albumItem->getParentId() ) {
				$albumsArray[ $albumItem->getParentId() ]->addChild( $albumItem );
				$albumsArray[ $id ]->setParent( $albumsArray[ $albumItem->getParentId() ] );
			} else {
				$rootIds[] = $id;
			}
		}

		if ( $albumId ) {
			$tree = $albumsArray[ $albumId ]->getChildren();
		} else {
			foreach ( $rootIds as $rootId ) {
				$tree[ $rootId ] = $albumsArray[ $rootId ];
			}
		}

		return $tree;
	}

	/**
	 * Добавление фотографии.
	 *
	 * <h1>Примеры</h1>
	 *
	 * <h2>Простой пример загрузки фотографии</h2>
	 * <code>
	 * <?php
	 * $photo = $api->createPhoto( array(
	 *     'image' => $_SERVER['DOCUMENT_ROOT'] . '/assets/images/forest.jpg',
	 *     'title' => 'Красивая фотография леса'
	 * ) );
	 * ?>
	 * </code>
	 *
	 * <h2>Загрузка фотографии в определенный альбом</h2>
	 * <code>
	 * <?php
	 * $photo = $api->createPhoto( array(
	 *     'image' => $_SERVER['DOCUMENT_ROOT'] . '/assets/images/forest.jpg',
	 *     'title' => 'Красивая фотография леса'
	 * ), 123456 );
	 * ?>
	 * </code>
	 *
	 * <h2>Загрузка фотографии в с геопривязкой</h2>
	 * <code>
	 * <?php
	 * $photo = $api->createPhoto( array(
	 *     'image' => $_SERVER['DOCUMENT_ROOT'] . '/assets/images/forest.jpg',
	 *     'title' => 'Красивая фотография леса',
	 *     'geo'   => array(55.75396, 37.620393),
	 *   //'geo'   => '55.75396 37.620393', // Можно задать и строкой
	 * ) );
	 * ?>
	 * </code>
	 *
	 * <h2>Загрузка фотографии в с тегами</h2>
	 * <code>
	 * <?php
	 * $photo = $api->createPhoto( array(
	 *     'image' => $_SERVER['DOCUMENT_ROOT'] . '/assets/images/forest.jpg',
	 *     'title' => 'Красивая фотография леса',
	 *     'tags'  => array('Лес', 'Природа', 'Лето'),
	 *   //'tags'  => 'Лес, Природа, Лето' // Можно задать и строкой
	 * ) );
	 * ?>
	 * </code>
	 *
	 * <h2>Собираем все вместе</h2>
	 * <code>
	 * <?php
	 * $photo = $api->createPhoto( array(
	 *     'image'             => $_SERVER['DOCUMENT_ROOT'] . '/assets/images/forest.jpg',
	 *     'title'             => 'Красивая фотография леса',
	 *     'geo'               => array(55.75396, 37.620393),
	 *     'tags'              => array('Лес', 'Природа', 'Лето'),
	 *     'isAdult'           => false,     // Материал для взрослых
	 *     'isDisableComments' => true,      // Указание на отключение комментариев
	 *     'isHideOriginal'    => true,      // Указание на то, что нужно скрыть оригинал
	 *     'access'            => 'private', // Может быть 'public', 'friends', 'private'
	 * ) );
	 * ?>
	 * </code>
	 *
	 * @param array    $data         Данные фотографии
	 *                               <ul>
	 *                               <li> ['image']             <i><u>string</u></i>                       Путь до изображения <b>(Обязательный)</b></li>
	 *                               <li> ['title']             <i><u>string</u></i>                       Название изображения <b>(Обязательный)</b></li>
	 *                               <li> ['geo']               <i><u>string|string[]</u></i>              Координаты</li>
	 *                               <li> ['tags']              <i><u>string|string[]</u></i>              Теги</li>
	 *                               <li> ['isAdult']           <i><u>bool</u></i>                         Метриал для взрослых</li>
	 *                               <li> ['isDisableComments'] <i><u>bool</u></i>                         Отключить комментарии</li>
	 *                               <li> ['isHideOriginal']    <i><u>bool</u></i>                         Скрывать оригинал изображения</li>
	 *                               <li> ['access']            <i><u>'public'|'friends'|'private'</u></i> Уровень доступа</li>
	 *                               </ul>
	 *
	 * @param int|null $albumId      Id родительского альбома. Если null, то фото будет загружено в корень
	 *
	 * @return \Yandex\Fotki\Api\Photo Добавленная фотография
	 * @throws \Yandex\Fotki\Exception\Api\Photo Если произошла ошибка во время загрузки фотографии
	 */
	public function createPhoto( $data, $albumId = null ) {
		$url = $albumId
			? sprintf( "http://api-fotki.yandex.ru/api/users/%s/album/%s/photos/?format=json", $this->_login, intval( $albumId ) )
			: sprintf( "http://api-fotki.yandex.ru/api/users/%s/photos/?format=json", $this->_login );

		$oAuthToken = $this->_transport->getOAuthToken();
		$fimpToken  = $this->_transport->getFimpToken();

		$headers = array(
			'Accept'        => "application/json",
			'Authorization' => $oAuthToken ? "OAuth {$oAuthToken}" : "FimpToken realm=\"fotki.yandex.ru\", token=\"{$fimpToken}\""
		);

		$response = Request::post( $url, $headers, array(
			'image' => File::add( $data['image'] )
		) );

		if ( $response->code === 201 ) {
			$responseBody = json_decode( json_encode( $response->body ), true );
			$photo        = new Photo( $this->_transport, $responseBody['links']['self'] );
			$photo->initWithData( $responseBody );

			//@formatter:off
			$photo->setTitle(             isset( $data['title'] )               ? $data['title']                : $photo->getTitle() );
			$photo->setSummary(           isset( $data['summary'] )             ? $data['summary']              : $photo->getSummary() );
			$photo->setIsAdult(           isset( $data['isAdult'] )             ? $data['isAdult']              : $photo->isAdult() );
			$photo->setIsHideOriginal(    isset( $data['isHideOriginal'] )      ? $data['isHideOriginal']       : $photo->isHideOriginal() );
			$photo->setIsDisableComments( isset( $data['isDisableComments'] )   ? $data['isDisableComments']    : $photo->isDisableComments() );
			$photo->setAccess(            isset( $data['access'] )              ? $data['access']               : $photo->getAccess() );
			$photo->setGeo(               isset( $data['geo'] )                 ? $data['geo']                  : $photo->getGeo() );
			$photo->setTags(              isset( $data['tags'] )                ? $data['tags']                 : $photo->getTags() );
			//@formatter:on

			return $this->updatePhoto( $photo );
		} else {
			throw new \Yandex\Fotki\Exception\Api\Photo( $response->body, $response->code );
		}
	}

	/**
	 * Редактирование фотографии
	 *
	 * <h1>Примеры</h1>
	 *
	 * <h2>Изменение заголовка</h2>
	 * <code>
	 * <?php
	 * $photo = $api->getPhoto(12345678)->load();
	 * $photo->setTitle('Новое название');
	 *
	 * $updatedPhoto = $api->updatePhoto($photo)->load();
	 * echo $updatedPhoto->getTitle();
	 * ?>
	 * </code>
	 *
	 * <h2>Изменение ссылки на родительский альбом</h2>
	 * <code>
	 * <?php
	 * $photo = $api->getPhoto(12345678)->load();
	 * $photo->setAlbumId(654321);
	 *
	 * $updatedPhoto = $api->updatePhoto($photo)->load();
	 * echo $updatedPhoto->getAlbumId();
	 * ?>
	 * </code>
	 *
	 * @param \Yandex\Fotki\Api\Photo $photo
	 *
	 * @return \Yandex\Fotki\Api\Photo Фотография, которую нужно обновить
	 * @throws \Yandex\Fotki\Exception\Api\Photo Если призошла ошибка во время запроса на обновление
	 * @throws \Yandex\Fotki\Exception\InvalidCall Если произошла ошибка при геренации XML
	 */
	public function updatePhoto( Photo $photo ) {
		$oAuthToken = $this->_transport->getOAuthToken();
		$fimpToken  = $this->_transport->getFimpToken();

		$body = $photo->getAtomEntryForSave()->asXML();

		$headers  = array(
			'Authorization' => $oAuthToken ? "OAuth {$oAuthToken}" : "FimpToken realm=\"fotki.yandex.ru\", token=\"{$fimpToken}\"",
			'Content-Type'  => 'application/atom+xml; type=entry'
		);
		$response = Request::put( $photo->getApiUrlEdit(), $headers, $body );

		if ( $response->code === 200 ) {
			$url = sprintf( "http://api-fotki.yandex.ru/api/users/%s/photo/%s/?format=json", $this->_login, intval( $photo->getId() ) );

			return new Photo( $this->_transport, $url );
		} else {
			throw new \Yandex\Fotki\Exception\Api\Photo( $response->body, $response->code );
		}
	}

	/**
	 * @param int $id
	 *
	 * @return \Yandex\Fotki\Api\Photo
	 */
	public function getPhoto( $id ) {
		$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/photo/%s/?format=json", $this->_login, trim( $id ) );
		$photo  = new \Yandex\Fotki\Api\Photo( $this->_transport, $apiUrl );

		return $photo;
	}

	/**
	 * Удаление фотографии.
	 *
	 * <h1>Примеры</h1>
	 *
	 * <h2>Удаление альбома</h2>
	 * <code>
	 * <?php
	 * $api->deletePhoto(12345678);
	 * ?>
	 * </code>
	 *
	 * @param int $photoId Id фотографии, которую нужно удалить
	 *
	 * @return $this
	 * @throws \Yandex\Fotki\Exception\Api\Photo
	 */
	public function deletePhoto( $photoId ) {
		$apiUrl = sprintf( "http://api-fotki.yandex.ru/api/users/%s/photo/%s/", $this->_login, intval( $photoId ) );

		$oAuthToken = $this->_transport->getOAuthToken();
		$fimpToken  = $this->_transport->getFimpToken();

		$response = Request::delete( $apiUrl, array(
			'Authorization' => $oAuthToken ? "OAuth {$oAuthToken}" : "FimpToken realm=\"fotki.yandex.ru\", token=\"{$fimpToken}\""
		) );

		if ( $response->code === 204 ) {
			return $this;
		} else {
			throw new \Yandex\Fotki\Exception\Api\Photo( $response->body, $response->code );
		}
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