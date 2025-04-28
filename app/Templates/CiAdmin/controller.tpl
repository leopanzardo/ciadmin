<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\{{modelName}};

class {{controllerName}} extends BaseController
{
    protected $model;

    public function __construct()
    {
        helper('ciadmin');
        $this->model = new {{modelName}}();
    }

    /**
     * Muestra el listado de registros.
     *
     * @return string
     */
    public function index()
    {
        $data['rows'] = $this->model->findAll();
        $data['title'] = '{{controllerName}} - Listado';
        return renderCiAdminView('{{viewFolder}}/index', $data);
    }

    /**
     * Muestra el formulario de creación.
     *
     * @return string
     */
    public function create()
    {
        $data['title'] = 'Crear {{controllerName}}';
        return renderCiAdminView('{{viewFolder}}/create', $data);
    }

    /**
     * Procesa la creación de un registro.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        $data = $this->request->getPost();

        if (! $this->validate($this->validationRules())) {
            session()->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }

        if ($this->model->insert($data)) {
            session()->setFlashdata('success', 'Registro creado correctamente.');
        } else {
            session()->setFlashdata('error', 'Error al crear el registro.');
        }

        return redirect()->to('{{viewFolder}}');
    }

    /**
     * Muestra el formulario de edición.
     *
     * @param int $id
     * @return string
     */
    public function edit($id)
    {
        $data['row'] = $this->model->find($id);
        $data['title'] = 'Editar {{controllerName}}';
        return renderCiAdminView('{{viewFolder}}/edit', $data);
    }

    /**
     * Procesa la actualización de un registro.
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id)
    {
        $data = $this->request->getPost();

        if (! $this->validate($this->validationRules())) {
            session()->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }

        if ($this->model->update($id, $data)) {
            session()->setFlashdata('success', 'Registro actualizado correctamente.');
        } else {
            session()->setFlashdata('error', 'Error al actualizar el registro.');
        }

        return redirect()->to('{{viewFolder}}');
    }

    /**
     * Elimina un registro.
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id)
    {
        if ($this->model->delete($id)) {
            session()->setFlashdata('success', 'Registro eliminado correctamente.');
        } else {
            session()->setFlashdata('error', 'Error al eliminar el registro.');
        }

        return redirect()->to('{{viewFolder}}');
    }

    /**
     * Reglas de validación generadas automáticamente.
     *
     * @return array
     */
    protected function validationRules(): array
    {
        return {{validationRules}};
    }
    
}
