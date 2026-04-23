<?php

namespace App\Http\Controllers;

use App\Actions\IncomeImage\IncomeImageActions;
use App\Http\Resources\IncomeImageResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeImageController extends BaseController
{
    private $incomeImageActions;

    public function __construct(IncomeImageActions $incomeImageActions)
    {
        parent::__construct();

        $this->incomeImageActions = $incomeImageActions;
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
            $result = $this->incomeImageActions->upload($validatedRequest['image']);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return response()->success(new IncomeImageResource($result));
    }
}
