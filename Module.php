<?php declare(strict_types=1);
/*
 * RolesManager
 *
 * Add roles to users and resources to manage the access rights and the
 * resource visibility in a more flexible way.
 *
 * @copyright Volodimir Shumeyko, 2024-2026
 * @license 
 *
 */
namespace RolesManager;

if (!class_exists(\Common\TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

require_once __DIR__ . '/src/General.php';

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;
use Omeka\Api\Representation\UserRepresentation;
use Common\TraitModule;
use RolesManager\General;

/**
 * RolesManager
 *
 * Add roles to users and resources to manage the access in a more flexible way.
 */
class Module extends AbstractModule
{

    use TraitModule;
    // use Common;
    use General;
    
    const NAMESPACE = __NAMESPACE__;

    public function init(ModuleManager $moduleManager): void
    {
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onEventMergeConfig']);
    }

    public function onEventMergeConfig(ModuleEvent $event): void
    {

        if(file_exists($this->modulePath() . '/config/permissions.php')){
            $permissions = [];
            $listperms = (include $this->modulePath() . '/config/permissions.php');
            foreach($listperms as $name => $dt){
                if(file_exists($this->modulePath() . '/config/permissions/'.$name.'.php')){
                    $permissions = array_merge_recursive($permissions, (include $this->modulePath() . '/config/permissions/'.$name.'.php'));
                }
            }
            if(!empty($permissions)){
                /** @var \Laminas\ModuleManager\Listener\ConfigListener $configListener */
                $configListener = $event->getParam('configListener');
                // At this point, the config is read only, so it is copied and replaced.
                $config = $configListener->getMergedConfig(false);
                $config = array_replace_recursive($config, ['permissions' => $permissions]);
                $configListener->setMergedConfig($config);
            }
        }

    }

    public function onBootstrap(MvcEvent $event): void
    {

        parent::onBootstrap($event);
        $this->setMvcEvent($event);
        $this->addDefAclRules();

    }

    /**
     * Add ACL rules for this module.
     */
    protected function addDefAclRules()
    {

        $resources = [
            Entity\Roles::class,
            Api\Adapter\RoleAdapter::class,
            Controller\Admin\RoleController::class,
            Controller\Admin\SettingsController::class,
            Controller\Admin\ImportController::class,
        ];

        $this->getAcl()->deny(
            null,
            $resources
        );

        $this->getAcl()->deny(
            Acl::ROLE_SITE_ADMIN,
            $resources
        );

        $this->getAcl()->allow(
            Acl::ROLE_GLOBAL_ADMIN,
            $resources
        );

    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {

        $sharedEventManager->attach(
            '*',
            'view.layout',
            [$this->getAcl(), 'writeDevRules'],
            -1001
        );

        $sharedEventManager->attach(
            'Laminas\Mvc\Application',
            'route',
            [$this->getAcl(), 'registrationAclRules'],
            1000
        );

        $sharedEventManager->attach(
            '*',
            'api.search.query',
            [$this, 'filterSearchQuery'],
            -1000
        );

        /// For dev only
        $sharedEventManager->attach(
            '*',
            'api.search.query.finalize',
            [$this, 'devSearchQueryFinalize'],
            -1000
        );

        $sharedEventManager->attach(
            '*',
            'view.advanced_search',
            [$this, 'filterAdvancedSearch'],
            -1000
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\User',
            'view.add.section_nav',
            [$this, 'filterViewSectionNav'],
            -1000
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\User',
            'view.edit.section_nav',
            [$this, 'filterViewSectionNav'],
            -1000
        );
       
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.add.section_nav',
            [$this, 'filterViewSectionNav'],
            -1000
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.edit.section_nav',
            [$this, 'filterViewSectionNav'],
            -1000
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.add.after',
            [$this, 'hidePropertiesInItemForm'],
            -1000
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.edit.after',
            [$this, 'hidePropertiesInItemForm'],
            -1000
        );
 
        $sharedEventManager->attach(
            \Omeka\Api\Adapter\UserAdapter::class,
            'api.create.post',
            [$this, 'apiCreateOrUpdatePostUser']
        );

        $sharedEventManager->attach(
            \Omeka\Api\Adapter\UserAdapter::class,
            'api.update.post',
            [$this, 'apiCreateOrUpdatePostUser']
        );

        $sharedEventManager->attach(
            '*',
            'view.layout',
            [$this, 'addAdminResourceHeaders']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\User',
            'view.details',
            [$this, 'viewUserDetails']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\User',
            'view.show.after',
            [$this, 'viewUserShowAfter']
        );

        // $sharedEventManager->attach(
        //     'Omeka\Form\SettingForm',
        //     'form.add_elements',
        //     [$this, 'filterSettingFormElement']
        // );

        $sharedEventManager->attach(
            'Omeka\Form\ResourceForm',
            'form.add_elements',
            [$this, 'filterResourceForm'],
            -1000
        );

        $sharedEventManager->attach(
            '*',
            'rep.resource.display_values',
            [$this, 'filterDisplayValues'],
            -1000
        );

        $sharedEventManager->attach(
            \Omeka\Media\Ingester\Manager::class,
            'service.registered_names',
            [$this, 'filterIngesterRegisteredNames']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.layout',
            [$this, 'addActionsToBrowse']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\ItemSet',
            'view.layout',
            [$this, 'addActionsToBrowse']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Asset',
            'view.layout',
            [$this, 'addActionsToBrowse']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.browse.before',
            [$this, 'addActionsToMediaBrowse']
        );

    }
    public function devSearchQueryFinalize(Event $event)
    {

        if($this->getConf('developing')){
            $target = $event->getTarget();
            $ResourceName = $target->getResourceName();
            $controller = False;
            $ADMIN = False;
            $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
            if(!empty($routeMatch) && is_object($routeMatch) && method_exists($routeMatch, 'getParam')){
                $controller = $routeMatch->getParam('__CONTROLLER__');
                $ADMIN = $routeMatch->getParam('__ADMIN__');
            }
            $args = $event->getParam('request')->getContent();
            $qb = $event->getParam('queryBuilder');
            ob_start();
            if($ADMIN) echo "ADMIN\r\n";
            echo $ResourceName."\r\n";
            if($controller) echo $controller."\r\n";
            print_r($args);
            echo "\r\n".$qb->getQuery()->getSQL();
            file_put_contents(OMEKA_PATH.'/logs/dev.query.finalize.log', ob_get_clean());
        }

    }

    public function filterSearchQuery(Event $event)
    {

        $target = $event->getTarget();
        $ResourceName = $target->getResourceName();
        $controller = False;
        $ADMIN = False;
        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        if(!empty($routeMatch) && is_object($routeMatch) && method_exists($routeMatch, 'getParam')){
            $controller = $routeMatch->getParam('__CONTROLLER__');
            $ADMIN = $routeMatch->getParam('__ADMIN__');
            $action = $routeMatch->getParam('action');
        }
        $args = $event->getParam('request')->getContent();

        if($ADMIN){

            if(!empty($this->getCurrentRoleOps('o:showonlyallowed')) || $this->getSets('show_owned') == 'true'){
                $viewall = False;
                if((!empty($this->getCurrentRoleOps('o:allowviewallitems')) && $ResourceName == 'items') || (!empty($this->getCurrentRoleOps('o:allowviewallmedias')) && $ResourceName == 'media') || (!empty($this->getCurrentRoleOps('o:allowviewallitemsets')) && $ResourceName == 'item_sets') || (!empty($this->getCurrentRoleOps('o:allowviewallassets')) && $ResourceName == 'assets') || $this->getSets('show_owned') == 'true'){
                    if(isset($args['__original_query']['owner_id']) || isset($args['__original_query']['all_item_set'])){
                        $viewall = True;
                    }elseif(isset($args['owner_id']) || isset($args['all_item_set'])){
                        $viewall = True;
                    }elseif($action == 'search'){
                        $viewall = True;
                    }
                }
                if(!$viewall){
                    $qb = $event->getParam('queryBuilder');
                    $entityAlias = $qb->getRootAlias();
                    if($ResourceName == 'item_sets'){
                        $allowed = $this->getCurrentRoleOps('o:allowed_item_sets');
                        if(!empty($allowed) && ($controller == 'item-set')){
                            $qb->andWhere($qb->expr()->in($entityAlias . '.id', $allowed));
                        }
                    }
                    if($ResourceName == 'items' || $ResourceName == 'media'){
                        $qb->andWhere($qb->expr()->eq($entityAlias . '.owner', $this->getCurrentUserID()));
                    }
                }
            }

            if($ResourceName == 'sites'){
                if(!empty($allowed = $this->getCurrentRoleOps('o:allowed_item_sites'))){
                    $qb = $event->getParam('queryBuilder');
                    $entityAlias = $qb->getRootAlias();
                    $qb->andWhere($qb->expr()->in($entityAlias . '.id', $allowed));
                }
            }
        }

        if($ResourceName == 'properties'){
            $qb = $event->getParam('queryBuilder');
            if(!empty($ops = $this->getCurrentRoleOps('no-display-values'))){
                $entityAlias = $qb->getRootAlias();
                $vocabularyIds = [];
                $subQuery = "SELECT id FROM property WHERE";
                foreach($ops as $kp => $prop){
                    list($prefix, $term) = explode(':', $prop);
                    if(empty($vocabularyIds[$prefix])){
                        $rc = $this->getConnection()->executeQuery("SELECT id FROM `vocabulary` WHERE `prefix` = '{$prefix}';")->fetchOne();
                        if(!empty($rc)){
                            $vocabularyIds[$prefix] = $rc;
                        }
                    }
                    if(!empty($vocabularyIds[$prefix])){
                        $subQuery .= " vocabulary_id = '{$vocabularyIds[$prefix]}' AND local_name = '$term'";
                        if($kp < count($ops)-1){
                            $subQuery .= " OR";
                        }
                    }
                }
                $subQuery .= ";";
                $propIds = [];
                $rc = $this->getConnection()->executeQuery($subQuery)->fetchAll();
                if(!empty($rc)){
                    foreach($rc as $pId){
                        $propIds[] = $pId['id'];
                    }
                    $qb->andWhere($qb->expr()->notIn($entityAlias . '.id', $propIds));
                }               
            }

        }

    }

    public function filterViewSectionNav(Event $event)
    {

        $sectionNav = $event->getParam('section_nav');
        if(!empty($this->getCurrentRoleOps('o:hide_item_sets_select'))){
            unset($sectionNav['item-sets']);
        }
        if(!empty($this->getCurrentRoleOps('o:hide_site_selector'))){
            unset($sectionNav['sites']);
        }
        if(!empty($this->getCurrentRoleOps('o:hide_apikey'))){
            unset($sectionNav['edit-keys']);
        }
        if(!empty($this->getCurrentRoleOps('o:hide_items_advanced_settings'))){
            unset($sectionNav['advanced-settings']);
        }
        $event->setParam('section_nav', $sectionNav);

    }

    public function apiCreateOrUpdatePostUser(Event $event): void
    {

        $request = $event->getParam('request');
        $user = $event->getParam('response')->getContent();
        $userId = $user->getId();
        if(!empty($adusrinf = $this->getSets('addition_user_information'))){
            foreach($adusrinf as $key => $label){
                $this->setUserSets($userId, $key, $request->getValue('o:'.$key));
            }
        }

    }

    public function addAdminResourceHeaders(Event $event): void
    {

        /** @var \Laminas\View\Renderer\PhpRenderer $view */
        $view = $event->getTarget();
        $params = $view->params()->fromRoute();

        $plugins = $view->getHelperPluginManager();
        $assetUrl = $plugins->get('assetUrl');

        $controller = False;
        $action = False;

        if(!empty($params['__CONTROLLER__'])){
            $controller = $params['__CONTROLLER__'];
        }elseif(!empty($params['controller'])){
            $controller = $params['controller'];
        }
        if(!empty($params['action'])){
            $action = $params['action'];
        }

        if(!empty($params['__ADMIN__'])){
            $plugins->get('headScript')->appendFile($assetUrl('js/admin-ui.js', 'RolesManager'), 'text/javascript', ['defer' => 'defer']);
        }

    }

    public function viewUserDetails(Event $event): void
    {
        $view = $event->getTarget();
        $user = $view->resource;
        $this->viewUserData($view, $user, 'common/admin/user-addition-information');
    }

    public function viewUserShowAfter(Event $event): void
    {
        $view = $event->getTarget();
        $user = $view->vars()->user;
        $this->viewUserData($view, $user, 'common/admin/user-addition-information-show');
    }

    protected function viewUserData(PhpRenderer $view, UserRepresentation $user, $partial): void
    {
        if(!empty($fields = $this->getSets('addition_user_information'))){
            $userSettings = $this->getUserSettings();
            $userSettings->setTargetId($user->id());
            echo $view->partial(
                $partial,
                [
                    'user' => $user,
                    'userSettings' => $userSettings,
                    'fields' => $fields,
                ]
            );
        }
    }

    public function filterResourceForm(Event $event)
    {

        $form = $event->getTarget();

        $allowed_resource_template = $this->getCurrentRoleOps('o:allowed_resource_template');
        if($allowed_resource_template && $form->has('o:resource_template[o:id]')){
            $resourceTemplateSelect = $form->get('o:resource_template[o:id]');
            $templates = $resourceTemplateSelect->getValueOptions();
            foreach($templates as $k => $v){
                if(!in_array($k, $allowed_resource_template)){
                    unset($templates[$k]);
                }
            }
            $resourceTemplateSelect->setValueOptions($templates);
            if(count($allowed_resource_template) == 1){
                $resourceTemplateSelect->setValue(current($allowed_resource_template));
            }

        }

    }

    public function filterDisplayValues(Event $event)
    {

        if(!empty($ops = $this->getCurrentRoleOps('no-display-values'))){
            $values = $event->getParams()['values'];
            $values = array_diff_key($values, array_flip($ops));
            $event->setParam('values', $values);
        }

    }

    public function hidePropertiesInItemForm(Event $event): void
    {

        if(!empty($ops = $this->getCurrentRoleOps('hidden-properties-in-item-form'))){
            $s = '<style>';
            foreach($ops as $term){
                $s .= '[data-property-term="'.$term.'"]{display: none;}';
            }
            $s .= '</style>';
            echo $s;
        }

    }

    public function filterIngesterRegisteredNames(Event $event): void
    {
        
        $rc = $event->getParam('registered_names');
        foreach($rc as $name){
            if($this->isAllowedIngester($name)){
                $r[] = $name;
            }
        }
        $event->setParam('registered_names', $r);

    }

    public function addActionsToBrowse(Event $event): void
    {

        $view = $event->getTarget();
        $params = $view->params()->fromRoute();
        $args = $view->params()->fromQuery();
        if(!empty($params['__ADMIN__']) && !empty($params['__CONTROLLER__']) && !empty($params['action'])){
            $controller = $params['__CONTROLLER__'];
            if($params['action'] == 'browse'){
                if((!empty($this->getCurrentRoleOps('o:allowviewallitems')) && $controller == 'item') || (!empty($this->getCurrentRoleOps('o:allowviewallitemsets')) && $controller == 'item-set') || (!empty($this->getCurrentRoleOps('o:allowviewallassets')) && $controller == 'asset') || $this->getSets('show_owned') == 'true' && !$this->getCurrentRoleOps('o:showonlyallowed')){
                    $vars = $view->vars();
                    $need = '<div id="page-actions">';
                    $add = '<div id="page-actions">';
                    if($controller == 'item-set'){
                        if($this->getCurrentRoleOps('o:allowed_item_sets')){
                            if(isset($args['all_item_set'])){
                                unset($args['all_item_set']);
                                $title = $view->translate('Show only mine');
                            }else{
                                $args['all_item_set'] = '';
                                $title = $view->translate('Show all');
                            }
                            $add .= $view->hyperlink($title, $view->url(null, ['action' => 'browse'], ['query' => $args], true), ['class' => 'button']);
                        }
                    }else{
                        if(isset($args['owner_id'])){
                            unset($args['owner_id']);
                            $title = $view->translate('Show only mine');
                        }else{
                            $args['owner_id'] = '';
                            $title = $view->translate('Show all');
                        }
                        $add .= $view->hyperlink($title, $view->url(null, ['action' => 'browse'], ['query' => $args], true), ['class' => 'button']);
                    }
                    $content = $vars->offsetGet('content');
                    $content = strtr($content, [$need => $add]);
                    $vars->offsetSet('content', $content);      
                }
            }
        }

    }

    public function addActionsToMediaBrowse(Event $event): void
    {

        if(!empty($this->getCurrentRoleOps('o:allowviewallmedias')) || $this->getSets('show_owned') == 'true' && !$this->getCurrentRoleOps('o:showonlyallowed')){
            $view = $event->getTarget();
            $params = $view->params()->fromRoute();
            $args = $view->params()->fromQuery();
            if(!empty($params['__ADMIN__']) && !empty($params['__CONTROLLER__']) && !empty($params['action'])){
                if($params['action'] == 'browse'){
                    echo '<div id="page-actions">';
                    if(isset($args['owner_id'])){
                        echo $view->hyperlink($view->translate('Show only mine'), $view->url(null, ['action' => 'browse'], true), ['class' => 'button']);
                    }else{
                        echo $view->hyperlink($view->translate('Show all'), '?owner_id=', ['class' => 'button']); 
                    }
                    echo '</div>';
                }
            }
        }

    }

    public function filterAdvancedSearch(Event $event): void
    {

        // $query = $event->getParam('query');
        $resourceType = $event->getParam('resourceType');
        $partials = $event->getParam('partials');        
        if(!empty($ops = $this->getCurrentRoleOps('o:list_partials_advancedsearch'))){
            if($this->getCurrentRoleOps('o:list_disallowed_partials_advancedsearch')){
                $partials = array_flip(array_diff_key(array_flip($partials), array_flip($ops)));
            }else{
                $partials = array_intersect_key($partials, $ops);
            }
        }
        if(!empty($this->getCurrentRoleOps('o:showonlyallowed')) || $this->getSets('show_owned') == 'true'){
            if((!empty($this->getCurrentRoleOps('o:allowviewallitems')) && $resourceType == 'item') || (!empty($this->getCurrentRoleOps('o:allowviewallmedias')) && $resourceType == 'media') || (!empty($this->getCurrentRoleOps('o:allowviewallitemsets')) && $resourceType == 'itemSet') || $this->getSets('show_owned') == 'true'){
                array_unshift($partials, 'common/advanced-search/owner-hidden');                
            }
        }        
        $event->setParam('partials', $partials);

    }

    public function getConfigForm(PhpRenderer $renderer)
    {

        $url = $renderer->url('admin/roles-manager-settings', ['action' => 'edit']);
        $response = $this->getMvcEvent()->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
        return $response;

    }

}
