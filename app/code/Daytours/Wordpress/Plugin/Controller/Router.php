<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 2/14/19
 * Time: 11:17 AM
 */

namespace Daytours\Wordpress\Plugin\Controller;

use FishPig\WordPress\Model\UserFactory;
use Magento\Framework\App\RequestInterface;

class Router extends \FishPig\WordPress\Controller\Router
{
    /**
     * @var \FishPig\WordPress\Model\User
     */
    private $userFactory;


    public function __construct(
        UserFactory $userFactory,
        \FishPig\WordPress\App\Integration\Tests\Proxy $integrationTests,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        array $routerPool = []
    )
    {
        parent::__construct($integrationTests, $routerUrlHelper, $requestDispatcher, $routerPool);
        $this->userFactory = $userFactory;
    }

    /**
     * @param RequestInterface $request
     */
    public function match(RequestInterface $request)
    {
        try {
            if (!$this->_app->canRun()) {
                return false;
            }

            $fullRequestUri = $this->_wpUrlBuilder->getPathInfo($request);
            $blogRoute = $this->_wpUrlBuilder->getBlogRoute();

            if ($blogRoute && ($blogRoute !== $fullRequestUri && strpos($fullRequestUri, $blogRoute . '/') !== 0)) {
                return false;
            }

            if (!($requestUri = $this->_wpUrlBuilder->getRouterRequestUri($request))) {
                $this->addRouteCallback(array($this, '_getHomepageRoutes'));
            }

            $this->addRouteCallback(array($this, '_getSimpleRoutes'));
            $this->addRouteCallback(array($this, '_getPostRoutes'));
            $this->addRouteCallback(array($this, '_getTaxonomyRoutes'));

            $this->addExtraRoutesToQueue();

            $explodeUrl = explode('/',$requestUri);
            if( count($explodeUrl) > 1 ){
                if( !is_numeric($explodeUrl[0]) && $explodeUrl[0] != 'category'  && $explodeUrl[0] != 'author'  && $explodeUrl[0] != 'tag'){
                    $user = $this->userFactory->create()->load($explodeUrl[0],'user_nicename');
                    if( $user->getId() ){
                        $search = $explodeUrl[0].'/';
                        $newUrl = str_replace($search,$user->getId().'/',$requestUri);
                        $requestUri = $newUrl;
                    }
                }
            }

            if (($route = $this->_matchRoute($requestUri)) !== false) {
                $request->setModuleName($route['path']['module'])
                    ->setControllerName($route['path']['controller'])
                    ->setActionName($route['path']['action'])
                    ->setAlias(
                        \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                        $this->_wpUrlBuilder->getUrlAlias($request)
                    );

                if (count($route['params']) > 0) {
                    foreach($route['params'] as $key => $value) {
                        $request->setParam($key, $value);
                    }
                }

                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
        }
        catch (\Exception $e) {
            throw $e;
        }

        return false;
    }


}