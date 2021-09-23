<?php

namespace App\Controllers\Samples;

use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;

class ResourceController extends AbstractController
{
    protected function determineMethod(): string
    {
        $method = $this->request()->getParsedBody()['_method'] ?? $this->request()->getMethod();
        $method = strtoupper($method);
        $action = $this->getArgs()['action'] ?? '';
        $id = $this->getArgs()['id'] ?? null;

        if ($action === 'create') {
            return $method === 'POST' ? 'create' : 'createForm';
        }
        if ($id && $action === 'update') {
            return $method === 'UPDATE' ? 'update' : 'updateForm';
        }
        if ($id && $action === 'delete') {
            return $method === 'DELETE' ? 'delete' : 'deleteForm';
        }
        if ($id) return 'show';
        return 'list';
    }

    public function onList(): ResponseInterface
    {
        return $this->view('samples/resource.twig', [
            'method' => 'onList',
        ]);
    }

    public function onShow($id): ResponseInterface
    {
        return $this->view('samples/resource.twig', [
            'method' => 'onShow',
            'id' => $id,
        ]);
    }

    public function onCreateForm(): ResponseInterface
    {
        return $this->view('samples/resource.twig', [
            'method' => 'onCreateForm',
        ]);
    }

    public function onCreate(): ResponseInterface
    {
        $this->messages()->addSuccess('executed onCreate');
        return $this->redirectToRoute('resource', [
            'action' => 'show',
            'id' => 101,
        ]);
    }

    public function onUpdateForm($id): ResponseInterface
    {
        return $this->view('samples/resource.twig', [
            'method' => 'onUpdateForm',
            'id' => $id,
        ]);
    }

    public function onUpdate($id): ResponseInterface
    {
        $this->messages()->addSuccess('executed onUpdate');
        return $this->redirectToRoute('resource', [
            'action' => 'show',
            'id' => 101,
        ]);
    }

    public function onDeleteForm($id): ResponseInterface
    {
        return $this->view('samples/resource.twig', [
            'method' => 'onDeleteForm',
            'id' => $id,
        ]);
    }

    public function onDelete($id): ResponseInterface
    {
        $this->messages()->addSuccess('executed onDelete');
        return $this->redirectToRoute('resource');
    }
}