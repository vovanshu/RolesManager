<?php
namespace RolesManager\View\Helper;

use Laminas\View\Helper\AbstractHelper;
// use Laminas\EventManager\EventManagerAwareTrait;
// use Laminas\EventManager\Event;

/**
 * View helper for rendering the item set selector form control.
 */
class ItemSetSelector extends AbstractHelper
{

    /**
     * Return the item set selector form control.
     *
     * @param bool $includeClosedSets Whether to include closed
     *  sets in the options available from the selector.
     * @return string
     */
    public function __invoke($includeClosedSets = false)
    {

        $view = $this->getView();
        $RolesManager = $view->RolesManager();

        $query['sort_by'] = 'owner_name';

        if(!empty($ops = $RolesManager->getCurrentRoleOps('o:allowed_item_sets'))){
            $query['id'] = $ops;
        }

        if (!$includeClosedSets) {
            $query['is_open'] = true;
        }
        $response = $view->api()->search('item_sets', $query);

        // if(!empty($common->getCurrentRoleOps('o:withoutowner_item_set_selector')) || !empty($common->getSets('withoutowner_item_set_selector'))){
        if($RolesManager->getCurrentRoleOps('o:withoutowner_item_set_selector') == 'true' || $RolesManager->getSets('withoutowner_item_set_selector') == 'true'){
            $query['sort_by'] = 'title';
            $itemSets = [];
            foreach ($response->getContent() as $itemSet) {
                $itemSets[] = $itemSet;
            }
            return $view->partial(
                'roles-manager/common/item-set-selector',
                [
                    'itemSets' => $itemSets,
                    'totalItemSetCount' => $response->getTotalResults(),
                    'owner' => $RolesManager->getSets('installation_title')
                ]
            );
            // $installationTitle = $this->settings->get('installation_title', 'Omeka S');
        }else{
            // Organize items sets by owner.
            $itemSetOwners = [];
            foreach ($response->getContent() as $itemSet) {
                $owner = $itemSet->owner();
                $email = $owner ? $owner->email() : null;
                $itemSetOwners[$email]['owner'] = $owner;
                $itemSetOwners[$email]['item_sets'][] = $itemSet;
            }

            return $view->partial(
                'common/item-set-selector',
                [
                    'itemSetOwners' => $itemSetOwners,
                    'totalItemSetCount' => $response->getTotalResults(),
                ]
            );
        }

    }

}
