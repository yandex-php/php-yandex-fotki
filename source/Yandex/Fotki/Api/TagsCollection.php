<?php
namespace Yandex\Fotki\Api;

/**
 * Class TagsCollection
 * @package Yandex\Fotki\Api
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see http://api.yandex.ru/fotki/doc/operations-ref/tag-collection-get.xml
 * @method \Yandex\Fotki\Api\TagsCollection setOrder(\string $order)
 * @method \Yandex\Fotki\Api\TagsCollection setLimit(\int $limit)
 * @method \Yandex\Fotki\Api\Tag[] getList()
 */
class TagsCollection extends \Yandex\Fotki\Api\CollectionAbstract
{
    /**
     * @return self
     * @throws \Yandex\Fotki\Exception\Api\TagsCollection
     */
    public function load()
    {
        try {
            $this->_loadCollectionData($this->_apiUrl);
            foreach ($this->_entries as $entry) {
                $tag = new \Yandex\Fotki\Api\Tag($this->_transport, $entry['links']['self']);
                $tag->initWithData($entry);
                $this->_data[$tag->getId()] = $tag;
            }
        } catch (\Yandex\Fotki\Exception\Api $ex) {
            throw new \Yandex\Fotki\Exception\Api\TagsCollection($ex->getMessage(), $ex->getCode(), $ex);
        }
        return $this;
    }
}