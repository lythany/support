<?php

declare(strict_types=1);

namespace Lythany\Tests\Support;

use PHPUnit\Framework\TestCase;
use Lythany\Support\Collection;

class CollectionTest extends TestCase
{
    public function testCanCreateEmptyCollection(): void
    {
        $collection = new Collection();
        
        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->isNotEmpty());
    }

    public function testCanCreateCollectionFromArray(): void
    {
        $items = [1, 2, 3, 4, 5];
        $collection = new Collection($items);
        
        $this->assertCount(5, $collection);
        $this->assertEquals($items, $collection->all());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isNotEmpty());
    }

    public function testMakeStaticMethod(): void
    {
        $collection = Collection::make([1, 2, 3]);
        
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals([1, 2, 3], $collection->all());
    }

    public function testRangeStaticMethod(): void
    {
        $collection = Collection::range(1, 5);
        
        $this->assertEquals([1, 2, 3, 4, 5], $collection->all());
    }

    public function testTimesStaticMethod(): void
    {
        $collection = Collection::times(3, fn($number) => $number * 2);
        
        $this->assertEquals([2, 4, 6], $collection->all());
    }

    public function testMapMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $mapped = $collection->map(fn($item) => $item * 2);
        
        $this->assertEquals([2, 4, 6], $mapped->all());
    }

    public function testFilterMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $filtered = $collection->filter(fn($item) => $item > 3);
        
        $this->assertEquals([3 => 4, 4 => 5], $filtered->all());
    }

    public function testFilterWithoutCallback(): void
    {
        $collection = new Collection([0, 1, false, 2, '', 3, null]);
        $filtered = $collection->filter();
        
        $this->assertEquals([1 => 1, 3 => 2, 5 => 3], $filtered->all());
    }

    public function testFirstMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $this->assertEquals(1, $collection->first());
        $this->assertEquals(4, $collection->first(fn($item) => $item > 3));
        $this->assertEquals('default', $collection->first(fn($item) => $item > 10, 'default'));
    }

    public function testLastMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $this->assertEquals(5, $collection->last());
        $this->assertEquals(5, $collection->last(fn($item) => $item > 3));
        $this->assertEquals('default', $collection->last(fn($item) => $item > 10, 'default'));
    }

    public function testTakeMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $this->assertEquals([1, 2, 3], $collection->take(3)->all());
        $this->assertEquals([3 => 4, 4 => 5], $collection->take(-2)->all());
    }

    public function testSkipMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $this->assertEquals([2 => 3, 3 => 4, 4 => 5], $collection->skip(2)->all());
    }

    public function testPluckMethod(): void
    {
        $collection = new Collection([
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'Bob', 'age' => 35]
        ]);
        
        $names = $collection->pluck('name');
        $this->assertEquals(['John', 'Jane', 'Bob'], $names->all());
    }

    public function testWhereMethod(): void
    {
        $collection = new Collection([
            ['name' => 'John', 'active' => true],
            ['name' => 'Jane', 'active' => false],
            ['name' => 'Bob', 'active' => true]
        ]);
        
        $active = $collection->where('active', true);
        $this->assertCount(2, $active);
    }

    public function testGroupByMethod(): void
    {
        $collection = new Collection([
            ['name' => 'John', 'department' => 'IT'],
            ['name' => 'Jane', 'department' => 'HR'],
            ['name' => 'Bob', 'department' => 'IT']
        ]);
        
        $grouped = $collection->groupBy('department');
        
        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped->get('IT'));
        $this->assertCount(1, $grouped->get('HR'));
    }

    public function testSortByMethod(): void
    {
        $collection = new Collection([
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'Bob', 'age' => 35]
        ]);
        
        $sorted = $collection->sortBy('age');
        $ages = $sorted->pluck('age')->all();
        
        $this->assertEquals([25, 30, 35], array_values($ages));
    }

    public function testSortByDescMethod(): void
    {
        $collection = new Collection([
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'Bob', 'age' => 35]
        ]);
        
        $sorted = $collection->sortByDesc('age');
        $ages = $sorted->pluck('age')->all();
        
        $this->assertEquals([35, 30, 25], array_values($ages));
    }

    public function testChunkMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5, 6, 7]);
        $chunks = $collection->chunk(3);
        
        $this->assertCount(3, $chunks);
        $this->assertEquals([1, 2, 3], $chunks->first()->all());
        $this->assertEquals([6 => 7], $chunks->last()->all());
    }

    public function testCollapseMethod(): void
    {
        $collection = new Collection([[1, 2], [3, 4], [5, 6]]);
        $collapsed = $collection->collapse();
        
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collapsed->all());
    }

    public function testFlattenMethod(): void
    {
        $collection = new Collection([1, [2, [3, 4]], 5]);
        $flattened = $collection->flatten();
        
        $this->assertEquals([1, 2, 3, 4, 5], $flattened->all());
    }

    public function testUniqueMethod(): void
    {
        $collection = new Collection([1, 2, 2, 3, 3, 4]);
        $unique = $collection->unique();
        
        $this->assertEquals([1, 2, 3, 4], array_values($unique->all()));
    }

    public function testValuesMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        $values = $collection->values();
        
        $this->assertEquals([1, 2, 3], $values->all());
    }

    public function testKeysMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        $keys = $collection->keys();
        
        $this->assertEquals(['a', 'b', 'c'], $keys->all());
    }

    public function testFlipMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        $flipped = $collection->flip();
        
        $this->assertEquals([1 => 'a', 2 => 'b', 3 => 'c'], $flipped->all());
    }

    public function testMergeMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $merged = $collection->merge([4, 5, 6]);
        
        $this->assertEquals([1, 2, 3, 4, 5, 6], $merged->all());
    }

    public function testMergeMethodWithDuplicateKeys(): void
    {
        $collection = new Collection([1 => 'a', 2 => 'b']);
        $merged = $collection->merge([3 => 'c', 1 => 'x']);
        
        // Merge reindexes arrays, so we get a numerically indexed result
        $this->assertEquals(['a', 'b', 'c', 'x'], $merged->all());
    }

    public function testIntersectMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4]);
        $intersect = $collection->intersect([2, 3, 5]);
        
        $this->assertEquals([1 => 2, 2 => 3], $intersect->all());
    }

    public function testDiffMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4]);
        $diff = $collection->diff([2, 3, 5]);
        
        $this->assertEquals([0 => 1, 3 => 4], $diff->all());
    }

    public function testReduceMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4]);
        $sum = $collection->reduce(fn($carry, $item) => $carry + $item, 0);
        
        $this->assertEquals(10, $sum);
    }

    public function testSumMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4]);
        
        $this->assertEquals(10, $collection->sum());
    }

    public function testAvgMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4]);
        
        $this->assertEquals(2.5, $collection->avg());
    }

    public function testMinMethod(): void
    {
        $collection = new Collection([3, 1, 4, 1, 5]);
        
        $this->assertEquals(1, $collection->min());
    }

    public function testMaxMethod(): void
    {
        $collection = new Collection([3, 1, 4, 1, 5]);
        
        $this->assertEquals(5, $collection->max());
    }

    public function testCountMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $this->assertEquals(5, $collection->count());
    }

    public function testContainsMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $this->assertTrue($collection->contains(3));
        $this->assertFalse($collection->contains(6));
        $this->assertTrue($collection->contains(fn($item) => $item > 4));
    }

    public function testEveryMethod(): void
    {
        $collection = new Collection([2, 4, 6, 8]);
        
        $this->assertTrue($collection->every(fn($item) => $item % 2 === 0));
        $this->assertFalse($collection->every(fn($item) => $item > 5));
    }

    public function testHasMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        
        $this->assertTrue($collection->has('a'));
        $this->assertFalse($collection->has('d'));
    }

    public function testGetMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        
        $this->assertEquals(1, $collection->get('a'));
        $this->assertEquals('default', $collection->get('d', 'default'));
    }

    public function testPutMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $collection->put('c', 3);
        
        $this->assertEquals(3, $collection->get('c'));
    }

    public function testPushMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->push(4, 5);
        
        $this->assertEquals([1, 2, 3, 4, 5], $collection->all());
    }

    public function testPopMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $popped = $collection->pop();
        
        $this->assertEquals(3, $popped);
        $this->assertEquals([1, 2], $collection->all());
    }

    public function testShiftMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $shifted = $collection->shift();
        
        $this->assertEquals(1, $shifted);
        $this->assertEquals([2, 3], array_values($collection->all()));
    }

    public function testPrependMethod(): void
    {
        $collection = new Collection([2, 3, 4]);
        $collection->prepend(1);
        
        $this->assertEquals(1, $collection->first());
    }

    public function testSliceMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $sliced = $collection->slice(1, 3);
        
        $this->assertEquals([1 => 2, 2 => 3, 3 => 4], $sliced->all());
    }

    public function testSpliceMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $spliced = $collection->splice(1, 2, [10, 11]);
        
        // splice() should return the removed elements
        $this->assertEquals([2, 3], $spliced->all());
    }

    public function testRandomMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $random = $collection->random();
        
        $this->assertContains($random, [1, 2, 3, 4, 5]);
        
        $randomCollection = $collection->random(3);
        $this->assertCount(3, $randomCollection);
    }

    public function testShuffleMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $shuffled = $collection->shuffle();
        
        $this->assertCount(5, $shuffled);
        // Note: We can't easily test randomness, but we can ensure all items are present
        $this->assertEquals([1, 2, 3, 4, 5], (new Collection($shuffled->all()))->sort()->values()->all());
    }

    public function testReverseMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $reversed = $collection->reverse();
        
        $this->assertEquals([4 => 5, 3 => 4, 2 => 3, 1 => 2, 0 => 1], $reversed->all());
    }

    public function testSearchMethod(): void
    {
        $collection = new Collection(['a', 'b', 'c', 'd']);
        
        $this->assertEquals(2, $collection->search('c'));
        $this->assertFalse($collection->search('z'));
        $this->assertEquals(3, $collection->search(fn($item) => $item === 'd'));
    }

    public function testRejectMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $rejected = $collection->reject(fn($item) => $item > 3);
        
        $this->assertEquals([1, 2, 3], array_values($rejected->all()));
    }

    public function testOnlyMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
        $only = $collection->only(['a', 'c']);
        
        $this->assertEquals(['a' => 1, 'c' => 3], $only->all());
    }

    public function testExceptMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
        $except = $collection->except(['a', 'c']);
        
        $this->assertEquals(['b' => 2, 'd' => 4], $except->all());
    }

    public function testForgetMethod(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        $collection->forget(['a', 'c']);
        
        $this->assertEquals(['b' => 2], $collection->all());
    }

    public function testTransformMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->transform(fn($item) => $item * 2);
        
        $this->assertEquals([2, 4, 6], $collection->all());
    }

    public function testEachMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $sum = 0;
        
        $collection->each(function($item) use (&$sum) {
            $sum += $item;
        });
        
        $this->assertEquals(6, $sum);
    }

    public function testMapWithKeysMethod(): void
    {
        $collection = new Collection([
            ['name' => 'John', 'email' => 'john@example.com'],
            ['name' => 'Jane', 'email' => 'jane@example.com']
        ]);
        
        $mapped = $collection->mapWithKeys(fn($item) => [$item['email'] => $item['name']]);
        
        $this->assertEquals([
            'john@example.com' => 'John',
            'jane@example.com' => 'Jane'
        ], $mapped->all());
    }

    public function testFlatMapMethod(): void
    {
        $collection = new Collection([
            ['items' => [1, 2]],
            ['items' => [3, 4]]
        ]);
        
        $flatMapped = $collection->flatMap(fn($item) => $item['items']);
        
        $this->assertEquals([1, 2, 3, 4], $flatMapped->all());
    }

    public function testArrayAccess(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        
        $this->assertTrue(isset($collection['a']));
        $this->assertEquals(1, $collection['a']);
        
        $collection['d'] = 4;
        $this->assertEquals(4, $collection['d']);
        
        unset($collection['a']);
        $this->assertFalse(isset($collection['a']));
    }

    public function testJsonSerialization(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        $json = $collection->toJson();
        
        $this->assertEquals('{"a":1,"b":2,"c":3}', $json);
    }

    public function testToArray(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        $array = $collection->toArray();
        
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    public function testIterator(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $sum = 0;
        
        foreach ($collection as $item) {
            $sum += $item;
        }
        
        $this->assertEquals(15, $sum);
    }

    public function testStringConversion(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        
        $this->assertEquals('{"a":1,"b":2}', (string) $collection);
    }

    public function testImplodeMethod(): void
    {
        $collection = new Collection(['hello', 'world', 'test']);
        
        $this->assertEquals('hello,world,test', $collection->implode(','));
        $this->assertEquals('hello world test', $collection->implode(' '));
    }

    public function testJoinMethod(): void
    {
        $collection = new Collection(['apple', 'banana', 'cherry']);
        
        $this->assertEquals('apple, banana and cherry', $collection->join(', ', ' and '));
        $this->assertEquals('apple', (new Collection(['apple']))->join(', ', ' and '));
        $this->assertEquals('', (new Collection())->join(', ', ' and '));
    }

    public function testNthMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $nth = $collection->nth(3);
        
        $this->assertEquals([0 => 1, 3 => 4, 6 => 7, 9 => 10], $nth->all());
    }

    public function testPadMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $padded = $collection->pad(5, 0);
        
        $this->assertEquals([1, 2, 3, 0, 0], $padded->all());
    }

    public function testZipMethod(): void
    {
        $collection = new Collection([1, 2, 3]);
        $zipped = $collection->zip(['a', 'b', 'c'], ['x', 'y', 'z']);
        
        $this->assertEquals([
            [1, 'a', 'x'],
            [2, 'b', 'y'],
            [3, 'c', 'z']
        ], $zipped->map(fn($item) => $item->all())->all());
    }

    public function testSplitMethod(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5, 6]);
        $split = $collection->split(3);
        
        $this->assertCount(3, $split);
        $this->assertEquals([1, 2], $split->first()->all());
    }

    public function testCountByMethod(): void
    {
        $collection = new Collection(['a', 'b', 'a', 'c', 'b', 'a']);
        $counts = $collection->countBy();
        
        $this->assertEquals(['a' => 3, 'b' => 2, 'c' => 1], $counts->all());
    }
}
