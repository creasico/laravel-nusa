<?php

namespace Creasi\Scripts;

use PDO;
use PDOStatement;

class Database
{
    private readonly PDO $conn;

    public function __construct(string $name, string $host, string $user, ?string $pass = null)
    {
        $this->conn = new PDO("mysql:dbname={$name};host={$host}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public function query(string $statement, ?int $mode = null, mixed ...$args): PDOStatement
    {
        return $this->conn->query($statement, $mode, ...$args);
    }

    /**
     * Handle the post-install Composer event.
     *
     * @param  \Composer\Script\Event  $event
     */
    public static function import($event): void
    {
        $libPath = \realpath(\dirname(__DIR__.'/../..'));

        require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

        $db = new static(
            name: env('DB_NAME', 'cahyadsn_wilayah'),
            host: env('DB_HOST', '127.0.0.1'),
            user: env('DB_USER', 'root'),
            pass: env('DB_PASS', 'secret'),
        );

        if ($wilayahSql = file_get_contents($libPath.'/submodules/cahyadsn-wilayah/db/wilayah.sql')) {
            $db->query($wilayahSql);
        }

        if ($pulauSql = file_get_contents($libPath.'/submodules/cahyadsn-wilayah/db/pulau_2022.sql')) {
            $db->query($pulauSql);
        }

        if ($coordinatesSql = file_get_contents($libPath.'/submodules/cahyadsn-wilayah/db/wilayah_level_1_2.sql')) {
            $db->query($coordinatesSql);
        }
    }
}
