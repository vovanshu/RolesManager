<?php declare(strict_types=1);

namespace RolesManager\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use RolesManager\Common;

class CommonPlugin extends AbstractPlugin
{

    use Common;

    public function __construct($serviceLocator, $requestedName = Null, array $options = null)
    {
        $this->setServiceLocator($serviceLocator);
    }
    
    public function __invoke()
    {
        return $this;
    }

}
