<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register','Api\UserController@register');
Route::post('login','Api\UserController@login');

Route::post('updateuser','Api\UserController@updateUser');

Route::get('user/{userId}','Api\UserController@getUserDetails');
Route::get('collegeslist','Api\UserController@getCollegeList');
Route::get('skillslist','Api\UserController@getSkillsList');

Route::get('usersByCollege/{collegeId}','Api\UserController@getUsersByCollege');

//POST API ROUTES

Route::post('post','Api\PostController@createPost');
Route::get('userpost/{userId}','Api\PostController@getUserPosts');
Route::get('posts/{user_id}','Api\PostController@getAllPosts');
Route::post('updatePost','Api\PostController@updatePost');
Route::get('getPostDetails/{postId}','Api\PostController@getPostDetails');

//POST LIKE
Route::post('createlike','Api\PostController@createLike');
Route::post('createcomment','Api\PostController@createComment');
Route::get('getPostLikes/{postId}','Api\PostController@getPostLikes');
Route::get('getPostComments/{postId}','Api\PostController@getPostComments');

//Team
Route::post('createTeam','Api\TeamController@createTeam');
Route::post('updateTeam','Api\TeamController@updateTeam');
Route::get('getMyTeams/{userId}','Api\TeamController@getMyTeams');
Route::get('removeTeamMember/{teamId}/{userId}','Api\TeamController@removeTeamMember');
Route::post('followTeam','Api\TeamController@followTeam');
Route::post('setRole','Api\TeamController@setRole');
Route::get('getTeamDetails/{teamId}/{userId}','Api\TeamController@getTeamDetails');
Route::get('getTeamAdmins/{teamId}','Api\TeamController@getTeamAdmins');
Route::get('getTeamMembers/{teamId}','Api\TeamController@getTeamMembers');
Route::get('getTeamFollowers/{teamId}','Api\TeamController@getTeamFollowers');
Route::get('getTeamPosts/{teamId}/{userId}','Api\PostController@getTeamPosts');
Route::get('getMyCreatedTeams/{userId}','Api\TeamController@getMyCreatedTeams');

//Course
Route::post('createCourse','Api\CourseController@createCourse');
Route::get('getMyCourses/{userId}','Api\CourseController@getMyCourses');
Route::get('getCourseSuggestion/{userId}','Api\CourseController@getCourseSuggestion');
Route::get('getCourseDetails/{courseId}','Api\CourseController@getCourseDetails');
Route::get('getCourseContent/{courseId}','Api\CourseController@getCourseContent');
Route::post('createCourseContent','Api\CourseController@createCourseContent');
Route::post('updateCourse','Api\CourseController@updateCourse');
Route::post('updateCourseContent','Api\CourseController@updateCourseContent');


//ADMIN CONTROLLER
Route::post('addTag','Api\AdminController@addTag');

//User Controller
Route::post('addSkill','Api\UserController@addSkill');
Route::get('getUsersSuggestions/{userId}','Api\UserController@getUsersSuggestions');
Route::get('removeSkill/{userId}/{skillId}','Api\UserController@removeSkill');
Route::get('getAddableSkillList/{userId}','Api\UserController@getAddableSkillList');

//Event 
Route::post('createEvent','Api\EventController@createEvent');
Route::get('getEventsForMe/{userId}','Api\EventController@getEventsForMe');
Route::post('joinEvent','Api\EventController@joinEvent');
Route::post('updateEvent','Api\EventController@updateEvent');
Route::get('checkParticipantsList/{eventId}/{teamId}','Api\EventController@checkParticipantsList');
Route::get('getEventDetails/{eventId}/{userId}','Api\EventController@getEventDetails');


//Conversation
Route::post('createConversation','Api\ConversationController@createConversation');
Route::get('getMyConversations/{userId}','Api\ConversationController@getMyConversations');
// Route::get('getmyconv/{userId}','Api\ConversationController@getmyconv');
Route::get('getConversationUsers/{convId}','Api\ConversationController@getConversationUsers');

//Message
Route::post('sendMessage','Api\ConversationController@sendMessage');
Route::get('getChatMessages/{convId}/{userId}/{from}/{paginate}','Api\ConversationController@getChatMessages');


Route::get('getNontags/{type}/{id}','Api\PostController@getNontags');