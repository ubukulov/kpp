<?php

namespace App\Http\Controllers;

use App\Models\AshanaLog;
use App\Models\User;
use App\Traits\KitchenTraits;
use Illuminate\Http\Request;
use File;
use Auth;

class KitchenController extends BaseController
{
    use KitchenTraits;

    public function index()
    {
        return view('ashana.index');
    }

    public function getStatistics($option_id)
    {
        $user = Auth::user();
        $condition = "ashana_logs.cashier_id = $user->id AND ashana_logs.date >= CURDATE()";
        switch($option_id) {
            case 1:
                $result = $this->query($condition);
                break;

            case 2:
                $condition = "ashana_logs.cashier_id = $user->id AND ashana_logs.date = CURDATE() - INTERVAL 1 DAY";
                $result = $this->query($condition);
                break;

            case 3:
                $condition = "ashana_logs.cashier_id = $user->id AND ashana_logs.date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)";
                $result = $this->query($condition);
                break;

            default:
                abort(404, 'Не верная опция передано');
                break;
        }

        return $result;
    }

    public function query(string $condition): string
    {
        $logs = AshanaLog::whereRaw($condition)
            ->selectRaw('companies.short_ru_name, SUM(case when ashana_logs.din_type = 1 then 1 else 0 end) as stan,
                            SUM(case when ashana_logs.din_type = 2 then 1 else 0 end) as bul')
            ->join('companies', 'companies.id', 'ashana_logs.company_id')
            ->groupBy('ashana_logs.company_id')
            ->get();

        return json_encode($logs);
    }

    public function getUserInfo(Request $request)
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

        /*$user = User::where(['users.iin' => $username, 'users_histories.status' => 'works'])
                    ->selectRaw('users.*')
                    ->orWhere(['users.uuid' => $username])
                    ->join('users_histories', 'users_histories.user_id', 'users.id')
                    ->first();*/

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

        /*if($user) {
            $result = response([
                'full_name' => $user->full_name,
                'company_name' => $user->company->short_ru_name,
                'count' => $user->countAshanaToday(),
                'user_id' => $user->id,
                'image' => (file_exists(public_path() . $user->image)) ? $user->image : null
            ], 200);
        } else {
            $result = response('', 404);
        }*/

        //return $result;
    }

    public function fixChanges(Request $request)
    {
        $user_id     = (int) $request->input('user_id');
        $cashier_id  = (int) $request->input('cashier_id') ?? 0;
        $din_type = (int) $request->input('din_type');

        $user = User::find($user_id);
        if($user) {
            if($user->company_id == 0) {
                return response('В штрих-коде отсутствует компания, срочно обратитесь в ИТ', 406);
            }

            if($user->countAshanaToday() > 1) {
                return response('Ваш лимит обедов за сегодня исчерпан', 406);
            }

            // Подготовка папок для сохранение картинки
            $dir = '/kitchen_photos/'. substr(md5(microtime()), mt_rand(0, 30), 2) . '/' . substr(md5(microtime()), mt_rand(0, 30), 2);
            if($request->input('path_to_image') && !empty($request->input('path_to_image'))) {
                if(!File::isDirectory(public_path(). $dir)){
                    File::makeDirectory(public_path(). $dir, 0777, true);
                }
            }

            if ($request->input('path_to_image') && !empty($request->input('path_to_image'))){
                $image = $request->input('path_to_image'); // image base64 encoded
                preg_match("/data:image\/(.*?);/",$image,$image_extension); // extract the image extension
                $image = preg_replace('/data:image\/(.*?);base64,/','',$image); // remove the type part
                $image = str_replace(' ', '+', $image);
                $imageName = $user->id.'_'.time() . '.' . $image_extension[1]; //generating unique file name;
                File::put(public_path(). $dir.'/'.$imageName,base64_decode($image));
                $pathToImage = $dir.'/'.$imageName;
            } else {
                $pathToImage = null;
            }


            AshanaLog::create([
                'user_id' => $user->id, 'company_id' => $user->company_id, 'din_type' => $din_type,
                'cashier_id' => $cashier_id, 'path_to_image' => $pathToImage
            ]);

            return response([
                'din_type' => $din_type,
                'full_name' => $user->full_name,
                'count' => $user->countAshanaToday()
            ], 200);
        } else {
            return response('Не найден пользватель, срочно обратитесь в ИТ', 404);
        }
    }
}
