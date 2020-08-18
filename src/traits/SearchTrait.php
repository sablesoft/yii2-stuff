<?php declare(strict_types=1);

namespace sablesoft\stuff\traits;

use yii\db\Expression;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use sablesoft\stuff\interfaces\SearchInterface;

/**
 * Trait SearchTrait
 * @package sablesoft\stuff\traits
 * @property array $filters
 */
trait SearchTrait
{
    /**
     * @param ActiveQuery $query
     */
    public function applyFilters(ActiveQuery $query)
    {
        foreach ($this->filters as $operator => $fields) {
            foreach($fields as $field => $dbField) {
                if (is_int($field)) {
                    $field = $dbField;
                }
                $query->andFilterWhere([$operator, $dbField, $this->$field]);
            }
        }
    }

    /**
     * @param ActiveQuery $query
     * @param string $field
     * @param string $elseOperator
     */
    public function nullFilter(ActiveQuery $query, string $field, string $elseOperator = 'eq')
    {
        if ($this->$field === SearchInterface::FILTER_IS_NULL) {
            $query->andFilterWhere(['is', $field, new Expression('null')]);
        } else {
            $query->andFilterWhere([$elseOperator, $field, $this->$field]);
        }
    }

    /**
     * @param ActiveQuery $query
     * @param string $column
     * @return array|null[]
     */
    protected function fetchColumn(ActiveQuery $query, $column = 'id') : array
    {
        $raw = $query->asArray()->all();
        $values = ArrayHelper::getColumn($raw, $column);
        return $values ? $values : [null];
    }
}
