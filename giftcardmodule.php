<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class GiftCardModule extends Module
{
    public function __construct()
    {
        $this->name = 'giftcardmodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Alan Martinez';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Tarjeta Regalo Módulo');
        $this->description = $this->l('Módulo para Tarjeta Regalo.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->confirmUninstall = $this->l('¿Seguro que quieres desinstalar?');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('actionOrderStatusUpdate') &&
            $this->registerHook('actionEmailSendBefore') &&
            $this->registerHook('displayAdminOrder') &&
            $this->installTab();
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallTab();
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminGiftCard';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Gift Cards';
        }

        return $tab->add();
    }

    private function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminGiftCard');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $order = new Order($params['id_order']);
        $newOrderStatus = $params['newOrderStatus']->id;

        // Solo proceder si el estado es 2
        if ($newOrderStatus != 2) {
            return;
        }

        $products = $order->getProducts();

        foreach ($products as $product) {
            if (in_array($product['product_reference'], ['TARJ-20', 'TARJ-50', 'TARJ-100'])) {
                $amount = (float)str_replace('TARJ-', '', $product['product_reference']);
                $this->generateGiftCard($order, $amount);
            }
        }
    }

    public function hookActionEmailSendBefore($params)
    {
        $template = $params['template'];
        $templateVars = $params['templateVars'];
        $emailTo = $params['to'];

        if ($template === 'gift_card' && isset($templateVars['{gift_card_code}'])) {
            $params['subject'] = $this->l('Tu tarjeta regalo del pedido #') . $templateVars['{order_reference}'];
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order($params['id_order']);
        $products = $order->getProducts();
        $giftCardInfo = '';

        foreach ($products as $product) {
            if (in_array($product['product_reference'], ['TARJ-20', 'TARJ-50', 'TARJ-100'])) {
                $amount = (float)str_replace('TARJ-', '', $product['product_reference']);
                $giftCardInfo .= 'Gift Card: ' . $amount . '€<br>';
            }
        }

        if (!empty($giftCardInfo)) {
            $this->context->smarty->assign([
                'giftCardInfo' => $giftCardInfo
            ]);

            return $this->display(__FILE__, 'views/templates/admin/giftcard_info.tpl');
        }

        return '';
    }

    private function generateGiftCard($order, $amount)
    {
        $cartRule = new CartRule();
        $cartRule->name = array_fill_keys(array_column(Language::getLanguages(), 'id_lang'), 'Tarjeta regalo ' . $amount . '€');
        $cartRule->description = 'Tarjeta regalo para el pedido ' . $order->reference;
        $cartRule->code = 'GIFT-' . Tools::strtoupper(Tools::passwdGen(10));
        $cartRule->quantity = 1;
        $cartRule->quantity_per_user = 1; // Total disponible por usuario: 1
        $cartRule->reduction_amount = $amount;
        $cartRule->reduction_tax = true;
        $cartRule->date_from = date('Y-m-d H:i:s');
        $cartRule->date_to = date('Y-m-d H:i:s', strtotime('+1 year'));
        $cartRule->active = 1;
        $cartRule->id_customer = 0; // No limitado a un solo cliente
        $cartRule->minimum_amount = 0;
        $cartRule->partial_use = false;
        $cartRule->priority = 1;

        if ($cartRule->add()) {
            $this->sendEmail($order, $cartRule->code);
        }
    }

    private function sendEmail($order, $giftCardCode)
    {
        $customer = new Customer($order->id_customer);
        $dateTo = date('Y-m-d', strtotime('+1 year'));
        $templateVars = [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{gift_card_code}' => $giftCardCode,
            '{gift_card_amount}' => number_format($order->total_paid, 2, ',', '.'),
            '{date_to}' => $dateTo,
            '{order_reference}' => $order->reference,
        ];

        // Determinar el texto del asunto y el texto del correo según el ID de la tienda
        $storeText = '';
        $templateFile = '';

        if ($order->id_shop == 2) {
            $storeText = 'Home Heavenly';
            $templateFile = 'homeheavenly';
        } elseif ($order->id_shop == 4) {
            $storeText = 'Don Tresillo';
            $templateFile = 'dontresillo';
        }

        $subjectCustomer = $this->l('Tu tarjeta regalo del pedido #') . $order->reference;
        $subjectReserve = $this->l('Tarjeta regalo del pedido #') . $order->reference;

        // Añadir el texto de la tienda a las variables de plantilla
        $templateVars['{store_text}'] = $storeText;

        // Enviar correo al cliente
        $mail_sent_to_customer = Mail::Send(
            (int)$order->id_lang,
            $templateFile,
            $subjectCustomer,
            $templateVars,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . $this->name . '/mails/es/',
            false,
            (int)$order->id_shop
        );

        // Enviar correo a la dirección de resguardo
        $mail_sent_to_reserve = Mail::Send(
            (int)$order->id_lang,
            $templateFile,
            $subjectReserve,
            $templateVars,
            'tarjetaregalo@homeheavenly.com',
            'Home Heavenly',
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . $this->name . '/mails/es/',
            false,
            (int)$order->id_shop
        );
    }
}
