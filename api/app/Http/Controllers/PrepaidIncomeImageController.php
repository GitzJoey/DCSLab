<?php

namespace App\Http\Controllers;

use App\Actions\PrepaidIncomeImage\PrepaidIncomeImageActions;
use App\Http\Resources\PrepaidIncomeImageResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrepaidIncomeImageController extends BaseController
{
    private $prepaidIncomeImageActions;

    public function __construct(PrepaidIncomeImageActions $prepaidIncomeImageActions)
    {
        parent::__construct();

        $this->prepaidIncomeImageActions = $prepaidIncomeImageActions;
    }

    public function upload(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);

        $validatedRequest = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->prepaidIncomeImageActions->upload($validatedRequest['image']);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return response()->success(new PrepaidIncomeImageResource($result));
    }
}
