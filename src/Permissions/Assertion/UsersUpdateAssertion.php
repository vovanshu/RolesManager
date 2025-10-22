<?php
namespace RolesManager\Permissions\Assertion;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Laminas\Permissions\Acl\Assertion\AssertionAggregate;
use Omeka\Permissions\Assertion\AssertionNegation;
use Omeka\Permissions\Assertion\IsSelfAssertion;
use Omeka\Permissions\Assertion\UserIsAdminAssertion;

class UsersUpdateAssertion implements AssertionInterface
{
    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null
    ) {

        $class = new AssertionAggregate;
        $class->addAssertions([
            new UserIsAdminAssertion,
            new AssertionNegation(new IsSelfAssertion),
        ]);
        return $class;

    }
}
