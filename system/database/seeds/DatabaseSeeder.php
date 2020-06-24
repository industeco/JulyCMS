<?php

use App\Models\ContentField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        $this->call(ConfigSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ContentFieldSeeder::class);
        $this->call(ContentTypeSeeder::class);
        $this->call(CatalogSeeder::class);
        $this->call(TagSeeder::class);

        DB::commit();

        foreach (ContentField::all() as $field) {
            $field->tableUp();
        }
    }
}
