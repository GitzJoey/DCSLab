<?php

namespace App\Http\Controllers;

use App\Actions\ProductImage\ProductImageActions;
use App\Http\Resources\ProductImageResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductImageController extends BaseController
{
    private $productImageActions;

    public function __construct(ProductImageActions $productImageActions)
    {
        parent::__construct();

        $this->productImageActions = $productImageActions;
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
            $result = $this->productImageActions->upload($validatedRequest['image']);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return response()->success(new ProductImageResource($result));
    }
}
