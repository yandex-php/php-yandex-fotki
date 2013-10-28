<?php
namespace Yandex\Fotki\Api;

/**
 * Class FimpAuth
 * @package Yandex\Fotki\Api
 * @see http://api.yandex.ru/fotki/doc/overview/authorization.xml
 */
abstract class AbstractAuth extends \Yandex\Fotki\ApiAbstract
{
    /**
     * @var string
     */
    protected $_token;
    /**
     * @var string
     */
    protected $_login;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }
}