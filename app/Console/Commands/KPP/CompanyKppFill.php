<?php

namespace App\Console\Commands\KPP;

use App\Models\Company;
use App\Models\Kpp;
use Illuminate\Console\Command;

class CompanyKppFill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpp:fill-company';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Это команда по умолчанию для всех компании добавляет возможность проехать через все КПП по белому списку';

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
    public function handle()
    {
        $this->info('The process is starting...');
        Company::chunk(50, function($companies){
            foreach($companies as $company) {
                $company->kpps()->detach();
                foreach(Kpp::all() as $item) {
                    $company->kpps()->attach($item);
                    $this->info("To the " . $company->short_ru_name . " added to kpp: " . $item->name);
                }
            }
        });
        $this->info('The process is finished.');
    }
}
