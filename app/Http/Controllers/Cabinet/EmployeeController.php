<?php

namespace App\Http\Controllers\Cabinet;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Auth;
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
        $companies = Company::all();
        $positions = Position::all();
        $departments = Department::all();
        return view('cabinet.employee.create', compact('positions', 'companies', 'departments'));
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

		if(isset($data['password'])) {
			$data['password'] = bcrypt($data['password']);
		}
        $user = User::create($data);

        // если у пользователя не задан uuid, то его генерируем и сохраняем
        if(empty($user->uuid)){
            //$str = $user->id."-".$user->full_name;
            $user->uuid = base64_encode($user->iin);
            $user->save();
        }

        // Зафиксируем статусы
        if ($user->hasWorkingStatus()) {
            if ($user->getWorkingStatus()->status != $data['status']) {
                $user->createUserHistory($user, $data);
            }
        } else {
            $user->createUserHistory($user, $data);
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
        $companies = Company::all();
        $positions = Position::all();
        $departments = Department::all();
        return view('cabinet.employee.edit', compact('employee', 'positions', 'companies', 'departments'));
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
            //$str = $user->id."-".$user->full_name;
            $data['uuid'] = base64_encode($user->iin);
        }

        // Зафиксируем статусы
        if ($user->hasWorkingStatus()) {
            if ($user->getWorkingStatus()->status != $data['status']) {
                $user->createUserHistory($user, $data);
            }
        } else {
            $user->createUserHistory($user, $data);
        }

        $user->update($data);

        // Ручной
        if ($request->hasFile('change_avatar')){
            // Подготовка папок для сохранение картинки
            $dir = '/users_photos/'. substr(md5(microtime()), mt_rand(0, 30), 2) . '/' . substr(md5(microtime()), mt_rand(0, 30), 2);
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }

            if(!empty($user->image)){
                unlink(public_path() . $user->image);
            }

            $image = $request->file('change_avatar');

            $imageName = $user->id.'_f_'.time() . '.' . $image->getClientOriginalExtension(); //generating unique file name;
            $imageName2 = $user->id.'_l_'.time() . '.' . $image->getClientOriginalExtension(); //generating unique file name;

            // create instance
            $img = Image::make($image->getRealPath());
            $img->save(public_path() . $dir . '/'.$imageName2);
            // resize image to fixed size
            $img->resize(200, 150);
            $img->save(public_path() . $dir . '/'.$imageName);
            $user->image = $dir.'/'.$imageName;
            $user->save();
        }

        // Камера
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
        return view('cabinet.employee.badge', compact('user'));
    }

    public function badges($ids)
    {
        $ids = explode(",", $ids);
        $users = User::whereIn('id', $ids)->get();
        return view('cabinet.employee.badges', compact('users'));
    }
}
