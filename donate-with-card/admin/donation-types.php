<?php
/**
 * Donation types admin page
 */
//if (!defined(ABSPATH)) {
//    exit;
//}

// Add Settings menu
// Display donation type list
// Display Add / Edit
// Add Edit Delete Operations
class DWC_Donation_Types
{
    private $tableName;

    /**
     * DWC_Donation_Types constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . "donation-types";
        // Insert a menu into settings page.
        add_action('admin_menu', array($this, 'dwc_add_options_submenu'));
        add_action('admin_init', array($this, 'dwc_donation_types_main_page_add_styles'));
    }

    public function dwc_donation_types_controller()
    {
        $action = $_GET['action'];
        if (!empty($action)) {
            switch ($action) {
                case 'edit':
                    $resultData = $this->getById();
                    if (empty($resultData)) {
                        $title = "ERROR!";
                        $message = "Donation type not found!";
                        return $this->dwc_show_error_page($title, $message);
                    } else {
                        return $this->showEditForm($resultData);
                    }

                    break;
                case 'delete':
                    $resultData = $this->getById();
                    if (empty($resultData)) {
                        $title = "ERROR!";
                        $message = "Donation type not found!";
                        return $this->dwc_show_error_page($title, $message);
                    } else {
                        return $this->dwc_show_delete_form($resultData);
                    }
                    break;
                case 'add':
                    return $this->showAddForm();
                    break;
                default:
                    break;
            }
        } else {
            return $this->dwc_display_edit_page();
        }
    }

    public function dwc_add_options_submenu()
    {
        $pageTitle = "DWC Settings - Donation Types";
        $menuText = "DWC Settings - Donation Types";
        $capability = "manage_options";
        $callback = array($this, "dwc_donation_types_controller");
        $menuSlug = "dwc_setting_donation_types";
        add_options_page($pageTitle, $menuText, $capability, $menuSlug, $callback);
    }

    public function dwc_display_edit_page()
    {
        global $wpdb;
        $sql = "SELECT * FROM `" . $wpdb->prefix . "donation-types` " . "ORDER BY ord ASC";
        $result = $wpdb->get_results($sql, ARRAY_A);
        wp_enqueue_style('datatables-css');
        wp_enqueue_script('datatables-js');
        ?>

        <h2>Donation Types</h2>
        <p>
            <a href='options-general.php?page=dwc_setting_donation_types&action=add'><span
                        class='dashicons dashicons-plus-alt'></span> Add New Donation Type</a>
        </p>
        <table id="example" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Label</th>
                <th>Default Price</th>
                <th>Edit</th>
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
                jQuery('#example').DataTable({
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

    public function dwc_donation_types_main_page_add_styles()
    {
        wp_register_style('datatables-css', "//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css");
        wp_register_script('datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js');
    }


    private function getById()
    {
        global $wpdb;
        $id = (int)$_GET['id'];
        $query = $wpdb->prepare("SELECT * FROM `{$this->tableName}` WHERE id=%d", $id);
        return $wpdb->get_row($query, ARRAY_A);
    }

    private function dwc_show_error_page($title, $message)
    {
        echo <<<HTML
<div class="wrap"><div class="notice notice-error"><p>$title</p><p>$message<br/><a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a></p></div></div>
HTML;
    }

    private function dwc_show_delete_form($resultData)
    {
        if (!$this->doDeleteOperation()) {
            ?>
            <div class="wrap">
                <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <p>
                        Are you sure you want to delete donation type <strong>#<?php echo $resultData['id']; ?>
                            - <?php echo $resultData['name']; ?></strong>
                    </p>
                    <input type="hidden" name="id" value="<?php echo $resultData['id']; ?>"/>
                    <input type='submit' name="delete" value='Delete' class='button'>
                    <p>
                        <a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a>
                    </p>
                </form>
            </div>
            <?php
        }
    }

    private function doDeleteOperation()
    {
        global $wpdb;
        if (isset($_POST['delete']) && $_POST['delete'] == 'Delete' && $_POST['id'] == $_GET['id']) {
            $result = $wpdb->delete($this->tableName, array('ID' => $_POST['id']), array("%d"));
            if (is_int($result) && $result > 0) {
                echo <<<HTML
<div class="wrap"><div class="notice notice-success"><p>SUCCESS</p><p>($result) Donation type successfully deleted!<br/><a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a></p></div></div>
HTML;
            } else if (!$result) {
                echo <<<HTML
<div class="wrap"><div class="notice notice-warning"><p>Warning</p><p>Error deleting donation type!<br/><a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a></p></div></div>
HTML;
            }
            return true;
        }
        return false;

    }

    private function showEditForm($donationTypeData)
    {
        if (!$this->doEditOperation()) {
            ?>
            <div id="wrap">
                <h2>Edit Donation Type #<?php echo $donationTypeData['id']; ?>
                    - <?php echo $donationTypeData['name']; ?></h2>
                <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <table class='wp-list-table widefat fixed'>
                        <tr>
                            <th>Unique name</th>
                            <td><input type="text" name="name" value="<?php echo $donationTypeData['name']; ?>"/></td>
                        </tr>
                        <tr>
                            <th>Label</th>
                            <td><input type="text" name="label" value="<?php echo $donationTypeData['label']; ?>"/></td>
                        </tr>
                        <tr>
                            <th>Default Price</th>
                            <td><input type="text" name="default_price"
                                       value="<?php echo $donationTypeData['default_price']; ?>"/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input type='hidden' name="id" value="<?php echo $donationTypeData['id']; ?>">
                                <input type='submit' name="update" value='Save' class='button'>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
        }
    }

    private function doEditOperation()
    {
        global $wpdb;
        if (isset($_POST['update']) && $_POST['update'] == 'Save' && $_POST['id'] == $_GET['id']) {
            $result = $wpdb->update(
                $this->tableName, //table
                array('name' => $_POST['name'], 'label' => $_POST['label'], "default_price" => $_POST['default_price']), //data
                array('ID' => $_POST['id']), //where
                array('%s'), //data format
                array('%d') //where format
            );

            if ($result > 0) {
                echo <<<HTML
<div class="wrap"><div class="notice notice-success"><p>SUCCESS</p><p>Donation type successfully saved!<br/><a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a></p></div></div>
HTML;

            } else if ($result == 0) {
                echo <<<HTML
<div class="wrap"><div class="notice notice-warning"><p>Warning</p><p>No changes detected!<br/><a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a></p></div></div>
HTML;
            }
            return true;
        }
        return false;
    }

    private function showAddForm()
    {
        if (!$this->doAddOperation()) {
            ?>
            <div id="wrap">
                <h2>Add Donation Type</h2>
                <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <table class='wp-list-table widefat fixed'>
                        <tr>
                            <th>Unique name</th>
                            <td><input type="text" name="name" value="<?php echo $donationTypeData['name']; ?>"/></td>
                        </tr>
                        <tr>
                            <th>Label</th>
                            <td><input type="text" name="label" value="<?php echo $donationTypeData['label']; ?>"/></td>
                        </tr>
                        <tr>
                            <th>Default Price</th>
                            <td><input type="text" name="default_price"
                                       value="<?php echo $donationTypeData['default_price']; ?>"/></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input type='submit' name="add" value='Save' class='button'>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
        }
    }

    private function doAddOperation()
    {
        global $wpdb;
        if (isset($_POST['add']) && $_POST['add'] == 'Save') {
            $result = $wpdb->insert(
                $this->tableName, //table
                array('name' => $_POST['name'], 'label' => $_POST['label'], "default_price" => $_POST['default_price']), //data
                array('%s'), //data format
                array('%d') //where format
            );

            if (is_int($result) && $result > 0) {
                echo <<<HTML
<div class="wrap"><div class="notice notice-success"><p>SUCCESS</p><p>Donation type successfully saved!<br/><a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a></p></div></div>
HTML;
            } else if (!$result) {
                echo <<<HTML
<div class="wrap"><div class="notice notice-error"><p>ERROR!</p><p>Error inserting data!<br/><a href="options-general.php?page=dwc_setting_donation_types">&laquo; Back to the list.</a></p></div></div>
HTML;
            }
            return true;
        }
        return false;
    }
}

new DWC_Donation_Types();
