<?php
/**
 * Plugin Name: 通用表单处理器
 * Plugin URI: https://example.com
 * Description: 一个通用的WordPress表单处理插件，支持REST API接口、邮件通知和管理界面配置。需要配合SMTP插件使用。
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class UniversalFormHandler {
    
    private $option_name = 'ufh_settings';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // 激活插件时刷新固定链接
        register_activation_hook(__FILE__, array($this, 'activate'));
    }
    
    public function init() {
        // 插件初始化
    }
    
    public function activate() {
        flush_rewrite_rules();
    }
    
    /**
     * 注册REST API路由
     */
    public function register_rest_routes() {
        register_rest_route('my-forms/v1', '/send/(?P<form_key>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_form_submission'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * 处理表单提交
     */
    public function handle_form_submission(WP_REST_Request $request) {
        $settings = get_option($this->option_name);
        $recipient_email = isset($settings['recipient_email']) ? $settings['recipient_email'] : get_option('admin_email');
        
        if (empty($recipient_email)) {
            return new WP_REST_Response(array('message' => '未配置接收邮箱地址'), 500);
        }
        
        $form_key = $request->get_param('form_key');
        $form_data = $request->get_body_params();
        
        if (empty($form_data)) {
            return new WP_REST_Response(array('message' => '未收到任何表单数据。API接口工作正常，正在等待POST请求。'), 400);
        }
        
        // 构建邮件主题
        $subject_template = isset($settings['email_subject']) ? $settings['email_subject'] : '来自网站"%2$s"的新表单提交：%1$s';
        $subject = sprintf($subject_template, $form_key, get_bloginfo('name'));
        
        // 构建邮件内容
        $message_body = $this->build_email_content($form_data, $settings);
        
        // 设置邮件头
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // 自动回复邮箱检测
        $reply_to_email = $this->detect_reply_email($form_data);
        if ($reply_to_email) {
            $headers[] = 'Reply-To: ' . $reply_to_email;
        }
        
        // 发送邮件
        $sent = wp_mail($recipient_email, $subject, $message_body, $headers);
        
        if ($sent) {
            // 处理重定向
            $redirect_url = !empty($form_data['_redirect_url']) ? esc_url_raw($form_data['_redirect_url']) : null;
            if ($redirect_url) {
                $response = new WP_REST_Response(array('message' => '提交成功。页面即将跳转...'));
                $response->set_status(302);
                $response->header('Location', $redirect_url);
                return $response;
            }
            
            $success_message = isset($settings['success_message']) ? $settings['success_message'] : '您的信息已成功提交，感谢您的反馈！';
            return new WP_REST_Response(array('message' => $success_message), 200);
        } else {
            $error_message = isset($settings['error_message']) ? $settings['error_message'] : '邮件发送失败。请检查SMTP插件配置。';
            return new WP_REST_Response(array('message' => $error_message), 500);
        }
    }
    
    /**
     * 构建邮件内容
     */
    private function build_email_content($form_data, $settings) {
        $email_template = isset($settings['email_template']) ? $settings['email_template'] : 'table';
        
        if ($email_template === 'simple') {
            return $this->build_simple_email($form_data);
        } else {
            return $this->build_table_email($form_data);
        }
    }
    
    /**
     * 构建表格样式邮件
     */
    private function build_table_email($form_data) {
        $message_body = '<p>您好，您收到一份新的表单提交。详细内容如下：</p>';
        $message_body .= '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
        $message_body .= '<thead><tr><th style="background-color: #f2f2f2; text-align: left;">项目</th><th style="background-color: #f2f2f2; text-align: left;">提交内容</th></tr></thead>';
        $message_body .= '<tbody>';
        
        foreach ($form_data as $key => $value) {
            if ($key === '_redirect_url') {
                continue;
            }
            
            $clean_key = htmlspecialchars(stripslashes($key));
            $clean_value = nl2br(htmlspecialchars(stripslashes(is_array($value) ? implode(', ', $value) : $value)));
            
            $message_body .= "<tr><td><strong>{$clean_key}</strong></td><td>{$clean_value}</td></tr>";
        }
        
        $message_body .= '</tbody></table>';
        return $message_body;
    }
    
    /**
     * 构建简单样式邮件
     */
    private function build_simple_email($form_data) {
        $message_body = '<p>您好，您收到一份新的表单提交：</p>';
        
        foreach ($form_data as $key => $value) {
            if ($key === '_redirect_url') {
                continue;
            }
            
            $clean_key = htmlspecialchars(stripslashes($key));
            $clean_value = nl2br(htmlspecialchars(stripslashes(is_array($value) ? implode(', ', $value) : $value)));
            
            $message_body .= "<p><strong>{$clean_key}:</strong> {$clean_value}</p>";
        }
        
        return $message_body;
    }
    
    /**
     * 检测回复邮箱
     */
    private function detect_reply_email($form_data) {
        $email_fields = array('email', 'your-email', 'user_email', 'contact_email', 'reply_email');
        
        foreach ($email_fields as $field) {
            if (isset($form_data[$field]) && is_email($form_data[$field])) {
                return sanitize_email($form_data[$field]);
            }
        }
        
        return '';
    }
    
    /**
     * 处理测试邮件
     */
    private function handle_test_email() {
        $settings = get_option($this->option_name);
        $test_email = isset($settings['recipient_email']) ? $settings['recipient_email'] : get_option('admin_email');
        
        if (empty($test_email)) {
            echo '<div class="notice notice-error"><p>未配置接收邮箱地址</p></div>';
            return;
        }
        
        $subject = '测试邮件 - ' . get_bloginfo('name');
        $message = '<h3>WordPress邮件测试</h3><p>如果您收到这封邮件，说明WordPress邮件功能正常工作。</p><p>发送时间: ' . current_time('Y-m-d H:i:s') . '</p><p><strong>提示：</strong>如果您没有收到邮件，请检查SMTP插件配置。</p>';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $sent = wp_mail($test_email, $subject, $message, $headers);
        
        if ($sent) {
            echo '<div class="notice notice-success"><p>测试邮件发送成功！请检查邮箱: ' . esc_html($test_email) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>测试邮件发送失败，请安装并配置SMTP插件，如 WP Mail SMTP</p></div>';
        }
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_options_page(
            '通用表单处理器设置',
            '表单处理器',
            'manage_options',
            'universal-form-handler',
            array($this, 'options_page')
        );
    }
    
    /**
     * 初始化设置
     */
    public function settings_init() {
        register_setting('ufh_settings', $this->option_name);
        
        add_settings_section(
            'ufh_basic_section',
            '基本设置',
            array($this, 'settings_section_callback'),
            'ufh_settings'
        );
        
        add_settings_field(
            'recipient_email',
            '接收邮箱',
            array($this, 'recipient_email_render'),
            'ufh_settings',
            'ufh_basic_section'
        );
        
        add_settings_field(
            'email_subject',
            '邮件主题模板',
            array($this, 'email_subject_render'),
            'ufh_settings',
            'ufh_basic_section'
        );
        
        add_settings_field(
            'email_template',
            '邮件模板样式',
            array($this, 'email_template_render'),
            'ufh_settings',
            'ufh_basic_section'
        );
        
        add_settings_field(
            'success_message',
            '成功提示信息',
            array($this, 'success_message_render'),
            'ufh_settings',
            'ufh_basic_section'
        );
        
        add_settings_field(
            'error_message',
            '错误提示信息',
            array($this, 'error_message_render'),
            'ufh_settings',
            'ufh_basic_section'
        );
        
        add_settings_field(
            'allowed_origins',
            '允许的域名 (CORS)',
            array($this, 'allowed_origins_render'),
            'ufh_settings',
            'ufh_basic_section'
        );
        
        add_settings_field(
            'test_email',
            '测试邮件',
            array($this, 'test_email_render'),
            'ufh_settings',
            'ufh_basic_section'
        );
    }
    
    public function recipient_email_render() {
        $options = get_option($this->option_name);
        $value = isset($options['recipient_email']) ? $options['recipient_email'] : get_option('admin_email');
        ?>
        <input type='email' name='<?php echo $this->option_name; ?>[recipient_email]' value='<?php echo esc_attr($value); ?>' style='width: 300px;' required>
        <p class="description">表单提交后将发送邮件到此邮箱地址</p>
        <?php
    }
    
    public function email_subject_render() {
        $options = get_option($this->option_name);
        $value = isset($options['email_subject']) ? $options['email_subject'] : '来自网站"%2$s"的新表单提交：%1$s';
        ?>
        <input type='text' name='<?php echo $this->option_name; ?>[email_subject]' value='<?php echo esc_attr($value); ?>' style='width: 400px;'>
        <p class="description">%1$s = 表单标识，%2$s = 网站名称</p>
        <?php
    }
    
    public function email_template_render() {
        $options = get_option($this->option_name);
        $value = isset($options['email_template']) ? $options['email_template'] : 'table';
        ?>
        <select name='<?php echo $this->option_name; ?>[email_template]'>
            <option value='table' <?php selected($value, 'table'); ?>>表格样式</option>
            <option value='simple' <?php selected($value, 'simple'); ?>>简单样式</option>
        </select>
        <p class="description">选择邮件内容的显示样式</p>
        <?php
    }
    
    public function success_message_render() {
        $options = get_option($this->option_name);
        $value = isset($options['success_message']) ? $options['success_message'] : '您的信息已成功提交，感谢您的反馈！';
        ?>
        <textarea name='<?php echo $this->option_name; ?>[success_message]' rows='3' style='width: 400px;'><?php echo esc_textarea($value); ?></textarea>
        <p class="description">表单成功提交后显示的信息</p>
        <?php
    }
    
    public function error_message_render() {
        $options = get_option($this->option_name);
        $value = isset($options['error_message']) ? $options['error_message'] : '邮件发送失败。请检查SMTP插件配置。';
        ?>
        <textarea name='<?php echo $this->option_name; ?>[error_message]' rows='3' style='width: 400px;'><?php echo esc_textarea($value); ?></textarea>
        <p class="description">表单提交失败时显示的信息</p>
        <?php
    }
    
    public function allowed_origins_render() {
        $options = get_option($this->option_name);
        $value = isset($options['allowed_origins']) ? $options['allowed_origins'] : '';
        ?>
        <textarea name='<?php echo $this->option_name; ?>[allowed_origins]' rows='4' style='width: 400px;' placeholder="https://www.example.com&#10;http://localhost:8080"><?php echo esc_textarea($value); ?></textarea>
        <p class="description">每行一个域名，用于CORS跨域限制。留空则允许所有域名</p>
        <?php
    }
    
    public function test_email_render() {
        $test_url = admin_url('admin.php?page=universal-form-handler&test_email=1&nonce=' . wp_create_nonce('test_email'));
        ?>
        <a href="<?php echo $test_url; ?>" class='button button-secondary'>发送测试邮件</a>
        <p class="description">测试WordPress邮件功能是否正常工作</p>
        <?php
        
        // 处理测试请求
        if (isset($_GET['test_email']) && $_GET['test_email'] == '1') {
            if (wp_verify_nonce($_GET['nonce'], 'test_email')) {
                $this->handle_test_email();
            }
        }
    }
    
    public function settings_section_callback() {
        echo '<div class="notice notice-warning"><p><strong>重要提示：</strong>本插件需要配合SMTP插件使用才能正常发送邮件。推荐安装以下插件之一：</p>';
        echo '<ul><li>• <strong>WP Mail SMTP</strong> - 最受欢迎的SMTP插件</li>';
        echo '<li>• <strong>Easy WP SMTP</strong> - 简单易用的SMTP配置</li>';
        echo '<li>• <strong>Post SMTP Mailer/Email Log</strong> - 功能全面的邮件插件</li></ul>';
        echo '<p>安装并配置好SMTP插件后，本表单处理器才能正常发送邮件通知。</p></div>';
    }
    
    /**
     * 选项页面
     */
    public function options_page() {
        $site_url = get_site_url();
        $html_code = $this->get_html_template($site_url);
        ?>
        <div class="wrap">
            <h1>通用表单处理器设置</h1>
            
            <div class="notice notice-info">
                <p><strong>API接口地址：</strong> <code><?php echo $site_url; ?>/wp-json/my-forms/v1/send/{form_key}</code></p>
                <p>将 <code>{form_key}</code> 替换为您的表单标识符，例如：<code>contact-form</code></p>
            </div>
            
            <form action='options.php' method='post'>
                <?php
                settings_fields('ufh_settings');
                do_settings_sections('ufh_settings');
                submit_button();
                ?>
            </form>
            
            <div class="card" style="margin-top: 20px;">
                <h2>完整HTML表单示例</h2>
                <p>复制下方代码到您的HTML文件中，或点击下载按钮获取完整文件：</p>
                <div style="margin-bottom: 10px;">
                    <button type="button" onclick="copyHtmlCode()" class="button button-secondary">📋 复制代码</button>
                    <button type="button" onclick="downloadHtmlFile()" class="button button-secondary">💾 下载HTML文件</button>
                </div>
                <textarea id="html-code" readonly style="width: 100%; height: 400px; font-family: monospace; font-size: 11px; background: #f9f9f9; border: 1px solid #ddd; padding: 10px;"><?php echo esc_textarea($html_code); ?></textarea>
            </div>
            
            <script>
            function copyHtmlCode() {
                const textarea = document.getElementById('html-code');
                textarea.select();
                document.execCommand('copy');
                alert('HTML代码已复制到剪贴板！');
            }
            
            function downloadHtmlFile() {
                const content = document.getElementById('html-code').value;
                const blob = new Blob([content], { type: 'text/html' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'contact-form.html';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
            </script>
        </div>
        <?php
    }
    
    /**
     * 获取HTML模板
     */
    private function get_html_template($site_url) {
        return '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>联系我们 - 表单示例</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #007cba; box-shadow: 0 0 0 2px rgba(0,124,186,0.1); }
        button { background: #007cba; color: white; padding: 12px 30px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; width: 100%; }
        button:hover { background: #005a87; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        #form-status { margin-top: 20px; padding: 15px; border-radius: 4px; display: none; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .loading { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>联系我们</h1>
        
        <form id="contact-form" action="' . $site_url . '/wp-json/my-forms/v1/send/contact-form" method="POST">
            <div class="form-group">
                <label for="name">姓名 *</label>
                <input type="text" id="name" name="姓名" required>
            </div>
            
            <div class="form-group">
                <label for="email">邮箱地址 *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">联系电话</label>
                <input type="tel" id="phone" name="联系电话">
            </div>
            
            <div class="form-group">
                <label for="subject">主题</label>
                <select id="subject" name="咨询主题">
                    <option value="">请选择咨询主题</option>
                    <option value="产品咨询">产品咨询</option>
                    <option value="技术支持">技术支持</option>
                    <option value="商务合作">商务合作</option>
                    <option value="其他">其他</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="message">留言内容 *</label>
                <textarea id="message" name="留言内容" rows="5" required placeholder="请详细描述您的问题或需求..."></textarea>
            </div>
            
            <input type="hidden" name="_redirect_url" value="">
            
            <button type="submit" id="submit-button">发送消息</button>
        </form>
        
        <div id="form-status"></div>
    </div>

    <script>
        const form = document.getElementById("contact-form");
        const statusDiv = document.getElementById("form-status");
        const submitButton = document.getElementById("submit-button");

        form.addEventListener("submit", function(event) {
            event.preventDefault();
            
            submitButton.disabled = true;
            submitButton.textContent = "发送中...";
            
            statusDiv.style.display = "block";
            statusDiv.className = "loading";
            statusDiv.textContent = "正在发送，请稍候...";

            const formData = new FormData(form);
            const actionURL = form.getAttribute("action");

            fetch(actionURL, {
                method: "POST",
                body: formData,
            })
            .then(response => {
                if (response.status === 302) {
                    const location = response.headers.get("Location");
                    if (location) {
                        window.location.href = location;
                        return;
                    }
                }
                
                if (response.ok) {
                    return response.json();
                }
                throw new Error("服务器响应失败 (状态码: " + response.status + ")");
            })
            .then(data => {
                if (data) {
                    statusDiv.className = "success";
                    statusDiv.textContent = data.message || "消息发送成功！";
                    form.reset();
                }
            })
            .catch(error => {
                console.error("表单提交错误:", error);
                statusDiv.className = "error";
                statusDiv.textContent = "发送失败，请检查网络连接或稍后重试。";
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = "发送消息";
            });
        });
    </script>
</body>
</html>';
    }
    
    /**
     * 加载前端脚本
     */
    public function enqueue_scripts() {
        // 可以在这里添加前端JavaScript支持
    }
}

// 初始化插件
new UniversalFormHandler();

// CORS 处理
add_action('rest_api_init', function() {
    $settings = get_option('ufh_settings');
    $allowed_origins_text = isset($settings['allowed_origins']) ? $settings['allowed_origins'] : '';
    
    if (!empty($allowed_origins_text)) {
        $allowed_origins = array_filter(array_map('trim', explode("\n", $allowed_origins_text)));
        
        if (!empty($allowed_origins)) {
            remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
            add_filter('rest_pre_serve_request', function($value) use ($allowed_origins) {
                $origin = get_http_origin();
                
                if ($origin && in_array($origin, $allowed_origins)) {
                    header('Access-Control-Allow-Origin: ' . esc_url_raw($origin));
                    header('Access-Control-Allow-Methods: POST');
                    header('Access-Control-Allow-Credentials: true');
                }
                
                return $value;
            });
        }
    }
}, 15);