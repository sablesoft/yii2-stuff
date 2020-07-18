<?php declare(strict_types=1);

namespace sablesoft\stuff\interfaces;

use yii\db\ActiveQuery;

/**
 * Interface DateTimeInterface
 * @package sablesoft\stuff\interfaces
 */
interface DateTimeInterface
{
    const FIELD_CREATED     = 'created';
    const FIELD_UPDATED     = 'updated';
    const FIELD_DELETED     = 'deleted';
    const FIELD_LAST_ACTION = 'last_action';

    /**
     * @param string $attribute
     * @param ActiveQuery $query
     */
    public function applyDateFilter(string $attribute, ActiveQuery $query);

    /**
     * @param ActiveQuery $query
     */
    public function applyDateFilters(ActiveQuery $query);

    /**
     * @return string|null
     */
    public function getCreated() : ?string;

    /**
     * @param string $created
     * @return mixed
     */
    public function setCreated(string $created);

    /**
     * @return string|null
     */
    public function getUpdated() : ?string;

    /**
     * @param string $updated
     * @return string|null
     */
    public function setUpdated(string $updated);

    /**
     * @return string|null
     */
    public function getDeleted() : ?string;

    /**
     * @param string $deleted
     * @return mixed
     */
    public function setDeleted(string $deleted);

    /**
     * @return string|null
     */
    public function getLastAction() : ?string;

    /**
     * @param string $lastAction
     * @return mixed
     */
    public function setLastAction(string $lastAction);

    /**
     * @return mixed
     */
    public function updateLastAction();

    /**
     * @return array
     */
    public function getDateFilterFields() : ?array;
}