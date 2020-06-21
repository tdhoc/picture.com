<?php

use Illuminate\Support\Facades\Route;

use App\Http\Model\Users;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('main');
});

Route::get('test', function () {
    return view('t');
});

Route::bind('user', function ($value) {
    return Users::where('id', $value)->orWhere('username', $value)->first();
});

Route::group(['prefix' => 'users'], function(){
    Route::get('register', 'UsersController@getRegister');
    Route::post('register', 'UsersController@postRegister');
    Route::get('login', 'UsersController@getLogin');
    Route::post('login', 'UsersController@postLogin');
    Route::get('profile/{user}', 'ProfileController@getProfile');
    Route::get('edit-profile', 'ProfileController@getEditProfile');
    Route::post('edit-profile', 'ProfileController@postEditProfile');
    Route::group(['middleware' => 'auth'], function(){
        Route::get('logout', 'UsersController@getLogout');
    });
});

Route::group(['middleware' => 'auth'], function(){
    Route::resource('pictures', 'PictureController', ['only' => ['index', 'store', 'destroy']]); 
});

Route::group(['prefix' => 'picture', 'middleware' => 'auth'], function(){
    Route::get('submit', 'PictureController@getSubmit');
    Route::post('submit', 'PictureController@postSubmit');
    Route::post('get-sub-category-suggestion', 'PictureController@getSubcategorySuggestion');
    Route::post('get-tag-suggestion', 'PictureController@getTagSuggestion');
});
Route::get('picture/show/{id}', 'PictureController@getShow');
Route::group(['prefix' => 'by_category'], function(){
    Route::get('{id}/{page}', 'PictureListController@getByCategory');
    Route::get('{id}', 'PictureListController@getByCategory');
});
Route::group([], function(){
    Route::post('get_infinite_data', 'PictureListController@getInfiniteData');
    Route::post('sort', 'PictureListController@sort');
    Route::post('get_submission_infos', 'PictureListController@getSubmissionInfos');
});

Route::group(['prefix' => 'admin',  'middleware' => 'admin'], function(){
    Route::get('category', 'CategoryController@getCategory');
    Route::post('addCategory', 'CategoryController@postAddCategory');
    Route::post('editCategory', 'CategoryController@postEditCategory');
    Route::post('deleteCategory', 'CategoryController@postDeleteCategory');
    Route::post('addSubcategory', 'CategoryController@postAddSubcategory');
    Route::post('editSubcategory', 'CategoryController@postEditSubcategory');
    Route::post('deleteSubcategory', 'CategoryController@postDeleteSubcategory');

    Route::get('users-management', 'UsersManagementController@getUser');
    Route::post('addUser', 'UsersManagementController@postAdd');
    Route::post('editUser', 'UsersManagementController@postEdit');
    Route::post('deleteUser', 'UsersManagementController@postDelete');
});