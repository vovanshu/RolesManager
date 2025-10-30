<?php declare(strict_types=1);
namespace RolesManager\Controller\Admin;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;
use Omeka\Mvc\Exception;
use RolesManager\Form\RoleAddForm;
use RolesManager\Form\RoleModForm;
use RolesManager\Form\RoleEditForm;
use RolesManager\Common;

class RoleController extends AbstractActionController
{

    use Common;

    public function browseAction()
    {

        $this->setBrowseDefaults('name', 'asc');
        $response = $this->api()->search('roles', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $roles = $response->getContent();
        return new ViewModel([
            'nativeRoles' => $this->getNativeRolesForMod(),
            'roles' => $roles
        ]);

    }

    protected function getEntity()
    {

        $id = $this->params('id');
        if ($id) {
            $entity = $this->api()->read('roles', $id)->getContent();
            if(!empty($entity)){
                return $entity;
            }
        }
        return Null;

    }

    public function showAction()
    {

        $entity = $this->getEntity();
        return new ViewModel([
            'resource' => $entity
        ]);

    }

    public function showDetailsAction()
    {

        $entity = $this->getEntity();
        $view = new ViewModel([
            'resource' => $entity
        ]);
        return $view->setTerminal(true);

    }

    public function deleteAction()
    {

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $entity = $this->getEntity();
                $response = $this->api($form)->delete('roles', $entity->id());
                if ($response) {
                    $this->messenger()->addSuccess('Role successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/roles');

    }

    public function deleteConfirmAction()
    {

        $entity = $this->getEntity();
        if(!empty($entity)){
            $form = $this->getForm(ConfirmForm::class);
            $form->setAttribute('action', $entity->url('delete'));
            if($entity->created() && $entity->countUser() > 0){
                $form->get('submit')->setAttribute('disabled', 'disabled');
            }
            $view = new ViewModel();
            $view->setVariable('form', $form);
            $view->setVariable('resource', $entity);
            $view->setTemplate('roles-manager/admin/role/delete-confirm');
            return $view->setTerminal(true);
        }

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
        return $view->setTerminal(true);

    }

    public function batchDeleteAction()
    {

        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], []);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one role to batch delete.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], []);
        }

        /** @var \Omeka\Form\ConfirmForm $form */
        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $response = $this->api($form)->batchDelete('roles', $resourceIds, [], ['continueOnError' => true]);
            if ($response) {
                $this->messenger()->addSuccess('Roles successfully deleted.'); // @translate
            }
        } else {
            $this->messenger()->addFormErrors($form);
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], []);

    }

    public function batchDeleteAllAction(): void
    {
        // TODO Support batch delete all.
        $this->messenger()->addError('Delete of all roles is not supported currently.'); // @translate
    }

    public function addAction()
    {

        $form = $this->getForm(RoleAddForm::class);
        $view = new ViewModel;
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'add-role');
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data['o:created'] = True;
            $form->setData($data);
            if ($form->isValid()) {
                if($data['usingSelectedRole'] == 'template' && !empty($data['o:roleAStpl'])){
                    $needRole = $data['o:roleAStpl'];
                }
                if($data['usingSelectedRole'] == 'parent' && !empty($data['o:parentRole'])){
                    $needRole = $data['o:parentRole'];
                    $data['o:parent'] = $data['o:parentRole'];
                }
                $tpl = $this->api()->searchOne('roles', ['name' => $needRole]);
                if($tpl->getTotalResults()){
                    $trplrole = $tpl->getContent()->jsonSerialize();
                    $data['o:options'] = $trplrole['o:options'];
                    if($data['usingSelectedRole'] == 'template' && !empty($data['o:roleAStpl'])){
                        $data['o:allow'] = $trplrole['o:allow'];
                        $data['o:deny'] = $trplrole['o:deny'];
                    }
                }
                $response = $this->api($form)->create('roles', $data);
                if ($response) {
                    $message = new Message(
                        'Role successfully created.' // @translate
                    );
                    $this->messenger()->addSuccess($message);
                    return $this->redirect()->toRoute('admin/roles', ['action' => 'edit', 'id' => $response->getContent()->id()]);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        $view->setVariable('form', $form);
        return $view;

    }

    public function modAction()
    {

        if (!$this->userIsAllowed(RoleController::class, 'change-native')) {
            throw new Exception\PermissionDeniedException;
        }

        $form = $this->getForm(RoleModForm::class);
        $view = new ViewModel;
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'mod-role');
        if ($this->getRequest()->isPost()) {
            $RoleLabels = $this->getAcl()->getRoleLabels();
            $data = $this->params()->fromPost();
            $data['o:created'] = False;
            if(empty($data['o:label']) && !empty($label = $RoleLabels[$data['o:name']])){
                $data['o:label'] = $label;
            }
            $form->setData($data);
            if ($form->isValid()) {
                $response = $this->api($form)->create('roles', $data);
                if ($response) {
                    if(!empty($data['o:options'])){
                        $this->setUserSettings($data['o:name'], $data['o:options']);
                    }
                    $message = new Message(
                        'Role successfully created.' // @translate
                    );
                    $this->messenger()->addSuccess($message);
                    return $this->redirect()->toRoute('admin/roles', ['action' => 'edit', 'id' => $response->getContent()->id()]);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        $view->setVariable('form', $form);
        return $view;

    }

    public function editAction()
    {

        if (!$this->userIsAllowed(RoleController::class, 'edit')) {
            throw new Exception\PermissionDeniedException;
        }
        $entity = $this->getEntity();
        $data = $entity->jsonSerialize();
        $allowDisactive = True;
        if($entity->created() && $entity->countUser() > 0){
            $allowDisactive = False;
        }
        $parent = $data['o:parent'];
        $form = $this->getForm(RoleEditForm::class, ['allowDisactive' => $allowDisactive, 'created' => $data['o:created'], 'current' => $data['o:name'], 'parent' => $parent]);
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'update-role');
        $name_role = $data['o:name'];
        if(!empty($data['o:options']['o:imitation_fields'])){
            $data['o:imitation_fields'] = $data['o:options']['o:imitation_fields'];
        }
        $form->get('role')->populateValues($data);
        $imitation_fields = $this->getRoleOps($parent, 'o:imitation_fields');
        if(!empty($imitation_fields)){
            foreach($imitation_fields as $key_field){
                $data['o:options'][$key_field] = $this->getRoleOps($parent, $key_field);
            }
        }
        if(!empty($data['o:options'])){
            $form->get('options')->populateValues($data['o:options']);
        }
        $permissions = $this->preparePermissionsForForm($data);
        if(!empty($permissions)){
            $form->get('permissions')->populateValues($permissions);
        }
        $view = new ViewModel;
        $view->setVariable('form', $form);
        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $form->setData($post);
            if($form->isValid()){
                $data = $post['role'];
                if(!empty($post['permissions'])){
                    $data = $this->prepareForSetPermissions($data, $post['permissions']);
                }
                if(!empty($parent) && empty($data['o:parent'])){
                    $tpl = $this->api()->searchOne('roles', ['name' => $parent]);
                    if($tpl->getTotalResults()){
                        $trplrole = $tpl->getContent()->jsonSerialize();
                        $data['o:allow'] = $trplrole['o:allow'];
                        $data['o:deny'] = $trplrole['o:deny'];
                    }
                }
                if(empty($parent) && !empty($data['o:parent'])){
                    $data['o:allow'] = [];
                    $data['o:deny'] = [];
                    $parent = $data['o:parent'];
                }
                if(!empty($post['options'])){
                    $data['o:options'] = $post['options'];
                }else{
                    $data['o:options'] = [];
                }
                if(!empty($data['o:imitation_fields'])){
                    $data['o:options']['o:imitation_fields'] = $data['o:imitation_fields'];
                }
                $data['o:options']['no-display-values'] = $this->params()->fromPost('no-display-values', []);
                $data['o:options']['hidden-properties-in-item-form'] = $this->params()->fromPost('hidden-properties-in-item-form', []);
                $response = $this->api($form)->update('roles', $entity->id(), $data);
                if ($response) {
                    $this->setUserSettings($name_role, $data['o:options']);
                    $message = new Message(
                        'Role successfully update.', // @translate
                    );
                    $this->messenger()->addSuccess($message);
                    return $this->redirect()->refresh();
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        $view->setVariable('data', $data);
        $view->setVariable('parent', $parent);
        return $view;

    }
    
    private function preparePermissionsForForm($data)
    {

        $result = [];
        if(!empty($data['o:allow'])){
            foreach($data['o:allow'] as $class => $labels){
                foreach($labels as $label){
                    $result[$class.'-'.$label] = 'allow';
                }
            }
        }
        if(!empty($data['o:deny'])){
            foreach($data['o:deny'] as $class => $labels){
                foreach($labels as $label){
                    $result[$class.'-'.$label] = 'deny';
                }
            }
        }
        return $result;

    }

    private function prepareForSetPermissions($data, array $permissions)
    {

        $allow = [];
        $deny = [];
        $rules = $this->getPermissionsRules();
        foreach($rules as $class => $cv){
            foreach($cv as $resource => $rv){
                foreach($rv as $label => $privileges){
                    if(!empty($permissions[$class.'-'.$label])){
                        if($permissions[$class.'-'.$label] == 'allow'){
                            $allow[$class][] = $label;
                        }
                        if($permissions[$class.'-'.$label] == 'deny'){
                            $deny[$class][] = $label;
                        }
                    }
                }
            }
        }
        if(!empty($allow)){
            $data['o:allow'] = $allow;
        }
        if(!empty($deny)){
            $data['o:deny'] = $deny;
        }
        // echo '<pre>';
// echo 'Allow:<br>';
        // print_r($allow);
// echo 'Deny:<br>';
        // print_r($deny);
        // print_r($permissions);
        // print_r($rules);
        // echo '</pre>';

        return $data;

    }

}
