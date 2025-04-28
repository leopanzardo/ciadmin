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

    protected $usage = 'php spark make:ciadmin [options]';
    protected $arguments = [];
    protected $options = [
        '--force' => 'Sobrescribe archivos existentes.',
        '-f' => 'Alias de --force para sobrescribir archivos.',
    ];

    protected array $reservedViewFolders = ['ciadmin', 'shared', 'system'];
    protected array $routesToWrite = [];

    public function run(array $params)
    {
        CLI::write('🚀 Iniciando generación de CIAdmin...', 'green');

        $force = in_array('--force', $params) || in_array('-f', $params);

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

            $this->generateModel($className, $table, $force);
            $this->generateController($className, $table, $force);
            $this->generateViews($className, $table, $force);

            // Acumulamos rutas para este controlador
            $this->addRoutesForTable($className, $table);
        }

        $this->generateDashboard($force);

        // Finalmente, escribir el nuevo Routes.php
        $this->generateRoutesFile();

        CLI::write("✅ CIAdmin generado con éxito.", 'green');
    }

    protected function generateModel(string $className, string $table, bool $force = false)
    {
        $modelPath = APPPATH . "Models/{$className}Model.php";

        if (($force === false) && file_exists($modelPath)) {
            CLI::write("⚠️ Modelo ya existe: {$className}Model.php", 'light_gray');
            return;
        }

        $code = $this->renderTemplate('model', [
            'modelName' => $className . 'Model',
            'tableName' => $table
        ]);

        write_file($modelPath, $code);

        if ($force && file_exists($modelPath)) {
            CLI::write("✏️ Modelo sobrescrito: {$className}Model.php", 'yellow');
        } else {
            CLI::write("✅ Modelo creado: {$className}Model.php", 'green');
        }
    }

    protected function generateController(string $className, string $table, bool $force = false)
    {
        $controllerPath = APPPATH . "Controllers/{$className}.php";

        if (($force === false) && file_exists($controllerPath)) {
            CLI::write("⚠️ Controlador ya existe: {$className}.php", 'light_gray');
            return;
        }

        $fields = $this->getTableFields($table);

        $code = $this->renderTemplate('controller', [
            'controllerName' => $className,
            'modelName' => $className . 'Model',
            'viewFolder' => $table,
            'validationRules' => $this->generateValidationRules($fields),
        ]);

        write_file($controllerPath, $code);

        if ($force && file_exists($controllerPath)) {
            CLI::write("✏️ Controlador sobrescrito: {$className}.php", 'yellow');
        } else {
            CLI::write("✅ Controlador creado: {$className}.php", 'green');
        }
    }

    protected function generateViews(string $className, string $table, bool $force = false)
    {
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
        if (($force === false) && file_exists($indexViewPath)) {
            CLI::write("⚠️ Vista index ya existe: {$table}/index.php", 'light_gray');
        } else {
            list($thead, $tbody) = $this->generateTableFields($fields);

            $html = $this->renderTemplate("view_index", [
                'viewFolder' => $table,
                'thead' => $thead,
                'tbody' => $tbody,
            ]);
            write_file($indexViewPath, $html);
            CLI::write(($force ? "✏️" : "✅") . " Vista index generada: {$table}/index.php", $force ? 'yellow' : 'green');
        }

        // Create view
        $createViewPath = "{$viewDir}/create.php";
        if (($force === false) && file_exists($createViewPath)) {
            CLI::write("⚠️ Vista create ya existe: {$table}/create.php", 'light_gray');
        } else {
            $formFields = $this->generateFormFields($fields, 'post');
            $html = $this->renderTemplate("view_create", [
                'viewFolder' => $table,
                'formFields' => $formFields,
            ]);
            write_file($createViewPath, $html);
            CLI::write(($force ? "✏️" : "✅") . " Vista create generada: {$table}/create.php", $force ? 'yellow' : 'green');
        }

        // Edit view
        $editViewPath = "{$viewDir}/edit.php";
        if (($force === false) && file_exists($editViewPath)) {
            CLI::write("⚠️ Vista edit ya existe: {$table}/edit.php", 'light_gray');
        } else {
            $formFields = $this->generateFormFields($fields, 'row');
            $html = $this->renderTemplate("view_edit", [
                'viewFolder' => $table,
                'formFields' => $formFields,
            ]);
            write_file($editViewPath, $html);
            CLI::write(($force ? "✏️" : "✅") . " Vista edit generada: {$table}/edit.php", $force ? 'yellow' : 'green');
        }
    }
    
    protected function generateDashboard(bool $force = false)
    {
        $controllerPath = APPPATH . "Controllers/Dashboard.php";
        if (($force === false) && file_exists($controllerPath)) {
            CLI::write("⚠️ Dashboard.php ya existe, no se sobrescribe.", 'light_gray');
        } else {
            $template = $this->renderTemplate('dashboard_controller', []);
            write_file($controllerPath, $template);
            CLI::write(($force ? "✏️" : "✅") . " Controlador Dashboard generado: Dashboard.php", $force ? 'yellow' : 'green');
        }

        $viewPath = APPPATH . "Views/dashboard.php";
        if (($force === false) && file_exists($viewPath)) {
            CLI::write("⚠️ dashboard.php ya existe, no se sobrescribe.", 'light_gray');
        } else {
            $template = $this->renderTemplate('dashboard_view', []);
            write_file($viewPath, $template);
            CLI::write(($force ? "✏️" : "✅") . " Vista dashboard generada: dashboard.php", $force ? 'yellow' : 'green');
        }

        // Modificar o agregar la ruta '/'
        $routesFile = APPPATH . 'Config/Routes.php';
        $routesContents = file_get_contents($routesFile);

        if (preg_match("/\\\$routes->get\\(\s*'\/'\s*,/", $routesContents)) {
            $routesContents = preg_replace(
                "/\\\$routes->get\\(\s*'\/'\s*,\s*'[^']+'\s*\);/",
                "\$routes->get('/', 'Dashboard::index');",
                $routesContents
            );
            file_put_contents($routesFile, $routesContents);
            CLI::write("✅ Ruta '/' actualizada para usar Dashboard::index.", 'green');
        } else {
            $newRoute = "\n// Ruta principal generada automáticamente\n\$routes->get('/', 'Dashboard::index');\n";
            file_put_contents($routesFile, $newRoute, FILE_APPEND);
            CLI::write("✅ Ruta '/' creada para usar Dashboard::index.", 'green');
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
    
    protected function addRoutesForTable(string $className, string $table)
    {
        $this->routesToWrite[] = "\$routes->get('{$table}', '{$className}::index');";
        $this->routesToWrite[] = "\$routes->get('{$table}/create', '{$className}::create');";
        $this->routesToWrite[] = "\$routes->post('{$table}/store', '{$className}::store');";
        $this->routesToWrite[] = "\$routes->get('{$table}/edit/(:num)', '{$className}::edit/\$1');";
        $this->routesToWrite[] = "\$routes->post('{$table}/update/(:num)', '{$className}::update/\$1');";
        $this->routesToWrite[] = "\$routes->get('{$table}/delete/(:num)', '{$className}::delete/\$1');";
    }
    
    protected function generateRoutesFile()
    {
        $content = <<<PHP
    <?php

    use CodeIgniter\\Router\\RouteCollection;

    /**
     * @var RouteCollection \$routes
     */

    \$routes->setDefaultNamespace('App\\Controllers');
    \$routes->setDefaultController('Dashboard');
    \$routes->setDefaultMethod('index');
    \$routes->setTranslateURIDashes(false);
    \$routes->set404Override();
    \$routes->setAutoRoute(false);

    // Ruta principal
    \$routes->get('/', 'Dashboard::index');

    // Rutas generadas automáticamente
    PHP;

        foreach ($this->routesToWrite as $route) {
            $content .= "\n" . $route;
        }

        $routesPath = APPPATH . 'Config/Routes.php';
        write_file($routesPath, $content);

        CLI::write("✅ Archivo Routes.php sobrescrito exitosamente.", 'green');
    }

}
