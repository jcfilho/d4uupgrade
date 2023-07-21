<?php
/**
 * Copyright © 2017 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Daytours\SubscribeAtCheckout\Model\Config\Source;

use Mageside\SubscribeAtCheckout\Helper\Config as Helper;

/**
 * Class SubscribeLayoutProcessor
 */
class SubscribeLayoutProcessor extends \Mageside\SubscribeAtCheckout\Model\Config\Source\SubscribeLayoutProcessor
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $checkbox = $this->_helper->getConfigModule('checkout_subscribe');

        $checked = $checkbox == 2 ? 0 : 1;
        $visible = $checkbox == 3 ? 0 : 1;
        $changeable = $checkbox == 4 ? 0 : 1;

        if ($this->_helper->getConfigModule('enabled')) {
            $jsLayoutSubscribe = [
                'components' => [
                    'checkout' => [
                        'children' => [
                            'steps' => [
                                'children' => [
                                    'billing-step' => [
                                        'children' => [
                                            'payment' => [
                                                'children' => [
                                                    'payments-list' => [
                                                        'children' => [
                                                            'before-place-order' => [
                                                                'children' => [
                                                                    'newsletter-subscribe' => [
                                                                        'config' => [
                                                                            'checkoutLabel' =>
                                                                                $this->_helper->getConfigModule('checkout_label'),
                                                                            'checked' => $checked,
                                                                            'visible' => $visible,
                                                                            'changeable' => $changeable,
                                                                            'template' => 'Mageside_SubscribeAtCheckout/form/element/newsletter-subscribe'
                                                                        ],
                                                                        'component' => 'Magento_Ui/js/form/form',
                                                                        'displayArea' => 'newsletter-subscribe',
                                                                    ]
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ];

//            $jsLayoutSubscribe = [
//                'components' => [
//                    'checkout' => [
//                        'children' => [
////                            'steps' => [
////                                'children' => [
//                                    'after-place-agreements' => [
//                                        'children' => [
////                                            'payment' => [
////                                                'children' => [
////                                                    'payments-list' => [
////                                                        'children' => [
////                                                            'before-place-order' => [
////                                                                'children' => [
////
////                                                                ]
////                                                            ]
////                                                        ]
////                                                    ]
////                                                ]
////                                            ]
//                                            'newsletter-subscribe' => [
//                                                'config' => [
//                                                    'checkoutLabel' =>
//                                                        $this->_helper->getConfigModule('checkout_label'),
//                                                    'checked' => $checked,
//                                                    'visible' => $visible,
//                                                    'changeable' => $changeable,
//                                                    'template' => 'Mageside_SubscribeAtCheckout/form/element/newsletter-subscribe'
//                                                ],
//                                                'component' => 'Magento_Ui/js/form/form',
//                                                'displayArea' => 'newsletter-subscribe',
//                                            ],
//                                            'after-payments' => [
//                                                'config' => [
//                                                    'template' => 'Daytours_Checkout/after_payments'
//                                                ],
//                                                'component' => 'Daytours_Checkout/js/view/after-payments',
//                                                'displayArea' => 'after-payments',
//                                            ]
//                                        ]
//                                    ],
////                                ]
////                            ]
//                        ]
//                    ]
//                ]
//            ];

            $jsLayout = array_merge_recursive($jsLayout, $jsLayoutSubscribe);
        }

        return $jsLayout;
    }
}
