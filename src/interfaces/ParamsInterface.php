<?php declare(strict_types=1);

namespace sablesoft\stuff\interfaces;

/**
 * Interface ParamsInterface
 * @package sablesoft\stuff\interfaces
 */
interface ParamsInterface
{
    /**
     * @return string|null
     */
    public static function paramsPath() : ?string;

    /**
     * @param string|null $path
     * @return array
     */
    public static function keys(string $path = null) : array;

    /**
     * @param string $path
     * @return bool
     */
    public static function checked(string $path) : bool;

    /**
     * @param string|null $path
     * @param null $default
     * @return mixed
     */
    public static function get(?string $path = null, $default = null);

    /**
     * @param string $value
     * @param string|null $path
     * @return bool
     */
    public static function have(string $value, ?string $path = null) : bool;
}
