<?php

class AdminGiftCardController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cart_rule';
        $this->className = 'CartRule';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->context = Context::getContext();
        $this->translator = $this->context->getTranslator();

        $this->fields_list = array(
            'id_cart_rule' => array(
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'width' => 25
            ),
            'name' => array(
                'title' => $this->trans('Name', [], 'Admin.Global'),
                'width' => 'auto',
                'filter_key' => 'a!name'
            ),
            'code' => array(
                'title' => $this->trans('Code', [], 'Admin.Global'),
                'width' => 100
            ),
            'reduction_amount' => array(
                'title' => $this->trans('Amount', [], 'Admin.Global'),
                'type' => 'price',
                'currency' => true
            ),
            'date_to' => array(
                'title' => $this->trans('Valid until', [], 'Admin.Global'),
                'type' => 'date'
            ),
            'active' => array(
                'title' => $this->trans('Status', [], 'Admin.Global'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false
            )
        );

        parent::__construct();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }

    public function initContent()
    {
        $this->content = $this->renderList();
        parent::initContent();
    }
}
