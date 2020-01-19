<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Console\Commands\Location;

use Illuminate\Console\Command;
use Amghost\Services\Locations\LocationCreationService;

class MakeLocationCommand extends Command
{
    /**
     * @var \Amghost\Services\Locations\LocationCreationService
     */
    protected $creationService;

    /**
     * @var string
     */
    protected $signature = 'p:location:make
                            {--short= : The shortcode name of this location (ex. us1).}
                            {--long= : A longer description of this location.}';

    /**
     * @var string
     */
    protected $description = 'Creates a new location on the system via the CLI.';

    /**
     * Create a new command instance.
     *
     * @param \Amghost\Services\Locations\LocationCreationService $creationService
     */
    public function __construct(LocationCreationService $creationService)
    {
        parent::__construct();

        $this->creationService = $creationService;
    }

    /**
     * Handle the command execution process.
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function handle()
    {
        $short = $this->option('short') ?? $this->ask(trans('command/messages.location.ask_short'));
        $long = $this->option('long') ?? $this->ask(trans('command/messages.location.ask_long'));

        $location = $this->creationService->handle(compact('short', 'long'));
        $this->line(trans('command/messages.location.created', [
            'name' => $location->short,
            'id' => $location->id,
        ]));
    }
}
