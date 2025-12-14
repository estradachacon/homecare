<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;

class PaymentController extends BaseController
{
public function packagesBySeller($sellerId)
{
    $packageModel = new PackageModel();
    return $this->response->setJSON(
        $packageModel->getPackagesPendingPaymentBySeller((int)$sellerId)
    );
}

}
