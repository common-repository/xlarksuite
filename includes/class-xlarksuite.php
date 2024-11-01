<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://codetay.com
 * @since      1.0.0
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @author     CODETAY <admin@user.vn>
 */
class Xlarksuite
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @var      Xlarksuite_Loader       Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @var      string       The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @var      string       The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('XLARKSUITE_VERSION')) {
            $this->version = XLARKSUITE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'xlarksuite';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->defineLarksuiteHook();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Xlarksuite_Loader. Orchestrates the hooks of the plugin.
     * - Xlarksuite_i18n. Defines internationalization functionality.
     * - Xlarksuite_Admin. Defines all hooks for the admin area.
     * - Xlarksuite_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xlarksuite-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-xlarksuite-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-xlarksuite-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'public/class-xlarksuite-public.php';

        $this->loader = new Xlarksuite_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Xlarksuite_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function set_locale()
    {
        $plugin_i18n = new Xlarksuite_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Xlarksuite_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_public_hooks()
    {
        $plugin_public = new Xlarksuite_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function larksuiteHook($payload, $resource, $resource_id, $id)
    {
        if ($resource !== 'order') {
            return $payload;
        }

        $currentDate = date('d/m/Y H:i:s');
        $orderStatus = strtoupper($payload['status']);
        $orderId = $payload['id'];
        $billingEmail = $payload['billing']['email'];
        $paymentMethodTitle = $payload['payment_method_title'];
        $total = number_format_i18n($payload['total']);

        $productFields = [];
//        $currency = $payload['currency'];
        $currencySymbol = $payload['currency_symbol'];

        $billingName = $payload['billing']['first_name'].' '.$payload['billing']['last_name'];
        $billingPhone = $payload['billing']['phone'];
        $billingCompany = $payload['billing']['company'];
        $billingAddress = $payload['billing']['address_1'].' '.$payload['billing']['address_2'].', '.$payload['billing']['city'].', '.$payload['billing']['state'].' '.$payload['billing']['postcode'].', '.$payload['billing']['country'];
        $billingAddress = preg_replace('/\s+/', ' ', $billingAddress);

        $shippingName = $payload['shipping']['first_name'].' '.$payload['shipping']['last_name'];
        $shippingPhone = $payload['shipping']['phone'];
        $shippingCompany = $payload['shipping']['company'];
        $shippingAddress = $payload['shipping']['address_1'].' '.$payload['shipping']['address_2'].', '.$payload['shipping']['city'].', '.$payload['shipping']['state'].' '.$payload['shipping']['postcode'].', '.$payload['shipping']['country'];
        $shippingAddress = preg_replace('/\s+/', ' ', $shippingAddress);
        $shippingMethod = $payload['shipping_lines'][0]['method_title'];
        $shippingFee = number_format_i18n($payload['shipping_lines'][0]['total']);

        $discountCode = $payload['coupon_lines'][0]['code'];
        $discountAmount = number_format_i18n($payload['coupon_lines'][0]['discount']);
        $discountFields = [];

        if (! empty($discountCode)) {
            $discountFields = [
                [
                    'is_short' => false,
                    'text'     => [
                        'content' => '',
                        'tag'     => 'lark_md',
                    ],
                ],
                [
                    'is_short' => true,
                    'text'     => [
                        'content' => '**Discount**',
                        'tag'     => 'lark_md',
                    ],
                ],
                [
                    'is_short' => true,
                    'text'     => [
                        'content' => "-$discountAmount",
                        'tag'     => 'lark_md',
                    ],
                ],
                [
                    'is_short' => true,
                    'text'     => [
                        'content' => "($discountCode)",
                        'tag'     => 'lark_md',
                    ],
                ],
            ];
        }

        foreach ($payload['line_items'] as $item) {
            $itemName = $item['name'];
            $itemQuantity = $item['quantity'];

            $productFields[] = [
                'is_short' => true,
                'text'     => [
                    'content' => "$itemName x $itemQuantity",
                    'tag'     => 'lark_md',
                ],
            ];

            $totalPerProduct = number_format_i18n($item['subtotal']);

            $productFields[] = [
                'is_short' => true,
                'text'     => [
                    'content' => $totalPerProduct,
                    'tag'     => 'lark_md',
                ],
            ];
        }

        $larksuitePayload = [
            'msg_type' => 'interactive',
            'card'     => [
                'config'        => [
                    'wide_screen_mode' => true,
                ],
                'header'        => [
                    'template' => 'green',
                    'title'    => [
                        'content' => "($currentDate) ORDER $orderStatus",
                        'tag'     => 'plain_text',
                    ],
                ],
                'i18n_elements' => [
                    'en_us' => [
                        [
                            'fields' => [
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "🗳 ORDER NUMBER: **$orderId**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "📅 DATE: **$currentDate**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => false,
                                    'text'     => [
                                        'content' => '',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "📩 EMAIL: **$billingEmail**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "🏧 PAYMENT METHOD: **$paymentMethodTitle**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'tag'  => 'div',
                                    'text' => [
                                        'content' => "-\nBILLING DETAILS",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => false,
                                    'text'     => [
                                        'content' => '',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "👤 **$billingName ($billingCompany)**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "📱**$billingPhone**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "🚕 **$billingAddress**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'tag'  => 'div',
                                    'text' => [
                                        'content' => "-\nSHIPPING DETAILS",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => false,
                                    'text'     => [
                                        'content' => '',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "👤 **$shippingName ($shippingCompany)**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "📱**$shippingPhone**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "🚕 **$shippingAddress**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                            ],
                            'tag'    => 'div',
                        ],
                        [
                            'tag'  => 'div',
                            'text' => [
                                'content' => "-\n**ORDER DETAILS**",
                                'tag'     => 'lark_md',
                            ],
                        ],
                        [
                            'fields' => [
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => '**Product**',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => '**Sub total**',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => false,
                                    'text'     => [
                                        'content' => '',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                ...$productFields,
                                [
                                    'is_short' => false,
                                    'text'     => [
                                        'content' => '',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => '**Shipping**',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => $shippingFee > 0 ? $shippingFee : '',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => $shippingMethod,
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                ...$discountFields,
                                [
                                    'is_short' => false,
                                    'text'     => [
                                        'content' => '',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => '**TOTAL**',
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                                [
                                    'is_short' => true,
                                    'text'     => [
                                        'content' => "**$total $currencySymbol**",
                                        'tag'     => 'lark_md',
                                    ],
                                ],
                            ],
                            'tag'    => 'div',
                        ],
                        [
                            'actions' => [
                                [
                                    'tag'  => 'button',
                                    'text' => [
                                        'content' => 'MANAGE',
                                        'tag'     => 'plain_text',
                                    ],
                                    'type' => 'primary',
                                    'url'  => admin_url("post.php?post=$resource_id&action=edit"),
                                ],
                            ],
                            'tag'     => 'action',
                        ],
                    ],
                ],
            ],
        ];

        $payload = array_merge($payload, $larksuitePayload);

        return $payload;
    }

    private function defineLarksuiteHook()
    {
        add_filter('woocommerce_webhook_deliver_async', '__return_false');
        add_filter('woocommerce_webhook_payload', [$this, 'larksuiteHook'], 10, 4);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Xlarksuite_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
