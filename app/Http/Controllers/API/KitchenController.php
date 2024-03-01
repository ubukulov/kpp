<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AshanaLog;
use App\Models\User;
use Illuminate\Http\Request;

class KitchenController extends BaseApiController
{
    public function getItems()
    {
        $items = AshanaLog::where(['user_id' => $this->user->id])
            ->selectRaw('ashana_logs.*, companies.short_en_name as company')
            ->selectRaw(
                'CASE
                    WHEN ashana_logs.din_type = 1 THEN "Стандарт"
                    ELSE "Булочки"
                END
                as type')
            ->join('companies', 'companies.id', 'ashana_logs.company_id')
            ->whereRaw('MONTH(ashana_logs.date) = MONTH(CURRENT_DATE())
                        AND YEAR(ashana_logs.date) = YEAR(CURRENT_DATE())')
            ->orderBy('ashana_logs.id', 'DESC')
            ->get();
        return response()->json($items);
    }

    public function getItemsByFilter(Request $request)
    {
        $date_start = $request->input('date_start');
        $date_end   = $request->input('date_end');
        $items = AshanaLog::where(['ashana_logs.user_id' => $this->user->id])
            ->selectRaw('ashana_logs.*, companies.short_en_name as company')
            ->selectRaw(
                'CASE
                    WHEN ashana_logs.din_type = 1 THEN "Стандарт"
                    ELSE "Булочки"
                END
                as type')
            ->join('companies', 'companies.id', 'ashana_logs.company_id')
            ->whereDate('ashana_logs.date', '>=', $date_start)
            ->whereDate('ashana_logs.date', '<=', $date_end)
            ->orderBy('ashana_logs.id', 'DESC')
            ->get();
        return response()->json($items);
    }

    public function getUserByUuid(Request $request)
    {
        $username = $request->input('username');
        $username = trim($username);
        if($this->isKazakh($username)) {
            return response([
                'message' => 'ПОЖАЛУЙСТА ПОМЕНЯЙТЕ РАСКЛАДКУ НА АНГЛИЙСКУЮ ИЛИ НА РУССКУЮ'
            ], 406);
        }

        if($this->isRussian($username)) {
            $username = $this->switch_en($username);
        }

        $users = User::where(['users.iin' => $username])->orWhere(['users.uuid' => $username])->get();

        if(count($users) == 0) {
            return response([
                'message' => 'НЕ НАЙДЕНО. ОБРАЩАЙТЕСЬ В ОТДЕЛ КАДРОВ ЗА ТАЛОНОМ.'
            ], 404);
        }

        foreach($users as $user) {
            if($user->hasWorkPermission()) {

                if($user->company->ashana === 1) {
                    return response([
                        'message' => 'Для сотрудников этой компании запрещено питаться в столовое'
                    ], 406);
                }

                return response([
                    'full_name' => $user->full_name,
                    'company_name' => $user->company->short_ru_name,
                    'count' => $user->countAshanaToday(),
                    'user_id' => $user->id,
                    'image' => (file_exists(public_path() . $user->image)) ? $user->image : null
                ], 200);
            }
        }

        return response([
            'message' => 'СОТРУДНИК УВОЛЕН'
        ], 406);
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

    public function fixChanges(Request $request)
    {
        $user_id     = (int) $request->input('user_id');
        $user = User::findOrFail($user_id);
        if($user) {
            if($user->company_id == 0) {
                return response('В штрих-коде отсутствует компания, срочно обратитесь в ИТ', 406);
            }

            $countAshanaToday = $user->countAshanaToday();

            if($user->position_id == 188 && $countAshanaToday == 1) {
                return response('Ваш лимит обедов за сегодня исчерпан', 406);
            }

            if($countAshanaToday > 1) {
                return response('Ваш лимит обедов за сегодня исчерпан', 406);
            }

            AshanaLog::create([
                'user_id' => $user->id, 'company_id' => $user->company_id, 'din_type' => 1,
                'cashier_id' => $this->user->id
            ]);

            return response([
                'din_type' => 1,
                'full_name' => $user->full_name,
                'count' => $user->countAshanaToday()
            ], 200);
        } else {
            return response('Не найден пользватель, срочно обратитесь в ИТ', 404);
        }
    }
}
