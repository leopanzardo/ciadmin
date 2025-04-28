<?php

if (!function_exists('renderCiAdminView')) {
    function renderCiAdminView(string $contentView, array $data = []): string
    {
        $html = '';

        // Renderizar header
        $headerPath = APPPATH . 'Views/ciadmin/header.tpl';
        if (file_exists($headerPath)) {
            $html .= parseTemplate(file_get_contents($headerPath), $data);
        }

        // Renderizar contenido
        $contentPath = APPPATH . "Views/{$contentView}.php";
        if (file_exists($contentPath)) {
            $html .= parseTemplate(file_get_contents($contentPath), $data);
        }

        // Renderizar footer
        $footerPath = APPPATH . 'Views/ciadmin/footer.tpl';
        if (file_exists($footerPath)) {
            $html .= parseTemplate(file_get_contents($footerPath), $data);
        }

        return $html;
    }
}

if (!function_exists('parseTemplate')) {
    function parseTemplate(string $template, array $vars = []): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }
}

if (!function_exists('displayFlashes')) {
    function displayFlashes(): string
    {
        $html = '';

        // Mapeo de tipos de flash a clases Bootstrap
        $alertTypes = [
            'success' => 'success',
            'error'   => 'danger',
            'warning' => 'warning',
            'info'    => 'info',
        ];

        foreach ($alertTypes as $key => $bootstrapClass) {
            if (session()->getFlashdata($key)) {
                $html .= <<<EOT
<div class="alert alert-{$bootstrapClass} alert-dismissible fade show m-3" role="alert">
    <?= session()->getFlashdata('{$key}') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
EOT;
            }
        }

        // Mostrar errores de validación
        if (session()->getFlashdata('errors')) {
            $errors = session()->getFlashdata('errors');
            $html .= <<<EOT
<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
    <ul class="mb-0">
EOT;
            foreach ($errors as $error) {
                $html .= "<li>{$error}</li>";
            }
            $html .= <<<EOT
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
EOT;
        }

        return $html;
    }
}
