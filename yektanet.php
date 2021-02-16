<?php 
/**
 * به نام خداوند بخشنده و مهربان
 * به نام خدای توسعه دهندگان
 * ساخته شده با عشق توسط مجتبی عملیان
 *
 * Plugin Name: yektanet
 * Plugin URI: https://www.amalian.ir/plugin/yektanet
 * Description:  Connect WordPress to Yektanet.com
 * Version: 1.0.0
 * Author: Mojtaba Amalian
 * Author URI: https://www.amalian.ir/
 * Text Domain: yektanet
 * Domain Path: /languages
 *
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * copyright : All rights reserved for Mojtaba Amalian.ir and Yektanet.com.
 */

// don't call the file directly
if (!defined('ABSPATH')) exit;

global $yektanet;
$yektanet = new yektanet();

define('yektanetScript', get_option('yektanetScript'));
define('yektanetProdunctBrand', get_option('yektanetProdunctBrand'));



class yektanet{

    public $pluginName=__CLASS__;

    function __construct()
    {
        $this->init();
    }

    function yektanet_plugin_create_menu()
    {

        add_menu_page('یکتانت' ,'یکتانت' , 'administrator', __FILE__, array($this,'yektanet_plugin_settings_page'),  plugin_dir_url( __DIR__ ).'yektanet/assets/img/icon.png');
    }
    function register_yektanet_plugin_settings()
    {
        //register our settings
        register_setting('yektanetsettings', 'yektanetScript');
        register_setting('yektanetsettings', 'yektanetProdunctBrand');
    }
    function yektanet_plugin_settings_page()
    {
    ?>
        <div class="wrap">
            <h1>تنظیمات یکتانت</h1>

            <form method="post" action="options.php">

                <?php settings_fields('yektanetsettings'); ?>
                <?php do_settings_sections('yektanetsettings'); ?>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">کد اسکریپت یکتانت</th>
                        <td><textarea type="text" name="yektanetScript"><?php echo esc_attr(get_option('yektanetScript')); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">برند محصولات</th>
                        <td><input type="text" name="yektanetProdunctBrand" value="<?php echo esc_attr(get_option('yektanetProdunctBrand')); ?>"></td>
                    </tr>

                </table>
                <?php submit_button(); ?>
                <h4> ساخته شده با عشق توسط  <a href="https://www.amalian.ir">مجتبی عملیان</a> </h4>
                <h5>تمامی حقوق برای مجتبی عملیان و شرکت یکتانت محفوظ است</h5>
            </form>
        </div>
    <?php
    }



    function yektanetHeader(){
        echo yektanetScript;
    }

    function siteUrl(){
        $siteUrl=get_site_url().'';
        return $siteUrl;
    }
    function jsonApiServer(){

        $server=$this->siteUrl().'/wp-json';
        return $server;

    }

    function cP2E($string) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $num = range(0, 9);
        $englishNumbersOnly = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace(',', '', $englishNumbersOnly);
        return $englishNumbersOnly;
    }

    function yektanet_productInfo()
    {

        $server=$this->jsonApiServer()."/wc/store/products/";

        $sku=get_the_ID();

        $productServer=$server.$sku;

        $serback=file_get_contents($productServer);

        $product=json_decode($serback,true);

        $sku=$product['id'];

        $name=$product['name'];

        $images=$product['images'][0]['src'];

        $brand=yektanetProdunctBrand;

        $on_sale=$product['on_sale'];

        if ($on_sale==true) {
            $price_html=$product['price_html'];
            $ex=explode("&nbsp;", $price_html);
            $b4Takhfif=cP2E(str_replace('<span class="matrix_wolfold-price"><span class="woocommerce-Price-amount amount"><bdi>', "", $ex[0]));
            $baTakhfif=cP2E(str_replace('<span class="woocommerce-Price-currencySymbol">تومان</span></bdi></span></span> <span class="matrix_wolffinal-price"><span class="woocommerce-Price-amount amount"><bdi>', "", $ex[1]));

            $price=$baTakhfif;
            $discount=(($b4Takhfif-$baTakhfif)/$b4Takhfif)*100;
        }else{
             $price=$product['prices']['price'];
             $discount=0;
        }


        $category='';
        foreach($product['categories'] as $n=>$cat){
            $category=$category.'["'.$cat['name'].'"],';
        }

        ?>
        <script>
        // Powered By Amalian.ir
        // plugin YektaNet : https://www.amalian.ir/plugin/yektanet
        // YektaNet.com retargeting product detail
        var productInfo = {
            sku: "<?php echo $sku; ?>",
            title: "<?php echo $name;?>",
            image: '<?php echo $images;?>',
            category: <?php echo $category;?>
            price: <?php echo $price;?>,
            discount: <?php echo $discount;?>,
            currency: "IRT",
            brand: "<?php echo $brand;?>",
            isAvailable: true,
        }
        yektanet("product", "detail", productInfo);
        </script>
        <?php

    }




    function yektanet_productPurchase()
    {

        $server=$this->jsonApiServer()."/wc/store/products/";

        $sku=get_the_ID();

        $productServer=$server.$sku;

        $serback=file_get_contents($productServer);

        $product=json_decode($serback,true);

        $sku=$product['id'];

        $on_sale=$product['on_sale'];

        if ($on_sale==true) {
            $price_html=$product['price_html'];
            $ex=explode("&nbsp;", $price_html);
            $b4Takhfif=cP2E(str_replace('<span class="matrix_wolfold-price"><span class="woocommerce-Price-amount amount"><bdi>', "", $ex[0]));
            $baTakhfif=cP2E(str_replace('<span class="woocommerce-Price-currencySymbol">تومان</span></bdi></span></span> <span class="matrix_wolffinal-price"><span class="woocommerce-Price-amount amount"><bdi>', "", $ex[1]));

            $price=$baTakhfif;
        }else{
            $price=$product['prices']['price'];
        }


        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $product_ids = array( $sku );
        $quantity=1;

        $customer_email = $current_user->email;
        $lolo=wc_customer_bought_product($customer_email, $user_id,$sku);

        if ($lolo) {
            ?>
        <script>
        // Powered By Amalian.ir
        // plugin YektaNet : https://www.amalian.ir/plugin/yektanet
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

    function yektanet_widgets()
    {

        global $wp_meta_boxes;
        wp_add_dashboard_widget('widgets_yektanet', __('یکتانت','yektanet'),  array($this,'admin_dashboard_widgets_yektanet') );

    }

    function admin_dashboard_widgets_yektanet()
    {
        echo 'ارتباط با سرور یکتانت برقرار است';
    }


    function init(){
        add_action('admin_menu',array($this, 'yektanet_plugin_create_menu'));
        add_action('admin_init',array($this, 'register_yektanet_plugin_settings'));
        add_action('wp_head',array($this, 'yektanetHeader'));
        add_action('woocommerce_single_product_summary',array($this, 'yektanet_productInfo' ));
        add_action('woocommerce_single_product_summary',array($this, 'yektanet_productPurchase'));
        add_action('wp_dashboard_setup',array($this,'yektanet_widgets'));
    }


}
