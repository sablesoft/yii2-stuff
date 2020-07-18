<?php declare(strict_types=1);

namespace sablesoft\stuff\interfaces;

use yii\db\ActiveQuery;
use yii\data\ActiveDataProvider;

/**
 * Interface SearchInterface
 * @package sablesoft\stuff\interfaces
 *
 * @property array $filters
 */
interface SearchInterface {

    const FILTER_IS_NULL = '_null';

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params) : ActiveDataProvider;

    /**
     * @return array
     */
    public function getFilters() : array;

    /**
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public function applyFilters(ActiveQuery $query);

    /**
     * @param ActiveQuery $query
     * @param string $field
     * @param string $elseOperator
     * @return ActiveQuery
     */
    public function nullFilter(ActiveQuery $query, string $field, string $elseOperator = 'eq');
}
