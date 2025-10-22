<?php declare(strict_types=1);
namespace RolesManager\Controller\Admin;

// use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;
use RolesManager\Entity\Roles;
use RolesManager\Entity\Permissions;
use RolesManager\Entity\RolesPermissions;
use RolesManager\Form\PermissionForm;
use RolesManager\Common;

class PermissionController extends AbstractActionController
{

    use Common;

    protected $counterstr = [];

    public function browseAction()
    {

        $this->setBrowseDefaults('name', 'asc');

        $params = $this->params()->fromQuery();
        // print_r($params);

        // unset($params['nid']);
        $fromRoute = $this->params()->fromRoute();
        if(!empty($fromRoute['class'])){
            $params['class'] = $fromRoute['class'];
        }
        // print_r(($this->params()->fromRoute()));

        $response = $this->api()->search('permissions', $params);
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));

        $permissions = $response->getContent();
        // $permissionCount = $this->viewHelpers()->get('permissionCount');
        // $permissionCount = $permissionCount($permissions);

        $view = new ViewModel;
        $view->setVariable('params', $params);
        $view->setVariable('permissions', $permissions);
        return $view;

    }

    protected function getEntity()
    {

        $id = $this->params('id');
        if ($id) {
            $entity = $this->api()->read('permissions', $id)->getContent();
        }
        if(!empty($entity)){
            return $entity;
        }
        return Null;

    }

    public function showAction()
    {

        $entity = $this->getEntity();
        $view = new ViewModel;
        $view->setVariable('permission', $entity);
        $view->setVariable('resource', $entity);
        return $view;

    }

    public function showDetailsAction()
    {

        $entity = $this->getEntity();
        $view = new ViewModel;
        $view->setVariable('permission', $entity);
        $view->setVariable('resource', $entity);
        return $view->setTerminal(true);

    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $entity = $this->getEntity();
                $this->getConnection()->executeQuery("DELETE FROM `roles_permissions` WHERE `permission_id` = '{$entity->id()}';");
                $response = $this->api($form)->delete('permissions', $entity->id());
                if ($response) {
                    $this->messenger()->addSuccess('Permission successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/permissions');
    }

    public function deleteConfirmAction()
    {

        $entity = $this->getEntity();
        // $roleCount = $this->viewHelpers()->get('roleCount');
        // $roleCount = $roleCount($role);
        // $roleCount = reset($roleCount);

        $view = new ViewModel([
            'permission' => $entity,
            // 'roleCount' => $roleCount,
            'resource' => $entity,
            'resourceLabel' => 'permission',
            'partialPath' => 'roles-manager/admin/permission/show-details',
        ]);
        return $view
            ->setTemplate('common/delete-confirm-details')
            ->setTerminal(true);
    }

    public function batchDeleteConfirmAction()
    {
        /** @var \Omeka\Form\ConfirmForm $form */
        $form = $this->getForm(ConfirmForm::class);
        $routeAction = $this->params()->fromQuery('all') ? 'batch-delete-all' : 'batch-delete';
        $form
            ->setAttribute('action', $this->url()->fromRoute(null, ['action' => $routeAction], true))
            ->setAttribute('id', 'batch-delete-confirm')
            ->setAttribute('class', $routeAction)
            ->setButtonLabel('Confirm delete'); // @translate
        $view = new ViewModel([
            'form' => $form,
        ]);
        return $view
            ->setTerminal(true);
    }

    public function batchDeleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], []);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one permission to batch delete.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], []);
        }

        /** @var \Omeka\Form\ConfirmForm $form */
        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $this->getConnection()->executeQuery("DELETE FROM `roles_permissions` WHERE `permissions_id` IN ({$resourceIds});");
            $response = $this->api($form)->batchDelete('permissions', $resourceIds, [], ['continueOnError' => true]);
            if ($response) {
                $this->messenger()->addSuccess('Permissions successfully deleted.'); // @translate
            }
        } else {
            $this->messenger()->addFormErrors($form);
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], []);
    }

    public function batchDeleteAllAction(): void
    {
        // TODO Support batch delete all.
        $this->messenger()->addError('Delete of all permissions is not supported currently.'); // @translate
    }

    public function addAction()
    {

        /** @var \RolesManager\Form\PermissionForm $form */
        $form = $this->getForm(PermissionForm::class);
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'update-permission');
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                $countlabels = 0;
                if(stripos($data['o:resource'], ',') !== False){
                    $resources = explode(',', $data['o:resource']);
                    if(stripos($data['o:privilege'], ',') !== False){
                        $privileges = explode(',', $data['o:privilege']);
                        $countprivileges = count($privileges);
                        if(stripos($data['o:label'], ',') !== False){
                            $labels = explode(',', $data['o:label']);
                            $countlabels = count($labels);
                        }
                        foreach($resources as $ri => $resource){
                            foreach($privileges as $pi => $privilege){
                                $datas[$ri.$pi]['o:resource'] = trim($resource);
                                $datas[$ri.$pi]['o:privilege'] = trim($privilege);
                                $datas[$ri.$pi]['o:assertion'] = trim($data['o:assertion']);
                                $datas[$ri.$pi]['o:class'] = $data['o:class'];
                                if($countprivileges == $countlabels){
                                    $datas[$ri.$pi]['o:label'] = trim($labels[$pi]);
                                }else{
                                    $datas[$ri.$pi]['o:label'] = trim($data['o:label']);
                                }
                                $datas[$ri.$pi]['o:comment'] = $data['o:comment'];
                            }
                        }
                    }else{
                        foreach($resources as $ri => $resource){
                            $datas[$ri]['o:resource'] = trim($resource);
                            $datas[$ri]['o:privilege'] = trim($data['o:privilege']);
                            $datas[$ri]['o:assertion'] = trim($data['o:assertion']);
                            $datas[$ri]['o:class'] = $data['o:class'];
                            $datas[$ri]['o:label'] = trim($data['o:label']);
                            $datas[$ri]['o:comment'] = $data['o:comment'];
                        }
                    }
                }elseif(stripos($data['o:privilege'], ',') !== False){
                    $privileges = explode(',', $data['o:privilege']);
                    $countprivileges = count($privileges);
                    if(stripos($data['o:label'], ',') !== False){
                        $labels = explode(',', $data['o:label']);
                        $countlabels = count($labels);
                    }
                    foreach($privileges as $pi => $privilege){
                        $datas[$pi]['o:resource'] = trim($data['o:resource']);
                        $datas[$pi]['o:privilege'] = trim($privilege);
                        $datas[$pi]['o:assertion'] = trim($data['o:assertion']);
                        $datas[$pi]['o:class'] = $data['o:class'];
                        if($countprivileges == $countlabels){
                            $datas[$pi]['o:label'] = trim($labels[$pi]);
                        }else{
                            $datas[$pi]['o:label'] = trim($data['o:label']);
                        }
                        $datas[$pi]['o:comment'] = $data['o:comment'];
                    }
                }else{
                    $response = $this->api($form)->create('permissions', $data);
                    if ($response) {
                        $message = new Message(
                            'Permission successfully created.' // @translate
                        );
                        $this->messenger()->addSuccess($message);
                        return $this->redirect()->toRoute('admin/permissions', ['action' => 'edit', 'nid' => $response->getContent()->id()]);
                    }
                }
                if(!empty($datas)){
                    foreach($datas as $adata){
                        $this->api($form)->create('permissions', $adata);
                    }
                    $message = new Message(
                        'Permissions successfully created.' // @translate
                    );
                    $this->messenger()->addSuccess($message);
                    if(!empty($data['o:class'])){
                        return $this->redirect()->toRoute('admin/permissions', ['action' => 'browse', 'nid' => $data['o:class']]);
                    }else{
                        return $this->redirect()->toRoute('admin/permissions');
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;

    }

    public function getPathsPermissions()
    {

        $configs = $this->getConfigs();

        $this->findKeyInArray('path_permissions', $configs);


    }

    public function presetPrivilegesAction()
    {

        $label_registred_classes = 'Registred Resource Classes'; // @translate
        $label_registred_permissions = 'Registred Permissions'; // @translate
        $label_found_classes = 'Found Resource Classes'; // @translate
        $label_found_permissions = 'Found Permissions'; // @translate
        $wrote_resource_classes = 'Wrote Resource Classes'; // @translate
        $permissions_inserted = 'Permissions inserted'; // @translate
        $permissions_updated = 'Permissions updated'; // @translate

        $configs = $this->getConfigs();
        $permissions = $this->getPermsByConfig($configs, True);
        $registred_classes = $this->getSets('resource_classes');
        $registred_permissions = $this->getConnection()->executeQuery("SELECT * FROM `permissions`;")->fetchAll();

        if($registred_classes){
            $result[$label_registred_classes] = count($registred_classes);
        }else{
            $result[$label_registred_classes] = 0;
        }
        if($registred_permissions){
            $result[$label_registred_permissions] = count($registred_permissions);
        }else{
            $result[$label_registred_permissions] = 0;
        }
        $result[$label_found_classes] = $permissions['total_classes'];
        $result[$label_found_permissions] = $permissions['total_permissions'];

        $simulate = False;
        $classaction = Null;
        $permaction = Null;
        $permins = 0;
        $permupd = 0;

        if ($this->getRequest()->isPost()) {
            $classaction = $this->params()->fromPost('classaction');
            $permaction = $this->params()->fromPost('permaction');
            if(!empty($this->params()->fromPost('simulate'))){
                $simulate = True;
            }
        }

        if(!empty($classaction)) {
            if($classaction == 'update' || $classaction == 'insert'){
                $wrp = $this->insertClasses($permissions['classes'], False, $simulate);
            }elseif($classaction == 'recreate'){
                $wrp = $this->insertClasses($permissions['classes'], True, $simulate);
            }
            if(!empty($wrp)){
                $result[$wrote_resource_classes] = $wrp;
            }
        }

        if(!empty($permaction) && !empty($permissions)) {
            if($permaction == 'update'){
                $rp = $this->updatePermissions($permissions, $simulate);
                $permins = $permins + $rp['inserted'];
                $permupd = $permupd + $rp['updated'];
            }elseif($permaction == 'insert'){
                $rp = $this->insertPermissions($permissions, False, $simulate);
                $permins = $permins + $rp['inserted'];
            }elseif($permaction == 'recreate'){
                $rp = $this->insertPermissions($permissions, True, $simulate);
                $permins = $permins + $rp['inserted'];
            }
        }

        if(!empty($permins)){
            $result[$permissions_inserted] = $permins;
        }
        if(!empty($permupd)){
            $result[$permissions_updated] = $permupd;
        }

        $actions = [
            'update' => 'Update exist and add new entries', // @translate
            'insert' => 'Add new entries', // @translate
            'recreate' => 'Recreate all entries', // @translate
        ];

        $form = $this->getForm(Form::class);

        $form->add([
            'name' => 'classaction',
            'type' => 'Select',
            'attributes' => [
                'value' => $classaction,
                'class' => 'chosen-select',
                'id' => 'classaction',
                'required' => true
            ],
            'options' => [
                'label' => 'Classes', // @translate
                'info' => 'You must select a action.', // @translate
                'value_options' => $actions,
            ],
        ]);

        $form->add([
            'name' => 'permaction',
            'type' => 'Select',
            'attributes' => [
                'value' => $permaction,
                'class' => 'chosen-select',
                'id' => 'permaction',
                'required' => true
            ],
            'options' => [
                'label' => 'Permissions', // @translate
                'info' => 'You must select a action.', // @translate
                'value_options' => $actions,
            ],
        ]);

        $form->add([
            'name' => 'simulate',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Simulate', // @translate
                'info' => 'Select this if you do not want entries rewrote.', // @translate
            ],
            'attributes' => [
                'id' => 'simulate',
                'checked' => $simulate ? True : False
            ],
        ]);


        $view = new ViewModel;
        // $view = new JsonModel;
        $view->setVariable('form', $form);
        if(!empty($vars)){
            $view->setVariable('vars', $vars);
        }
        $view->setVariable('result', $result);
        // $view->setTerminal(True);
        return $view;

    }

    public function getActFromName($rc, $level = 99)
    {

        if($level > 80)
            $rc = substr($rc, 0, -6);
        if($level > 60)
            $rc = preg_split('#([A-Z][^A-Z]*)#', $rc, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if($level > 40)
            $rc = array_map('lcfirst', $rc);
        return join('-', $rc);

    }

    public function editAction()
    {

        $entity = $this->getEntity();
        $data = $entity->jsonSerialize();
        $form = $this->getForm(PermissionForm::class);
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'update-permission');
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                $response = $this->api($form)->update('permissions', $entity->id(), $data);
                if ($response) {
                    $message = new Message(
                        'Permission successfully update.', // @translate
                    );
                    $this->messenger()->addSuccess($message);
                    return $this->redirect()->refresh();
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        $form->setData($data);
        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('data', $data);
        return $view;

    }

    // protected function mAdd($form, $data)
    // {

    //     if(stripos($data['o:resource'], ',') !== False){
    //         $resources = explode(',', $data['o:resource']);
    //         array_map('trim', $resources);
    //         if(stripos($data['o:privilege'], ',') !== False){
    //             $privileges = explode(',', $data['o:privilege']);
    //             array_map('trim', $privileges);
    //             $countprivileges = count($privileges);
    //             if(stripos($data['o:label'], ',') !== False){
    //                 $labels = explode(',', $data['o:label']);
    //                 array_map('trim', $labels);
    //                 $countlabels = count($labels);
    //             }
    //             foreach($resources as $ri => $resource){
    //                 foreach($privileges as $pi => $privilege){
    //                     $data['multiadd'][$ri.$pi]['o:resource'] = $resource;
    //                     $data['multiadd'][$ri.$pi]['o:privilege'] = $privilege;
    //                     $data['multiadd'][$ri.$pi]['o:class'] = $data['o:class'];
    //                     if($countprivileges == $countlabels){
    //                         $data['multiadd'][$ri.$pi]['o:label'] = $labels[$pi];
    //                     }else{
    //                         $data['multiadd'][$ri.$pi]['o:label'] = $data['o:label'];
    //                     }
    //                     $data['multiadd'][$ri.$pi]['o:comment'] = $data['o:comment'];
    //                 }
    //             }
    //         }else{
    //             foreach($resources as $ri => $resource){
    //                 $data['multiadd'][$ri]['o:resource'] = $resource;
    //                 $data['multiadd'][$ri]['o:privilege'] = $data['o:privilege'];
    //                 $data['multiadd'][$ri]['o:class'] = $data['o:class'];
    //                 $data['multiadd'][$ri]['o:label'] = $data['o:label'];
    //                 $data['multiadd'][$ri]['o:comment'] = $data['o:comment'];
    //             }
    //         }
    //     }elseif(stripos($data['o:privilege'], ',') !== False){
    //         $privileges = explode(',', $data['o:privilege']);
    //         array_map('trim', $privileges);
    //         $countprivileges = count($privileges);
    //         if(stripos($data['o:label'], ',') !== False){
    //             $labels = explode(',', $data['o:label']);
    //             array_map('trim', $labels);
    //             $countlabels = count($labels);
    //         }
    //         foreach($privileges as $pi => $privilege){
    //             $data['multiadd'][$pi]['o:resource'] = $resource;
    //             $data['multiadd'][$pi]['o:privilege'] = $privilege;
    //             $data['multiadd'][$pi]['o:class'] = $data['o:class'];
    //             if($countprivileges == $countlabels){
    //                 $data['multiadd'][$pi]['o:label'] = $labels[$pi];
    //             }else{
    //                 $data['multiadd'][$pi]['o:label'] = $data['o:label'];
    //             }
    //             $data['multiadd'][$pi]['o:comment'] = $data['o:comment'];
    //         }
    //     }else{
    //         $response = $this->api($form)->create('permissions', $data);
    //         if ($response) {
    //             $message = new Message(
    //                 'Permission successfully created.' // @translate
    //             );
    //             $this->messenger()->addSuccess($message);
    //             return $this->redirect()->toRoute('admin/permission-id', ['action' => 'edit', 'id' => $response->getContent()->id()]);
    //         }
    //     }
    // }

    public function getMainNameRes($res)
    {

        if(stripos($res, '\\') !== False){
            $rc = explode('\\', $res);
            return $rc[0];
        }

    }

    public function getClassRes($res)
    {

        // $Services = $this->getPluginManager()->get('getServices');
        // include $Services('Config')['RolesManager']['configfiles']['permissions'];

        foreach($this->config['class'] as $val => $vars){
            if(in_array($res, $vars)){
                return $val;
            }
        }
        if(stripos($res, '\\') !== False){
            $rc = explode('\\', $res);
            return $rc[0];
        }
        return 'admin';

    }

    public function getLabelPriv($res, $priv, $assert = False)
    {

        // $Services = $this->getPluginManager()->get('getServices');
        // include $Services('Config')['RolesManager']['configfiles']['permissions'];

        if(!empty($assert['labels'])){
            foreach($assert['labels'] as $val => $vars){
                if(in_array($priv, $vars)){
                    return $val;
                }
            }
        }else{
            foreach($this->config['labels'] as $val => $vars){
                if(in_array($priv, $vars)){
                    return $val;
                }
            }
        }
        return $priv;


    }

    public function getAssertion($res, $priv, $need)
    {

        // $Services = $this->getPluginManager()->get('getServices');
        // include $Services('Config')['RolesManager']['configfiles']['permissions'];

        if(!empty($this->config['assertions'][$need])){
            $ra = $this->config['assertions'][$need];
            if(!empty($ra['content'])){
                return $ra;
                // return strtr(quotemeta($ra['content']), ["
// " => '\r\n']);
            }else{
                foreach($ra as $rav){
                    if(!empty($rav['inc'][$res])){
                        if(is_array($rav['inc'][$res]) && in_array($priv, $rav['inc'][$res]) || $rav['inc'][$res] === True){
                            return $rav;
                        }
                        // return strtr(quotemeta($rav['content']), ["
// " => '\r\n']);
                    }
                }
            }

        }
        return False;

    }

}
