<?php

namespace App\Repositories;

use App\Models\Page;
use App\Classes\FileUpload;
use App\Models\PageContent;
use Illuminate\Validation\Rule;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class PageRepository implements BaseRepository
{

    protected $page;
    protected $file;
    protected $pageContent;

    public function __construct(Page $page, FileUpload $file, PageContent $pageContent)
    {
        $this->page = $page;
        $this->file = $file;
        $this->pageContent = $pageContent;
    }

    public function getAll()
    {
        $pages = $this->page::with(
            'children',
            'parent',
            'pageContents'
        )->get();
        return response()->json([
            "status" => true,
            "data" => $pages,
            "message" => "Page List Loaded!"
        ]);
    }
    public function getAllPublic()
    {
        $pages = $this->page::with(
            'children',
            'parent',
        )
        ->where('status', 1)
        ->get();
        return response()->json([
            "status" => true,
            "data" => $pages,
            "message" => "Page List Loaded!"
        ]);
    }

    public function getById(int $id)
    {
        return $this->page::with(
            'children',
            'parent',
            'pageContents'
        )->find($id);
    }
    public function getBySlug(string $slug)
    {
        return $this->page::with(
            'children',
            'parent',
            'pageContents'
        )->where('slug', $slug)->first();;
    }

    public function create($request)
    {

        try {
            $request->validate([
                'parent_id' => 'nullable|exists:pages,id',
                'name' => 'required|string|max:255',
                'serial' => 'required|integer|unique:pages,serial',
            ]);

            $page = $this->page;
            $page->name = $request->name;
            $page->serial = $request->serial;
            $page->status = $request->status;
            $page->parent_id = $request->parent_id;

            $page->slug = $this->page->generateUniqueSlug($request->name);

            $page->save();

            return response()->json([
                "status" => true,
                "data" => $page,
                "message" => "Page Created!"
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
                'parent_id' => 'nullable|exists:pages,id',
                'name' => 'required|string|max:255',
                'serial' => [
                    'required',
                    'integer',
                    Rule::unique('pages', 'serial')->ignore($request->id),
                ],
                'id' => 'required|integer',
            ]);


            $page = $this->getById($id);

            $page->name = $request->name;
            $page->serial = $request->serial;
            $page->status = $request->status;
            $page->parent_id = $request->parent_id;



            $page->save();

            $page = $this->getById($id);

            return response()->json([
                "status" => true,
                "data" => $page,
                "message" => "Page Updated!"
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
            $page = $this->getById($id);

            if (!empty($page)) {
                if (!empty($page->pageContents)) {
                    foreach ($page->pageContents as $pageContent) {

                        $contentPhotosArray = $pageContent->content_photos ? json_decode($pageContent->content_photos) : [];
                        if (!empty($contentPhotosArray)) {
                            foreach ($contentPhotosArray as $photoData) {
                                $this->file->fileDelete('page-contents', $photoData);
                            }
                        }
                    }
                }


                $page->delete();
                return response()->json([
                    'message' => 'Page deleted successfully',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Page not found!',
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
            $page = $this->getById($id);

            if (empty($page)) {
                return response()->json([
                    'message' => 'Page details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Page details found',
                'data' => $page,
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
            $page = $this->getBySlug($slug);

            if (empty($page)) {
                return response()->json([
                    'message' => 'Page details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Page details found',
                'data' => $page,
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



    // ================================================== //
    // ================================================== //
    // ============= PAGE CONTENT FUNCTIONS ============= //
    // ================================================== //
    // ================================================== //

    public function pageContentList()
    {
        $pageContents = $this->pageContent::with(
            'page'
        )->get();
        return response()->json([
            "status" => true,
            "data" => $pageContents,
            "message" => "Page List Loaded!"
        ]);
    }

    public function getContentById(int $id)
    {
        return $this->pageContent::with('page')->find($id);
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


    public function addPageContent($request)
    {

        try {
            $request->validate([
                'id' => 'required|exists:pages,id',
                'title' => 'string|max:255',
                'description' => 'required|string',
            ]);

            $content_photos = [];
            $json_content_photos = null;
            $descriptionWithUrls = null;
            if ($request->filled('content_photos') && count($request->content_photos)) {
                foreach ($request->content_photos as $photoData) {
                    $photo = $this->file->base64ImgUpload($photoData, "", 'page-contents');
                    $content_photos[] = $photo;
                    $imageUrls[] = asset('storage/page-contents/' . $photo);
                }
                $json_content_photos = json_encode($content_photos);

                $descriptionWithUrls = $this->replacePlaceholdersWithUrls($request->input('description'), $imageUrls);
            }



            $pageContent = $this->pageContent;

            $pageContent->title = $request->title;
            $pageContent->description = $descriptionWithUrls ? $descriptionWithUrls : $request->description;
            $pageContent->content_photos = $json_content_photos;
            $pageContent->page_id = $request->id;

            $pageContent->save();

            return response()->json([
                "status" => true,
                "data" => $pageContent,
                "message" => "Page Content Created!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => null,
                'status' => false,
            ], 500);
        }
    }

    public function updatePageContent($request)
    {

        try {

            $request->validate([
                'id' => 'required',
                'title' => 'string|max:255',
                'description' => 'required|string',
            ]);

            $pageContent = $this->getContentById($request->id);

            // return [$pageContent, $request];

            if (!empty($pageContent)) {
                $contentPhotosArray = $pageContent->content_photos ? json_decode($pageContent->content_photos) : [];

                if (!empty($contentPhotosArray)) {
                    foreach ($contentPhotosArray as $photoData) {
                        $this->file->fileDelete('page-contents', $photoData);
                    }
                }

                $pageContent->content_photos = null;
            }

            $content_photos = [];
            $json_content_photos = null;
            $descriptionWithUrls = null;
            if ($request->filled('content_photos') && count($request->content_photos)) {
                foreach ($request->content_photos as $photoData) {
                    $photo = $this->file->base64ImgUpload($photoData, "", 'page-contents');
                    $content_photos[] = $photo;
                    $imageUrls[] = asset('storage/page-contents/' . $photo);
                }
                $json_content_photos = json_encode($content_photos);

                $descriptionWithUrls = $this->replacePlaceholdersWithUrls($request->input('description'), $imageUrls);
            }

            $pageContent->title = $request->title;
            $pageContent->description = $descriptionWithUrls ? $descriptionWithUrls : $request->description;
            $pageContent->content_photos = $json_content_photos;

            $pageContent->save();

            return response()->json([
                "status" => true,
                "data" => $pageContent,
                "message" => "Page Content Created!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'data' => null,
                'status' => false,
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

    public function showPageContent($id)
    {

        try {
            $pageContent = $this->getContentById($id);
            // return $pageContent;
            $pageContent->description = $this->convertAbsoluteImagesToBase64($pageContent->description);

            if (empty($pageContent)) {
                return response()->json([
                    'message' => 'Page Content details not found',
                    'data' => [],
                    'status' => false,
                ], 404);
            }

            return response()->json([
                'message' => 'Page Content details found',
                'data' => $pageContent,
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

    public function deleteContent($id)
    {
        try {
            $pageContent = $this->getContentById($id);
            if (!empty($pageContent)) {
                $contentPhotosArray = $pageContent->content_photos ? json_decode($pageContent->content_photos) : [];

                if (!empty($contentPhotosArray)) {
                    foreach ($contentPhotosArray as $photoData) {
                        $this->file->fileDelete('page-contents', $photoData);
                    }
                }

                $pageContent->delete();
                return response()->json([
                    'message' => 'Page Content deleted successfully',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Page Content not found!',
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
}