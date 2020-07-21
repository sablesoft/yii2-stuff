<?php declare(strict_types=1);

namespace sablesoft\stuff\interfaces;

/**
 * Interface IsDeletedInterface
 * @package sablesoft\stuff\interfaces
 */
interface IsDeletedInterface
{
    const DELETED_FIELD = 'deletedField';
    const FIELD = 'deleted';

    /**
     * @return bool
     */
    public function getIsDeleted() : bool;
}
