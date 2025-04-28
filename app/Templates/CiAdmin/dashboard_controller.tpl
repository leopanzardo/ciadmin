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
        $dbName = $db->database ?? 'CiAdmin';

        return renderCiAdminView('dashboard', [
            'modules' => $tables,
            'dbName' => $dbName,
        ]);
    }
}
