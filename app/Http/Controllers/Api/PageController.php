<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\PageRepository;

class PageController extends Controller
{
    protected $page;

    public function __construct(PageRepository $page)
    {
        $this->page   = $page;
    }

    public function index()
    {
        return $this->page->getAll();
    }
    public function indexPublic()
    {
        return $this->page->getAllPublic();
    }
    
    
    public function store(Request $request)
    {
        return $this->page->create($request);
    }

    public function show($id)
    {
        return $this->page->show($id);
    }
    public function showPublic($slug)
    {
        return $this->page->showPublic($slug);
    }

    public function update(Request $request)
    {
        return $this->page->update($request->id, $request);
    }

    public function destroy(Request $request)
    {
        return $this->page->delete($request->id);
    }
    
    public function createContent(Request $request)
    {
        return $this->page->addPageContent($request);
    }

    public function showContent($id)
    {
        return $this->page->showPageContent($id);
    }
    
    public function contentIndex(Request $request)
    {
        return $this->page->pageContentList();
    }

    public function updateContent(Request $request)
    {
        return $this->page->updatePageContent($request);
    }

    public function destroyContent(Request $request)
    {
        return $this->page->deleteContent($request->id);
    }
}