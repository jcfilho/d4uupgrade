<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 6/1/18
 * Time: 3:03 PM
 */

namespace Daytours\Wordpress\Plugin\Block\Sidebar\Widget;


use Zend_Db_Select;

class Cloud
{
    public function aroundGetTags(\FishPig\WordPress\Block\Sidebar\Widget\Cloud $subject, \Closure $proceed)
    {
        if ($subject->hasTags()) {
            return $subject->_getData('tags');
        }

        $subject->setTags(false);

        $tags = $subject->_factory->getFactory('Term')->create()->getCollection()->addCloudFilter('post_tag');
        $tags->getSelect()->reset(Zend_Db_Select::ORDER);
        $tags->addOrderByItemCount();
        $tags->setPageSize(5);

        if (count($tags) > 0) {
            $max = 0;
            $hasPosts = false;

            foreach($tags as $tag) {
                    $max = $tag->getCount() > $max ? $tag->getCount() : $max;

                    if ($tag->getCount() > 0) {
                        $hasPosts = true;
                    }
            }

            if ($hasPosts) {
                $subject->setMaximumPopularity($max);
                $subject->setTags($tags);
            }
        }

        return $subject->getData('tags');
    }

}