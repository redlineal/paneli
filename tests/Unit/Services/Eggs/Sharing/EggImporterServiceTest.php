<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Services\Sharing;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\Egg;
use Amghost\Models\Nest;
use Tests\Traits\MocksUuids;
use Illuminate\Http\UploadedFile;
use Amghost\Models\EggVariable;
use Illuminate\Database\ConnectionInterface;
use Amghost\Exceptions\AmghostException;
use Amghost\Services\Eggs\Sharing\EggImporterService;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Contracts\Repository\NestRepositoryInterface;
use Amghost\Exceptions\Service\Egg\BadJsonFormatException;
use Amghost\Exceptions\Service\InvalidFileUploadException;
use Amghost\Contracts\Repository\EggVariableRepositoryInterface;

class EggImporterServiceTest extends TestCase
{
    use MocksUuids;

    /**
     * @var \Illuminate\Database\ConnectionInterface|\Mockery\Mock
     */
    protected $connection;

    /**
     * @var \Amghost\Contracts\Repository\EggVariableRepositoryInterface|\Mockery\Mock
     */
    protected $eggVariableRepository;

    /**
     * @var \Illuminate\Http\UploadedFile|\Mockery\Mock
     */
    protected $file;

    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface|\Mockery\Mock
     */
    protected $nestRepository;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Eggs\Sharing\EggImporterService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->connection = m::mock(ConnectionInterface::class);
        $this->eggVariableRepository = m::mock(EggVariableRepositoryInterface::class);
        $this->file = m::mock(UploadedFile::class);
        $this->nestRepository = m::mock(NestRepositoryInterface::class);
        $this->repository = m::mock(EggRepositoryInterface::class);

        $this->service = new EggImporterService(
            $this->connection, $this->repository, $this->eggVariableRepository, $this->nestRepository
        );
    }

    /**
     * Test that a service option can be successfully imported.
     */
    public function testEggConfigurationIsImported()
    {
        $egg = factory(Egg::class)->make();
        $nest = factory(Nest::class)->make();

        $this->file->shouldReceive('getError')->withNoArgs()->once()->andReturn(UPLOAD_ERR_OK);
        $this->file->shouldReceive('isFile')->withNoArgs()->once()->andReturn(true);
        $this->file->shouldReceive('getSize')->withNoArgs()->once()->andReturn(100);
        $this->file->shouldReceive('openFile->fread')->with(100)->once()->andReturn(json_encode([
            'meta' => ['version' => 'PTDL_v1'],
            'name' => $egg->name,
            'author' => $egg->author,
            'variables' => [
                $variable = factory(EggVariable::class)->make(),
            ],
        ]));
        $this->nestRepository->shouldReceive('getWithEggs')->with($nest->id)->once()->andReturn($nest);

        $this->connection->shouldReceive('beginTransaction')->withNoArgs()->once()->andReturnNull();
        $this->repository->shouldReceive('create')->with(m::subset([
            'uuid' => $this->getKnownUuid(),
            'nest_id' => $nest->id,
            'name' => $egg->name,
        ]), true, true)->once()->andReturn($egg);

        $this->eggVariableRepository->shouldReceive('create')->with(m::subset([
            'egg_id' => $egg->id,
            'env_variable' => $variable->env_variable,
        ]))->once()->andReturnNull();
        $this->connection->shouldReceive('commit')->withNoArgs()->once()->andReturnNull();

        $response = $this->service->handle($this->file, $nest->id);
        $this->assertNotEmpty($response);
        $this->assertInstanceOf(Egg::class, $response);
        $this->assertSame($egg, $response);
    }

    /**
     * Test that an exception is thrown if the file is invalid.
     */
    public function testExceptionIsThrownIfFileIsInvalid()
    {
        $this->file->shouldReceive('getError')->withNoArgs()->once()->andReturn(UPLOAD_ERR_NO_FILE);
        try {
            $this->service->handle($this->file, 1234);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(InvalidFileUploadException::class, $exception);
            $this->assertEquals(trans('exceptions.nest.importer.file_error'), $exception->getMessage());
        }
    }

    /**
     * Test that an exception is thrown if the file is not a file.
     */
    public function testExceptionIsThrownIfFileIsNotAFile()
    {
        $this->file->shouldReceive('getError')->withNoArgs()->once()->andReturn(UPLOAD_ERR_OK);
        $this->file->shouldReceive('isFile')->withNoArgs()->once()->andReturn(false);

        try {
            $this->service->handle($this->file, 1234);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(InvalidFileUploadException::class, $exception);
            $this->assertEquals(trans('exceptions.nest.importer.file_error'), $exception->getMessage());
        }
    }

    /**
     * Test that an exception is thrown if the JSON metadata is invalid.
     */
    public function testExceptionIsThrownIfJsonMetaDataIsInvalid()
    {
        $this->file->shouldReceive('getError')->withNoArgs()->once()->andReturn(UPLOAD_ERR_OK);
        $this->file->shouldReceive('isFile')->withNoArgs()->once()->andReturn(true);
        $this->file->shouldReceive('getSize')->withNoArgs()->once()->andReturn(100);
        $this->file->shouldReceive('openFile->fread')->with(100)->once()->andReturn(json_encode([
            'meta' => ['version' => 'hodor'],
        ]));

        try {
            $this->service->handle($this->file, 1234);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(InvalidFileUploadException::class, $exception);
            $this->assertEquals(trans('exceptions.nest.importer.invalid_json_provided'), $exception->getMessage());
        }
    }

    /**
     * Test that an exception is thrown if bad JSON is provided.
     */
    public function testExceptionIsThrownIfBadJsonIsProvided()
    {
        $this->file->shouldReceive('getError')->withNoArgs()->once()->andReturn(UPLOAD_ERR_OK);
        $this->file->shouldReceive('isFile')->withNoArgs()->once()->andReturn(true);
        $this->file->shouldReceive('getSize')->withNoArgs()->once()->andReturn(100);
        $this->file->shouldReceive('openFile->fread')->with(100)->once()->andReturn('}');

        try {
            $this->service->handle($this->file, 1234);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(BadJsonFormatException::class, $exception);
            $this->assertEquals(trans('exceptions.nest.importer.json_error', [
                'error' => json_last_error_msg(),
            ]), $exception->getMessage());
        }
    }
}
