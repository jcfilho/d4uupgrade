<?php
namespace Magebay\Messages\Controller\Adminhtml;

class Messages extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magebay_messages_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magebay_Messages::messages';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magebay\Messages\Model\Messages';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magebay_Messages::messages';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';
}