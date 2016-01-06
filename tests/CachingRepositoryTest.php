<?php

namespace Logaretm\Depo\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Logaretm\Depo\Tests\Models\Task;
use Logaretm\Depo\Tests\Repositories\CachingTaskRepository;
use Logaretm\Depo\Tests\Repositories\TaskRepository;
use Orchestra\Testbench\TestCase;

class CachingTaskRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var CachingTaskRepository
     */
    protected $repository;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/migrations'),
        ]);

        $this->withFactories(__DIR__.'/factories');
    }

    function prepareTest()
    {
        \Cache::flush();
        factory(Task::class, 10)->create(['completed' => true]);
        factory(Task::class, 8)->create(['completed' => false]);
        $this->repository = new CachingTaskRepository(new TaskRepository, 10, $this->app['cache.store']);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('cache.default', 'redis');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /** @test */
    function it_generates_cache_key_based_on_the_query()
    {
        $this->prepareTest();
        $this->repository->inProgress();
        $firstKey = $this->repository->generateCacheKey('get');
        $this->repository->get();

        $this->repository->inProgress();

        $secondKey = $this->repository->generateCacheKey('get');

        $this->assertEquals($firstKey, $secondKey);

        $this->repository->get();
        $this->repository->completed();

        $thirdKey = $this->repository->generateCacheKey('get');

        $this->assertNotEquals($thirdKey, $secondKey);
    }

    /** @test */
    function it_caches_the_results_of_queries_after_first_usage()
    {
        $this->prepareTest();
        DB::enableQueryLog();

        $this->assertCount(8, $this->repository->inProgress()->get());
        $this->assertCount(10, $this->repository->completed()->get());

        $this->assertCount(2, DB::getQueryLog());

        $this->assertCount(8, $this->repository->inProgress()->get());
        $this->assertCount(10, $this->repository->completed()->get());

        $this->assertCount(2, DB::getQueryLog());

        $this->assertCount(18, $this->repository->all());
        $this->assertCount(3, DB::getQueryLog());

        $this->assertCount(18, $this->repository->all());
        $this->assertCount(3, DB::getQueryLog());

        DB::disableQueryLog();
        \Cache::flush();
    }
}