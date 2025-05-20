<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\Kpp;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\DB;
use Image;
use Auth;
use CKUD;
use Cache;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(env('APP_ENV') == 'production') {
            if(Cache::has('ckud_users')) {
                $ckud_users_numbers = Cache::get('ckud_users');
            } else {
                $ckud_users_numbers = [];
                for($i=0; $i<10; $i++) {
                    $ckud_users = CKUD::getEmployees($i, 100);
                    if($ckud_users->result == 0) {
                        foreach($ckud_users->data as $datum) {
                            if(!array_key_exists($datum->Number, $ckud_users_numbers)) {
                                $ckud_users_numbers[$datum->Number] = $datum->ID;
                            }
                        }
                    }
                }

                Cache::put('ckud_users', $ckud_users_numbers, 3600);
            }
        } else {
            $ckud_users_numbers = [];
        }

        $employees = User::orderBy('id', 'DESC')->orderBy('users.full_name', 'ASC')
            ->selectRaw('users.*, companies.short_ru_name, positions.title as position_name, departments.title as department_name')
            ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
            ->join('companies', 'companies.id', 'users.company_id')
            ->join('positions', 'positions.id', 'users.position_id')
            ->leftJoin('departments', 'departments.id', 'users.department_id')
            ->get();

        return view('admin.employee.index', compact('employees', 'ckud_users_numbers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::all();
        $positions = Position::all();
        $roles = Role::all();
        $permissions = Permission::all();
        $departments = Department::all();
        $kpp = Kpp::all();
        $ckud_groups = CKUD::getGroups();
        return view('admin.employee.create',
            compact('companies', 'positions', 'roles', 'permissions', 'departments', 'kpp', 'ckud_groups')
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->all();
        $data['password'] = (empty($data['password'])) ? null :bcrypt($data['password']);
        $user = User::create($data);

        // если у пользователя не задан uuid, то его генерируем и сохраняем
        $user->uuid = $user->generateUniqueRandomNumber();
        $user->save();

        // Зафиксируем статусы
        if ($user->hasWorkingStatus()) {
            if ($user->getWorkingStatus()->status != $data['status']) {
                $user->createUserHistory($user, $data);
            }
        } else {
            $user->createUserHistory($user, $data);
        }

        // присвоение ролей к пользователю
        if(!empty($data['roles'])) {
            foreach($data['roles'] as $item) {
                $role = Role::findOrFail($item);
                $user->roles()->attach($role);
            }
        }

        // дать разрешение к пользователю
        if (!empty($data['permissions'])) {
            foreach($data['permissions'] as $value) {
                $permission = Permission::findOrFail($value);
                $user->permissions()->attach($permission);
            }
        }

        /*if($data['ckud_group_id'] != 0){
            $data = [
                'Comment' => $user->position->title,
                'employeeGroupID' => $data['ckud_group_id'],
                'Number' => $user->id,
                'KeyNumber' => $user->uuid,
                'ResidentialAddress' => $user->company->full_company_name,
                'photo_http' => $user->photo_http,
            ];

            $arr = explode(" ", $user->full_name);
            $LastName = (array_key_exists(0, $arr)) ? $arr[0] : '';
            $FirstName = (array_key_exists(1, $arr)) ? $arr[1] : '';
            $SecondName = (array_key_exists(2, $arr)) ? $arr[2] : '';
            $data['LastName'] = $LastName;
            $data['FirstName'] = $FirstName;
            $data['SecondName'] = $SecondName;

            CKUD::addEmployee($data);
        }*/

        return redirect()->route('employee.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $employee = User::findOrFail($id);
        $roles = Role::all();
        $permissions = Permission::all();
        $companies = Company::all();
        $positions = Position::all();
        $departments = Department::all();
        $kpp = Kpp::all();
        return view('admin.employee.edit', compact('employee', 'roles', 'permissions', 'companies', 'positions', 'departments', 'kpp'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $data = $request->all();
            if(empty($user->password)) {
                $data['password'] = (empty($data['password'])) ? $user->password :bcrypt($data['password']);
            } else {
                $data['password'] = (empty($data['password'])) ? $user->password :bcrypt($data['password']);
            }

            $data['badge'] = (isset($data['badge']) && $data['badge'] == 'on') ? 1 : 0;
            // если у пользователя не задан uuid, то его генерируем и сохраняем
            if(empty($user->uuid) || strlen($user->uuid) != 7) {
                $data['uuid'] = $user->generateUniqueRandomNumber();
            }

            $user->update($data);

            // Зафиксируем статусы
            if ($user->hasWorkingStatus()) {
                if ($user->getWorkingStatus()->status != $data['status']) {
                    $user->createUserHistory($user, $data);
                }
                // Если сотрудник уволень, то удаляем запись в СКУДе
                if($data['status'] == 'fired' && Cache::has('ckud_users')) {
                    $ckud_users = Cache::get('ckud_users');
                    if(array_key_exists($user->id, $ckud_users)) {
                        CKUD::removeEmployee($ckud_users[$user->id]);
                    }
                }
            } else {
                $user->createUserHistory($user, $data);
            }

            if(!empty($data['roles'])) {
                foreach($data['roles'] as $item) {
                    $role = Role::findOrFail($item);
                    if($role->slug == 'otdel-kadrov' && (isset($data['companies']) || isset($data['departments']))) {
                        $user->settings = json_encode(array(
                            'human_resources_departments' => [
                                'companies' => (isset($data['companies'])) ? implode(",", $data['companies']) : null,
                                'departments' => (isset($data['departments'])) ? implode(",", $data['departments']) : null,
                            ]
                        ));
                        $user->save();
                    }
                }
            }

            // присвоение ролей к пользователю
            if (!empty($data['roles'])) {
                $user->roles()->detach();
                foreach($data['roles'] as $item) {
                    $role = Role::findOrFail($item);
                    if(!$user->hasRole($role->slug)) {
                        $user->roles()->attach($role);
                    }
                }
            }

            // дать разрешение к пользователю
            if (!empty($data['permissions'])) {
                $user->permissions()->detach();
                foreach($data['permissions'] as $value) {
                    $permission = Permission::findOrFail($value);
                    if(!$user->hasPermission($permission->slug)) {
                        $user->permissions()->attach($permission);
                    }
                }
            }

            // Проверка на наличие картинки (лицевая)
            if ($request->path_docs_fac && !empty($request->path_docs_fac)){
                // Подготовка папок для сохранение картинки
                $dir = '/users_photos/'. substr(md5(microtime()), mt_rand(0, 30), 2) . '/' . substr(md5(microtime()), mt_rand(0, 30), 2);
                if(!File::isDirectory(public_path(). $dir)){
                    File::makeDirectory(public_path(). $dir, 0777, true);
                }

                if(!empty($user->image)){
                    unlink(public_path() . $user->image);
                }

                $image = $request->input('path_docs_fac'); // image base64 encoded
                preg_match("/data:image\/(.*?);/",$image,$image_extension); // extract the image extension
                $image = preg_replace('/data:image\/(.*?);base64,/','',$image); // remove the type part
                $image = str_replace(' ', '+', $image);
                $imageName = $user->id.'_f_'.time() . '.' . $image_extension[1]; //generating unique file name;
                $imageName2 = $user->id.'_l_'.time() . '.' . $image_extension[1]; //generating unique file name;
                //File::put(public_path(). $dir.'/'.$imageName,base64_decode($image));

                // create instance
                $img = Image::make(base64_decode($image));
                $img->save(public_path() . $dir . '/'.$imageName2);
                // resize image to fixed size
                $img->resize(200, 150);
                $img->save(public_path() . $dir . '/'.$imageName);
                $user->image = $dir.'/'.$imageName;
                $user->save();
            }

            DB::commit();

            return redirect()->route('employee.index');

        } catch (\Exception $exception) {
            DB::rollBack();
            dd("Error Code: " . $exception->getCode() . ". Error Text: " . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function badge($id)
    {
        $user = User::findOrFail($id);
        if(strlen($user->uuid) != 7) {
            $user->uuid = $user->generateUniqueRandomNumber();
            $user->save();
        }
        return view('admin.employee.badge', compact('user'));
    }

    public function badges($ids)
    {
        $ids = explode(",", $ids);
        $users = User::whereIn('id', $ids)->get();

        foreach($users as $user) {
            if(strlen($user->uuid) != 7) {
                $user->uuid = $user->generateUniqueRandomNumber();
                $user->save();
            }
        }

        return view('admin.employee.badges', compact('users'));
    }
}
