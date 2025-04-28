<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use CodeIgniter\Database\Forge;

class MakeCiAdmin extends BaseCommand
{
    protected $group       = 'Generators';
    protected $name        = 'make:ciadmin';
    protected $description = 'Genera una app administrativa basada en la base de datos';
    protected array $reservedViewFolders = ['ciadmin', 'shared', 'system'];

    public function run(array $params)
    {
        CLI::write('🚀 Iniciando generación de CIAdmin...', 'green');

        $db = Database::connect();
        $forge = \Config\Database::forge();
        $tables = $db->listTables();

        if (empty($tables)) {
            CLI::error('❌ No se encontraron tablas en la base de datos.');
            return;
        }

        foreach ($tables as $table) {
            $className = ucfirst($table);

            CLI::write("📄 Procesando tabla: {$table}", 'yellow');

            $this->generateModel($className, $table);
            $this->generateController($className, $table);
            $this->generateViews($className, $table);
        }
        
        $this->generateDashboard();

        CLI::write("✅ CIAdmin generado con éxito.", 'green');
    }

    protected function generateModel(string $className, string $table)
    {
        $modelPath = APPPATH . "Models/{$className}Model.php";
        if (file_exists($modelPath)) {
            CLI::write("⚠️ Modelo ya existe: {$className}Model.php", 'light_gray');
            return;
        }

        $code = $this->renderTemplate('model', [
            'modelName' => $className . 'Model',
            'tableName' => $table
        ]);

        write_file($modelPath, $code);
        CLI::write("✅ Modelo creado: {$className}Model.php", 'green');
    }

    protected function generateController(string $className, string $table)
    {
        $controllerPath = APPPATH . "Controllers/{$className}.php";
        if (file_exists($controllerPath)) {
            CLI::write("⚠️ Controlador ya existe: {$className}.php", 'light_gray');
            return;
        }

        $fields = $this->getTableFields($table);

        $code = $this->renderTemplate('controller', [
            'controllerName'   => $className,
            'modelName'        => $className . 'Model',
            'viewFolder'       => $table,
            'validationRules'  => $this->generateValidationRules($fields),
        ]);

        write_file($controllerPath, $code);
        CLI::write("✅ Controlador creado: {$className}.php", 'green');
    }

    protected function generateViews(string $className, string $table)
    {
        
        // Verificar si el nombre de la tabla es una palabra reservada
        if (in_array(strtolower($table), $this->reservedViewFolders)) {
            CLI::error("❌ No se pueden generar vistas para la tabla reservada: {$table}");
            return;
        }
        
        $viewDir = APPPATH . "Views/{$table}";
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        $fields = $this->getTableFields($table);

        // Index view
        $indexViewPath = "{$viewDir}/index.php";
        if (!file_exists($indexViewPath)) {
            list($thead, $tbody) = $this->generateTableFields($fields);

            $html = $this->renderTemplate("view_index", [
                'viewFolder' => $table,
                'thead' => $thead,
                'tbody' => $tbody,
            ]);
            write_file($indexViewPath, $html);
            CLI::write("✅ Vista index generada: {$table}/index.php", 'green');
        }

        // Create view
        $createViewPath = "{$viewDir}/create.php";
        if (!file_exists($createViewPath)) {
            $formFields = $this->generateFormFields($fields, 'post');
            $html = $this->renderTemplate("view_create", [
                'viewFolder' => $table,
                'formFields' => $formFields,
            ]);
            write_file($createViewPath, $html);
            CLI::write("✅ Vista create generada: {$table}/create.php", 'green');
        }

        // Edit view
        $editViewPath = "{$viewDir}/edit.php";
        if (!file_exists($editViewPath)) {
            $formFields = $this->generateFormFields($fields, 'row');
            $html = $this->renderTemplate("view_edit", [
                'viewFolder' => $table,
                'formFields' => $formFields,
            ]);
            write_file($editViewPath, $html);
            CLI::write("✅ Vista edit generada: {$table}/edit.php", 'green');
        }
    }
    
    protected function appendRoutes(string $className, string $table)
    {
        $routesFile = APPPATH . 'Config/Routes.php';

        // Código de rutas a agregar
        $newRoutes = <<<ROUTES

    // --- Rutas generadas automáticamente para {$className}
    \$routes->get('{$table}', '{$className}::index');
    \$routes->get('{$table}/create', '{$className}::create');
    \$routes->post('{$table}/store', '{$className}::store');
    \$routes->get('{$table}/edit/(:num)', '{$className}::edit/\$1');
    \$routes->post('{$table}/update/(:num)', '{$className}::update/\$1');
    \$routes->get('{$table}/delete/(:num)', '{$className}::delete/\$1');

    ROUTES;

        // Verificamos que no se hayan agregado antes
        $routesContents = file_get_contents($routesFile);

        if (strpos($routesContents, "\$routes->get('{$table}'") === false) {
            file_put_contents($routesFile, $newRoutes, FILE_APPEND);
            CLI::write("✅ Rutas agregadas para: {$className}", 'green');
        } else {
            CLI::write("⚠️ Las rutas para {$className} ya existen en Routes.php", 'light_gray');
        }
    }
    
    protected function renderTemplate(string $templateName, array $vars = []): string
    {
        $templatePath = APPPATH . "Templates/CiAdmin/{$templateName}.tpl";
        if (!file_exists($templatePath)) {
            CLI::error("❌ No se encuentra el template: {$templateName}.tpl");
            return '';
        }

        $template = file_get_contents($templatePath);

        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }
    
    protected function getTableFields(string $table): array
    {
        $db = \Config\Database::connect();
        $fieldsData = $db->getFieldData($table);

        $fields = [];
        foreach ($fieldsData as $field) {
            if (in_array($field->name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $fields[] = [
                'name'       => $field->name,
                'type'       => strtolower($field->type),
                'max_length' => property_exists($field, 'max_length') ? $field->max_length : null,
            ];
        }

        return $fields;
    }
    
    protected function generateFormFields(array $fields, string $source = 'post'): string
    {
        $html = "";

        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $fieldType = strtolower($field['type']);
            $label = ucfirst(str_replace('_', ' ', $fieldName));

            // Determinar tipo de input
            $inputType = 'text';
            if (str_contains($fieldName, 'email')) {
                $inputType = 'email';
            } elseif (str_contains($fieldName, 'password')) {
                $inputType = 'password';
            } elseif (in_array($fieldType, ['int', 'bigint', 'smallint', 'mediumint', 'tinyint'])) {
                $inputType = 'number';
            } elseif ($fieldType === 'date') {
                $inputType = 'date';
            } elseif (in_array($fieldType, ['datetime', 'timestamp'])) {
                $inputType = 'datetime-local';
            }

            // Determinar valor
            if ($source === 'post') {
                $value = '<?= old(\'' . $fieldName . '\') ?>';
            } else {
                $value = '<?= old(\'' . $fieldName . '\', $row[\'' . $fieldName . '\'] ?? \'\') ?>';
            }

            $html .= <<<EOT
    <div class="mb-3 col-md-6">
        <label for="{$fieldName}" class="form-label">{$label}</label>
        <input type="{$inputType}" class="form-control" id="{$fieldName}" name="{$fieldName}" value="{$value}">
    </div>

    EOT;
        }

        return $html;
    }
    
    protected function generateValidationRules(array $fields): string
    {
        $rules = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = strtolower($field['type']);
            $maxLength = $field['max_length'] ?? null;

            $rule = [];

            // No validamos campos opcionales tipo timestamps
            if (in_array($name, ['created_at', 'updated_at'])) {
                continue;
            }

            // Siempre pedimos que los campos sean requeridos (puede mejorarse más adelante)
            $rule[] = 'required';

            // Tipos especiales
            if (str_contains($name, 'email')) {
                $rule[] = 'valid_email';
            } elseif (str_contains($name, 'password')) {
                $rule[] = 'min_length[6]';
            } elseif (in_array($type, ['int', 'bigint', 'smallint', 'tinyint', 'mediumint'])) {
                $rule[] = 'integer';
            } elseif (in_array($type, ['decimal', 'float', 'double'])) {
                $rule[] = 'decimal';
            } elseif (in_array($type, ['date'])) {
                $rule[] = 'valid_date';
            } elseif (in_array($type, ['datetime', 'timestamp'])) {
                // Opcionalmente podríamos validar formato datetime aquí
            }

            // Si sabemos el largo máximo
            if ($maxLength && is_numeric($maxLength) && $maxLength > 0) {
                $rule[] = "max_length[{$maxLength}]";
            }

            $rules[$name] = implode('|', $rule);
        }

        // Formatear para insertar en el controller
        $rulesString = "[\n";
        foreach ($rules as $fieldName => $fieldRules) {
            $rulesString .= "            '{$fieldName}' => '{$fieldRules}',\n";
        }
        $rulesString .= "        ]";

        return $rulesString;
    }
    
    protected function generateTableFields(array $fields): array
    {
        // Encabezados de la tabla
        $thead = "";
        foreach ($fields as $field) {
            $thead .= "                <th>" . ucfirst(str_replace('_', ' ', $field['name'])) . "</th>\n";
        }
        $thead .= "                <th>Acciones</th>\n";

        // Filas de la tabla
        $tbody = "";
        $tbody .= "            <?php foreach (\$rows as \$row): ?>\n";
        $tbody .= "            <tr>\n";
        foreach ($fields as $field) {
            $tbody .= "                <td><?= \$row['{$field['name']}'] ?? '' ?></td>\n";
        }
        $tbody .= "                <td>\n";
        $tbody .= "                    <a href=\"<?= site_url('<?= \$viewFolder ?>/edit/' . \$row['id']) ?>\" class=\"btn btn-sm btn-primary\">Editar</a>\n";
        $tbody .= "                    <a href=\"<?= site_url('<?= \$viewFolder ?>/delete/' . \$row['id']) ?>\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('¿Seguro que desea eliminar este registro?')\">Eliminar</a>\n";
        $tbody .= "                </td>\n";
        $tbody .= "            </tr>\n";
        $tbody .= "            <?php endforeach; ?>\n";

        return [$thead, $tbody];
    }
    
    protected function generateDashboard()
    {
        $controllerPath = APPPATH . "Controllers/Dashboard.php";
        if (!file_exists($controllerPath)) {
            $template = $this->renderTemplate('dashboard_controller', []);
            write_file($controllerPath, $template);
            CLI::write("✅ Controlador Dashboard generado: Dashboard.php", 'green');
        } else {
            CLI::write("⚠️ Dashboard.php ya existe, no se sobrescribe.", 'light_gray');
        }

        $viewPath = APPPATH . "Views/dashboard.php";
        if (!file_exists($viewPath)) {
            $template = $this->renderTemplate('dashboard_view', []);
            write_file($viewPath, $template);
            CLI::write("✅ Vista dashboard generada: dashboard.php", 'green');
        } else {
            CLI::write("⚠️ dashboard.php ya existe, no se sobrescribe.", 'light_gray');
        }

        // Modificar o agregar la ruta '/' para que apunte al Dashboard
        $routesFile = APPPATH . 'Config/Routes.php';
        $routesContents = file_get_contents($routesFile);

        if (preg_match("/\\\$routes->get\\(\s*'\/'\s*,/", $routesContents)) {
            // Si ya existe una ruta '/', la reemplazamos
            $routesContents = preg_replace(
                "/\\\$routes->get\\(\s*'\/'\s*,\s*'[^']+'\s*\);/",
                "\$routes->get('/', 'Dashboard::index');",
                $routesContents
            );
            file_put_contents($routesFile, $routesContents);
            CLI::write("✅ Ruta '/' actualizada para usar Dashboard::index.", 'green');
        } else {
            // Si no existe, la agregamos al final
            $newRoute = "\n// Ruta principal generada automáticamente\n\$routes->get('/', 'Dashboard::index');\n";
            file_put_contents($routesFile, $newRoute, FILE_APPEND);
            CLI::write("✅ Ruta '/' creada para usar Dashboard::index.", 'green');
        }
    }

}
