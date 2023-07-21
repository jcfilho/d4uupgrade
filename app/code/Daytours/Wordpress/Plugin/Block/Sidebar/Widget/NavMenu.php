<?php

namespace Daytours\Wordpress\Plugin\Block\Sidebar\Widget;

use \FishPig\WordPress\Block\Context as WPContext;
use \Magento\Framework\DataObject;
use \Daytours\Wordpress\Helper\Data as DataHelperWP;

class NavMenu
{
    public $_factory;
    /**
     * @var DataObject
     */
    private $dataObject;
    /**
     * @var DataHelperWP
     */
    private $dataHelperWP;

    public function __construct(
        WPContext $wpContext,
        DataObject $dataObject,
        DataHelperWP $dataHelperWP
    )
    {
        $this->_factory = $wpContext->getFactory();
        $this->dataObject = $dataObject;
        $this->dataHelperWP = $dataHelperWP;
    }


    public function aroundGetMenu(\FishPig\WordPress\Block\Sidebar\Widget\NavMenu $subject, \Closure $proceed)
    {

        $idMenu = $this->dataHelperWP->getMenuId();
        $menu = $this->_factory->getFactory('Menu')->create()->load($idMenu);
        return $menu;
    }

    /**
     * Recursively uild and return tree html
     *
     * @return string
     */
    public function aroundGetTreeHtml(\FishPig\WordPress\Block\Sidebar\Widget\NavMenu $subject, \Closure $proceed)
    {
        if ($subject->getMenu()) {
            return $this->_getTreeHtmlLevel(0, $subject->getMenu()->getMenuTreeObjects(),$subject);
        }

        return '';
    }

    /**
     * Build and return a single level of tree html and recurse to render sub items
     *
     * @param int $level Menu level (0-index)
     * @param FishPig\WordPress\Model\Menu\Item[] $menuTreeObjects Collection of menu items
     * @return string
     */
    protected function _getTreeHtmlLevel($level, $menuTreeObjects,$subject)
    {
        $indentString = str_repeat("\t", $level);

        $html = '';

        foreach ($menuTreeObjects as $current) {
            $classes = [
                'menu-item',
                'menu-item-' . $current->getId(),
                'menu-item-type-' . $current->getItemType(),
                'menu-item-object-' . $current->getObjectType(),
            ];

            $hasChildren = '';
            if (count($current->getChildrenItems())) {
                $classes[] = 'menu-item-has-children';
                $hasChildren = '<span class="icon-to-slide-mobile"></span>';
            }

            $html .= $indentString . '<li id="menu-item-' . $current->getId() . '" class="' . implode(' ', $classes) . '">' . PHP_EOL;
            $html .= $indentString . "\t" . '<a href="' . $subject->escapeHtml($current->getUrl()) . '" title="' . $subject->escapeHtml($current->getLabel()) . '">';
            $html .= $subject->escapeHtml($current->getLabel()) . '</a>' . $hasChildren . PHP_EOL;

            if (count($current->getChildrenItems())) {
                $html .= $indentString . "\t" . '<ul class="sub-menu">' . PHP_EOL;
                $html .= $this->_getTreeHtmlLevel($level + 1, $current->getChildrenItems()->getItems(),$subject);
                $html .= $indentString . "\t" . '</ul>' . PHP_EOL;
            }

            $html .= $indentString . '</li>' . PHP_EOL;
        }

        return $html;
    }

}