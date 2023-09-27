<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\AlbumRepository;

class AlbumController extends Controller
{
    protected $album;

    public function __construct(AlbumRepository $album)
    {
        $this->album   = $album;
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        if ($type)
            return $this->album->getAllType($type);
        else
            return $this->album->getAll();
    }
    public function indexPublic(Request $request)
    {
            $type = $request->query('type');
            return $this->album->getAllPublic($type);
    }

    public function store(Request $request)
    {
        if ($request->type == 'photo')
            return $this->album->create($request);
        else return $this->album->createVideo($request);
    }

    public function show($id)
    {
        return $this->album->show($id);
    }
    public function showPublic($slug)
    {
        return $this->album->showPublic($slug);
    }

    public function update(Request $request)
    {
        return $this->album->update($request->id, $request);
    }

    public function destroy(Request $request)
    {
        return $this->album->delete($request->id);
    }

    public function destroyImage(Request $request)
    {
        return $this->album->deleteImage($request->id);
    }
}