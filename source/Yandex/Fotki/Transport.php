<?php
namespace Yandex\Fotki;

use Yandex\Fotki\Exception;

/**
 * Class Transport
 * @package Yandex\Fotki
 * @author  Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 * @method array get( $url, array $params = null )
 * @method array post( $url, array $params = null )
 * @method array put( $url, array $params = null )
 * @method array delete( $url, array $params = null )
 */
class Transport implements \Serializable {
	const METHOD_POST   = 'POST';
	const METHOD_GET    = 'GET';
	const METHOD_PUT    = 'PUT';
	const METHOD_DELETE = 'DELETE';
	/**
	 * @var string
	 */
	protected $_oauthToken;

	/**
	 * @var string
	 */
	protected $_fimpToken;

	public function __construct() {
	}

	public function __call( $method, $arguments ) {
		$result        = null;
		$requestMethod = strtoupper( $method );
		if ( ! in_array( $requestMethod, array( self::METHOD_POST, self::METHOD_GET ) ) ) {
			throw new Exception( sprintf( "Method %s is not supported!", $method ) );
		} else {
			array_unshift( $arguments, $requestMethod );
			$result = call_user_func_array( array( $this, 'request' ), $arguments );
		}

		return $result;
	}

	/**
	 * (PHP 5 >= 5.1.0)<br/>
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @see  \Serializable::serialize()
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize( array( 'token' => $this->_oauthToken ) );
	}

	/**
	 * (PHP 5 >= 5.1.0)<br/>
	 * Constructs the object
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @see  \Serializable::unserialize()
	 *
	 * @param string $serialized The string representation of the object.
	 *
	 * @return void
	 */
	public function unserialize( $serialized ) {
		$serialized = unserialize( $serialized );
		if ( is_array( $serialized ) && isset( $serialized['token'] ) ) {
			$this->_oauthToken = $serialized['token'];
		}
	}

	/**
	 * @return string
	 */
	public function getOAuthToken() {
		return $this->_oauthToken;
	}

	/**
	 * @param string $token OAuth токен
	 *
	 * @return self
	 */
	public function setOAuthToken( $token ) {
		$this->_oauthToken = (string) $token;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFimpToken() {
		return $this->_fimpToken;
	}

	/**
	 * @param string $token Fimp токен
	 *
	 * @return self
	 */
	public function setFimpToken( $token ) {
		$this->_fimpToken = (string) $token;

		return $this;
	}
	
	public function get( $url, array $params = null )     { return $this->request(self::METHOD_GET, $url, $params); }
	public function post( $url, array $params = null )    { return $this->request(self::METHOD_POST, $url, $params); }
	public function put( $url, array $params = null )     { return $this->request(self::METHOD_PUT, $url, $params); }
	public function delete( $url, array $params = null )  { return $this->request(self::METHOD_DELETE, $url, $params); }
 
	public function request( $method, $url, array $params = null ) {
		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$headers   = array();
		$headers[] = 'Accept: application/json';
		switch ( $method ) {
			case self::METHOD_POST:
				curl_setopt( $curl, CURLOPT_POST, 1 );
				if ( is_array( $params ) ) {
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $params );
				}
				break;
			default:
				curl_setopt( $curl, CURLOPT_HTTPGET, 1 );
				break;
		}
		if ( ! empty( $this->_oauthToken ) ) {
			$headers[] = 'Authorization: OAuth ' . $this->_oauthToken;
		} elseif ( ! empty( $this->_fimpToken ) ) {
			$headers[] = 'Authorization: FimpToken realm="fotki.yandex.ru", token="' . $this->_fimpToken . '"';
		}
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		$data = curl_exec( $curl );
		$code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		if ( curl_errno( $curl ) ) {
            $curl_error = curl_error($curl);
            curl_close($curl);
            throw new Exception\CurlError($curl_error);
		}
		curl_close( $curl );

		$result = array(
			'code' => $code,
			'data' => $data
		);

		return $result;
	}
}
