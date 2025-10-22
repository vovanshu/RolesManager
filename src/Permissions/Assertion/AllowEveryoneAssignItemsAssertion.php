<?php
namespace RolesManager\Permissions\Assertion;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Laminas\Permissions\Acl\Assertion\AssertionAggregate;
use Omeka\Permissions\Assertion\OwnsEntityAssertion;
use Omeka\Permissions\Assertion\HasSitePermissionAssertion;

class AllowEveryoneAssignItemsAssertion implements AssertionInterface
{
    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null
    ) {

        $class = new AssertionAggregate;
        $class->addAssertions([
            new OwnsEntityAssertion,
            new HasSitePermissionAssertion("admin"),
            new HasSitePermissionAssertion("editor"),
            new HasSitePermissionAssertion("viewer"),
        ]);
        $class->setMode(AssertionAggregate::MODE_AT_LEAST_ONE);
        return $class;

    }
}
