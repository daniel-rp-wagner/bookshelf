<?php

class SeriesController extends Controller
{
    public function index(): void
    {
        $model = $this->loadModel('Series');
        $list  = $model->getAllSeries();
        $this->outputData($list);
    }

    public function createSeries(): void
    {
        $data    = json_decode(file_get_contents('php://input'), true);
        $model   = $this->loadModel('Series');
        $result   = $model->createSeries($data);
        $this->outputData($result, 201);
    }

    public function seriesById(int $id): void
    {
        $model   = $this->loadModel('Series');
        $series  = $model->getSeriesById($id);
        if (!$series) {
            throw new ApiException(404, 'NOT_FOUND', 'Series not found');
        }
        $this->outputData($series);
    }

    public function updateSeries(int $id): void
    {
        $data  = json_decode(file_get_contents('php://input'), true);
        $data['id'] = $id;
        $model = $this->loadModel('Series');
        $result = $model->updateSeries($data);
        $this->outputData($result);
    }

    public function deleteSeries(int $id): void
    {
        $model = $this->loadModel('Series');
        $result = $model->deleteSeries($id);
        $this->outputData($result, 204);
    }
}