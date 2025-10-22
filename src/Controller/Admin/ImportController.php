<?php declare(strict_types=1);
namespace RolesManager\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Form\ConfirmForm;
use Omeka\Form\Element\PropertySelect;
use Omeka\Stdlib\Message;
use RolesManager\Form\RoleAddForm;
// use RolesManager\Form\RoleEditForm;
use RolesManager\Common;

class ImportController extends AbstractActionController
{

    use Common;

    public function __construct($serviceLocator = Null, $requestedName = Null, $options = Null)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function browseAction()
    {

        $path = $this->getConf('imports');
        $list = [];
        $listImported = [];
        $rc = glob($path.'*.csv');
        foreach($rc as $f){
            $p = pathinfo($f);
            $list[$p['filename']] = $p;
        }
        $rc = glob($path.'*.imp');
        foreach($rc as $f){
            $p = pathinfo($f);
            $listImported[$p['filename']] = $p;
        }
        $view = new ViewModel;
        $view->setVariable('list', $list);
        $view->setVariable('listImported', $listImported);
        return $view;

    }

    public function configAction()
    {

        $path = $this->getConf('imports');
        $name = $this->params('name');
        $list = glob($path.'*.*');
        foreach ($list as $file){
            $pathinfo = pathinfo($file);
            $file_list[$pathinfo['filename']] = $pathinfo['filename'];
        }

        $form = $this->getForm(Form::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager-import', ['action' => 'prepare']));

        $form->add([
            'name' => 'file-import',
            'type' => 'Select',
            'attributes' => [
                'class' => 'chosen-select',
                // 'multiple' => true,
                'id' => 'file-import',
                'required' => true,
                'value' => $name
            ],
            'options' => [
                'label' => 'File for import', // @translate
                'value_options' => $file_list,
            ],
        ]);

        $form->add([
            'name' => 'type-import',
            'type' => 'Select',
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select type data for import', // @translate
                // 'multiple' => true,
                'id' => 'type-import',
                'required' => true
            ],
            'options' => [
                'label' => 'Type import', // @translate
                'value_options' => ['users' => 'Users', 'roles' => 'Roles'],
            ],
        ]);

        $form->add([
            'name' => 'parent-role',
            'type' => 'Select',
            'attributes' => [
                'class' => 'parent-role',
                'data-placeholder' => 'Select role will be default for users or template for roles.', // @translate
                // 'multiple' => true,
                'id' => 'parent-role',
                'required' => true
            ],
            'options' => [
                'label' => 'Parent role', // @translate
                'value_options' => array_reverse($this->getAcl()->getRoleLabels()),
            ],
        ]);

        $form->add([
            'name' => 'delimiter',
            'type' => 'text',
            'options' => [
                'label' => 'Delimiter', // @translate
            ],
            'attributes' => [
                'id' => 'delimiter',
                'value' => ',',
                'required' => true
            ],
        ]);

        $form->add([
            'name' => 'enclosure',
            'type' => 'text',
            'options' => [
                'label' => 'enclosure', // @translate
            ],
            'attributes' => [
                'id' => 'Enclosure',
                'value' => '"',
                'required' => true
            ],
        ]);

        $form->add([
            'name' => 'escape',
            'type' => 'text',
            'options' => [
                'label' => 'Escape', // @translate
            ],
            'attributes' => [
                'id' => 'escape',
                'value' => '\\',
                'required' => true
            ],
        ]);

        $form->add([
            'name' => 'mval-separator',
            'type' => 'text',
            'options' => [
                'label' => 'Multi-value separator', // @translate
            ],
            'attributes' => [
                'id' => 'mval-separator',
                'value' => '|',
                'required' => true
            ],
        ]);

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;

    }

    public function prepareAction()
    {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $path = $this->getConf('imports');
            $post = $request->getPost()->toArray();

            $max_line_length = 10000;
            $rowc = 0;

            $options[''] = 'None';
            $options['name'] = 'Name';
            if($post['type-import'] == 'roles'){
                $options['label'] = 'Label';
            }
            if($post['type-import'] == 'users'){
                $options['email'] = 'E-mail';
                $options['role'] = 'Role';
                if(!empty($aops = $this->getSets('addition_user_information'))){
                    foreach($aops as $aoname => $aolabel){
                        $options[$aoname] = $aolabel;
                    }
                }
                $findRoleBy[''] = 'None';
                $findRoleBy['name'] = 'Name';
                $findRoleBy['label'] = 'Label';
                if(!empty($aops = $this->getSets('addition_role_information'))){
                    foreach($aops as $aoname => $aolabel){
                        $findRoleBy[$aoname] = $aolabel;
                    }
                }
            }
            $options['item_sets'] = 'Item Sets';

            if (($handle = fopen($path.$post['file-import'].'.csv', "r")) !== FALSE) {
                while (($rows = fgetcsv($handle, $max_line_length, $post['delimiter'], $post['enclosure'], $post['escape'])) !== false) {
                    if(!empty($rows)){
                        $count_rows = count($rows);
                        if($rowc == 0){
                            for ($c=0; $c < $count_rows; $c++) {
                                $head[$c] = trim($rows[$c]);
                            }
                        }else{
                            if($rowc <= 1){
                                for ($c=0; $c < $count_rows; $c++) {
                                    if(stripos($rows[$c], $post['mval-separator']) !== False){
                                        $vals = explode($post['mval-separator'], $rows[$c]);
                                        $count_vals = count($vals);
                                        for ($v=0; $v < $count_vals; $v++) {
                                            $data[$rowc][$c][$v] = trim($vals[$v]);
                                        }
                                    }else{
                                        $data[$rowc][$c] = trim($rows[$c]);
                                    }
                                }
                            }
                        }
                        $rowc++;
                    }
                }
                fclose($handle);
            }
            
            $form = $this->getForm(Form::class);
            $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager-import', ['action' => 'import']));

            $form->add([
                'name' => 'file-import',
                'type' => 'hidden',
                'attributes' => [
                    'required' => true,
                    'value' => $post['file-import']
                ],
                'options' => [
                    'label' => 'File for import', // @translate
                ],
            ]);
            
            $form->add([
                'name' => 'file-import-info',
                'type' => 'text',
                'attributes' => [
                    'required' => false,
                    'value' => $post['file-import'],
                    'disabled' => True
                ],
                'options' => [
                    'label' => 'File for import', // @translate
                ],
            ]);

            $form->add([
                'name' => 'type-import',
                'type' => 'hidden',
                'attributes' => [
                    'required' => true,
                    'value' => $post['type-import']
                ],
                'options' => [
                    'label' => 'Type import', // @translate
                ],
            ]);

            $form->add([
                'name' => 'type-import-info',
                'type' => 'text',
                'attributes' => [
                    'required' => false,
                    'value' => $post['type-import'],
                    'disabled' => True
                ],
                'options' => [
                    'label' => 'Type import', // @translate
                ],
            ]);

            if($post['type-import'] == 'roles'){
                $form->add([
                    'name' => 'parent-role-is-template',
                    'type' => 'checkbox',
                    'options' => [
                        'label' => 'Parent Role as Template', // @translate
                        'info' => 'Select this if you want new role copy permissions.', // @translate
                    ],
                    'attributes' => [
                        'id' => 'parent-role-is-template',
                        'value' => false,
                    ],
                ]);
            }

            $form->add([
                'name' => 'parent-role',
                'type' => 'hidden',
                'attributes' => [
                    'required' => true,
                    'value' => $post['parent-role'],
                ],
                'options' => [
                    'label' => 'parent-role', // @translate
                ],
            ]);

            $form->add([
                'name' => 'delimiter',
                'type' => 'hidden',
                'attributes' => [
                    'required' => true,
                    'value' => $post['delimiter'],
                ],
                'options' => [
                    'label' => 'Delimiter', // @translate
                ],
            ]);

            $form->add([
                'name' => 'enclosure',
                'type' => 'hidden',
                'attributes' => [
                    'required' => true,
                    'value' => $post['enclosure'],
                ],
                'options' => [
                    'label' => 'Enclosure', // @translate
                ],
            ]);

            $form->add([
                'name' => 'escape',
                'type' => 'hidden',
                'attributes' => [
                    'required' => true,
                    'value' => $post['escape'],
                ],
                'options' => [
                    'label' => 'Escape', // @translate
                ],
            ]);

            $form->add([
                'name' => 'mval-separator',
                'type' => 'hidden',
                'attributes' => [
                    'required' => true,
                    'value' => $post['mval-separator'],
                ],
                'options' => [
                    'label' => 'Multi-value separator', // @translate
                ],
            ]);
            

            $form->add([
                'name' => 'find_item_sets_by',
                'type' => PropertySelect::class,
                'options' => [
                    'label' => 'Search Item Sets by', // @translate
                    'info' => 'Select property to use for searching Item Sets.', // @translate
                    'empty_option' => '[None]', // @translate
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'id' => 'media_alt_text_property',
                    'class' => 'chosen-select'
                ],
            ]);

            if($post['type-import'] == 'users'){
                $form->add([
                    'name' => 'find_role_by',
                    'type' => 'Select',
                    'options' => [
                        'label' => 'Search Role by', // @translate
                        'info' => 'Select property to use for searching Role.', // @translate
                        'empty_option' => '[None]', // @translate
                        'term_as_value' => true,
                        'value_options' => $findRoleBy,
                    ],
                    'attributes' => [
                        'id' => 'media_alt_text_property',
                        'class' => 'chosen-select'
                    ],
                ]);
            }

            for ($c=0; $c < $count_rows; $c++) {

                if(is_array($data[1][$c])){
                    $val = join(' | ', $data[1][$c]);
                }else{
                    $val = $data[1][$c];
                }

                $form->add([
                    'name' => 'row-value-'.$c,
                    'type' => 'Text',
                    'attributes' => [
                        'id' => 'row-value-'.$c,
                        'required' => False,
                        'value' => $val,
                        'disabled' => True
                    ],
                    'options' => [
                        'label' => $head[$c]
                    ],
                ]);
                
                $form->add([
                    'name' => 'row-relation-'.$c,
                    'type' => 'Select',
                    'attributes' => [
                        'class' => 'chosen-select',
                        // 'multiple' => true,
                        'id' => 'row-relation-'.$c,
                        'required' => False,
                        // 'value' => $name
                    ],
                    'options' => [
                        'label' => 'Relation to value', // @translate
                        'value_options' => $options,
                    ],
                ]);

            }

            $form->add([
                'name' => 'count-rows',
                'type' => 'Text',
                'attributes' => [
                    'id' => 'count-rows',
                    'required' => False,
                    'value' => $rowc,
                    'disabled' => True
                ],
                'options' => [
                    'label' => 'Count rows', // @translate
                ],
            ]);
            $view = new ViewModel;
            $view->setVariable('form', $form);
            return $view;

        }else{
            return $this->redirect()->toRoute('admin/roles-manager-import', ['action' => 'browse']);
        }

    }

    public function importAction()
    {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $path = $this->getConf('imports');
            $post = $request->getPost()->toArray();

            $max_line_length = 10000;
            $rowc = 0;
            $impc = 0;

            if (($handle = fopen($path.$post['file-import'].'.csv', "r")) !== FALSE) {

                $fnimp = $path.$post['file-import'].'.imp';
                if($post['type-import'] == 'roles'){
                    file_put_contents($fnimp, "roles\r\n");
                }elseif($post['type-import'] == 'users'){
                    file_put_contents($fnimp, "users|email\r\n");
                }

                while (($rows = fgetcsv($handle, $max_line_length, $post['delimiter'], $post['enclosure'], $post['escape'])) !== false) {
                    if(!empty($rows)){
                        $data = [];
                        $aopsval = [];
                        $count_rows = count($rows);
                        if($rowc > 0){
                            if($post['type-import'] == 'roles'){
                                // $form = $this->getForm(RoleAddForm::class);
                                $tpl = $this->api()->searchOne('roles', ['name' => $post['parent-role']]);
                                if($tpl->getTotalResults()){
                                    $trplrole = $tpl->getContent()->jsonSerialize();
                                    $data['o:options'] = $trplrole['o:options'];
                                    if(!empty($post['parent-role-is-template'])){
                                        $data['o:allow'] = $trplrole['o:allow'];
                                        $data['o:deny'] = $trplrole['o:deny'];
                                    }else{
                                        $data['o:parent'] = $post['parent-role'];
                                    }
                                }else{
                                    $data['o:parent'] = $post['parent-role'];
                                }
                                $data['o:active'] = True;
                                $data['o:created'] = True;
                            }elseif($post['type-import'] == 'users'){
                                $data['o:is_active'] = True;
                            }
                            for ($c=0; $c < $count_rows; $c++) {
                                $q = [];
                                $allowedItemSets = [];
                                if(stripos($rows[$c], $post['mval-separator']) !== False){
                                    $vals = explode($post['mval-separator'], $rows[$c]);
                                    $count_vals = count($vals);
                                    for ($v=0; $v < $count_vals; $v++) {
                                        if($post['row-relation-'.$c] == 'item_sets'){
                                            $q['property'][$v]['joiner'] = 'or';
                                            $q['property'][$v]['property'] = $post['find_item_sets_by'];
                                            $q['property'][$v]['type'] = 'eq';
                                            $q['property'][$v]['text'] = trim($vals[$v]);
                                        }
                                    }
                                }else{
                                    if(in_array($post['row-relation-'.$c], ['name', 'label', 'email'])){
                                        $data['o:'.$post['row-relation-'.$c]] = trim($rows[$c]);
                                    }
                                    if(in_array($post['row-relation-'.$c], ['role'])){
                                        $data['o:role'] = trim($rows[$c]);
                                    }
                                    if(!empty($aops = $this->getSets('addition_user_information'))){
                                        if(in_array($post['row-relation-'.$c], array_keys($aops)) && !empty($rows[$c])){
                                            $aopsval[$post['row-relation-'.$c]] = trim($rows[$c]);
                                        }
                                    }
                                    if($post['row-relation-'.$c] == 'item_sets'){
                                        // $q['property'][0]['joiner'] = 'or';
                                        $q['property'][0]['property'] = $post['find_item_sets_by'];
                                        $q['property'][0]['type'] = 'eq';
                                        $q['property'][0]['text'] = trim($rows[$c]);
                                    }
                                }

                                if($post['row-relation-'.$c] == 'item_sets'){
                                    $itemSets = $this->api()->search('item_sets', $q)->getContent();
                                    if(!empty($itemSets)){
                                        foreach ($itemSets as $itemSet){
                                            $allowedItemSets[] = $itemSet->id();
                                        }
                                        $data['o:options']['o:allowed_item_sets'] = $allowedItemSets;
                                    }
                                }

                            }

                            if($post['type-import'] == 'roles'){
                                $response = $this->api()->create('roles', $data);
                                if ($response){
                                    file_put_contents($fnimp, $response->getContent()->id()."\r\n", FILE_APPEND);
                                    $impc++;
                                    // $response = $this->api($form)->create('roles', $data);
                                    // if (!empty($trplrole['listPermissions'])){
                                    //     $this->addPermissions($response->getContent()->id(), $trplrole['listPermissions']);
                                    // }
                                }
                            }elseif($post['type-import'] == 'users'){
                                if(!empty($post['find_role_by'])){
                                    $sq = [];
                                    if(in_array($post['find_role_by'], ['name', 'label'])){
                                        $sq['name'] = $data['o:role'];
                                    }else{
                                        // if(!empty($aops = $this->getSets('addition_role_information'))){
                                        //     foreach($aops as $aoname => $aolabel){
                                        //         $findRoleBy[$aoname] = $aolabel;
                                        //     }
                                        // }
                                    }
                                    $existRole = $this->api()->searchOne('roles', $sq);
                                    if(!$existRole->getTotalResults()){
                                        $data['o:role'] = $post['parent-role'];
                                    }
                                }else{
                                    $data['o:role'] = $post['parent-role'];
                                }

                                if(empty($existRole)){
                                    $sq['name'] = $data['o:role'];
                                    $existRole = $this->api()->searchOne('roles', $sq);
                                }

                                $response = $this->api()->create('users', $data);
                                if($response){
                                    $impc++;
                                    $userID = $response->getContent()->id();
                                    file_put_contents($fnimp, $userID.'|'.$response->getContent()->email()."\r\n", FILE_APPEND);
                                    if(!empty($aopsval)){
                                        foreach($aopsval as $avname => $avlabel){
                                            $this->setUserSets($userID, $avname,  $avlabel);
                                        }
                                    }
                                    $sets = $existRole->getContent()->options();
                                    if(!empty($sets['o:allowed_resource_template']) && (count($sets['o:allowed_resource_template']) == 1 || !empty($sets['o:hide_default_resource_template']))){
                                        $this->getUserSettings()->set('default_resource_template', current($sets['o:allowed_resource_template']), $userID);
                                    }
                                    if(!empty($sets['o:allowed_item_sets']) && (count($sets['o:allowed_item_sets']) == 1 || !empty($sets['o:remove_browse_defaults_admin_item_sets']))){
                                        $this->getUserSettings()->set('default_item_sets', current($sets['o:allowed_item_sets']), $userID);
                                    }
                                    if(!empty($sets['o:allowed_item_sites']) && (count($sets['o:allowed_item_sites']) == 1 || !empty($sets['o:remove_browse_defaults_admin_sites']))){
                                        $this->getUserSettings()->set('default_item_sites', current($sets['o:allowed_item_sites']), $userID);
                                    }
                                }

                            }

                            $result['data'][$rowc] = $data;

                        }
                        $rowc++;
                    }
                }
                fclose($handle);
            }
            
            // $result['post'] = $post;
            $result['imported'] = $impc;
            $result['count'] = $rowc;
            // $result['head'] = $head;
            // $result['data'] = $data;

            // $form = $this->getForm(Form::class);
            $view = new ViewModel;
            // $view->setVariable('form', $form);
            $view->setVariable('result', $result);
            return $view;

        }else{
            return $this->redirect()->toRoute('admin/roles-manager-import', ['action' => 'browse']);
        }

    }

    public function uploadingAction()
    {
        $httpResponse = $this->getResponse();
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $fileData = $this->getRequest()->getFiles()->toArray();
            try {
                $path = $this->getConf('imports');
                if(!file_exists($path)){
                    mkdir($path, 0755, True);
                }
                move_uploaded_file($fileData['file']['tmp_name'], $path.$fileData['file']['name']);
                $this->messenger()->addSuccess('File successfully uploaded.'); // @translate
            } catch (ValidationException $e) {
                $errors = [];
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveArrayIterator(
                        $e->getErrorStore()->getErrors(),
                        RecursiveArrayIterator::CHILD_ARRAYS_ONLY
                    )
                );
                foreach ($iterator as $error) {
                    $errors[] = $this->translate($error);
                }
            }
        } else {
            // $httpResponse->setContent(json_encode([$this->translate('Asset uploads must be POSTed.')]));
            // $httpResponse->setStatusCode(405);
        }
        return $this->redirect()->toRoute('admin/roles-manager-import', ['action' => 'browse']);

    }

    public function deleteAction()
    {

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $name = $this->params('name');
                $path = $this->getConf('imports');
                if (unlink($path.$name.'.csv')) {
                    if (file_exists($path.$name.'.imp')) {
                        unlink($path.$name.'.imp');
                    }
                    $this->messenger()->addSuccess('File successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/roles-manager-import', ['action' => 'browse']);

    }

    public function deleteConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('imports');
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager-import', ['action' => 'delete', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setTemplate('roles-manager/admin/import/delete-confirm');
        return $view->setTerminal(true);

    }

    
    public function undoAction()
    {

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $name = $this->params('name');
                $path = $this->getConf('imports');
                if(file_exists($path.$name.'.imp')){
                    $cont = file_get_contents($path.$name.'.imp');
                    if(!empty($cont)){
                        $conts = explode("\r\n", $cont);
                        if(stripos($conts[0], '|') !== False){
                            $head = explode('|', $conts[0]);
                            $type = $head[0];
                        }else{
                            $head = false;
                            $type = $conts[0];
                        }
                        foreach($conts as $k => $v){
                            if($k && $v){
                                if($head){
                                    $a = explode('|', $v);
                                    $rc[$a[0]] = $a[1];
                                }else{
                                    $rc[$v] = $v;
                                }
                            }
                        }
                        $ind = join(',', array_keys($rc));
                        $sql = "SET FOREIGN_KEY_CHECKS=0;";
                        if($type == 'roles'){
                            $sql .= "DELETE FROM `roles` WHERE `id` in ({$ind});";
                        }elseif($type == 'users'){
                            $sql .= "DELETE FROM `user` WHERE `id` in ({$ind});";
                            $sql .= "DELETE FROM `user_setting` WHERE `user_id` in ({$ind});";
                        }
                        $sql .= "SET FOREIGN_KEY_CHECKS=1;";
                        $this->getConnection()->executeStatement($sql);
                        if (file_exists($path.$name.'.imp')) {
                            unlink($path.$name.'.imp');
                        }
                        $this->messenger()->addSuccess('Entries was undo import.'); // @translate
                        
                    }else{

                    }
                }else{

                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/roles-manager-import', ['action' => 'browse']);

    }

    public function undoConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('imports');
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager-import', ['action' => 'undo', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setVariable('path', $path);
        $view->setTemplate('roles-manager/admin/import/undo-confirm');
        return $view->setTerminal(true);

    }

}
