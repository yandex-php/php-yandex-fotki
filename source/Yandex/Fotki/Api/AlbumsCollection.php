<?php
namespace Yandex\Fotki\Api;

/**
 * Class AlbumsCollection
 * @package Yandex\Fotki\Api
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see http://api.yandex.ru/fotki/doc/operations-ref/albums-collection-get.xml
 * @method \Yandex\Fotki\Api\AlbumsCollection setOrder( $order )
 * @method \Yandex\Fotki\Api\AlbumsCollection setLimit( $limit )
 * @method \Yandex\Fotki\Api\Album[] getList()
 */
class AlbumsCollection extends \Yandex\Fotki\Api\CollectionAbstract
{

	const MAX_LIMIT = 100;

    /**
     * @return self
     * @throws \Yandex\Fotki\Exception\Api\AlbumsCollection
     */
    public function load()
    {
        try {
            $this->_loadCollectionData($this->_apiUrl);
            foreach ($this->_entries as $entry) {
                $album = new \Yandex\Fotki\Api\Album($this->_transport, $entry['links']['self']);
                $album->initWithData($entry);
                $this->_data[$album->getId()] = $album;
            }
        } catch (\Yandex\Fotki\Exception\Api $ex) {
            throw new \Yandex\Fotki\Exception\Api\AlbumsCollection($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this;
    }
}