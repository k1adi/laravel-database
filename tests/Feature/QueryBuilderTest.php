<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        DB::delete('DELETE FROM `categories`');
    }

    public function testInsert()
    {
        DB::table('categories')->insert([
            'id' => 'GADGET',
            'name' => 'Laptop'
        ]);
        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Ramen'
        ]);

        $result = DB::select('SELECT count(id) AS total FROM `categories`');
        self::assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        // Select column id and name and then get all rows data
        $collection = DB::table('categories')->select(['id', 'name'])->get();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });

        // Select all(default) column and then get the first row
        $collection = DB::table('categories')->first();
        Log::info(json_encode($collection));

        // Select all rows with just one definition column
        $collection = DB::table('categories')->pluck('name');
        Log::info(json_encode($collection));

        // Select first row of definition column
        $collection = DB::table('categories')->pluck('name')->first();
        Log::info(json_encode($collection));
    }
}
