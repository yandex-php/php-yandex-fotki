<?php
namespace Yandex\Fotki\Api;

use Yandex\Fotki\Exception\InvalidCall;

/**
 * Class AlbumsCollection
 * @package Yandex\Fotki\Api
 * @author  Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see     http://api.yandex.ru/fotki/doc/operations-ref/album-get.xml
 * @method \Yandex\Fotki\Api\Album setOrder( $order )
 * @method \Yandex\Fotki\Api\Album setLimit( $limit )
 * @method \Yandex\Fotki\Api\Photo[] getList()
 */
class Album extends \Yandex\Fotki\Api\CollectionAbstract {
	/**
	 * @var string Ссылка для редактирования ресурса альбома
	 */
	protected $_apiUrlEdit;
	/**
	 * @var string Ссылка на коллекцию фотографий альбома
	 */
	protected $_apiUrlPhotos;
	/**
	 * @var string Ссылка на ресурс фотографии, являющейся обложкой альбома
	 */
	protected $_apiUrlCover;
	/**
	 * @var string Ссылка на выдачу данных альбома, представленных в формате YmapsML
	 */
	protected $_apiUrlYmapsml;
	/**
	 * @var string Ссылка на родительский альбом
	 */
	protected $_apiUrlParent;
	/**
	 * @var string Идентификатор Atom Entry альбома
	 */
	protected $_atomId;
	/**
	 * @var int Идентификатор родительского альбома
	 */
	protected $_parentId;
	/**
	 * @var string Логин пользователя на Яндекс.Фотках
	 */
	protected $_author;
	/**
	 * @var string Название альбома
	 */
	protected $_title;
	/**
	 * @var string Описание альбома
	 */
	protected $_summary;
	/**
	 * @var int Время создания альбома
	 */
	protected $_datePublished;
	/**
	 * @var int Время последнего редактирования альбома
	 */
	protected $_dateEdited;
	/**
	 * @var int Время последнего значимого с точки зрения системы изменения альбома
	 */
	protected $_dateUpdated;
	/**
	 * @var string Ссылка на веб-страницу альбома в интерфейсе Яндекс.Фоток
	 */
	protected $_url;
	/**
	 * @var bool Флаг защиты альбома паролем
	 */
	protected $_isProtected;
	/**
	 * @var int Количество фотографий в альбоме
	 */
	protected $_imageCount;
	/**
	 * @var string Пароль к альбому
	 */
	protected $_password;

	/**
	 * @return string
	 */
	public function getApiUrlParent() {
		return $this->_apiUrlParent;
	}

	/**
	 * @param string $apiUrlParent
	 *
	 * @return self
	 */
	public function setApiUrlParent( $apiUrlParent ) {
		$this->_apiUrlParent = (string) $apiUrlParent;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiUrl() {
		return $this->_apiUrl;
	}

	/**
	 * @return string
	 */
	public function getApiUrlCover() {
		return $this->_apiUrlCover;
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
	public function getApiUrlPhotos() {
		return $this->_apiUrlPhotos;
	}

	/**
	 * @return string
	 */
	public function getApiUrlYmapsml() {
		return $this->_apiUrlYmapsml;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->_author;
	}

	/**
	 * @param string $author
	 */
	public function setAuthor( $author ) {
		$this->_author = (string) $author;
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
	 * @return int
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
	 * @return int
	 */
	public function getImageCount() {
		return $this->_imageCount;
	}

	/**
	 * @return boolean
	 */
	public function isProtected() {
		return $this->_isProtected;
	}

	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->_parentId;
	}

	/**
	 * @param int|null $parentId
	 *
	 * @return $this
	 */
	public function setParentId( $parentId ) {
		if ( is_null( $parentId ) ) {
			$this->_parentId = null;
		} else {
			$this->_parentId = (int) $parentId;
		}

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
	 *
	 * @return self
	 */
	public function setSummary( $summary ) {
		if ( is_null( $summary ) ) {
			$this->_summary = null;
		} else {
			$this->_summary = (string) $summary;
		}

		return $this;
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
		if ( is_null( $title ) ) {
			$this->_title = null;
		} else {
			$this->_title = (string) $title;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->_url;
	}

	/**
	 * @param string $password
	 *
	 * @return $this
	 */
	public function setPassword( $password ) {
		if ( is_null( $password ) ) {
			$this->_password = null;
		} else {
			$this->_password = (string) $password;
		}

		return $this;
	}

	/**
	 * @return self
	 * @throws \Yandex\Fotki\Exception\Api\Album
	 */
	public function load() {
		try {
			$data = $this->_getData( $this->_transport, $this->_apiUrl );
		} catch ( \Yandex\Fotki\Exception\Api $ex ) {
			throw new \Yandex\Fotki\Exception\Api\Album( $ex->getMessage(), $ex->getCode(), $ex );
		}
		$this->initWithData( $data );
		$this->_loadPhotos();

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
		if ( isset( $entry['links']['self'] ) ) {
			$this->_apiUrl = (string) $entry['links']['self'];
		}
		if ( isset( $entry['id'] ) ) {
			$this->_atomId = (string) $entry['id'];
		}
		if ( isset( $entry['links']['album'] ) ) {
			if ( preg_match( '/\/(\d+)\//', $entry['links']['album'], $matches ) ) {
				$this->_parentId = (string) $matches[1];
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
		if ( isset( $entry['imageCount'] ) ) {
			$this->_imageCount = (int) $entry['imageCount'];
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
		if ( isset( $entry['links']['alternate'] ) ) {
			$this->_url = (string) $entry['links']['alternate'];
		}
		if ( isset( $entry['links']['album'] ) ) {
			$this->setApiUrlParent( $entry['links']['album'] );
		}
		if ( isset( $entry['links']['photos'] ) ) {
			$this->_apiUrlPhotos = (string) $entry['links']['photos'];
		}
		if ( isset( $entry['links']['edit'] ) ) {
			$this->_apiUrlEdit = (string) $entry['links']['edit'];
		}
		if ( isset( $entry['links']['ymapsml'] ) ) {
			$this->_apiUrlYmapsml = (string) $entry['links']['ymapsml'];
		}
		if ( isset( $entry['links']['cover'] ) ) {
			$this->_apiUrlCover = (string) $entry['links']['cover'];
		}

		return $this;
	}

	/**
	 * Загружаем список фотографий
	 * @throws \Yandex\Fotki\Exception\Api\PhotosCollection
	 * @return self
	 */
	protected function _loadPhotos() {
		if ( ! empty( $this->_apiUrlPhotos ) ) {
			try {
				$this->_loadCollectionData( $this->_apiUrlPhotos );
				foreach ( $this->_entries as $entry ) {
					$photo = new \Yandex\Fotki\Api\Photo( $this->_transport );
					$photo->initWithData( $entry )
					      ->setApiUrlAlbum( $this->_apiUrl );
					$this->_data[ $photo->getId() ] = $photo;
				}
			} catch ( \Yandex\Fotki\Exception\Api $ex ) {
				throw new \Yandex\Fotki\Exception\Api\PhotosCollection( $ex->getMessage(), $ex->getCode(), $ex );
			}
		}

		return $this;
	}

	/**
	 * @return \SimpleXMLElement
	 * @throws \Yandex\Fotki\Exception\InvalidCall
	 */
	public function getAtomEntryForSave() {
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$xml = new \SimpleXMLElement( '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:f="yandex:fotki"></entry>',
			LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL );

		if ( $this->_title !== null ) {
			$xml->addChild( 'title', $this->_title );
		}
		if ( $this->_summary !== null ) {
			$xml->addChild( 'summary', $this->_summary );
		}
		if ( $this->_password !== null ) {
			$xml->addChild( 'f:password', $this->_password, 'yandex:fotki' );
		}
		if ( $this->_parentId !== null ) {

			if ( ! $this->_author ) {
				throw new InvalidCall( "'Author' parameter must be set to create the parent album link" );
			}

			$href = sprintf( 'http://api-fotki.yandex.ru/api/users/%s/album/%s/', $this->_author, $this->_parentId );

			$link = $xml->addChild( 'link' );
			$link->addAttribute( 'href', $href );
			$link->addAttribute( 'rel', 'album' );
		}

		return $xml;
	}
}
