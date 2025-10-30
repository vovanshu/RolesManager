<?php declare(strict_types=1);
namespace RolesManager\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;
use RolesManager\Common;

class SettingsController extends AbstractActionController
{

    use Common;

    public function __construct($serviceLocator = Null, $requestedName = Null, $options = Null)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function testAction()
    {

        $r = [];

        $reader = new \Laminas\Config\Reader\Ini;
        $db = $reader->fromFile(OMEKA_PATH . '/config/database.ini');
        
        $link = mysqli_connect($db['host'],$db['user'],$db['password'], $db['dbname']);
        mysqli_query($link, "SET NAMES 'utf8'");

        $rc = mysqli_query($link, "SHOW COLUMNS FROM roles;");

        while($row = mysqli_fetch_assoc($rc)){
            // $r['roles'][] = $row;
            if($row['Field'] == 'allow'){
                $r['fildExists'][] = 'allow';
            }
            if($row['Field'] == 'deny'){
                $r['fildExists'][] = 'deny';
            }
            //     mysqli_query($link, "ALTER TABLE items_review_status DROP COLUMN contract;");

        }

        mysqli_query($link, "ALTER TABLE roles ADD COLUMN IF NOT EXISTS allow longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL;");
        mysqli_query($link, "ALTER TABLE roles ADD COLUMN IF NOT EXISTS deny longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL;");


        $rc = mysqli_query($link, "SHOW TABLES LIKE 'permissions';");
        if(mysqli_fetch_assoc($rc)){
            $r['permissions'] = 'exists';
        }

        $rc = mysqli_query($link, "SHOW TABLES LIKE 'roles_permissions';");
        if(mysqli_fetch_assoc($rc)){
            $r['roles_permissions'] = 'exists';
        }


        $view = new ViewModel;
        $view->setVariable('result', $r);
        return $view;

    }

    public function updoctrineAction()
    {

        if($this->getConf('developing')){
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
        return $this->redirect()->toRoute('admin/roles-manager-settings', ['action' => 'edit']);

    }

    public function editAction()
    {

        $form = $this->getForm(Form::class);

        $form->add([
            'name' => $this->getOps('backup_users'),
            'type' => 'checkbox',
            'options' => [
                'label' => 'Backup data users', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => $this->getOps('backup_users'),
                'value' => $this->getSets('backup_users')
            ],
        ]);

        $form->add([
            'name' => $this->getOps('show_owned'),
            'type' => 'checkbox',
            'options' => [
                'label' => 'At the begin show your owned', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => $this->getOps('show_owned'),
                'value' => $this->getSets('show_owned')
            ],
        ]);

        $form->add([
            'name' => $this->getOps('viewer_can_assign_items'),
            'type' => 'checkbox',
            'options' => [
                'label' => 'Viewer can assign items', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => $this->getOps('viewer_can_assign_items'),
                'value' => $this->getSets('viewer_can_assign_items')
            ],
        ]);
        $form->add([
            'name' => $this->getOps('withoutowner_site_selector'),
            'type' => 'checkbox',
            'options' => [
                'label' => 'List site selector without owner', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => $this->getOps('withoutowner_site_selector'),
                'value' => $this->getSets('withoutowner_site_selector')
            ],
        ]);
        $form->add([
            'name' => $this->getOps('withoutowner_item_set_selector'),
            'type' => 'checkbox',
            'options' => [
                'label' => 'List item set selector without owner', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'id' => $this->getOps('withoutowner_item_set_selector'),
                'value' => $this->getSets('withoutowner_item_set_selector')
            ],
        ]);
        $form->add([
            'name' => $this->getOps('addition_role_information'),
            'type' => 'textarea',
            'options' => [
                'as_key_value' => True,
                'label' => 'Addition role information', // @translate
            ],
            'attributes' => [
                'id' => $this->getOps('addition_role_information'),
                'required' => false,
                'class' => 'textarea',
                'rows' => 12,
                'value' => $this->getSets('addition_role_information', [$this, 'arrayToTextList'])
            ],
        ]);
        $form->add([
            'name' => $this->getOps('addition_user_information'),
            'type' => 'textarea',
            'options' => [
                'as_key_value' => True,
                'label' => 'Addition user information', // @translate
            ],
            'attributes' => [
                'id' => $this->getOps('addition_user_information'),
                'required' => false,
                'class' => 'textarea',
                'rows' => 12,
                'value' => $this->getSets('addition_user_information', [$this, 'arrayToTextList'])
            ],
        ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost()->toArray();
            if(!empty($post[$this->getOps('addition_role_information')])){
                $post[$this->getOps('addition_role_information')] = $this->textListToArray($post[$this->getOps('addition_role_information')]);
            }
            if(!empty($post[$this->getOps('addition_user_information')])){
                $post[$this->getOps('addition_user_information')] = $this->textListToArray($post[$this->getOps('addition_user_information')]);
            }
            foreach($this->getConf('options') as $key){
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

        $options = $this->getConf('options');
        if($this->getSets('backup_users') == 'true'){
            $tables = ['roles', 'user', 'user_setting', 'api_key'];
        }else{
            $tables = ['roles'];
        }
        $path = $this->getConf('backups');
        $r = $this->backuping_data($options, $tables, $path);
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
        return $this->redirect()->toRoute('admin/roles-manager-settings', ['action' => 'backups']);
    }

    public function restoreConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager-settings', ['action' => 'restore', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        $view->setTemplate('roles-manager/admin/settings/restore-confirm');
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
        return $this->redirect()->toRoute('admin/roles-manager-settings', ['action' => 'backups']);

    }

    public function deleteConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/roles-manager-settings', ['action' => 'delete', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        $view->setTemplate('roles-manager/admin/settings/delete-confirm');
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

    private function backuping_data($options, $tables, $path) 
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
        foreach($options as $key => $name){
            $value = $this->getSets($key);
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
