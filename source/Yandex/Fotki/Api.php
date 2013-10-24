<?php
namespace Yandex\Fotki;

class Api
{
    /**
     * @var \Yandex\Fotki\Transport
     */
    protected $_transport;
    /**
     * @var Api\Auth
     */
    protected $_auth;
    /**
     * @var Api\ServiceDocument
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
        if (mb_strlen($str) <= 20) {
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
     * @return null|Api\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return Api\ServiceDocument
     */
    public function getServiceDocument()
    {
        return $this->_serviceDocument;
    }

    /**
     * @return Api\AlbumsCollection
     */
    public function getAlbumsCollection()
    {
        $apiUrl = $this->_serviceDocument->getUrlAlbumsCollection();
        $albumsCollection = new \Yandex\Fotki\Api\AlbumsCollection($this->_transport, $apiUrl);
        return $albumsCollection;
    }
}