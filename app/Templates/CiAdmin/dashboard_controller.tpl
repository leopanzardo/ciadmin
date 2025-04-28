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

        return $this->renderDashboard('dashboard', [
            'title'   => $pageTitle,
            'modules' => $tables,
            'dbName'  => $dbName,
        ]);
    }

    private function renderDashboard(string $contentView, array $data = []): string
    {
        $html  = view('ciadmin/header', $data);
        $html .= view($contentView, $data);
        $html .= view('ciadmin/footer', $data);

        return $html;
    }
}
