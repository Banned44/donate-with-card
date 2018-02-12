<?php

class Donations extends PluginBase
{
    private $tableName;
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = &$wpdb;
        $this->tableName = $this->db->prefix . "donations";
        add_action('admin_menu', array($this, self::PREFIX . "donations_admin_page_menu_reg"));
//        add_action('admin_init', array($this, self::PREFIX . "style_script_reg_operations"));
    }

    public function donations_admin_page_menu_reg()
    {
        $pageTitle = __("Donations", 'dwc-plugin');
        $menuText = __("Donations", 'dwc-plugin');
        $capability = "manage_options";
        $callback = array($this, self::PREFIX . "donations_admin_page_controller");
        $menuSlug = "donations";
        $icon = "dashicons-analytics";
        add_menu_page($pageTitle, $menuText, $capability, $menuSlug, $callback, $icon);
    }

    private function getAll()
    {
        $sql = "SELECT * FROM `" . $this->tableName . "` ORDER BY id DESC";
        return $this->db->get_results($sql, ARRAY_A);
    }

    public function donations_admin_page_controller()
    {
        $displayData = $this->getAll();
        wp_enqueue_style('datatables-css');
        wp_enqueue_script('datatables-js');
        ?>
        <div id="wrap">
            <h2>
                <?php _e('Donations', 'dwc-plugin'); ?></h2>
            <table id="dt" class="display" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?php _e("Date", 'dwc-plugin'); ?></th>
                    <th><?php _e("Name", 'dwc-plugin'); ?></th>
                    <th><?php _e("Email", 'dwc-plugin'); ?></th>
                    <th><?php _e("Tel", 'dwc-plugin'); ?></th>
                    <th><?php _e("Total", 'dwc-plugin'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($displayData as $v) {
                    echo "<tr><td>{$v['created']}</td><td>{$v['name']}&nbsp;{$v['surname']}</td><td>{$v['email']}</td><td>{$v['phone']}</td><td>{$v['total']}</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        <script>
            jQuery.noConflict();
            jQuery(document).ready(function () {
                jQuery('#dt').DataTable({
                    responsive: {
                        details: false
                    },
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],

                });
            });
        </script>
        <?php
    }


    public function addSuccessfulDonation($data)
    {
        // insert to donations table and get the id
        // if successful, add items to donation-items table.

        // total
        // name surname
        // email
        // phone
        // trid
        // donation-notes

        $result = $this->db->insert(
            $this->tableName,
            array(
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'trid' => $data['trid'],
                'donation-notes' => $data['donation_notes'],
                'total' => $data['total'],
            ),
            array('%s', '%f', '%s', '%s', '%s', '%s'),
            array('%d')
        );

        if (is_int($result) && $result > 0) {

        } else if (!$result) {

        }
    }
}