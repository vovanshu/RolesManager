<?php declare(strict_types=1);

namespace RolesManager\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use RolesManager\General;

class GeneralPlugin extends AbstractPlugin
{

    use General;

    public function __construct($serviceLocator, $requestedName = Null, array $options = null)
    {
        $this->setServiceLocator($serviceLocator);
    }
    
    public function __invoke()
    {
        return $this;
    }

}
