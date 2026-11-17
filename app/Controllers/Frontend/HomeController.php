<?php declare(strict_types = 1); 

namespace App\Controllers\Frontend;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Controllers\Frontend\FrontendController; 

class HomeController extends FrontendController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Mostra la home con il collegamento al profilo del cliente autenticato, senza cache condivisa.
     *
     * @return string
     */
    public function index(): string
    {
        $this->data['controller'] = 'home';
        $this->data['action'] = 'index';
        return $this->render('frontend/home/indexView', $this->data);
    }
}
