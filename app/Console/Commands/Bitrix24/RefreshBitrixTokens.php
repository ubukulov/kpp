<?php

namespace App\Console\Commands\Bitrix24;

use App\Services\BitrixTokenService;
use Illuminate\Console\Command;
use App\Models\BitrixAccount;
use Carbon\Carbon;
class RefreshBitrixTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:refresh-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Автоматически обновляет токены Bitrix24';

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
     * @return int
     */
    public function handle(BitrixTokenService $bitrixTokenService)
    {
        $accounts = BitrixAccount::all();

        foreach ($accounts as $account) {
            if ($account->expires_at && Carbon::now()->addMinutes(5)->gte($account->expires_at)) {
                $this->info("Updating token for {$account->domain}...");
                $bitrixTokenService->refreshToken($account);
                $this->info("Token updated.");
            }
        }
    }
}
