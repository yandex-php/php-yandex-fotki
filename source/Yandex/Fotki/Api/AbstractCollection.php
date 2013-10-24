<?php
namespace Yandex\Fotki\Api;

abstract class AbstractCollection extends \Yandex\Fotki\ApiAbstract
{
    /**
     * По времени последнего изменения, от новых к старым
     */
    const BY_LAST_UPDATE_ASC = 'updated';
    /**
     * По времени последнего изменения, от старых к новым
     */
    const BY_LAST_UPDATE_DESC = 'rupdated';
    /**
     * По времени создания, от новых к старым
     */
    const BY_PUBLISH_DATE_ASC = 'published';
    /**
     * По времени создания, от старых к новым
     */
    const BY_PUBLISH_DATE_DESC = 'rpublished';
    /**
     * По времени создания согласно EXIF-данным, от новых к старым (только для фотографий)
     */
    const BY_PUBLISH_DATE_EXIF_ASC = 'created';
    /**
     * По времени создания согласно EXIF-данным, от старых к новым (только для фотографий)
     */
    const BY_PUBLISH_DATE_EXIF_DESC = 'rcreated';
    /**
     * @var string
     */
    protected $_apiUrl;
    /**
     * @var string
     */
    protected $_apiUrlNextPage;
    /**
     * @var string Порядок элементов отображения выдачи
     * @see \Yandex\Fotki\Dict\Order
     */
    protected $_order = self::BY_LAST_UPDATE_ASC;
    /**
     * @var string Смещении страницы в последовательности
     */
    protected $_offset;
    /**
     * @var int Кол-во элементов на странице выдачи (не более 100)
     */
    protected $_limit;
    /**
     * @var \callable Фильтр
     */
    protected $_filter;
    /**
     * @var array Данные
     */
    protected $_data = array();

    /**
     * @param \Yandex\Fotki\Transport $transport
     * @param string $apiUrl
     * @return self
     */
    public function __construct(\Yandex\Fotki\Transport $transport, $apiUrl)
    {
        $this->_apiUrl = $apiUrl;
        $this->_transport = $transport;
    }

    public function next()
    {
        $this->__construct($this->_transport, $this->_apiUrlNextPage);
        return $this->load();
    }

    /**
     * @param int $limit
     * @return self
     */
    public function setLimit($limit)
    {
        $this->_limit = (int)$limit;
        return $this;
    }

//    /**
//     * @param string $offset
//     * @return self
//     */
//    public function setOffset($offset)
//    {
//        $this->_offset = $offset;
//        return $this;
//    }

    /**
     * @param string $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * @param \callable $func
     * @return self
     */
    public function setFilter(\Closure $func = null)
    {
        $this->_filter = $func;
        return $this;
    }

    protected function _getApiUrlWithParams($url)
    {
        $result = $url;
        if (!empty($this->_order)) {
            $result .= $this->_order;
            if (!empty($this->_offset)) {
                $result .= (';' . $this->_offset);
            }
            $result .= '/';
        }
        if ($this->_limit > 0) {
            $result .= '?limit=' . $this->_limit;
        }
        return $result;
    }
}