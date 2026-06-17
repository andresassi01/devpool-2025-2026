<?php

namespace App\Client;

class TaskClient extends BaseClient
{
    protected string $baseUrl;
    protected int $timeout = 10;
    protected array $defaultHeaders = [
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    ];

    public function __construct()
    {
        $this->baseUrl = $_ENV['GOLANG_API_URL'] ?? 'http://host.docker.internal:8080';
        parent::__construct();
    }

    public function createTask(string $title, string $description = ''): array
    {
        $data = ['title' => $title];

        if ($description !== '') {
            $data['description'] = $description;
        }

        return $this->post('/v1/tasks', $data);
    }

    public function listTasks(): array
    {
        return $this->get('/v1/tasks');
    }
}
