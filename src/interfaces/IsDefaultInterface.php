<?php declare(strict_types=1);

namespace sablesoft\stuff\interfaces;

/**
 * Interface IsDefaultInterface
 * @package sablesoft\stuff\interfaces
 *
 * @property bool $isDefault
 */
interface IsDefaultInterface
{
    const FIELD = 'is_default';
    const IS_DEFAULT_FIELD = 'isDefaultField';

    /**
     * @return bool
     */
    public function getIsDefault() : bool;

    /**
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault(bool $isDefault) : self;
}
