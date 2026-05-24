<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentModel;
use App\Services\EmailService;

class AuthController extends BaseController
{
    private UserModel    $userModel;
    private StudentModel $studentModel;
    private EmailService $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->userModel    = new UserModel();
        $this->studentModel = new StudentModel();
        $this->emailService = new EmailService();
    }

}