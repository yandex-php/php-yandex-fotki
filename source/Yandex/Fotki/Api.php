<?php
namespace Yandex\Fotki;

class Api
{
    /**
     * @var \Yandex\Fotki\Transport
     */
    protected $_transport;
    /**
     * @var \Yandex\Fotki\Api\Auth
     */
    protected $_auth;
    /**
     * @var \Yandex\Fotki\Api\ServiceDocument
     */
    protected $_serviceDocument;
    /**
     * @var string
     */
    protected $_login;

    /**
     * @param string $login
     */
    public function __construct($login)
    {
        $this->_login = (string)$login;
        $this->_transport = new \Yandex\Fotki\Transport();
        $this->_serviceDocument = new \Yandex\Fotki\Api\ServiceDocument($this->_transport, $this->_login);
    }

    /**
     * @param string $str Token или пароль пользователя
     * @return self
     */
    public function auth($str)
    {
        $token = null;
        $password = null;
        // пароль на Яндексе не может быть более 20 символов
        if (strlen($str) <= 20) {
            $password = $str;
        } else {
            $token = $str;
        }
        $this->_auth = new \Yandex\Fotki\Api\Auth($this->_transport, $this->_login, $password, $token);
        $this->_transport->setToken($this->_auth->getToken());
        return $this;
    }

    /**
     * Загрузка сервисного документа
     * @return self
     */
    public function loadServiceDocument()
    {
        $this->_serviceDocument->load();
        return $this;
    }

    /**
     * @return null|\Yandex\Fotki\Api\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Yandex\Fotki\Api\ServiceDocument
     */
    public function getServiceDocument()
    {
        return $this->_serviceDocument;
    }

    /**
     * @return \Yandex\Fotki\Api\AlbumsCollection
     */
    public function getAlbumsCollection()
    {
        $apiUrl = $this->_serviceDocument->getUrlAlbumsCollection();
        $albumsCollection = new \Yandex\Fotki\Api\AlbumsCollection($this->_transport, $apiUrl);
        return $albumsCollection;
    }

    /**
     * @return \Yandex\Fotki\Api\PhotosCollection
     */
    public function getPhotosCollection()
    {
        $apiUrl = $this->_serviceDocument->getUrlPhotosCollection();
        $photosCollection = new \Yandex\Fotki\Api\PhotosCollection($this->_transport, $apiUrl);
        return $photosCollection;
    }

    /**
     * @param string|int $id
     * @return \Yandex\Fotki\Api\Album
     */
    public function getAlbum($id)
    {
        $apiUrl = sprintf("http://api-fotki.yandex.ru/api/users/%s/album/%s/?format=json", $this->_login, trim($id));
        $album = new \Yandex\Fotki\Api\Album($this->_transport, $apiUrl);
        return $album;
    }
}