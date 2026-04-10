<?php
namespace RolesManager\Permissions;

// use Omeka\Api\ResourceInterface;
// use Laminas\Authentication\AuthenticationServiceInterface;
// use Laminas\Permissions\Acl\Acl as ZendAcl;
use Laminas\EventManager\Event;
// use Laminas\Permissions\Acl\Assertion\AssertionAggregate;
// use Omeka\Permissions\Assertion\OwnsEntityAssertion;
// use Omeka\Permissions\Assertion\HasSitePermissionAssertion;
// use RolesManager\Entity\Roles;
use RolesManager\Common;

class Acl extends \Omeka\Permissions\Acl
{

    use Common;

    protected $listRoles;

    public function listRoles()
    {

        if(empty($this->listRoles)){
            $rc = $this->getConnection()->executeQuery("SELECT * FROM `roles` WHERE `active` = 1;");
            if(!empty($rc)){
                foreach($rc->fetchAll() as $v){
                    if(!empty($v['options'])){
                        $v['options'] = json_decode($v['options'], True);
                    }
                    if(!empty($v['allow'])){
                        $v['allow'] = json_decode($v['allow'], True);
                    }
                    if(!empty($v['deny'])){
                        $v['deny'] = json_decode($v['deny'], True);
                    }
                    $this->listRoles[$v['name']] = $v;
                }
            }
        }
        return $this->listRoles;

    }

    /**
     * Registration custom ACL roles.
     *
     * @param Acl $acl
     */
    public function registrationRoles()
    {

        $listRoles = $this->listRoles();
        if(!empty($listRoles)){
            foreach($listRoles as $role){
                if (!$this->hasRole($role['name'])) {
                    $this->addRole($role['name']);
                }
                $this->addRoleLabel($role['name'], $role['label']);
            }
        }

     }

    public function registrationAclRules(Event $event)
    {

        if($this->getConf('debug')){
            ob_start();
            print_r($this->rules);
            file_put_contents(OMEKA_PATH.'/logs/dev.rules.system.log', ob_get_clean());
        }

        $current = $this->getRoleCurrentUser();
        if(!empty($this->listRoles) && !empty($this->listRoles[$current])){
            if(!empty($parent = $this->listRoles[$current]['parent'])){
                $allow = $this->listRoles[$parent]['allow'];
                $deny = $this->listRoles[$parent]['deny'];
            }else{
                $allow = $this->listRoles[$current]['allow'];
                $deny = $this->listRoles[$current]['deny'];
            }
            $permissions = $this->getPermissionsRules();
            foreach($permissions as $class => $cv){
                foreach($cv as $resource => $rv){
                    if($this->resourceExists($resource)){
                        foreach($rv as $label => $privileges){
                            if(!empty($privileges) && is_array($privileges)){
                                foreach($privileges as $privilege){
                                    if(is_array($privilege)){
                                        $assertion = $this->getAssertionObject(current($privilege));                              
                                        $privilege = key($privilege);
                                    }else{
                                        $assertion = Null;
                                    }
                                    if($allow && !empty($allow[$class]) && in_array($label, $allow[$class]) && $assertion !== False){
                                        $this->allow(
                                            $current,
                                            $resource,
                                            $privilege,
                                            $assertion
                                        );
                                    }
                                    if($deny && !empty($deny[$class]) && in_array($label, $deny[$class]) && $assertion !== False){
                                        $this->deny(
                                            $current,
                                            $resource,
                                            $privilege,
                                            $assertion
                                        );
                                    }
                                }
                            }else{
                                if($allow && !empty($allow[$class]) && in_array($label, $allow[$class])){
                                    $this->allow(
                                        $current,
                                        $resource
                                    );
                                }
                                if($deny && !empty($deny[$class]) && in_array($label, $deny[$class])){
                                    $this->deny(
                                        $current,
                                        $resource
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        if($this->getSets('viewer_can_assign_items') == 'true'){
            $this->allow(
                null,
                'Omeka\Entity\Site',
                'can-assign-items',
                $this->getAssertionObject('RolesManager\Permissions\Assertion\AllowEveryoneAssignItemsAssertion')
            );
        }

        if($this->getConf('debug')){
            ob_start();
            print_r($this->rules);
            file_put_contents(OMEKA_PATH.'/logs/dev.rules.set.log', ob_get_clean());
        }

    }

    public function getAllRules()
    {
        return $this->rules;
    }

    public function writeDevRules(Event $event)
    {

        if($this->getConf('debug')){
            ob_start();
            print_r($this->rules);
            file_put_contents(OMEKA_PATH.'/logs/dev.rules.finish.log', ob_get_clean());
        }

    }

}
