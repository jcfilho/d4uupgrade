<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Mui;

use Magento\Ui\Controller\UiActionInterface;

/**
 * Class Render
 */
class Render extends \Magebay\Marketplace\Controller\Product\Account implements UiActionInterface
{
    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     */
    public function __construct(
		\Magento\Framework\App\Action\Context $context, 
		\Magento\Customer\Model\Session $customerSession, 
		\Magento\Framework\View\Element\UiComponentFactory $factory
	)
    {
        parent::__construct($context, $customerSession);
        $this->factory = $factory;
    }
	/**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $component = $this->factory->create($this->_request->getParam('namespace'));
        $this->__prepareComponent($component);
        $this->_response->appendBody((string) $component->render());
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function executeAjaxRequest()
    {
        $this->execute();
    }


    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function __prepareComponent(\Magento\Framework\View\Element\UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->__prepareComponent($child);
        }
        $component->prepare();
    }
}
