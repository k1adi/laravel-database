<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        DB::delete('DELETE FROM `categories`');
    }

    public function testCrud()
    {
        DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
            'GADGET', 'Laptop', 'Description laptop', '2020-10-10 10:10:10'
        ]);

        $results = DB::select('SELECT * FROM `categories` WHERE id = ?', ['GADGET']);
        
        self::assertCount(1, $results);
        self::assertEquals('GADGET', $results[0]->id);
        self::assertEquals('Laptop', $results[0]->name);
        self::assertEquals('Description laptop', $results[0]->desc);
        self::assertEquals('2020-10-10 10:10:10', $results[0]->created_at);
    }

    public function testNamedBinding()
    {
        DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (:id, :name, :desc, :created_at)', [
            'id' => 'GADGET',
            'name' => 'Laptop',
            'desc' => 'Description laptop',
            'created_at' => '2020-10-10 10:10:10'
        ]);

        $results = DB::select('SELECT * FROM `categories` WHERE id = ?', ['GADGET']);
        
        self::assertCount(1, $results);
        self::assertEquals('GADGET', $results[0]->id);
        self::assertEquals('Laptop', $results[0]->name);
        self::assertEquals('Description laptop', $results[0]->desc);
        self::assertEquals('2020-10-10 10:10:10', $results[0]->created_at);
    }
}
