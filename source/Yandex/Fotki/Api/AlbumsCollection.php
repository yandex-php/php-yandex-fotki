<?php
namespace Yandex\Fotki\Api;

/**
 * Class AlbumsCollection
 * @package Yandex\Fotki\Api
 * @see http://api.yandex.ru/fotki/doc/operations-ref/albums-collection-get.xml
 * @method \Yandex\Fotki\Api\AlbumsCollection setOrder(\string $order)
 * @method \Yandex\Fotki\Api\AlbumsCollection setLimit(\int $limit)
 */
class AlbumsCollection extends \Yandex\Fotki\Api\AbstractCollection
{
    /**
     * @var string
     */
    protected $_dateUpdated;

    /**
     * @return self
     * @throws \Yandex\Fotki\Exception\Api\AlbumsCollection
     */
    public function load()
    {
        try {
            $data = $this->_getData($this->_transport, $this->_getApiUrlWithParams($this->_apiUrl));
        } catch (\Yandex\Fotki\Exception\Api $ex) {
            throw new \Yandex\Fotki\Exception\Api\AlbumsCollection($ex->getMessage(), $ex->getCode(), $ex);
        }
        $this->_apiUrlNextPage = null;
        if (isset($data['links']['next'])) {
            $this->_apiUrlNextPage = (string)$data['links']['next'];
        }
        foreach ($data['entries'] as $entry) {
            $album = new \Yandex\Fotki\Api\Album($this->_transport, $entry['links']['self']);
            $album->initWithData($entry);
            $this->_data[$album->getId()] = $album;
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
     * @return array|Album[]
     */
    public function getAlbums()
    {
        return $this->_data;
    }
}