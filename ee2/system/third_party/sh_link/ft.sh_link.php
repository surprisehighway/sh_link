<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include config
require PATH_THIRD.'sh_link/config.php';

class Sh_link_ft extends EE_Fieldtype {

    public $info = array(
        'name'      => SH_LINK_NAME,
        'version'   => SH_LINK_VERSION
    );
    
    // Allow use as tag pair
    public $has_array_data = TRUE;

    // Set by Low Variables
    public $var_id;
    
    // Available link types
    public $link_types = array('custom', 'email', 'file');

    // Default field settings
    private $default_settings = array(
        'sh_link_allow_email'      => 'y',
        'sh_link_allow_custom'     => 'y',
        'sh_link_allow_page'       => '',
        'sh_link_allow_file'       => '',
        'sh_link_allow_new_window' => ''
    );

    private static $_PAGES_INSTALLED = null;


    // --------------------------------------------------------------------
    

    /**
     * Constructor
     */
    function __construct()
    {
        ee()->lang->loadfile('sh_link');
        parent::__construct();

        if(is_null(self::$_PAGES_INSTALLED))
        {
            self::$_PAGES_INSTALLED = $this->_pages_installed();
        }

        if(self::$_PAGES_INSTALLED)
        {
            $this->link_types[] = 'page';
        }
    }

    /**
     * Register Content Types
     */
    public function accepts_content_type($name)
    {
        return in_array($name, array('channel', 'grid', 'low_variables'));
    }


    // --------------------------------------------------------------------
    

    /**
     * Display Field Settings
     */
    function display_settings($settings)
    {
        $setting_fields = $this->_settings_fields($settings);

        foreach($setting_fields as $row)
        {
            ee()->table->add_row($row);
        }
    }

    /**
     * Display Grid Cell Settings
     */
    function grid_display_settings($settings)
    {
        $setting_fields = $this->_settings_fields($settings);

        $grid_settings = array();

        foreach($setting_fields as $row)
        {
            $grid_settings[] = $this->grid_settings_row(
                $row[0], 
                $this->grid_full_cell_container($row[1])
            );
        }

        return $grid_settings;
    }

    /**
     * Display Matrix Cell Settings
     */
    function display_cell_settings($settings)
    {
        $setting_fields = $this->_settings_fields($settings);

        return $setting_fields;
    }

    /**
     * Display Low Variable Settings
     */
    function display_var_settings($settings)
    {
        $namespace = 'variable_settings[sh_link]';
        $setting_fields = $this->_settings_fields($settings, $namespace);

        return $setting_fields;
    }

    /**
     * Assemble Settings Display Fields
     */
    function _settings_fields($settings, $namespace = FALSE)
    {
        // Make sure we have all settings
        foreach($this->default_settings as $key => $val)
        {
            if(!isset($settings[$key]))
            {
                $settings[$key] = $val;
            }
        }

        // Allowed settings
        $allow = $this->_get_allowed_settings($settings);

        $setting_fields = array();

        // New window setting
        $name = ($namespace) ? $namespace.'[sh_link_allow_new_window]' : 'sh_link_allow_new_window';
        $setting_fields[] = array(
            lang('allow_new_window'),
            form_label(form_checkbox($name, 'y', $allow['new_window']).' Yes', null)
        );

        // Allowed type settings
        $options = '';
        foreach($this->link_types as $type)
        {
            $name = ($namespace) ? $namespace.'[sh_link_allow_'.$type.']' : 'sh_link_allow_'.$type;
            $options .= form_label(form_checkbox($name, 'y', $allow[$type]).' '.lang('type_'.$type), null).'<br>';
        }

        $setting_fields[] = array(
            lang('allowed_types'),
            $options
        );

        return $setting_fields;
    }
    

    // --------------------------------------------------------------------


    /**
     * Save Field Settings
     */
    function save_settings($settings)
    {
        return array(
            'sh_link_allow_email'      => empty($settings['sh_link_allow_email']) ? '' : 'y',
            'sh_link_allow_custom'     => empty($settings['sh_link_allow_custom']) ? '' : 'y',
            'sh_link_allow_page'       => empty($settings['sh_link_allow_page']) ? '' : 'y',
            'sh_link_allow_file'       => empty($settings['sh_link_allow_file']) ? '' : 'y',
            'sh_link_allow_new_window' => empty($settings['sh_link_allow_new_window']) ? '' : 'y',
        );
    }

    /**
     * Save Matrix Cell Settings
     */
    function save_cell_settings($settings)
    {
        return $this->save_settings($settings);
    }

    /**
     * Save Low Variable Settings
     */
    function save_var_settings($settings)
    {
        return $this->save_settings($settings);
    }

    /**
     * Parse allowed settings
     */
    function _get_allowed_settings($settings)
    {
        return array(
            'custom'      => isset($settings['sh_link_allow_custom']) && $settings['sh_link_allow_custom'] == 'y',
            'email'       => isset($settings['sh_link_allow_email']) && $settings['sh_link_allow_email'] == 'y',
            'page'        => isset($settings['sh_link_allow_page']) && $settings['sh_link_allow_page'] == 'y',
            'file'        => isset($settings['sh_link_allow_file']) && $settings['sh_link_allow_file'] == 'y',
            'new_window'  => isset($settings['sh_link_allow_new_window']) && $settings['sh_link_allow_new_window'] == 'y'
        );
    }

    // --------------------------------------------------------------------

    /**
     * Display Field on Publish
     */
    function display_field($data)
    {       
        // Submitted page with error
        if(is_array($data))
        {
            $data = $this->save($data);
        }

        // Data from db
        $data = json_decode(html_entity_decode($data));

        // Matrix check
        $field_name = isset($this->cell_name) ? $this->cell_name : $this->field_name;

        // View path
        ee()->load->add_package_path(PATH_THIRD.SH_LINK_PACKAGE);

        // Add CSS and JS
        $this->_load_css('sh_link');
        $this->_load_js('sh_link');

        // Assemble allowable options
        $settings = $this->settings;
        $allow = $this->_get_allowed_settings($settings);

        $options = array('' => lang('choose_type'));
        foreach($this->link_types as $type)
        {   
            if($allow[$type])
            {
               $options[$type] = lang('type_'.$type); 
            }
        }

        // Assemble variables
        $vars = array(
            'field_name' => $field_name,
            'data'       => $data,
            'new_window' => $allow['new_window'],
            'options'    => $options,
            'pages'      => $this->_get_pages()
        );

        // Add File variables
        if($allow['file'])
        {
            // Load file libraries and initialize browser
            ee()->load->library('file_field');
            ee()->load->library('filemanager');
            ee()->file_field->browser();

            // @todo - convert to field settings
            $allowed_dirs = 'all';
            $allowed_type = 'all';

            $vars['filename']  = '';
            $vars['directory'] = '';

            $existing_file = (isset($data->file)) ? $data->file : '';

            if(!empty($existing_file))
            {
                preg_match('/^{filedir_([0-9]+)}(.*)/', $existing_file, $matches);
                $vars['directory']  = $matches[1];
                $vars['filename'] = $matches[2];
            }
            
            $thumb_info = ee()->filemanager->get_thumb($vars['filename'], $vars['directory']);
            $vars['thumb'] = $thumb_info['thumb'];
            $vars['thumb_class'] = $thumb_info['thumb_class'];
        }
        
        // Load view
        return ee()->load->view('index', $vars, TRUE);
    }

    /**
     * Display Matrix Cell
     */
    function display_cell($data)
    {
        $this->_load_js('sh_link_matrix');
        return $this->display_field($data);
    }

    /**
     * Display Grid Cell
     */
    function grid_display_field($data)
    {
        $this->_load_js('sh_link_grid');
        return $this->display_field($data);
    }

    /**
     * Display Low Variable
     */
    function display_var_field($data)
    {
        return $this->display_field($data);
    }


    // --------------------------------------------------------------------
    

    /**
     * Save Field
     */
    function save($data)
    {
        if(isset($data['type']))
        {
            $type = $data['type'];
            $entry_data = isset($data[$type]) ? $data[$type] : '';
            $new_window = (isset($data['new_window'] && $data['new_window'] == 'y') ? 'y' : '';
            $field_name = isset($this->cell_name) ? $this->cell_name : $this->field_name;

            if($type == 'file')
            {
                ee()->load->library('file_field');

                $field_name = 'sh_link_file_'.$field_name;
                $filename  = (isset($data['file']['filename'])) ? $data['file']['filename'] : '';
                $directory = (isset($data['file']['directory'])) ? $data['file']['directory'] : '';

                $entry_data = '';

                if(!empty($filename) && !empty($directory))
                {
                    $entry_data = ee()->file_field->format_data($filename, $directory);
                }
            }

            if(empty($entry_data)) 
            {
                $data = '';
            }
            else
            {
                $data = json_encode(array('type' => $type, $type => $entry_data, 'new_window' => $new_window));
            }
        }

        return $data;
    }

    /**
     * Save Matrix Cell
     */
    function save_cell($data)
    {
        return $this->save($data);
    }

    /**
     * Save Low Variable
     */
    function save_var_field($data)
    {
        return $this->save($data);
    }


    // --------------------------------------------------------------------

    
    /**
     * Pre-process field data before replace_tag().
     *
     * Parses {filedir_x}filename.txt tags.
     */
    function pre_process($data)
    {
        // Decode field data
        $data_decoded = json_decode(html_entity_decode($data));

        // File
        if(isset($data_decoded->file))
        {
            ee()->load->library('file_field');
            $data_decoded->file_vars = ee()->file_field->parse_field($data_decoded->file);
            $data = json_encode($data_decoded);
        }
        
        return $data;
    }

    /**
     * Parse tag in template
     */
    function replace_tag($data, $params = array(), $tagdata = FALSE)
    {
        if($data == '') return;

        // Decode field data
        $data = json_decode(html_entity_decode($data));
        if(empty($data->type)) return;

        // Parse tags
        if($tagdata)
        {
            // Tag pair, replace vars
            $vars = array(
                'link_new_window' => (isset($data->new_window) && $data->new_window == 'y') ? 'y' : '',
                'link_type'       => $data->type,
                'link_url'        => $this->_get_link($data)
            );

            // Page vars
            if($data->type == 'page' && !empty($data->page))
            {
                $pages = $this->_get_pages();

                if(isset($pages['uris'][$data->page]))
                {
                    $vars['link_entry_id'] = $data->page;
                    $vars['link_page_uri'] = $pages['uris'][$data->page];
                    $vars['link_page_title'] = $pages['titles'][$data->page];
                }
            }

            // File vars
            if($data->type == 'file' && !empty($data->file) && !empty($data->file_vars))
            {
                foreach($data->file_vars as $key => $val)
                {
                    $vars['link_'.$key] = $data->file_vars->$key;
                }
            }

            return ee()->TMPL->parse_variables_row($tagdata, $vars);
        }
        else
        {
            // Single tag, just return the url
            return $this->_get_link($data);
        }
    }

    /**
     * Parse {my_field:type} tag in template
     */
    function replace_type($data, $params = array(), $tagdata = FALSE)
    {
        $data = json_decode(html_entity_decode($data));
        return isset($data->type) ? $data->type : '';
    }

    /**
     * Parse {my_field:url} tag in template
     */
    function replace_url($data, $params = array(), $tagdata = FALSE)
    {
        $data = json_decode(html_entity_decode($data));
        return $this->_get_link($data);
    }

    /**
     * Parse {my_field:new_window} tag in template
     */
    function replace_new_window($data, $params = array(), $tagdata = FALSE)
    {
        $data = json_decode(html_entity_decode($data));
        return isset($data->new_window) ? 'y' : '';
    }

    /**
     * Parse the tag for Low Variables
     */
    function display_var_tag($data, $params = array(), $tagdata = FALSE)
    {
        $data = $this->pre_process($data);
        return $this->replace_tag($data, $params, $tagdata);
    }

    /*
     * Link helper
     */
    private function _get_link($data, $tag = FALSE)
    {
        $link = '';

        $type = $data->type;

        switch($type)
        {
            // Pages module
            case 'page':
                $pages = $this->_get_pages();
                
                if(isset($pages['uris'][$data->$type]))
                {
                    $link = ee()->functions->create_url($pages['uris'][$data->$type]);
                }
                break;

            // File
            case 'file':
                if(!empty($data->file) && !empty($data->file_vars))
                {
                    $link = $data->file_vars->url;
                }
                break;

            // Email
            case 'email':
                $link = 'mailto:'.$data->$type;
                break;

            // Custom URL
            default:
                $link = $data->$type;
                break;
        }

        return $link; 
    }


    // --------------------------------------------------------------------


    /**
     * Check if Pages data exists
     */
    private function _pages_installed()
    {
        $site_id = ee()->config->item('site_id');
        $pages = ee()->config->item('site_pages');

        return !empty($pages[$site_id]['uris']);
    }

    /**
     * Pages helper
     *
     * Store site pages info.
     */
    private function _get_pages()
    {
        if( ! ee()->session->cache(SH_LINK_PACKAGE, 'pages'))
        {
            $site_id = ee()->config->item('site_id');
            $pages = ee()->config->item('site_pages');

            if(!empty($pages[$site_id]['uris']))
            {
                $site_pages = $pages[$site_id];
                $site_pages['titles'] = $this->_get_page_titles(array_keys($site_pages['uris']));

                ee()->session->set_cache(SH_LINK_PACKAGE, 'pages', $site_pages);
            }
            else
            {
                ee()->session->set_cache(SH_LINK_PACKAGE, 'pages', FALSE);
            }
        }

        return ee()->session->cache(SH_LINK_PACKAGE, 'pages');
    }

    /**
     * Pages Title Helper
     */
    private function _get_page_titles($entry_ids)
    {
        $query = ee()->db->select('entry_id, title')
            ->from('channel_titles')
            ->where_in('entry_id', $entry_ids)
            ->get();

        $entry_data = array();
        foreach($query->result() as $entry)
        {
            $entry_data[$entry->entry_id] = $entry->title;
        }
        
        return $entry_data;
    }


    // --------------------------------------------------------------------

    /**
     * Load css
     */
    private function _load_css($filename)
    {
        if( ! ee()->session->cache(SH_LINK_PACKAGE, $filename.'_css_loaded'))
        {
            $path = URL_THIRD_THEMES.SH_LINK_PACKAGE.'/';
            ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$path.'styles/'.$filename.'.css" />');
            ee()->session->set_cache(SH_LINK_PACKAGE, $filename.'_css_loaded', TRUE);
        }
    }

    /**
     * Load javascript
     */
    private function _load_js($filename)
    {
        if( ! ee()->session->cache(SH_LINK_PACKAGE, $filename.'_js_loaded'))
        {
            $path = URL_THIRD_THEMES.SH_LINK_PACKAGE.'/';
            ee()->cp->add_to_foot('<script type="text/javascript" src="'.$path.'scripts/'.$filename.'.js"></script>');
            ee()->session->set_cache(SH_LINK_PACKAGE, $filename.'_js_loaded', TRUE);
        }
    }
}
// END Sh_link_ft class

/* End of file ft.sh_link.php */
/* Location: ./system/expressionengine/third_party/sh_link/ft.sh_link.php */