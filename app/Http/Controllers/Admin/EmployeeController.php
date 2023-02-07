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
use Image;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = User::orderBy('id', 'DESC')->get();
        return view('admin.employee.index', compact('employees'));
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
        return view('admin.employee.create', compact('companies', 'positions', 'roles', 'permissions', 'departments', 'kpp'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['password'] = (empty($data['password'])) ? null :bcrypt($data['password']);
        $user = User::create($data);

        // если у пользователя не задан uuid, то его генерируем и сохраняем
        //$str = $user->id."-".$user->full_name;
        $user->uuid = base64_encode($user->iin);
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
        $user = User::findOrFail($id);
        $data = $request->all();
        if(empty($user->password)) {
            $data['password'] = (empty($data['password'])) ? null :bcrypt($data['password']);
        } else {
            $data['password'] = (empty($data['password'])) ? $user->password :bcrypt($data['password']);
        }

        $data['badge'] = (isset($data['badge']) && $data['badge'] == 'on') ? 1 : 0;
        // если у пользователя не задан uuid, то его генерируем и сохраняем
        if(empty($user->uuid) || $data['iin'] != $user->iin) {
            //$str = $user->id."-".$user->full_name;
            $data['uuid'] = base64_encode($user->iin);
        }

        $user->update($data);

        // Зафиксируем статусы
        if ($user->hasWorkingStatus()) {
            if ($user->getWorkingStatus()->status != $data['status']) {
                $user->createUserHistory($user, $data);
            }
        } else {
            $user->createUserHistory($user, $data);
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

        return redirect()->route('employee.index');
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
        return view('admin.employee.badge', compact('user'));
    }

    public function badges($ids)
    {
        $ids = explode(",", $ids);
        $users = User::whereIn('id', $ids)->get();
        return view('admin.employee.badges', compact('users'));
    }
}
