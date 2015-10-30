<?php
namespace Yandex\Fotki\Api;

use Yandex\Fotki\Exception\InvalidCall;

/**
 * Class Photo
 * @package Yandex\Fotki\Api
 * @author  Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see     http://api.yandex.ru/fotki/doc/operations-ref/photo-get.xml
 */
class Photo extends \Yandex\Fotki\ApiAbstract {
	/**
	 * Сторона ограничивающего квадрата 50px
	 */
	const SIZE_XXXS = 'XXXS';
	/**
	 * Сторона ограничивающего квадрата 75px
	 */
	const SIZE_XXS = 'XXS';
	/**
	 * Сторона ограничивающего квадрата 100px
	 */
	const SIZE_XS = 'XS';
	/**
	 * Сторона ограничивающего квадрата 150px
	 */
	const SIZE_S = 'S';
	/**
	 * Сторона ограничивающего квадрата 300px
	 */
	const SIZE_M = 'M';
	/**
	 * Сторона ограничивающего квадрата 500px
	 */
	const SIZE_L = 'L';
	/**
	 * Сторона ограничивающего квадрата 800px
	 */
	const SIZE_XL = 'XL';
	/**
	 * Сторона ограничивающего квадрата 1024px
	 */
	const SIZE_XXL = 'XXL';
	/**
	 * Сторона ограничивающего квадрата 1280px
	 */
	const SIZE_XXXL = 'XXXL';
	/**
	 * Оригинальный размер фотографии
	 */
	const SIZE_ORIGINAL = 'orig';
	/**
	 * @var string ID альбома
	 */
	protected $_albumId;
	/**
	 * @var string
	 */
	protected $_apiUrl;
	/**
	 * @var string Идентификатор Atom Entry фотографии
	 */
	protected $_atomId;
	/**
	 * @var string Название фотографии
	 */
	protected $_title;
	/**
	 * @var string
	 */
	protected $_summary;
	/**
	 * @var string Логин пользователя на Яндекс.Фотках
	 */
	protected $_author;
	/**
	 * @var string Ссылка для редактирования ресурса фотографии
	 */
	protected $_apiUrlEdit;
	/**
	 * @var string Ссылка на web-страницу фотографии в интерфейсе Яндекс.Фоток
	 */
	protected $_url;
	/**
	 * @var string Ссылка для редактирования содержания ресурса фотографии (графического файла)
	 */
	protected $_apiUrlEditMedia;
	/**
	 * @var string Ссылка на альбом, в котором содержится фотография
	 */
	protected $_apiUrlAlbum;
	/**
	 * @var int Время последнего редактирования фотографии
	 */
	protected $_dateEdited;
	/**
	 * @var int Время последнего значимого с точки зрения системы изменения альбома
	 */
	protected $_dateUpdated;
	/**
	 * @var int Время загрузки фотографии
	 */
	protected $_datePublished;
	/**
	 * @var int Дата создания фотографии согласно ее EXIF-данным
	 */
	protected $_dateCreated;
	/**
	 * @var string Уровень доступа к фотографии
	 */
	protected $_access;
	/**
	 * @var bool Флаг доступности фотографии только взрослой аудитории
	 */
	protected $_isAdult;
	/**
	 * @var bool Флаг, запрещающий показ оригинала фотографии
	 */
	protected $_isHideOriginal;
	/**
	 * @var bool Флаг, запрещающий комментирование фотографии
	 */
	protected $_isDisableComments;
	/**
	 * @var array
	 */
	protected $_img = array();
	/**
	 * @var array Географическая привязка фотографии к карте
	 */
	protected $_geo;
	/**
	 * @var string Адресная привязка фотографии карте. Содержит адрес, а также географические координаты объекта
	 */
	protected $_address;
	/**
	 * @var string Ссылка на графический файл фотографии
	 */
	protected $_content;
	/**
	 * @var string
	 */
	protected $_tags;

	/**
	 * @param \Yandex\Fotki\Transport $transport
	 * @param string                  $apiUrl
	 *
	 * @return self
	 */
	public function __construct( \Yandex\Fotki\Transport $transport, $apiUrl = null ) {
		$this->_transport = $transport;
		$this->_apiUrl    = $apiUrl;
	}

	/**
	 * @return string
	 */
	public function getAccess() {
		return $this->_access;
	}

	/**
	 * @param string $access
	 *
	 * @return self
	 */
	public function setAccess( $access ) {
		$this->_access = is_null( $access ) ? null : (string) $access;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAddress() {
		return $this->_address;
	}

	/**
	 * @return string
	 */
	public function getApiUrlAlbum() {
		return $this->_apiUrlAlbum;
	}

	/**
	 * @param string $apiUrlAlbum
	 */
	public function setApiUrlAlbum( $apiUrlAlbum ) {
		$this->_apiUrlAlbum = (string) $apiUrlAlbum;
	}

	/**
	 * @return string
	 */
	public function getApiUrlEdit() {
		return $this->_apiUrlEdit;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->_author;
	}

	/**
	 * @param $author
	 *
	 * @return $this
	 */
	public function setAuthor( $author ) {
		$this->_author = is_null( $author ) ? null : strval( $author );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTags() {
		return $this->_tags;
	}

	/**
	 * @param string $tags
	 *
	 * @return self
	 */
	public function setTags( $tags ) {
		if ( is_array( $tags ) || $tags instanceof \ArrayAccess ) {
			$this->_tags = implode( ', ', $tags );
		} else {
			$this->_tags = (string) $tags;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->_content;
	}

	/**
	 * @param string|null $format В каком формате возвращать время (null = timestamp)
	 *
	 * @return int|string
	 */
	public function getDateCreated( $format = null ) {
		$result = null;
		if ( ! empty( $this->_dateCreated ) ) {
			$result = strtotime( $this->_dateCreated );
			if ( ! empty( $format ) ) {
				$result = date( $format, $result );
			}
		}

		return $result;
	}

	/**
	 * @param string|null $format В каком формате возвращать время (null = timestamp)
	 *
	 * @return int|string
	 */
	public function getDateEdited( $format = null ) {
		$result = null;
		if ( ! empty( $this->_dateEdited ) ) {
			$result = strtotime( $this->_dateEdited );
			if ( ! empty( $format ) ) {
				$result = date( $format, $result );
			}
		}

		return $result;
	}

	/**
	 * @param string|null $format В каком формате возвращать время (null = timestamp)
	 *
	 * @return int|string
	 */
	public function getDatePublished( $format = null ) {
		$result = null;
		if ( ! empty( $this->_datePublished ) ) {
			$result = strtotime( $this->_datePublished );
			if ( ! empty( $format ) ) {
				$result = date( $format, $result );
			}
		}

		return $result;
	}

	/**
	 * @param string|null $format В каком формате возвращать время (null = timestamp)
	 *
	 * @return int|string
	 */
	public function getDateUpdated( $format = null ) {
		$result = null;
		if ( ! empty( $this->_dateUpdated ) ) {
			$result = strtotime( $this->_dateUpdated );
			if ( ! empty( $format ) ) {
				$result = date( $format, $result );
			}
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	public function getGeo() {
		$result = null;
		if ( ! empty( $this->_geo ) ) {
			$result = explode( ' ', $this->_geo );
		}

		return $result;
	}

	/**
	 * @param $geo
	 *
	 * @return $this
	 */
	public function setGeo( $geo ) {
		if ( is_array( $geo ) ) {
			$this->_geo = implode( ' ', $geo );
		} elseif ( is_null( $geo ) ) {
			$this->_geo = null;
		} else {
			$this->_geo = (string) $geo;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getId() {
		$result = substr( $this->_atomId, strrpos( $this->_atomId, ':' ) + 1 );

		return $result;
	}

	/**
	 * @return string
	 */
	public function getAtomId() {
		return $this->_atomId;
	}

	/**
	 * @return boolean
	 */
	public function isAdult() {
		return $this->_isAdult;
	}

	/**
	 * @return boolean
	 */
	public function isDisableComments() {
		return $this->_isDisableComments;
	}

	/**
	 * @return boolean
	 */
	public function isHideOriginal() {
		return $this->_isHideOriginal;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * @param string $title
	 *
	 * @return self
	 */
	public function setTitle( $title ) {
		$this->_title = is_null( $title ) ? null : strval( $title );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSummary() {
		return $this->_summary;
	}

	/**
	 * @param string $summary
	 */
	public function setSummary( $summary ) {
		$this->_summary = is_null( $summary ) ? null : strval( $summary );
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->_url;
	}

	/**
	 * @return int
	 */
	public function getAlbumId() {
		$result = $this->_albumId;
		$pos    = strrpos( $this->_albumId, ':' );
		if ( $pos !== false ) {
			$result = substr( $this->_albumId, strrpos( $this->_albumId, ':' ) + 1 );
		}

		return $result;
	}

	/**
	 * @param string $albumId
	 *
	 * @return string
	 */
	public function setAlbumId( $albumId ) {
		$this->_albumId = is_null( $albumId ) ? null : (string) $albumId;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getAlbumAtomId() {
		$result = null;
		$pos    = strrpos( $this->_albumId, ':' );
		if ( $pos !== false ) {
			$result = $this->_albumId;
		}

		return $result;
	}

	/**
	 * Получить ссылку по нику
	 *
	 * @param null|string $nick
	 *
	 * @return null|string
	 */
	public function getImgHref( $nick = null ) {
		$result = null;
		$data   = $this->getImg( $nick );
		if ( ! empty( $data ) ) {
			$result = $data['href'];
		}

		return $result;
	}

	/**
	 * @param null|string $nick
	 *
	 * @return null|array
	 */
	public function getImg( $nick = null ) {
		$nick   = is_null( $nick ) ? self::SIZE_ORIGINAL : $nick;
		$result = null;
		if ( isset( $this->_img[ $nick ] ) ) {
			$result = $this->_img[ $nick ];
		}

		return $result;
	}

	/**
	 * Получить ширину нужного ресайза
	 *
	 * @param null|string $nick
	 *
	 * @return null|int
	 */
	public function getImgWidth( $nick = null ) {
		$result = null;
		$data   = $this->getImg( $nick );
		if ( ! empty( $data ) ) {
			$result = $data['width'];
		}

		return $result;
	}

	/**
	 * Получить высоту нужного ресайза
	 *
	 * @param null|string $nick
	 *
	 * @return null|int
	 */
	public function getImgHeight( $nick = null ) {
		$result = null;
		$data   = $this->getImg( $nick );
		if ( ! empty( $data ) ) {
			$result = $data['height'];
		}

		return $result;
	}

	/**
	 * Получить размер в байтах нужного ресайза
	 *
	 * @param null|string $nick
	 *
	 * @return null|int
	 */
	public function getImgSize( $nick = null ) {
		$result = null;
		$data   = $this->getImg( $nick );
		if ( ! empty( $data ) ) {
			$result = $data['bytesize'];
		}

		return $result;
	}

	/**
	 * @return self
	 * @throws \Yandex\Fotki\Exception\Api\Photo
	 */
	public function load() {
		try {
			$data = $this->_getData( $this->_transport, $this->_apiUrl );
		} catch ( \Yandex\Fotki\Exception\Api $ex ) {
			throw new \Yandex\Fotki\Exception\Api\Photo( $ex->getMessage(), $ex->getCode(), $ex );
		}
		$this->initWithData( $data );

		return $this;
	}

	/**
	 * Инициализируем объект массивом значений
	 *
	 * @param array $entry
	 *
	 * @return self
	 */
	public function initWithData( array $entry ) {
		if ( isset( $entry['id'] ) ) {
			$this->_atomId = (string) $entry['id'];
		}
		if ( isset( $entry['links']['album'] ) ) {
			if ( preg_match( '/\/(\d+)\//', $entry['links']['album'], $matches ) ) {
				$this->_albumId = $matches[1];
			}
		}
		if ( isset( $entry['authors'][0]['name'] ) ) {
			$this->_author = (string) $entry['authors'][0]['name'];
		}
		if ( isset( $entry['author'] ) ) {
			$this->_author = (string) $entry['author'];
		}
		if ( isset( $entry['title'] ) ) {
			$this->setTitle( $entry['title'] );
		}
		if ( isset( $entry['summary'] ) ) {
			$this->setSummary( $entry['summary'] );
		}
		if ( isset( $entry['published'] ) ) {
			$this->_datePublished = (string) $entry['published'];
		}
		if ( isset( $entry['updated'] ) ) {
			$this->_dateUpdated = (string) $entry['updated'];
		}
		if ( isset( $entry['edited'] ) ) {
			$this->_dateEdited = (string) $entry['edited'];
		}
		if ( isset( $entry['links']['self'] ) ) {
			$this->_apiUrl = (string) $entry['links']['self'];
		}
		if ( isset( $entry['links']['alternate'] ) ) {
			$this->_url = (string) $entry['links']['alternate'];
		}
		if ( isset( $entry['links']['album'] ) ) {
			$this->setApiUrlAlbum( $entry['links']['album'] );
		}
		if ( isset( $entry['links']['edit'] ) ) {
			$this->_apiUrlEdit = (string) $entry['links']['edit'];
		}
		if ( isset( $entry['links']['editMedia'] ) ) {
			$this->_apiUrlEditMedia = (string) $entry['links']['editMedia'];
		}
		if ( isset( $entry['access'] ) ) {
			$this->setAccess( $entry['access'] );
		}
		if ( isset( $entry['xxx'] ) ) {
			$this->setIsAdult( $entry['xxx'] );
		}
		if ( isset( $entry['disableComments'] ) ) {
			$this->setIsDisableComments( $entry['disableComments'] );
		}
		if ( isset( $entry['hideOriginal'] ) ) {
			$this->setIsHideOriginal( $entry['hideOriginal'] );
		}
		if ( isset( $entry['tags'] ) ) {
			$this->setTags( $entry['tags'] );
		}
		if ( isset( $entry['img'] ) ) {
			$this->_img = $entry['img'];
		}
		// TODO: определить как получить
//        if (isset($entry['addressBinding']['address'])) {
//            $this->setAddress($entry['addressBinding']['address']);
//        }
		if ( isset( $entry['geo']['coordinates'] ) ) {
			$this->_geo = $entry['geo']['coordinates'];
		}

		return $this;
	}

	/**
	 * @param boolean $isAdult
	 *
	 * @return self
	 */
	public function setIsAdult( $isAdult ) {
		$this->_isAdult = is_null( $isAdult ) ? null : (bool) $isAdult;

		return $this;
	}

	/**
	 * @param boolean $isDisableComments
	 *
	 * @return self
	 */
	public function setIsDisableComments( $isDisableComments ) {
		$this->_isDisableComments = is_null( $isDisableComments ) ? null : (bool) $isDisableComments;

		return $this;
	}

	/**
	 * @param boolean $isHideOriginal
	 *
	 * @return self
	 */
	public function setIsHideOriginal( $isHideOriginal ) {
		$this->_isHideOriginal = is_null( $isHideOriginal ) ? null : (bool) $isHideOriginal;

		return $this;
	}

	/**
	 * @param $data
	 *
	 * @return mixed|null
	 * @throws \Yandex\Fotki\Exception\Api
	 */
	public function upload( $data ) {
		return $this->_postData( $this->_transport, $this->_apiUrl, $data );
	}

	public function update() {
		// @todo:
	}

	/**
	 * Удаление фотографии
	 * @return bool
	 * @throws \Yandex\Fotki\Exception\Api\Photo
	 */
	public function delete() {
		try {
			$this->_deleteData( $this->_transport, $this->_apiUrl );
		} catch ( \Yandex\Fotki\Exception\Api $ex ) {
			throw new \Yandex\Fotki\Exception\Api\Photo( $ex->getMessage(), $ex->getCode(), $ex );
		}
		$this->__destruct();

		return true;
	}

	public function __destruct() {
		foreach ( $this as &$property ) {
			$property = null;
		}
	}

	/**
	 * @return \SimpleXMLElement
	 * @throws \Yandex\Fotki\Exception\InvalidCall
	 */
	public function getAtomEntryForSave() {
		//@formatter:off
		/** @noinspection CheckTagEmptyBody */
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$entryTag = <<<XML
		<entry xmlns	    = "http://www.w3.org/2005/Atom"
			   xmlns:app    = "http://www.w3.org/2007/app"
			   xmlns:f 	    = "yandex:fotki"
			   xmlns:georss = "http://www.georss.org/georss"></entry>
XML;
		//@formatter:on

		$xml = new \SimpleXMLElement( $entryTag, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL );

		$xml->addChild( 'id', $this->_atomId );
		if ( $this->_title !== null ) {
			$xml->addChild( 'title', $this->_title );
		}
		if ( $this->_summary !== null ) {
			$xml->addChild( 'summary', $this->_summary );
		}
		$isAdult = $xml->addChild( 'f:xxx', null, 'yandex:fotki' );
		$isAdult->addAttribute( 'value', $this->_isAdult ? 'true' : 'false' );

		$isHideOriginal = $xml->addChild( 'f:hide_original', null, 'yandex:fotki' );
		$isHideOriginal->addAttribute( 'value', $this->_isHideOriginal ? 'true' : 'false' );

		$isDisableComments = $xml->addChild( 'f:disable_comments', null, 'yandex:fotki' );
		$isDisableComments->addAttribute( 'value', $this->_isDisableComments ? 'true' : 'false' );

		$access = $xml->addChild( 'f:access', null, 'yandex:fotki' );
		$access->addAttribute( 'value', $this->_access );

		if ( $this->_albumId !== null ) {
			if ( ! $this->_author ) {
				throw new InvalidCall( "'Author' parameter must be set to create the parent album link" );
			}

			$href = sprintf( 'http://api-fotki.yandex.ru/api/users/%s/album/%s/', $this->_author, $this->_albumId );

			$link = $xml->addChild( 'link' );
			$link->addAttribute( 'href', $href );
			$link->addAttribute( 'rel', 'album' );
		}

		if ( $this->_geo ) {
			$geo = $xml->addChild( 'georss:point', $this->_geo, 'http://www.georss.org/georss' );
			$geo->addAttribute( 'f:maptyype', 'schema', 'yandex:fotki' );
			$geo->addAttribute( 'f:zoomlevel', 7, 'yandex:fotki' );
		}
		if ( $this->_tags ) {
			if ( ! $this->_author ) {
				throw new InvalidCall( "'Author' parameter must be set to create the tags" );
			}

			$tags = array_map( 'trim', explode( ',', $this->_tags ) );
			foreach ( $tags as $tag ) {
				$scheme = sprintf( 'http://api-fotki.yandex.ru/api/users/%s/tags/', $this->_author );

				$category = $xml->addChild( 'category', null );
				$category->addAttribute( 'term', $tag );
				$category->addAttribute( 'scheme', $scheme );
			}
		}

		//todo tags
		return $xml;
	}
}
