<?php
namespace RolesManager\Controller\Admin;

use Omeka\Permissions\Acl;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use RolesManager\TraitGeneral;

class IndexController extends AbstractActionController
{

    use TraitGeneral;

    public function browseAction()
    {
        // When the current user is not an admin, filter out sites where the
        // logged in user has no role.
        $siteQuery = [];
        $isAdmin = $this->getAcl()->isAdminRole($this->identity()->getRole());
        if (!$isAdmin && !$this->getCurrentRoleOps('o:allowed_item_sites')) {
            $siteQuery['user_has_role'] = '1';
        }

        $sitesResponse = $this->api()->search('sites', $siteQuery);
        $itemsResponse = $this->api()->search('items', ['limit' => 0, 'owner_id' => '']);
        $itemCount = $itemsResponse->getTotalResults();
        if($this->getServiceLocator()->has('ItemsReview')){
            $ItemsReview = $this->getServiceLocator()->get('ItemsReview');
            if($ItemsReview->roleIsContributor()){
                $myItemsResponse = $this->api()->search('items', ['owner_id' => $this->getCurrentUserID()]);
                $myItemCount = $myItemsResponse->getTotalResults();
                $itemCount = $this->translate('Your').': '.$myItemCount.' / '.$this->translate('Total').': '.$itemCount;
            }
        }

        $itemSetsResponse = $this->api()->search('item_sets', ['limit' => 0]);
        $vocabulariesResponse = $this->api()->search('vocabularies', ['limit' => 0]);
        $resourceTemplatesResponse = $this->api()->search('resource_templates', ['limit' => 0]);

        $itemSetCount = $itemSetsResponse->getTotalResults();
        $allowed = $this->getCurrentRoleOps('o:allowed_item_sets');
        if(!empty($allowed) && is_array($allowed)){
            $allowedItemSetsResponse = $this->api()->search('item_sets', ['id' => join(',', $allowed)]);
            $allowedItemSetsCount = $allowedItemSetsResponse->getTotalResults();
            $itemSetCount = $this->translate('Your').': '.$allowedItemSetsCount.' / '.$this->translate('Total').': '.$itemSetCount;
        }

        $view = new ViewModel;
        $view->setVariable('sites', $sitesResponse->getContent());
        $view->setVariable('itemCount', $itemCount);
        $view->setVariable('itemSetCount', $itemSetCount);
        $view->setVariable('vocabularyCount', $vocabulariesResponse->getTotalResults());
        $view->setVariable('resourceTemplateCount', $resourceTemplatesResponse->getTotalResults());
        $view->setTemplate('omeka/admin/index/browse');
        return $view;
    }

    public function linkedResourcesAction()
    {
        $resource = $this->api()->read('resources', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('resource', $resource);
        $view->setTemplate('omeka/admin/index/linked-resources');
        return $view;
    }
}
