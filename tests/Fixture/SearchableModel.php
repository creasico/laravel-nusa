<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Creasi\Nusa\Models\Model;

final class SearchableModel extends Model
{
    public static ?string $connectionName = null;

    protected $table = 'searchables';

    public function getConnectionName()
    {
        return self::$connectionName ?? parent::getConnectionName();
    }
}
