<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
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
            'id' => 'Food',
            'name' => 'Ramen'
        ]);

        $result = DB::select('SELECT count(id) AS total FROM `categories`');
        self::assertEquals(2, $result[0]->total);
    }
}
