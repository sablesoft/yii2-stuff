<?php declare(strict_types=1);

namespace sablesoft\stuff\commands;

use yii\rbac\Role;
use yii\base\Exception;
use yii\rbac\Permission;
use yii\console\Controller;
use sablesoft\stuff\helpers\AccessHelper;

/**
 * Class RbacController
 * @package sablesoft\stuff\commands
 */
class RbacController extends Controller
{
    const CONFIG_RULE = 'rule';
    const CONFIG_DESC = 'desc';
    const CONFIG_CHILD = 'child';

    /**
     * Init users roles
     *
     * @throws \Exception
     */
    public function actionInit()
    {
        $admin = $this->createAdmin();
        $this->stdout("Admin role installed!\r\n");
        $this->createAreas($admin);
        $this->stdout("App areas permissions installed!\r\n");
        $this->createCustom();
        $this->stdout("App custom permissions installed!\r\n");
        $this->createRoles();
        $this->stdout("App custom roles installed and configured!\r\n");
    }

    /**
     * @return Role
     * @throws \Exception
     */
    protected function createAdmin() : Role
    {
        $auth = \Yii::$app->authManager;
        $admin = $auth->getRole(AccessHelper::ROLE_ADMIN);
        if (!$admin) {
            $admin = $auth->createRole(AccessHelper::ROLE_ADMIN);
            $admin->description = "Super admin role";
            $auth->add($admin);
        }
        return $admin;
    }

    /**
     * @param Role $admin
     * @throws Exception
     */
    protected function createAreas(Role $admin) : void
    {
        $auth = \Yii::$app->authManager;
        foreach (AccessHelper::get(AccessHelper::PARAMS_AREAS, []) as $key => $actions) {
            $name = is_string($key) ? $key : $actions;
            $actions = is_array($actions) ? $actions : AccessHelper::defaultActions();
            $permission = $this->areaPermissions($name, $actions);
            if (!$auth->hasChild($admin, $permission)) {
                $auth->addChild($admin, $permission);
            }
        }
    }

    /**
     * @param string $area
     * @param array $actions
     * @return Permission
     * @throws Exception
     * @throws \Exception
     */
    protected function areaPermissions(string $area, array $actions) : Permission {
        $auth = \Yii::$app->authManager;
        $parent = $auth->getPermission($area);
        if (!$parent) {
            $parent = $auth->createPermission($area);
            $parent->description = ucfirst($area) . " area";
            $auth->add($parent);
        }
        foreach ($actions as $action) {
            $permission = $auth->getPermission("$area.$action");
            if (!$permission) {
                $permission = $auth->createPermission("$area.$action");
                $permission->description = ucfirst($area) . " $action action";
                $auth->add($permission);
            }
            if (!$auth->hasChild($parent, $permission)) {
                $auth->addChild($parent,$permission);
            }
        }

        return $parent;
    }

    /**
     * @throws \Exception
     */
    protected function createCustom() : void
    {
        $this->_createCustom(AccessHelper::get(AccessHelper::PARAMS_CUSTOM, []));
    }

    /**
     * @param array $custom
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    protected function _createCustom(array $custom) : array
    {
        $permissions = [];
        $auth = \Yii::$app->authManager;
        foreach ($custom as $name => $config) {
            if (is_int($name) && is_string($config)) {
                $name = $config;
                $config = [];
            }
            $permission = $auth->getPermission($name);
            $isNew = !$permission;
            if ($isNew) {
                $permission = $auth->createPermission($name);
            }
            $permission->description = $config[self::CONFIG_DESC] ?? null;
            $permission->ruleName = $config[self::CONFIG_RULE] ?? null;
            $isNew ? $auth->add($permission) : $auth->update($name, $permission);
            if (!empty($config[self::CONFIG_CHILD])) {
                $children = $this->_createCustom((array) $config[self::CONFIG_CHILD]);
                foreach ($children as $child) {
                    if (!$auth->hasChild($permission, $child)) {
                        $auth->addChild($permission, $child);
                    }
                }
            }
            $permissions[] = $permission;
        }

        return $permissions;
    }

    /**
     * @throws \Exception
     */
    protected function createRoles() : void
    {
        $auth = \Yii::$app->authManager;
        foreach (AccessHelper::get(AccessHelper::PARAMS_ROLES, []) as $name => $permissions) {
            $role = $auth->getRole($name);
            if (!$role) {
                $role = $auth->createRole($name);
                $auth->add($role);
            }
            foreach ($permissions as $name) {
                $permission = $auth->getPermission($name);
                if (!$permission) {
                    throw new Exception("Invalid role permission config!");
                }
                if (!$auth->hasChild($role, $permission)) {
                    $auth->addChild($role, $permission);
                }
            }
        }
    }
}
