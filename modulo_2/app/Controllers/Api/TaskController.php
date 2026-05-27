<?php

namespace App\Controllers\Api;

use App\Client\TaskClient;
use App\Core\Controller;

class TaskController extends Controller
{
    private TaskClient $client;

    public function __construct()
    {
        $this->client = new TaskClient();
    }

    public function index()
    {
        $this->validateRequestMethods(['GET']);
        $result = $this->client->listTasks();

        if (!$result['success']) {
            return $this->jsonResponse($result['error'], [], $result['statusCode'] ?: 502);
        }

        return $this->jsonResponse($result['data']);
    }

    public function store()
    {
        $this->validateRequestMethods(['POST']);
        $data = $this->getRequestData();

        if (empty($data['title'])) {
            return $this->jsonResponse(
                ['RequiredParams' => ['title' => 'Title is required']],
                'error',
                400
            );
        }

        $result = $this->client->createTask(
            $data['title'],
            $data['description'] ?? ''
        );

        if (!$result['success']) {
            return $this->jsonResponse($result['error'], [], $result['statusCode'] ?: 502);
        }

        return $this->jsonResponse($result['data'], '', $result['statusCode']);
    }
}
