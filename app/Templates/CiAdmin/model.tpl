<?php

namespace App\Models;

use CodeIgniter\Model;

class {{modelName}} extends Model
{
    protected $table      = '{{tableName}}';
    protected $primaryKey = '{{primaryKey}}';
    protected $useAutoIncrement = {{useAutoIncrement}};
    protected $returnType     = '{{returnType}}';
    protected $useSoftDeletes = {{useSoftDeletes}};

    protected $allowedFields = [{{allowedFields}}];

    protected $useTimestamps = {{useTimestamps}};
{{createdFieldLine}}{{updatedFieldLine}}
}
