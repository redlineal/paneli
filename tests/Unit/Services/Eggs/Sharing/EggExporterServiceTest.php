<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Eggs\Sharing;

use Mockery as m;
use Carbon\Carbon;
use Tests\TestCase;
use Amghost\Models\Egg;
use Amghost\Models\EggVariable;
use Tests\Assertions\NestedObjectAssertionsTrait;
use Amghost\Services\Eggs\Sharing\EggExporterService;
use Amghost\Contracts\Repository\EggRepositoryInterface;

class EggExporterServiceTest extends TestCase
{
    use NestedObjectAssertionsTrait;

    /**
     * @var \Carbon\Carbon
     */
    protected $carbon;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Eggs\Sharing\EggExporterService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
        $this->carbon = new Carbon();
        $this->repository = m::mock(EggRepositoryInterface::class);

        $this->service = new EggExporterService($this->repository);
    }

    /**
     * Test that a JSON structure is returned.
     */
    public function testJsonStructureIsExported()
    {
        $egg = factory(Egg::class)->make();
        $egg->variables = collect([$variable = factory(EggVariable::class)->make()]);

        $this->repository->shouldReceive('getWithExportAttributes')->with($egg->id)->once()->andReturn($egg);

        $response = $this->service->handle($egg->id);
        $this->assertNotEmpty($response);

        $data = json_decode($response);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
        $this->assertObjectHasNestedAttribute('meta.version', $data);
        $this->assertObjectNestedValueEquals('meta.version', 'PTDL_v1', $data);
        $this->assertObjectHasNestedAttribute('author', $data);
        $this->assertObjectNestedValueEquals('author', $egg->author, $data);
        $this->assertObjectHasNestedAttribute('exported_at', $data);
        $this->assertObjectNestedValueEquals('exported_at', Carbon::now()->toIso8601String(), $data);
        $this->assertObjectHasNestedAttribute('scripts.installation.script', $data);
        $this->assertObjectHasNestedAttribute('scripts.installation.container', $data);
        $this->assertObjectHasNestedAttribute('scripts.installation.entrypoint', $data);
        $this->assertObjectHasAttribute('variables', $data);
        $this->assertArrayHasKey('0', $data->variables);
        $this->assertObjectHasAttribute('name', $data->variables[0]);
        $this->assertObjectNestedValueEquals('name', $variable->name, $data->variables[0]);
    }
}
