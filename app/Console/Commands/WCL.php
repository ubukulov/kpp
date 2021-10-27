<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WCL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wcl:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $filename = $this->ask('Enter filename: ');
        $path_to_file = 'files/' . $filename;
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(public_path($path_to_file));
        /*$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $key=>$arr) {
            $company_id = $arr['A'];
            $gov_number = $arr['B'];
        }*/
        $worksheet = $spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
            //    even if a cell value is not set.
            // By default, only cells that have a value
            //    set will be iterated.
            foreach ($cellIterator as $cell) {
                dd($cell->getValue(), $cell);

            }

        }
    }
}
