<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RecommendationEngine;

class TrainRecommendationEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:train';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Train the AI Recommendation Engine (Daily Job)';

    /**
     * Execute the console command.
     */
    public function handle(RecommendationEngine $engine)
    {
        $this->info('Starting AI Recommendation Engine training...');

        $startTime = microtime(true);

        $engine->trainAll();

        $duration = round(microtime(true) - $startTime, 2);

        $this->info("Training completed in {$duration} seconds.");
    }
}
