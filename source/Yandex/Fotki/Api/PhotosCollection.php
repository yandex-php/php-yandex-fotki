<?php
namespace Yandex\Fotki\Api;

/**
 * Class PhotosCollection
 * @package Yandex\Fotki\Api
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see http://api.yandex.ru/fotki/doc/operations-ref/albums-collection-get.xml
 * @method \Yandex\Fotki\Api\PhotosCollection setOrder(\string $order)
 * @method \Yandex\Fotki\Api\PhotosCollection setLimit(\int $limit)
 * @method \Yandex\Fotki\Api\Photo[] getList()
 */
class PhotosCollection extends \Yandex\Fotki\Api\CollectionAbstract
{

    /**
     * @return self
     * @throws \Yandex\Fotki\Exception\Api\PhotosCollection
     */
    public function load()
    {
        try {
            $data = $this->_getData($this->_transport, $this->_getApiUrlWithParams($this->_apiUrl));
        } catch (\Yandex\Fotki\Exception\Api $ex) {
            throw new \Yandex\Fotki\Exception\Api\PhotosCollection($ex->getMessage(), $ex->getCode(), $ex);
        }
        $this->_apiUrlNextPage = null;
        if (isset($data['links']['next'])) {
            $this->_apiUrlNextPage = (string)$data['links']['next'];
        }
        foreach ($data['entries'] as $entry) {
            $photo = new \Yandex\Fotki\Api\Photo($this->_transport, $entry['links']['self']);
            $photo->initWithData($entry);
            $this->_data[$photo->getId()] = $photo;
        }
        return $this;
    }
}