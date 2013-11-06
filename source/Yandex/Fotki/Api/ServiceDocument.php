<?php
namespace Yandex\Fotki\Api;

/**
 * Class ServiceDocument
 * @package Yandex\Fotki\Api
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see http://api.yandex.ru/fotki/doc/operations-ref/service-document-get.xml
 */
class ServiceDocument extends \Yandex\Fotki\ApiAbstract
{
    /**
     * @var string Login пользователя
     */
    protected $_login;
    /**
     * @var string
     */
    protected $_apiUrl;
    /**
     * @var string Ссылка на коллекцию всех фото пользователя
     */
    protected $_urlPhotosCollection;
    /**
     * @var string Ссылка на коллекцию альбомов пользователя
     */
    protected $_urlAlbumsCollection;
    /**
     * @var string Ссылка на коллекцию тэгов пользователя
     */
    protected $_urlTagsCollection;

    /**
     * @param \Yandex\Fotki\Transport $transport
     * @param string $login
     * @return \Yandex\Fotki\Api\ServiceDocument
     */
    public function __construct(\Yandex\Fotki\Transport $transport, $login)
    {
        $this->_login = $login;
        $this->_apiUrl = sprintf('http://api-fotki.yandex.ru/api/users/%s/', $login);
        $this->_transport = $transport;
    }

    /**
     * @return self
     * @throws \Yandex\Fotki\Exception\Api\ServiceDocument
     */
    public function load()
    {
        try {
            $data = $this->_getData($this->_transport, $this->_apiUrl);
        } catch (\Yandex\Fotki\Exception\Api $ex) {
            throw new \Yandex\Fotki\Exception\Api\ServiceDocument($ex->getMessage(), $ex->getCode(), $ex);
        }
        $this->_urlPhotosCollection = $data['collections']['photo-list']['href'];
        $this->_urlAlbumsCollection = $data['collections']['album-list']['href'];
        $this->_urlTagsCollection = $data['collections']['tag-list']['href'];
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlAlbumsCollection()
    {
        $result = $this->_urlAlbumsCollection;
        if (empty($result)) {
            $result = sprintf("http://api-fotki.yandex.ru/api/users/%s/albums/", $this->_login);
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getUrlPhotosCollection()
    {
        $result = $this->_urlPhotosCollection;
        if (empty($result)) {
            $result = sprintf("http://api-fotki.yandex.ru/api/users/%s/photos/", $this->_login);
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getUrlTagsCollection()
    {
        $result = $this->_urlTagsCollection;
        if (empty($result)) {
            $result = sprintf("http://api-fotki.yandex.ru/api/users/%s/tags/", $this->_login);
        }
        return $result;
    }
}