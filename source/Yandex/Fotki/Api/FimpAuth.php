<?php
namespace Yandex\Fotki\Api;

/**
 * Class FimpAuth
 * @package Yandex\Fotki\Api
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @see http://api.yandex.ru/fotki/doc/concepts/fimptoken.xml
 */
class FimpAuth extends \Yandex\Fotki\Api\AbstractAuth
{
    /**
     * @var string
     */
    protected $_credentials;
    /**
     * @var string
     */
    protected $_rsaKey;
    /**
     * @var string
     */
    protected $_requestId;

    /**
     * @param \Yandex\Fotki\Transport $transport
     * @param string|null $login
     * @param string|null $password
     * @param null $token
     * @throws \Yandex\Fotki\Exception\Api\Auth
     * @return \Yandex\Fotki\Api\FimpAuth
     */
    public function __construct(\Yandex\Fotki\Transport $transport, $login, $password, $token = null)
    {
        $this->_transport = $transport;
        $this->_login = $login;
        if (!empty($token)) {
            $this->_token = trim($token);
        } elseif (!empty($login) && !empty($password)) {
            $this->_loadRsaKey($transport, 'http://auth.mobile.yandex.ru/yamrsa/key/');
            $credentials = sprintf("<credentials login='%s' password='%s'/>", $login, $password);
            $this->_credentials = \Yandex\Fotki\Encrypt::encrypt($this->_rsaKey, $credentials);
            $this->load();
        } else {
            throw new \Yandex\Fotki\Exception\Api\Auth("Not specified password or token!");
        }
    }

    /**
     * @return self
     */
    public function load()
    {
        $params = array('request_id' => $this->_requestId,
            'credentials' => $this->_credentials);
        $this->_loadToken($this->_transport, 'http://auth.mobile.yandex.ru/yamrsa/token/', $params);
        return $this;
    }

    protected function _loadRsaKey(\Yandex\Fotki\Transport $transport, $apiUrl)
    {
        $error = true;
        $result = null;
        $tmp = $transport->get($apiUrl);
        if ($tmp['code'] == 200) {
            $result = $tmp['data'];
            $error = false;
        }
        if ($error) {
            $text = strip_tags($tmp['data']);
            $msg = sprintf("Command %s error (%s). %s", get_called_class(), $apiUrl, trim($text));
            if ($tmp['code'] == 502) {
                throw new \Yandex\Fotki\Exception\ServerError(sprintf("Error get RSA key! %s", $msg), $tmp['code']);
            } else {
                throw new \Yandex\Fotki\Exception\Api\Auth(sprintf("Error get RSA key! %s", $msg), $tmp['code']);
            }
        }
        $response = new \SimpleXMLElement($result);
        $this->_requestId = (string)$response->request_id;
        $this->_rsaKey = (string)$response->key;
        return $this;
    }

    protected function _loadToken(\Yandex\Fotki\Transport $transport, $apiUrl, array $data)
    {
        $error = true;
        $result = null;
        $tmp = $transport->post($apiUrl, $data);
        if ($tmp['code'] == 200) {
            $result = $tmp['data'];
            $error = false;
        }
        if ($error) {
            $text = strip_tags($tmp['data']);
            $msg = sprintf("Command %s error (%s). %s", get_called_class(), $apiUrl, trim($text));
            if ($tmp['code'] == 502) {
                throw new \Yandex\Fotki\Exception\ServerError(sprintf("Error get token! %s", $msg), $tmp['code']);
            } else {
                throw new \Yandex\Fotki\Exception\Api\Auth(sprintf("Error get token! %s", $msg), $tmp['code']);
            }
        }
        $response = new \SimpleXMLElement($result);
        $this->_token = (string)$response->token;
        return $this;
    }
}