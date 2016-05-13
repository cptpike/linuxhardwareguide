<?php
/**
 * This file is part of the Comment Attachment plugin.
 *
 * Copyright (c) 2013 Martin Pícha (http://latorante.name)
 *
 * For the full copyright and license information, please view
 * the comment-attachment.php file in root directory of this plugin.
 */

if (!defined('ABSPATH')) { exit; }

if (!class_exists('wpCommentAttachment')){
    class wpCommentAttachment
    {
        /* admin settings */
        private $adminPage      = 'discussion';
        private $adminCheckboxes;
        private $adminPrefix    = 'commentAttachment';
        private $key            = 'commentAttachment';
        private $settings;


        public function __construct()
        {
            //error_reporting(1);
            if(!get_option($this->key)){ $this->initializeSettings(); }
            $this->settings = $this->getSavedSettings();
            $this->defineConstants();
            add_action('plugins_loaded', array($this, 'loaded'));
            add_action('init', array($this, 'init'));
            add_action('admin_init', array($this, 'adminInit'));
        }


        /******************* Inits, innit :D *******************/

        /**
         * Loaded, check request
         */

        public function loaded()
        {
            // check to delete att
            if(isset($_GET['deleteAtt']) && ($_GET['deleteAtt'] == '1')){
                if((isset($_GET['c'])) && is_numeric($_GET['c'])){
                    wpCommentAttachment::deleteAttachment($_GET['c']);
                    delete_comment_meta($_GET['c'], 'attachmentId');
                    add_action('admin_notices', function(){
                        echo "<div class='updated'><p>Comment Attachment deleted.</p></div>";
                    });
                }
            }
        }

        /**
         * Classic init
         */

        public function init()
        {
            if(!$this->checkRequirements()){ return; }
            add_filter('preprocess_comment',        array($this, 'checkAttachment'));
            add_action('comment_form_top',          array($this, 'displayBeforeForm'));
            add_action('comment_form_before_fields',array($this, 'displayFormAttBefore'));
            add_action('comment_form_after_fields', array($this, 'displayFormAttAfter'));
            add_action('comment_form_logged_in_after',array($this, 'displayFormAtt'));
            add_filter('comment_text',              array($this, 'displayAttachment'));
            add_action('comment_post',              array($this, 'saveAttachment'));
            add_action('delete_comment',            array($this, 'deleteAttachment'));
            add_filter('upload_mimes',              array($this, 'getAllowedUploadMimes'));
            add_filter('comment_notification_text', array($this, 'notificationText'), 10, 2);
        }


        /**
         * Admin init
         */

        public function adminInit()
        {
            $this->setUserNag();
            add_filter('plugin_action_links', array($this, 'displayPluginActionLink'), 10, 2);
            add_filter('comment_row_actions', array($this, 'addCommentActionLinks'), 10, 2);
            register_setting($this->adminPage, $this->key, array($this, 'validateSettings'));
            add_settings_section($this->adminPrefix,           'Comment Attachment', '', $this->adminPage);
            add_settings_section($this->adminPrefix . 'Types', 'Allowed File Types', '', $this->adminPage);
            foreach ($this->getSettings() as $id => $setting){
                $setting['id'] = $id;
                $this->createSetting($setting);
            }
        }


        /*************** Plugins admin settings ****************/

        /**
         * Get's admin settings page variables
         *
         * @return mixed
         */

        public function getSettings() {
            $setts[$this->adminPrefix . 'Position'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Display attachment field',
                'desc'    => '',
                'type'    => 'select',
                'std'     => '',
                'choices' => array(
                    'before' => 'Before default comment form fields.',
                    'after' => 'After default comment form fields.')
            );
            $setts[$this->adminPrefix . 'Title'] = array(
                'title'   => 'Attachment field title',
                'desc'    => '',
                'std'     => 'Upload Attachment',
                'type'    => 'text',
                'section' => $this->adminPrefix
            );
            $setts[$this->adminPrefix . 'MaxSize'] = array(
                'title'   => 'Maxium file size <small>(in megabytes)</small>',
                'desc'    => 'Your server currently allows us to use maximum of <strong>' . $this->getMaximumUploadFileSize() . 'MB(s).</strong>',
                'std'     => $this->getMaximumUploadFileSize(),
                'type'    => 'number',
                'section' => $this->adminPrefix
            );
            $setts[$this->adminPrefix . 'Required'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Is attachment required?',
                'desc'    => '',
                'type'    => 'checkbox',
                'std'     => 0
            );
            $setts[$this->adminPrefix . 'Bind'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Attach attachment with current post?',
                'desc'    => '',
                'type'    => 'checkbox',
                'std'     => 1
            );
            $setts[$this->adminPrefix . 'ThumbTitle'] = array(
                'title'   => 'Text before attachment in a commment',
                'desc'    => '',
                'std'     => 'Attachment:',
                'type'    => 'text',
                'section' => $this->adminPrefix
            );
            $setts[$this->adminPrefix . 'APosition'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Position of attchment in comment text',
                'desc'    => '',
                'type'    => 'select',
                'std'     => '',
                'choices' => array(
                    'before' => 'Before comment.',
                    'after' => 'After comment.',
                    'none' => 'Don\'t display attchment. (really?)')
            );
            $setts[$this->adminPrefix . 'Link'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Make attchment in comment a link?',
                'desc'    => '(Link\'s to the original file.)',
                'type'    => 'checkbox',
                'std'     => 0
            );
            $setts[$this->adminPrefix . 'Thumb'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Show image thumbnail?',
                'desc'    => '(if attachment is image)',
                'type'    => 'checkbox',
                'std'     => 1
            );
            $setts[$this->adminPrefix . 'ThumbSize'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Image attachment size in comment',
                'desc'    => '(if thumbnail is set to visible, and is image)',
                'type'    => 'select',
                'std'     => '',
                'choices' => $this->getRegisteredImageSizes()
            );
            $setts[$this->adminPrefix . 'Player'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Try to embed audio/video player?',
                'desc'    => '(if attachment is audio/video)<br />
                            <strong style="color: red;">NOTE: </strong>This is an experimental feature, assuming you are using <strong>Wordpress 3.6, and higher,</strong> it uses the wordpress native <br />
                            <code>[video]</code> and <code>[audio]</code> shortcodes to attach media file. It only takes <strong>.mp4, .m4v, .webm, .ogv, .wmv, .flv</strong> for videos,<br />
                            and <strong>.mp3, .m4a, .ogg, .wav, .wma</strong> for audio files. Read more about <a href="http://codex.wordpress.org/Video_Shortcode" target="_blank">[video] shortcode</a>, or read more about <a href="https://codex.wordpress.org/Audio_Shortcode" target="_blank">[audio] shortcode.</a>',
                'type'    => 'checkbox',
                'std'     => 1
            );
            $setts[$this->adminPrefix . 'Delete'] = array(
                'section' => $this->adminPrefix,
                'title'   => 'Delete attachment upon comment deletition?<br />',
                'desc'    => '',
                'type'    => 'checkbox',
                'std'     => 1
            );
            $setts[$this->adminPrefix . 'H01']  = array('section' => $this->adminPrefix . 'Types', 'title' => '<strong>Images</strong>', 'type' => 'heading');
            $setts[$this->adminPrefix . 'JPG']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'JPG', 'type' => 'checkbox', 'std' => 1);
            $setts[$this->adminPrefix . 'GIF']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'GIF', 'type' => 'checkbox', 'std' => 1);
            $setts[$this->adminPrefix . 'PNG']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'PNG', 'type' => 'checkbox', 'std' => 1);
            $setts[$this->adminPrefix . 'H02']  = array('section' => $this->adminPrefix . 'Types', 'title' => '<strong>Documents</strong>', 'type' => 'heading');
            $setts[$this->adminPrefix . 'TXT']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'TXT', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'PDF']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'PDF', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'DOC']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'DOC', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'DOCX'] = array('section' => $this->adminPrefix . 'Types', 'title' => 'DOCX', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'PPT']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'PPT', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'PPTX'] = array('section' => $this->adminPrefix . 'Types', 'title' => 'PPTX', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'PPS']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'PPS', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'PPSX'] = array('section' => $this->adminPrefix . 'Types', 'title' => 'PPSX', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'ODT']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'ODT', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'XLS']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'XLS', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'XLSX'] = array('section' => $this->adminPrefix . 'Types', 'title' => 'XLSX', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'H03']  = array('section' => $this->adminPrefix . 'Types', 'title' => '<strong>Archives</strong>', 'type' => 'heading');
            $setts[$this->adminPrefix . 'RAR']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'RAR', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'ZIP']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'ZIP', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'H04']  = array('section' => $this->adminPrefix . 'Types', 'title' => '<strong>Audio</strong>', 'type' => 'heading');
            $setts[$this->adminPrefix . 'MP3']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'MP3', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'M4A']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'M4A', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'OGG']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'OGG', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'WAV']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'WAV', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'WMA']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'WMA', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'H05']  = array('section' => $this->adminPrefix . 'Types', 'title' => '<strong>Video</strong>', 'type' => 'heading');
            $setts[$this->adminPrefix . 'MP4']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'MP4', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'M4V']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'M4V', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'MOV']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'MOV', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'WMV']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'WMV', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'AVI']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'AVI', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'MPG']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'MPG', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'OGV']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'OGV', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . '3GP']  = array('section' => $this->adminPrefix . 'Types', 'title' => '3GP', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . '3G2']  = array('section' => $this->adminPrefix . 'Types', 'title' => '3G2', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'FLV']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'FLV', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'WEBM'] = array('section' => $this->adminPrefix . 'Types', 'title' => 'WEBM ', 'type' => 'checkbox', 'std' => 0);
            $setts[$this->adminPrefix . 'H06']  = array('section' => $this->adminPrefix . 'Types', 'title' => '<strong>Others</strong>', 'type' => 'heading');
            $setts[$this->adminPrefix . 'APK']  = array('section' => $this->adminPrefix . 'Types', 'title' => 'APK ', 'type' => 'checkbox', 'std' => 0);
            return $setts;
        }


        /********* Let's do this, plugin functionality *********/

        /**
         * Does what it says
         *
         * @return object
         */

        private function getSavedSettings(){ return get_option($this->key); }


        /**
         * Returns maximum upload file size
         *
         * @return mixed
         */

        public static function getMaximumUploadFileSize()
        {
            $maxUpload      = (int)(ini_get('upload_max_filesize'));
            $maxPost        = (int)(ini_get('post_max_size'));
            $memoryLimit    = (int)(ini_get('memory_limit'));
            return min($maxUpload, $maxPost, $memoryLimit);
        }


        /**
         * Define plugin constatns
         */

        private function defineConstants()
        {
            define('ATT_REQ',   ($this->settings[$this->adminPrefix . 'Required'] == '1' ? TRUE : FALSE));
            define('ATT_BIND',  ($this->settings[$this->adminPrefix . 'Bind'] == '1' ? TRUE : FALSE));
            define('ATT_DEL',   ($this->settings[$this->adminPrefix . 'Delete'] == '1' ? TRUE : FALSE));
            define('ATT_LINK',  ($this->settings[$this->adminPrefix . 'Link'] == '1' ? TRUE : FALSE));
            define('ATT_THUMB', ($this->settings[$this->adminPrefix . 'Thumb'] == '1' ? TRUE : FALSE));
            define('ATT_PLAY',  ($this->settings[$this->adminPrefix . 'Player'] == '1' ? TRUE : FALSE));
            define('ATT_POS',   ($this->settings[$this->adminPrefix . 'Position']));
            define('ATT_APOS',  ($this->settings[$this->adminPrefix . 'APosition']));
            define('ATT_TITLE', ($this->settings[$this->adminPrefix . 'Title']));
            define('ATT_TSIZE', ($this->settings[$this->adminPrefix . 'ThumbSize']));
            define('ATT_MAX',   ($this->settings[$this->adminPrefix . 'MaxSize']));

        global $ATT_MAX;
        $ATT_MAX = $this->settings[$this->adminPrefix . 'MaxSize'];
        }


        /**
         * For image thumb dropdown.
         *
         * @return mixed
         */

        private function getRegisteredImageSizes()
        {
            foreach(get_intermediate_image_sizes() as $size){
                $arr[$size] = ucfirst($size);
            };
            return $arr;
        }


        /**
         * If there's a place to set up those mime types,
         * it's here.
         *
         * @return array
         */

        private function getMimeTypes()
        {
            return array(
                $this->adminPrefix . 'JPG' => array(
                                        'image/jpeg',
                                        'image/jpg',
                                        'image/jp_',
                                        'application/jpg',
                                        'application/x-jpg',
                                        'image/pjpeg',
                                        'image/pipeg',
                                        'image/vnd.swiftview-jpeg',
                                        'image/x-xbitmap'),
                $this->adminPrefix . 'GIF' => array(
                                        'image/gif',
                                        'image/x-xbitmap',
                                        'image/gi_'),
                $this->adminPrefix . 'PNG' => array(
                                        'image/png',
                                        'application/png',
                                        'application/x-png'),
                $this->adminPrefix . 'DOCX'=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $this->adminPrefix . 'RAR'=> 'application/x-rar-compressed',
                $this->adminPrefix . 'ZIP' => array(
                                        'application/zip',
                                        'application/x-zip',
                                        'application/x-zip-compressed',
                                        'application/x-compress',
                                        'application/x-compressed',
                                        'multipart/x-zip'),
                $this->adminPrefix . 'DOC' => array(
                                        'application/msword',
                                        'application/doc',
                                        'application/text',
                                        'application/vnd.msword',
                                        'application/vnd.ms-word',
                                        'application/winword',
                                        'application/word',
                                        'application/x-msw6',
                                        'application/x-msword'),
                $this->adminPrefix . 'PDF' => array(
                                        'application/pdf',
                                        'application/x-pdf',
                                        'application/acrobat',
                                        'applications/vnd.pdf',
                                        'text/pdf',
                                        'text/x-pdf'),
                $this->adminPrefix . 'PPT' => array(
                                        'application/vnd.ms-powerpoint',
                                        'application/mspowerpoint',
                                        'application/ms-powerpoint',
                                        'application/mspowerpnt',
                                        'application/vnd-mspowerpoint',
                                        'application/powerpoint',
                                        'application/x-powerpoint',
                                        'application/x-m'),
                $this->adminPrefix . 'PPTX'=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                $this->adminPrefix . 'PPS' => 'application/vnd.ms-powerpoint',
                $this->adminPrefix . 'PPSX'=> 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                $this->adminPrefix . 'ODT' => array(
                                        'application/vnd.oasis.opendocument.text',
                                        'application/x-vnd.oasis.opendocument.text'),
                $this->adminPrefix . 'XLS' => array(
                                        'application/vnd.ms-excel',
                                        'application/msexcel',
                                        'application/x-msexcel',
                                        'application/x-ms-excel',
                                        'application/vnd.ms-excel',
                                        'application/x-excel',
                                        'application/x-dos_ms_excel',
                                        'application/xls'),
                $this->adminPrefix . 'XLSX'=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                $this->adminPrefix . 'MP3' => array(
                                        'audio/mpeg',
                                        'audio/x-mpeg',
                                        'audio/mp3',
                                        'audio/x-mp3',
                                        'audio/mpeg3',
                                        'audio/x-mpeg3',
                                        'audio/mpg',
                                        'audio/x-mpg',
                                        'audio/x-mpegaudio'),
                $this->adminPrefix . 'M4A' => 'audio/mp4a-latm',
                $this->adminPrefix . 'TXT' => array(
                			'text/plain',
                			'text/x-c++'
                                        ),
                $this->adminPrefix . 'OGG' => array(
                                        'audio/ogg',
                                        'application/ogg'),
                $this->adminPrefix . 'WAV' => array(
                                        'audio/wav',
                                        'audio/x-wav',
                                        'audio/wave',
                                        'audio/x-pn-wav'),
                $this->adminPrefix . 'WMA' => 'audio/x-ms-wma',
                $this->adminPrefix . 'MP4' => array(
                                        'video/mp4v-es',
                                        'audio/mp4'),
                $this->adminPrefix . 'M4V' => array(
                                        'video/mp4',
                                        'video/x-m4v'),
                $this->adminPrefix . 'MOV' => array(
                                        'video/quicktime',
                                        'video/x-quicktime',
                                        'image/mov',
                                        'audio/aiff',
                                        'audio/x-midi',
                                        'audio/x-wav',
                                        'video/avi'),
                $this->adminPrefix . 'WMV' => 'video/x-ms-wmv',
                $this->adminPrefix . 'AVI' => array(
                                        'video/avi',
                                        'video/msvideo',
                                        'video/x-msvideo',
                                        'image/avi',
                                        'video/xmpg2',
                                        'application/x-troff-msvideo',
                                        'audio/aiff',
                                        'audio/avi'),
                $this->adminPrefix . 'MPG' => array(
                                        'video/avi',
                                        'video/mpeg',
                                        'video/mpg',
                                        'video/x-mpg',
                                        'video/mpeg2',
                                        'application/x-pn-mpg',
                                        'video/x-mpeg',
                                        'video/x-mpeg2a',
                                        'audio/mpeg',
                                        'audio/x-mpeg',
                                        'image/mpg'),
                $this->adminPrefix . 'OGV' => 'video/ogg',
                $this->adminPrefix . '3GP' => array(
                                        'audio/3gpp',
                                        'video/3gpp'),
                $this->adminPrefix . '3G2' => array(
                                        'video/3gpp2',
                                        'audio/3gpp2'),
                $this->adminPrefix . 'FLV' => 'video/x-flv',
                $this->adminPrefix . 'WEBM'=> 'video/webm',
                $this->adminPrefix . 'APK' => 'application/vnd.android.package-archive',
            );
        }


        /**
         * Gets allowed file types extensions
         *
         * @return array
         */

        public function getAllowedFileExtensions()
        {
            $return = array();
            $pluginFileTypes = $this->getMimeTypes();
            foreach($this->settings as $key => $value){
                if(array_key_exists($key, $pluginFileTypes)){
                    $return[] = strtolower(str_replace($this->adminPrefix, '', $key));
                }
            }
            return $return;
        }


        /**
         * Gets allowed file types for attachment check.
         *
         * @return array
         */

        public function getAllowedMimeTypes()
        {
            $return = array();
            $pluginFileTypes = $this->getMimeTypes();
            foreach($this->settings as $key => $value){
                if(array_key_exists($key, $pluginFileTypes)){
                    // if we can't check mime type correctly, might as well add these cctet-streams ...
                    // user will see nag about that function being missing.
                    if(!function_exists('finfo_file') || !function_exists('mime_content_type')){
                        if(($key == $this->adminPrefix . 'DOCX') || ($key == $this->adminPrefix . 'DOC') || ($key == $this->adminPrefix . 'PDF') ||
                            ($key == $this->adminPrefix . 'ZIP') || ($key == $this->adminPrefix . 'RAR')){
                            $return[] = 'application/octet-stream';
                        }
                    }
                    if(is_array($pluginFileTypes[$key])){
                        foreach($pluginFileTypes[$key] as $fileType){
                            $return[] = $fileType;
                        }
                    } else {
                        $return[] = $pluginFileTypes[$key];
                    }
                }
            }
            return $return;
        }


        /**
         * This one actually will need explaining, it's hard
         *
         * @param array $existing
         * @return array
         */

        public function getAllowedUploadMimes($existing = array())
        {
            // we get mime types and saved file types
            $return = array();
            $pluginFileTypes = $this->getMimeTypes();
            foreach($this->settings as $key => $value){
                // list thru them and if it's allowed and not in list, we added there,
                // in reality, I'm thinking about removing the wp ones, and all mines,
                // since wordpress mime types are very limited, we can do better guys
                // cuase it sucks, and doesn't have enough mime types, actually let's
                // just do it ...
                if(array_key_exists($key, $pluginFileTypes)){
                    $keyCheck = strtolower(str_replace($this->adminPrefix,'', $key));
                    // here we would have checked, if mime type is already there,
                    // but we want strong list of mime types, so we just add it all.
                    if(is_array($pluginFileTypes[$key])){
                        foreach($pluginFileTypes[$key] as $fileType){
                            $keyHacked = preg_replace("/[^0-9a-zA-Z ]/", "", $fileType);
                            $return[$keyCheck . '|' . $keyCheck . '_' . $keyHacked] = $fileType;
                        }
                    } else {
                        $return[$keyCheck] = $pluginFileTypes[$key];
                    }
                }
            }
            return array_merge($return, $existing);
        }


        /*
         * For error info, and form upload info.
         */

        public function displayAllowedFileTypes()
        {
            $fileTypesString = '';
            foreach($this->getAllowedFileExtensions() as $value){
                $fileTypesString .= $value . ', ';
            }
            return substr($fileTypesString, 0, -2);
        }


        /**
         * For attachment display, get's image mime types
         *
         * @return array
         */

        public function getImageMimeTypes()
        {
            return array(
                'image/jpeg',
                'image/jpg',
                'image/jp_',
                'application/jpg',
                'application/x-jpg',
                'image/pjpeg',
                'image/pipeg',
                'image/vnd.swiftview-jpeg',
                'image/x-xbitmap',
                'image/gif',
                'image/x-xbitmap',
                'image/gi_',
                'image/png',
                'application/png',
                'application/x-png'
            );
        }


        /**
         * For attachment display, get's audio mime types
         *
         * @return array
         */
        // TODO: only check ones audio player can play?

        public function getAudioMimeTypes()
        {
            return array(
                'audio/mpeg',
                'audio/x-mpeg',
                'audio/mp3',
                'audio/x-mp3',
                'audio/mpeg3',
                'audio/x-mpeg3',
                'audio/mpg',
                'audio/x-mpg',
                'audio/x-mpegaudio',
                'audio/mp4a-latm',
                'audio/ogg',
                'application/ogg',
                'audio/wav',
                'audio/x-wav',
                'audio/wave',
                'audio/x-pn-wav',
                'audio/x-ms-wma'
            );
        }


        /**
         * For attachment display, get's audio mime types
         *
         * @return array
         */

        public function getVideoMimeTypes()
        {
            return array(
                'video/mp4v-es',
                'audio/mp4',
                'video/mp4',
                'video/x-m4v',
                'video/quicktime',
                'video/x-quicktime',
                'image/mov',
                'audio/aiff',
                'audio/x-midi',
                'audio/x-wav',
                'video/avi',
                'video/x-ms-wmv',
                'video/avi',
                'video/msvideo',
                'video/x-msvideo',
                'image/avi',
                'video/xmpg2',
                'application/x-troff-msvideo',
                'audio/aiff',
                'audio/avi',
                'video/avi',
                'video/mpeg',
                'video/mpg',
                'video/x-mpg',
                'video/mpeg2',
                'application/x-pn-mpg',
                'video/x-mpeg',
                'video/x-mpeg2a',
                'audio/mpeg',
                'audio/x-mpeg',
                'image/mpg',
                'video/ogg',
                'audio/3gpp',
                'video/3gpp',
                'video/3gpp2',
                'audio/3gpp2',
                'video/x-flv',
                'video/webm',
            );
        }


        /**
         * This way we sort of fake our "enctype" in, since there's not ohter hook
         * that would allow us to put it there naturally, and no, we won't use JS for that
         * since that's rubbish and not bullet-proof. Yes, this creates empty form on page,
         * but who cares, it works and does the trick.
         */

        public function displayBeforeForm()
        {
            // echo '</form><form action="'. get_home_url() .'/wp-comments-post.php" method="POST" enctype="multipart/form-data" id="attachmentForm" class="comment-form" novalidate>';
        }


        /*
         * Display form upload field.
         */

        public function displayFormAttBefore()  { if(ATT_POS == 'before'){ $this->displayFormAtt(); } }
        public function displayFormAttAfter()   { if(ATT_POS == 'after'){ $this->displayFormAtt(); } }
        public function displayFormAtt()
        {
            return;

            $maxsize = $this->settings[$this->adminPrefix . 'MaxSize'];
            $required = ATT_REQ ? ' <span class="required">*</span>' : '';
            echo '<p class="comment-form-url comment-form-attachment">'.
                '<label for="attachment">' . ATT_TITLE . $required .'<small class="attachmentRules">Allowed file types: <strong>'. $this->displayAllowedFileTypes() .'</strong>, maximum file size: <strong>'. ATT_MAX .'MB(s).</strong></small></label>'.
                '</p>'.
                '<p class="comment-form-url comment-form-attachment"><input id="attachment" name="attachment" type="file" /></p>';
        }


        /**
         * Checks attachment, size, and type and throws error if something goes wrong.
         *
         * @param $data
         * @return mixed
         */

        public function checkAttachment($data)
        {
            if($_FILES['attachment']['size'] > 0 && $_FILES['attachment']['error'] == 0){

                $fileInfo = pathinfo($_FILES['attachment']['name']);
                $fileExtension = strtolower($fileInfo['extension']);

                if(function_exists('finfo_file')){
                    $fileType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['attachment']['tmp_name']);
                } elseif(function_exists('mime_content_type')) {
                    $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
                } else {
                    $fileType = $_FILES['attachment']['type'];
                }

                #error_log("FType: $fileType, Ext: $fileExtension, POS: ".strpos($fileType, "text") );

                // Is: allowed mime type / file extension, and size? extension making lowercase, just to make sure
                if ( (strpos($fileType, "text") !== false ) && ( $_FILES['attachment']['size'] < (ATT_MAX * 1048576) ) ) { // file size from admin
	                // LHG allows all file extension for text files. Log outputs can have all kind of names.
                        // we can live with this but have to rename the file

                        if (substr($_FILES['attachment']['name'], -4) != ".txt" )
				$_FILES['attachment']['name'] = $_FILES['attachment']['name'].".txt";
                        #error_log("Fname: ".$_FILES['attachment']['name']);

                } elseif (!in_array($fileType, $this->getAllowedMimeTypes()) || !in_array(strtolower($fileExtension), $this->getAllowedFileExtensions()) || $_FILES['attachment']['size'] > (ATT_MAX * 1048576)) { // file size from admin
                    wp_die('<strong>ERROR:</strong> File you upload must be valid file type <strong>('. $this->displayAllowedFileTypes() .')</strong>, and under '. ATT_MAX .'MB(s)!');
                } 

            // error 4 is actually empty file mate
            } elseif (ATT_REQ && $_FILES['attachment']['error'] == 4) {
                wp_die('<strong>ERROR:</strong> Attachment is a required field!');
            } elseif($_FILES['attachment']['error'] == 1) {
                wp_die('<strong>ERROR:</strong> The uploaded file exceeds the upload_max_filesize directive in php.ini.');
            } elseif($_FILES['attachment']['error'] == 2) {
                wp_die('<strong>ERROR:</strong> The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
            } elseif($_FILES['attachment']['error'] == 3) {
                wp_die('<strong>ERROR:</strong> The uploaded file was only partially uploaded. Please try again later.');
            } elseif($_FILES['attachment']['error'] == 6) {
                wp_die('<strong>ERROR:</strong> Missing a temporary folder.');
            } elseif($_FILES['attachment']['error'] == 7) {
                wp_die('<strong>ERROR:</strong> Failed to write file to disk.');
            } elseif($_FILES['attachment']['error'] == 7) {
                wp_die('<strong>ERROR:</strong> A PHP extension stopped the file upload.');
            }
            return $data;
        }


        /**
         * Notification email message
         *
         * @param $notify_message
         * @param $comment_id
         * @return string
         */

        public function notificationText($notify_message,  $comment_id)
        {
            if(wpCommentAttachment::hasAttachment($comment_id)){
                $attachmentId = get_comment_meta($comment_id, 'attachmentId', TRUE);
                $attachmentName = basename(get_attached_file($attachmentId));
                $notify_message .= 'Attachment:' . "\r\n" .  $attachmentName . "\r\n\r\n";
            }
            return $notify_message;
        }


        /**
         * Inserts file attachment from your comment to wordpress
         * media library, assigned to post.
         *
         * @param $fileHandler
         * @param $postId
         * @return mixed
         */

        public function insertAttachment($fileHandler, $postId)
        {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
            return media_handle_upload($fileHandler, $postId);
        }


        /**
         * Save attachment to db, with all sizes etc. Assigned
         * to post, or not.
         *
         * @param $commentId
         */

        public function saveAttachment($commentId)
        {
            if($_FILES['attachment']['size'] > 0){
                $bindId = ATT_BIND ? $_POST['comment_post_ID'] : 0;
                $attachId = $this->insertAttachment('attachment', $bindId);
                add_comment_meta($commentId, 'attachmentId', $attachId);
                unset($_FILES);
            }
        }


        /**
         * Displays attachment in comment, according to
         * position selected in settings, and according to way selected in admin.
         *
         * @param $comment
         * @return string
         */

        public function displayAttachment($comment)
        {
            $attachmentId = get_comment_meta(get_comment_ID(), 'attachmentId', TRUE);
            if(is_numeric($attachmentId) && !empty($attachmentId)){

                // atachement info
                $attachmentLink = wp_get_attachment_url($attachmentId);
                $attachmentMeta = wp_get_attachment_metadata($attachmentId);
                $attachmentName = basename(get_attached_file($attachmentId));
                $attachmentType = get_post_mime_type($attachmentId);
                $attachmentRel  = '';

                #error_log("ID: $attachmentId");
                #error_log("Link: $attachmentLink - Type: $attachmentType");

                global $txt_att_attachment;
                //was: $this->settings[$this->adminPrefix . 'ThumbTitle']

                // let's do wrapper html
                $contentBefore  = '<!-- help-p --><div class="attachmentFile"><br />' . $txt_att_attachment . ': ';
                $contentAfter   = '<div class="clear clearfix"></div></div>';

                // admin behaves differently
                if(is_admin()){
                    $contentInner = $attachmentName;
                } else {
                    // shall we do image thumbnail or not?
                    if(ATT_THUMB && in_array($attachmentType, $this->getImageMimeTypes()) && !is_admin()){
                        $attachmentRel = 'rel="lightbox"';
                        $contentInner = wp_get_attachment_image($attachmentId, ATT_TSIZE);
                        // audio player?
                    } elseif (ATT_PLAY && in_array($attachmentType, $this->getAudioMimeTypes())){
                        if(shortcode_exists('audio')){
                            $contentInner = do_shortcode('[audio src="'. $attachmentLink .'"]');
                        } else {
                            $contentInner = $attachmentName;
                        }
                        // video player?
                    } elseif (ATT_PLAY && in_array($attachmentType, $this->getVideoMimeTypes())){
                        if(shortcode_exists('video')){
                            $contentInner .= do_shortcode('[video src="'. $attachmentLink .'"]');
                        } else {
                            $contentInner = $attachmentName;
                        }
                        // rest ..
                    } else {
                        $contentInner = '' . $attachmentName . '';
                    }
                }

                // attachment link, if it's not video / audio
                if(is_admin()){
                    $contentInnerFinal = '<a '.$attachmentRel.' class="attachmentLink" target="_blank" href="'. $attachmentLink .'" title="Download: '. $attachmentName .'">';
                        $contentInnerFinal .= $contentInner;
                    $contentInnerFinal .= '</a>';
                } else {
                    if((ATT_LINK) && !in_array($attachmentType, $this->getAudioMimeTypes()) && !in_array($attachmentType, $this->getVideoMimeTypes())){
                        $contentInnerFinal = '<a '.$attachmentRel.' class="attachmentLink" target="_blank" href="'. $attachmentLink .'" title="Download: '. $attachmentName .'">';
                            $contentInnerFinal .= $contentInner;
                        $contentInnerFinal .= '</a>';
                    } else {
                        $contentInnerFinal = $contentInner;
                    }
                }

                // bring a sellotape, this needs taping together
                $contentInsert = $contentBefore . $contentInnerFinal . $contentAfter;

                // attachment comment position
                if(ATT_APOS == 'before' && !is_admin()){
                    $comment = $contentInsert . $comment;
                } elseif(ATT_APOS == 'after' || is_admin()) {
                    $comment .= $contentInsert;
                }
            }
            return $comment;
        }


        /**
         * This deletes attachment after comment deletition.
         *
         * @param $commentId
         */

        public function deleteAttachment($commentId)
        {
            $attachmentId = get_comment_meta($commentId, 'attachmentId', TRUE);
            if(is_numeric($attachmentId) && !empty($attachmentId) && ATT_DEL){
                wp_delete_attachment($attachmentId, TRUE);
            }
        }


        /**
         * Has attachment
         *
         * @param $commentId
         * @return bool
         */

        public static function hasAttachment($commentId)
        {
            $attachmentId = get_comment_meta($commentId, 'attachmentId', TRUE);
            if(is_numeric($attachmentId) && !empty($attachmentId)){
                return true;
            }
            return false;
        }


        /*************** Admin Settings Functions **************/

        /**
         * Comment Action links
         *
         * @param $actions
         * @param $comment
         * @return array
         */

        public function addCommentActionLinks($actions, $comment)
        {
            if(wpCommentAttachment::hasAttachment($comment->comment_ID)){
                $url = $_SERVER["SCRIPT_NAME"] . "?c=$comment->comment_ID&deleteAtt=1";
                $actions['deleteAtt'] = "<a href='$url' title='".esc_attr__('Delete Attachment')."'>".__('Delete Attachment').'</a>';
            }
            return $actions;
        }


        /**
         * Plugin action links
         *
         * @param $links
         * @param $file
         * @return mixed
         */

        public function displayPluginActionLink($links, $file)
        {
            static $thisPlugin;
            if (!$thisPlugin){ $thisPlugin = plugin_basename(__FILE__); }
            if ($file == $thisPlugin){
                $settingsLink = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-discussion.php" title="Settings > Discussion > Comment Attachment">Settings</a>';
                array_push($links, $settingsLink);
            }
            return $links;
        }


        /**
         * Validates settings
         *
         * @param $input
         * @return bool
         */

        public static function validateSettings($input)
        {
            // attachment size check
            if($input['commentAttachmentMaxSize'] > wpCommentAttachment::getMaximumUploadFileSize()){
                add_settings_error('commentAttachment', 'commentAttachmentMaxSize', 'I\'m sorry, but we can\'t have attachment bigger than server allows us to. If you wish to change this and you don\'t know how, <a href="https://www.google.com/search?q=how+to+change+php.ini+upload_max_filesize" target="_blank">try this.</a>');
                $input['commentAttachmentMaxSize'] = wpCommentAttachment::getMaximumUploadFileSize();
            }
            return $input;
        }


        /**
         * Does what it says, better believe it at 4:33AM,
         * am I right? :D
         */

        public function initializeSettings()
        {
            $default = array();
            foreach ($this->getSettings() as $id => $setting){
                if ($setting['type'] != 'heading')
                    $default[$id] = $setting['std'];
            }
            update_option($this->key, $default);
        }


        /**
         * Displays settings in admin.
         *
         * @param array $args
         */

        public function displaySetting($args = array())
        {
            extract($args);
            $options = get_option($this->key);
            if (! isset($options[$id]) && $type != 'checkbox')
                $options[$id] = $std;
            elseif (! isset($options[$id]))
                $options[$id] = 0;
            $field_class = '';
            if ($class != '')
                $field_class = ' ' . $class;
            switch ($type){
                case 'heading':
                    break;
                case 'checkbox':
                    echo '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="' . $this->key . '[' . $id . ']" value="1" ' . checked($options[$id], 1, false) . ' /> <label for="' . $id . '"><span class="description">' . $desc . '</span></label>';
                    break;
                case 'select':
                    echo '<select id="' . $id . '" class="select' . $field_class . '" name="' . $this->key . '[' . $id . ']">';
                    foreach ($choices as $value => $label)
                        echo '<option value="' . esc_attr($value) . '"' . selected($options[$id], $value, false) . '>' . $label . '</option>';
                    echo '</select>';
                    if ($desc != '')
                        echo '<br /><span class="description">' . $desc . '</span>';
                    break;
                case 'radio':
                    $i = 0;
                    foreach ($choices as $value => $label){
                        echo '<input class="radio' . $field_class . '" type="radio" name="' . $this->key . '[' . $id . ']" id="' . $id . $i . '" value="' . esc_attr($value) . '" ' . checked($options[$id], $value, false) . '> <label for="' . $id . $i . '">' . $label . '</label>';
                        if ($i < count($options) - 1)
                            echo '<br />';
                        $i++;
                    }
                    if ($desc != '')
                        echo '<br /><span class="description">' . $desc . '</span>';
                    break;
                case 'textarea':
                    echo '<textarea class="' . $field_class . '" id="' . $id . '" name="' . $this->key . '[' . $id . ']" placeholder="' . $std . '" rows="5" cols="30">' . wp_htmledit_pre($options[$id]) . '</textarea>';
                    if ($desc != '')
                        echo '<br /><span class="description">' . $desc . '</span>';
                    break;
                case 'password':
                    echo '<input class="regular-text' . $field_class . '" type="password" id="' . $id . '" name="' . $this->key . '[' . $id . ']" value="' . esc_attr($options[$id]) . '" />';
                    if ($desc != '')
                        echo '<br /><span class="description">' . $desc . '</span>';
                    break;
                case 'text':
                case 'number':
                default:
                    echo '<input class="regular-text' . $field_class . '" type="'. $type .'" id="' . $id . '" name="' . $this->key . '[' . $id . ']" placeholder="' . $std . '" value="' . esc_attr($options[$id]) . '" />';
                    if ($desc != '')
                        echo '<br /><span class="description">' . $desc . '</span>';
                    break;
            }
        }


        /**
         * Simple helper for Wordpress Settings API
         *
         * @param array $args
         */

        public function createSetting($args = array())
        {
            extract($args);
            $field_args = array(
                'type'      => isset($type) ? $type : NULL,
                'id'        => isset($id) ? $id : NULL,
                'desc'      => isset($desc) ? $desc : NULL,
                'std'       => isset($std) ? $std : NULL,
                'choices'   => isset($choices) ? $choices : NULL,
                'label_for' => isset($id) ? $id : NULL,
                'class'     => isset($class) ? $class : NULL
            );
            if ($type == 'checkbox'){ $this->adminCheckboxes[] = $id; }
            add_settings_field($id, $title, array($this, 'displaySetting'), $this->adminPage, $section, $field_args);
        }


        /***************** Plugin basic weapons ****************/

        /**
         * Let's check Wordpress version, and PHP version and tell those
         * guys whats needed to upgrade, if anything.
         *
         * @return bool
         */

        private function checkRequirements()
        {
            if (!function_exists('mime_content_type') && !function_exists('finfo_file')){
                add_action('admin_notices', array($this, 'displayFunctionMissingNotice'));
                return TRUE;
            }
            return TRUE;
        }


        /**
         * Notify use about missing needed functions, and less security caused by that, let them hide nag of course.
         */

        public function displayFunctionMissingNotice()
        {
            $currentUser = wp_get_current_user();
            if (!get_user_meta($currentUser->ID, 'wpCommentAttachmentIgnoreNag') && current_user_can('install_plugins')){
                $this->displayAdminError((sprintf(
                    'It seems like your PHP installation is missing "mime_content_type" or "finfo_file" functions which are crucial '.
                    'for detecting file types of uploaded attachments. Please update your PHP installation OR be very careful with allowed file types, so '.
                    'intruders won\'t be able to upload dangerous code to your website! | <a href="%1$s">Hide Notice</a>', '?wpCommentAttachmentIgnoreNag=1')), 'updated');
            }
        }


        /**
         * Save user nag if set, if they want to hide the message above.
         */

        private function setUserNag()
        {
            $currentUser = wp_get_current_user();
            if (isset($_GET['wpCommentAttachmentIgnoreNag']) && '1' == $_GET['wpCommentAttachmentIgnoreNag'] && current_user_can('install_plugins')){
                add_user_meta($currentUser->ID, 'wpCommentAttachmentIgnoreNag', 'true', true);
            }
        }


        /**
         * Admin error helper
         *
         * @param $error
         */

        private function displayAdminError($error, $class="error") { echo '<div id="message" class="'. $class .'"><p><strong>' . $error . '</strong></p></div>';  }


        /**
         * Get's plugin instance
         *
         * @return mixed
         */

        public static function getInstance()
        {
            if (!isset(static::$instance)) { static::$instance = new static; }
            return static::$instance;
        }

        protected function __clone(){}

    }
}

new wpCommentAttachment();