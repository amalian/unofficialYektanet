<?php 
/**
 * به نام خداوند بخشنده و مهربان
 * به نام خدای توسعه دهندگان
 * ساخته شده با عشق توسط مجتبی عملیان
 *
 * Plugin Name: Unofficial Yektanet
 * Plugin URI: https://www.amalian.ir/plugin/unofficialYektanet
 * Description:  Connect WordPress to Yektanet.com
 * Version: 1.0.0
 * Author: Mojtaba Amalian
 * Author URI: https://www.amalian.ir/
 * Text Domain: unofficialYektanet
 * Domain Path: /languages
 *
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * copyright : All rights reserved for Mojtaba Amalian.ir and Yektanet.com.
 */

// don't call the file directly
if (!defined('ABSPATH')) exit;

global $unofficialYektanet;
$unofficialYektanet = new $unofficialYektanet();

define('unofficialYektanetScript', get_option('unofficialYektanetScript'));
define('unofficialYektanetProductBrand', get_option('unofficialYektanetProductBrand'));



class $unofficialYektanet{



    function __construct()
    {
        $this->init();
    }

    function unofficialYektanet_plugin_create_menu()
    {

        add_menu_page('یکتانت' ,'یکتانت' , 'administrator', __FILE__, array($this,'unofficialYektanet_plugin_settings_page'),  plugin_dir_url( __DIR__ ).'unofficialYektanet/assets/img/icon.png');
    }
    function register_yektanet_plugin_settings()
    {
        //register our settings
        register_setting('unofficialYektanetsettings', 'unofficialYektanetScript');
        register_setting('unofficialYektanetsettings', 'unofficialYektanetProductBrand');
    }
    function unofficialYektanet_plugin_settings_page()
    {
    ?>
        <div class="wrap">
            <h1>تنظیمات یکتانت</h1>

            <form method="post" action="options.php">

                <?php settings_fields('unofficialYektanetsettings'); ?>
                <?php do_settings_sections('unofficialYektanetsettings'); ?>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">کد اسکریپت یکتانت</th>
                        <td><textarea type="text" name="unofficialYektanetScript"><?php echo esc_attr(get_option('unofficialYektanetScript')); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">برند محصولات</th>
                        <td><input type="text" name="unofficialYektanetProductBrand" value="<?php echo esc_attr(get_option('unofficialYektanetProductBrand')); ?>"></td>
                    </tr>

                </table>
                <?php submit_button(); ?>
                <h4> ساخته شده با عشق توسط  <a href="https://www.amalian.ir">مجتبی عملیان</a> </h4>
                <h5>تمامی حقوق برای مجتبی عملیان و شرکت یکتانت محفوظ است</h5>
            </form>
        </div>
    <?php
    }



    function unofficialYektanetHeader(){
        echo unofficialYektanetScript;
    }

    private $siteUrl='';
    private $id=0;
    private $product;

    public function setSiteUrl($siteUrl)
    {
         $this->siteUrl=$siteUrl;
    }
    public function setId($id)
    {
         $this->id=$id;
    }
    public function run(){
        $getData=file_get_contents($this->siteUrl.'/wp-json/wc/store/products/'.$this->id);
        $data=json_decode($getData,true);
        $this->product= (object) $data;
    }
    public function get($query)
    {
        return $this->product->$query;
    }
    public function cP2E($string) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $num = range(0, 9);
        $englishNumbersOnly = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace(',', '', $englishNumbersOnly);
        return $englishNumbersOnly;
    }
    function unofficialYektanet_productInfo(){

        $this->setSiteUrl(get_site_url());
        $this->setId(get_the_ID());
        $this->run();

        $on_sale=$this->get('on_sale');

        if ($on_sale==true) {
            $price_html=$this->get('price_html');
            $ex=explode("&nbsp;", $price_html);
            $b4Takhfif=$this->cP2E(str_replace('<span class="matrix_wolfold-price"><span class="woocommerce-Price-amount amount"><bdi>', "", $ex[0]));
            $baTakhfif=$this->get('prices')['sale_price'];

            $price=$baTakhfif;
            $discount=(($b4Takhfif-$baTakhfif)/$b4Takhfif)*100;
        }else{
            $price=$this->get('prices')['price'];
            $discount=0;
        }

        if (is_array($this->get('categories'))) {
            $strCat="";
            foreach ($this->get('categories') as $cateq => $cat) {
                $strCat = $strCat. '["' . $cat['name'] . '"],';
            }
            $category=$strCat;
        }else{
            $category=$this->get('categories');
        }


        ?>
        <script>
            // Powered By Amalian.ir
            // plugin unofficialYektanet : https://www.amalian.ir/plugin/unofficialYektanet
            // YektaNet.com retargeting product detail
            var productInfo = {
                sku: "<?php echo $this->get("id");    ?>",
                title: "<?php echo $this->get("name");    ?>",
                image: '<?php echo $this->get('images')[0]['src'];   ?>',
                category: <?php echo $category;?>
                price: <?php echo $price;?>,
            discount: <?php echo $discount;?>,
            currency: "IRT",
                brand: "<?php echo unofficialYektanetProductBrand;?>",
                isAvailable: true,
            }
            yektanet("product", "detail", productInfo);
        </script>
        <?php
}
    function unofficialYektanet_productPurchase()
    {


        $this->setSiteUrl(get_site_url());
        $this->setId(get_the_ID());
        $this->run();

        $on_sale=$this->get('on_sale');

        if ($on_sale==true) {
            $price_html=$this->get('price_html');
            $ex=explode("&nbsp;", $price_html);
            $b4Takhfif=$this->cP2E(str_replace('<span class="matrix_wolfold-price"><span class="woocommerce-Price-amount amount"><bdi>', "", $ex[0]));
            $baTakhfif=$this->get('prices')['sale_price'];

            $price=$baTakhfif;
            //$discount=(($b4Takhfif-$baTakhfif)/$b4Takhfif)*100;
        }else{
            $price=$this->get('prices')['price'];
           // $discount=0;
        }

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $product_ids = array( $this->get('id') );
        $quantity=1;

        $customer_email = $current_user->email;
        $lolo=wc_customer_bought_product($customer_email, $user_id,$this->get('id'));

        if ($lolo) {
            ?>
            <script>
                // Powered By Amalian.ir
                // plugin unofficialYektanet : https://www.amalian.ir/plugin/unofficialYektanet
                // YektaNet.com retargeting product purchase
                var purchaseInfo = {
                    sku: "<?php echo $sku; ?>",  // شناسه محصول
                    quantity: <?php echo $quantity; ?>,
                    price: <?php echo $price; ?>,       // تومان
                    currency: "IRT",    // IRT for Toman
                    yektanet("product", "purchase", purchaseInfo)
            </script>
            <?php
        }

    }

    function unofficialYektanet_widgets()
    {

        global $wp_meta_boxes;
        wp_add_dashboard_widget('widgets_yektanet', __('یکتانت','unofficialYektanet'),  array($this,'admin_dashboard_widgets_unofficialYektanet') );

    }

    function admin_dashboard_widgets_yektanet()
    {
        echo 'ارتباط با سرور یکتانت برقرار است';
    }


    function init(){
        add_action('admin_menu',array($this, 'unofficialYektanet_plugin_create_menu'));
        add_action('admin_init',array($this, 'register_unofficialYektanet_plugin_settings'));
        add_action('wp_head',array($this, 'unofficialYektanetHeader'));
        add_action('woocommerce_single_product_summary',array($this, 'unofficialYektanet_productInfo' ));
        add_action('woocommerce_single_product_summary',array($this, 'unofficialYektanet_productPurchase'));
        add_action('wp_dashboard_setup',array($this,'unofficialYektanet_widgets'));
    }


}
