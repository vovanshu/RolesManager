<?php declare(strict_types=1);
/*
 * RolesManager
 *
 * Add roles to users and resources to manage the access rights and the
 * resource visibility in a more flexible way.
 *
 * @copyright Volodimir Shumeyko, 2024-2025
 * @license 
 *
 */
namespace RolesManager;

if (!class_exists(\Common\TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

if (!class_exists(RolesManager\Common::class)) {
    require_once __DIR__ . '/Common.php';
}

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Renderer\PhpRenderer;
// use Laminas\Permissions\Acl\Assertion\AssertionAggregate;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;
// use Omeka\Permissions\Assertion\OwnsEntityAssertion;
// use Omeka\Permissions\Assertion\HasSitePermissionAssertion;
use Omeka\Api\Representation\UserRepresentation;
use Common\TraitModule;
use RolesManager\Common;

/**
 * RolesManager
 *
 * Add roles to users and resources to manage the access in a more flexible way.
 */
class Module extends AbstractModule
{

    use TraitModule;
    use Common;
    
    const NAMESPACE = __NAMESPACE__;

    public function getConfig()
    {

        // $config = include $this->modulePath().'/config/module.config.php';
        if(file_exists($this->modulePath() . '/config/permissions')){
            $permissions = [];
            foreach(glob($this->modulePath() . '/config/permissions/*.php') as $file){
                $permissions = array_merge_recursive($permissions, (include $file));
                // $permissions = $permissions + (include $file);
                
                // $permissions = $permissions ? $permissions + (include $file) : (include $file);
            }
        }

        // array_walk($permissions, function(&$v) {
        //     $v = array_map('array_unique', $v);
        // });

        return include $this->modulePath().'/config/module.config.php';;
    }

    public function onBootstrap(MvcEvent $event): void
    {

        parent::onBootstrap($event);
        $this->setMvcEvent($event);
        $this->addDefAclRules();
        // $this->getAcl()->registrationAclRules();

    }

    /**
     * Add ACL rules for this module.
     */
    protected function addDefAclRules()
    {

        $resources = [
            Entity\Roles::class,
            // Entity\Permissions::class,
            Api\Adapter\RoleAdapter::class,
            // Api\Adapter\PermissionAdapter::class,
            Controller\Admin\RoleController::class,
            // Controller\Admin\PermissionController::class,
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
            // [$this, 'registrationAclRules'],
            1000
        );

        // $sharedEventManager->attach(
        //     'Omeka\Api\Adapter\ItemAdapter',
        //     'api.search.query',
        //     [$this, 'prepareSearchQuery']
        // );

        // $sharedEventManager->attach(
        //     'Omeka\Api\Adapter\MediaAdapter',
        //     'api.search.query',
        //     [$this, 'prepareSearchQuery']
        // );

        // $sharedEventManager->attach(
        //     'Omeka\Api\Adapter\ItemSetAdapter',
        //     'api.search.query',
        //     [$this, 'prepareSearchQuery']
        // );

        // $sharedEventManager->attach(
        //     'Omeka\Api\Adapter\AssetAdapter',
        //     'api.search.query',
        //     [$this, 'prepareSearchQuery']
        // );

        $sharedEventManager->attach(
            '*',
            'api.search.query',
            [$this, 'filterSearchQuery']
        );

        $sharedEventManager->attach(
            '*',
            'api.search.query.finalize',
            [$this, 'filterSearchQueryFinalize']
        );

        $sharedEventManager->attach(
            '*',
            'view.advanced_search',
            [$this, 'filterAdvancedSearch']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\User',
            'view.add.section_nav',
            [$this, 'filterViewSectionNav']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\User',
            'view.edit.section_nav',
            [$this, 'filterViewSectionNav']
        );
       
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.add.section_nav',
            [$this, 'filterViewSectionNav']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.edit.section_nav',
            [$this, 'filterViewSectionNav']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.add.after',
            [$this, 'hidePropertiesInItemForm']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.edit.after',
            [$this, 'hidePropertiesInItemForm']
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
            [$this, 'filterResourceForm']
        );

        $sharedEventManager->attach(
            '*',
            'rep.resource.display_values',
            [$this, 'filterDisplayValues']
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

    public function prepareSearchQuery(Event $event)
    {

        if(!empty($this->getCurrentRoleOps('o:showonlyallowed'))){
            
            $target = $event->getTarget();
            $ResourceName = $target->getResourceName();
            // echo '<!-- '.$ResourceName.' -->';
            // $params = $event->getParams();
            $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
            $controller = $routeMatch->getParam('__CONTROLLER__');
            $args = $event->getParam('request')->getContent();

            if(!empty($routeMatch) && is_object($routeMatch) && method_exists($routeMatch, 'getParam')){
                if(!empty($routeMatch->getParam('__ADMIN__'))){
                    
                    // $action = $routeMatch->getParam('action');
                    // $actions = ['browse'];
                    // print_r($routeMatch->getParams());
                    // $request = $event->getParam('request');
                    // print_r(array_keys($params));
                    
                    $viewall = False;
                    if((!empty($this->getCurrentRoleOps('o:allowviewallitems')) && $ResourceName == 'items') || (!empty($this->getCurrentRoleOps('o:allowviewallmedias')) && $ResourceName == 'media') || (!empty($this->getCurrentRoleOps('o:allowviewallitemsets')) && $ResourceName == 'item_sets') || (!empty($this->getCurrentRoleOps('o:allowviewallassets')) && $ResourceName == 'assets')){
                        if(isset($args['owner_id']) || isset($args['all_item_set'])){
                            $viewall = True;
                        }
                    }
                    if(!$viewall){
                        $entityAlias = 'omeka_root';
                        $qb = $event->getParam('queryBuilder');
                    // $ignored = ['sort_by_default', 'sort_order_default', 'sort_by', 'sort_order', 'page'];   
                    // if(!isset($params['owner_id']) && (array_keys($params) == $ignored) && !empty($this->getCurentUserID())){
                        // if($ResourceName == 'item_sets'){
                        //     $allowed = $this->getCurrentRoleOps('o:allowed_item_sets');
                        //     if(!empty($allowed) && ($controller == 'item-set')){
                        //         $qb->andWhere($qb->expr()->in($entityAlias . '.id', $allowed));
                        //     }
                        // }
                        // if($ResourceName == 'items' || $ResourceName == 'media'){
                        //     $qb->andWhere($qb->expr()->eq($entityAlias . '.owner', $this->getCurentUserID()));
                        // }
                    }
                        
                    // }else{
                        // $qb->andWhere($expr->like($entityAlias . '.owner', '%'));
                    // }
                    // print_r($params);
                }
            }
        }
        

    }

    public function filterSearchQuery(Event $event)
    {

        $target = $event->getTarget();
        $ResourceName = $target->getResourceName();

        $entityAlias = 'omeka_root';
        $controller = False;
        $ADMIN = False;
        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        if(!empty($routeMatch) && is_object($routeMatch) && method_exists($routeMatch, 'getParam')){
            $controller = $routeMatch->getParam('__CONTROLLER__');
            $ADMIN = $routeMatch->getParam('__ADMIN__');
        }
        $args = $event->getParam('request')->getContent();

        $entityAlias = 'omeka_root';

        // echo $ResourceName;
        if($ADMIN){

            if(!empty($this->getCurrentRoleOps('o:showonlyallowed')) || $this->getSets('show_owned') == 'true'){

                $viewall = False;
                if((!empty($this->getCurrentRoleOps('o:allowviewallitems')) && $ResourceName == 'items') || (!empty($this->getCurrentRoleOps('o:allowviewallmedias')) && $ResourceName == 'media') || (!empty($this->getCurrentRoleOps('o:allowviewallitemsets')) && $ResourceName == 'item_sets') || (!empty($this->getCurrentRoleOps('o:allowviewallassets')) && $ResourceName == 'assets') || $this->getSets('show_owned') == 'true'){
                    if(isset($args['owner_id']) || isset($args['all_item_set'])){
                        $viewall = True;
                    }
                }

                if(!$viewall){
                    $qb = $event->getParam('queryBuilder');
                // $ignored = ['sort_by_default', 'sort_order_default', 'sort_by', 'sort_order', 'page'];   
                // if(!isset($params['owner_id']) && (array_keys($params) == $ignored) && !empty($this->getCurentUserID())){
                    if($ResourceName == 'item_sets'){
                        $allowed = $this->getCurrentRoleOps('o:allowed_item_sets');
                        if(!empty($allowed) && ($controller == 'item-set')){
                            $qb->andWhere($qb->expr()->in($entityAlias . '.id', $allowed));
                        }
                    }
                    if($ResourceName == 'items' || $ResourceName == 'media'){
                        $qb->andWhere($qb->expr()->eq($entityAlias . '.owner', $this->getCurentUserID()));
                    }
                }
            }

        }

        if($ResourceName == 'properties'){
            $qb = $event->getParam('queryBuilder');
            if(!empty($ops = $this->getCurrentRoleOps('no-display-values'))){
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

    public function filterSearchQueryFinalize(Event $event)
    {

        // $target = $event->getTarget();
        // $ResourceName = $target->getResourceName();

        // $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        // $controller = $routeMatch->getParam('__CONTROLLER__');
        // $args = $event->getParam('request')->getContent();

        // $entityAlias = 'omeka_root';

        // $qb = $event->getParam('queryBuilder');
        // $request = $event->getParam('request');

        // echo '<pre>';

        // print_r(get_class_methods($qb->getQuery()));
        // print_r($request->getContent());
        // print_r(get_class_methods($request));

        // echo '</pre>';


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

        // echo "<!---\r\n";
        // echo $controller."\r\n";
        // echo $action."\r\n";
        // echo "\r\n--->";
        if(!empty($params['__ADMIN__'])){
            // if(in_array($controller, ['item']) && in_array($action, ['add', 'edit'])){
                $plugins->get('headScript')->appendFile($assetUrl('js/admin-ui.js', 'RolesManager'), 'text/javascript', ['defer' => 'defer']);
            // }
        }

        // $params = $view->params()->fromRoute();
        // $args = $view->params()->fromQuery();
        // if(!empty($params['__ADMIN__']) && !empty($params['__CONTROLLER__']) && !empty($params['action'])){
            // $controller = $params['__CONTROLLER__'];
        // }

        // $plugins->get('headLink')->appendStylesheet($assetUrl('css/items-review.css', 'ItemsReview'));
        // if (in_array($action, ['add', 'edit'])) {
            // $plugins->get('headLink')->appendStylesheet($assetUrl('css/items-review-contract.css', 'ItemsReview'));
            // $plugins->get('headScript')->appendFile($assetUrl('js/items-review.js', 'ItemsReview'), 'text/javascript', ['defer' => 'defer']);
        // }

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
                                $add .= $view->hyperlink($view->translate('Show only mine'), $view->url(null, ['action' => 'browse'], true), ['class' => 'button']);
                            }else{
                                $add .= $view->hyperlink($view->translate('Show all'), '?all_item_set=', ['class' => 'button']); 
                            }
                        }
                    }else{
                        if(isset($args['owner_id'])){
                            $add .= $view->hyperlink($view->translate('Show only mine'), $view->url(null, ['action' => 'browse'], true), ['class' => 'button']);
                        }else{
                            $add .= $view->hyperlink($view->translate('Show all'), '?owner_id=', ['class' => 'button']); 
                        }
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

        // (!empty($this->getCurrentRoleOps('o:allowviewallmedias')) && $controller == 'media') ||
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

        if(!empty($ops = $this->getCurrentRoleOps('o:list_partials_advancedsearch'))){
            $partials = $event->getParam('partials');
            if($this->getCurrentRoleOps('o:list_disallowed_partials_advancedsearch')){
                $partials = array_flip(array_diff_key(array_flip($partials), array_flip($ops)));
            }else{
                $partials = $ops;
            }
            $event->setParam('partials', $partials);
        }
        
    }

    public function getConfigForm(PhpRenderer $renderer)
    {

        $url = $renderer->url('admin/roles-manager-settings', ['action' => 'edit']);
        // return "<script>window.location.href = '$url';</script>";
        $response = $this->getMvcEvent()->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
        return $response;

    }

}
