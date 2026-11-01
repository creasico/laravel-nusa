<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Creasi\Nusa\Models\Model;

final class SearchableModel extends Model
{
    public static ?string $connectionName = null;

    public static ?string $tableName = null;

    protected $table = 'searchables';

    /**
     * Resolve the connection used by the test model.
     */
    public function getConnectionName()
    {
        return self::$connectionName ?? parent::getConnectionName();
    }

    /**
     * Resolve the table used by the test model.
     */
    public function getTable()
    {
        return self::$tableName ?? parent::getTable();
    }
}
