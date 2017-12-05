<?php
    
    $this->startSetup();    
    
    $this->addAttribute('catalog_category', 'category_seotext', array(
        'group'             => 'General Information',
        'type'              => 'text',
        'backend'           => '',
        'frontend_input'    => '',
        'frontend'          => '',
        'label'             => 'SEO text',
        'input'             => 'textarea',
        'class'             => '',
        'global'             => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'frontend_class'     => '',
        'required'          => false,
        'wysiwyg_enabled' => true,
        'visible_on_front' => true,
        'is_html_allowed_on_front' => true,
        'user_defined'      => true,
        'position'          => 100,
    ));
    
    $this->endSetup();
