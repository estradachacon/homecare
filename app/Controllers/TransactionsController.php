<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TransactionModel;


class TransactionsController extends BaseController
{
    public function index()
    {
        $model = new TransactionModel();
        $data['transactions'] = $model->orderBy('created_at', 'DESC')->findAll();

        return view('transactions/index', $data);
    }
}
