<?php
namespace RolesManager\Permissions\Assertion;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Omeka\Permissions\Assertion\AssertionNegation;
use Omeka\Permissions\Assertion\OwnsEntityAssertion;

class VocabularyUpdateAssertion implements AssertionInterface
{
    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null
    ) {

        return new AssertionNegation(new OwnsEntityAssertion);

    }
}
