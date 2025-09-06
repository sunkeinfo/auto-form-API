# 文件打包说明

## 创建 universal-form-handler.zip

需要打包的文件：
```
universal-form-handler.zip
├── universal-form-handler.php
└── README.md
```

## 创建 contact-form-example.zip

需要打包的文件：
```
contact-form-example.zip
├── form-example.html
├── simple-form.html
└── README-examples.md
```

## 打包步骤

### Windows 系统：
1. 选中需要打包的文件
2. 右键点击 → 发送到 → 压缩(zipped)文件夹
3. 重命名为对应的文件名

### macOS 系统：
1. 选中需要打包的文件
2. 右键点击 → 压缩项目
3. 重命名为对应的文件名

### Linux 系统：
```bash
# 创建插件包
zip -r universal-form-handler.zip universal-form-handler.php README.md

# 创建示例包
zip -r contact-form-example.zip form-example.html simple-form.html README-examples.md
```

## 注意事项

1. 确保zip文件名与网站下载链接一致
2. 插件包应包含完整的使用说明
3. 示例包应包含多种使用场景的代码
4. 所有文件都应该是UTF-8编码