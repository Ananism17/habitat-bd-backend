<?php

namespace App\Repositories;

use App\Models\Album;
use App\Models\AlbumContent;

use App\Classes\FileUpload;

use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class AlbumRepository implements BaseRepository
{

    protected $model;
    protected $albumContent;
    protected $file;

    public function __construct(Album $model, FileUpload $file, AlbumContent $albumContent)
    {
        $this->model = $model;
        $this->albumContent = $albumContent;
        $this->file = $file;
    }

    public function getAll()
    {
        // $albums = Album::all();
        $albums = $this->model->latest()->paginate(10);
        return response()->json([
            "status" => true,
            "data" => $albums,
            "message" => "Album List Loaded!"
        ]);
    }

    public function getAllType($type)
    {
        $albums = $this->model::where('type', $type)->latest()->paginate(10);
        return response()->json([
            "status" => true,
            "data" => $albums,
            "message" => "Album List Loaded!"
        ]);
    }

    public function getAllPublic($type)
    {
        $albums = $this->model::where('type', $type)
        ->where('status', 1)
        ->latest()
        ->paginate(10);
        return response()->json([
            "status" => true,
            "data" => $albums,
            "message" => "Album List Loaded!"
        ]);
    }

    public function getById(int $id)
    {
        return $this->model::with('albumContents')->find($id);
    }
    public function getBySlug($slug)
    {
        return $this->model::with(
            'albumContents'
        )->where('slug', $slug)->first();
    }
    
    public function getImageById(int $id)
    {
        return $this->albumContent->find($id);
    }

    public function addAlbumContent($image, $id)
    {
        if ($image == NULL) {
            $validator = Validator::make(['album_photo' => $image], [
                'album_photo' => 'image|mimes:jpg,png,jpeg,webp',
            ]);
        }

        // Save the valid photo
        $photo = $this->file->base64ImgUpload($image, "", 'album-contents');

        //save AlbumContent
        $albumContent = new AlbumContent();
        $albumContent->album_id = $id; // Set the album_id
        $albumContent->media_path = $photo;
        $albumContent->save();
    }

    public function create($request)
    {

        try {

            if ($request->cover_photo == NULL) {
                $request->validate([
                    "cover_photo"    => "image:mime:jpg,png,jpeg,webp",
                ]);
            }

            $request->validate([
                'type' => 'required|in:photo,video',
                'title' => 'required|string',
                'description' => 'required|string',
            ]);

            
            $album = $this->model;
            $album->title = $request->title;
            $album->slug = $this->model->generateUniqueSlug($request->title);
            $album->description = $request->description;
            $album->status = $request->status;
            $album->type = $request->type;

            $album->cover_photo = $this->file->base64ImgUpload($request->cover_photo, $file = "", $folder = 'albums');
            $album->save();

            if ($request->has('album_photos')) {
                foreach ($request->album_photos as $photoData) {
                    $this->addAlbumContent($photoData, $album->id);
                }
            }

            return response()->json([
                "status" => true,
                "data" => $album,
                "message" => "Album Created!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => null,
                'status' => false,
            ], 500);
        }
    }
    public function createVideo($request)
    {
        try {
            $request->validate([
                'type' => 'required|in:photo,video',
                'title' => 'required|string',
                'description' => 'required|string',
            ]);

            $album = $this->model;
            $album->slug = $this->model->generateUniqueSlug($request->title);
            $album->title = $request->title;
            $album->description = $request->description;
            $album->status = $request->status;
            $album->type = $request->type;

            $album->save();
            return response()->json([
                "status" => true,
                "data" => $album,
                "message" => "Video Added"
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
                'title' => 'required|string',
                'description' => 'required|string',
                'id' => 'required|integer'
            ]);


            $album = $this->getById($id);

            if ($request->cover_photo != "") {
                $this->file->fileDelete('albums', $album->cover_photo);
            }

            $fileName = NULL;
            if (substr($request->cover_photo, 0, 22) == 'data:image/jpg;base64,'  ||  substr($request->cover_photo, 0, 22) == "data:image/png;base64," || substr($request->cover_photo, 0, 22) == "data:image/webp;base64" || substr($request->cover_photo, 0, 22) == "data:image/jpeg;base64") {
                if ($request->cover_photo != "") {
                    $fileName = $this->file->base64ImgUpload($request->cover_photo, $file = "", $folder = "albums");
                }
            } else {
                $album->cover_photo = $fileName ?  $fileName :  $album->cover_photo;
            }
            
            $album->cover_photo = $fileName ?  $fileName :  $album->cover_photo;
            $album->title = $request->title;
            $album->status = $request->status;
            $album->description = $request->description ? $request->description : NULL;

            if ($request->has('album_photos')) {
                foreach ($request->album_photos as $photoData) {
                    $this->addAlbumContent($photoData, $id);
                }
            }

            $album->save();

            return response()->json([
                "status" => true,
                "data" => $album,
                "message" => "Album Updated!"
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
            $album = $this->getById($id);
            if (!empty($album)) {
                $this->file->fileDelete('albums', $album->cover_photo);
                if ($album->has('albumContents')) {
                    foreach ($album->albumContents as $photoData) {
                        $this->file->fileDelete('album-contents', $photoData->media_path);
                    }
                }
                $album->delete();
                return response()->json([
                    'message' => 'Album deleted successfully',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Album not found!',
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

    public function deleteImage($imageId)
    {
        try {
            $albumContent = $this->getImageById($imageId);

            if (!empty($albumContent)) {
                $this->file->fileDelete('album-contents', $albumContent->media_path);

                $albumContent->delete();
                return response()->json([
                    'message' => 'Image deleted successfully',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Image not found!',
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
            $album = $this->getById($id);

            if (empty($album)) {
                return response()->json([
                    'message' => 'Album details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Album details found',
                'data' => $album,
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
    
    public function showPublic($slug)
    {
        try {
            $album = $this->getBySlug($slug);

            if (empty($album)) {
                return response()->json([
                    'message' => 'Album details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Album details found',
                'data' => $album,
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