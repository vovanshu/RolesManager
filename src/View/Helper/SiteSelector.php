<?php
namespace RolesManager\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class SiteSelector extends AbstractHelper
{

    public function __invoke()
    {

        $view = $this->getView();
        $RolesManager = $view->RolesManager();

        $query['sort_by'] = 'title';
        if(!empty($ops = $RolesManager->getCurrentRoleOps('o:allowed_item_sites'))){
            $query['id'] = $ops;
        }

        $sites = $view->api()->search('sites', $query)->getContent();
        if($RolesManager->getCurrentRoleOps('o:withoutowner_site_selector') == 'true' || $RolesManager->getSets('withoutowner_site_selector') == 'true'){
            $allowedSites = [];
            foreach ($sites as $site) {
                if ($site->userIsAllowed('can-assign-items')) {
                    $allowedSites[] = $site;
                }
            }
            return $view->partial(
                'roles-manager/common/site-selector',
                [
                    'sites' => $allowedSites,
                    'totalCount' => count($allowedSites),
                    'owner' => $RolesManager->getSets('installation_title')
                ]
            );
        }else{
            $sitesByOwner = [];
            $totalCount = 0;
            foreach ($sites as $site) {
                if ($site->userIsAllowed('can-assign-items')) {
                    $owner = $site->owner();
                    $email = $owner ? $owner->email() : null;
                    $sitesByOwner[$email]['owner'] = $owner;
                    $sitesByOwner[$email]['sites'][] = $site;
                    $totalCount++;
                }
            }
            ksort($sitesByOwner);
            return $view->partial(
                'common/site-selector',
                [
                    'sitesByOwner' => $sitesByOwner,
                    'totalCount' => $totalCount,
                ]
            );
        }

    }

}
