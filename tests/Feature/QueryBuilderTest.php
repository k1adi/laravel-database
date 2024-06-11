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

    public function insertDummy()
    {
        DB::table('categories')->insert([
            'id' => 'GADGET',
            'name' => 'Smartphone',
            'desc' => 'Smartphone desc',
            'created_at' => '2024-06-07 14:00:00' 
        ]);
        DB::table('categories')->insert([
            'id' => 'ATK',
            'name' => 'Pencil',
            'desc' => 'Pencil desc',
            'created_at' => '2024-06-08 14:00:00' 
        ]);
        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Ramen',
            'created_at' => '2024-06-09 14:00:00' 
        ]);
        DB::table('categories')->insert([
            'id' => 'FASHION',
            'name' => 'Hat',
            'created_at' => '2024-06-10 14:00:00' 
        ]);
    }

    public function testWhereMethod()
    {
        $this->insertDummy();

        // where(column, operator, value)
        $result = DB::table('categories')
            ->where('name', '=', 'Smartphone')
            ->get();
        Log::info('where(column, operator, value) => ' . json_encode($result));
        $this->assertCount(1, $result);

        // where([condition1, condition2])
        $result = DB::table('categories')
            ->where([
                ['name', '=', 'Pencil'],
                ['desc', '=', 'Pencil desc']
            ])
            ->get();
        Log::info('where([condition1, condition2]) => ' . json_encode($result));
        $this->assertCount(1, $result);

        // where(callback(Builder))
        $result = DB::table('categories')
            ->where(function($query) {
                $query->where('name', '=', 'Ramen');
            })
            ->get();
        Log::info('where(callback(Builder)) => ' . json_encode($result));
        $this->assertCount(1, $result);

        // orWhere(column, operator, value)
        $result = DB::table('categories')
            ->where('name', '=', 'Smartphone')
            ->orWhere('name', '=', 'Hat')
            ->get();
        Log::info('orWhere(column, operator, value) => ' . json_encode($result));
        $this->assertCount(2, $result);

        // orWhere(callback(Builder))
        $result = DB::table('categories')
            ->where('name', '=', 'Ramen')
            ->orWhere(function($query) {
                $query->where('name', '=', 'Hat');
            })
            ->get();
        Log::info('orWhere(callback(Builder)) => ' . json_encode($result));
        $this->assertCount(2, $result);

        // whereNot(callback(Builder))
        $result = DB::table('categories')
            ->whereNot(function($query) {
                $query->where('name', '=', 'Pencil');
            })
            ->get();
        Log::info('whereNot(callback(Builder)) => ' . json_encode($result));
        $this->assertCount(3, $result);
    }

    public function testWhereBetween()
    {
        $this->insertDummy();

        $collection = DB::table('categories')
            ->whereBetween('created_at', ['2024-06-06 14:00:00', '2024-06-08 14:00:00'])
            ->get();
        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info('whereBetween => ' . json_encode($item));
        });
    }

    public function testWherenIn()
    {
        $this->insertDummy();

        $collection = DB::table('categories')->whereIn('id', ['GADGET', 'ATK'])->get();
        self::assertCount(2, $collection);

        $collection->each(function ($item) {
            Log::info('whereIn => ' . json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertDummy();

        $collection = DB::table('categories')->whereNull('desc')->get();
        self::assertCount(2, $collection);

        $collection->each(function ($item) {
            Log::info('whereNull => ' . json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertDummy();

        $collection = DB::table('categories')->whereDate('created_at', '2024-06-07')->get();
        self::assertCount(1, $collection);
    }

    public function testWhereMonth()
    {
        $this->insertDummy();

        $collection = DB::table('categories')->whereMonth('created_at', '06')->get();
        self::assertCount(4, $collection);
    }

    public function testWhereDay()
    {
        $this->insertDummy();

        $collection = DB::table('categories')->whereDay('created_at', '10')->get();
        self::assertCount(1, $collection);
    }

    public function testWhereYear()
    {
        $this->insertDummy();

        $collection = DB::table('categories')->whereYear('created_at', '2024')->get();
        self::assertCount(4, $collection);
    }
    
    public function testWhereTime()
    {
        $this->insertDummy();

        $collection = DB::table('categories')->whereTime('created_at', '14:00')->get();
        self::assertCount(4, $collection);
    }

    public function testUpdate()
    {
        $this->insertDummy();

        DB::table('categories')->where('id', '=', 'FOOD')->update([
            'name' => 'Bakso'
        ]);

        $collection = DB::table('categories')->where('name', '=', 'Bakso')->get();
        self::assertCount(1, $collection);
    }

    public function testUpsert()
    {
        DB::table('categories')->updateOrInsert([
            'id' => 'VOUCHER'
        ], [
            'name' => 'Voucher',
            'desc' => 'Ticket & Voucher',
            'created_at' => '2024-06-10 16:10:00'
        ]);

        $collection = DB::table('categories')->where('id', '=', 'VOUCHER')->get();
        self::assertCount(1, $collection);
    }

    public function testIncrement()
    {
        DB::table('counters')->where('id', '=', 'sample')->increment('counter', 5);

        $collection = DB::table('counters')->where('id', '=', 'sample')->get();
        self::assertCount(1, $collection);
        LOG::info("Query Builder Increment => $collection");
    }

    public function testDecrement()
    {
        DB::table('counters')->where('id', '=', 'sample')->decrement('counter', 3);

        $collection = DB::table('counters')->where('id', '=', 'sample')->get();
        self::assertCount(1, $collection);
        LOG::info("Query Builder Increment => $collection");
    }
}
