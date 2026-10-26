<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : How many daily backups to keep}';
    protected $description = 'Back up the MySQL database to a gzipped .sql file using pure PHP (no mysqldump needed).';

    public function handle(): int
    {
        $tables = array_map(fn ($r) => array_values((array) $r)[0], DB::select('SHOW TABLES'));
        $sql = "-- Shani CMS backup ".now()->toDateTimeString()."\nSET FOREIGN_KEY_CHECKS=0;\n";

        foreach ($tables as $table) {
            $create = (array) DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $sql .= "\nDROP TABLE IF EXISTS `{$table}`;\n".end($create).";\n";

            foreach (DB::table($table)->get() as $row) {
                $vals = array_map(
                    fn ($v) => is_null($v) ? 'NULL' : "'".addslashes((string) $v)."'",
                    (array) $row
                );
                $sql .= "INSERT INTO `{$table}` VALUES (".implode(',', $vals).");\n";
            }
        }
        $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

        $name = 'backups/db-'.now()->format('Y-m-d-His').'.sql.gz';
        Storage::disk('local')->put($name, gzencode($sql, 6));
        $this->info('Backup written: '.$name);

        $keep = (int) $this->option('keep');
        collect(Storage::disk('local')->files('backups'))
            ->filter(fn ($f) => str_ends_with($f, '.sql.gz'))->sortDesc()->values()
            ->slice($keep)->each(fn ($f) => Storage::disk('local')->delete($f));

        return self::SUCCESS;
    }
}
