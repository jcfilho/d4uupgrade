<?php
namespace Magebay\Marketplace\Controller\Product;
use Magento\Framework\Controller\Result;
use Magento\Framework\View\Result\PageFactory;

abstract class Attribute extends \Magebay\Marketplace\Controller\Product\Account{

    /**
     * @var string
     */
    protected $_entityTypeId;
	
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;	
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Registry $coreRegistry,
		PageFactory $resultPageFactory
	){		
        $this->_coreRegistry = $coreRegistry;
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context, $customerSession);
	}
	
    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
	 
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->_objectManager->create('Magento\Catalog\Model\Product\Url')->formatUrlKey($label)
            ),
            0,
            30
        );
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(md5(time()), 0, 8));
        }
        return $code;
    }

    /**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_entityTypeId = $this->_objectManager->create(
            'Magento\Eav\Model\Entity'
        )->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getTypeId();
		
        return parent::dispatch($request);
    }	
}