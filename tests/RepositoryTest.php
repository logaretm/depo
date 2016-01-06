<?php

namespace Logaretm\Depo\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Logaretm\Depo\Repositories\Exceptions\RepositoryException;
use Logaretm\Depo\Tests\Models\NotAModel;
use Logaretm\Depo\Tests\Models\NotATask;
use Logaretm\Depo\Tests\Models\Task;
use Logaretm\Depo\Tests\Repositories\TaskRepository;
use Orchestra\Testbench\TestCase;

class RepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var TaskRepository
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
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /** @test */
    function it_throws_an_exception_when_a_non_eloquent_object_is_passed()
    {
        $this->setExpectedException(RepositoryException::class);

        $this->repository = new TaskRepository(new NotAModel);
    }

    /** @test */
    function it_throws_an_exception_when_a_non_supported_model_is_passed()
    {
        $this->setExpectedException(RepositoryException::class);

        $this->repository = new TaskRepository(new NotATask);
    }

    /** @test */
    function it_allows_usage_of_query_builder_methods()
    {
        factory(Task::class, 10)->create(['completed' => false]);
        factory(Task::class, 5)->create(['completed' => true]);

        $this->repository = new TaskRepository;

        $this->assertCount(10, $this->repository->inProgress()->get());
        $this->assertCount(5, $this->repository->completed()->get());

        Task::completed()->first()->update(['completed' => false]);

        $this->assertCount(11, $this->repository->inProgress()->get());
        $this->assertCount(4, $this->repository->completed()->get());

        // Set scope.
        $this->repository->completed();

        // Testing if all method ignores the previously set scope.
        $this->assertCount(15, $this->repository->all());
    }
}