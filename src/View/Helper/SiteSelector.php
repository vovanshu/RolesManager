<?php
namespace RolesManager\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class SiteSelector extends AbstractHelper
{

    public function __invoke()
    {

        $view = $this->getView();
        $common = $view->RolesManagerCommon();

        $query['sort_by'] = 'title';
        if(!empty($ops = $common->getCurrentRoleOps('o:allowed_item_sites'))){
            $query['id'] = $ops;
        }

        $sites = $view->api()->search('sites', $query)->getContent();
        if($common->getCurrentRoleOps('o:withoutowner_site_selector') == 'true' || $common->getSets('withoutowner_site_selector') == 'true'){
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
                    'owner' => $common->getSets('installation_title')
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
