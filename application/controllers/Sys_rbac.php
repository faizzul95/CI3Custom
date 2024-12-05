<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sys_rbac extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        model('SystemMenuNavigation_model');
    }

    public function index()
    {
        error(404); // no page to load. load in route
    }

    # SECTION FOR NAVIGATION MENU

    public function showNavigation($id)
    {
        $data = $this->SystemMenuNavigation_model->find($id);
        jsonResponse($data);
    }

    public function listNavigationDt()
    {
        $paginateData = $this->SystemMenuNavigation_model
            ->setAppends(['status_badge', 'menu_location_type']) // appends the badge status
            ->safeOutputWithException(['status_badge', 'menu_location_type'])
            ->setPaginateFilterColumn(
                [
                    'menu_title',
                    'menu_url',
                    'is_main_menu',
                    'menu_location'
                ]
            )->paginate_ajax($_POST, $_POST['condition']);

        if (hasData($paginateData, 'data')) {
            foreach ($paginateData['data'] as $key => $data) {

                $actionArr = [];

                $del = permission('menu-navigation-delete') ? actionBtn('delete', 'deleteRecord', $data['id'], ['class' => 'btn-sm btn-soft-danger']) : null;
                $edit = permission('menu-navigation-update') ? actionBtn('edit', 'editRecord', $data['id'], ['class' => 'btn-sm btn-soft-success']) : null;

                array_push($actionArr, $edit, $del);

                // Replace the data with formatted data
                $paginateData['data'][$key] = [
                    $data['menu_title'],
                    $data['menu_url'],
                    $data['menu_location_type'],
                    $data['menu_order'],
                    $data['status_badge'], // not an actually column, this coming from appends in models or using setAppends() method
                    '<div class="text-center">' . implode(' ', $actionArr) . '</div>'
                ];
            }
        }

        jsonResponse($paginateData);
    }

    public function saveNavigation()
    {
        $data = $this->request->all();

        // Default messaege
        $response = ['code' => 422, 'message' => 'Fail to save'];

        $id = hasData($data, 'menu_id', true);

        $is_main_menu = ($data['is_main_menu'] == 0) ? $data['is_main_menu']  : $data['sub_menu'];
        $typeForm = (empty($id)) ? 'create' : 'update';
        $current_order = (empty($data['menu_id'])) ? 0 : $data['old_menu_order'];

        $menu_order = $this->updateMenuArrangement($data['menu_order'], $data['menu_location'], $is_main_menu, $current_order, $typeForm);

        // Insert or update data
        $response = $this->SystemMenuNavigation_model->insertOrUpdate(['id' => $id], [
            'menu_title' => $data['menu_title'],
            'menu_description' => $data['menu_description'],
            'menu_url' => $data['menu_url'],
            'menu_order' => $menu_order,
            'menu_icon' => $data['menu_icon'],
            'is_main_menu' => $is_main_menu,
            'menu_location' => $data['menu_location'],
            'is_active' => $data['is_active'],
        ]);

        jsonResponse($response);
    }

    public function deleteNavigation($id)
    {
        $deleteData = $this->SystemMenuNavigation_model->destroy($id);
        jsonResponse($deleteData);
    }

    public function getListMenuSelect()
    {
        $menuloc = input('menu_location');
        $menu_id = input('menu_id');

        if (hasData($menuloc))
            $this->SystemMenuNavigation_model->where('menu_location', $menuloc);

        $data = $this->SystemMenuNavigation_model
            ->where('is_main_menu', 0)
            ->orderBy("menu_order", "asc")
            ->get();

        echo '<option value=""> - Select Sub Menu - </option>';
        if (hasData($data)) {
            foreach ($data as $row) {
                if ($menu_id != $row['id']) {
                    echo '<option value="' . $row['id'] . '">' . purify($row['menu_title']) . '</option>';
                }
            }
        }
    }

    public function getMenuOrderSelect()
    {
        $typeMenu = input('typeMenu');
        $menu_id = input('menuid');
        $menuloc = input('menu_location');
        $menu_id_pk = input('menu_id_pk');

        $data = null;
        if ($typeMenu == '0') {
            $data = $this->SystemMenuNavigation_model->where('is_main_menu', 0)->where('menu_location', $menuloc)->orderBy("menu_order", "asc")->get();
        } else if ($typeMenu == '1') {
            $data = $this->SystemMenuNavigation_model->where('is_main_menu', $menu_id)->where('menu_location', $menuloc)->orderBy("menu_order", "asc")->get();
        }

        $text = ($typeMenu == 0) ? 'Menu' : 'Sub Menu';
        echo '<option value="0"> From the beginning of ' . $text . ' </option>';

        if (count($data) > 0) {
            foreach ($data as $row) {
                if ($menu_id_pk != $row['id']) {
                    echo '<option value="' . $row['menu_order'] . '"> After ' . purify($row['menu_title']) . '</option>';
                }
            }
        }
    }

    public function updateMenuArrangement($menu_order = null, $menu_location = 1, $is_main_menu = null, $current_order = null, $formType = 'create')
    {
        // If menu_order is provided, update menu arrangement
        if (!empty($menu_order)) {
            $typeAction = '+'; // Default action to plus
            $query = null;

            // Prepare the query based on form type (create or update)
            if ($formType === 'create') {
                // For new menu items, shift orders greater than current menu_order
                $query = $this->SystemMenuNavigation_model
                    ->where('menu_order', '>', $menu_order)
                    ->where('is_main_menu', $is_main_menu)
                    ->where('menu_location', $menu_location)
                    ->orderBy("menu_order", "ASC")
                    ->get();

                $menu_order++;
            } else {

                // For updating existing menu items
                if ($current_order > $menu_order) {
                    // Moving item up in the order
                    $query = $this->SystemMenuNavigation_model
                        ->where('menu_location', $menu_location)
                        ->where('menu_order', '>', $menu_order)
                        ->where('menu_order', '<', $current_order)
                        ->where('is_main_menu', $is_main_menu)
                        ->orderBy("menu_order", "ASC")
                        ->get();

                    $menu_order++;
                } elseif ($current_order < $menu_order) {

                    // Moving item down in the order
                    $query = $this->SystemMenuNavigation_model
                        ->where('menu_location', $menu_location)
                        ->where('menu_order', '<=', $menu_order)
                        ->where('menu_order', '>', $current_order)
                        ->where('is_main_menu', $is_main_menu)
                        ->orderBy("menu_order", "ASC")
                        ->get();

                    $typeAction = '-'; // Change action to minus
                }
            }

            // Update menu orders
            if (!empty($query)) {
                foreach ($query as $row) {
                    $this->SystemMenuNavigation_model->patch(
                        [
                            'menu_order' => ($typeAction === '+')
                                ? $row['menu_order'] + 1
                                : $row['menu_order'] - 1
                        ],
                        $row['id']
                    );
                }
            }

            return $menu_order;
        }

        // Rearrange sub-menu items if no specific menu_order is provided
        $resultMenu = $this->SystemMenuNavigation_model
            ->where('is_main_menu', $is_main_menu)
            ->where('menu_location', $menu_location)
            ->orderBy("menu_order", "ASC")
            ->get();

        $arrangement = 1;
        foreach ($resultMenu as $row) {
            $this->SystemMenuNavigation_model->patch(
                ['menu_order' => $arrangement],
                $row['id']
            );
            $arrangement++;
        }

        return '1';
    }
}
