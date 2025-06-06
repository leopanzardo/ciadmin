<?php

if (!function_exists('renderCiAdminView')) {
    function renderCiAdminView(string $contentView, array $data = []): string
    {
        $html = view('ciadmin/header', $data);
        $html .= view($contentView, $data);
        $html .= view('ciadmin/footer', $data);
        return $html;
    }
}

if (!function_exists('parseTemplate')) {
    function parseTemplate(string $template, array $vars = []): string
    {
        foreach ($vars as $key => $value) {
            if (!is_scalar($value)) {
                $value = (string) json_encode($value);
            }
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
                $html .= '<div class="alert alert-' . $bootstrapClass . ' alert-dismissible fade show m-3" role="alert">' . session()->getFlashdata($key) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
            }
        }

        // Mostrar errores de validación
        if (session()->getFlashdata('errors')) {
            $errors = session()->getFlashdata('errors');
            $html .= '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
    <ul class="mb-0">';
            foreach ($errors as $error) {
                $html .= "<li>$error</li>";
            }
            $html .= '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
        }

        return $html;
    }
}
