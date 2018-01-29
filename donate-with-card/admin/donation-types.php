<?php
/**
 * Donation types admin page
 */
if (!defined(ABSPATH)) {
    exit;
}

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
    }

    public function dwc_donation_types_controller()
    {
        $action = $_GET['action'];
        if (!empty($action)) {
            switch ($action) {
                case 'edit':
                    return $this->dwc_display_edit_form();
                    break;
                case 'delete':

                    break;
                case 'add':

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
        ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" type="text/css"
              media="all"/>
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>


        <h2>Donation Types</h2>
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
                echo "<tr><td>{$v['id']}</td><td>{$v['name']}</td><td>{$v['label']}</td><td>{$v['default_price']}</td><td><a href='options-general.php?page=dwc_setting_donation_types&action=edit&id={$v['id']}'><span class='dashicons dashicons-edit'></span></a><a href='options-general.php?page=dwc_setting_donation_types&action=delete&id={$v['id']}'><span class='dashicons dashicons-trash'></span></a></td>";
            }
            ?>
            </tbody>
        </table>

        <script>
            $(document).ready(function () {
                $('#example').DataTable({
                    responsive: {
                        details: false
                    },
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "columns": [
                        {"width": "15px"},
                        null,
                        null,
                        null,
                        {"width": "15px"}
                    ]
                });
            });
        </script>
        <?php
    }

    public function dwc_display_edit_form()
    {
        global $wpdb;
        $id = (int)$_GET['id'];
        $query = $wpdb->prepare("SELECT * FROM `{$this->tableName}` WHERE id=%d", $id);
        $resultData = $wpdb->get_row($query, ARRAY_A);
        if (empty($resultData)) {
            ?>
            <div class="wrap">
                <div class="error"><p>ERROR!</p></div>
                Donation type not found! <a
                        href="<?php echo admin_url('options-general.php?page=dwc_setting_donation_types') ?>">&laquo;
                    Back to the list.</a>
            </div>
            <?php
        } else {

            var_dump($resultData);
        }
    }
}

new DWC_Donation_Types();
