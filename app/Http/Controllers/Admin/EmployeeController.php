<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = User::all();
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
        return view('admin.employee.create', compact('companies', 'positions', 'roles', 'permissions'));
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
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        // если у пользователя не задан uuid, то его генерируем и сохраняем
        $str = $user->id."-".$user->full_name;
        $user->uuid = base64_encode($str);
        $user->save();

        // присвоение ролей к пользователю
        foreach($request->input('roles') as $item) {
            $role = Role::findOrFail($item);
            $user->roles()->attach($role);
        }

        // дать разрешение к пользователю
        foreach($request->input('permissions') as $value) {
            $permission = Permission::findOrFail($value);
            $user->permissions()->attach($permission);
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
        return view('admin.employee.edit', compact('employee', 'roles', 'permissions', 'companies', 'positions'));
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
        // если у пользователя не задан uuid, то его генерируем и сохраняем
        if(empty($user->uuid)) {
            $str = $user->id."-".$user->full_name;
            $data['uuid'] = base64_encode($str);
        }

        $user->update($data);

        // присвоение ролей к пользователю
        foreach($request->input('roles') as $item) {
            $role = Role::findOrFail($item);
            if(!$user->hasRole($role->slug)) {
                $user->roles()->attach($role);
            }
        }

        // дать разрешение к пользователю
        foreach($request->input('permissions') as $value) {
            $permission = Permission::findOrFail($value);
            if(!$user->hasPermission($permission->slug)) {
                $user->permissions()->attach($permission);
            }
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
}
