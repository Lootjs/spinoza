<?php

namespace Loot\Spinoza\Commands;

use Illuminate\Console\Command;
use Loot\Spinoza\SpinozaWriter;

final class GenerateDocs extends Command
{
    protected $signature = 'larabase:generate-docs';

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
     * @param SpinozaWriter $spinoza
     * @return void
     */
    public function handle(SpinozaWriter $spinoza)
    {
        $start = \microtime(true);

        $spinoza->writeDocs(
            $data = $spinoza->collectData()
        );

        $end = \microtime(true);
        $this->table(['Sapere aude'], []);
        $this->comment(\sprintf('Find %s rows', count($data['routes']) + count($data['events'])));
        $this->comment(\sprintf('Elapsed time: %ss', round($end - $start, 3)));
    }
}
