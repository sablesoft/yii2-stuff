<?php declare(strict_types=1);

namespace sablesoft\stuff\db;

use yii\db\ColumnSchemaBuilder;

/**
 * Class Migration
 * @package app\models\base
 */
class Migration extends \yii\db\Migration
{
    const COLUMN_TYPE_BLOB  = 'longblob';
    const COLUMN_TYPE_BIT   = 'bit';

    /**
     * @param string $type
     * @param null $length
     * @return ColumnSchemaBuilder
     */
    public function custom(string $type, $length = null) : ColumnSchemaBuilder
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder($type, $length);
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function bit() : ColumnSchemaBuilder
    {
        return $this->custom(self::COLUMN_TYPE_BIT, 1);
    }

    /**
     * @return ColumnSchemaBuilder
     */
    public function blob() : ColumnSchemaBuilder
    {
        return $this->custom(self::COLUMN_TYPE_BLOB);
    }
}
