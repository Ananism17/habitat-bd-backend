<?php

namespace App\Repositories;



use App\Classes\FileUpload;
use App\Models\SliderImage;
use Illuminate\Validation\Rule;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class SliderRepository implements BaseRepository
{

    protected $slider;
    protected $file;

    public function __construct(SliderImage $slider, FileUpload $file)
    {
        $this->slider = $slider;
        $this->file = $file;
    }

    public function getAll()
    {
        $sliders = $this->slider->all();
        return response()->json([
            "status" => true,
            "data" => $sliders,
            "message" => "Slider Image List Loaded!"
        ]);
    }

    public function getAllPublic()
    {
        $sliders = $this->slider->where('status', 1)
            ->orderBy('serial', 'asc')
            ->get();
        return response()->json([
            "status" => true,
            "data" => $sliders,
            "message" => "Slider Image List Loaded!"
        ]);
    }

    public function getById(int $id)
    {
        return $this->slider->find($id);
    }


    public function create($request)
    {

        try {

            if ($request->url == NULL) {
                $request->validate([
                    "url"    => "image:mime:jpg,png,jpeg,webp",
                ]);
            }

            $request->validate([
                'caption' => 'required|string|max:255',
                'serial' => 'required|integer|unique:slider_images,serial',
            ]);

            $slider = $this->slider;
            $slider->caption = $request->caption;
            $slider->url = $this->file->base64ImgUpload($request->url, $file = "", $folder = 'sliders');
            $slider->status = $request->status;
            $slider->serial = $request->serial;

            $slider->save();

            return response()->json([
                "status" => true,
                "data" => $slider,
                "message" => "Slider Image Created!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => null,
                'status' => false,
            ], 500);
        }
    }


    public function update(int $id, $request)
    {
        try {
            $request->validate([
                'caption' => 'required|string|max:255',
                'serial' => [
                    'required',
                    'integer',
                    Rule::unique('slider_images', 'serial')->ignore($request->id),
                ],
                'id' => 'required|integer'
            ]);


            $slider = $this->getById($id);

            if ($request->url != "") {
                $this->file->fileDelete('sliders', $slider->url);
            }

            $fileName = NULL;
            if (substr($request->url, 0, 22) == 'data:image/jpg;base64,'  ||  substr($request->url, 0, 22) == "data:image/png;base64," || substr($request->url, 0, 22) == "data:image/webp;base64" || substr($request->url, 0, 22) == "data:image/jpeg;base64") {
                if ($request->url != "") {
                    $fileName = $this->file->base64ImgUpload($request->url, $file = "", $folder = "sliders");
                }
            } else {
                $slider->url = $fileName ?  $fileName :  $slider->url;
            }

            $slider->url = $fileName ?  $fileName :  $slider->url;
            $slider->caption = $request->caption;
            $slider->status = $request->status;
            $slider->serial = $request->serial;



            $slider->save();

            return response()->json([
                "status" => true,
                "data" => $slider,
                "message" => "Slider Image Updated!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $slider = $this->getById($id);
            if (!empty($slider)) {
                $this->file->fileDelete('sliders', $slider->url);

                $slider->delete();
                return response()->json([
                    'message' => 'Slider deleted successfully',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Slider not found!',
                    'status' => false,
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => [],
                'status' => false,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $slider = $this->getById($id);

            if (empty($slider)) {
                return response()->json([
                    'message' => 'Slider details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Slider details found',
                'data' => $slider,
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => null,
                'status' => false,
            ], 500);
        }
    }
}