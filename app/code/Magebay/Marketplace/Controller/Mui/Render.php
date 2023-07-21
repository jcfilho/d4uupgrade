<?php
/**
 * @Author      : haunv
**/
namespace Magebay\Marketplace\Controller\Mui;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Controller\UiActionInterface;

/**
 * Class Render
 */
class Render extends \Magento\Framework\App\Action\Action implements UiActionInterface
{
    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     */
    public function __construct(Context $context, UiComponentFactory $factory)
    {
        parent::__construct($context);
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
        $this->prepareComponent($component);
        $this->_response->appendBody((string) $component->render());
    }
    /**
     * Getting name
     *
     * @return mixed
     */
    protected function getName()
    {
        return $this->_request->getParam('name');
    }

    /**
     * Getting component
     *
     * @return mixed
     */
    protected function getComponent()
    {
        return $this->_request->getParam('component');
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
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }    


    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
