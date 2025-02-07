<?php
defined('BASEPATH') or exit('No direct script access allowed');;

class QueueController extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        model('SystemQueueJob_model');
    }

    public function index()
    {
        render('system/queue', [
            'title' => 'List Queue',
            'currentSidebar' => null,
            'currentSubSidebar' => null,
            'permission' => permission(
                [
                    'queue-list-view'
                ]
            )
        ]);
    }

    public function show($id)
    {
        $data = $this->SystemQueueJob_model->find($id);
        jsonResponse($data);
    }

    public function listDt()
    {
        $paginateData = $this->SystemQueueJob_model
            ->safeOutput()
            ->setPaginateFilterColumn(
                [
                    'uuid',
                    'type',
                    'payload',
                    'attempt',
                    'status',
                    'message'
                ]
            )->paginate_ajax($_POST);

        if (hasData($paginateData, 'data')) {
            foreach ($paginateData['data'] as $key => $data) {

                $actionArr = [];

                $del = permission('queue-delete') ? actionBtn('delete', 'deleteRecord', $data['id'], ['class' => 'btn-sm btn-soft-danger']) : null;
                $edit = permission('queue-update') ? actionBtn('edit', 'editRecord', $data['id'], ['class' => 'btn-sm btn-soft-success']) : null;

                array_push($actionArr, $edit, $del);

                // Replace the data with formatted data
                $paginateData['data'][$key] = [
                    $data['uuid'],
                    $data['type'],
                    $data['attempt'],
                    $data['payload'],
                    '<div class="text-center">' . implode(' ', $actionArr) . '</div>'
                ];
            }
        }

        jsonResponse($paginateData);
    }

    public function delete($id)
    {
        $deleteData = $this->SystemQueueJob_model->destroy($id);
        jsonResponse($deleteData);
    }
}
