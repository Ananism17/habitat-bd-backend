<?php

namespace App\Repositories;


use App\Classes\FileUpload;
use App\Models\Content;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class ContentRepository implements BaseRepository
{
    protected $content;
    protected $file;

    public function __construct(Content $content, FileUpload $file)
    {
        $this->content = $content;
        $this->file = $file;
    }

    public function getAll()
    {
        $contents = $this->content->latest()->paginate(5);
        return response()->json([
            "status" => true,
            "data" => $contents,
            "message" => "Content List Loaded!"
        ]);
    }
    public function getAllType($type)
    {
        $contents = $this->content::where('type', $type)->latest()->paginate(5);
        return response()->json([
            "status" => true,
            "data" => $contents,
            "message" => "Content List Loaded!"
        ]);
    }

    public function getAllPublic($type)
    {
        $albums = $this->content::where('type', $type)
        ->where('status', 1)
        ->orderBy('count', 'desc')
        ->latest()
        ->paginate(5);
        return response()->json([
            "status" => true,
            "data" => $albums,
            "message" => "Content List Loaded!"
        ]);
    }

    public function getById(int $id)
    {
        return $this->content->find($id);
    }

    public function getBySlug($slug)
    {
        return $this->content->where('slug', $slug)->first();
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
                'title' => 'required|string|max:255',
                'type' => 'required|in:story,news',
            ]);


            $content = $this->content;
            $content->title = $request->title;
            $content->type = $request->type;
            $content->status = $request->status;
            $content->slug = $this->content->generateUniqueSlug($request->title);

            $content->cover_photo = $this->file->base64ImgUpload($request->cover_photo, $file = "", $folder = 'contents');
            $content->save();



            return response()->json([
                "status" => true,
                "data" => $content,
                "message" => " Content Created!"
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
                'id' => 'required|integer',
                'type' => 'required|in:story,news'
            ]);


            $content = $this->getById($id);



            if ($request->cover_photo != "") {
                $this->file->fileDelete('contents', $content->cover_photo);
            }

            $fileName = NULL;
            if (substr($request->cover_photo, 0, 22) == 'data:image/jpg;base64,'  ||  substr($request->cover_photo, 0, 22) == "data:image/png;base64," || substr($request->cover_photo, 0, 22) == "data:image/webp;base64" || substr($request->cover_photo, 0, 22) == "data:image/jpeg;base64") {
                if ($request->cover_photo != "") {
                    $fileName = $this->file->base64ImgUpload($request->cover_photo, $file = "", $folder = "contents");
                }
            } else {
                $content->cover_photo = $fileName ?  $fileName :  $content->cover_photo;
            }


            $content->cover_photo = $fileName ?  $fileName :  $content->cover_photo;

            $content->title = $request->title;
            $content->status = $request->status;
            $content->type = $request->type;



            $content->save();

            return response()->json([
                "status" => true,
                "data" => $content,
                "message" => "Content Updated!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function replacePlaceholdersWithUrls($description, $imageUrls)
    {
        // Replace placeholders with actual image URLs based on the order of images
        return preg_replace_callback('/{{IMAGE_PLACEHOLDER_(\d+)}}/', function ($matches) use ($imageUrls) {
            $index = $matches[1];
            if (isset($imageUrls[$index])) {
                return $imageUrls[$index];
            }
            // Handle cases where there's no corresponding URL (optional)
            return '';
        }, $description);
    }

    public function convertAbsoluteImagesToBase64($htmlContent)
    {
        return preg_replace_callback('#<img\s+([^>]+)>#', function ($matches) {
            $imgTag = $matches[0]; // Full img tag
            $attributes = $matches[1]; // All attributes

            // Find the src attribute within the attributes
            if (preg_match('/src="([^"]+)"/', $attributes, $srcMatches)) {
                $imageUrl = $srcMatches[1]; // URL from the src attribute

                // Construct the absolute path to the image on your server
                $imagePath = public_path(parse_url($imageUrl, PHP_URL_PATH));

                // Check if the image file exists on your server
                if (file_exists($imagePath)) {
                    // Read the image data directly from the filesystem
                    $imageData = file_get_contents($imagePath);

                    if ($imageData !== false) {
                        // Encode the image data to base64
                        $base64Image = 'data:image/' . pathinfo($imagePath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);

                        // Replace the src attribute with the base64-encoded src
                        $imgTag = str_replace($imageUrl, $base64Image, $imgTag);
                    }
                }
            }

            return $imgTag;
        }, $htmlContent);
    }

    public function updateDescription($request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $content = $this->getById($request->id);

            if (!empty($content)) {
                $contentPhotosArray = $content->content_photos ? json_decode($content->content_photos) : [];

                if (!empty($contentPhotosArray)) {
                    foreach ($contentPhotosArray as $photoData) {
                        $this->file->fileDelete('contents', $photoData);
                    }
                }

                $content->content_photos = null;
            }

            $content_photos = [];
            $json_content_photos = null;
            $descriptionWithUrls = null;
            if ($request->filled('content_photos') && count($request->content_photos)) {
                foreach ($request->content_photos as $photoData) {
                    $photo = $this->file->base64ImgUpload($photoData, "", 'contents');
                    $content_photos[] = $photo;
                    $imageUrls[] = asset('storage/contents/' . $photo);
                }
                $json_content_photos = json_encode($content_photos);

                $descriptionWithUrls = $this->replacePlaceholdersWithUrls($request->input('description'), $imageUrls);
            }

            $content->description = $descriptionWithUrls ? $descriptionWithUrls : $request->description;
            $content->content_photos = $json_content_photos;

            $content->save();

            return response()->json([
                "status" => true,
                "data" => $content,
                "message" => "Content Updated!"
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
            $content = $this->getById($id);
            if (!empty($content)) {

                $contentPhotosArray = $content->content_photos ? json_decode($content->content_photos) : [];

                if (!empty($contentPhotosArray)) {
                    foreach ($contentPhotosArray as $photoData) {
                        $this->file->fileDelete('contents', $photoData);
                    }
                }


                $this->file->fileDelete('contents', $content->cover_photo);

                $content->delete();
                return response()->json([
                    'message' => 'Content deleted successfully',
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

    public function show($id)
    {
        try {
            $content = $this->getById($id);

            $content->description = $this->convertAbsoluteImagesToBase64($content->description);

            if (empty($content)) {
                return response()->json([
                    'message' => 'Content details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Content details found',
                'data' => $content,
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
            $content = $this->getBySlug($slug);

            $content->count = $content->count+1;
            
            $content->save();
            
            if (empty($content)) {
                return response()->json([
                    'message' => 'Content details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Content details found',
                'data' => $content,
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