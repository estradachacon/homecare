<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class PdfEditorController extends BaseController
{
    public function index()
    {
        return view('pdf_editor/index');
    }
}
