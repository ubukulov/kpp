<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AlertItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatcherController extends Controller
{
    public function list()
    {
        $alertItems = AlertItem::all();
        return view('admin.dispatcher.index', compact('alertItems'));
    }

    public function listCreate()
    {
        $alerts = Alert::all();
        $users = User::join('companies', 'companies.id', 'users.company_id')
            ->selectRaw('users.id, users.full_name, companies.short_ru_name, positions.title as p_name')
            ->join('positions', 'positions.id', 'users.position_id')
            ->get();
        return view('admin.dispatcher.create', compact('alerts', 'users'));
    }

    public function listStore(Request $request)
    {
        $data = $request->except('users');
        $data['sms'] = (empty($data['sms'])) ? 0 : 1;
        $data['voice'] = (empty($data['voice'])) ? 0 : 1;
        $data['whatsapp'] = (empty($data['whatsapp'])) ? 0 : 1;

        try {
            $alertItem = AlertItem::create($data);
            foreach($request->input('users') as $user_id) {
                $user = User::findOrFail($user_id);
                $alertItem->users()->attach($user);
            }
            DB::commit();
            return redirect()->route('admin.dispatcher.alerts.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            dd("Error: " . $exception->getMessage());
        }
    }

    public function index()
    {
        $alerts = Alert::all();
        return view('admin.dispatcher.alert.index', compact('alerts'));
    }

    public function create()
    {
        return view('admin.dispatcher.alert.create');
    }

    public function edit($id)
    {
        $alert = Alert::findOrFail($id);
        return view('admin.dispatcher.alert.edit', compact('alert'));
    }

    public function store(Request $request)
    {
        Alert::create($request->all());
        return redirect()->route('admin.dispatcher.alerts.index');
    }

    public function update(Request $request, $id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update($request->all());
        return redirect()->route('admin.dispatcher.alerts.index');
    }
}
