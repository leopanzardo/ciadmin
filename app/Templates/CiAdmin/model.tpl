<?php

namespace App\Models;

use CodeIgniter\Model;

class {{modelName}} extends Model
{
    protected $table      = '{{tableName}}';
    protected $primaryKey = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
