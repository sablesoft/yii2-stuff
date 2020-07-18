<?php declare(strict_types=1);

namespace sablesoft\stuff\widgets;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use sablesoft\stuff\traits\ParamsTrait;
use sablesoft\stuff\interfaces\ParamsInterface;

/**
 * Class Menu
 * @package sablesoft\stuff\widgets
 */
class Menu extends Nav implements ParamsInterface  {

    use ParamsTrait;

    const PARAMS = 'menu';

    const CONFIG_MENU   = '_menu';
    const CONFIG_SUBMIT = '_submit';
    const CONFIG_ACCESS = '_access';
    const CONFIG_DIVIDER = '_divider';
    const CONFIG_LABEL = 'label';
    const CONFIG_URL = 'url';
    const CONFIG_ITEMS = 'items';

    const PLACEHOLDER_USERNAME = '_username';

    /**
     * @return array
     */
    public static function items() : array {
        $params = self::get();
        $items = static::prepareItems($params);
        return $items ? $items[self::CONFIG_ITEMS] : [];
    }

    /**
     * @param $config
     * @param null $oldKey
     * @return array|mixed
     */
    protected static function prepareItems($config, $oldKey = null) {
        $items = [];
        foreach ((array) $config as $key => $subConfig) {
            if ($key === self::CONFIG_SUBMIT) {
                return self::submitButton($subConfig);
            }
            if ($key === self::CONFIG_MENU) {
                $items = $subConfig;
                foreach($items as $field => $value) {
                    if($field == self::CONFIG_LABEL) {
                        $items[$field] = ($value === self::PLACEHOLDER_USERNAME) ?
                            Yii::$app->user->identity->username :
                            Yii::t('app', $value);
                    }
                }
                continue;
            }
            if (strpos($key, self::CONFIG_DIVIDER) !== false) {
                $items[self::CONFIG_ITEMS][] = "<li class='divider'></li>";
                continue;
            }
            if (!static::checkAccess($subConfig, $key, $oldKey )) {
                continue;
            }
            $items[self::CONFIG_ITEMS][] = static::prepareItems(
                $subConfig,
                trim( "$oldKey.$key", '.' )
            );
        }

        return $items;
    }

    /**
     * @param array $config
     * @return string
     */
    protected static function submitButton(array $config): string
    {
        $options = $config['options'] ?? [];
        return '<li>'
            . Html::beginForm(Url::to($config[self::CONFIG_URL]), 'post')
            . Html::submitButton(
                Yii::t('app', $config[self::CONFIG_LABEL]),
                $options
            )
            . Html::endForm()
            . '</li>';
    }

    /**
     * @param array $config
     * @param string $key
     * @param null|string $oldKey
     * @return bool
     */
    protected static function checkAccess(array &$config, string $key, $oldKey = null) {
        $default = $oldKey ?  "$oldKey.$key" : $key;
        $permission = ArrayHelper::remove($config, self::CONFIG_ACCESS, $default);
        if ($permission === '@') {
            return !Yii::$app->user->isGuest;
        }
        if ($permission === '?') {
            return Yii::$app->user->isGuest;
        }

        return Yii::$app->user->can($permission);
    }

    /**
     * @inheritDoc
     */
    public static function paramsPath(): ?string
    {
        return self::PARAMS;
    }
}
