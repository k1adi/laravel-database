<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        DB::delete('DELETE FROM `products`');
        DB::delete('DELETE FROM `categories`');
        DB::delete('DELETE FROM `counters`');
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

    public function insertCategoryDummy()
    {
        $this->seed(CategorySeeder::class);
    }

    public function testWhereMethod()
    {
        $this->insertCategoryDummy();

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
        $this->insertCategoryDummy();

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
        $this->insertCategoryDummy();

        $collection = DB::table('categories')->whereIn('id', ['GADGET', 'ATK'])->get();
        self::assertCount(2, $collection);

        $collection->each(function ($item) {
            Log::info('whereIn => ' . json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertCategoryDummy();

        $collection = DB::table('categories')->whereNull('desc')->get();
        self::assertCount(2, $collection);

        $collection->each(function ($item) {
            Log::info('whereNull => ' . json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategoryDummy();

        $collection = DB::table('categories')->whereDate('created_at', '2024-06-07')->get();
        self::assertCount(1, $collection);
    }

    public function testWhereMonth()
    {
        $this->insertCategoryDummy();

        $collection = DB::table('categories')->whereMonth('created_at', '06')->get();
        self::assertCount(4, $collection);
    }

    public function testWhereDay()
    {
        $this->insertCategoryDummy();

        $collection = DB::table('categories')->whereDay('created_at', '10')->get();
        self::assertCount(1, $collection);
    }

    public function testWhereYear()
    {
        $this->insertCategoryDummy();

        $collection = DB::table('categories')->whereYear('created_at', '2024')->get();
        self::assertCount(4, $collection);
    }
    
    public function testWhereTime()
    {
        $this->insertCategoryDummy();

        $collection = DB::table('categories')->whereTime('created_at', '14:00')->get();
        self::assertCount(4, $collection);
    }

    public function testUpdate()
    {
        $this->insertCategoryDummy();

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
        $this->seed(CounterSeeder::class);

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

    public function testDelete()
    {
        $this->insertCategoryDummy();

        DB::table('categories')->where('id', '=', 'FOOD')->delete();
        $collection = DB::table('categories')->where('id', '=', 'FOOD')->get();
        self::assertCount(0, $collection);
    }

    public function insertProductDummy(){
        $this->insertCategoryDummy();

        DB::table('products')->insert([
            'id' => '1',
            'name' => 'Samsung Fold',
            'desc' => 'Samsung Fold Description',
            'price' => 10000000,
            'category_id' => 'GADGET'
        ]);

        DB::table('products')->insert([
            'id' => '2',
            'name' => 'Samsung Flip',
            'desc' => 'Samsung Flip Description',
            'price' => 11000000,
            'category_id' => 'GADGET'
        ]);
    }

    public function testTruncateTable()
    {
        $this->insertProductDummy();
        self::assertDatabaseCount('products', 2);

        DB::table('products')->truncate();
        self::assertDatabaseCount('products', 0);
    }

    public function testQueryJoin()
    {
        $this->insertProductDummy();

        $collection = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'categories.name as category_name', 'products.price')
            ->get();
        self::assertCount(2, $collection);
        LOG::info('Query Builder Join => ' . json_encode($collection));
    }

    public function testQueryOrdering()
    {
        $this->insertProductDummy();

        $collection = DB::table('products')->get();
        LOG::info("Query Builder Ordering Default => $collection");

        $collection = DB::table('products')->orderBy('price', 'desc')->get();
        LOG::info("Query Builder Ordering Price Desc => $collection");
        
        $collection = DB::table('products')
            ->orderBy('price', 'desc')
            ->orderBy('name', 'desc')
            ->get();
        LOG::info("Query Builder Ordering price and name => $collection");

        self::assertCount(2, $collection);
    }

    public function testQueryPaging()
    {
        $this->insertCategoryDummy();

        $page1 = DB::table('categories')
            ->skip(0)->take(2)->get();

        self::assertCount(2, $page1);
        LOG::info("Query Paging, page 1 => $page1");

        $page2 = DB::table('categories')
            ->skip(2)->take(2)->get();

        self::assertCount(2, $page2);
        LOG::info("Query Paging, page 2 => $page2");
    }

    public function insertManyCategories()
    {
        for ($i=0; $i < 150; $i++) { 
            DB::table('categories')->insert([
                'id' => "CATEGORY - $i",
                'name' => "Category Name - $i",
                'created_at' => now()
            ]);
        }
    }

    public function testQueryChunk()
    {
        $this->insertManyCategories();

        DB::table('categories')->orderBy('id')
            ->chunk(10, function($category) {
                self::assertNotNull($category);
                LOG::info("Query Chunk => $category");
            });
    }

    public function testQueryLazy()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')->orderBy('id')->lazy(10)->take(5);
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            LOG::info('Lazy collection => ' . json_encode($item));
        });
    }

    public function testQueryCursor()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')->orderBy('id')->cursor();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            LOG::info('Cursor result => ' . json_encode($item));
        });
    }

    public function testQueryCount()
    {
        $this->insertProductDummy();
        
        $collection = DB::table('products')->count('id');
        self::assertEquals(2, $collection);
    }
    
    public function testQueryMax()
    {
        $this->insertProductDummy();
        
        $collection = DB::table('products')->max('price');
        self::assertEquals(11000000, $collection);
    }
    
    public function testQueryMin()
    {
        $this->insertProductDummy();
        
        $collection = DB::table('products')->min('price');
        self::assertEquals(10000000, $collection);
    }
    
    public function testQueryAverage()
    {
        $this->insertProductDummy();
        
        $collection = DB::table('products')->avg('price');
        self::assertEquals(10500000, $collection);
    }
    
    public function testQuerySum()
    {
        $this->insertProductDummy();
        
        $collection = DB::table('products')->sum('price');
        self::assertEquals(21000000, $collection);
    }

    public function testQueryRawAgregate()
    {
        $this->insertProductDummy();

        $collection = DB::table('products')
            ->select(
                DB::raw('count(*) as total_product'),
                DB::raw('min(price) as min_price'),
                DB::raw('max(price) as max_price'),
            )->get();
        
        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(10000000, $collection[0]->min_price);
        self::assertEquals(11000000, $collection[0]->max_price);
        Log::info("Raw Agregate => $collection");
    }

    public function insertFoodProduct(){
        DB::table('products')->insert([
            'id' => '3',
            'name' => 'Mie Goreng',
            'desc' => 'Mie Goreng Description',
            'price' => 15000,
            'category_id' => 'FOOD'
        ]);

        DB::table('products')->insert([
            'id' => '4',
            'name' => 'Nasi Goreng',
            'desc' => 'Nasi Goreng Description',
            'price' => 15000,
            'category_id' => 'FOOD'
        ]);
    }

    public function testQueryGroupBy()
    {
        $this->insertProductDummy();
        $this->insertFoodProduct();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_product'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->get();

        Log::info("Grouping => $collection");
        self::assertCount(2, $collection);
        self::assertEquals('GADGET', $collection[0]->category_id);
        self::assertEquals('FOOD', $collection[1]->category_id);
        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(2, $collection[1]->total_product);
    }

    public function testQueryGroupByHaving()
    {
        $this->insertProductDummy();
        $this->insertFoodProduct();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_product'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->having(DB::raw('count(*)'), '>', 2)
            ->get();

        Log::info("Grouping => $collection");
        self::assertCount(0, $collection);
    }

    public function testQueryBuilderLocking()
    {
        $this->insertProductDummy();

        DB::transaction(function () {
            $collection = DB::table('products')->where('id', '=', '1')
                ->lockForUpdate()->get();
            
            self::assertCount(1, $collection);
        });
    }

    public function testQueryBuilderPagination()
    {
        $this->insertCategoryDummy();

        $paginate = DB::table('categories')->paginate(2);
        Log::info('Pagination => ' . json_encode($paginate));
        self::assertEquals(1, $paginate->currentPage());
        self::assertEquals(2, $paginate->perPage());
        self::assertEquals(2, $paginate->lastPage());
        self::assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        Log::info('Pagination Collection => ' . json_encode($collection));
        self::assertCount(2, $collection);
    }

    public function testQueryBuilderIteratePagination()
    {
        $this->insertCategoryDummy();

        $page = 1;
        while(true) {
            $paginate = DB::table('categories')->paginate(2, ['*'], 'page', $page);
            if($paginate->isEmpty()) {
                break;
            } else {
                $page++;
                $collection = $paginate->items();
                self::assertNotNull($collection);
                Log::info('Iterate Pagination => ' . json_encode($collection));
            }
        }
    }

    public function testQueryBuilderCursorPaginator()
    {
        $this->insertCategoryDummy();

        $cursor = 'id';
        while(true) {
            $paginate = DB::table('categories')->orderBy('id')->cursorPaginate(2, ['*'], 'cursor', $cursor);

            foreach($paginate->items() as $item) {
                self::assertNotNull($item);
                Log::info(json_encode($item));
            }
            $cursor = $paginate->nextCursor();
            if($cursor == null) {
                break;
            }
        }
    }
}
