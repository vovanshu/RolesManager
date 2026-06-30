<?php declare(strict_types=1);
namespace RolesManager\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;
use RolesManager\TraitGeneral;

class SettingsController extends AbstractActionController
{

    use TraitGeneral;

    public function __construct($serviceLocator = Null, $requestedName = Null, $options = Null)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function updoctrineAction()
    {

        if ($this->isAppDevMode() && $this->userIsAllowed('RolesManager\Controller\Admin\Settings', 'updoctrine')){
            $params = [
                'process' => 'UpdateDoctrine',
            ];
            $this->jobDispatcher()->dispatch(\RolesManager\Job\UpdateDoctrine::class, $params);
            $message = new Message(
                'Update Doctrine Module add to Jobs.' // @translate
            );
            $this->messenger()->addSuccess($message);
        }else{
            $message = new Message(
                'Update Doctrine Module not allowed.' // @translate
            );
            $this->messenger()->addError($message);
        }
        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'edit']);

    }

    public function uplocaletplAction()
    {

        if ($this->isAppDevMode() && $this->userIsAllowed('RolesManager\Controller\Admin\Settings', 'uplocaletpl')){
            $params = [
                'process' => 'UpdateLocaleTemplate',
            ];
            $this->jobDispatcher()->dispatch(\RolesManager\Job\UpdateLocaleTemplate::class, $params);
            $message = new Message(
                'Update Locale template add to Jobs.' // @translate
            );
            $this->messenger()->addSuccess($message);
        }else{
            $message = new Message(
                'Update Locale template not allowed.' // @translate
            );
            $this->messenger()->addError($message);
        }
        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'edit']);

    }

    public function rulesAddAction()
    {

        $name = $this->params('name');
        if($this->getUpdateRolesManagerRules($name)){
            $message = new Message(
                'Rules %s add successfully.', // @translate
                $name
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addSuccess($message);
        }else{
            $message = new Message(
                'Rules %s add failed.', // @translate
                $name
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addError($message);
        }
        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'uprules']);

    }

    public function rulesUpgradeAction()
    {

        $name = $this->params('name');
        if($this->getUpdateRolesManagerRules($name)){
            $message = new Message(
                'Rules %s upgrade successfully.', // @translate
                $name
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addSuccess($message);
        }else{
            $message = new Message(
                'Rules %s upgrade failed.', // @translate
                $name
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addError($message);
        }
        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'uprules']);

    }

    public function rulesUpgradeAllAction()
    {

        $uplist = $this->getUpdateRolesManagerRulesList();
        if(!empty($uplist)){
            if(file_exists($this->modulePath() . '/config/permissions.php')){
                foreach($uplist as $name => $time){
                    $curlist = (include $this->modulePath() . '/config/permissions.php');
                    if(!empty($curlist[$name]) && (strtotime($time) > strtotime($curlist[$name]))){
                        if(!$this->getUpdateRolesManagerRules($name)){
                            $this->messenger()->addError('Upgrade all rules failed!'); // @translate
                            break;
                        }
                    }
                }
                $this->messenger()->addSuccess('Upgrade all rules successfully!'); // @translate
            }else{
                foreach($uplist as $name => $time){
                    if(!$this->getUpdateRolesManagerRules($name)){
                        $this->messenger()->addError('Add all rules failed!'); // @translate
                        break;
                    }
                }
                $this->messenger()->addSuccess('Add all rules successfully!'); // @translate
            }
        }else{
            $this->messenger()->addError('Upgrade or Add all rules failed!'); // @translate
        }

        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'uprules']);

    }


    public function rulesDeleteConfirmAction()
    {

        $name = $this->params('name');
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager/name', ['controller' => 'settings', 'action' => 'rules-delete', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('name', $name);
        $view->setTemplate('roles-manager/admin/settings/rules-delete-confirm');
        return $view->setTerminal(true);

    }

    public function rulesDeleteAction()
    {

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $name = $this->params('name');
                $dest = $this->modulePath() . '/config/permissions/'.$name.'.php';
                if (unlink($dest)) {
                    $this->setCurListPermissions($name);
                    $this->messenger()->addSuccess('File rules successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'uprules']);

    }

    public function uprulesAction()
    {

        $uplist = $this->getUpdateRolesManagerRulesList();
        $curlist = [];
        if(file_exists($this->modulePath() . '/config/permissions.php')){
            $curlist = (include $this->modulePath() . '/config/permissions.php');
        }
        $view = new ViewModel;
        $view->setVariable('update_list', $uplist);
        $view->setVariable('current_list', $curlist);
        $view->setVariable('cache', $this->getConf('imports').'cache-permissions.php');
        return $view;

    }

    private function getUpdateRolesManagerRulesList()
    {
        
        $dest = $this->getConf('imports');
        if(!file_exists($dest)){
            mkdir($dest, 0755, true);
        }
        $dest .= 'cache-permissions.php';
        if(file_exists($dest) && filectime($dest) > time() - 1800){
            $rc = (include $dest);
            if(!empty($rc) && is_array($rc)){
                return $rc;
            }
        }
        $path = $this->getConf('repository_rules');
        $path .= 'permissions.php';
        $cont = file_get_contents($path);
        if(!empty($cont)){
            file_put_contents($dest, $cont);
            $rc = (include $dest);
            if(!empty($rc) && is_array($rc)){
                return $rc;
            }
        }
        return [];

    }

    private function setCurListPermissions($name, $uplist = Null)
    {

        $curlist = [];
        if(file_exists($this->modulePath() . '/config/permissions.php')){
            $curlist = (include $this->modulePath() . '/config/permissions.php');
        }
        $str = "<?php declare(strict_types=1);\r\n\r\nreturn [\r\n";
        if(!empty($curlist)){
            foreach($curlist as $k => $time){
                if(!empty($uplist)){
                    if(!empty($uplist[$k]) && $k == $name){
                        $time = $uplist[$k];
                        unset($uplist[$k]);
                    }
                    $str .= "    '$k' => '$time',\r\n";
                }elseif($k !== $name){
                    $str .= "    '$k' => '$time',\r\n";
                }
            }
        }
        if(!empty($uplist[$name])){
            foreach($uplist as $k => $time){
                if($k == $name){
                    $str .= "    '$k' => '$time',\r\n";
                }
            }
        }
        $str .= "];\r\n";
        file_put_contents($this->modulePath() . '/config/permissions.php', $str);

    }

    private function getUpdateRolesManagerRules($name)
    {
        
        $path = $this->getConf('repository_rules');
        $path .= 'permissions/'.$name.'.php';
        $cont = file_get_contents($path);
        if(!empty($cont)){
            $dest = $this->modulePath() . '/config/permissions';
            if(!file_exists($dest)){
                mkdir($dest, 0755, true);
            }
            $dest .= '/'.$name.'.php';
            if(file_exists($dest)){
                unlink($dest);
            }
            if(file_put_contents($dest, $cont)){
                $uplist = $this->getUpdateRolesManagerRulesList();
                $this->setCurListPermissions($name, $uplist);
                return True;
            }
        }
        return False;

    }

    public function editAction()
    {

        $form = $this->getForm(Form::class);

        $form->add([
            'name' => 'roles_manager_backup_users',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Backup user data', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => 'roles_manager_backup_users',
                'value' => $this->getSets('roles_manager_backup_users')
            ],
        ]);

        $form->add([
            'name' => 'roles_manager_show_owned',
            'type' => 'checkbox',
            'options' => [
                'label' => 'At the begin showing your owned entries', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => 'roles_manager_show_owned',
                'value' => $this->getSets('roles_manager_show_owned')
            ],
        ]);

        $form->add([
            'name' => 'roles_manager_viewer_can_assign_items',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Viewer can assign items', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => 'roles_manager_viewer_can_assign_items',
                'value' => $this->getSets('roles_manager_viewer_can_assign_items')
            ],
        ]);
        $form->add([
            'name' => 'roles_manager_withoutowner_site_selector',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Site selector without owner', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => 'roles_manager_withoutowner_site_selector',
                'value' => $this->getSets('roles_manager_withoutowner_site_selector')
            ],
        ]);
        $form->add([
            'name' => 'roles_manager_withoutowner_item_set_selector',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Item set selector without owner', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => 'roles_manager_withoutowner_item_set_selector',
                'value' => $this->getSets('roles_manager_withoutowner_item_set_selector')
            ],
        ]);
        $form->add([
            'name' => 'roles_manager_addition_role_information',
            'type' => 'textarea',
            'options' => [
                'as_key_value' => True,
                'label' => 'Addition role information', // @translate
            ],
            'attributes' => [
                'id' => 'roles_manager_addition_role_information',
                'required' => false,
                'class' => 'textarea',
                'rows' => 12,
                'value' => $this->getSets('roles_manager_addition_role_information', [$this, 'arrayToTextList'])
            ],
        ]);
        $form->add([
            'name' => 'roles_manager_addition_user_information',
            'type' => 'textarea',
            'options' => [
                'as_key_value' => True,
                'label' => 'Addition user information', // @translate
            ],
            'attributes' => [
                'id' => 'roles_manager_addition_user_information',
                'required' => false,
                'class' => 'textarea',
                'rows' => 12,
                'value' => $this->getSets('roles_manager_addition_user_information', [$this, 'arrayToTextList'])
            ],
        ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost()->toArray();
            if(!empty($post['roles_manager_addition_role_information'])){
                $post['roles_manager_addition_role_information'] = $this->textListToArray($post['roles_manager_addition_role_information']);
            }
            if(!empty($post['roles_manager_addition_user_information'])){
                $post['roles_manager_addition_user_information'] = $this->textListToArray($post['roles_manager_addition_user_information']);
            }
            foreach($this->getConf('settings') as $key => $defval){
                if(isset($post[$key])){
                    $this->setSets($key, $post[$key]);
                }
            }
            $message = new Message(
                'Settings save successfully.' // @translate
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addSuccess($message);
            return $this->redirect()->refresh();
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;

    }

    public function backupsAction()
    {

        $path = $this->getConf('backups');
        $list = glob($path.'*.sql');
        $view = new ViewModel;
        $view->setVariable('list', $list);
        return $view;

    }

    public function backupingAction()
    {

        $settings = $this->getConf('settings');
        if($this->getSets('roles_manager_backup_users') == 'true'){
            $tables = ['roles', 'user', 'user_setting', 'api_key'];
        }else{
            $tables = ['roles'];
        }
        $path = $this->getConf('backups');
        $r = $this->backuping_data($settings, $tables, $path);
        $view = new ViewModel;
        $view->setVariable('result', $r);
        return $view;

    }
    
    public function restoreAction()
    {

        $name = $this->params('name');

        $path = $this->getConf('backups');
        
        if(file_exists($path.$name)){
            $sql = "SET FOREIGN_KEY_CHECKS=0;";
            $sql .= file_get_contents($path.$name);
            $sql .= "SET FOREIGN_KEY_CHECKS=1;";
            try{
                $result = $this->getConnection()->executeStatement($sql);
                $this->messenger()->addSuccess('Restore successfully.'); // @translate
            }catch(\Exception $e){
                $this->getLogger()->err((string) $e);
                $this->messenger()->addError('Restore failed!'); // @translate
            }
        }else{
            $this->messenger()->addError('Restore failed - file no found!'); // @translate
        }
        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'backups']);
    }

    public function restoreConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager/name', ['controller' => 'settings', 'action' => 'restore', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        // $view->setTemplate('roles-manager/admin/settings/restore-confirm');
        return $view->setTerminal(true);

    }

    public function deleteAction()
    {

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $name = $this->params('name');
                $path = $this->getConf('backups');
                if (unlink($path.$name)) {
                    $this->messenger()->addSuccess('File backup successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/roles-manager/default', ['controller' => 'settings', 'action' => 'backups']);

    }

    public function deleteConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager/name', ['controller' => 'settings', 'action' => 'delete', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        // $view->setTemplate('roles-manager/admin/settings/delete-confirm');
        return $view->setTerminal(true);

    }

    public function detailsAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $view = new ViewModel();
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        // $view->setTemplate('roles-manager/admin/settings/delete-confirm');
        return $view->setTerminal(true);

    }

    private function infoAboutBackup($file)
    {

        $content = file_get_contents($file);
        if(stripos($content, 'Begin backup DB') !== False){
            $rc = explode("--\n--  Begin backup DB\n\n\n", $content);
            $r = strtr($rc[0], ["\n" => '<br>', '--' => '']);
        }else{
            $r = 'Information about backup no foud!';
        }
        return $r;

    }

    private function backuping_data($settings, $tables, $path) 
    {

        $time_zone = $this->getSets('time_zone');
        date_default_timezone_set($time_zone);
        $r['timestamp'] = $timestamp = date('Y-m-d H:i:s');
        $dest = $path.date('Y-m-d-H-i-s').'.sql';

        $reader = new \Laminas\Config\Reader\Ini;
        $db = $reader->fromFile(OMEKA_PATH . '/config/database.ini');

        $link = mysqli_connect($db['host'],$db['user'],$db['password'], $db['dbname']);
        mysqli_query($link, "SET NAMES 'utf8'");

        $result = '';
        $result .= "--\n-- Backup Settings\n--\n\n";

        $oi = 1;
        foreach($settings as $name => $defval){
            $value = $this->getSets($name);
            if(!empty($value)){
                if(is_array($value)){
                    $value = json_encode($value);
                }elseif(is_string($value)){
                    $value = strtr($value, ["\r"=> '\r', "\n"=> '\n']);
                    $value = '"'.$value.'"';
                }
                $value = addslashes($value);
                $result .= "DELETE FROM `setting` WHERE `id` = '$name';\n";
                $result .= "INSERT INTO setting VALUES('$name', '$value');\n";
                $totalCount['Settings'] = $oi;
                $oi++;
            }
        }
        $result.="\n\n\n";
        
        foreach($tables as $table)
        {

            $rc = mysqli_query($link, "SELECT * FROM `$table`;");
            $num_fields = mysqli_num_fields($rc);
            $num_rows = mysqli_num_rows($rc);

            $result.= "--\n-- Backup table $table\n--\n\n";
            $result.= 'DROP TABLE IF EXISTS '.$table.';';

            $createTable = mysqli_fetch_row(mysqli_query($link, "SHOW CREATE TABLE `$table`;"));
            $result.= "\n\n".$createTable[1].";\n\n";
            $counter = 1;

            //Over tables
            for ($i = 0; $i < $num_fields; $i++){
            //Over rows
                while($row = mysqli_fetch_row($rc)){   
                    if($counter == 1){
                        $result.= 'INSERT INTO '.$table.' VALUES(';
                    } else{
                        $result.= '(';
                    }

                    //Over fields
                    for($j=0; $j<$num_fields; $j++) 
                    {
                        if(is_string($row[$j])){
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = str_replace("\n","\\n",$row[$j]);
                        }
                        if(isset($row[$j])) {
                            $result.= '"'.$row[$j].'"' ;
                        }else{
                            $result.= '""';
                        }
                        if($j<($num_fields-1)){
                            $result.= ',';
                        }
                    }

                    if($num_rows == $counter){
                        $result.= ");\n";
                    } else{
                        $result.= "),\n";
                    }
                    $counter++;
                }
                $totalCount[$table] = $counter-1;
            }
            $result.="\n\n\n";
        }

        $head = "--    Info about Backup\n--\n--   Timestampe = $timestamp\n\n--   Total count\n";
        foreach($totalCount as $k => $v){
            $r[$k] = $v;
            $head .= "--   $k = $v\n";
        }
        $head .= "--\n--  Begin backup DB\n\n\n";

        $result = $head.$result;
        if(!file_exists($path)){
            mkdir($path, 0755, True);
        }
        if(!file_exists(dirname($path).'/.htaccess')){
            file_put_contents(dirname($path).'/.htaccess', "
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order Allow,Deny
    Deny from all
</IfModule>
");
        }
        file_put_contents($dest, $result);
        return $r;

    }

}
