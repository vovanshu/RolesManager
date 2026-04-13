<?php

namespace RolesManager;

trait Common
{

    protected $configName = __NAMESPACE__;

    protected $mvcEvent;

    protected $serviceLocator;

    protected $applicationRouteMatch;

    protected $acl;

    protected $connection;

    protected $settings;

    protected $userSettings;

    protected $config;

    protected $apiManager;

    // protected $ApiAdapterManager;

    protected $ApiAdapter = [];

    protected $entityManager;

    protected $logger;

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

    public function getApplicationRouteMatch()
    {

        if($this->applicationRouteMatch){
            if(!$this->applicationRouteMatch){
                $this->applicationRouteMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
            }
            return $this->applicationRouteMatch;
        }
        return;

    }

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

    public function getCurrentUserID()
    {

        $user = $this->getAcl()->getAuthenticationService()->getIdentity();
        if($user){
            return $user->getId();
        }
        return Null;

    }

    public function getRoleCurrentUser()
    {

        $r = 'public';
        $rc = $this->getAcl()->getAuthenticationService()->getIdentity();
        if($rc){
            $r = $rc->getRoleId();
        }
        return $r;

    }

    public function getRoleUser($userID)
    {

        $r = $this->getUser($userID);
        if(!empty($r['role'])){
            return $r['role'];
        }
        return False;

    }

    public function getUser($userID)
    {

        $rc = $this->getConnection()->executeQuery("SELECT id, name, email, role, created FROM `user` WHERE `id` = '{$userID}' LIMIT 1;");
        if(!empty($rc)){
            return $rc->fetchAssociative();
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

    public function convert_size($size)
    {
        $unit=array('b','Kb','Mb','Gb','Tb','Pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
    
}
