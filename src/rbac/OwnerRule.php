<?php /** @noinspection PhpMissingFieldTypeInspection */
declare(strict_types=1);

namespace sablesoft\stuff\rbac;

use yii\rbac\Item;
use yii\rbac\Rule;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use sablesoft\stuff\interfaces\IsOwnerInterface;

/**
 * Class OwnerRule
 * @package app\models\rbac
 */
class OwnerRule extends Rule {

    public $name = 'isOwner';

    /**
     * @param int|string $userId
     * @param Item $item
     * @param array $params
     * @return bool
     * @throws InvalidConfigException
     */
    public function execute($userId, $item, $params) : bool
    {
        /** @var IsOwnerInterface|ActiveRecord $model */
        if (!$model = ArrayHelper::remove($params, 'model')) {
            return false;
        }

        if (!$model->hasMethod('isOwner')) {
            throw new InvalidConfigException("OwnerRule: model must implement OwnerInterface!");
        }

        return $model->isOwner((string) $userId);
    }
}
