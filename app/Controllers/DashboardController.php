<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\EnrollmentModel;
use App\Models\DepartmentModel;
use App\Models\CourseModel;

class DashboardController extends BaseController
{
    private StudentModel $studentModel;
    private EnrollmentModel $enrollmentModel;

    public function __construct()
    {
        parent::__construct();
        $this->studentModel    = new StudentModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    public function index(): void
    {
        $this->requireAuth();

        $studentStats     = $this->studentModel->getStats();
        $deptStats        = $this->studentModel->getByDepartmentStats();
        $recentEnrollments = $this->enrollmentModel->getRecentEnrollments(5);

        // Week 1: Server info on dashboard
        $serverInfo = [
            'php_version'     => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'protocol'        => $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1',
            'request_method'  => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'server_name'     => $_SERVER['SERVER_NAME'] ?? 'localhost',
        ];

        // Week 6: External API — OpenWeatherMap
        $weather = $this->fetchWeather();

        $this->view('dashboard', compact('studentStats', 'deptStats', 'recentEnrollments', 'serverInfo', 'weather'));
    }

    // Week 6: External API call with fallback
    private function fetchWeather(): ?array
    {
        $apiKey = $_ENV['WEATHER_API_KEY'] ?? '';
        $city   = $_ENV['WEATHER_CITY'] ?? 'Addis Ababa';

        if (!$apiKey || $apiKey === 'get-free-key-at-openweathermap.org') {
            return null;
        }

        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city)
             . "&appid=" . urlencode($apiKey) . "&units=metric";

        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $response = @file_get_contents($url, false, $ctx);

        if (!$response) return null;

        $data = json_decode($response, true);
        if (!$data || ($data['cod'] ?? 0) != 200) return null;

        return [
            'city'        => $data['name'],
            'temp'        => round($data['main']['temp']),
            'description' => ucfirst($data['weather'][0]['description'] ?? ''),
            'icon'        => $data['weather'][0]['icon'] ?? '',
            'humidity'    => $data['main']['humidity'],
        ];
    }
}