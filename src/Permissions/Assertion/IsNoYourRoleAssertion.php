<?php
namespace RolesManager\Permissions\Assertion;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class IsNoYourRoleAssertion implements AssertionInterface
{
    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null
    ) {

        $user = $acl->getAuthenticationService()->getIdentity();
        $userRole = $user->getRoleId();
        return $userRole !== $resource->getName();

    }
}
