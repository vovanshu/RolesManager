<?php declare(strict_types=1);

namespace RolesManager\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
// use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
// use Omeka\Api\Representation\UserRepresentation;
// use Omeka\Entity\AbstractEntity;
// use RolesManager\Entity\Roles;
// use RolesManager\Entity\Permissions;
// use RolesManager\Entity\RolesPermissions;
use RolesManager\Common;
use RolesManager\General;

/**
 * Role representation.
 */
class RoleRepresentation extends AbstractEntityRepresentation
{

    use Common;
    use General;

    protected $countUsers = [];

    // protected $permissions = [];

    public function getControllerName()
    {
        return 'role';
    }

    public function getJsonLdType()
    {
        return 'o-module-roles:Role';
    }

    public function getJsonLd()
    {
        return [
            'o:id' => $this->id(),
            'o:name' => $this->name(),
            'o:label' => $this->label(),
            'o:active' => $this->active(),
            'o:created' => $this->created(),
            'o:parent' => $this->parent(),
            'o:options' => $this->options(),
            'o:allow' => $this->allow(),
            'o:deny' => $this->deny(),
            'countUser' => $this->countUser()
        ];
    }

    public function getReference()
    {
        return new RoleReference($this->resource, $this->getAdapter());
    }

    public function id(): int
    {
        return $this->resource->getId();
    }

    public function name(): string
    {
        return $this->resource->getName();
    }

    public function label(): string
    {
        return $this->resource->getLabel();
    }

    public function active(): bool
    {
        return $this->resource->getActive();
    }

    public function created(): bool
    {
        return $this->resource->getCreated();
    }

    public function parent(): string
    {
        return $this->resource->getParent();
    }

    public function options(): array
    {
        return $this->resource->getOptions();
    }

    public function allow(): array
    {
        return $this->resource->getAllow();
    }

    public function deny(): array
    {
        return $this->resource->getDeny();
    }

    public function getEntity()
    {
        return $this->resource;
    }

    public function countUser($full = False)
    {

        if(empty($this->countUsers)){
            $qb = $this->getConnection()->createQueryBuilder();
            $select['role'] = '`_u`.`role`';
            $select['count'] = 'COUNT(`_u`.`id`) AS "count"';

            $qb
                ->select($select)
                ->from('`user`', '_u')
                ->groupBy('`_u`.`role`')
                ->orderBy('`_u`.`role`', 'ASC');

            $rc = $this->getConnection()->executeQuery($qb, $qb->getParameters());
            if(!empty($rc)){
                $r = $rc->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
                if(!empty($r)){
                    $this->countUsers = $r;
                    foreach($r as $k => $v){
                        $this->countUsers[$k] = $v['count'];
                    }
                }
            }
        }
        if($full){
            return $this->countUsers;
        }
        if(!empty($this->countUsers[$this->name()])){
            return $this->countUsers[$this->name()];
        }
        return;

    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/roles',
            [
                'action' => $action ?: 'show',
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    /**
     * Return the admin URL to the resource browse page for the role.
     *
     * Similar to url(), but with the type of resources.
     *
     * @param string|null $resourceType May be "resource" (unsupported),
     * "item-set", "item", "media" or "user".
     * @param bool $canonical Whether to return an absolute URL
     * @return string
     */
    public function urlToUsers(): string
    {

        $url = $this->getViewHelper('Url');
        return $url(
            'admin/default',
            ['controller' => 'user', 'action' => 'browse'],
            [
                'query' => ['role' => $this->name()],
                'force_canonical' => false,
            ]
        );

    }

}
