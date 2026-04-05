<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Avoids Schema::change() which requires pragma_table_xinfo (SQLite >= 3.26.0).
     * Instead we use pragma_table_info (available in all SQLite versions) to check
     * nullability, and recreate the table only when the column is actually NOT NULL.
     */
    public function up(): void
    {
        $columns = DB::select("PRAGMA table_info('requests')");
        $urlColumn = collect($columns)->firstWhere('name', 'url');

        // notnull = 0 means already nullable — nothing to do (e.g. fresh installs)
        if (! $urlColumn || $urlColumn->notnull == 0) {
            return;
        }

        $this->recreateRequestsTable(urlNullable: true);
    }

    public function down(): void
    {
        $columns = DB::select("PRAGMA table_info('requests')");
        $urlColumn = collect($columns)->firstWhere('name', 'url');

        // notnull = 1 means already NOT NULL — nothing to do
        if (! $urlColumn || $urlColumn->notnull == 1) {
            return;
        }

        $this->recreateRequestsTable(urlNullable: false);
    }

    /**
     * Recreate the requests table with url either nullable or not.
     * SQLite does not support ALTER COLUMN, so table recreation is required.
     */
    private function recreateRequestsTable(bool $urlNullable): void
    {
        $urlDef = $urlNullable ? 'url VARCHAR NULL' : 'url VARCHAR NOT NULL DEFAULT ""';

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement("CREATE TABLE requests_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            collection_id INTEGER NULL,
            folder_id INTEGER NULL,
            user_id INTEGER NOT NULL,
            name VARCHAR NOT NULL,
            method VARCHAR NOT NULL,
            {$urlDef},
            headers TEXT NULL,
            body_type VARCHAR NULL,
            body TEXT NULL,
            auth_type VARCHAR NULL,
            auth_data TEXT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL,
            FOREIGN KEY (folder_id) REFERENCES collection_folders(id) ON DELETE SET NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        DB::statement('INSERT INTO requests_new SELECT * FROM requests');
        DB::statement('DROP TABLE requests');
        DB::statement('ALTER TABLE requests_new RENAME TO requests');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
