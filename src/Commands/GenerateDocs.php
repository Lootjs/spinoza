<?php

namespace Loot\Spinoza\Commands;

use Illuminate\Console\Command;
use Loot\Spinoza\SpinozaWriter;

final class GenerateDocs extends Command
{
    protected $signature = 'spinoza:generate {--force-update : Обновить кэш классов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate documentation for service';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $start = \microtime(true);

        $spinoza = (new SpinozaWriter);
        $data = $spinoza->collectData();
        $spinoza->writeDocs($data);

        $end = \microtime(true);
        $this->info(\sprintf('Find %s annotations', count($data['routes']) + count($data['events'])));
        $this->info(\sprintf('Elapsed time: %ss', round($end - $start, 3)));
    }
}
