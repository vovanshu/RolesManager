<?php declare(strict_types=1);

namespace RolesManager\Form\Element;

use Laminas\Form\Element\Select;
use Laminas\View\Helper\Url;
use Omeka\Api\Manager as ApiManager;

class ParentRoleSelect extends Select
{
    /**
     * @var ApiManager
     */
    protected $apiManager;

    /**
     * @var Url
     */
    protected $url;

    protected $urlHelper;

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

        $current = $this->getOption('current');
        $RoleCurrentUser = $this->getOption('RoleCurrentUser');
        $query = $this->getOption('query');
        if (!is_array($query)) {
            $query = [];
        }
        if (!isset($query['sort_by'])) {
            $query['sort_by'] = 'name';
        }

        $nameAsValue = $this->getOption('name_as_value', false);

        $valueOptions = [];

        $response = $this->getApiManager()->search('roles', $query);
        foreach ($response->getContent() as $role) {
            $name = $role->name();
            $key = $nameAsValue ? $name : $role->id();
            if($role->name() !== $current && !$role->parent() && $role->name() !== $RoleCurrentUser){
                $valueOptions[$key] = $role->label();
            }
        }

        $prependValueOptions = $this->getOption('prepend_value_options');
        if (is_array($prependValueOptions)) {
            $valueOptions = $prependValueOptions + $valueOptions;
        }
        return $valueOptions;
    }

    public function setOptions($options)
    {
        if (!empty($options['chosen'])) {
            $defaultOptions = [
                'resource_value_options' => [
                    'resource' => 'roles',
                    'query' => [],
                    'option_text_callback' => function ($v) {
                        return $v->name();
                    },
                ],
                'name_as_value' => true,
            ];
            if (isset($options['resource_value_options'])) {
                $options['resource_value_options'] += $defaultOptions['resource_value_options'];
            } else {
                $options['resource_value_options'] = $defaultOptions['resource_value_options'];
            }
            if (!isset($options['name_as_value'])) {
                $options['name_as_value'] = $defaultOptions['name_as_value'];
            }

            $urlHelper = $this->getUrlHelper();
            $defaultAttributes = [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select roles…', // @translate
                'data-api-base-url' => $urlHelper('api/default', ['resource' => 'roles']),
            ];
            $this->setAttributes($defaultAttributes);
        }

        return parent::setOptions($options);
    }

    public function setApiManager(ApiManager $apiManager): self
    {
        $this->apiManager = $apiManager;
        return $this;
    }

    public function getApiManager(): ApiManager
    {
        return $this->apiManager;
    }

    public function setUrlHelper(Url $urlHelper): self
    {
        $this->urlHelper = $urlHelper;
        return $this;
    }

    public function getUrlHelper(): Url
    {
        return $this->urlHelper;
    }
}
