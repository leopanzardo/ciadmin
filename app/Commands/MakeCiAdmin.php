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
        '-f'      => 'Alias de --force.',
        '--only'  => 'Genera solo model, controller, view, routes o dashboard.',
        '-o'      => 'Alias de --only.',
        '--appname' => 'Nombre de la aplicación a usar en el dashboard y el header.',
        '-a'       => 'Alias de --appname.',
        '--table' => 'Nombre de tabla o tablas (separados por coma) para las cuales generar los componentes.',
        '-t'      => 'Alias de --table.',
        '--theme' => 'Nombre del tema de Bootswatch a utilizar, si no se especifica utilizará el tema por defecto de Bootstrap.'
        
    ];

    protected array $reservedViewFolders = ['ciadmin', 'shared', 'system'];
    protected array $routesToWrite = [];
    protected array $routesSummary = [];
    protected int $modelsCreated = 0;
    protected int $controllersCreated = 0;
    protected int $viewsCreated = 0;
    protected string $theme = 'default';
    protected string $templatePath = APPPATH . 'Templates/CiAdmin/';
    protected string $bootstrapTheme = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css';

    public function run(array $params)
    {
        CLI::write('Iniciando generación de CIAdmin...', 'green');

        // Manejar parámetros
        $force = array_key_exists('force', $params) || array_key_exists('f', $params);
        $only = $params['only'] ?? $params['o'] ?? null;
        $only = match ($only) {
            'models' => 'model',
            'controllers' => 'controller',
            'views' => 'view',
            default => $only,
        };
        if (!in_array($only, [null, 'model', 'controller', 'view', 'routes', 'dashboard'], true)) {
            CLI::error('Opción --only inválida. Usá model, controller, view, routes o dashboard.');
            return EXIT_ERROR;
        }
        $specificTables = $params['table'] ?? $params['t'] ?? null;
        $appName = $params['appname'] ?? $params['a'] ?? null;
        $this->theme = $params['theme'] ?? 'default';

        // Si se pasa un nuevo nombre de aplicación, actualizamos la constante
        if ($appName) {
            $this->updateAppNameConstant($appName);
        }

        // Conectar a base de datos
        $db = Database::connect();

        if ($specificTables) {
            $specificTables = array_map('trim', explode(',', $specificTables));
        } else {
            $specificTables = $db->listTables();
        }

        if (empty($specificTables)) {
            CLI::error('No se encontraron tablas para procesar.');
            return;
        }

        foreach ($specificTables as $table) {
            $className = ucfirst($table);

            CLI::write("Procesando tabla: {$table}", 'light_blue');

            if (!$only || $only === 'model') {
                $this->generateModel($className, $table, $force);
            }
            if (!$only || $only === 'controller') {
                $this->generateController($className, $table, $force);
            }
            if (!$only || $only === 'view') {
                $this->generateViews($className, $table, $force);
            }

            if (!$only || $only === 'routes') {
                $this->addRoutesForTable($className, $table);
            }
        }

        if (!$only || $only === 'dashboard') {
            $this->generateDashboard($force, isset($params['theme']));
        }

        if (!$only || $only === 'routes' || $only === 'dashboard') {
            if (!$this->generateRoutesFile(!$only || $only === 'dashboard')) {
                return EXIT_ERROR;
            }
        }

        $this->showGenerationSummary();

        CLI::write("CIAdmin generado con éxito!", 'green');
    }

    protected function generateModel(string $className, string $table, bool $force = false)
    {
        $modelPath = APPPATH . "Models/{$className}Model.php";

        if (($force === false) && file_exists($modelPath)) {
            CLI::write("-> Modelo ya existe: {$className}Model.php", 'orange');
            return;
        }

        $fieldData = $this->getTableFields($table);
        $fields = $fieldData['fields'];

        $primaryKey = $fieldData['primaryKey'];
        $useAutoIncrement = false;
        $useSoftDeletes = false;
        $useTimestamps = false;
        $createdField = '';
        $updatedField = '';
        $deletedField = '';
        $allowedFields = [];

        foreach ($fields as $field) {
            $name = $field['name'];

            if ($field['primary_key']) {
                $primaryKey = $name;
                $useAutoIncrement = $field['auto_increment'] ?? false;
                continue;
            }

            if ($name === 'deleted_at') {
                $useSoftDeletes = true;
                $deletedField = 'deleted_at';
                continue;
            }

            if ($name === 'created_at') {
                $useTimestamps = true;
                $createdField = 'created_at';
                continue;
            }

            if ($name === 'updated_at') {
                $useTimestamps = true;
                $updatedField = 'updated_at';
                continue;
            }

            $allowedFields[] = "'$name'";
        }

        $code = $this->renderTemplate('model', [
            'modelName'       => $className . 'Model',
            'tableName'       => $table,
            'primaryKey'      => $primaryKey,
            'useAutoIncrement'=> $useAutoIncrement ? 'true' : 'false',
            'returnType'      => 'array',
            'useSoftDeletes'  => $useSoftDeletes ? 'true' : 'false',
            'allowedFields'   => implode(', ', $allowedFields),
            'useTimestamps'   => $useTimestamps ? 'true' : 'false',
            'createdFieldLine' => $createdField ? "    protected \$createdField  = '{$createdField}';\n" : '',
            'updatedFieldLine' => $updatedField ? "    protected \$updatedField  = '{$updatedField}';\n" : '',
        ]);

        write_file($modelPath, $code);

        if ($force && file_exists($modelPath)) {
            CLI::write("-> Modelo sobrescrito: {$className}Model.php", 'light_blue');
        } else {
            CLI::write("-> Modelo creado: {$className}Model.php", 'green');
            $this->modelsCreated++;
        }
    }

    protected function generateController(string $className, string $table, bool $force = false)
    {
        $controllerPath = APPPATH . "Controllers/{$className}.php";

        if (($force === false) && file_exists($controllerPath)) {
            CLI::write("-> Controlador ya existe: {$className}.php", 'orange');
            return;
        }

        $fieldData = $this->getTableFields($table);
        $fields = $fieldData['fields'];

        $code = $this->renderTemplate('controller', [
            'controllerName' => $className,
            'modelName' => $className . 'Model',
            'viewFolder' => $table,
            'validationRules' => $this->generateValidationRules($fields),
        ]);

        write_file($controllerPath, $code);

        if ($force && file_exists($controllerPath)) {
            CLI::write("-> Controlador sobrescrito: {$className}.php", 'light_blue');
        } else {
            CLI::write("-> Controlador creado: {$className}.php", 'green');
            $this->controllersCreated++;
        }
    }

    protected function generateViews(string $className, string $table, bool $force = false)
    {
        if (in_array(strtolower($table), $this->reservedViewFolders)) {
            CLI::error("-> No se pueden generar vistas para la tabla reservada: {$table}");
            return;
        }

        $viewDir = APPPATH . "Views/{$table}";
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        $fieldData = $this->getTableFields($table);
        $fields = $fieldData['fields'];

        // Index view
        $indexViewPath = "{$viewDir}/index.php";
        if (($force === false) && file_exists($indexViewPath)) {
            CLI::write("-> Vista index ya existe: {$table}/index.php", 'orange');
        } else {
            list($thead, $tbody) = $this->generateTableFields($fieldData, $table);

            $html = $this->renderTemplate("view_index", [
                'viewFolder' => $table,
                'thead' => $thead,
                'tbody' => $tbody,
            ]);
            write_file($indexViewPath, $html);
            CLI::write("-> Vista index generada: {$table}/index.php", $force ? 'light_blue' : 'green');
            $this->viewsCreated++;
        }

        // Create view
        $createViewPath = "{$viewDir}/create.php";
        if (($force === false) && file_exists($createViewPath)) {
            CLI::write("-> Vista create ya existe: {$table}/create.php", 'orange');
        } else {
            $formFields = $this->generateFormFields($fields, 'post');
            $html = $this->renderTemplate("view_create", [
                'viewFolder' => $table,
                'formFields' => $formFields,
            ]);
            write_file($createViewPath, $html);
            CLI::write("-> Vista create generada: {$table}/create.php", $force ? 'light_blue' : 'green');
            $this->viewsCreated++;
        }

        // Edit view
        $editViewPath = "{$viewDir}/edit.php";
        if (($force === false) && file_exists($editViewPath)) {
            CLI::write("-> Vista edit ya existe: {$table}/edit.php", 'orange');
        } else {
            $formFields = $this->generateFormFields($fields, 'row');
            $html = $this->renderTemplate("view_edit", [
                'viewFolder' => $table,
                'formFields' => $formFields,
            ]);
            write_file($editViewPath, $html);
            CLI::write("-> Vista edit generada: {$table}/edit.php", $force ? 'light_blue' : 'green');
            $this->viewsCreated++;
        }
        
        // View view
        $viewViewPath = "{$viewDir}/view.php";
        if (($force === false) && file_exists($viewViewPath)) {
            CLI::write("-> Vista view ya existe: {$table}/view.php", 'orange');
        } else {
            $viewFields = $this->generateViewRowFields($fields);
            $html = $this->renderTemplate("view_view", [
                'viewFolder' => $table,
                'rowFields' => $viewFields,
            ]);
            write_file($viewViewPath, $html);
            CLI::write("-> Vista view generada: {$table}/view.php", $force ? 'light_blue' : 'green');
            $this->viewsCreated++;
        }

    }
    
    protected function generateDashboard(bool $force = false, bool $changeTheme = false)
    {
        CLI::write("Procesando Dashboard", 'light_blue');
        $controllerPath = APPPATH . "Controllers/Dashboard.php";
        if (($force === false) && file_exists($controllerPath)) {
            CLI::write("-> Dashboard.php ya existe, no se sobrescribe.", 'orange');
        } else {
            $template = $this->renderTemplate('dashboard_controller', []);
            write_file($controllerPath, $template);
            CLI::write("-> Controlador Dashboard generado: Dashboard.php", $force ? 'light_blue' : 'green');
            $this->controllersCreated++;
        }

        $viewPath = APPPATH . "Views/dashboard.php";
        $templatePath = $this->templatePath . "dashboard_view.php"; // <- Cambiamos esto

        if (($force === false) && file_exists($viewPath)) {
            CLI::write("-> dashboard.php ya existe, no se sobrescribe.", 'orange');
        } else {
            if (file_exists($templatePath)) {
                copy($templatePath, $viewPath);
                CLI::write("-> Vista dashboard generada: dashboard.php", $force ? 'light_blue' : 'green');
                $this->viewsCreated++;
            } else {
                CLI::error("-> No se encontró el template dashboard_view.php para el Dashboard.");
            }
        }
        
        $headerTemplatePath = $this->templatePath . 'header.tpl';
        $headerTargetPath = APPPATH . 'Views/ciadmin/header.php';
        if (!$force && !$changeTheme && is_file($headerTargetPath)) {
            return;
        }
        $templateData = [
            'APPNAME' => '<?= CIADMIN_APPNAME ?>',
            'DisplayFlashes' => '<?= displayFlashes() ?>',
            'BootstrapThemeURL' => $this->getBootswatchThemeCDN($this->theme),
        ];

        $headerHtml = parseTemplate(file_get_contents($headerTemplatePath), $templateData);
        write_file($headerTargetPath, $headerHtml);

        CLI::write("-> Vista header generada: ciadmin/header.php", $force ? 'light_blue' : 'green');
        $this->viewsCreated++;

    }
    
    protected function renderTemplate(string $templateName, array $vars = []): string
    {
        $templatePath = $this->templatePath . "{$templateName}.tpl";
        if (!file_exists($templatePath)) {
            CLI::error("No se encuentra el template: {$templateName}.tpl");
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
        $primaryKey = 'id';
        $primaryKeyIsNumeric = false;
        
        foreach ($fieldsData as $field) {
            $isInteger = in_array(strtolower($field->type), ['int', 'bigint', 'smallint', 'mediumint', 'tinyint']);
            $isPrimaryKey = property_exists($field, 'primary_key') && $field->primary_key;
            
            $fields[] = [
                'name'            => $field->name,
                'type'            => strtolower($field->type),
                'max_length'      => property_exists($field, 'max_length') ? $field->max_length : null,
                'primary_key'     => $isPrimaryKey,
                'auto_increment'  => $isPrimaryKey && $isInteger,
                'not_null'        => property_exists($field, 'nullable') ? !$field->nullable : false,
            ];

            if ($isPrimaryKey) {
                $primaryKey = $field->name;
                if ($isInteger) $primaryKeyIsNumeric = true;
            }
        }

        return [
            'fields' => $fields,
            'primaryKey' => $primaryKey,
            'primaryKeyIsNumeric' => $primaryKeyIsNumeric,
        ];
    }
    
    protected function generateFormFields(array $fields, string $source = 'post'): string
    {
        $html = "";
        $excludedFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($fields as $field) {
            $fieldName = $field['name'];

            // Saltar si es autoincremental o si está en la lista de campos excluidos
            if (!empty($field['auto_increment']) || in_array($fieldName, $excludedFields, true)) {
                continue;
            }

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
            $value = $source === 'post'
                ? '<?= old(\'' . $fieldName . '\') ?>'
                : '<?= old(\'' . $fieldName . '\', $row[\'' . $fieldName . '\'] ?? \'\') ?>';

            $isRequired = !empty($field['not_null']);
            $requiredAttr = $isRequired ? 'required' : '';
            $feedback = $isRequired
                ? "<div class=\"invalid-feedback\">\n            Por favor, completá el campo {$label}.\n        </div>"
                : '';

            $html .= <<<EOT
                <div class="mb-3 col-md-6">
                    <label for="{$fieldName}" class="form-label">{$label}</label>
                    <input type="{$inputType}" class="form-control" id="{$fieldName}" name="{$fieldName}" value="{$value}" {$requiredAttr}>
                    {$feedback}
                </div>
            EOT;
        }

        return $html;
    }
    
    protected function generateValidationRules(array $fields): string
    {
        $rules = [];

        // Campos que no requieren validación
        $excludedFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = strtolower($field['type']);
            $maxLength = $field['max_length'] ?? null;

            // Excluir campos innecesarios
            if (!empty($field['auto_increment']) || in_array($name, $excludedFields, true)) {
                continue;
            }

            $rule = [];

            if (!empty($field['not_null'])) {
                $rule[] = 'required';
            }

            // Tipos especiales
            if (str_contains($name, 'email')) {
                $rule[] = 'valid_email';
            } elseif (str_contains($name, 'password')) {
                $rule[] = 'min_length[6]';
            } elseif (in_array($type, ['int', 'bigint', 'smallint', 'tinyint', 'mediumint'])) {
                $rule[] = 'integer';
            } elseif (in_array($type, ['decimal', 'float', 'double'])) {
                $rule[] = 'decimal';
            } elseif ($type === 'date') {
                $rule[] = 'valid_date';
            } elseif (in_array($type, ['datetime', 'timestamp'])) {
                // Aquí podrías agregar validación de formato si lo necesitás
            }

            // Validar largo máximo si está disponible
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
    
    protected function generateTableFields(array $fieldData, string $viewFolder): array
    {
        $fields = $fieldData['fields'];
        $primaryKey = $fieldData['primaryKey'];
        
        // Campos a excluir del listado
        $excludedFields = ['created_at', 'updated_at', 'deleted_at'];
        $textTypes = ['text', 'tinytext', 'mediumtext', 'longtext', 'blob', 'tinyblob', 'mediumblob', 'longblob'];
        
        // Encabezados de la tabla
        $thead = "                <th>Acciones</th>\n";
        foreach ($fields as $field) {
            if (!empty($field['auto_increment']) || in_array($field['name'], $excludedFields, true) || in_array($field['type'], $textTypes)) {
                continue;
            }
            $thead .= "                <th>" . ucfirst(str_replace('_', ' ', $field['name'])) . "</th>\n";
        }

        // Filas de la tabla
        $tbody = "";
        $tbody .= "            <?php foreach (\$rows as \$row): ?>\n";
        $tbody .= "            <tr>\n";
        $tbody .= "                <td>\n";
        $tbody .= "                    <div class=\"btn-group\">\n";
        $tbody .= "                        <a href=\"<?= site_url('{$viewFolder}/view/' . \$row['{$primaryKey}']) ?>\" class=\"btn btn-info\" title=\"Ver\">\n";
        $tbody .= "                            <i class=\"bi bi-eye\"></i>\n";
        $tbody .= "                        </a>\n";
        $tbody .= "                        <a href=\"<?= site_url('{$viewFolder}/edit/' . \$row['{$primaryKey}']) ?>\" class=\"btn btn-primary\" title=\"Editar\">\n";
        $tbody .= "                            <i class=\"bi bi-pencil\"></i>\n";
        $tbody .= "                        </a>\n";
        $tbody .= "                    </div>\n";
        $tbody .= "                    <form method=\"post\" class=\"d-inline-block\" action=\"<?= esc(site_url('{$viewFolder}/delete/' . rawurlencode((string) \$row['{$primaryKey}'])), 'attr') ?>\" onsubmit=\"return confirm('¿Seguro que desea eliminar este registro?')\">\n";
        $tbody .= "                        <?= csrf_field() ?>\n";
        $tbody .= "                        <input type=\"hidden\" name=\"_method\" value=\"DELETE\">\n";
        $tbody .= "                        <button type=\"submit\" class=\"btn btn-danger\" aria-label=\"Eliminar\" title=\"Eliminar\"><i class=\"bi bi-trash\"></i></button>\n";
        $tbody .= "                    </form>\n";
        $tbody .= "                </td>\n";

        foreach ($fields as $field) {
            if (!empty($field['auto_increment']) || in_array($field['name'], $excludedFields, true) || in_array($field['type'], $textTypes)) {
                continue;
            }
            $tbody .= "                <td><?= \$row['{$field['name']}'] ?? '' ?></td>\n";
        }

        $tbody .= "            </tr>\n";
        $tbody .= "            <?php endforeach; ?>\n";

        return [$thead, $tbody];
    }
    
    protected function generateViewRowFields(array $fields): string
    {
        $html = "";

        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $label = ucfirst(str_replace('_', ' ', $fieldName));

            $html .= <<<EOT
                <div class="mb-3 col-md-6">
                    <label class="form-label fw-bold">{$label}</label>
                    <p class="form-control-plaintext border rounded py-2 px-3 bg-light"><?= \$row['{$fieldName}'] ?? '&nbsp;' ?></p>
                </div>
            EOT;
        }

        return $html;
    }
    
    protected function addRoutesForTable(string $className, string $table)
    {
        $fieldData = $this->getTableFields($table);
        $primaryKey = $fieldData['primaryKey'];
        $paramType = $fieldData['primaryKeyIsNumeric'] ? ':num' : ':segment';
        
        $this->routesToWrite[] = "\$routes->get('{$table}', '{$className}::index');";
        $this->routesSummary[] = ['GET', "{$table}", "{$className}::index"];

        $this->routesToWrite[] = "\$routes->get('{$table}/create', '{$className}::create');";
        $this->routesSummary[] = ['GET', "{$table}/create", "{$className}::create"];

        $this->routesToWrite[] = "\$routes->post('{$table}/store', '{$className}::store');";
        $this->routesSummary[] = ['POST', "{$table}/store", "{$className}::store"];
        
        $this->routesToWrite[] = "\$routes->get('{$table}/view/({$paramType})', '{$className}::view/\$1');";
        $this->routesSummary[] = ['GET', "{$table}/view/{$primaryKey}", "{$className}::view"];

        $this->routesToWrite[] = "\$routes->get('{$table}/edit/({$paramType})', '{$className}::edit/\$1');";
        $this->routesSummary[] = ['GET', "{$table}/edit/{$primaryKey}", "{$className}::edit"];

        $this->routesToWrite[] = "\$routes->post('{$table}/update/({$paramType})', '{$className}::update/\$1');";
        $this->routesSummary[] = ['POST', "{$table}/update/{$primaryKey}", "{$className}::update"];

        $this->routesToWrite[] = "\$routes->delete('{$table}/delete/({$paramType})', '{$className}::delete/\$1', ['filter' => 'csrf']);";
        $this->routesSummary[] = ['DELETE', "{$table}/delete/{$primaryKey}", "{$className}::delete"];
    }
    
    protected function showGenerationSummary()
    {
        CLI::newLine();
        CLI::write('Resumen general de generación:', 'cyan');
        CLI::newLine();

        // Tabla de cantidades
        $summaryData = [
            ['Modelos creados', $this->modelsCreated],
            ['Controladores creados', $this->controllersCreated],
            ['Vistas creadas', $this->viewsCreated],
            ['Total rutas generadas', count($this->routesSummary)],
        ];

        CLI::table($summaryData, ['Elemento', 'Cantidad']);

        CLI::newLine();
        CLI::write('Detalle de rutas generadas:', 'cyan');
        CLI::newLine();

        // Tabla de rutas
        $routesHeaders = ['Método', 'URL', 'Controlador::Método'];
        CLI::table($this->routesSummary, $routesHeaders);
    }
    
    protected function generateRoutesFile(bool $includeDashboard): bool
    {
        $routesPath = APPPATH . 'Config/Routes.php';
        $original = file_get_contents($routesPath);
        if ($original === false) {
            CLI::error('No se pudo leer Routes.php.');
            return false;
        }
        $eol = str_contains($original, "\r\n") ? "\r\n" : "\n";
        $lines = preg_split('/\r\n|\n|\r/', rtrim($original, "\r\n"));
        $knownRoutes = array_fill_keys(array_map('trim', $lines), true);
        $legacyDeletes = [];
        foreach ($this->routesToWrite as $route) {
            if (str_starts_with($route, '$routes->delete(')) {
                $legacy = str_replace(
                    ['->delete(', ", ['filter' => 'csrf']"],
                    ['->get(', ''],
                    $route
                );
                $legacyDeletes[$legacy] = isset($knownRoutes[$route]) ? null : $route;
                if (isset($knownRoutes[$legacy])) {
                    $knownRoutes[$route] = true;
                }
            }
        }
        $content = [];
        foreach ($lines as $line) {
            $statement = trim($line);
            if (array_key_exists($statement, $legacyDeletes)) {
                if ($legacyDeletes[$statement] !== null) {
                    $content[] = $legacyDeletes[$statement];
                    $legacyDeletes[$statement] = null;
                }
                continue;
            }
            $content[] = $line;
        }
        if ($includeDashboard) {
            $homeRoute = '$routes->get(\'/\', \'Home::index\');';
            $dashboardRoute = '$routes->get(\'/\', \'Dashboard::index\');';
            $homeIndex = array_search($homeRoute, array_map('trim', $content), true);
            if ($homeIndex !== false) {
                $content[$homeIndex] = $dashboardRoute;
            } elseif (!preg_match('~\$routes->(?:get|add|match)\h*\([^\r\n]*[\x22\x27]/[\x22\x27]~', $original)) {
                $content[] = $dashboardRoute;
            }
        }
        foreach ($this->routesToWrite as $route) {
            if (!isset($knownRoutes[$route])) {
                $content[] = $route;
                $knownRoutes[$route] = true;
            }
        }
        if ($this->routesToWrite !== [] && !isset($knownRoutes['$routes->setAutoRoute(false);'])) {
            $content[] = '$routes->setAutoRoute(false);';
        }
        $updated = implode($eol, $content) . $eol;
        if ($updated !== $original && !write_file($routesPath, $updated)) {
            CLI::error('No se pudo actualizar Routes.php.');
            return false;
        }
        return true;
    }
    
    protected function updateAppNameConstant(string $appName): void
    {
        $constantsPath = APPPATH . 'Config/Constants.php';

        // Leemos el contenido existente
        $content = file_get_contents($constantsPath);

        // Si ya existe CIADMIN_APPNAME, lo reemplazamos
        if (strpos($content, 'CIADMIN_APPNAME') !== false) {
            $content = preg_replace(
                "/defined\('CIADMIN_APPNAME'\)\s*\|\|\s*define\('CIADMIN_APPNAME',\s*'[^']*'\);/",
                "defined('CIADMIN_APPNAME') || define('CIADMIN_APPNAME', '{$appName}');",
                $content
            );
            CLI::write("Constante CIADMIN_APPNAME actualizada en Constants.php.", 'green');
        } else {
            // Si no existe, la agregamos al final
            $content .= "\n\ndefined('CIADMIN_APPNAME') || define('CIADMIN_APPNAME', '{$appName}');";
            CLI::write("Constante CIADMIN_APPNAME agregada a Constants.php.", 'green');
        }

        // Guardamos los cambios
        write_file($constantsPath, $content);
    }
    
    protected function getBootswatchThemeCDN(string $theme): string
    {
        $theme = strtolower($theme);
        if ($theme === 'default') {
            return $this->bootstrapTheme;
        }
        $themes = [
            'brite', 'cerulean', 'cosmo', 'cyborg', 'darkly', 'flatly', 'journal',
            'litera', 'lumen', 'lux', 'materia', 'minty', 'morph', 'pulse', 'quartz',
            'sandstone', 'simplex', 'sketchy', 'slate', 'solar', 'spacelab',
            'superhero', 'united', 'vapor', 'yeti', 'zephyr',
        ];
        if (!in_array($theme, $themes, true)) {
            CLI::write("Tema Bootswatch desconocido: {$theme}. Se usará Bootstrap.", 'yellow');
            return $this->bootstrapTheme;
        }
        return "https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/{$theme}/bootstrap.min.css";
    }


}
