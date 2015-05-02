<?php
namespace Yandex\Fotki;

/**
 * Class ApiAbstract
 * @package Yandex\Fotki
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
abstract class ApiAbstract implements \Serializable
{
    /**
     * @var \Yandex\Fotki\Transport
     */
    protected $_transport;

    /**
     * @return self
     */
    abstract public function load();

    /**
     * (PHP 5 >= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @see \Serializable::serialize()
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        $data = array();
        $reflection = new \ReflectionClass(get_called_class());
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $key = $property->getName();
            $value = $property->getValue($this);
            $data[$key] = $value;
        }
        return serialize($data);
    }

    /**
     * (PHP 5 >= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @see \Serializable::unserialize()
     * @param string $serialized The string representation of the object.
     * @return void
     */
    public function unserialize($serialized)
    {
        $serialized = unserialize($serialized);
        if (is_array($serialized)) {
            $reflection = new \ReflectionClass(get_called_class());
            foreach ($serialized as $key => $value) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($this, $value);
                if ($property->isPrivate() || $property->isProtected()) {
                    $property->setAccessible(false);
                }
            }
        }
    }

    /**
     * Загрузка данных из api
     * @param Transport $transport
     * @param string $apiUrl
     * @return mixed|null
     * @throws Exception\Api
     */
    protected function _getData(\Yandex\Fotki\Transport $transport, $apiUrl)
    {
        $error = true;
        $result = null;
        $tmp = $transport->get($apiUrl);
        if ($tmp['code'] == 200) {
            $result = json_decode($tmp['data'], true);
            if (!is_null($result)) {
                $error = false;
            }
        }
        if ($error) {
            $text = strip_tags($tmp['data']);
            $msg = sprintf("Command %s error (%s). %s", get_called_class(), $apiUrl, trim($text));
            throw new \Yandex\Fotki\Exception\Api($msg, $tmp['code']);
        }
        return $result;
    }

    /**
     * Удаление данных из api
     * @param Transport $transport
     * @param string $apiUrl
     * @return mixed|null
     * @throws Exception\Api
     */
    protected function _deleteData(\Yandex\Fotki\Transport $transport, $apiUrl)
    {
        $error = true;
        $result = null;
        $tmp = $transport->delete($apiUrl);
        if ($tmp['code'] == 200) {
            $result = json_decode($tmp['data'], true);
            if (!is_null($result)) {
                $error = false;
            }
        }
        if ($error) {
            $text = strip_tags($tmp['data']);
            $msg = sprintf("Command %s error (%s). %s", get_called_class(), $apiUrl, trim($text));
            throw new \Yandex\Fotki\Exception\Api($msg, $tmp['code']);
        }
        return $result;
    }

    /**
     * Загрузка фото
     * @param Transport $transport
     * @param           $apiUrl
     * @param array     $data
     *
     * @return mixed|null
     * @throws Exception\Api
     */
    protected function _postData(\Yandex\Fotki\Transport $transport, $apiUrl, array $data)
    {
        $error = true;
        $result = null;
        $tmp = $transport->post($apiUrl, $data);
        if ($tmp['code'] == 200 || $tmp['code'] == 201) {
            $result = json_decode($tmp['data'], true);
            if (!is_null($result)) {
                $error = false;
            }
        }
        if ($error) {
            $text = strip_tags($tmp['data']);
            $msg = sprintf("Command %s error (%s). %s", get_called_class(), $apiUrl, trim($text));
            throw new \Yandex\Fotki\Exception\Api($msg, $tmp['code']);
        }
        return $result;
    }
}