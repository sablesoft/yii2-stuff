<?php declare(strict_types=1);

namespace sablesoft\stuff\commands;

use yii\helpers\ArrayHelper;
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
        $this->createDomains($admin);
        $this->stdout("App domains installed!\r\n");
        $this->createRoles();
        $this->stdout("App roles installed and configured!\r\n");
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
     * @throws \Exception
     */
    protected function createDomains(Role $admin) : void
    {
        $auth = \Yii::$app->authManager;
        $domains = AccessHelper::get(AccessHelper::PARAMS_DOMAINS, []);
        if (!$domains) {
            $this->createApp($admin);
        }
        foreach ($domains as $domain => $config) {
            $name = ucfirst($domain);
            $permission = $auth->getPermission($domain);
            if (!$permission) {
                $permission = $auth->createPermission($domain);
                $permission->description = "$name domain";
                $auth->add($permission);
            }
            if (!$auth->hasChild($admin, $permission)) {
                $auth->addChild($admin, $permission);
            }
            $this->createAreas($config, $admin, $permission);
            $this->stdout("$name areas permissions installed!\r\n");
            $this->createCustom($config, $domain);
            $this->stdout("$name custom permissions installed!\r\n");

        }
    }

    /**
     * @param Role $admin
     * @throws Exception
     */
    protected function createApp(Role $admin) : void
    {
        $config = AccessHelper::get();
        $this->createAreas($config, $admin);
        $this->stdout("Areas permissions installed!\r\n");
        $this->createCustom($config);
        $this->stdout("Custom permissions installed!\r\n");
    }

    /**
     * @param array $config
     * @param Role $admin
     * @param Permission $domain
     * @throws Exception
     */
    protected function createAreas(array $config, Role $admin, ?Permission $domain = null) : void
    {
        $auth = \Yii::$app->authManager;
        $parent = $domain ?? $admin;
        foreach (ArrayHelper::getValue($config, AccessHelper::PARAMS_AREAS, []) as $key => $actions) {
            $area = is_string($key) ? $key : $actions;
            $area = $domain ? $domain->name .".". $area : $area;
            $actions = is_array($actions) ?
                $actions :
                ArrayHelper::getValue($config, AccessHelper::PARAMS_DEFAULT_ACTIONS, []);
            $permission = $this->areaPermissions($area, $actions);
            if (!$auth->hasChild($parent, $permission)) {
                $auth->addChild($parent, $permission);
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
        $name = preg_replace('/\./', ' ', $area);
        $name = ucfirst($name);
        $parent = $auth->getPermission($area);
        if (!$parent) {
            $parent = $auth->createPermission($area);
            $parent->description = "$name area";
            $auth->add($parent);
        }
        foreach ($actions as $action) {
            $permission = $auth->getPermission("$area.$action");
            if (!$permission) {
                $permission = $auth->createPermission("$area.$action");
                $permission->description = "$name $action action";
                $auth->add($permission);
            }
            if (!$auth->hasChild($parent, $permission)) {
                $auth->addChild($parent,$permission);
            }
        }

        return $parent;
    }

    /**
     * @param string $domain
     * @param array $config
     * @throws Exception
     */
    protected function createCustom(array $config, ?string $domain = null) : void
    {
        $custom = ArrayHelper::getValue($config, AccessHelper::PARAMS_CUSTOM, []);
        $custom = $domain ? [$domain => [self::CONFIG_CHILD => $custom]] : $custom;
        $this->_createCustom($custom);
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
            if ($config[self::CONFIG_DESC]) {
                $permission->description = $config[self::CONFIG_DESC];
            }
            if ($config[self::CONFIG_RULE]) {
                $permission->ruleName = $config[self::CONFIG_RULE];
            }
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
                $role->description = ucfirst($name) . " role";
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
