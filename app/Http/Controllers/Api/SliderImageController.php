<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SliderRepository;
use Illuminate\Http\Request;

class SliderImageController extends Controller
{
    protected $slider;

    public function __construct(SliderRepository $slider)
    {
        $this->slider   = $slider;
    }

    public function index(Request $request)
    {
        
            return $this->slider->getAll();
    }

    public function indexPublic(Request $request)
    {

            return $this->slider->getAllPublic();
    }

    public function store(Request $request)
    {
        return $this->slider->create($request);
    }

    public function show($id)
    {
        return $this->slider->show($id);
    }

    public function update(Request $request)
    {
        return $this->slider->update($request->id, $request);
    }

    public function destroy(Request $request)
    {
        return $this->slider->delete($request->id);
    }
}