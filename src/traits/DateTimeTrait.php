<?php /** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace sablesoft\stuff\traits;

use Yii;
use Exception;
use yii\db\Expression;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\AttributeBehavior;
use kartik\date\DatePicker;
use sablesoft\stuff\helpers\DateTimeHelper;
use sablesoft\stuff\interfaces\DateTimeInterface;

/**
 * Trait DateTimeTrait
 *
 * @package app\models\base
 *
 * @property string|null $created
 * @property string|null $updated
 * @property string|null $deleted
 * @property string|null $lastAction
 *
 * @property array $dateFilterFields
 * @method hasAttribute(string $attribute)
 */
trait DateTimeTrait
{
    /**
     * @return array|array[]
     */
    public function datetimeBehaviors() : array
    {
        return [
            'created.save' => [
                'class'      => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [$this->getCreatedField()],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [$this->getCreatedField()]
                ],
                'value' => function($event) {
                    $field = $this->getCreatedField();
                    return $this->$field ? $this->$field : DateTimeHelper::now();
                }
            ],
            'updated.save' => [
                'class'      => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [$this->getUpdatedField()],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [$this->getUpdatedField()]
                ],
                'value' => function($event) {
                    return DateTimeHelper::now();
                }
            ]
        ];
    }

    /**
     * @param string $attribute
     * @param bool $asDate
     * @return array
     * @throws Exception
     */
    public function datetimeColumn(string $attribute, $asDate = false) : array
    {
        $format = $asDate ? 'date' : 'datetime';
        return [
            'attribute' => $attribute,
            'format' => $format,
            'filter'  => DatePicker::widget([
                'model' => $this,
                'attribute' => $attribute,
                'options' => [
                    'autocomplete' => 'off',
                    'placeholder' => Yii::t('app','Date') .'...'
                ],
                'pluginOptions' => [
                    'forceParse' => true,
                    'format' => 'yyyy-mm-dd',
                    'autoclose' => true,
                    'todayHighlight' => true
                ]
            ])
        ];
    }

    /**
     * @return string
     */
    protected function getCreatedField() : string
    {
        return property_exists($this, 'fieldCreated') ?
            $this->fieldCreated :
            DateTimeInterface::FIELD_CREATED;
    }

    /**
     * @return string|null
     */
    public function getCreated(): ?string
    {
        $field = $this->getCreatedField();
        return $this->hasAttribute($field) ? $this->$field : null;
    }

    /**
     * @param string $created
     * @return $this
     */
    public function setCreated(string $created)
    {
        $field = $this->getCreatedField();
        if ($this->hasAttribute($field) && !$this->$field) {
            $this->$field = $created;
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getUpdatedField() : string
    {
        return property_exists($this, 'fieldUpdated') ?
            $this->fieldUpdated : DateTimeInterface::FIELD_UPDATED;
    }

    /**
     * @return string|null
     */
    public function getUpdated(): ?string
    {
        $field = $this->getUpdatedField();
        return $this->hasAttribute($field) ? $this->$field : null;
    }

    /**
     * @param string $updated
     * @return $this
     */
    public function setUpdated(string $updated)
    {
        $field = $this->getUpdatedField();
        if ($this->hasAttribute($field)) {
            $this->$field = $updated;
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getDeletedField() : string
    {
        return property_exists($this, 'fieldDeleted') ?
            $this->fieldDeleted : DateTimeInterface::FIELD_DELETED;
    }

    /**
     * @return string|null
     */
    public function getDeleted(): ?string
    {
        $field = $this->getDeletedField();
        return $this->hasAttribute($field) ? $this->$field : null;
    }

    /**
     * @param string $deleted
     * @return $this
     */
    public function setDeleted(string $deleted)
    {
        $field = $this->getDeletedField();
        if ($this->hasAttribute($field) && !$this->$field) {
            $this->$field = $deleted;
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getLastActionField() : string
    {
        return property_exists($this, 'fieldLastAction') ?
            $this->fieldLastAction : DateTimeInterface::FIELD_LAST_ACTION;
    }

    /**
     * @return string|null
     */
    public function getLastAction(): ?string
    {
        $field = $this->getLastActionField();
        return $this->hasAttribute($field) ? $this->$field : null;
    }

    /**
     * @param string $lastAction
     * @return $this
     */
    public function setLastAction(string $lastAction)
    {
        $field = $this->getLastActionField();
        if ($this->hasAttribute($field)) {
            $this->$field = $lastAction;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function updateLastAction()
    {
        $field = $this->getLastActionField();
        if (!$this->hasAttribute($field)) {
            return $this;
        }

        $this->$field = DateTimeHelper::now();
        $this->updateAttributes([$field => $this->$field]);

        return $this;
    }

    /**
     * @param string $attribute
     * @param ActiveQuery $query
     */
    public function applyDateFilter(string $attribute, ActiveQuery $query)
    {
        if(!$this->hasAttribute($attribute) || empty($this->$attribute)) {
            return;
        }

        $date = $this->$attribute;
        $query->andFilterWhere([
            'BETWEEN',
            $attribute,
            $this->dateExpression($date),
            $this->dateExpression($date, false)
        ]);
    }

    /**
     * @param ActiveQuery $query
     */
    public function applyDateFilters(ActiveQuery $query)
    {
        foreach ($this->dateFields() as $field) {
            $this->applyDateFilter($field, $query);
        }
    }

    /**
     * @param $attributes
     * @return int
     */
    public function updateAttributes($attributes)
    {
        $attributes[$this->getUpdatedField()] = DateTimeHelper::now();
        /** @noinspection PhpUndefinedClassInspection */
        $result = parent::updateAttributes($attributes);
        if ($result) {
            $this->afterSave(false, $attributes);
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function dateFields() : array
    {
        return $this->getDateFilterFields() ?? [
            $this->getCreatedField(),
            $this->getUpdatedField(),
            $this->getLastActionField(),
            $this->getDeletedField()
        ];
    }

    /**
     * @param string $date
     * @param bool $begin
     * @return Expression
     */
    protected function dateExpression(string $date, bool $begin = true) : Expression
    {
        $time = $begin ? "00:00:00" : "23:59:59";
        return new Expression("STR_TO_DATE('$date $time', '%Y-%m-%d %H:%i:%s')");
    }
}
