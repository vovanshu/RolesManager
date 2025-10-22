<?php declare(strict_types=1);

namespace RolesManager\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Omeka\Entity\AbstractEntity;

/**
 * A table with the name `roles` may create an issue in Doctrine 2, so "roles"
 * is used to avoid quoting all queries.
 * @link https://stackoverflow.com/questions/14080720/doctrine2-does-not-escape-table-name-on-scheme-update
 * @link https://github.com/doctrine/doctrine2/issues/4247
 * @link https://github.com/doctrine/doctrine2/issues/5874
 *
 * @Entity
 * @Table(
 *     name="`roles`"
 * )
 */
class Roles extends AbstractEntity
{
    /**
     * @var int
     *
     * @Id
     * @Column(
     *     type="integer"
     * )
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     *
     * Note: The limit of 190 is related to the format of the base (utf8mb4) and
     * to the fact that there is an index and the max index size is 767, so
     * 190 x 4 = 760.
     *
     * @Name
     * @Column(
     *     length=190,
     *     unique=true
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * Note: The limit of 190 is related to the format of the base (utf8mb4) and
     * to the fact that there is an index and the max index size is 767, so
     * 190 x 4 = 760.
     *
     * @Label
     * @Column(
     *     length=190,
     *     unique=false
     * )
     */

    protected $label;

    /**
     * @var int
     *
     * @Active
     * @Column(
     *     type="integer"
     * )
     */
    protected $active;

    /**
     * @var int
     *
     * @Created
     * @Column(
     *     type="integer"
     * )
     */
    protected $created;

    /**
     * @var string
     *
     * Note: The limit of 190 is related to the format of the base (utf8mb4) and
     * to the fact that there is an index and the max index size is 767, so
     * 190 x 4 = 760.
     *
     * @Parent
     * @Column(
     *     length=190,
     *     nullable=true
     * )
     */
    protected $parent;

    /**
     * @Column(
     *     name="`options`",
     *     type="text",
     *     nullable=true
     * )
     */
    protected $options;

    /**
     * @Column(
     *     name="`allow`",
     *     type="text",
     *     nullable=true
     * )
     */
    protected $allow;

    /**
     * @Column(
     *     name="`deny`",
     *     type="text",
     *     nullable=true
     * )
     */
    protected $deny;

    /* *
     * This relation cannot be set in the core, so it is not a doc block.
     *
     * @var ArrayCollection|Permissions[]
     *
     * Many Roles have Many Users.
     * @ManyToMany(
     *     targetEntity="RolesManager\Entity\RolesPermissions",
     *     mappedBy="role",
     *     inversedBy="permissions"
     * )
     * @JoinTable(
     *     name="role_user",
     *     joinColumns={
     *         @JoinColumn(
     *             name="role_id",
     *             referencedColumnName="id",
     *             onDelete="cascade",
     *             nullable=false
     *         )
     *     },
     *     inverseJoinColumns={
     *         @JoinColumn(
     *             name="user_id",
     *             referencedColumnName="id",
     *             onDelete="cascade",
     *             nullable=false
     *         )
     *     }
     * )
     */
    protected $permissions;

    /**
     * Because the relation cannot be annotated for the users in the core, the
     * join relation is declared here. This property is available only in orm,
     * not in Omeka S.
     *
     * @var ArrayCollection|RoleUser[]
     *
     * One Role has Many relations to User via RoleUsers.
     * @OneToMany(
     *     targetEntity="RolesManager\Entity\RoleUser",
     *     mappedBy="role",
     *     cascade={"persist", "remove"}
     * )
     */
    // protected $roleUsers;


    public function __construct()
    {
        // $this->users = new ArrayCollection();
        // $this->roleUsers = new ArrayCollection();
        // $this->resources = new ArrayCollection();
        // $this->permissions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setLabel($label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setActive($active): self
    {
        $this->active = (bool) $active;
        return $this;
    }

    public function getActive(): bool
    {
        return (bool) $this->active;
    }

    public function setCreated($created): self
    {
        $this->created = (bool) $created;
        return $this;
    }

    public function getCreated(): bool
    {
        return (bool) $this->created;
    }

    public function setParent($parent): self
    {
        $this->parent = (string) $parent;
        return $this;
    }

    public function getParent(): string
    {
        return (string) $this->parent;
    }

    // public function setPermissions($permissions): self
    // {
    //     $this->permissions = json_encode($permissions);
    //     return $this;
    // }
    //
    // public function getPermissions(): array
    // {
    //     if($rc = $this->permissions){
    //         return json_decode($rc, True);
    //     }
    //     return [];
    // }

    public function setOptions($options): self
    {
        if(!empty($options)){
            $this->options = json_encode($options);
        }else{
            $this->options = Null;
        }
        return $this;
    }

    public function getOptions(): array
    {
        if(!empty($rc = $this->options)){
            return json_decode($rc, True);
        }
        return [];
    }

        public function setAllow($allow): self
    {
        if(!empty($allow)){
            $this->allow = json_encode($allow);
        }else{
            $this->allow = Null;
        }
        return $this;
    }

    public function getAllow(): array
    {
        if(!empty($rc = $this->allow)){
            return json_decode($rc, True);
        }
        return [];
    }

    public function setDeny($deny): self
    {
        if(!empty($deny)){
            $this->deny = json_encode($deny);
        }else{
            $this->deny = Null;
        }
        return $this;
    }

    public function getDeny(): array
    {
        if(!empty($rc = $this->deny)){
            return json_decode($rc, True);
        }
        return [];
    }

    // public function getPermissions(): array
    // {
    //     if(!empty($this->permissions))
    //         return $this->permissions;
    //     return [];
    // }

}
