<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Models\Travel;
use Illuminate\Http\Request;

class TourController extends Controller
{

    public function index(Travel $travel, Request $request)
    {
        $request->validate([
            'priceFrom' => 'numeric',
            'priceTo' => 'numeric',
            'dateFrom' => 'date',
            'dateTo' => 'date',
        ]);

        $tours = $travel->tours()
            ->when($request->dateFrom, function($query) use ($request) {
                $query->where('starting_date', '<=', $request->dateFrom);
            })
            ->when($request->dateTo, function($query) use ($request) {
                $query->where('starting_date', '<=', $request->dateTo);
            })
            ->when($request->priceFrom, function($query) use ($request) {
                $query->where('price', '>=', $request->priceFrom * 100);
            })
            ->when($request->priceTo, function($query) use ($request) {
                $query->where('price', '<=', $request->priceTo * 100);
            })
            
            ->orderBy('starting_date')
            ->paginate();
            
        return TourResource::collection($tours);
    }

}
