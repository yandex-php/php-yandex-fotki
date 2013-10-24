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
     * @var \Yandex\Fotki\Api\Album[] Список загруженных альбомов в коллекции
     */
    protected $_albums = array();

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
        foreach ($data['entries'] as $entry) {
            $album = new \Yandex\Fotki\Api\Album($this->_transport, $entry['links']['self']);
            $this->_albums[] = $album->initWithData($entry);
        }
        return $this;
    }

    /**
     * @return array|Album[]
     */
    public function getAlbums()
    {
        if (!empty($this->_filter)) {
            $func = $this->_filter;
            $result = $func($this->_albums);
        } else {
            $result = $this->_albums;
        }
        return $result;
    }
}