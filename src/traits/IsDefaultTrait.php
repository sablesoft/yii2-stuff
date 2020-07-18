<?php /** @noinspection PhpUndefinedClassInspection */
declare(strict_types=1);

namespace sablesoft\stuff\traits;

use yii\db\ActiveRecord;
use sablesoft\stuff\helpers\DateTimeHelper;
use sablesoft\stuff\interfaces\IsDefaultInterface;

/**
 * Trait IsDefaultTrait
 * @package app\models\base
 *
 * @property bool $isDefault
 * @method getOldAttribute(string $attribute)
 * @method updateAttributes(array $attributes)
 * @method addError(string $attribute, string $message)
 * @method static find()
 */
trait IsDefaultTrait
{
    /**
     * @return bool
     */
    public function getIsDefault() : bool
    {
        $field = $this->isDefaultField();
        return (bool) $this->$field;
    }

    /**
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault(bool $isDefault) : self
    {
        $field = $this->isDefaultField();
        $this->$field = $isDefault;

        return $this;
    }

    /**
     * @return string
     */
    protected function isDefaultField() : string
    {
        $property = IsDefaultInterface::IS_DEFAULT_FIELD;
        return property_exists($this, $property) ?
            $this->$property : IsDefaultInterface::FIELD;
    }

    /**
     * @return bool
     */
    protected function checkDefault() : bool
    {
        if (!$this->isDefault && $this->getOldAttribute($this->isDefaultField())) {
            $this->addError($this->isDefaultField(), 'Cannot uncheck default!');
            return false;
        }
        if ($this->isDefault && !$this->getOldAttribute($this->isDefaultField())) {
            /** @var ActiveRecord|IsDefaultInterface $oldDefault */
            $oldDefault = static::find()->where([$this->isDefaultField() => 1])->one();
            $oldDefault->isDefault = false;
            $oldDefault->updateAttributes([
                $this->isDefaultField() => $oldDefault->isDefault,
                'updated'   => DateTimeHelper::now()
            ]);
        }

        return true;
    }
}
