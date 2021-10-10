<?php

namespace App\Controllers\Samples;

use App\Controllers\AbstractController;
use App\Controllers\Filters\PostArray;
use Psr\Http\Message\ResponseInterface;

class ResourceController extends AbstractController
{
    public function __construct(PostArray $postArray)
    {
        $this->addArgFilter($postArray);
    }

    protected function determineMethod(): string
    {
        $method = $this->request()->getParsedBody()['_method'] ?? $this->request()->getMethod();
        $method = strtoupper($method);
        $action = $this->getArgs()['action'] ?? '';
        $id = (int) ($this->getArgs()['id'] ?? null);

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

    public function onCreate($posts): ResponseInterface
    {
        $id = (int) ($posts['id'] ?? null);
        if (!$id) {
            $this->messages()->addError('Please specify ID to create');
            return $this->redirect()->toRoute('resource', [
                'action' => 'create',
            ]);
        }
        $this->messages()->addSuccess('Created a new resource ID: ' . $id);
        return $this->redirect()->toRoute('resource', [
            'action' => 'show',
            'id' => $id,
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
        $this->messages()->addSuccess('Updated ID:'.$id);
        return $this->redirect()->toRoute('resource', [
            'action' => 'show',
            'id' => $id,
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
        $this->messages()->addSuccess('Deleted ID:'.$id);
        return $this->redirect()->toRoute('resource');
    }
}