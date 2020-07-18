<?php declare(strict_types=1);

namespace sablesoft\stuff\traits;

use yii\helpers\ArrayHelper;
use sablesoft\stuff\interfaces\SelectInterface;

/**
 * Trait SelectTrait
 * @package app\models\base
 *
 * @property array $dropDown
 * @method static find()
 */
trait SelectTrait
{
    /**
     * @param array $config
     * @param bool $withParams
     * @return array
     */
    public static function getSelect(array $config = [], bool $withParams = false) : array
    {
        // prepare items:
        $query = static::find();
        $where = !empty($config[SelectInterface::CONFIG_WHERE])?
            $config[SelectInterface::CONFIG_WHERE] : false;
        if(is_array($where)) {
            $query = $query->where($where);
        }
        $models = $query->all();
        $from = !empty($config[SelectInterface::CONFIG_FROM])?
            $config[SelectInterface::CONFIG_FROM] : SelectInterface::DEFAULT_FIELD_FROM;
        $to = !empty($config[DropDownInterface::CONFIG_TO])?
            $config[SelectInterface::CONFIG_TO] : SelectInterface::DEFAULT_FIELD_TO;
        $items = ArrayHelper::map($models, $from, $to);
        if (!$withParams) {
            return $items;
        }

        // prepare params:
        $params = [];
        if (!empty($config['selected'])) {
            $selected = static::find()->where(['is_default' => 1])->one();
            if ($selected->id) {
                $params = [
                    'options' => [
                        $selected->id => ['Selected' => true]
                    ]
                ];
            }
        }
        if (isset($config['prompt'])) {
            $params['prompt'] = $config['prompt'];
        }

        return [$items, $params];
    }
}
