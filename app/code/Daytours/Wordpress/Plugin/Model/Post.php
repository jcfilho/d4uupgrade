<?php

namespace Daytours\Wordpress\Plugin\Model;

use \FishPig\WordPress\Model\UserFactory;

class Post
{

    /**
     * @var \FishPig\WordPress\Model\User
     */
    private $userFactory;

    public function __construct(
        UserFactory $userFactory
    )
    {
        $this->userFactory = $userFactory;
    }

    public function aroundGetTermCollectionAsString(\FishPig\WordPress\Model\Post $subject, \Closure $proceed,$taxonomy)
    {

        $key = 'term_collection_as_string_';
        switch ($taxonomy){
            case 'one_category':
                $taxonomy = 'category';
                return $this->getOneCategory($subject,$taxonomy);
                break;
            case 'array_category':
                $taxonomy = 'category';
                return $this->getArrayCategoryOrTag($subject,$taxonomy);
                break;
            case 'array_tags':
                $taxonomy = 'post_tag';
                return $this->getArrayCategoryOrTag($subject,$taxonomy);
                break;

        }

        return [];
    }

    public function getOneCategory(\FishPig\WordPress\Model\Post $subject,$taxonomy){
        $terms = [];
        $termsResult = $subject->getTermCollection($taxonomy);
        $terms['name'] = $termsResult->getFirstItem()->getName();
        $terms['url'] = $termsResult->getFirstItem()->getUrl();
        return $terms;
    }

    public function getArrayCategoryOrTag(\FishPig\WordPress\Model\Post $subject,$taxonomy){
        $terms = [];
        $termsResult = $subject->getTermCollection($taxonomy);

        foreach($termsResult as $term) {
            $terms[] = [
              'url' => $term->getUrl(),
              'name' => $term->getName()
            ];
        }

        return $terms;
    }

    public function afterGetUrl(\FishPig\WordPress\Model\Post $subject, $result){

        if( $subject->getData('post_type') == 'post' ){
            $permalink = explode("/",$subject->getData('permalink'));
            if( is_numeric($permalink[0]) ){
                if( $user = $this->userFactory->create()->load($permalink[0])){
                    $search = $permalink[0].'/';
                    $newUrl = str_replace($search,$user->getUserNicename().'/',$result);
                    $result = $newUrl;
                }
            }
        }

        return $result;
    }

}