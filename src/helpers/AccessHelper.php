<?php declare(strict_types=1);

namespace sablesoft\stuff\helpers;

use Yii;
use yii\web\Controller;
use yii\base\InvalidConfigException;
use sablesoft\stuff\traits\ParamsTrait;
use sablesoft\stuff\interfaces\ParamsInterface;

/**
 * Class AccessHelper
 * @package sablesoft\stuff\helper
 */
class AccessHelper implements ParamsInterface
{
    use ParamsTrait;

    const PARAMS = 'access';
    const PARAMS_PATTERN = 'pattern';
    const PARAMS_SKIP_MODULE = 'skipModule';
    const PARAMS_SKIP_CONTROLLER = 'skipController';
    const PARAMS_DEFAULT_ACTIONS = 'defaultActions';
    const PARAMS_DOMAINS = 'domains';
    const PARAMS_AREAS = 'areas';
    const PARAMS_ROLES = 'roles';
    const PARAMS_CUSTOM = 'custom';
    const PARAMS_USERS = 'users';

    const DEFAULT_PATTERN = "{module}.{controller}.{action}";

    const ROLE_ADMIN = 'admin';

    const AREA_PROFILE     = 'profile';
    const PERMISSION_ROLE_ASSIGN = 'role.assign';

    /**
     * @return bool
     */
    public static function isConsole() : bool
    {
        return Yii::$app->request->isConsoleRequest;
    }

    /**
     * @return bool
     */
    public static function canAssignRole() : bool
    {
        return self::isConsole() || Yii::$app->user->can(
            static::PERMISSION_ROLE_ASSIGN
        );
    }

    /**
     * @return array
     */
    public static function defaultActions() : array
    {
        return static::get(static::PARAMS_DEFAULT_ACTIONS, []);
    }

    /**
     * @return bool
     */
    public static function isAdmin() : bool
    {
        return self::isConsole() || Yii::$app->user->can(static::ROLE_ADMIN);
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public static function isProfile() : bool
    {
        if (self::isConsole()) {
            return false;
        }

        return strpos(
            Yii::$app->request->getPathInfo(),
            self::AREA_PROFILE) === 0;
    }

    /**
     * @param Controller $sender
     * @return bool
     */
    public static function isSkip(Controller $sender) : bool
    {
        if (static::have($sender->module->id, static::PARAMS_SKIP_MODULE)) return true;
        if (static::have($sender->getUniqueId(), static::PARAMS_SKIP_CONTROLLER)) return true;

        return false;
    }

    /**
     * @param Controller $sender
     * @return string
     */
    public static function getPermission(Controller $sender) : string
    {
        static::prepare();
        /** @noinspection RegExpRedundantEscape */
        return preg_replace(
            ['/\{module}/', '/\{controller}/', '/\{action}/'],
            [$sender->module->id, $sender->getUniqueId(), $sender->action->id],
            static::getPattern($sender)
        );
    }

    /**
     * @param Controller $sender
     * @return string
     */
    protected static function getPattern(Controller $sender) : string
    {
        $key = static::PARAMS_PATTERN;
        $moduleKey = $sender->module->id;
        return static::get("$key.$moduleKey", static::DEFAULT_PATTERN);
    }

    /**
     * @return string|null
     */
    public static function paramsPath() : ?string
    {
        return self::PARAMS;
    }
}
