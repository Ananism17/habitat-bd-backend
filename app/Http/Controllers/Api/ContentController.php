<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ContentRepository;

class ContentController extends Controller
{
    protected $content;

    public function __construct(ContentRepository $content)
    {
        $this->content   = $content;
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        if ($type)
            return $this->content->getAllType($type);
        else
            return $this->content->getAll($type);
    }

    public function indexPublic(Request $request)
    {
            $type = $request->query('type');
            return $this->content->getAllPublic($type);
    }

    public function store(Request $request)
    {
        return $this->content->create($request);
    }

    public function show($id)
    {
        return $this->content->show($id);
    }
    public function showPublic($slug)
    {
        return $this->content->showPublic($slug);
    }

    public function update(Request $request)
    {
        return $this->content->update($request->id, $request);
    }
    
    public function updateDescription(Request $request)
    {
        return $this->content->updateDescription($request);
    }

    public function destroy(Request $request)
    {
        return $this->content->delete($request->id);
    }
}