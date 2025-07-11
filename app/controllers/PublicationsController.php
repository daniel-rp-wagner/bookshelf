<?php

class PublicationsController extends Controller
{
    public function index(): void
    {
        $model = $this->loadModel('Publication');
        $list  = $model->getAllPublications();
        $this->outputData($list);
    }

    public function createPublications(): void
    {
        $data    = json_decode(file_get_contents('php://input'), true);
        $model   = $this->loadModel('Publication');
        $result   = $model->createPublications($data);
        $this->outputData($result, 201);
    }

    public function publicationById(int $id): void
    {
        $model   = $this->loadModel('Publication');
        $series  = $model->getPublicationById($id);
        if (!$series) {
            throw new ApiException(404, 'NOT_FOUND', 'Publications not found');
        }
        $this->outputData($series);
    }

    public function updatePublications(int $id): void
    {
        $data  = json_decode(file_get_contents('php://input'), true);
        $data['id'] = $id;
        $model = $this->loadModel('Publication');
        $result = $model->updatePublications($data);
        $this->outputData($result);
    }

    public function deletePublications(int $id): void
    {
        $model = $this->loadModel('Publication');
        $result = $model->deletePublications($id);
        $this->outputData($result, 204);
    }
}