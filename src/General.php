<?php

namespace RolesManager;

require_once __DIR__ . '/Common.php';

use Omeka\Permissions\Acl;
use RolesManager\Common;

trait General
{

    use Common;

    protected $listRoles;

    public function getPermissionsRules()
    {

        $permissions = $this->getConfigs()['permissions'];
        if(!empty($permissions)){
            if(!empty($permissions['rules'])){
                return $permissions['rules'];
            }
        }
        return Null;

    }

    public function getPermissionsClasses()
    {

        $permissions = $this->getConfigs()['permissions'];
        if(!empty($permissions) && !empty($permissions['classes'])){
            return $permissions['classes'];
        }
        return Null;

    }

    public function getPermissionLabel($name)
    {

        $permissions = $this->getConfigs()['permissions'];
        if(!empty($permissions) && !empty($permissions['labels'])){
            if(!empty($permissions['labels'][$name])){
                if(is_array($permissions['labels'][$name])){
                    return current($permissions['labels'][$name]);
                }elseif(is_string($permissions['labels'][$name])){
                    return $permissions['labels'][$name];
                }
                
            }else{
                return $name.' - Label no found!';
            }
        }
        return 'Labels no found!';

    }

    public function getCurrentRoleOps($name)
    {

        return $this->getRoleOps($this->getRoleCurrentUser(), $name);

    }

    public function getRoleOps($role, $name)
    {

        $imitation = $this->getConf('imitation_fields');
        $listRoles = $this->getAcl()->listRoles();
        if(!empty($listRoles[$role])){
            if(!empty($listRoles[$role]['parent'])){
                $parent = $listRoles[$role]['parent'];
                if(!empty($listRoles[$parent]['options']['o:imitation_fields'])){
                    foreach($listRoles[$parent]['options']['o:imitation_fields'] as $key_field){
                        array_push($imitation, $key_field);
                    }
                }
                if(!empty($listRoles[$parent]['options'][$name]) && in_array($name, $imitation)){
                    return $listRoles[$parent]['options'][$name];
                }
            }
            if(!empty($listRoles[$role]['options'][$name])){
                return $listRoles[$role]['options'][$name];
            }
        }
        return Null;

    }

    public function getNativeRole()
    {
        return $this->getAcl()->getRoles();
    }

    private function getNativeRolesForMod()
    {

        $r = [];
        $rs = $this->getApiManager()->search('roles', [])->getContent();
        $explude[] = Acl::ROLE_GLOBAL_ADMIN;
        if(!empty($rs)){
            foreach($rs as $role){
                $explude[] = $role->name();
            }
        }
        $rc = $this->getAcl()->getRoleLabels();
        $rc['public'] = 'Public';
        foreach($rc as $name => $label){
            if(!in_array($name, $explude)){
                $r[$name] = $label;
            }
        }
        return $r;

    }
    
    private function resourceExists($resource)
    {

        if(($pos = stripos($resource, '::class')) !== False){
            $rc = substr($resource, 0, $pos);
            if (class_exists($rc, True)) {
                $class = $rc;
            }
        }else{
            $class = $resource;
        }
        if(!empty($class) && $this->getAcl()->hasResource($class)){
            return True;
        }
        return False;

    }
    
    private function getAssertionObject($assertion)
    {

        if(!empty($assertion) && class_exists($assertion)){
            try {
                return new $assertion();
            } catch (\Throwable $e) {
                $this->getLogger()->err((string) $e);
                return False;
            }
        }
        return Null;

    }

    private function isAllowedIngester($name)
    {

        if($this->getRoleCurrentUser() == Acl::ROLE_GLOBAL_ADMIN){
            return True;
        }elseif(!empty($ops = $this->getCurrentRoleOps('o:list_media_types'))){
            if(!empty($this->getCurrentRoleOps('o:list_disallowed_media_types'))){
                if(in_array($name, $ops)){
                    return False;
                }
            }else{
                if(!in_array($name, $ops)){
                    return False;
                }
            }
        }
        return True;

    }

    private function setUserSettings($role, $settings)
    {

        $rc = $this->getConnection()->executeQuery("SELECT * FROM `user` WHERE `role` = '{$role}';");
        if(!empty($rc)){
            $r = $rc->fetchAll();
            foreach($r as $user){
                if(!empty($settings['o:allowed_resource_template']) && (count($settings['o:allowed_resource_template']) == 1 || !empty($settings['o:hide_default_resource_template']))){
                    $this->getUserSettings()->set('default_resource_template', current($settings['o:allowed_resource_template']), $user['id']);
                }
                if(!empty($settings['o:allowed_item_sets']) && (count($settings['o:allowed_item_sets']) == 1 || !empty($settings['o:remove_browse_defaults_admin_item_sets']))){
                    $this->getUserSettings()->set('default_item_sets', [current($settings['o:allowed_item_sets'])], $user['id']);
                }
                if(!empty($settings['o:allowed_item_sites']) && (count($settings['o:allowed_item_sites']) == 1 || !empty($settings['o:remove_browse_defaults_admin_sites']))){
                    $this->getUserSettings()->set('default_item_sites', [current($settings['o:allowed_item_sites'])], $user['id']);
                }
            }
        }

    }
    
    public function isParentRole($role)
    {

        if($this->getConnection()->executeQuery("SELECT * FROM `roles` WHERE `parent` = '$role';")->fetchOne()){
            return True;
        }
        return False;

    }

}
