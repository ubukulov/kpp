<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Services\WmsConnectionService;

class BaseController extends Controller
{
    protected ?WmsConnectionService $wmsConnectionService = null;

    public function __construct()
    {
        $companies = Cache::remember('shared.companies', 3600, function () {
            return Company::all();
        });

        $asCompanies = Cache::remember('shared.as_companies', 3600, function () {
            return Company::where('ashana', 0)->get();
        });

        View::share('companies', $companies);
        View::share('as_companies', $asCompanies);
    }

    protected function wmsConnectionService(): WmsConnectionService
    {
        if ($this->wmsConnectionService === null) {
            $this->wmsConnectionService = app(WmsConnectionService::class);
        }

        return $this->wmsConnectionService;
    }

    public function switch_en($str): string
    {
        $converter = [
            'а' => 'f',	'б' => ',',	'в' => 'd',	'г' => 'u',	'д' => 'l',	'е' => 't',	'ё' => '`',
            'ж' => ';',	'з' => 'p',	'и' => 'b',	'й' => 'q',	'к' => 'r',	'л' => 'k',	'м' => 'v',
            'н' => 'y',	'о' => 'j',	'п' => 'g',	'р' => 'h',	'с' => 'c',	'т' => 'n',	'у' => 'e',
            'ф' => 'a',	'х' => '[',	'ц' => 'w',	'ч' => 'x',	'ш' => 'i',	'щ' => 'o',	'ь' => 'm',
            'ы' => 's',	'ъ' => ']',	'э' => "'",	'ю' => '.',	'я' => 'z',

            'А' => 'F',	'Б' => '<',	'В' => 'D',	'Г' => 'U',	'Д' => 'L',	'Е' => 'T',	'Ё' => '~',
            'Ж' => ':',	'З' => 'P',	'И' => 'B',	'Й' => 'Q',	'К' => 'R',	'Л' => 'K',	'М' => 'V',
            'Н' => 'Y',	'О' => 'J',	'П' => 'G',	'Р' => 'H',	'С' => 'C',	'Т' => 'N',	'У' => 'E',
            'Ф' => 'A',	'Х' => '{',	'Ц' => 'W',	'Ч' => 'X',	'Ш' => 'I',	'Щ' => 'O',	'Ь' => 'M',
            'Ы' => 'S',	'Ъ' => '}',	'Э' => '"',	'Ю' => '>',	'Я' => 'Z',

            '"' => '@',	'№' => '#',	';' => '$',	':' => '^',	'?' => '&',	'.' => '/',	',' => '?',
        ];

        return strtr($str, $converter);
    }

    public function isRussian($str): bool
    {
        return (bool) preg_match('/[А-Яа-яЁё]/u', $str);
    }

    public function isKazakh($str): bool
    {
        return (bool) preg_match('/[ҚқҰұҮүҒғҢңІіӘәӨөҺһ]/u', $str);
    }
}
