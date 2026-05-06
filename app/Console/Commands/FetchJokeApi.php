<?php

namespace App\Console\Commands;

use App\Models\ApiFetch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class FetchJokeApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:fetch-joke {--timeout=10 : HTTP timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch a random joke from Official Joke API and store it in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeout = (int) $this->option('timeout');

        try {
            $response = Http::timeout($timeout)->acceptJson()->get('https://official-joke-api.appspot.com/random_joke');

            if (! $response->ok()) {
                $this->error('Request failed with HTTP '.$response->status());
                return self::FAILURE;
            }

            $data = $response->json();

            if (! is_array($data)) {
                $this->error('Invalid JSON payload.');
                return self::FAILURE;
            }

            ApiFetch::create([
                'source' => 'official-joke-api',
                'external_id' => isset($data['id']) ? (string) $data['id'] : null,
                'payload' => $data,
                'fetched_at' => now(),
            ]);

            $this->info('Stored joke fetch successfully.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Fetch failed: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
