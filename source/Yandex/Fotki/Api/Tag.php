<?php
namespace Yandex\Fotki\Api;

/**
 * Class Tag
 * @package Yandex\Fotki\Api
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see http://api.yandex.ru/fotki/doc/operations-ref/get-tag.xml
 */
class Tag extends \Yandex\Fotki\ApiAbstract
{
    /**
     * @var \Yandex\Fotki\Transport
     */
    protected $_transport;
    /**
     * @var string
     */
    protected $_apiUrl;
    /**
     * @var string Идентификатор Atom Entry тега
     */
    protected $_atomId;
    /**
     * @var string Название тега
     */
    protected $_title;
    /**
     * @var string Время последнего значимого с точки зрения системы изменения тега
     */
    protected $_dateUpdated;
    /**
     * @var string Ссылка для редактирования ресурса тега
     */
    protected $_apiUrlEdit;
    /**
     * @var string Ссылка на коллекцию фотографий тега
     */
    protected $_apiUrlPhotos;
    /**
     * @var string Ссылка на веб-страницу тега в интерфейсе Яндекс.Фоток
     */
    protected $_apiUrlAlternate;
    /**
     * @var int Количество фотографий тега
     */
    protected $_imageCount;
    /**
     * @var string Автор тега
     */
    protected $_author;

    /**
     * @param \Yandex\Fotki\Transport $transport
     * @param string $apiUrl
     */
    public function __construct(\Yandex\Fotki\Transport $transport, $apiUrl)
    {
        $this->_transport = $transport;
        $this->_apiUrl = (string)$apiUrl;
    }

    /**
     * Коллекция фотографий тега
     * @return null|\Yandex\Fotki\Api\PhotosCollection
     */
    public function getPhotosCollection()
    {
        $result = null;
        if (!empty($this->_apiUrlPhotos)) {
            $result = new \Yandex\Fotki\Api\PhotosCollection($this->_transport, $this->_apiUrlPhotos);
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getId()
    {
        $result = sprintf('%u', crc32($this->_atomId));
        return $result;
    }

    /**
     * @return string
     */
    public function getAtomId()
    {
        return $this->_atomId;
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
    public function getImageCount()
    {
        return $this->_imageCount;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @return self
     * @throws \Yandex\Fotki\Exception\Api\Tag
     */
    public function load()
    {
        try {
            $data = $this->_getData($this->_transport, $this->_apiUrl);
        } catch (\Yandex\Fotki\Exception\Api $ex) {
            throw new \Yandex\Fotki\Exception\Api\Tag($ex->getMessage(), $ex->getCode(), $ex);
        }
        $this->initWithData($data);
        return $this;
    }

    /**
     * Инициализируем объект массивом значений
     * @param array $entry
     * @return self
     */
    public function initWithData(array $entry)
    {
        if (isset($entry['id'])) {
            $this->_atomId = (string)$entry['id'];
        }
        if (isset($entry['title'])) {
            $this->_title = (string)$entry['title'];
        }
        if (isset($entry['updated'])) {
            $this->_dateUpdated = (string)$entry['updated'];
        }
        if (isset($entry['links']['edit'])) {
            $this->_apiUrlEdit = (string)$entry['links']['edit'];
        }
        if (isset($entry['links']['photos'])) {
            $this->_apiUrlPhotos = (string)$entry['links']['photos'];
        }
        if (isset($entry['links']['alternate'])) {
            $this->_apiUrlAlternate = (string)$entry['links']['alternate'];
        }
        if (isset($entry['imageCount'])) {
            $this->_imageCount = (int)$entry['imageCount'];
        }
        if (isset($entry['authors'][0]['name'])) {
            $this->_author = (string)$entry['authors'][0]['name'];
        }
        if (isset($entry['author'])) {
            $this->_author = (string)$entry['author'];
        }
        return $this;
    }
}
