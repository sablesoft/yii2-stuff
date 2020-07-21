<?php declare(strict_types=1);

namespace sablesoft\stuff\traits;

use yii\base\Exception;
use sablesoft\stuff\interfaces\IsDeletedInterface;

/**
 * Trait IsDeletedTrait
 * @package sablesoft\stuff\traits
 * @property bool $isDeleted
 * @method updateAttributes(array $attributes)
 * @method hasAttribute(string $attribute)
 */
trait IsDeletedTrait
{
    /**
     * @return bool
     * @throws Exception
     */
    public function getIsDeleted() : bool
    {
        $field = $this->deletedField();
        if (!$this->hasAttribute($field)) {
            throw new Exception("Invalid deleted field!");
        }
        return (bool) $this->$field;
    }

    /**
     * @param array $condition
     * @param string $deletedField
     * @return mixed
     */
    public static function findDeleted(array $condition, string $deletedField = IsDeletedInterface::FIELD) {
        return static::findOne(array_merge($condition, ['not', $deletedField, null]));
    }

    /**
     * @param array $condition
     * @param string $deletedField
     * @return mixed
     */
    public static function findNotDeleted(array $condition, string $deletedField = IsDeletedInterface::FIELD) {
        return static::findOne(array_merge($condition, [$deletedField => null]));
    }

    /**
     * @return string
     */
    protected function deletedField() : string
    {
        $property = IsDeletedInterface::DELETED_FIELD;
        return property_exists($this, $property) ?
            $this->$property : IsDeletedInterface::FIELD;
    }
}
