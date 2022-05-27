<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WorldController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\RentalOrderReport;
use App\Http\Controllers\API\TopClientController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\ClientMasterDataController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [UserController::class, 'Login']);


Route::group(['prefix' => 'user', 'middleware' => 'auth:sanctum'], function () {
    Route::post('create', [UserController::class, 'Register']);
    Route::post('edit', [UserController::class, 'Edit']);
    Route::post('logout', [UserController::class, 'LogOut']);
    Route::get('list', [UserController::class, 'GetUsers']);
    Route::get('get-filter-data', [UserController::class, 'GetFilterData'])->name('get_filter_data');
    Route::get('get-registration-data', [UserController::class, 'GetRegisteration'])->name('get_registeration_data');
    Route::post('change-password', [UserController::class, 'ChangePasswordSubmit']);
    Route::get('change-password-questions', [UserController::class, 'ChangePasswordSubmit']);
    Route::get('get-city-by-country', [WorldController::class, 'getCityByCountry']);
    Route::get('profile', [UserController::class, 'GetProfile']);
    Route::post('add-role', [UserController::class, 'AddRole']);
    Route::post('change_user_status', [UserController::class, 'ChangeStatus']);
    Route::get('get-all-permissions', [UserController::class, 'GetAllPermissions']);
    Route::post('give-permission-to-role', [UserController::class, 'GivePermissionToRole']);
    Route::post('add-security-question', [UserController::class, 'AddSecurityQuestion']);
    Route::get('get-user', [UserController::class, 'GetUser']);

});

Route::get('user/get-security-questions', [UserController::class, 'GetSecurityQuestions']);


Route::group(['prefix' => 'client', 'middleware' => 'auth:sanctum'], function () {
    Route::get('search', [ClientController::class, 'Search']);
    Route::get('listing', [ClientController::class, 'Listing']);
    Route::post('add-client', [ClientController::class, 'AddClient']);
    Route::post('submit-rqr', [ClientController::class, 'SubmitRQR']);
    Route::get('rqr-form-data', [ClientController::class, 'GetRqrFormData']);
    Route::get('get-single-client', [ClientController::class, 'GetSingleclient']);
    Route::get('get-edit-rqr-data', [ClientController::class, 'GetEditRQRData']);
    Route::post('update-rqr-data', [ClientController::class, 'UpdateRQRData']);
    Route::get('view-rqr', [ClientController::class, 'ViewRQR']);
    Route::get('view-rqr-assigne', [ClientController::class, 'ViewRQRAssigne']);
    Route::post('change-rqr-assigne-status', [ClientController::class, 'ChangeRQRAssigneStatus']);
    // quotation rental form routes
    Route::get('get-qr-data', [ClientController::class, 'GetQRData']);
    Route::get('edit-qr-data', [ClientController::class, 'EditQRData']);
    Route::post('update-qr-data', [ClientController::class, 'UpdateQRData']);
    Route::get('view-qr-data', [ClientController::class, 'ViewQRData']);
    Route::post('change-qr-assigne-status', [ClientController::class, 'ChangeQRAssigneStatus']);

    Route::post('change-final-approve', [ClientController::class, 'ChangeFinalApprove']);

    Route::get('view-qr-assigne', [ClientController::class, 'ViewQRAssigne']);
    Route::get('qr-search', [ClientController::class, 'QRSearch']);
    Route::get('generate-pdf', [PDFController::class, 'generatePDF']);

    // Rental Delivery Order form routes
    Route::get('get-delivery-data', [ClientController::class, 'GetRentalDeliveryData']);
    Route::post('update-rdo-form', [ClientController::class, 'UpdateRDOForm']);
    Route::get('edit-rdo-data', [ClientController::class, 'EditRDOData']);
    Route::get('view-rdo-assigne', [ClientController::class, 'ViewRDOAssigne']);
    Route::get('view-rdo-form-data', [ClientController::class, 'ViewRDOFormData']);
    Route::post('change-rdo-assigne-status', [ClientController::class, 'ChangeRDOAssigneStatus']);
    Route::get('download-attachment', [ClientController::class, 'DownloadRDOAttachment']);

    // Prior Delivery Inspection form routes
    Route::get('get-prior-delivery-inspection', [ClientController::class, 'GetPriorDeliveryInspection']);
    Route::get('edit-pdi-form-step-one', [ClientController::class, 'EditPDFormStepOne']);
    Route::get('edit-pdi-form-step-two', [ClientController::class, 'EditPDFormStepTwo']);
    Route::post('update-pdi-form', [ClientController::class, 'UpdatePDIForm']);
    Route::get('view-pdi-form-data', [ClientController::class, 'ViewPDIData']);
    Route::get('change-pdi-assigne-status', [ClientController::class, 'ChangePDIAssigneStatus']);
    Route::get('view-pdi-assigne', [ClientController::class, 'ViewPDIAssigne']);
    Route::post('pdi-assigne-select', [ClientController::class, 'PdiAssignesSelect']);
    Route::post('assignes-update-pdi', [ClientController::class, 'UpdateAssigneesPDI']);
    

 // Rental Delivery Note form routes
    Route::get('get-renatl-delivery-note', [ClientController::class, 'GetRentalDeliveryNote']);
    Route::get('edit-rdn-form-step-one', [ClientController::class, 'EditRDNForm']);
    Route::post('update-rdn-form', [ClientController::class, 'UpdateRDNForm']);
    Route::get('view-rdn-form-data', [ClientController::class, 'ViewRDNData']);
    Route::post('change-rdn-assigne-status', [ClientController::class, 'ChangeRDNAssigneStatus']);
    Route::get('view-rdn-assigne', [ClientController::class, 'ViewRDNAssigne']);
    Route::get('edit-rdn-form-step-two', [ClientController::class, 'EditRDNFormStepTwo']);
    Route::post('assignes-update-rdn', [ClientController::class, 'UpdateAssigneesRDN']);


     // Off Hire form routes
    Route::get('get-off-hire-data', [ClientController::class, 'GetOffHireData']);
    Route::get('edit-off-form', [ClientController::class, 'EditOFFHIREForm']);
    Route::post('update-offhire-form', [ClientController::class, 'UpdateOFFHIREForm']);
    Route::get('view-off-hire-data', [ClientController::class, 'ViewOFFHIREData']);
    Route::get('view-off-hire-assigne', [ClientController::class, 'ViewOffHireAssigne']);
    Route::get('change-off-hire-assigne-status', [ClientController::class, 'ChangeOFFHIREAssigneStatus']);
    Route::post('change-final-approve-off-hire', [ClientController::class, 'ChangeFinalApproveOffHire']);

    // MCR form routes
    Route::get('get-mcr-form-data', [ClientController::class, 'GetMCRData']);
    Route::get('edit-mcr-form-stepone', [ClientController::class, 'EditMCRFormStepOne']);
    Route::get('edit-mcr-form-steptwo', [ClientController::class, 'EditMCRFormStepTwo']);
    Route::post('update-mcr-form', [ClientController::class, 'UpdateMCRForm']);
    Route::get('view-mcr-assignees', [ClientController::class, 'ViewMCRAssigne']);
    Route::get('view-mcr-form-stepone', [ClientController::class, 'ViewMCRFormStepOne']);
    Route::get('view-mcr-form-steptwo', [ClientController::class, 'ViewMCRFormStepTwo']);
    Route::get('change-mcr-assigne-status', [ClientController::class, 'ChangeMCRAssigneStatus']);
    Route::post('assignes-update-mcr', [ClientController::class, 'UpdateAssigneesMCR']);
    
    // EquMaintance form routes
    Route::get('get-equp-maintnce-data', [ClientController::class, 'GetEquMaintanceData']);
    Route::get('edit-eqp-form-stepone', [ClientController::class, 'EditEQPMFormStepOne']);
    Route::get('edit-eqp-form-steptwo', [ClientController::class, 'EditEQPMFormStepTwo']);
    Route::post('update-eqpm-form', [ClientController::class, 'UpdateEQPMData']);
    Route::get('view-eqp-form-stepone', [ClientController::class, 'ViewEQPMFormStepOne']);
    Route::get('view-eqp-form-steptwo', [ClientController::class, 'ViewEQPMFormStepTwo']);
    Route::post('change-eqpm-assigne-status', [ClientController::class, 'ChangeEQPMssigneStatus']);
    Route::post('change-eqpm-status-to-good', [ClientController::class, 'ChangeStatusToGood']);
    Route::get('view-eqpm-assigne', [ClientController::class, 'ViewEQPMAssigne']);
    Route::post('eqpm-mentance-assignes', [ClientController::class, 'EqupMentanceAssignes']);
    
    
    
});

Route::group(['prefix' => 'order', 'middleware' => 'auth:sanctum'], function () {
    
    Route::get('get-order-data', [OrderController::class, 'GetOrderData']);
    Route::get('view-order-data', [OrderController::class, 'ViewOrder']);
    Route::get('view-order-detail', [OrderController::class, 'ViewOrderDetail']);
});


Route::group(['prefix' => 'invoice', 'middleware' => 'auth:sanctum'], function () {
    Route::get('get-client-invoice-data', [InvoiceController::class, 'GetClientInvoiceData']);
    Route::get('edit-client-invoice-data', [InvoiceController::class, 'EditInvoiceData']);
    Route::get('edit-invoice-detail', [InvoiceController::class, 'InvoiceDetail']);
    Route::post('update-invoice-detail', [InvoiceController::class, 'UpdateInvoice']);
    Route::post('change-paid-status', [InvoiceController::class, 'ChangePaidStatus']);
    Route::get('view-client-invoice', [InvoiceController::class, 'ViewInvoiceStepOne']);
    Route::get('view-client-invoice-steptwo', [InvoiceController::class, 'ViewInvoiceStepTwo']);
    
});


Route::group(['prefix' => 'reports', 'middleware' => 'auth:sanctum'], function () {  
    Route::get('get-client-master-data', [ClientMasterDataController::class, 'GetClientMsaterData']);
    Route::get('edit-client-master-data', [ClientMasterDataController::class, 'EditClientMaster']);
    Route::get('get-client-master-orders', [ClientMasterDataController::class, 'GetClientMasterEquipment']);
    Route::get('get-client-order-list', [ClientMasterDataController::class, 'GetClientOrderList']);
    Route::get('client-master-order-detail', [ClientMasterDataController::class, 'ClientMasterEquipmentDetail']);
    Route::get('equipment-master-data-listing', [ClientMasterDataController::class, 'EquipmentMasterDataListing']);
    Route::get('edit-equipment-master-data', [ClientMasterDataController::class, 'EditEquipmentMasterData']);
    Route::get('get-client-data', [RentalOrderReport::class, 'GetClientData']);
    Route::get('get-order-data', [RentalOrderReport::class, 'GetOrderData']);
    Route::get('get-order-status-data', [RentalOrderReport::class, 'GetOrderStatusData']);
    Route::get('download-order-report', [RentalOrderReport::class, 'DownloadOderReport']);
    Route::post('download-order-report-pdf', [PDFController::class, 'GenrateOrderPDF']);
    
});
// 

Route::group(['prefix' => 'equipment', 'middleware' => 'auth:sanctum'], function () {
    Route::get('rqr-form-equipment-list', [EquipmentController ::class, 'GetRqrFormEquipmentList']);

});

Route::group(['prefix' => 'dashboard', 'middleware' => 'auth:sanctum'], function () {
    Route::get('get-top-clients', [TopClientController ::class, 'GetTopClientList']);
    Route::get('get-dashboard-order-list', [TopClientController ::class, 'GetDashboardOrderList']);
    Route::get('get-order-and-reservation', [TopClientController ::class, 'OrderAndReservation']);
    Route::get('get-top-equipment-list', [TopClientController ::class, 'TopEquipmentList']);

});


Route::post('user/password-change-request', [UserController::class, 'ForgotPasswordRequest'])->name('ChangePasswordRequest');

Route::post('user/change-forgot-password', [UserController::class, 'ChangeForgotPassword']);

Route::get('user/verify', [UserController::class, 'VerifyUser'])->name('verify_token');
//Route::get('test', function () {
//    \Spatie\Permission\Models\Permission::create(['name' => 'Add User','guard_name'=>'api']);
//    \Spatie\Permission\Models\Permission::create(['name' => 'Edit User','guard_name'=>'api']);
//    \Spatie\Permission\Models\Permission::create(['name' => 'Delete User','guard_name'=>'api']);
//    \Spatie\Permission\Models\Permission::create(['name' => 'Update User','guard_name'=>'api']);
//    \Spatie\Permission\Models\Permission::create(['name' => 'Create RQR','guard_name'=>'api']);
//    \Spatie\Permission\Models\Permission::create(['name' => 'Edit RQR','guard_name'=>'api']);
//    \Spatie\Permission\Models\Permission::create(['name' => 'Delete RQR','guard_name'=>'api']);
//});

Route::get('run_queue', function () {
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('route:cache');

});

Route::post('users/assign_role', function(){
    $user = (\App\Models\User::with('roles')->first());
    $user->with('roles');
    $user->assignRole('CEO');
    return $user->getAllPermissions();

});

Route::get('get-all-roles', function () {
    return \Spatie\Permission\Models\Role::all();
});


//Route::get('clearcache', function () {
//    \Illuminate\Support\Facades\Artisan::call('cache:clear');
//});


Route::fallback(function () {
    return response()->json(['message' => 'Not Found.',], 404);
});

