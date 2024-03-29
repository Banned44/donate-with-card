<?php

/**
 * Class Dt
 * Donation Types Settings Page Menu, Display and Add/Edit/Delete Display/Operation class.
 */
class Dt extends PluginBase
{
    private $tableName;
    private $db;

    /**
     * Operation result dialog classnames
     */
    const MESSAGE_TYPE_SUCCESS = "success";
    const MESSAGE_TYPE_ERROR = "error";
    const MESSAGE_TYPE_INFO = "info";

    /**
     * Dt constructor.
     * Registers menu and style_script_reg operations to wordpress.
     */
    public function __construct()
    {
        global $wpdb;
        $this->db = &$wpdb;
        $this->tableName = DONATION_TYPES_TABLE_NAME;
        add_action('admin_menu', array($this, self::PREFIX . "menu_operations"));
        add_action('admin_init', array($this, self::PREFIX . "style_script_reg_operations"));
    }

    /**
     * Adds donation type config page to wordpress settings menu.
     */
    public function menu_operations()
    {
        $pageTitle = __("Online Bağış Eklentisi Ayarları", 'dwc-plugin');
        $menuText = __("Online Bağış Eklentisi Ayarları", 'dwc-plugin');
        $capability = "manage_options";
        $callback = array($this, self::PREFIX . "page_controller");
        $menuSlug = "dwc_setting_donation_types";
        add_options_page($pageTitle, $menuText, $capability, $menuSlug, $callback);
    }

    /**
     * Adds datatable css/js scripts to header
     */
    public function style_script_reg_operations()
    {
        wp_register_style('datatables-css', "//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css");
        wp_register_script('datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js');
    }

    /**
     * Donation types admin page main controller
     */
    public function page_controller()
    {
        $action = @$_GET['action'];
        if (!empty($action)) {
            switch ($action) {
                case 'edit':
                case 'delete':
                    $resultData = $this->getById();
                    if (empty($resultData)) {
                        $title = __("Hata!", 'dwc-plugin');
                        $message = __("Bağış Tipi Bulunamadı!", 'dwc-plugin');
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
                case 'saveVposSettings':
                    update_option(DWC_OPTION_NAME_VPOS_CUSTOMER_ID, $_POST['vposCustomerId']);
                    update_option(DWC_OPTION_NAME_VPOS_MERCHANT_ID, $_POST['vposMerchantId']);
                    update_option(DWC_OPTION_NAME_VPOS_USERNAME, $_POST['vposUsername']);
                    update_option(DWC_OPTION_NAME_VPOS_PASSWORD, $_POST['vposPassword']);
                    update_option(DWC_OPTION_NAME_VPOS_CARDVALIDATIONURL, $_POST['vposValidationUrl']);
                    update_option(DWC_OPTION_NAME_VPOS_CARPROVISIONURL, $_POST['vposProvisionUrl']);
                    $this->resultDisplay(__("İşlem Başaılı!", 'dwc-plugin'), __("Sanal pos ayarları başarıyla kaydedildi.", 'dwc-plugin'));

                    break;
                default:
                    $title = __('AKSİYON HATASI!', 'dwc-plugin');
                    $message = sprintf(__('Bilinmeyen aksiyon `%s`', 'dwc-plugin'), $postAction);
                    $this->errorDisplay($title, $message);
                    break;
            }

        }
        return $this->dataListDisplay();
    }

    /**
     * Adds a new donation type to database
     * @param $name string
     * @param $label string
     * @param $defaultPrice string|float (decimal sepearator must be a dot)
     */
    private function add($name, $label, $defaultPrice)
    {
        $result = $this->db->insert(
            $this->tableName, //table
            array('name' => $name, 'label' => $label, "default_price" => $defaultPrice), //data
            array('%s', '%s', '%f'), //data format
            array('%d') //where format
        );

        if (is_int($result) && $result > 0) {
            $title = __("İşlem Başarılı", 'dwc-plugin');
            $content = __("Bağış tipi başarıyla kaydedildi!", 'dwc-plugin');
            $this->resultDisplay($title, $content);
        } else if (!$result) {
            $title = __("Hata!", 'dwc-plugin');
            $content = __("Veri ekleme sırasında hata oluştu!", 'dwc-plugin');
            $this->resultDisplay($title, $content, self::MESSAGE_TYPE_ERROR);
        }
    }

    /**
     * Edits given donation type id with given datas
     * @param $id int
     * @param $name string
     * @param $label string
     * @param $defaultPrice string|float|null
     */
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
            $title = __("İşlem Başarılı!", 'dwc-plugin');
            $content = __("Bağış tipi başarıyla güncellendi!", 'dwc-plugin');
            $this->resultDisplay($title, $content);
        } else if (!$result) {
            $title = __("Hata!", 'dwc-plugin');
            $content = __("Veri güncelleme sırasında hata oluştu!", 'dwc-plugin');
            $this->resultDisplay($title, $content, self::MESSAGE_TYPE_ERROR);
        }
    }

    /**
     * Deletes given donation type by id
     * @param $id int
     */
    private function delete($id)
    {
        $result = $this->db->delete($this->tableName, array('ID' => $id), array("%d"));
        if (is_int($result) && $result > 0) {
            $title = __("İşlem Başarılı!", 'dwc-plugin');
            $content = __("Bağış tipi başarı ile silindi!", 'dwc-plugin');
            $this->resultDisplay($title, $content);
        } else if (!$result) {
            $title = __("Hata!", 'dwc-plugin');
            $content = __("Veri silme sırasında hata oluştu!", 'dwc-plugin');
            $this->resultDisplay($title, $content, self::MESSAGE_TYPE_ERROR);
        }
    }

    /**
     * Returns the donation type by the id (id is taken from $_GET['id'])
     * @return array|null
     */
    private function getById()
    {
        $id = (int)filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        $query = $this->db->prepare("SELECT * FROM `{$this->tableName}` WHERE id=%d", $id);
        return $this->db->get_row($query, ARRAY_A);
    }

    /** Returns the donation type list
     * @return mixed
     */
    private function getAll()
    {
        $sql = "SELECT * FROM `" . $this->tableName . "` ORDER BY ord ASC";
        return $this->db->get_results($sql, ARRAY_A);
    }

    /**
     * Returns success message html
     * @param $title
     * @param $message
     * @param string $messageType
     */
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

    /**
     * Returns fail message html
     * @param $title
     * @param $message
     */
    private function errorDisplay($title, $message)
    {
        return $this->resultDisplay($title, $message, self::MESSAGE_TYPE_ERROR);
    }

    /**
     * Returns edit form html
     * @param $editData array should contain these indexes: id,name,label,default_price
     */
    private function editFormDisplay($editData)
    {
        ?>
        <div id="wrap">
            <h2><?php
                printf(
                    __('Bağış tipi güncelleme #%s - %s', 'dwc-plugin'),
                    $editData['id'],
                    $editData['name']
                );
                ?></h2>
            <form method="post" action="options-general.php?page=dwc_setting_donation_types">
                <table class='wp-list-table widefat fixed'>
                    <tr>
                        <th><?php _e("Benzersiz Ad(Boşluk içermemeli, büyük harflerle yazılmalıdır)", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="name" value="<?php echo $editData['name']; ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("İsim", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="label" value="<?php echo $editData['label']; ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Varsayılan Değer(Ondalık olarak nokta (.) kullanın)", 'dwc-plugin'); ?></th>
                        <td><input type="number" min="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="
this.parentNode.parentNode.style.backgroundColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'inherit':'red'"
                                   name="default_price" value="<?php echo $editData['default_price']; ?>"/></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="action" value="edit"/>
                            <input type='hidden' name="id" value="<?php echo $editData['id']; ?>">
                            <input type='submit' name="update" value='<?php _e("Kaydet", 'dwc-plugin'); ?>'
                                   class='button'>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Returns delete form html
     * @param $deleteData array should contain these indexes: id,name
     */
    private function deleteFormDisplay($deleteData)
    {
        ?>
        <div class="wrap">
            <form method="post" action="options-general.php?page=dwc_setting_donation_types">
                <p><?php
                    printf(
                        __('<strong>#%s - %s</strong> olarak kaydedilen bağış tipini silmek istediğinize emin misiniz?', 'dwc-plugin'),
                        $deleteData['id'],
                        $deleteData['name']
                    );
                    ?></p>
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" name="id" value="<?php echo $deleteData['id']; ?>"/>
                <input type='submit' name="delete" value='<?php _e("Sil", 'dwc-plugin'); ?>' class='button'>
                <p>
                    <a href="options-general.php?page=dwc_setting_donation_types"><?php _e("&laquo; Listeleme ekranına geri dön.", 'dwc-plugin'); ?></a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Returns add form html
     */
    private function addFormDisplay()
    {
        ?>
        <div id="wrap">
            <h2><?php _e("Bağış Tipi Ekle", 'dwc-plugin'); ?></h2>
            <form method="post" action="options-general.php?page=dwc_setting_donation_types">
                <table class='wp-list-table widefat fixed'>
                    <tr>
                        <th><?php _e("Benzersiz Ad(Boşluk içermemeli, büyük harflerle yazılmalıdır)", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="name" value=""/></td>
                    </tr>
                    <tr>
                        <th><?php _e("İsim", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="label" value=""/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Varsayılan Değer(Ondalık olarak nokta (.) kullanın)", 'dwc-plugin'); ?></th>
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

    /**
     * Returns default listing page html
     */
    private function dataListDisplay()
    {
        $result = $this->getAll();
        wp_enqueue_style('datatables-css');
        wp_enqueue_script('datatables-js');
        ?>

        <h2><?php _e("Bağış Tipleri", 'dwc-plugin'); ?></h2>
        <p>
            <a href='options-general.php?page=dwc_setting_donation_types&action=add'><span
                        class='dashicons dashicons-plus-alt'></span> <?php _e("Yeni Bir Bağış Tipi Ekle", 'dwc-plugin'); ?>
            </a>
        </p>
        <table id="dt" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>#</th>
                <th><?php _e("Benzersiz İsim", 'dwc-plugin'); ?></th>
                <th><?php _e("İsim", 'dwc-plugin'); ?></th>
                <th><?php _e("Varsayılan Değer", 'dwc-plugin'); ?></th>
                <th><?php _e("İşlemler", 'dwc-plugin'); ?></th>
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


        <div id="wrap">
            <h2><?php _e("KuveytTürk Sanal Pos Ayarları", 'dwc-plugin'); ?></h2>
            <form method="post" action="options-general.php?page=dwc_setting_donation_types">
                <table class='wp-list-table widefat fixed'>
                    <tr>
                        <th><?php _e("Müşteri Numarası(CustomerId)", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="vposCustomerId"
                                   value="<?php echo get_option(DWC_OPTION_NAME_VPOS_CUSTOMER_ID); ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Mağaza Kodu(MerchantId)", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="vposMerchantId"
                                   value="<?php echo get_option(DWC_OPTION_NAME_VPOS_MERCHANT_ID); ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Kullanıcı Adı(Username)", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="vposUsername"
                                   value="<?php echo get_option(DWC_OPTION_NAME_VPOS_USERNAME); ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("Şifre(Password)", 'dwc-plugin'); ?></th>
                        <td><input type="pasword" name="vposPassword"
                                   value="<?php echo get_option(DWC_OPTION_NAME_VPOS_PASSWORD); ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("SanalPos 3D Model Ödeme Noktası Adresi", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="vposValidationUrl"
                                   value="<?php echo get_option(DWC_OPTION_NAME_VPOS_CARDVALIDATIONURL); ?>"/></td>
                    </tr>
                    <tr>
                        <th><?php _e("SanalPos 3D Model Ödeme Onaylama Adresi", 'dwc-plugin'); ?></th>
                        <td><input type="text" name="vposProvisionUrl"
                                   value="<?php echo get_option(DWC_OPTION_NAME_VPOS_CARPROVISIONURL); ?>"/></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="action" value="saveVposSettings"/>
                            <input type='submit' name="add" value='<?php _e("Kaydet", 'dwc-plugin'); ?>' class='button'>
                        </td>
                    </tr>
                </table>
            </form>
        </div>


        <?php
    }
}