<?php

namespace Logaretm\Depo\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Logaretm\Depo\Repositories\CachingRepository;
use Logaretm\Depo\Repositories\Contracts\CachingRepository as CachingRepositoryInterface;
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
        \Cache::flush();
    }

    function prepareTest()
    {
        factory(Task::class, 10)->create(['completed' => true]);
        factory(Task::class, 8)->create(['completed' => false]);
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

    /**
     * @param CachingRepositoryInterface $repository
     */
    function check_caching_repository_key_generation(CachingRepositoryInterface $repository)
    {
        $repository->inProgress();
        $firstKey = $repository->generateCacheKey('get');
        $repository->resetScope();

        $repository->inProgress();

        $secondKey = $repository->generateCacheKey('get');

        $this->assertEquals($firstKey, $secondKey);

        $repository->resetScope();
        $repository->completed();

        $thirdKey = $repository->generateCacheKey('get');

        $this->assertNotEquals($thirdKey, $secondKey);
        $repository->resetScope();

        \Cache::flush();
    }


    /**
     * @param CachingRepositoryInterface $repository
     */
    function check_caching_repository_caching(CachingRepositoryInterface $repository)
    {
        DB::enableQueryLog();

        $this->assertCount(8, $repository->inProgress()->get());
        $this->assertCount(10, $repository->completed()->get());

        $this->assertCount(2, DB::getQueryLog());

        $this->assertCount(8, $repository->inProgress()->get());
        $this->assertCount(10, $repository->completed()->get());

        $this->assertCount(2, DB::getQueryLog());

        $this->assertCount(18, $repository->all());
        $this->assertCount(3, DB::getQueryLog());

        $this->assertCount(18, $repository->all());
        $this->assertCount(3, DB::getQueryLog());

        DB::disableQueryLog();
        \Cache::flush();
    }


    /** @test */
    function general_implementation_allows_passing_models_as_a_repository()
    {
        $this->prepareTest();

        $repository = new CachingRepository(new Task, 10, $this->app['cache.store']);

        $this->check_caching_repository_key_generation($repository);
        $this->check_caching_repository_caching($repository);
    }

    /** @test */
    function it_generates_cache_key_based_on_the_query()
    {
        $this->prepareTest();

        $repository = new CachingTaskRepository(new TaskRepository, 10, $this->app['cache.store']);
        $this->check_caching_repository_key_generation($repository);
    }

    /** @test */
    function it_caches_the_results_of_queries_after_first_usage()
    {
        $this->prepareTest();
        $repository = new CachingTaskRepository(new TaskRepository, 10, $this->app['cache.store']);
        $this->check_caching_repository_caching($repository);
    }
}