<?php
namespace Magebay\Messages\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Reply model
 *
 * @method \Magebay\Messages\Model\ResourceModel\Reply _getResource()
 * @method \Magebay\Messages\Model\ResourceModel\Reply getResource()
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method string getMetaKeywords()
 * @method $this setMetaKeywords(string $value)
 * @method string getMetaDescription()
 * @method $this setMetaDescription(string $value)
 * @method string getIdentifier()
 * @method $this setIdentifier(string $value)
 */
class Reply extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Reply's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magebay_magebaynew_reply';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'magebaynew_reply';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
	
	protected $_replyCollection;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
	
    public function __construct(
		\Magebay\Messages\Model\ReplyFactory $replyCollection,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_url = $url;
        $this->_replyCollection = $replyCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    } 
	
	 /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebay\Messages\Model\ResourceModel\Reply');
    }
	
	public function getAvailableReply()
    {
        $option[] = [
            'value' => '',
            'label' => __('-------- Please Select Reply --------'),
        ]; 
		$sliderCollection = $this->_replyCollection->create();
        $sliderCollection = $sliderCollection->getCollection();
         foreach ($sliderCollection as $slider) {
            $option[] = [
               'value' => $slider->getId(),
               'label' => $slider->getTitle(),
            ];
        } 
        return $option;
    }

    /**
     * Retrieve model title
     * @param  boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Reply' : 'Categories';
    }

    /**
     * Retrieve true if reply is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getStatus() == self::STATUS_ENABLED);
    }

    /**
     * Retrieve available reply statuses
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }
}