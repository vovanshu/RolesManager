<?php declare(strict_types=1);

namespace RolesManager\Form\Element;

use Laminas\Form\Element\Select;
use Laminas\View\Helper\Url;
use Omeka\Api\Manager as ApiManager;
use RolesManager\General;

class ResourceClassesSelect extends Select
{

    use General;

    /**
     * @see https://github.com/zendframework/zendframework/issues/2761#issuecomment-14488216
     *
     * {@inheritDoc}
     * @see \Laminas\Form\Element\Select::getInputSpecification()
     */
    public function getInputSpecification(): array
    {
        $inputSpecification = parent::getInputSpecification();
        $inputSpecification['required'] = !empty($this->attributes['required']);
        return $inputSpecification;
    }

    public function getValueOptions(): array
    {

        $valueOptions = [];
        $resource_classes = $this->getSets('resource_classes');
        foreach ($resource_classes as $key => $label) {
            $valueOptions[$key] = $label;
        }

        $prependValueOptions = $this->getOption('prepend_value_options');
        if (is_array($prependValueOptions)) {
            $valueOptions = $prependValueOptions + $valueOptions;
        }
        return $valueOptions;

    }

}
