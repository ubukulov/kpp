<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class CKUDController extends BaseController
{
    public function index()
    {
        return view('admin.ckud.index');
    }

    public function getUsers($company_id)
    {
        $result = DB::select("SELECT
users.id,
users.full_name,
positions.title as pos_name,
departments.title as dep_name,
companies.short_ru_name as company,
users.uuid,
CONCAT('https://kpp.dlg.kz:8900', REGEXP_REPLACE(users.image, 'f', 'l')) as img,
(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status
FROM `users`
INNER JOIN companies ON companies.id=users.company_id
INNER JOIN positions ON positions.id=users.position_id
LEFT JOIN departments ON departments.id=users.department_id
WHERE
users.company_id=$company_id AND LENGTH(users.uuid) = 7
HAVING
status='works'");

        $users = [];
        foreach ($result as $item) {
            $arr = explode(' ', $item->full_name);
            $lastname = (array_key_exists(0, $arr)) ? $arr[0] : '';
            $firstname = (array_key_exists(1, $arr)) ? $arr[1] : '';
            $patronymic = (array_key_exists(2, $arr)) ? $arr[2] : '';
            $users[] = [
                'id' => $item->id,
                'lastname' => $lastname,
                'firstname' => $firstname,
                'patronymic' => $patronymic,
                'pos_name' => $item->pos_name,
                'dep_name' => $item->dep_name,
                'company' => $item->company,
                'uuid' => $item->uuid,
                'img' => $item->img,
            ];
        }

        return response()->json($users);

    }
}
