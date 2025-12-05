<?php

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TechniqueController;
use App\Http\Controllers\API\KitchenController;
use App\Http\Controllers\API\WebcontController;
use App\Http\Controllers\API\AggregationController;
use App\Http\Controllers\API\InventSamsungController;
use App\Http\Controllers\API\FirebaseController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [ApiController::class, 'loginUser']);

Route::prefix('v2')->group(function () {
    Route::get('get-companies', [ApiController::class, 'getCompanies']);
    Route::get('get-bt', [ApiController::class, 'getBt']);
    Route::get('get-lift-capacity', [ApiController::class, 'getLiftCapacity']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/get-companies-info', [ApiController::class, 'getCompaniesInfo']);
    Route::get('get-all-roles', [ApiController::class, 'getAllRoles']);
    Route::get('/get-roles', [UserController::class, 'getUserRoles']);
    Route::get('printers', [ApiController::class, 'getPrinters']);

    Route::prefix('user')->group(function () {
        Route::get('/get-user-info', [UserController::class, 'getUserInfoByToken']);
    });

    # Technique
    Route::prefix('technique')->group(function () {
        Route::get('/get-technique-places', [TechniqueController::class, 'getTechniquePlaces']);
        Route::post('/get-information-by-qr-code', [TechniqueController::class, 'getInformationByQRCode']);
        Route::post('/receive-technique-to-place', [TechniqueController::class, 'receiveTechniqueToPlace']);
        Route::post('/move-technique-to-other-place', [TechniqueController::class, 'moveTechniqueToOtherPlace']);
        Route::post('/shipping-technique', [TechniqueController::class, 'shippingTechnique']);
    });

    # Ashana
    Route::prefix('ashana')->group(function () {
        Route::get('/get-items', [KitchenController::class, 'getItems']);
        Route::post('/get-items-by-filter', [KitchenController::class, 'getItemsByFilter']);
        Route::post('get-user-by-uuid', [KitchenController::class, 'getUserByUuid']);
        Route::post('fix-changes', [KitchenController::class, 'fixChanges']);
    });

    # Webcont
    Route::prefix('webcont')->group(function () {
        Route::get('get-webcont-containers-zones', [WebcontController::class, 'getContainersZones']);
        Route::get('get-webcont-techniques', [WebcontController::class, 'getTechniques']);
        Route::post('get-container-info', [WebcontController::class, 'getContainerInfo']);
        Route::get('get-free-rows', [WebcontController::class, 'getFreeRows']);
        Route::get('get-free-slingers', [WebcontController::class, 'getFreeSlingers']);
        Route::get('get-container-tasks', [WebcontController::class, 'getContainerTasks']);
        Route::get('get-container-task/{id}/details', [WebcontController::class, 'getContainerTaskDetails']);
    });

    # Aggregation JTI
    Route::post('aggregation', [AggregationController::class, 'aggregation']);
    Route::get('get-aggregation-codes', [AggregationController::class, 'getAggregationCodes']);
    Route::get('get-aggregation-qty', [AggregationController::class, 'getAggregationQty']);
    Route::post('delete-aggregation-sscc', [AggregationController::class, 'deleteAggregationSscc']);
    Route::get('get-aggregations-ordercodes', [AggregationController::class, 'getAggregationOrderCodes']);
    Route::post('aggregation-order', [AggregationController::class, 'aggregationOrder']);
    Route::get('get-aggregation-order-scanqty', [AggregationController::class, 'getAggregationOrderScanQty']);

    # Inventory Samsung
    Route::get('invent-list', [InventSamsungController::class, 'getInventCodeList']);
    Route::post('inventory', [InventSamsungController::class, 'inventory']);
    Route::get('inventory', [InventSamsungController::class, 'getInventory']);
    Route::get('inventory/{id}', [InventSamsungController::class, 'getInventoryById']);

    # Parking
    Route::post('parking', [InventSamsungController::class, 'parking']);

    # Firebase
    Route::post('save-fcm-token', [FirebaseController::class, 'saveFcmToken']);
});
