<?php

namespace App\Http\Controllers;

use App\Actions\PrepaidExpenseImage\PrepaidExpenseImageActions;
use App\Http\Resources\PrepaidExpenseImageResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrepaidExpenseImageController extends BaseController
{
    private $prepaidExpenseImageActions;

    public function __construct(PrepaidExpenseImageActions $prepaidExpenseImageActions)
    {
        parent::__construct();

        $this->prepaidExpenseImageActions = $prepaidExpenseImageActions;
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
            $result = $this->prepaidExpenseImageActions->upload($validatedRequest['image']);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return response()->success(new PrepaidExpenseImageResource($result));
    }
}
