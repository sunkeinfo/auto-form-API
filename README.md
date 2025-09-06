# WordPress 通用表单处理器插件

一个功能完整的WordPress插件，提供通用表单处理服务，支持REST API接口、邮件通知和后台管理界面。

## 功能特性

- ✅ REST API接口 (`/wp-json/my-forms/v1/send/{form_key}`)
- ✅ 可配置的邮件接收地址
- ✅ 自定义邮件主题和内容模板
- ✅ 自动检测回复邮箱地址
- ✅ 支持表单提交后重定向
- ✅ CORS跨域支持配置
- ✅ 后台管理界面
- ✅ 多种邮件模板样式
- ✅ 自定义成功/错误提示信息

## 安装步骤

### 1. 上传插件文件
将 `universal-form-handler.php` 文件上传到您的WordPress网站的 `/wp-content/plugins/` 目录下。

### 2. 激活插件
在WordPress后台 -> 插件 -> 已安装的插件中找到"通用表单处理器"，点击"启用"。

### 3. 配置插件
进入 WordPress后台 -> 设置 -> 表单处理器，配置以下选项：

- **接收邮箱**: 表单提交后接收通知的邮箱地址
- **邮件主题模板**: 自定义邮件主题格式
- **邮件模板样式**: 选择表格样式或简单样式
- **成功提示信息**: 表单成功提交后的提示文字
- **错误提示信息**: 表单提交失败时的提示文字
- **允许的域名**: CORS跨域限制配置（可选）

### 4. 刷新固定链接
进入 WordPress后台 -> 设置 -> 固定链接，点击"保存更改"以激活API路由。

### 5. 配置SMTP（推荐）
安装 WP Mail SMTP 插件并配置SMTP服务，确保邮件发送稳定。

## 使用方法

### API接口地址
```
POST https://your-domain.com/wp-json/my-forms/v1/send/{form_key}
```

将 `{form_key}` 替换为您的表单标识符，例如：`contact-form`

### HTML表单示例

```html
<form action="https://your-domain.com/wp-json/my-forms/v1/send/contact-form" method="POST">
    <input type="text" name="姓名" required>
    <input type="email" name="email" required>
    <textarea name="留言内容" required></textarea>
    
    <!-- 可选：提交成功后重定向 -->
    <input type="hidden" name="_redirect_url" value="https://example.com/thank-you">
    
    <button type="submit">提交</button>
</form>
```

### JavaScript异步提交示例

```javascript
const form = document.getElementById('contact-form');
form.addEventListener('submit', function(event) {
    event.preventDefault();
    
    const formData = new FormData(form);
    const actionURL = form.getAttribute('action');

    fetch(actionURL, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        form.reset();
    })
    .catch(error => {
        alert('提交失败，请稍后重试');
    });
});
```

## 高级配置

### 自动回复邮箱检测
插件会自动检测以下字段作为回复邮箱：
- `email`
- `your-email`
- `user_email`
- `contact_email`
- `reply_email`

### 特殊字段说明
- `_redirect_url`: 提交成功后重定向的URL地址
- 以 `_` 开头的字段不会显示在邮件内容中

### CORS跨域配置
在插件设置中配置允许的域名，每行一个：
```
https://www.example.com
http://localhost:8080
```

### 防火墙白名单
如使用宝塔面板等防火墙，请添加URL白名单规则：
```
^/wp-json/my-forms/v1/send/.*
```

## 邮件模板变量

### 邮件主题模板
- `%1$s`: 表单标识符 (form_key)
- `%2$s`: 网站名称

默认模板：`来自网站"%2$s"的新表单提交：%1$s`

## 故障排除

### 1. 404错误
- 确保已刷新固定链接
- 检查API地址是否正确

### 2. 邮件发送失败
- 安装并配置 WP Mail SMTP 插件
- 检查服务器邮件配置
- 确认接收邮箱地址正确

### 3. CORS跨域问题
- 在插件设置中配置允许的域名
- 检查防火墙设置

### 4. 表单数据为空
- 确保使用POST方法提交
- 检查表单字段的name属性

## 技术支持

如遇到问题，请检查：
1. WordPress版本兼容性
2. 插件是否正确激活
3. 固定链接是否已刷新
4. 邮件服务配置是否正确

## 更新日志

### v1.0.0
- 初始版本发布
- 支持REST API表单处理
- 后台管理界面
- 邮件通知功能
- CORS跨域支持
