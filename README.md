# WordPress 通用表单处理器插件

**无需预设计表单结构，任何HTML表单都能直接使用！**

这是一个革命性的WordPress表单处理插件，**无需提前设计表单字段**，直接将任何HTML表单的所有内容一次性通过邮件发送。支持跨域调用，**不局限于本站使用，任何外部网站都可以调用这个API**。

## 🚀 核心优势

- 🎯 **零配置表单** - 无需预设字段，任何HTML表单直接可用
- 🌐 **跨域支持** - 外部网站、静态页面、任何域名都可调用
- 📧 **智能邮件** - 自动将所有表单字段整理成邮件发送
- 🔧 **即插即用** - 安装后立即可用，无需复杂配置
- 🎨 **灵活定制** - 支持自定义邮件模板和提示信息

## 功能特性

- ✅ **通用REST API接口** (`/wp-json/my-forms/v1/send/{form_key}`)
- ✅ **智能表单解析** - 自动处理任意表单字段
- ✅ **跨域CORS支持** - 支持外部网站调用
- ✅ **自动邮件发送** - 将表单内容整理成邮件
- ✅ **回复邮箱检测** - 自动识别用户邮箱用于回复
- ✅ **重定向支持** - 提交后可跳转到指定页面
- ✅ **多种邮件模板** - 表格样式或简单样式
- ✅ **自定义提示信息** - 个性化成功/错误提示

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

### 5. 配置SMTP（必需）
**重要：** 本插件需要配合SMTP插件使用。推荐安装以下插件之一：
- **WP Mail SMTP** - 最受欢迎的SMTP插件
- **Easy WP SMTP** - 简单易用的SMTP配置
- **Post SMTP Mailer/Email Log** - 功能全面的邮件插件

## 🎯 使用方法（超简单）

### 1. API接口地址
```
POST https://your-domain.com/wp-json/my-forms/v1/send/{form_key}
```

将 `{form_key}` 替换为您的表单标识符，例如：`contact-form`

### 2. 核心理念
**无需预设计表单！** 只需要：
1. 设计任意HTML表单
2. 将表单action指向API地址
3. 所有表单字段自动通过邮件发送

### 3. 适用场景
- ✅ 静态网站表单处理
- ✅ 外部网站联系表单
- ✅ 第三方页面表单提交
- ✅ 任何需要表单处理的场景

### 4. HTML表单示例

**任何表单都可以直接使用，无需修改字段结构：**

```html
<!-- 基础联系表单 -->
<form action="https://your-domain.com/wp-json/my-forms/v1/send/contact-form" method="POST">
    <input type="text" name="姓名" required>
    <input type="email" name="email" required>
    <input type="tel" name="电话">
    <select name="咨询类型">
        <option value="产品咨询">产品咨询</option>
        <option value="技术支持">技术支持</option>
    </select>
    <textarea name="留言内容" required></textarea>
    
    <!-- 可选：提交成功后重定向 -->
    <input type="hidden" name="_redirect_url" value="https://example.com/thank-you">
    
    <button type="submit">提交</button>
</form>

<!-- 复杂表单示例 -->
<form action="https://your-domain.com/wp-json/my-forms/v1/send/registration" method="POST">
    <input type="text" name="公司名称">
    <input type="text" name="联系人">
    <input type="email" name="email">
    <input type="text" name="职位">
    <input type="text" name="行业">
    <textarea name="需求描述"></textarea>
    <input type="checkbox" name="同意条款" value="是">
    
    <button type="submit">注册</button>
</form>
```

**所有字段都会自动发送到邮箱，无需预先配置！**

### 5. JavaScript异步提交示例

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

## 🔧 高级配置

### 智能邮箱检测
插件会自动检测以下字段作为回复邮箱（无需配置）：
- `email`
- `your-email` 
- `user_email`
- `contact_email`
- `reply_email`

**任何包含这些名称的字段都会被自动识别为回复邮箱**

### 特殊字段说明
- `_redirect_url`: 提交成功后重定向的URL地址
- 以 `_` 开头的字段不会显示在邮件内容中

### 跨域配置（支持外部网站）
**默认允许所有域名调用**，如需限制可在插件设置中配置允许的域名：
```
https://www.example.com
http://localhost:8080
https://static-site.netlify.app
```

**支持场景：**
- 静态网站（Netlify、Vercel、GitHub Pages等）
- 外部域名网站
- 本地开发环境
- 任何第三方网站

### 防火墙白名单
如使用宝塔面板等防火墙，请添加URL白名单规则：
```
^/wp-json/my-forms/v1/send/.*
```

## 📧 邮件模板

### 邮件主题模板
- `%1$s`: 表单标识符 (form_key)
- `%2$s`: 网站名称

默认模板：`来自网站"%2$s"的新表单提交：%1$s`

## ❓ 故障排除

### 1. 404错误
- 确保已刷新固定链接
- 检查API地址是否正确

### 2. 邮件发送失败
- **必须安装SMTP插件**（如 WP Mail SMTP）
- 检查SMTP插件配置
- 确认接收邮箱地址正确
- 使用插件内置的测试邮件功能

### 3. CORS跨域问题
- 在插件设置中配置允许的域名
- 检查防火墙设置

### 4. 表单数据为空
- 确保使用POST方法提交
- 检查表单字段的name属性

## 💡 使用技巧

### 表单字段命名建议
- 使用中文字段名更直观：`姓名`、`电话`、`公司名称`
- 邮箱字段建议命名为 `email` 以便自动识别
- 避免使用下划线开头的字段名（会被隐藏）

### 最佳实践
1. **任何现有表单都可直接使用** - 只需修改action地址
2. **支持所有HTML表单元素** - input、textarea、select等
3. **自动处理文件上传** - 支持文件字段（需服务器支持）
4. **完美支持静态网站** - 解决静态网站表单处理难题

## 🛠 技术支持

### 快速检查清单
1. ✅ WordPress插件已激活
2. ✅ 已安装并配置SMTP插件
3. ✅ 已刷新固定链接
4. ✅ API地址格式正确
5. ✅ 表单使用POST方法

## 🎉 为什么选择这个插件？

### 传统表单处理的痛点
- ❌ 需要预先设计表单结构
- ❌ 修改表单需要重新配置
- ❌ 静态网站无法处理表单
- ❌ 跨域调用复杂

### 本插件的解决方案
- ✅ **零配置** - 任何表单直接可用
- ✅ **动态适应** - 自动处理任意字段
- ✅ **跨域友好** - 完美支持外部调用
- ✅ **即插即用** - 安装后立即可用

## 📝 更新日志

### v1.0.0
- 🎯 革命性的零配置表单处理
- 🌐 完整的跨域CORS支持
- 📧 智能邮件模板系统
- 🔧 简洁的后台管理界面
- 🚀 支持任意HTML表单结构
