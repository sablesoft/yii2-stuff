<?php declare(strict_types=1);

namespace sablesoft\stuff\interfaces;

/**
 * Interface IsOwnerInterface
 * @package sablesoft\stuff\interfaces
 */
interface IsOwnerInterface
{
    /**
     * @param string|null $ownerKey
     * @return bool
     */
    public function isOwner(string $ownerKey = null) : bool;
}
