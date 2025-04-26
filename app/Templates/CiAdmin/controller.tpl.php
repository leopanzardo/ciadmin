<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\{{modelName}};

class {{controllerName}} extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new {{modelName}}();
    }

    public function index()
    {
        $data['rows'] = $this->model->findAll();
        return view('{{viewFolder}}/index', $data);
    }

    public function create()
    {
        return view('{{viewFolder}}/create');
    }

    public function store()
    {
        $data = $this->request->getPost();
        $this->model->insert($data);
        return redirect()->to('{{viewFolder}}');
    }

    public function edit($id)
    {
        $data['row'] = $this->model->find($id);
        return view('{{viewFolder}}/edit', $data);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        $this->model->update($id, $data);
        return redirect()->to('{{viewFolder}}');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        return redirect()->to('{{viewFolder}}');
    }
}
