<?php

class Donations extends PluginBase
{
    private $tableName;
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = &$wpdb;
        $this->tableName = 'dwc_donations';
        add_action('admin_menu', array($this, self::PREFIX . "donations_admin_page_menu_reg"));
//        add_action('admin_init', array($this, self::PREFIX . "style_script_reg_operations"));
    }

    public function donations_admin_page_menu_reg()
    {
        $pageTitle = __("Bağışlar", 'dwc-plugin');
        $menuText = __("Bağışlar", 'dwc-plugin');
        $capability = "manage_options";
        $callback = array($this, self::PREFIX . "donations_admin_page_controller");
        $menuSlug = "donations";
        $icon = "dashicons-analytics";
        add_menu_page($pageTitle, $menuText, $capability, $menuSlug, $callback, $icon);
    }

    private function getAll()
    {
        $sql = "SELECT * FROM `" . self::DONATIONS_TABLE_NAME . "` ORDER BY id DESC";
        return $this->db->get_results($sql, ARRAY_A);
    }

    private function getDonationItems($id)
    {
        $sql = "SELECT dt.name as name, di.amount as amount FROM " . self::DONATION_ITEMS_TABLE_NAME . "  di," . self::DONATION_TYPES_TABLE_NAME . "  dt WHERE di.donation_type_id=dt.id AND di.donation_id=" . $id . " ORDER BY di.id DESC";
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
                <?php _e('Bağışlar', 'dwc-plugin'); ?></h2>
            <table id="dt" class="display" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?php _e("Tarih", 'dwc-plugin'); ?></th>
                    <th><?php _e("İsim", 'dwc-plugin'); ?></th>
                    <th><?php _e("E-Posta", 'dwc-plugin'); ?></th>
                    <th><?php _e("Tel", 'dwc-plugin'); ?></th>
                    <th><?php _e("Detaylar", 'dwc-plugin'); ?></th>
                    <th><?php _e("Notlar", 'dwc-plugin'); ?></th>
                    <th><?php _e("Toplam", 'dwc-plugin'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($displayData as $v) {

                    $donationBasket = $this->getDonationItems($v['id']);

                    $detailsText = "";
                    if (is_array($donationBasket) && count($donationBasket) > 0) {
                        foreach ($donationBasket as $vv) {
                            $detailsText .= stripslashes($vv['name']) . '->' . $vv['amount'] . __("TL", "dwc-plugin") . '<br/>';
                        }
                    }

                    echo "<tr><td>{$v['created']}</td><td>{$v['name']}&nbsp;{$v['surname']}</td><td>{$v['email']}</td><td>{$v['phone']}</td><td>{$detailsText}</td><td>{$v['donation_notes']}</td><td>{$v['total']}</td></tr>";
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


    public function addSuccessfulDonation($data, $provisionResultData)
    {
        // insert to donations table and get the id
        // if successful, add items to donation-items table.
        try {
            $this->db->query('START TRANSACTION');
            $name = $data['donatorInfos']['name'];
            $email = $data['donatorInfos']['email'];
            $phone = $data['donatorInfos']['tel'];
            $donationNotes = $data['donatorInfos']['donation_notes'];
            $total = $data['donationTotal'];
            $insertionId = $this->insertDonationInfos($name, $email, $phone, $total, $donationNotes, $provisionResultData);
            foreach ($data['donationBasket'] as $donationItem) {
                $donationId = $insertionId;
                $donationTypeId = $donationItem['id'];
                $amount = $donationItem['price'];
                $this->insertDonationItems($donationId, $donationTypeId, $amount);
            }
            //  here add monolog and log that somebody has made a donation.
            $this->db->query('COMMIT');
            return true;
        } catch (Exception $e) {
            error_log("addSuccessfulDonation error:" . $e->getMessage());
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
            self::DONATION_ITEMS_TABLE_NAME,
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

    private function insertDonationInfos($name, $email, $phone, $total, $donationNotes = null, $provision_result = null)
    {
        $insertionData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'donation_notes' => $donationNotes,
            'provision_result' => print_r($provision_result, 1),
            'total' => $total
        ];
        $result = $this->db->insert(
            self::DONATIONS_TABLE_NAME,
            $insertionData,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%f')
        );
        if ($result === false) {
            throw new Exception("Bağış bilgisi ekleme hatası! wpdb result returned false. Donation info data is: " . var_export($insertionData, 1));
        } else if (is_int($result) && $result === 1) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Bağış bilgisi ekleme hatası! wbdb result is not 1. Donation info data is: " . var_export($insertionData, 1));
        }
    }
}