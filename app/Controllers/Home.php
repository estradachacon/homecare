<?php

namespace App\Controllers;
use App\Models\BranchModel;

class Home extends BaseController
{
    protected $groupModel;
    protected $imageModel;
    protected $branchModel = null;
    public function __construct()
    {
        $this->branchModel = new BranchModel();
    }
    public function index()
    {
        $session = session();

        if ($session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('welcome_message', [
        ]);
    }
}
