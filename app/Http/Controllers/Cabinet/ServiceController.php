<?php

namespace App\Http\Controllers\Cabinet;

use App\Classes\Bitrix24;
use App\Http\Controllers\Controller;
use App\Models\Service\Service;
use App\Models\Service\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $service_requests = ServiceRequest::where(['company_id' => Auth::user()->company_id])
                ->with('user', 'service', 'status')
                ->orderBy('created_at', 'desc')
                ->get();
        return view('cabinet.service.index', compact('service_requests'));
    }

    public function create()
    {
        $services = Service::all();
        return view('cabinet.service.create', compact('services'));
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $user = Auth::user();
            $company = $user->company;
            $serviceCategory = Service::findOrFail($data['root_service_id']);
            $service = Service::findOrFail($data['service_id']);

            $data['name'] = $service->name;
            $data['serviceCategoryId'] = $serviceCategory->bitrix24_id;
            $data['company'] = $company->full_company_name;
            $data['companyBin'] = $company->bin;
            $data['quantity'] = 5;

            $contractor = Service::findOrFail($data['service_id'])
                ->contractors()
                ->first();

            $data['contractorId'] = $contractor->bitrix24_id;
            $data['fullName'] = $user->full_name;
            $data['email'] = $user->email;
            $data['phone'] = $user->phone;
            $data['typeCompany'] = $company->type_company;

            $bitrix24 = new Bitrix24();

            $serviceRequest = ServiceRequest::create([
                'company_id' => $company->id, 'service_id' => $data['service_id'], 'user_id' => $user->id, 'request_date' => Carbon::now(),
                'quantity' => 5, 'address' => 'test', 'total_price' => 1000, 'comment' => $data['description'], 'status_id' => 1
            ]);

            $data['dateTime'] = $serviceRequest->request_date;

            if($result = $bitrix24->createSmartProcess($data)) {
                $serviceRequest->bitrix24_id = $result['item']['id'];
                $serviceRequest->save();

                DB::commit();

                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false], 409);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
}
