<?php declare(strict_types=1);

namespace sablesoft\stuff\interfaces;

/**
 * Interface DropDownInterface
 * @package app\interfaces
 */
interface SelectInterface
{
    const DEFAULT_FIELD_FROM    = 'id';
    const DEFAULT_FIELD_TO      = 'name';

    const CONFIG_TO     = 'to';
    const CONFIG_FROM   = 'from';
    const CONFIG_WHERE  = 'where';

    /**
     * @param array $config
     * @param bool $withParams
     * @return array
     */
    public static function getSelect(array $config = [], bool $withParams = false): array;
}
