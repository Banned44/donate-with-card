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
        try {
            $this->db->query('START TRANSACTION');
            $name = $data['donatorInfos']['name'];
            $email = $data['donatorInfos']['email'];
            $phone = $data['donatorInfos']['tel'];
            $trId = $data['donatorInfos']['donation_notes'];
            $donationNotes = $data['donatorInfos']['name'];
            $total = $data['donationTotal'];
            $insertionId = $this->insertDonationInfos($name, $email, $phone, $trId, $total, $donationNotes);

            foreach ($data['donationBasket'] as $donationItem) {
                $donationId = $donationItem['id'];
                $donationTypeId = $insertionId;
                $amount = $donationItem['price'];
                $this->insertDonationItems($donationId, $donationTypeId, $amount);
            }
            //  here add monolog and log that somebody has made a donation.
            return true;
            $this->db->query('COMMIT');
        } catch (Exception $e) {
            // here use that monolog to log the error with detailed description.
            $this->db->query('ROLLBACK');
            return false;
        }

    }

    private function insertDonationItems($donation_id, $donation_type_id, $amount)
    {
        $insertionData = [
            'donation_id' => $donation_id,
            'donation_type_id' => $donation_type_id,
            'amount' => $amount
        ];
        $result = $this->db->insert(
            $this->db->prefix . "donation-items",
            $insertionData,
            ['%d', '%d', '%f']
        );
        if ($result === false) {
            throw new Exception("Donation item insertion error. wpdb result returned false. Donation item data is : " . var_export($insertionData, 1));
        } else if (is_int($result) && $result === 1) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Donation item insertion error. wbdb result is not 1. Donation item data is :" . var_export($insertionData, 1));
        }
    }

    private function insertDonationInfos($name, $email, $phone, $trId, $total, $donationNotes = null)
    {
        $insertionData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'trid' => $trId,
            'donation-notes' => $donationNotes,
            'total' => $total
        ];
        $result = $this->db->insert(
            $this->tableName,
            $insertionData,
            array('%s', '%s', '%s', '%s', '%s', '%f')
        );
        if ($result === false) {
            throw new Exception("Donation info insertion error . wpdb result returned false. Donation info data is: " . var_export($insertionData, 1));
        } else if (is_int($result) && $result === 1) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Donation info insertion error . wbdb result is not 1. Donation info data is: " . var_export($insertionData, 1));
        }
    }
}