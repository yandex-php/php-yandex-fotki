<?php
namespace Yandex\Fotki\Api;

/**
 * Class CollectionAbstract
 * @package Yandex\Fotki\Api
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
abstract class CollectionAbstract extends \Yandex\Fotki\ApiAbstract
{
    /**
     * По времени последнего изменения, от новых к старым
     */
    const BY_LAST_UPDATE_ASC = 'updated';
    /**
     * По времени последнего изменения, от старых к новым
     */
    const BY_LAST_UPDATE_DESC = 'rupdated';
    /**
     * По времени создания, от новых к старым
     */
    const BY_PUBLISH_DATE_ASC = 'published';
    /**
     * По времени создания, от старых к новым
     */
    const BY_PUBLISH_DATE_DESC = 'rpublished';
    /**
     * По времени создания согласно EXIF-данным, от новых к старым (только для фотографий)
     */
    const BY_PUBLISH_DATE_EXIF_ASC = 'created';
    /**
     * По времени создания согласно EXIF-данным, от старых к новым (только для фотографий)
     */
    const BY_PUBLISH_DATE_EXIF_DESC = 'rcreated';
    /**
     * @var string
     */
    protected $_apiUrl;
    /**
     * @var string
     */
    protected $_apiUrlNextPage;
    /**
     * @var string
     */
    protected $_dateUpdated;
    /**
     * @var string Порядок элементов отображения выдачи
     * @see \Yandex\Fotki\Dict\Order
     */
    protected $_order;
    /**
     * @var string Смещении страницы в последовательности
     */
    protected $_offset;
    /**
     * @var int|null Кол-во элементов на странице выдачи (не более 100)
     */
    protected $_limit;
    /**
     * @var array Данные
     */
    protected $_data = array();
    /**
     * @var array Исходные данные
     */
    protected $_entries = array();

    /**
     * @param \Yandex\Fotki\Transport $transport
     * @param string $apiUrl
     * @return self
     */
    public function __construct(\Yandex\Fotki\Transport $transport, $apiUrl)
    {
        $this->_apiUrl = $apiUrl;
        $this->_transport = $transport;
    }

    /**
     * Загрузка следующей страницы выдачи
     * @return $this
     * @throws \Yandex\Fotki\Exception\Api\StopIteration
     */
    public function loadNext()
    {
        $this->resetFilters();
        if (empty($this->_apiUrlNextPage)) {
            throw new \Yandex\Fotki\Exception\Api\StopIteration("Not found next page of collection");
        }
        $this->__construct($this->_transport, $this->_apiUrlNextPage);
        try {
            $this->load();
        } catch (\Yandex\Fotki\Exception\Api\AlbumsCollection $ex) {
            throw new \Yandex\Fotki\Exception\Api\StopIteration($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this;
    }

    /**
     * Загрузить всю коллекцию
     * @param null|int $limitQueries Ограничиваем кол-во запросов к api на получение коллекции
     * @return self
     */
    public function loadAll($limitQueries = null)
    {
        $limitQueries = is_null($limitQueries) ? 20 : $limitQueries;
        $albums[] = $this
            ->load();
        for ($i = 0; $i < $limitQueries; $i++) {
            try {
                $this->loadNext();
            } catch (\Yandex\Fotki\Exception\Api\StopIteration $ex) {
                break;
            }
        }
        return $this;
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
     * @return array
     */
    public function getList()
    {
        $result = $this->_data;
        $limit = (int)$this->_limit;
        if ($limit > 0) {
            $result = array_slice($result, 0, $limit);
        }
        return $result;
    }

    /**
     * @param int $limit
     * @return self
     */
    public function setLimit($limit)
    {
        $this->_limit = is_null($limit) ? null : (int)$limit;
        return $this;
    }

    /**
     * @param string $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

//    /**
//     * @param string $offset
//     * @return self
//     */
//    public function setOffset($offset)
//    {
//        $this->_offset = $offset;
//        return $this;
//    }

    /**
     * Сбрасываем фильтры
     * @return self
     */
    public function resetFilters()
    {
        $this->_order = null;
        $this->_limit = null;
        return $this;
    }

    /**
     * Загружаем информацию по коллекции для дальнейшей обработки
     * @param string $apiUrl
     * @throws \Exception
     * @throws \Yandex\Fotki\Exception
     */
    protected function _loadCollectionData($apiUrl)
    {
        $this->_apiUrlNextPage = null;
        $this->_dateUpdated = null;
        $this->_entries = array();
        try {
            $data = $this->_getData($this->_transport, $this->_getApiUrlWithParams($apiUrl));
            if (isset($data['links']['next'])) {
                $this->_apiUrlNextPage = (string)$data['links']['next'];
            }
            if (isset($data['updated'])) {
                $this->_dateUpdated = (string)$data['updated'];
            }
            if (isset($data['entries']) && is_array($data['entries'])) {
                $this->_entries = $data['entries'];
            }
        } catch (\Yandex\Fotki\Exception $ex) {
            throw $ex;
        }
    }

    protected function _getApiUrlWithParams($url)
    {
        $parts = parse_url($url);
        if (!isset($parts['query'])) {
            $parts['query'] = '';
        }
        if (!empty($this->_order)) {
            $parts['path'] .= $this->_order;
            if (!empty($this->_offset)) {
                $parts['path'] .= (';' . $this->_offset);
            }
            $parts['path'] .= '/';
        }
        $limit = (int)$this->_limit;
        if ($limit > 0) {
            if (!empty($parts['query'])) {
                $parts['query'] .= '&';
            }
            $parts['query'] .= 'limit=' . $limit;
        }
        $result = sprintf("%s://%s%s?%s", $parts['scheme'], $parts['host'], $parts['path'], $parts['query']);
        return $result;
    }
}