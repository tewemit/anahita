<?php

/**
 * Token Controller. Performs password RESTful operation for reseting a token.
 *
 * @category   Anahita
 *
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 *
 * @link       http://www.GetAnahita.com
 */
class ComPeopleControllerToken extends ComBaseControllerResource
{
    /**
     * Constructor.
     *
     * @param KConfig $config An optional KConfig object with configuration options.
     */
    public function __construct(KConfig $config)
    {
        parent::__construct($config);
        $this->registerCallback('after.add', array($this, 'mailConfirmation'));
    }

    /**
     * Initializes the default configuration for the object.
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param KConfig $config An optional KConfig object with configuration options.
     */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'behaviors' => array('com://site/mailer.controller.behavior.mailer'),
        ));

        parent::_initialize($config);
    }

    /**
     * Dispatches a correct action based on the state.
     *
     * @param KCommandContext $context
     */
    protected function _actionPost(KCommandContext $context)
    {
        return $this->execute('add', $context);
    }

    /**
     * Resets a password.
     *
     * @param KCommandContext $context
     */
    protected function _actionAdd(KCommandContext $context)
    {
        $data = $context->data;
        $email = $data->email;
        //an email
        $user = $this->getService('repos://site/users.user')
                      ->getQuery()
                      ->email($email)
                      ->where('IF(@col(block),@col(activation) <> \'\',1)')
                      ->fetch();

        if ($user) {
            $user->requiresActivation()->save();
            $this->getResponse()->status = KHttpResponse::CREATED;
            $this->user = $user;
        } else {
            throw new LibBaseControllerExceptionNotFound('Email Not Found');
        }
    }

    /**
     * Send an email confirmation after reset.
     *
     * @param KCommandContext $context
     */
    public function mailConfirmation(KCommandContext $context)
    {
        if ($this->user) {
            $this->mail(array(
                'to' => $this->user->email,
                'subject' => sprintf(JText::_('COM-PEOPLE-MAIL-SUBJECT-PASSWORD-RESET'), JFactory::getConfig()->getValue('sitename')),
                'template' => 'password_reset',
            ));
        }
    }
}
