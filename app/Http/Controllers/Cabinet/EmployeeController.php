<?php

namespace App\Http\Controllers\Cabinet;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Position;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Auth;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Auth::user()->company;
        $employees = User::where(['company_id' => $company->id])->get();
        return view('cabinet.employee.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $positions = Position::all();
        return view('cabinet.employee.create', compact('positions'));
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
        $data['company_id'] = Auth::user()->company->id;
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        // если у пользователя не задан uuid, то его генерируем и сохраняем
        if(empty($user->uuid)){
            $str = $user->id."-".$user->full_name;
            $user->uuid = base64_encode($str);
            $user->save();
        }

        return redirect()->route('cabinet.employees.index');
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
        $positions = Position::all();
        return view('cabinet.employee.edit', compact('employee', 'positions'));
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

        return redirect()->route('cabinet.employees.index');
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

        // если у пользователя не задан uuid, то его генерируем и сохраняем
        if(empty($user->uuid)) {
            $str = $user->id."-".$user->full_name;
            $user->uuid = base64_encode($str);
            $user->save();
        }

        $code = $user->uuid;

        return view('cabinet.employee.badge', compact('user', 'code'));
    }
}
