<?php declare(strict_types=1);

namespace RolesManager\Api\Representation;

use Omeka\Api\Adapter\AdapterInterface;
use Omeka\Api\Representation\ResourceReference;
use Omeka\Api\ResourceInterface;

class RoleReference extends ResourceReference
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(ResourceInterface $resource, AdapterInterface $adapter)
    {
        $this->name = $resource->getName();
        parent::__construct($resource, $adapter);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function jsonSerialize(): array
    {
        return [
            '@id' => $this->apiUrl(),
            'o:name' => $this->name(),
            'o:label' => $this->label(),
            'o:active' => $this->active(),
            'o:created' => $this->created(),
            'o:parent' => $this->parent(),
            'o:options' => $this->options(),
            'countUser' = > $this->countUser()
        ];
    }
}
