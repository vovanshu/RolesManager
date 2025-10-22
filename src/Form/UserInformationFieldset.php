<?php declare(strict_types=1);

namespace RolesManager\Form;

use Laminas\Form\Fieldset;

class UserInformationFieldset extends Fieldset
{
    /**
     * @var string
     */
    protected $label = 'Addition information'; // @translate

    protected $elementGroups = [
        'addition_information' => 'Addition information', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'addition-information')
            ->setOption('element_groups', $this->elementGroups)
        ;
    }
}
