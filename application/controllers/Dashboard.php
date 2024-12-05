<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        render('dashboard/main', [
            'title' => 'Dashoard',
            'currentSidebar' => null,
            'currentSubSidebar' => null,
            'permission' => permission(
                [
                    'dashboard-view'
                ]
            )
        ]);
    }
}
