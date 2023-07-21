<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 10/05/2018
 * Time: 09:46
 */

namespace Magenest\Customize\Setup;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Cms\Model\BlockFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Page factory.
     *
     * @var PageFactory
     */
    private $pageFactory;


    /**
     * Block factory
     *
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * Init.
     *
     * @param PageFactory $pageFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        BlockFactory $blockFactory
    )
    {
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * Upgrade.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();


        if (version_compare($context->getVersion(), '1.0.2') < 0) {

//          cms 404 page
            $_404_page_content = '<div class="page-404-content content-center">
                <h4>{{trans "Page not found"}}</h4>
                <h2>{{trans "You go very far away"}}</h2>
                <p>{{trans "The page you are looking for doesn’t exists or another error ocurred"}}</p>
                <div class="image">
                    <span>4</span>
                    <img class="404-image" src="{{view url=\'images/404-image.png\'}}"/>
                    <span>4</span>
                </div>
                <p>{{trans "You may find what you wher looking for on our homepge. Continue planing your trip and doing the best activities and tour with Daytours4u"}}</p>
                <a href="{{store url=\'\'}}" class="btn btn-go-home">{{trans "Go to homepage"}}</a>
                </div>';

            $_404_page = $this->pageFactory->create()->load(
                'no-route',
                'identifier'
            );

            if ($_404_page->getId()) {
                $_404_page->setContentHeading('');
                $_404_page->setPageLayout('1column');
                $_404_page->setContent($_404_page_content);
                $_404_page->save();
            }

//            cms banner block
            $_banner_about_us_info = [
                'title' => 'Banner About Us',
                'identifier' => 'banner_about_us',
                'stores' => [0],
                'is_active' => 1,
                'content' => '<div class="banner_top_page" style="background-image: url({{view url=\'images/banner_aboutus.jpg\'}})">
                                <div class="container">
                                    <div class="content_text">
                                        <div class="name">{{trans "Who We Are"}}</div>
                                        <div class="des">{{trans "We are your friendly, professional, and local online travel agency"}}</div>
                                    </div>
                                </div>
                            </div>'
            ];

            $this->blockFactory->create()->setData($_banner_about_us_info)->save();
        }

        $setup->endSetup();
    }
}