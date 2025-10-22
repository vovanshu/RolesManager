<?php
namespace RolesManager\Form;

use Laminas\Form\Form;
use Omeka\Form\Element\Recaptcha;
use RolesManager\Common;

class ForgotPasswordForm extends Form
{

    use Common;

    public function __construct($serviceLocator, $requestedName, $options)
    {
        $this->setServiceLocator($serviceLocator);
        parent::__construct();
    }

    public function init()
    {
        $this->add([
            'name' => 'email',
            'type' => 'Email',
            'options' => [
                'label' => 'Email', // @translate
            ],
            'attributes' => [
                'id' => 'email',
                'required' => true,
            ],
        ]);
        if($this->getSets('recaptcha_enable_on_forgot_password') == 'true'){
            $this->add([
                'name' => 'recaptcha',
                'type' => Recaptcha::class,
                'attributes' => [
                    'type' => 'recaptcha',
                    'name' => 'g-recaptcha-response',
                    'class' => 'g-recaptcha',
                ],
            ]);
        }
    }
}
