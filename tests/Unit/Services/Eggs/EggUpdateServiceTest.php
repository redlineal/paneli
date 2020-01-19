<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Services\Options;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\Egg;
use Amghost\Services\Eggs\EggUpdateService;
use Amghost\Exceptions\AmghostException;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Exceptions\Service\Egg\NoParentConfigurationFoundException;

class EggUpdateServiceTest extends TestCase
{
    /**
     * @var \Amghost\Models\Egg
     */
    protected $model;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Eggs\EggUpdateService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->model = factory(Egg::class)->make();
        $this->repository = m::mock(EggRepositoryInterface::class);

        $this->service = new EggUpdateService($this->repository);
    }

    /**
     * Test that an Egg is updated when no config_from attribute is passed.
     */
    public function testEggIsUpdatedWhenNoConfigFromIsProvided()
    {
        $this->repository->shouldReceive('withoutFreshModel->update')
            ->with($this->model->id, ['test_field' => 'field_value'])->once()->andReturnNull();

        $this->service->handle($this->model, ['test_field' => 'field_value']);

        $this->assertTrue(true);
    }

    /**
     * Test that Egg is updated when a valid config_from attribute is passed.
     */
    public function testOptionIsUpdatedWhenValidConfigFromIsPassed()
    {
        $this->repository->shouldReceive('findCountWhere')->with([
            ['nest_id', '=', $this->model->nest_id],
            ['id', '=', 1],
        ])->once()->andReturn(1);

        $this->repository->shouldReceive('withoutFreshModel->update')
            ->with($this->model->id, ['config_from' => 1])->once()->andReturnNull();

        $this->service->handle($this->model, ['config_from' => 1]);

        $this->assertTrue(true);
    }

    /**
     * Test that an exception is thrown if an invalid config_from attribute is passed.
     */
    public function testExceptionIsThrownIfInvalidParentConfigIsPassed()
    {
        $this->repository->shouldReceive('findCountWhere')->with([
            ['nest_id', '=', $this->model->nest_id],
            ['id', '=', 1],
        ])->once()->andReturn(0);

        try {
            $this->service->handle($this->model, ['config_from' => 1]);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(NoParentConfigurationFoundException::class, $exception);
            $this->assertEquals(trans('exceptions.nest.egg.must_be_child'), $exception->getMessage());
        }
    }

    /**
     * Test that an integer linking to a model can be passed in place of the Egg model.
     */
    public function testIntegerCanBePassedInPlaceOfModel()
    {
        $this->repository->shouldReceive('find')->with($this->model->id)->once()->andReturn($this->model);
        $this->repository->shouldReceive('withoutFreshModel->update')
            ->with($this->model->id, ['test_field' => 'field_value'])->once()->andReturnNull();

        $this->service->handle($this->model->id, ['test_field' => 'field_value']);

        $this->assertTrue(true);
    }
}
