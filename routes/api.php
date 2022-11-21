<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\AttributesController;
use App\Http\Controllers\VariationController;
use App\Http\Controllers\MakesController;
use App\Http\Controllers\ModelsController;
use App\Http\Controllers\ModelYearsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ProductAddOnsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\BlogCategoriesController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;


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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/me', [AuthController::class, 'me']);
Route::post('/get-best-seller-products', [ProductsController::class, 'getBestSellerProducts']);


Route::post('/fecth-products', [ProductsController::class, 'fecthProducts']);

Route::post('/fecth-product-details', [ProductsController::class, 'fecthProductDetails']);
Route::post('/categories', [CategoriesController::class, 'categories']);
Route::get('/all-categories', [CategoriesController::class, 'Allcategories']);
Route::get('/all-blog-categories', [BlogCategoriesController::class, 'Allcategories']);
 Route::post('/save-cart-items', [CartController::class, 'saveCartItems']);
Route::post('/remove-cart-items', [CartController::class, 'removeCartItems']);
Route::get('/fetch/countries', [AuthController::class, 'fetchCountryies']);
Route::get('/fetch/country', [AuthController::class, 'fetchCountry']);
Route::post('/fetch/states', [AuthController::class, 'fetchStates']);
Route::get('/fecth-region-options', [ShippingController::class, 'fecthRegionOptions']);
Route::post('/update-shippng-address', [CartController::class, 'updateShippingAddress']);
Route::get('/seacrh-products', [ProductsController::class, 'seacrhProducts']);

Route::post('/save-ordered', [OrdersController::class, 'save']);
Route::get('/order/details/{order_id}', [OrdersController::class, 'orderDetails']);
Route::get('/fecth-makes', [MakesController::class, 'fetchMakes']);
Route::post('/fecth-models', [ModelsController::class, 'fetchModels']);
Route::get('/fecth-years', [ModelYearsController::class, 'fetchModelYears']);

Route::post('/send-contact-enquiry', [AuthController::class, 'sendContactEnquiry']);



Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', function(Request $request) {
        return auth()->user();
    });

    Route::get('/get-current-user-info', [AuthController::class, 'getUserInfo']);
    Route::post('/fetch-address', [AuthController::class, 'fetchAddress']);
    Route::get('/fecth-orders', [OrdersController::class, 'fecthOrders']);
    Route::post('/update-address', [AuthController::class, 'updateAddress']);
    Route::post('/update-user-details', [AuthController::class, 'updateUserDetails']);
    
    Route::post('/update-cart-items', [CartController::class, 'saveCartItems']);
    Route::post('/get-cart-items', [CartController::class, 'getCartItems']);
    Route::post('/apply-coupon', [CartController::class, 'applyCoupon']);


    Route::post('/upload-files', [FilesController::class, 'save']);
    Route::post('/fetch-files', [FilesController::class, 'fetchFiles']);
    Route::post('/fetch-files-details', [FilesController::class, 'fetchFilesDetails']);

    Route::get('/fetch/shipping', [ShippingController::class, 'fetchShipping']);
    Route::post('/shipping/save', [ShippingController::class, 'save']);
    Route::post('/shipping/details', [ShippingController::class, 'details']);
    Route::post('/shipping/delete', [ShippingController::class, 'delete']);

    Route::get('/fetch/shipping-classes', [ShippingController::class, 'fetchShippingClassess']);
    Route::post('/shipping-class/save', [ShippingController::class, 'saveShippingClassess']);
    Route::post('/shipping-class/details', [ShippingController::class, 'detailsShippingClassess']);
    Route::post('/shipping-class/delete', [ShippingController::class, 'deleteShippingClassess']);

    
    Route::get('/fetch/shipping-zoones', [ShippingController::class, 'fetchShippingZoones']);
    Route::post('/shipping-zoone/save', [ShippingController::class, 'saveShippingZoones']);
    Route::post('/shipping-zoone/details', [ShippingController::class, 'detailsShippingZoones']);
    Route::post('/shipping-zoone/delete', [ShippingController::class, 'deleteShippingZoones']);

    Route::post('/zoone-method/save', [ShippingController::class, 'saveZooneMethod']);
    Route::post('/zoone-methode/details', [ShippingController::class, 'zoomMethosDetails']);
    Route::post('/zoone-method/delete', [ShippingController::class, 'deleteZooneMethod']);
    Route::post('/zoone-method/update-status', [ShippingController::class, 'UpdateStatusZooneMethod']);
    Route::post('/zoone-method/updates', [ShippingController::class, 'UpdateZooneMethod']);


    Route::post('/category/save', [CategoriesController::class, 'save']);
    Route::post('/category/details', [CategoriesController::class, 'details']);
    Route::post('/category/delete', [CategoriesController::class, 'delete']);

    Route::post('/blog-category/save', [BlogCategoriesController::class, 'save']);
    Route::post('/blog-category/details', [BlogCategoriesController::class, 'details']);
    Route::post('/blog-category/delete', [BlogCategoriesController::class, 'delete']);
    Route::post('/blog-categories', [BlogCategoriesController::class, 'categories']);

    
    Route::get('/fetch/makes', [MakesController::class, 'fetchMakes']);
    Route::post('/make/save', [MakesController::class, 'save']);
    Route::post('/make/details', [MakesController::class, 'details']);
    Route::post('/make/delete', [MakesController::class, 'delete']);

    Route::post('/fetch/models', [ModelsController::class, 'fetchModels']);
    Route::post('/model/save', [ModelsController::class, 'save']);
    Route::post('/model/details', [ModelsController::class, 'details']);
    Route::post('/model/delete', [ModelsController::class, 'delete']);

    Route::post('/fetch/blogs', [BlogController::class, 'index']);
    Route::post('/blog/save', [BlogController::class, 'save']);
    Route::post('/blog/details', [BlogController::class, 'details']);
    Route::post('/blog/delete', [BlogController::class, 'delete']);

    Route::post('/fetch/pages', [PageController::class, 'index']);
    Route::post('/page/save', [PageController::class, 'save']);
    Route::post('/page/details', [PageController::class, 'details']);
    Route::post('/page/delete', [PageController::class, 'delete']);

    Route::get('/fetch/model-years', [ModelYearsController::class, 'fetchModelYears']);
    Route::post('/model-year/save', [ModelYearsController::class, 'save']);
    Route::post('/model-year/details', [ModelYearsController::class, 'details']);
    Route::post('/model-year/delete', [ModelYearsController::class, 'delete']);


    Route::post('/save/attributes', [AttributesController::class, 'saveAttributes']);
    Route::post('/delete/attribute', [AttributesController::class, 'deleteAttributes']);
    Route::get('/fetch-attributes', [AttributesController::class, 'fetchAttributes']);

    Route::post('/add/variation', [VariationController::class, 'addVariation']);
    Route::post('/fetch-variations', [VariationController::class, 'fetchVariation']);
    Route::post('/save/variation', [VariationController::class, 'saveVariation']);
    Route::post('/delete/variation', [VariationController::class, 'deleteVariation']);
    Route::post('/update/variation/image', [VariationController::class, 'updateVariationImage']);


    Route::post('/products', [ProductsController::class, 'index']);
    Route::post('/product/save', [ProductsController::class, 'save']);
    Route::post('/product/details', [ProductsController::class, 'details']);
    Route::post('/product/delete', [ProductsController::class, 'delete']);

    Route::get('/fetch/coupons', [CouponController::class, 'fetchCoupons']);
    Route::post('/coupon/save', [CouponController::class, 'save']);
    Route::post('/coupon/details', [CouponController::class, 'details']);
    Route::post('/coupon/delete', [CouponController::class, 'delete']);

    Route::get('/fetch/product-add-ons', [ProductAddOnsController::class, 'fetchAddOns']);
    Route::post('/product-add-ons/save', [ProductAddOnsController::class, 'save']);
    Route::post('/product-add-ons/details', [ProductAddOnsController::class, 'details']);
    Route::post('/product-add-ons/delete', [ProductAddOnsController::class, 'delete']);
    Route::post('/delete/addon-options', [ProductAddOnsController::class, 'deleteAddonOptions']);

    Route::get('/fetch/users', [AuthController::class, 'users']);
    Route::post('/user/details', [AuthController::class, 'userDetails']);
    Route::post('/user/save', [AuthController::class, 'saveUser']);
    Route::post('/import-users', [AuthController::class, 'importUsers']);


    Route::get('/fetch/orders', [OrdersController::class, 'orders']);
    Route::get('/fetch/order/details/{order_id}', [OrdersController::class, 'orderDetails']);
    Route::post('/import-orders', [OrdersController::class, 'importOrders']);


    
    
    Route::post('/sign-out', [AuthenticationController::class, 'logout']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

