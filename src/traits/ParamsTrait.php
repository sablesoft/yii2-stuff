<?php declare(strict_types=1);

namespace sablesoft\stuff\traits;

use yii\helpers\ArrayHelper;

/**
 * Trait ParamsTrait
 * @package sablesoft\stuff\traits
 */
trait ParamsTrait
{
    /**
     * @var array|null
     */
    protected static ?array $_params = null;

    /**
     * @param string|null $path
     * @return array
     */
    public static function keys(string $path = null) : array
    {
        $params = static::get($path, []);

        return array_keys($params);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function checked(string $path) : bool
    {
        static::prepare();
        return (bool) static::get($path);
    }

    /**
     * @param string|null $path
     * @param null $default
     * @return mixed|null
     */
    public static function get(?string $path = null, $default = null)
    {
        static::prepare();
        if ($path == null) {
            return static::$_params;
        }

        return ArrayHelper::getValue(static::$_params, $path, $default);
    }

    /**
     * @param string $value
     * @param string $path
     * @return bool
     */
    public static function have(string $value, ?string $path = null) : bool
    {
        static::prepare();
        $array = $path ? (array) static::get($path, []) : static::$_params;
        return in_array($value, $array);
    }

    /**
     * Init access params
     */
    protected static function prepare() : void
    {
        if (static::$_params !== null) {
            return;
        }

        $path = static::paramsPath();

        if ($path == null) {
            static::$_params = \Yii::$app->params;
            return;
        }

        static::$_params = !empty(\Yii::$app->params[$path]) ?
            \Yii::$app->params[$path] : [];

        $preparer = static::$preparer ?? null;
        if ($preparer !== null) {
            static::$preparer();
        }
    }
}
