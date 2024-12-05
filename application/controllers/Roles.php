<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Roles extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        model('MasterRole_model');
        model('UserProfile_model');
    }

    public function index()
    {
        error(404); // no page to load. load in route
    }

    public function show($id)
    {
        $data = $this->MasterRole_model->find($id);
        jsonResponse($data);
    }

    public function listDt()
    {
        $paginateData = $this->MasterRole_model
            ->setAppends(['status_badge']) // appends the badge status
            ->safeOutputWithException(['status_badge'])
            ->withCount('users')
            ->setPaginateFilterColumn(
                [
                    'role_name',
                    'role_rank',
                    'role_status'
                ]
            )->paginate_ajax($_POST, $_POST['condition']);

        if (hasData($paginateData, 'data')) {
            foreach ($paginateData['data'] as $key => $data) {

                $actionArr = [];

                $del = permission('roles-delete') && $data['users_count'] < 1 ? actionBtn('delete', 'deleteRecord', $data['id'], ['class' => 'btn-sm btn-soft-danger']) : null;
                $edit = permission('roles-update') ? actionBtn('edit', 'editRecord', $data['id'], ['class' => 'btn-sm btn-soft-success']) : null;

                array_push($actionArr, $edit, $del);

                // Replace the data with formatted data
                $paginateData['data'][$key] = [
                    $data['role_name'],
                    hasData($data, 'role_rank', true, '<i><small> (Tiada rekod ditemui) </small></i>'),
                    $data['users_count'],
                    $data['status_badge'], // not an actually column, this coming from appends in models or using setAppends() method
                    '<div class="text-center">' . implode(' ', $actionArr) . '</div>'
                ];
            }
        }

        jsonResponse($paginateData);
    }

    public function save()
    {
        // Get data with safe from XSS attack
        $data = $this->request->all();

        // Default messaege
        $response = ['code' => 422, 'message' => 'Peranan gagal disimpan'];

        // Get the info from the form
        $id = hasData($data, 'id', true);

        // Insert or update data
        $response = $this->MasterRole_model->insertOrUpdate(['id' => $id], $data);

        jsonResponse($response);
    }

    public function delete($id)
    {
        $deleteData = $this->MasterRole_model->destroy($id);
        jsonResponse($deleteData);
    }

    public function select($id = NULL)
    {
        $dataRoles = $this->MasterRole_model->where('role_status', 1)->get();
        $selectData = '<option value=""> - Pilih - </option>';

        $listData = [];
        if (hasData($id)) {
            $userRoles = $this->UserProfile_model->select('role_id')->where('user_id', $id)->get();
            $listData = hasData($userRoles) ? array_column($userRoles, 'role_id') : [];
        }

        if ($dataRoles) {
            foreach ($dataRoles as $row) {
                if (hasData($listData) && in_array($row['id'], $listData)) {
                    continue;
                }

                $selectData .= '<option value="' . $row['id'] . '"> ' . $row['role_name'] . ' </option>';
            }
        }

        jsonResponse($selectData);
    }
}