<?php

namespace App\Http\Controllers;

use App\Actions\ExpenseImage\ExpenseImageActions;
use App\Http\Resources\ExpenseImageResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseImageController extends BaseController
{
    private $expenseImageActions;

    public function __construct(ExpenseImageActions $expenseImageActions)
    {
        parent::__construct();

        $this->expenseImageActions = $expenseImageActions;
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
            $result = $this->expenseImageActions->upload($validatedRequest['image']);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return response()->success(new ExpenseImageResource($result));
    }
}
