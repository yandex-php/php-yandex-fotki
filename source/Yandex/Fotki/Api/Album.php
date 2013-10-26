<?php
namespace Yandex\Fotki\Api;

/**
 * Class AlbumsCollection
 * @package Yandex\Fotki\Api
 * @see http://api.yandex.ru/fotki/doc/operations-ref/album-get.xml
 */
class Album extends \Yandex\Fotki\Api\AbstractCollection
{
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
    protected $_id;
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
     * @return string
     */
    public function getApiUrlParent()
    {
        return $this->_apiUrlParent;
    }

    /**
     * @param string $apiUrlParent
     * @return self
     */
    public function setApiUrlParent($apiUrlParent)
    {
        $this->_apiUrlParent = (string)$apiUrlParent;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->_apiUrl;
    }

    /**
     * @return string
     */
    public function getApiUrlCover()
    {
        return $this->_apiUrlCover;
    }

    /**
     * @return string
     */
    public function getApiUrlEdit()
    {
        return $this->_apiUrlEdit;
    }

    /**
     * @return string
     */
    public function getApiUrlPhotos()
    {
        return $this->_apiUrlPhotos;
    }

    /**
     * @return string
     */
    public function getApiUrlYmapsml()
    {
        return $this->_apiUrlYmapsml;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->_author;
    }

    /**
     * @param string|null $format В каком формате возвращать время (null = timestamp)
     * @return int|string
     */
    public function getDateEdited($format = null)
    {
        $result = null;
        if (!empty($this->_dateEdited)) {
            $result = strtotime($this->_dateEdited);
            if (!empty($format)) {
                $result = date($format, $result);
            }
        }
        return $result;
    }

    /**
     * @param string|null $format В каком формате возвращать время (null = timestamp)
     * @return int|string
     */
    public function getDatePublished($format = null)
    {
        $result = null;
        if (!empty($this->_datePublished)) {
            $result = strtotime($this->_datePublished);
            if (!empty($format)) {
                $result = date($format, $result);
            }
        }
        return $result;
    }

    /**
     * @param string|null $format В каком формате возвращать время (null = timestamp)
     * @return int|string
     */
    public function getDateUpdated($format = null)
    {
        $result = null;
        if (!empty($this->_dateUpdated)) {
            $result = strtotime($this->_dateUpdated);
            if (!empty($format)) {
                $result = date($format, $result);
            }
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getId()
    {
        $result = substr($this->_id, strrpos($this->_id, ':') + 1);
        return $result;
    }

    /**
     * @return string
     */
    public function getAtomId()
    {
        return $this->_id;
    }

    /**
     * @return int
     */
    public function getImageCount()
    {
        return $this->_imageCount;
    }

    /**
     * @return boolean
     */
    public function isProtected()
    {
        return $this->_isProtected;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->_parentId;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->_summary;
    }

    /**
     * @param string $summary
     * @return self
     */
    public function setSummary($summary)
    {
        $this->_summary = (string)$summary;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->_title = (string)$title;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @return self
     * @throws \Yandex\Fotki\Exception\Api\Album
     */
    public function load()
    {
        try {
            $data = $this->_getData($this->_transport, $this->_apiUrl);
        } catch (\Yandex\Fotki\Exception\Api $ex) {
            throw new \Yandex\Fotki\Exception\Api\Album($ex->getMessage(), $ex->getCode(), $ex);
        }
        $this->initWithData($data);
        return $this;
    }

    /**
     * @param null|int $count
     * @throws \Yandex\Fotki\Exception\Api\Album
     * @return \Yandex\Fotki\Api\Photo[]
     */
    public function getPhotos($count = null)
    {
        $result = array();
        if (!empty($this->_apiUrlPhotos)) {
            if (!empty($count)) {
                $this->setLimit($count);
            }
            $url = $this->_getApiUrlWithParams($this->_apiUrlPhotos);
            try {
                $data = $this->_getData($this->_transport, $url);
            } catch (\Yandex\Fotki\Exception\Api $ex) {
                throw new \Yandex\Fotki\Exception\Api\Album($ex->getMessage(), $ex->getCode(), $ex);
            }
            if (isset($data['entries'])) {
                foreach ($data['entries'] as $photoData) {
                    $photo = new \Yandex\Fotki\Api\Photo($this->_transport);
                    $photo->initWithData($photoData)
                        ->setApiUrlAlbum($this->_apiUrl);
                    $this->_data[$photo->getId()] = $photo;
                }
            }
            $result = $this->_data;
        }
        return $result;
    }

    /**
     * Инициализируем объект массивом значений
     * @param array $entry
     * @return self
     */
    public function initWithData(array $entry)
    {
        if (isset($entry['links']['self'])) {
            $this->_apiUrl = (string)$entry['links']['self'];
        }
        if (isset($entry['id'])) {
            $this->_id = (string)$entry['id'];
        }
        if (isset($entry['links']['album'])) {
            if (preg_match('/\/(\d+)\//', $entry['links']['album'], $matches)) {
                $this->_parentId = (string)$matches[1];
            }
        }
        if (isset($entry['authors'][0]['name'])) {
            $this->_author = (string)$entry['authors'][0]['name'];
        }
        if (isset($entry['author'])) {
            $this->_author = (string)$entry['author'];
        }
        if (isset($entry['title'])) {
            $this->setTitle($entry['title']);
        }
        if (isset($entry['summary'])) {
            $this->setSummary($entry['summary']);
        }
        if (isset($entry['imageCount'])) {
            $this->_imageCount = (int)$entry['imageCount'];
        }
        if (isset($entry['published'])) {
            $this->_datePublished = (string)$entry['published'];
        }
        if (isset($entry['updated'])) {
            $this->_dateUpdated = (string)$entry['updated'];
        }
        if (isset($entry['edited'])) {
            $this->_dateEdited = (string)$entry['edited'];
        }
        if (isset($entry['links']['alternate'])) {
            $this->_url = (string)$entry['links']['alternate'];
        }
        if (isset($entry['links']['album'])) {
            $this->setApiUrlParent($entry['links']['album']);
        }
        if (isset($entry['links']['photos'])) {
            $this->_apiUrlPhotos = (string)$entry['links']['photos'];
        }
        if (isset($entry['links']['edit'])) {
            $this->_apiUrlEdit = (string)$entry['links']['edit'];
        }
        if (isset($entry['links']['ymapsml'])) {
            $this->_apiUrlYmapsml = (string)$entry['links']['ymapsml'];
        }
        if (isset($entry['links']['cover'])) {
            $this->_apiUrlCover = (string)$entry['links']['cover'];
        }
        return $this;
    }
}
