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
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Cache::forget('ckud_users');
        if(env('APP_ENV') == 'production') {
            if(Cache::has('ckud_users')) {
                $ckud_users_numbers = Cache::get('ckud_users');
            } else {
                $ckud_users_numbers = [];
                for($i=0; $i<10; $i++) {
                    $ckud_users = CKUD::getEmployees($i, 100);
                    if(!is_null($ckud_users) && $ckud_users->result == 0) {
                        foreach($ckud_users->data as $datum) {
                            if(!array_key_exists($datum->Number, $ckud_users_numbers)) {
                                $ckud_users_numbers[$datum->Number] = $datum->ID;
                            }
                        }
                    } else {
                        abort(403, "CKUD не доступен");
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

            if($data['kpp_id'] != 0) {
                $data['kpp_name'] = Kpp::find($data['kpp_id'])->name;
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

    public function list(Request $request)
    {
        try {
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

            $query = User::orderBy('users.id', 'DESC')->orderBy('users.full_name', 'ASC')
                ->selectRaw('users.*, companies.short_ru_name, positions.title as position_name, departments.title as department_name')
                ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
                ->join('companies', 'companies.id', '=', 'users.company_id')
                ->join('positions', 'positions.id', '=', 'users.position_id')
                ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
                ->groupBy('users.id');

            return DataTables::of($query)
                ->filterColumn('short_ru_name', function($query, $keyword) {
                    $query->where('companies.short_ru_name', 'like', "%{$keyword}%");
                })
                ->filterColumn('position_name', function($query, $keyword) {
                    $query->where('positions.title', 'like', "%{$keyword}%");
                })
                ->filterColumn('department_name', function($query, $keyword) {
                    $query->where('departments.title', 'like', "%{$keyword}%");
                })
                ->addColumn('checkbox', function ($employee) {
                    return '<input type="checkbox" v-model="ids" name="'.$employee->id.'" value="'.$employee->id.'">';
                })
                ->addColumn('skud', function ($employee) use ($ckud_users_numbers) {
                    if (array_key_exists($employee->id, $ckud_users_numbers)) {
                        return '<i class="fa fa-check-circle" style="color: green; font-size: 20px;"></i>';
                    } else {
                        return '<i class="fa fa-minus" style="color: red; font-size: 20px;"></i>';
                    }
                })
                ->addColumn('status', function ($employee) {
                    return trans("words.".$employee->status);
                })
                ->addColumn('badge', function ($employee) {
                    $icons = '';
                    if (strlen($employee->uuid) == 7) {
                        $icons .= '<i class="fa fa-id-card" style="color: green; font-size: 20px;"></i>&nbsp;';
                    }
                    $icons .= '<a target="_blank" href="'.route('admin.employee.badge', ['id' => $employee->id]).'"><i class="fa fa-print"></i></a>';
                    if ($employee->badge == 1) {
                        $icons .= '&nbsp;&nbsp;<i class="fa fa-check-circle" style="color: green; font-size: 20px;"></i>';
                    }
                    return $icons;
                })
                ->addColumn('action', function ($employee) {
                    return '<a href="'.route('employee.edit', ['employee' => $employee->id]).'">Ред.</a>
                        &nbsp;&nbsp;
                        <button type="button" class="btn btn-success" onclick="window.open(\'/admin/employee/'.$employee->id.'/auth\', \'_blank\')">Войти</button>';
                })
                ->rawColumns(['checkbox', 'skud', 'badge', 'action']) // чтобы HTML не экранировался
                ->filter(function ($query) use ($request) {
                    if ($request->search['value']) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->where('users.full_name', 'like', "%{$search}%")
                                ->orWhere('companies.short_ru_name', 'like', "%{$search}%")
                                ->orWhere('positions.title', 'like', "%{$search}%")
                                ->orWhere('departments.title', 'like', "%{$search}%")
                                ->orWhereRaw('(SELECT users_histories.status
                               FROM users_histories
                               WHERE users_histories.user_id = users.id
                               ORDER BY users_histories.id DESC LIMIT 1) like ?', ["%{$search}%"]);
                        });
                    }

                    // ФИЛЬТР ПО СТАТУСУ
                    if ($request->has('emp_status') && is_array($request->emp_status)) {

                        $statuses = $request->emp_status;
                        $placeholders = implode(",", array_fill(0, count($statuses), '?'));

                        $query->havingRaw("status IN ($placeholders)", $statuses);
                    }
                })
                ->make(true);
        } catch (\Throwable $e) {
            Log::error("DataTable error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
