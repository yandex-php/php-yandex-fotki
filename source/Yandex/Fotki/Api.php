<?php
namespace Yandex\Fotki;

/**
 * Class Api
 * @package Yandex\Fotki
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
class Api
{
    /**
     * @var \Yandex\Fotki\Transport
     */
    protected $_transport;
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
     * @deprecated
     * @param $str
     * @return self
     */
    public function auth($str)
    {
        $token = null;
        $password = null;
        // пароль на Яндексе не может быть более 20 символов
        if (strlen($str) <= 20) {
            $this->password($str);
        } else {
            $this->fimp($str);
        }
        return $this;
    }

    /**
     * Авторизация по fimp-токену
     * @deprecated
     * @param string $token Fimp токен
     * @return self
     */
    public function fimp($token)
    {
        trigger_error('\\Yandex\\Fotki\\Api::fimp() is deprecated! Use \\Yandex\\Fotki\\Api::oauth()', E_USER_DEPRECATED);
        $this->_transport->setFimpToken($token);
        return $this;
    }

    /**
     * Авторизация по паролю
     * @deprecated
     * @param string $password
     * @return self
     */
    public function password($password)
    {
        trigger_error('\\Yandex\\Fotki\\Api::password() is deprecated! Use \\Yandex\\Fotki\\Api::oauth()', E_USER_DEPRECATED);
        $auth = new \Yandex\Fotki\Api\FimpAuth($this->_transport, $this->_login, $password, null);
        $this->_transport->setFimpToken($auth->getToken());
        return $this;
    }

    /**
     * Авторизацию по oauth-токену
     * @param string $token OAuth токен
     * @return self
     */
    public function oauth($token)
    {
        $this->_transport->setOAuthToken($token);
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
     * @return \Yandex\Fotki\Api\TagsCollection
     */
    public function getTagsCollection()
    {
        $apiUrl = $this->_serviceDocument->getUrlTagsCollection();
        $tagsCollection = new \Yandex\Fotki\Api\TagsCollection($this->_transport, $apiUrl);
        return $tagsCollection;
    }

    /**
     * @param string $title
     * @return \Yandex\Fotki\Api\Tag
     */
    public function getTag($title)
    {
        $apiUrl = sprintf("http://api-fotki.yandex.ru/api/users/%s/tag/%s/?format=json", $this->_login, trim($title));
        $tag = new \Yandex\Fotki\Api\Tag($this->_transport, $apiUrl);
        return $tag;
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

    /**
     * @param $data
     * @param $albumId
     *
     * @return $this
     * @throws \Yandex\Fotki\Exception\Api\Photo
     */
    public function sendPhoto($data, $albumId)
    {
        if($albumId) {
            $apiUrl = sprintf("http://api-fotki.yandex.ru/api/users/%s/album/%s/photos/?format=json", $this->_login, trim($albumId));
        } else {
            $apiUrl = sprintf("http://api-fotki.yandex.ru/api/users/%s/photos/?format=json", $this->_login);
        }
        $photo = new \Yandex\Fotki\Api\Photo($this->_transport, $apiUrl);
        return $photo->upload($data);
    }
}