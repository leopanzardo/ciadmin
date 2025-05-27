<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = Database::connect();
        $tables = $db->listTables();
        $dbName = $db->database ?? 'CIAdmin';

        $pageTitle = 'Panel de Administración';
        if (!empty($dbName)) {
            $pageTitle .= ' - ' . ucfirst($dbName);
        }

        $data = [
            'title'   => $pageTitle,
            'modules' => $tables,
            'dbName'  => $dbName,
        ];
        
        return view('ciadmin/header', $data)
            . view('dashboard', $data)
            . view('ciadmin/footer', $data);
    }
    
}
