<?php declare(strict_types=1);

namespace sablesoft\stuff\helpers;

/**
 * Class DateTimeHelper
 * @package app\helper
 */
class DateTimeHelper
{
    /**
     * @param string $timeZone
     * @return string
     */
	public static function now(string $timeZone = 'Utc') : string
	{
		$dateTime = new \DateTime('now');
        $dateTime = $dateTime->setTimezone(new \DateTimeZone($timeZone));
		return $dateTime->format('Y-m-d H:i:s');
	}

    /**
     * @param int $timestamp
     * @param string $timeZone
     * @return string
     */
    public static function timestampNow(int $timestamp, string $timeZone = 'Utc') : string
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timestamp);
        $dateTime = $dateTime->setTimezone(new \DateTimeZone($timeZone));
        return $dateTime->format('Y-m-d H:i:s');
    }
}
