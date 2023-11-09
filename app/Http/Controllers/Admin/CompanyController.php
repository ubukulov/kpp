<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\Kpp;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index()
    {
        $companies = Company::all();
        return view('admin.company.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        $kpp = Kpp::all();
        return view('admin.company.create', compact('kpp'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $company = Company::create($request->all());

            // добавление к компанию выбранные КПП
            foreach($request->input('kpp') as $item) {
                $kpp = Kpp::findOrFail($item);
                $company->kpps()->attach($kpp);
            }

            DB::commit();
            return redirect()->route('company.index');

        } catch (\Exception $exception) {
            DB::rollBack();
            dd("Попробуйте чуть позже. Ошибка: " . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Factory|View
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        $kpp = Kpp::all();
        return view('admin.company.edit', compact('company', 'kpp'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->update($request->all());

            // Обновление записей по КПП
            $company->kpps()->detach();
            foreach($request->input('kpp') as $item) {
                $kpp = Kpp::findOrFail($item);
                if(!$company->hasKpp($kpp->name)) {
                    $company->kpps()->attach($kpp);
                }
            }

            DB::commit();
            return redirect()->route('company.index');

        } catch (\Exception $exception) {
            DB::rollBack();
            dd("Произошло ошибка при обновление данных. Текст ошибки: " . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        //
    }
}
