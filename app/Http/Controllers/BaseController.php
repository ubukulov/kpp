<?php

namespace App\Http\Controllers;
use App\Models\Company;
use View;

class BaseController extends Controller
{
    public function __construct()
    {
        View::share('companies', Company::all());
        View::share('as_companies', Company::where('ashana', 0)->get());
    }

    public function switch_en($str)
    {
        $converter = array(
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
        );

        return strtr($str, $converter);
    }

    public function isRussian($str)
    {
        return preg_match('/[А-Яа-яЁё]/u', $str);
    }

    public function isKazakh($str)
    {
        return preg_match('/[ҚқҰұҮүҒғҢңІіӘәӨөҺһ]/u', $str);
    }
}
