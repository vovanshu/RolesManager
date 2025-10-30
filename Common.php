<?php

namespace RolesManager;

use RolesManager\Entity\Roles;
use RolesManager\Entity\Permissions;
use RolesManager\Entity\RolesPermissions;
use Omeka\Permissions\Acl;

trait Common
{

    protected $configName = 'rolesmanager';

    protected $mvcEvent;

    protected $serviceLocator;

    protected $acl;

    protected $connection;

    protected $settings;

    protected $userSettings;

    protected $config;

    protected $apiManager;

    protected $ApiAdapterManager;

    protected $ApiAdapter = [];

    protected $entityManager;

    protected $logger;

    protected $listRoles;

    public function setMvcEvent($mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;
    }

    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    /**
     * Set the service locator.
     *
     * @param $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get the service locator.
     *
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    // public function getAdapter($resourceName = null)
    // {

    //     if($this->serviceLocator){
    //         if(!$this->ApiAdapterManager){
    //             $this->ApiAdapterManager = $this->getServiceLocator()->get('Omeka\ApiAdapterManager');
    //         }
    //         if(!empty($resourceName)){
    //             if(empty($this->ApiAdapter[$resourceName])){
    //                 $this->ApiAdapter[$resourceName] = $this->getServiceLocator()->get('Omeka\ApiAdapterManager')->get($resourceName);
    //             }
    //             return $this->ApiAdapter[$resourceName];
    //         }
    //         return $this->ApiAdapterManager;
    //     }
    //     return;

    // }

    public function getConnection()
    {

        if($this->serviceLocator){
            if(!$this->connection){
                $this->connection = $this->getServiceLocator()->get('Omeka\Connection');
            }
            return $this->connection;
        }
        return;

    }

    public function getLogger()
    {

        if($this->serviceLocator){
            if(!$this->logger){
                $this->logger = $this->getServiceLocator()->get('Omeka\Logger');
            }
            return $this->logger;
        }
        return;

    }

    public function getApiManager()
    {

        if($this->serviceLocator){
            if(!$this->apiManager){
                $this->apiManager = $this->getServiceLocator()->get('Omeka\ApiManager');
            }
            return $this->apiManager;
        }
        return;

    }

    public function getEntityManager()
    {

        if($this->serviceLocator){
            if(!$this->entityManager){
                $this->entityManager = $this->getServiceLocator()->get('Omeka\EntityManager');
            }
            return $this->entityManager;
        }
        return;

    }

    public function getAcl()
    {

        if($this->serviceLocator){
            if(!$this->acl){
                $this->acl = $this->getServiceLocator()->get('Omeka\Acl');
            }
            return $this->acl;
        }
        return;

    }

    public function getSettings()
    {

        if($this->serviceLocator){
            if(!$this->settings){
                $this->settings = $this->getServiceLocator()->get('Omeka\Settings');
            }
            return $this->settings;
        }
        return;

    }

    public function getUserSettings()
    {

        if($this->serviceLocator){
            if(!$this->userSettings){
                $this->userSettings = $this->getServiceLocator()->get('Omeka\Settings\User');
            }
            return $this->userSettings;
        }
        return;

    }

    public function getConfigs()
    {

        if($this->serviceLocator){
            if(!$this->config){
                $this->config = $this->getServiceLocator()->get('Config');
            }
            return $this->config;
        }
        return;
        
    }

    public function getMediaIngesters()
    {
        return $this->getServiceLocator()->get('Omeka\Media\Ingester\Manager');
    }

    public function getConf($name = Null, $param = Null, $all = False)
    {

        $config = $this->getConfigs()[$this->configName];
        if(!empty($name)){
            if(!empty($config[$name])){
                if(!empty($param)){
                    if(isset($config[$name][$param])){
                        return $config[$name][$param];
                    }else{
                        return False;
                    }
                }else{
                    return $config[$name];
                }
            }
        }else{
            if($all){
                return $config;
            }else{
                return False;
            }
        }

    }

    public function getOps($name)
    {

        $config = $this->getConfigs()[$this->configName];
        if(!empty($name)){
            if(!empty($config['options']) && !empty($config['options'][$name])){
                return $config['options'][$name];
            }
        }
        return False;

    }

    public function getSets($name, $callback = [])
    {
        
        $name = (($opt = $this->getOps($name)) ? $opt : $name);
        $r = ($this->getSettings()->get($name) ? $this->getSettings()->get($name) : ($this->getConf('settings', $name) ? $this->getConf('settings', $name) : Null));
        if(!empty($callback)){
            $r = call_user_func_array($callback, [$r]);
        }
        return $r;
        
    }

    public function setSets($name, $value)
    {
        
        $name = (($opt = $this->getOps($name)) ? $opt : $name);
        $this->getSettings()->set($name, $value);
        
    }


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

    public function getUserSets($name, $userId, $callback = [])
    {
        
        $name = (($opt = $this->getOps($name)) ? $opt : $name);
        $r = $this->getUserSettings()->get($name, Null, $userId);
        if(!empty($callback)){
            $r = call_user_func_array($callback, [$r]);
        }
        return $r;
        
    }

    private function setUserSets($userId, $name, $value)
    {

        $name = (($opt = $this->getOps($name)) ? $opt : $name);
        $this->getUserSettings()->set($name, $value, $userId);

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
        // $explude[] = $acl::ROLE_SITE_ADMIN;
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
                // return eval("$content");
                return new $assertion();
            } catch (\Throwable $e) {
                $this->getLogger()->err((string) $e);
                return False;
            }
        }
        return Null;

    }

    private function getCurentUserID()
    {

        $user = $this->getAcl()->getAuthenticationService()->getIdentity();
        if($user){
            return $user->getId();
        }
        return Null;

    }

    private function getRoleCurrentUser()
    {

        $r = 'public';
        $rc = $this->getAcl()->getAuthenticationService()->getIdentity();
        if($rc){
            $r = $rc->getRoleId();
        }
        return $r;

    }

    private function getRoleUser($userID)
    {

        $r = $this->getUser($userID);
        if(!empty($r['role'])){
            return $r['role'];
        }
        return False;

    }

    private function getUser($userID)
    {

        $user = $this->getApiManager()->read('users', $userID)->getContent();
        if(!empty($user)){
            $r['id'] = $user['o:id'];
            $r['name'] = $user['o:name'];
            $r['email'] = $user['o:email'];
            $r['created'] = $user['o:created'];
            $r['role'] = $user['o:role'];
            return $r;
        }
        return False;

    }

    private function getUserEntry($id)
    {
        return $this->getAdapter('users')->findEntity($id);
    }

    public function getStrConf($name, $param = Null)
    {

        $rc = $this->getConf($name, $param);
        if(!empty($rc)){
            return $this->translate($rc);
        }
        return False;

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

    public function getListMediaTypes()
    {

        foreach ($this->getMediaIngesters()->getRegisteredNames() as $ingester) {
            $r[$ingester] = $this->getMediaIngesters()->get($ingester)->getLabel();
        }
        return $r;

    }

    private function arrayToTextList($string, $separator = ' = ')
    {

        if(!empty($string)){
            if(is_string($string)){
                $rc = json_decode($string, True);
            }else{
                $rc = $string;
            }
            $r = '';
            foreach($rc as $k => $v){
                $r .= "$k$separator$v\r\n";
            }
            return $r;
        }
        return;

    }

    /**
     * Get each line of a string separately as a key-value list.
     *
     * @param string $string
     * @return array
     */
    private function textListToArray($string, $keyValueSeparator = ' = ')
    {

        $result = [];
        foreach ($this->stringToList($string) as $keyValue) {
            [$key, $value] = array_map('trim', explode($keyValueSeparator, $keyValue, 2));
            $result[$key] = $value;
        }
        return $result;

    }

    /**
     * Get each line of a string separately as a list.
     *
     * @param string $string
     * @return array
     */
    protected function stringToList($string)
    {
        return array_filter(array_map('trim', explode("\n", $this->fixEndOfLine($string))), 'strlen');
    }

    /**
     * Clean the text area from end of lines.
     *
     * This method fixes Windows and Apple copy/paste from a textarea input.
     *
     * @param string $string
     * @return string
     */
    private function fixEndOfLine($string)
    {
        return str_replace(["\r\n", "\n\r", "\r"], ["\n", "\n", "\n"], (string) $string);
    }

    private function hadIPInWLrecaptcha()
    {

        if(!empty($value = $this->getSets('recaptcha_ip_white_list'))){
            $list = explode("\r\n", $value);
            if($_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR']){
                $curIP = ip2long($_SERVER['REMOTE_ADDR']);
            }else{
                $curIP = ip2long($_SERVER['HTTP_X_REAL_IP']);
            }
            // if(in_array($curIP, $list)){
                // return True;
            // }
            foreach($list as $v){
                if(stripos($v, '-') !== False){
                    $va = explode('-', $v);
                    if($curIP >= ip2long($va[0]) && $curIP <= ip2long($va[1])){
                        return True;
                    }
                }else{
                    if($curIP == ip2long($v)){
                        return True;
                    }
                }
            }
        }
        return False;

    }

    public function findKeyInArray($rc, $need)
    {

        $r = [];
        if(!empty($rc[$need])){
            $r[$need] = $rc[$need];
        }
        foreach($rc as $a => $b){
            if(!empty($b[$need])){
                $r[$a] = $b[$need];
            }
            if(!empty($b) && is_array($b)){
                foreach($b as $c => $d){
                    if(!empty($d[$need])){
                        $r[$a] = $d[$need];
                    }
                }
            }
        }
        return $r;

    }

    public function isEntrieExistInArray($haystack, $needs, $key = False)
    {

        foreach($haystack as $i => $a){
            $rc = [];
            foreach($needs as $n => $v){
                if(isset($a[$n]) && $a[$n] === $v){
                    $rc[] = $n;
                }
            }
            if(count($needs) === count($rc)){
                if($key && isset($a[$key])){
                    return $a[$key];
                }else{
                    return $i;
                }
            }
        }
        return False;

    }
    
    public function isParentRole($role)
    {

        if($this->getConnection()->executeQuery("SELECT * FROM `roles` WHERE `parent` = '$role';")->fetchOne()){
            return True;
        }
        return False;

    }

}
