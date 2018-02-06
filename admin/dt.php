<?php

class Dt extends PluginBase
{
    private $tableName;
    private $db;

    const MESSAGE_TYPE_SUCCESS = "success";
    const MESSAGE_TYPE_ERROR = "error";
    const MESSAGE_TYPE_INFO = "info";

    /**
     * Dt constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->db = &$wpdb;
        $this->tableName = $this->db->prefix . "donation-types";
        add_action('admin_menu', array($this, self::PREFIX . "menu_operations"));
        add_action('admin_init', array($this, self::PREFIX . "style_script_reg_operations"));
    }

    public function menu_operations()
    {
        $pageTitle = __("DWC Settings - Donation Types", 'dwc-plugin');
        $menuText = __("DWC Settings - Donation Types", 'dwc-plugin');
        $capability = "manage_options";
        $callback = array($this, self::PREFIX . "page_controller");
        $menuSlug = "dwc_setting_donation_types";
        add_options_page($pageTitle, $menuText, $capability, $menuSlug, $callback);
    }

    public function style_script_reg_operations()
    {
        wp_register_style('datatables-css', "//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css");
        wp_register_script('datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js');
    }

    public function page_controller()
    {
        $action = @$_GET['action'];
        if (!empty($action)) {
            switch ($action) {
                case 'edit':
                case 'delete':
                    $resultData = $this->getById();
                    if (empty($resultData)) {
                        $title = __("ERROR!", 'dwc-plugin');
                        $message = __("Donation type not found!", 'dwc-plugin');
                        return $this->errorDisplay($title, $message);
                    } else {
                        return $action == 'edit' ? $this->editFormDisplay($resultData) : $this->deleteFormDisplay($resultData);
                    }
                    break;
                case 'add':
                    return $this->addFormDisplay();
                    break;
                default:
                    return $this->dataListDisplay();
                    break;
            }
        } else if (!empty($_POST['action'])) {
            $postAction = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
            switch ($postAction) {
                case 'add':
                case 'edit':
                    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
                    $label = filter_var($_POST['label'], FILTER_SANITIZE_STRING);
                    $defaultPrice = !empty($_POST['default_price']) ? str_replace(",", ".", filter_var($_POST['default_price'], FILTER_SANITIZE_STRING)) : null;
                    if ($postAction == "add") {
                        $this->add($name, $label, $defaultPrice);
                    } else {
                        $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
                        $this->edit($id, $name, $label, $defaultPrice);
                    }
                    break;

                case 'delete':
                    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
                    $this->delete($id);
                    break;
                default:
                    $title = __('ACTION ERROR!', 'dwc-plugin');
                    $message = sprintf(__('Unknown action `%s`', 'dwc-plugin'), $postAction);
                    $this->errorDisplay($title, $message);
                    break;
            }

        }
        return $this->dataListDisplay();
    }

    private function add($name, $label, $defaultPrice)
    {
        $result = $this->db->insert(
            $this->tableName, //table
            array('name' => $name, 'label' => $label, "default_price" => $defaultPrice), //data
            array('%s', '%s', '%f'), //data format
            array('%d') //where format
        );

        if (is_int($result) && $result > 0) {
            $title = __("SUCCESS", 'dwc-plugin');
            $content = __("Donation type successfully saved!", 'dwc-plugin');
            $this->resultDisplay($title, $content);
        } else if (!$result) {
            $title = __("ERROR!", 'dwc-plugin');
            $content = __("Error inserting data!", 'dwc-plugin');
            $this->resultDisplay($title, $content, self::MESSAGE_TYPE_ERROR);
        }
    }

    private function edit($id, $name, $label, $defaultPrice)
    {
        $result = $this->db->update(
            $this->tableName, //table
            array('name' => $name, 'label' => $label, "default_price" => $defaultPrice), //data
            array('ID' => $id), //where
            array('%s', '%s', '%f'), //data format
            array('%d') //where format
        );
        if (is_int($result) && $result > 0) {
            $title = __("SUCCESS", 'dwc-plugin');
            $content = __("Donation type successfully updated!", 'dwc-plugin');
            $this->resultDisplay($title, $content);
        } else if (!$result) {
            $title = __("ERROR!", 'dwc-plugin');
            $content = __("Error updating data!", 'dwc-plugin');
            $this->resultDisplay($title, $content, self::MESSAGE_TYPE_ERROR);
        }
    }

    private function delete($id)
    {
        $result = $this->db->delete($this->tableName, array('ID' => $id), array("%d"));
        if (is_int($result) && $result > 0) {
            $title = __("SUCCESS", 'dwc-plugin');
            $content = __("Donation type successfully deleted!", 'dwc-plugin');
            $this->resultDisplay($title, $content);
        } else if (!$result) {
            $title = __("ERROR!", 'dwc-plugin');
            $content = __("Error deleting data!", 'dwc-plugin');
            $this->resultDisplay($title, $content, self::MESSAGE_TYPE_ERROR);
        }
    }

    private function getById()
    {
        $id = (int)filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        $query = $this->db->prepare("SELECT * FROM `{$this->tableName}` WHERE id=%d", $id);
        return $this->db->get_row($query, ARRAY_A);
    }

    private function getAll()
    {
        $sql = "SELECT * FROM `" . $this->tableName . "` ORDER BY ord ASC";
        return $this->db->get_results($sql, ARRAY_A);
    }

    private function resultDisplay($title, $message, $messageType = self::MESSAGE_TYPE_SUCCESS)
    {
        ?>
        <div class="wrap">
            <div class="is-dismissible notice notice-<?php echo $messageType; ?>">
                <p>
                    <strong><?php echo $title; ?></strong>
                    <br><br>
                    <?php echo $message; ?>
                    <br>
                </p>
            </div>
        </div>
        <?php
    }

    private function errorDisplay($title, $message)
    {
        return $this->resultDisplay($title, $message, self::MESSAGE_TYPE_ERROR);
    }

    private function editFormDisplay($editData)
    {
        ?>
        <div id="wrap">
            <h2><?php
                printf(
                    __('Edit Donation Type #%s - %s', 'dwc-plugin'),
                    $editData['id'],
                    $editData['name']
                );
                ?></h2>
            <form method="post" action="options-general.php?page=dwc_setting_donation_types">
                <table class='wp-list-table widefat fixed'>
                    <tr>
                        <th><?php _e("Unique name", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="name" value="<?php echo $editData['name']; ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Label", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="label" value="<?php echo $editData['label']; ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Default Price", 'dwc-plugin'); ?></th>
                        <td><input type="number" min="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="
this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'red'"
                                   name="default_price" value="<?php echo $editData['default_price']; ?>"/></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="action" value="edit"/>
                            <input type='hidden' name="id" value="<?php echo $editData['id']; ?>">
                            <input type='submit' name="update" value='<?php _e("Save", 'dwc-plugin'); ?>'
                                   class='button'>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    private function deleteFormDisplay($deleteData)
    {
        ?>
        <div class="wrap">
            <form method="post" action="options-general.php?page=dwc_setting_donation_types">
                <p><?php
                    printf(
                        __('Are you sure you want to delete donation type <strong>#%s - %s</strong> ?', 'dwc-plugin'),
                        $deleteData['id'],
                        $deleteData['name']
                    );
                    ?></p>
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" name="id" value="<?php echo $deleteData['id']; ?>"/>
                <input type='submit' name="delete" value='<?php _e("Delete", 'dwc-plugin'); ?>' class='button'>
                <p>
                    <a href="options-general.php?page=dwc_setting_donation_types"><?php _e("&laquo; Back to the list.", 'dwc-plugin'); ?></a>
                </p>
            </form>
        </div>
        <?php
    }

    private function addFormDisplay()
    {
        ?>
        <div id="wrap">
            <h2><?php _e("Add Donation Type", 'dwc-plugin'); ?></h2>
            <form method="post" action="options-general.php?page=dwc_setting_donation_types">
                <table class='wp-list-table widefat fixed'>
                    <tr>
                        <th><?php _e("Unique name", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="name" value=""/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Label", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="label" value=""/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Default Price", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="default_price" pattern="^\d+(?:\.\d{1,2})?$" onblur="
this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'red'"/></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="action" value="add"/>
                            <input type='submit' name="add" value='<?php _e("Save", 'dwc-plugin'); ?>' class='button'>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    private function dataListDisplay()
    {
        $result = $this->getAll();
        wp_enqueue_style('datatables-css');
        wp_enqueue_script('datatables-js');
        ?>

        <h2><?php _e("Donation Types", 'dwc-plugin'); ?></h2>
        <p>
            <a href='options-general.php?page=dwc_setting_donation_types&action=add'><span
                        class='dashicons dashicons-plus-alt'></span> <?php _e("Add New Donation Type", 'dwc-plugin'); ?>
            </a>
        </p>
        <table id="dt" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>#</th>
                <th><?php _e("Name", 'dwc-plugin'); ?></th>
                <th><?php _e("Label", 'dwc-plugin'); ?></th>
                <th><?php _e("Default Price", 'dwc-plugin'); ?></th>
                <th><?php _e("Actions", 'dwc-plugin'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($result as $v) {
                $v['default_price'] = empty($v['default_price']) ? "-" : $v['default_price'];
                echo "<tr><td>{$v['id']}</td><td>{$v['name']}</td><td>{$v['label']}</td><td>{$v['default_price']}</td><td><a href='options-general.php?page=dwc_setting_donation_types&action=edit&id={$v['id']}'><span class='dashicons dashicons-edit'></span></a> <a href='options-general.php?page=dwc_setting_donation_types&action=delete&id={$v['id']}'><span class='dashicons dashicons-trash'></span></a></td>";
            }
            ?>
            </tbody>
        </table>

        <script>
            jQuery.noConflict();
            jQuery(document).ready(function () {
                jQuery('#dt').DataTable({
                    responsive: {
                        details: false
                    },
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "columns": [
                        {"width": "15px"},
                        null,
                        null,
                        null,
                        {"width": "30px"}
                    ]
                });
            });
        </script>
        <?php
    }
}