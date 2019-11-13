<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserSettingResourceCollection;
use App\Models\User\UserSetting;
use Illuminate\Http\Request;

class UserSettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Store user's setting.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\UserSettingResourceCollection
     */
    public function store(Request $request)
    {
        $request->validate(UserSetting::$rules);

        $setting = $request->user()->getSettingByKey($request->key) ?: new UserSetting;

        $setting->user_id = $request->user()->id;
        $setting->key = $request->key;
        $setting->value = $request->value;

        if (!$setting->save()) {
            return $this->errorResponse('User\'s setting could not be saved.', 500);
        }

        return $this->wrapResponse(new UserSettingResourceCollection($request->user()->settings()->get()));
    }
}
